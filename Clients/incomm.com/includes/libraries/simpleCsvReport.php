<?php

/**
 * Simple CSV report generation class
 * 
 * @category giftingapp
 * @package simpleCsvReport
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

class simpleCsvReport {
	const ERROR_PROMO_USAGE = 800001;
	const ERROR_INCORRECT_METHOD = 800002;

	/**
	 * Singleton instance referring to the class itself
	 * 
	 * @access private
	 * @var object
	 * @see self::getInstance()
	 */
	private static $_instance = null;

	/**
	 * Promo report options
	 * array (
	 *   'startDate'         => 'Start date, CLI option -s',
	 *   'endDate'           => 'End date, CLI option -e',
	 *   'partners'          => 'Partner names, CLI option -p (for only one partner,
	 *                           but here it keeps one or many partners)', 
	 *   'remoteDelivery'    => 'Remote delivery, sends report to SFTP server, CLI option -r',
	 *   'forceRegeneration' => 'Force to regenerate CSV reports, CLI option -f'
	 * )
	 * 
	 * @access public
	 * @var array
	 */
	public $_promoReportOptions = array();

	/**
	 * Remote SFTP connection classes keeper
	 * 
	 * @access protected
	 * @var array
	 */
	protected $_remoteConnections = array();

	/**
	 * SQL template
	 *
	 * @access protected
	 * @var string
	 */
	protected $_sqls = array(
		'promo' => '
			SELECT p.description AS product,
				m.created AS date,
				ptg.pluginData AS promoCode,
				m.amount - pt.discountAmount AS totalPaid,
				g.partner AS partnerName
			FROM promoTransactions pt
			JOIN promoTriggers ptg ON pt.promoTriggerId = ptg.id
			JOIN messages m on pt.messageId = m.id
			JOIN gifts g on m.giftId = g.id
			JOIN products p ON g.productId = p.id
			WHERE m.created BETWEEN "%s" AND "%s"
				AND m.refunded IS NULL
				AND g.envName = "%s"
				AND %s'
		);

	/**
	 * DB query result fields
	 *
	 * @access protected
	 * @var array 
	 */
	protected $_fields = array();

	/**
	 * Use master DB link or not
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_useDbMaster = false;

	/**
	 * Write to a CSV file or echo out
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $_writeToFile = true;

	/**
	 * CSV report file name
	 *
	 * @access protected
	 * @var string
	 */
	protected $_csvFileName;

	protected $_reportDirectory;

	protected $_handle;

	/**
	 * Singleton pattern that always return the same class instance
	 * 
	 * @access public
	 * @return object self
	 * @static
	 * @uses self::$_instance
	 */
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->_reportDirectory = '/var/reports/' . ENV::main()->getEnvName();
	}

	/*
	protected function _getSftpConnections($partner) { 
		$conntions = array();
		$ftp = settingModel::getPartnerSettings($partner, 'reportFtp');
		// If there's no ftp data set, return
		if (!isset($ftp['domain'])) {
			return false;
		}
		$domains = explode('|', $ftp['domain']);
		$usernames = explode('|', $ftp['username']);
		$passwords = explode('|', $ftp['password']);
		$remoteDirs = explode('|', $ftp['remoteDir']);
		foreach ($domains as $i => $domain) { 
			$sftp = new sftp;
			$sftp->host = $domains[$i];
			$sftp->user = $usernames[$i];
			$sftp->pass = $passwords[$i];
			$sftp->fdir = $remoteDirs[$i];
			$sftp->port = 22;
			$sftp->protocol = 'sftp';
			// Set connection timeout
			$sftp->timeout = 8;
			// connect to ftp
			if ($sftp->connect()) {
				// Successfully connected, change current directory to fdir
				$sftp->chdir();
				$conns[] = $sftp;
			} else {
				print 'UNABLE TO AUTH!' . PHP_EOL;
			}
		}

		return $conntions;
	}
	*/

	public function __call($name, $arguments) {
		switch ($name) {
			case 'useDbMaster':
				$this->_useDbMaster = $arguments[0];
				break;
			case 'writeToFile':
				if (is_string($arguments[0])) {
					$this->_csvFileName = $arguments[0];
					$this->_writeToFile = true;
				} elseif (is_bool($arguments[0]) && $arguments[0] == false) {
					$this->_writeToFile = false;
				}
				break;
			case 'echoOut':
				if (is_bool($arguments[0])) {
					$this->_writeToFile = ! $arguments[0];
				}
				break;
			default:
				throw new Exception('Incorrect method call, name: ' . $name, self::ERROR_INCORRECT_METHOD);
		}

		return $this;
	}

	protected function _fopen($mode = 'wb') {
		$filename = $this->_reportDirectory . '/' . $this->_csvFileName;
		$this->_handle = fopen($filename, $mode);
	}

	protected function _fclose() {
		fclose($this->_handle);
	}

	protected function _fwriteLine() {
		if (count($this->_fields) == 0) {
			return false;
		}
		$fields = array();
		foreach ($this->_fields as $f) {
			$fields[] = strpos($f, ',') ? '"' . $f . '"' : $f;
		}
		$line = implode(',', $fields) . PHP_EOL;
		if (fwrite($this->_handle, $line) === false) {
			return false;
		}

		return true;
	}

	protected function _fwriteHeader($recordSet) {
		$this->_fields = array();
		$i = 0;
		while ($i < mysql_num_fields($recordSet)) {
			$this->_fields[] = mysql_field_name($recordSet, $i);
			$i++;
		}
		$this->_fwriteLine();
	}

	public function promoReportCli() {
		$options = getopt('s:e::r::f::p::h::', array(
			'start:', 'end:', 'partner:',
			'remote', 'force', 'help'
			));
		$this->_promoReportOptions = array(
			'startDate' => isset($options['s']) ? $options['s'] : $this->_printPromoReportUsage(),
			'endDate' => isset($options['e']) ? $options['e'] : $options['s'],
			'partners' => isset($options['p']) ? explode(',', $options['p']) : array(), 
			'remoteDelivery' => isset($options['r']),
			'forceRegeneration' => isset($options['f'])
			);
		$this->_promoReportOptions['startTimestamp'] = strtotime($this->_promoReportOptions['startDate']);
		$this->_promoReportOptions['endTimestamp'] = strtotime($this->_promoReportOptions['endDate']);
		// Escape special chars from partner names
		foreach ($this->_promoReportOptions['partners'] as $index => $partner) {
			$this->_promoReportOptions['partners'][$index] = db::escape($partner);
		}
		if (empty($this->_csvFileName)) {
			$start = date('Ymd', $this->_promoReportOptions['startTimestamp']);
			$end = date('Ymd', $this->_promoReportOptions['endTimestamp']);
			$this->_csvFileName = (count($this->_promoReportOptions['partners']) == 0 ? 'all-' : '')
				. (($start != $end) ? $start . '-' . $end : $start) . '.csv';
		}
		$this->_reportDirectory .= '/promos';

		print '- Starting generating report for promo data between '
			. date('Y-m-d', $this->_promoReportOptions['startTimestamp'])
			. ' and ' . date('Y-m-d', $this->_promoReportOptions['endTimestamp'])
			. ' ...' . PHP_EOL;

		$this->promoReportGenerate();

		return $this;
	}

	public function promoReportGenerate() {
		if (empty($this->_promoReportOptions)) {
			$this->_printPromoReportUsage();
		}
		$startDateTime = date('Y-m-d 00:00:00', $this->_promoReportOptions['startTimestamp']);
		$endDateTime = date('Y-m-d 23:59:59', $this->_promoReportOptions['endTimestamp']);
		$partnerNames = (count($this->_promoReportOptions['partners']) > 0)
			? ' g.partner IN ("' . implode('", "', $this->_promoReportOptions['partners']) . '")'
			: ' 1';
		$envName = ENV::main()->getEnvName();

		print '- Fetching promo data from database ...' . PHP_EOL;

		$rs = db::query(
			sprintf($this->_sqls['promo'], $startDateTime, $endDateTime, $envName, $partnerNames),
			$this->_useDbMaster
			);
		if (mysql_num_rows($rs) == 0) {
			print '** No promo data found **' . PHP_EOL . '[DONE]' . PHP_EOL;
			return;
		}

		print '- Writing promo data to CSV file: ' . $this->_reportDirectory . '/' . $this->_csvFileName
			. ' ...' . PHP_EOL;

		$this->_fopen();
		$this->_fwriteHeader($rs);
		while ($this->_fields = mysql_fetch_assoc($rs)) {
			$this->_fwriteLine();
		}
		$this->_fclose();

		print '[DONE]' . PHP_EOL;
	}

	protected function _printPromoReportUsage() {
		throw new Exception('
  usage:
    php rawXml.php -s[start string]
    options:
      -s Start date string in strtotime format
      -e End date string in strtotime format (optional)
      -p Partner to generate the reports for (optional)
      -r Remote delivery that sends CSV file to remote SFTP (optional)
      -f Force CSV files to regenerate when running (optional)
  ', self::ERROR_PROMO_USAGE);
	}

}

/*
SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

SELECT  P.description AS Product,
        M.created AS Date,
        PTG.pluginData AS PromoCode,
        M.amount - PT.discountAmount AS TotalPaid
FROM    promoTransactions PT
JOIN    promoTriggers PTG ON PT.promoTriggerId = PTG.id
JOIN    messages M on PT.messageId = M.id
JOIN    gifts G on M.giftId = G.id
JOIN    products P ON G.productId = P.id
WHERE   M.created BETWEEN '$DATE_FROM' AND '$DATE_TO'
AND     M.refunded IS NULL;

SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;
*/
