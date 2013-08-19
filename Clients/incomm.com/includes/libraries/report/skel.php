<?php

/**
 * Reporting tool - skel abstract class
 * 
 * @category giftingapp
 * @package libtool.report
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

// Package libtool.report
namespace libtool\report;

// Use class libtool\report as report
use libtool\report;

abstract class skel {
	/**
	 * Class constants for exception code
	 * Exception code class 13001* (13001[0-9])
	 */
	const EXCP_BAD_METHOD = 130011;

	/**
	 * Class constants for different date & time types
	 * - Timestamp: start / end
	 * - Date & time: start / end
	 * - Date in file name: start / end
	 * - Date (Y-m-d): start / end
	 */
	const DT_TIMESTAMP_START = 0;
	const DT_TIMESTAMP_END = 1;
	const DT_DATETIME_START = 2;
	const DT_DATETIME_END = 3;
	const DT_FILENAME_START = 4;
	const DT_FILENAME_END = 5;
	const DT_DATE_START = 6;
	const DT_DATE_END = 7;

	/**
	 * Class constants for different ledger types
	 * - LDGR_NOOP means nothing
	 */
	const LDGR_ACT = 'activation';
	const LDGR_DEACT = 'deactivation';
	const LDGR_ACT_FEE = 'activationFee';
	const LDGR_DEACT_FEE = 'deactivationFee';
	const LDGR_PROMO_ACT = 'promoActivation';
	const LDGR_PROMO_DEACT = 'promoDeactivation';
	const LDGR_NDR = 'ndr';
	const LDGR_NDR_FEE = 'ndrFee';
	const LDGR_CBK = 'chargebackPartnerFee';
	const LDGR_NOOP = '';

	/**
	 * Class constants for different SQL statement types
	 * - Standard (activation, deactivation and NDR)
	 * - Chargeback
	 * - Redemption
	 */
	const SQL_STD = 0;
	const SQL_CBK = 1;
	const SQL_RED = 2;

	/**
	 * Class constants for injected array indexes for method _fetchAndWrite()
	 * in activationsDetail, activationsSummary, redemptionsDetail and
	 * redemptionsSummary classes
	 * - Report run >> SQL type
	 * - Report run >> ledger type
	 * - Report run >> row rendering method name
	 */
	const IDX_RUN_SQL = 0;
	const IDX_RUN_LEDGER = 1;
	const IDX_RUN_METHOD = 2;
	const IDX_RUN_COND = 4;

	/**
	 * Class constants for database master / slave switch
	 */
	const USE_MASTER = true;
	const USE_SLAVE = false;
	
	protected $_headers = array();
	protected $_handle = null;
	protected $_args;
	protected $_filename;
	protected $_partnerDir;
	protected $_defaultTimeZone;
	protected $_timeZone;
	protected $_reportTzDateTimes = array();
	protected $_serverTzDateTimes = array();
	protected $_actions = array(
		self::LDGR_ACT => 'A',
		self::LDGR_DEACT => 'D',
		self::LDGR_NDR => 'NDR'
	);
	protected $_isWritable = false;

	private $_renderStartTimestamp;
	private $_renderEndTimestamp;
	private $_renderedNumRows;

	abstract public function initialize();
	abstract public function finally();

	public function __construct(array $args) {
		$this->_args = $args;
		$this->_partnerDir = report::REPORT_DIR . \Env::getEnvName() . '/partners/';
		$this->_defaultTimeZone = date_default_timezone_get();
		$this->_timeZone = \settingModel::getSetting('reportTimeZone', 'reportTimeZone');
		$this->_calculateReportAndServerDateTimes();
		$this->_generateReportFilename();
		if ($this->_isWritable()) {
			$this->_handle = fopen($this->_filename, 'wb');
			$this->_renderedNumRows = 0;
			$this->_isWritable = true;
			if (isset($this->_headers)) {
				$this->_putCsvHeaders();
			}
		}
	}  //end __construct

	public function __destruct() {
		if ($this->_isWritable) {
			fclose($this->_handle);
		}
	}

	final public function run() {
		$this->initialize();
		$this->_run();
		$this->finally();
	}  //end run

	private function _run() {
		// Skip the run if report file is on local drive already. Plus the rerun
		// option does not exist
		if (!$this->_isWritable) {
			return;
		}
		// Get all the methods start with run from called class
		$methods = get_class_methods(get_called_class());
		if (!isset($methods)) {
			throw new \BadMethodCallException(
				'Cannot find any method from class [' . get_called_class() . '] to run report',
				self::EXCP_BAD_METHOD
			);
		}
		foreach ($methods as $m) {
			if (!preg_match('/^run[a-zA-Z0-9_]+/', $m)) {
				continue;
			}
			if (isset($this->_runMethodMaps[$m])) {
				echo '   > Grabbing ', $this->_runMethodMaps[$m], ' data ...', PHP_EOL;
			}
			$this->$m();
		}
	}  //end _run

	final protected function _isWritable() {
		return (!file_exists($this->_filename) || report::override());
	}

	final protected function _putCsv(array $fields) {
		if (count($fields) == 0) {
			return false;
		}
		$this->_renderedNumRows += 1;
		$editedFields = $fields;
		
		// Apply settings from $this->_headers
		$headerKeys = array_keys($this->_headers);
		for($colNum=0; $colNum<count($headerKeys); $colNum++) {
			$colSettings = $this->_headers[$headerKeys[$colNum]];
			if(array_key_exists('decimalPrecision', $colSettings)) {
				$editedFields[$colNum] = round($editedFields[$colNum], $colSettings['decimalPrecision']);
			}
			if(array_key_exists('padPrecision', $colSettings)) {
				$editedFields[$colNum] = number_format($editedFields[$colNum], $colSettings['padPrecision']);
			}
			if(array_key_exists('removeLeadingZeros', $colSettings) && $colSettings['removeLeadingZeros']) {
				$editedFields[$colNum] = ltrim($editedFields[$colNum], 0);
			}
			if(array_key_exists("quoteData", $colSettings) && $colSettings['quoteData']) {
				$editedFields[$colNum] = "\"$editedFields[$colNum]\"";
			}
		}
		$line = implode(',', $editedFields);
		$line .= "\n";
		return fwrite($this->_handle, $line);
	}
	
	final protected function _putCsvHeaders() {
		$headers = array_keys($this->_headers);
		if (count($headers) == 0) {
			return false;
		}
		$this->_renderedNumRows += 1;
		$line = implode(',', $headers);
		$line .= "\n";
		return fwrite($this->_handle, $line);
	}

	protected function _generateReportFilename() {
		$args =& $this->_args;
		$report = $args[report::IDX_REPORT_NAME];
		$format = $args[report::IDX_REPORT_FORMAT];
		$pattern = \settingModel::getSetting('reportGenerateFilename', $report);
		if (!is_null($pattern)) {
			$report = $pattern;
		}
		$file = $this->_partnerDir . $args[report::IDX_PARTNER] . '/' . $format . '/' . $report;
		if (!strstr($file, '|iteration|')) {
			$file .= '|iteration|';
		}
		//if it's a daily report, just show the day
		if (!strstr($file, '|startdate|')) {
			$file .= '|startdate|';
		}
		$this->_filename = str_replace(
			array(
				'|iteration|', '|startdate|', '|enddate|'
			), 
			array(
				ucfirst($args[report::IDX_REPORT_FREQ]),
				$this->_serverTzDateTimes[self::DT_FILENAME_START],
				$this->_serverTzDateTimes[self::DT_FILENAME_END]
			),
			$file
		) . '.' . $format;
	}  //end _generateReportFilename

	protected function _calculateReportAndServerDateTimes() {
		$start = $this->_args[report::IDX_RANGE_START];
		$end = $this->_args[report::IDX_RANGE_END];
		$format = \settingModel::getSetting(
			'reportGenerateDateFormat',
			$this->_args[report::IDX_REPORT_NAME]
		);
		if (is_null($format)) {
			$format = 'Ymd';
		}
		// Assign server local timestamps
		$this->_assignDesignatedTimezoneTimestamp($this->_serverTzDateTimes, $start, $end);
		// Report designated timezone timestamps
		// - Our times should be in our report time, not GMT
		date_default_timezone_set($this->_timeZone);
		$this->_assignDesignatedTimezoneTimestamp($this->_reportTzDateTimes, $start, $end);
		// - Set back to our default timezone
		date_default_timezone_set($this->_defaultTimeZone);
		// Convert server / designated timezone timestamps to server dates and times
		$this->_assignStartEndDateTimes($this->_serverTzDateTimes, $format);
		$this->_assignStartEndDateTimes($this->_reportTzDateTimes, $format);
	}  //end _calculateReportAndServerDateTimes

	private function _assignDesignatedTimezoneTimestamp(array & $dateTimes, $start, $end) {
		$dateTimes = array(
			self::DT_TIMESTAMP_START => strtotime($start . ' 00:00:00'),
			self::DT_TIMESTAMP_END => strtotime($end . ' 23:59:59')
		);
	}  //end _assignDesignateTimezoneTimestamp

	private function _assignStartEndDateTimes(array & $dateTimes, $format) {
		$dateTimes = array(
			self::DT_TIMESTAMP_START => $dateTimes[self::DT_TIMESTAMP_START],
			self::DT_TIMESTAMP_END => $dateTimes[self::DT_TIMESTAMP_END],
			self::DT_DATETIME_START => date('Y-m-d H:i:s', $dateTimes[self::DT_TIMESTAMP_START]),
			self::DT_DATETIME_END => date('Y-m-d H:i:s', $dateTimes[self::DT_TIMESTAMP_END]),
			self::DT_FILENAME_START => date($format, $dateTimes[self::DT_TIMESTAMP_START]),
			self::DT_FILENAME_END => date($format, $dateTimes[self::DT_TIMESTAMP_END]),
			self::DT_DATE_START => date('Y-m-d', $dateTimes[self::DT_TIMESTAMP_START]),
			self::DT_DATE_END => date('Y-m-d', $dateTimes[self::DT_TIMESTAMP_END])
		);
	}  //end _assignStartEndDateTimes

	protected function _date($format, $timestamp = null) {
		$timestamp = !isset($timestamp) ? time() : $timestamp;
		date_default_timezone_set($this->_timeZone);
		$date = date($format, $timestamp);
		date_default_timezone_set($this->_defaultTimeZone);

		return $date;
	}  //end _date

	protected function _sql($sqlType, $extraArg = self::LDGR_NOOP, $extraCond = '') {
		switch ($sqlType) {
			// Separate out the handling of redemption SQL since it is not summrized and fetched
			// from ledgers table like everyone else
			case self::SQL_RED:
				$sql = $this->_sqlRedemptionQuery();
				break;
			case self::SQL_STD:
			case self::SQL_CBK:
				$sql = $this->_sqlActivationQuery($sqlType, $extraArg, $extraCond);
				break;
			default:
				$sql = '';
				break;
		}

		return $sql;
	}  //end _sql

	private function _sqlRedemptionQuery() {
		return sprintf(
			$this->_sqls[self::SQL_RED],
			$this->_args[report::IDX_PARTNER],
			$this->_reportTzDateTimes[self::DT_DATETIME_START],
			$this->_reportTzDateTimes[self::DT_DATETIME_END]
		);
	}  //end _sqlRedemptionQuery

	private function _sqlActivationQuery($sqlType, $extraArg, $extraCond = '') {
		return sprintf(
			$this->_sqls[$sqlType],
			$extraArg,
			$this->_args[report::IDX_PARTNER],
			$this->_reportTzDateTimes[self::DT_DATETIME_START],
			$this->_reportTzDateTimes[self::DT_DATETIME_END],
			$extraCond
		);
	}  //end _sqlActivationQuery

	protected function _query($sql) {
		return \db::query($sql, $this->_useMasterOrSlaveDbConn());
	}  //end _query

	private function _useMasterOrSlaveDbConn() {
		switch (\Env::main()->getEnvName()) {
			case 'staging':
			case 'production':
				return self::USE_SLAVE;
			default:
				return self::USE_MASTER;
		}
	}  //end _useMasterDbConn

	protected function _renderStartOutput() {
		$this->_renderStartTimestamp = time();
		$calledClassName = trim(str_replace(__NAMESPACE__, '', get_called_class()), '\\');
		echo '[BEGIN]', PHP_EOL,
			' - Running ', strtoupper($this->_args[report::IDX_REPORT_FREQ]), ' report ', $calledClassName,
			' ', date('Y-m-d H:i:s', $this->_renderStartTimestamp), ' ...', PHP_EOL,
			' - Period between ', $this->_reportTzDateTimes[self::DT_DATETIME_START],
			' and ', $this->_reportTzDateTimes[self::DT_DATETIME_END], ' ...', PHP_EOL;
		if ($this->_isWritable) {
			echo ' - Collecting and writing data to ', $this->_filename, ' ...', PHP_EOL;
		} else {
			echo ' - Report file has already existed at ', $this->_filename, PHP_EOL,
				'   > No override option found. Will just send the existing file to remote server', PHP_EOL;
		}
	}  //end _renderStartOutput

	protected function _renderEndOutput() {
		$this->_renderEndTimestamp = time();
		$this->_renderedNumRows = $this->_renderedNumRows ?: 1;
		echo ' - Finished at ', date('Y-m-d H:i:s', $this->_renderEndTimestamp), ', took ',
			$this->_convertSecToDayhrMinSec($this->_renderEndTimestamp - $this->_renderStartTimestamp),
			' and rendered ', ($this->_renderedNumRows - 1), ' row(s)', PHP_EOL,
			'[DONE]', PHP_EOL, PHP_EOL;
	}  //end _renderEndOutput

	private function _convertSecToDayhrMinSec($sec) {
		list($day, $hour, $minute, $second) = explode(':', gmdate('d:H:i:s', $sec));
		$outputs = array();
		if (((int)$day - 1) > 0) {
			$outputs[] = ((int)$day - 1) . ' day(s)';
		}
		if ((int)$hour > 0) {
			$outputs[] = (int)$hour . ' hour(s)';
		}
		if ((int)$minute > 0) {
			$outputs[] = (int)$minute . ' minute(s)';
		}
		if ((int)$second > 0 || count($outputs) == 0) {
			$outputs[] = (int)$second . ' second(s)';
		}

		return implode(' ', $outputs);
	}  //end _convertSecToDayhrMinSec

}  //end skel
