<?php

/**
 * Main reporting class
 * 
 * @category giftingapp
 * @package libtool
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

// Package libtool
namespace libtool;

use libtool\cli\exception\incorrectCliEnvironmentException as IncorrectCliEnvironmentException;
use libtool\cli\exception\noValueSpecifiedException as NoValueSpecifiedException;
use libtool\ftp\exception\sftpBadConnectionException as SftpBadConnectionException;
use libtool\ftp\exception\nonExistentRemoteDirectoryException as NonExistentRemoteDirectoryException;
use libtool\ftp\exception\badDbSettingException as BadDbSettingException;
use \UnexpectedValueException;

class report {
	
	const REPORT_VERSION = "2.0";
	
	/**
	 * Class constants for exception code
	 * Exception code class 13000* (13000[0-9])
	 */
	const EXCP_BAD_SETTING = 130001;
	const EXCP_BAD_SFTP_CONN = 130002;

	/**
	 * Class constants for array/collection indexes
	 */
	const IDX_PARTNER = 'partner';
	const IDX_RANGE_START = 'rangeStart';
	const IDX_RANGE_END = 'rangeEnd';
	const IDX_REPORT_NAME = 'reportName';
	const IDX_REPORT_FREQ = 'reportFreq';
	const IDX_REPORT_FORMAT = 'reportFormat';

	/**
	 * Class constants for report frequency
	 */
	const REPORT_FREQ_DAILY = 'daily';
	const REPORT_FREQ_WEEKLY = 'weekly';
	const REPORT_FREQ_MONTHLY = 'monthly';

	/**
	 * Class constant for report stored directory
	 */
	const REPORT_DIR = '/var/reports/';

	/**
	 * Command line option holder, saves values from cli option parser
	 * @var array
	 * @access public
	 * @static
	 */
	public static $options = array();
	/**
	 * Report running partners collection
	 * @var array 
	 * @access public
	 * @static
	 */
	public static $partners = array();
	/**
	 * Command line option for reporting start date
	 * @var string
	 * @access public
	 * @static
	 */
	public static $startDate;
	/**
	 * Command line option for reporting end date
	 * @var string
	 * @access public
	 * @static
	 */
	public static $endDate;

	/**
	 * Different types of reports to be generated
	 * @var array
	 * @access private
	 * @static
	 */
	private static $_reportGenerateTypes = array();
	/**
	 * Report generating frequencies
	 * @var array 
	 * @access private
	 * @static
	 */
	private static $_reportGenerateFreqs = array();
	/**
	 * Remote SFTP server connections
	 * @var array 
	 * @access private
	 * @static
	 */
	private static $_remoteConns = array();

	public static function run() {
		try {
			// Parse CLI options
			cli::option(array(
				'start' => array('s:', 'start:'),
				'end' => array('e::', 'end::'),
				'partner' => array('p::', 'partner::'),
				'rerun' => array('r', 'rerun'),
				'sftpUpload' => array('u', 'upload'),
				'help' => array('h', 'help')
			))->parse();
			// Print help/usage if help option exists
			if (cli::option()->exists('help')) {
				self::usage();
			}
			// Run reports
			self::_run();
		} catch (NoValueSpecifiedException $e) {
			// Print the usage and exit if required option values are not found
			self::usage();
		} catch (IncorrectCliEnvironmentException $e) {
			self::_exceptionHandler($e);
		} catch (UnexpectedValueException $e) {
			self::_exceptionHandler($e);
		} catch (SftpBadConnectionException $e) {
			self::_printError($e);
		} catch (BadDbSettingException $e) {
			self::_printError($e);
		}
		// Destroy FTP/SFTP connection
		self::_destroyConnections();
	}  //end run

	public static function usage() {
		echo '
  usage:
  php run.php -s[start date]
  
  options:
    -s, --start=START        Start date string in strtotime format
    -e, --end[=END]          End date string in strtotime format (optional)
    -p, --partner[=PARTNER]  Partner to generate the reports for (optional)
    -r, --rerun              Force regeneration and overriding to old files (optional)
    -u, --upload             Upload reports to SFTP which its connection settings are
                             stored in DB (optional)
    -h, --help               Display this help and exit (optional)', PHP_EOL, PHP_EOL;
		exit(0);
	}  //end usage

	protected static function _run() {
		self::_assignCliOptions();
		$rangeEnd = strtotime(self::$endDate);
		// Cycle through all partners that want reports
		foreach(self::$partners as $partner) {
			// (Re-)Initialize the start range
			$rangeStart = strtotime(self::$startDate);
			// Force the partner for the time being
			\globals::forcePartnerRedirectLoaderForBatchScript($partner, null);
			// Go through all dates
			while($rangeStart <= $rangeEnd) {
				// Go through each report by name
				// - Get the report type for each partner
				foreach(self::_reportGenerateTypes($partner) as $reportName => $reportFormat) {
					//grab the frequency we need to generate it at
					self::_reportGenerateFreqs($reportName);
					$params = array(
						self::IDX_PARTNER => $partner,
						self::IDX_RANGE_START => $rangeStart,
						self::IDX_REPORT_NAME => $reportName,
						self::IDX_REPORT_FORMAT => $reportFormat
					);
					self::_daily($params);
					self::_weekly($params);
					self::_monthly($params);
				}
				$rangeStart += 86400; // 60s x 60m x 24h
			}
		}
	}  //end _run

	private static function _daily(array $args) {
		if (!self::_reportFreqExists($args[self::IDX_REPORT_NAME], self::REPORT_FREQ_DAILY)) {
			return false;
		}
		$args[self::IDX_REPORT_FREQ] = self::REPORT_FREQ_DAILY;
		$args[self::IDX_RANGE_START] = date('Y-m-d', $args[self::IDX_RANGE_START]);
		$args[self::IDX_RANGE_END] = $args[self::IDX_RANGE_START];
		// Run reports ...
		return self::_report($args);
	}  //end _daily

	private static function _weekly(array $args) {
		if (!self::_reportFreqExists($args[self::IDX_REPORT_NAME], self::REPORT_FREQ_WEEKLY)
			|| (date('l', $args[self::IDX_RANGE_START]) != 'Sunday')) {
			return false;
		}
		$args[self::IDX_REPORT_FREQ] = self::REPORT_FREQ_WEEKLY;
		$args[self::IDX_RANGE_END] = date('Y-m-d', $args[self::IDX_RANGE_START]);
		// 518400 = 6 days = 86400s * 6d
		$args[self::IDX_RANGE_START] = date('Y-m-d', $args[self::IDX_RANGE_START] - 518400);
		// Run reports ...
		return self::_report($args);
	}  //end _weekly

	private static function _monthly(array $args) {
		if (!self::_reportFreqExists($args[self::IDX_REPORT_NAME], self::REPORT_FREQ_MONTHLY)
			|| (date('j', $args[self::IDX_RANGE_START]) != date('t', $args[self::IDX_RANGE_START]))) {
			return false;
		}
		$args[self::IDX_REPORT_FREQ] = self::REPORT_FREQ_MONTHLY;
		$args[self::IDX_RANGE_END] = date('Y-m-d', $args[self::IDX_RANGE_START]);
		$args[self::IDX_RANGE_START] = date('Y-m-01', $args[self::IDX_RANGE_START]);
		// Run reports ...
		return self::_report($args);
	}  //end _monthly

	private static function _report(array $args) {
		$class = '\\' . __CLASS__ . '\\' . $args[self::IDX_REPORT_NAME];
		$report = new $class($args);
		$report->run();
		unset($report);
	}  //end _report

	private static function _assignCliOptions() {
		self::$options = cli::option()->getMappedOptions();
		self::$partners = isset(self::$options['partner'])
			? array(self::$options['partner'])
			: self::fetchPartnersList();
		self::$startDate = self::$options['start'];
		self::$endDate = isset(self::$options['end'])
			? self::$options['end']
			: self::$startDate;
	}  //end _assignCliOptions
	
	private static function fetchPartnersList() {
		$partnerActiveSettings = \settingModel::getPartnerSettings(null, 'reportActive');
		$activeReportPartners = array_keys($partnerActiveSettings, "1");
		$matchingVersionPartners = array_filter($activeReportPartners, array(__CLASS__, 'partnerHasMatchingReportVersion'));
		return $matchingVersionPartners;
	}
	
	private static function partnerHasMatchingReportVersion($partner) {
		$partnerReportVersions = \settingModel::getPartnerSettings($partner, 'reportGenerateVersion');
		return $partnerReportVersions['reportGenerateVersion'] == self::REPORT_VERSION;
	}

	private static function _reportGenerateTypes($partner) {
		if (!isset(self::$_reportGenerateTypes[$partner])) {
			self::$_reportGenerateTypes[$partner] =
				\settingModel::getPartnerSettings($partner, 'reportGenerateType');
		}
		return self::$_reportGenerateTypes[$partner];
	}  //end _reportGenerateTypes

	private static function _reportGenerateFreqs($reportName) {
		if (!isset(self::$_reportGenerateFreqs[$reportName])) {
			$reportGenerateFreq = \settingModel::getSetting('reportGenerateFreq', "{$reportName}Freq");
			$freqAsKeys = explode(',', $reportGenerateFreq);
			self::$_reportGenerateFreqs[$reportName] = array_fill_keys($freqAsKeys, 1);
		}

		return self::$_reportGenerateFreqs[$reportName];
	}  //end _reportGenerateFreqs

	private static function _reportFreqExists($reportName, $freq) {
		return isset(self::$_reportGenerateFreqs[$reportName][$freq]);
	}  //end _reportFreqExists

	private static function _exceptionHandler($exception) {
		echo PHP_EOL, 'Error: ', $exception->getCode(), ' - ', $exception->getMessage(),
			PHP_EOL, PHP_EOL, 'Stack trace:', PHP_EOL, $exception->getTraceAsString(), PHP_EOL;
	}  //end _exceptionHandler

	private static function _printError($exception) {
		echo ' - *** [ERROR]: ', $exception->getCode(), ' - ', $exception->getMessage(), PHP_EOL;
	}

	public static function send($partner, $file) {
		if (!cli::option()->exists('sftpUpload')) {
			return;
		}
		self::$_remoteConns = array();
		self::_establishConnections($partner);
		foreach (self::$_remoteConns as $conn) {
			echo ' - Sending report to remote server ', $conn->host, ' ...', PHP_EOL;
			$remote = $conn->fdir . basename($file);
			$conn->put($file, $remote);
		}
		
	}  //end send

	private static function _establishConnections($partner) {
		$settings = \settingModel::getPartnerSettings($partner, 'reportFtp');
		// If there's no ftp data set, return
		if (!isset($settings['domain'])) {
			throw new BadDbSettingException(
				'Bad database settings for SFTP/FTP',
				self::EXCP_BAD_SETTING
			);
		}
		echo ' - Establishing SFTP connection ...', PHP_EOL;
		$domains = explode('|', $settings['domain']);
		$usernames = explode('|', $settings['username']);
		$passwords = explode('|', $settings['password']);
		$remoteDirs = explode('|', $settings['remoteDir']);
		try {
			foreach ($domains as $i => $domain) {
				$sftp = new ftp\sftp;
				$sftp->host = trim($domain);
				$sftp->user = trim($usernames[$i]);
				$sftp->pass = trim($passwords[$i]);
				$sftp->fdir = trim($remoteDirs[$i]);
				$sftp->port = 22;
				$sftp->protocol = 'sftp';
				// Set connection timeout
				$sftp->timeout = 8;
				// connect to ftp
				if ($sftp->connect()) {
					// Successfully connected, change current directory to fdir
					$sftp->chdir();
					self::$_remoteConns[] = $sftp;
				} else {
					throw new SftpBadConnectionException(
						'Bad SFTP connection, cannot establish connection to server ' . $sftp->host,
						self::EXCP_BAD_SFTP_CONN
					);
				}
			}
		} catch (NonExistentRemoteDirectoryException $e) {
			self::_printError($e);
		}
	}  //end _establishConnections

	private static function _destroyConnections() {
		if (count(self::$_remoteConns) == 0) {
			return;
		}
		foreach (self::$_remoteConns as $conn) {
			if ($conn instanceof ftp\sftp) {
				$conn->close();
				unset($conn);
			}
		}
		self::$_remoteConns = array();
	}  //end _destroyConnections

	public static function override() {
		if (cli::is() && cli::option()->exists('rerun')) {
			return true;
		}

		return false;
	}  //end override

}  //end report

/*
SELECT temp.id AS actId, temp.type AS actType, temp.giftId AS actGiftId, temp.amount AS actAmount, temp.timestamp AS actTimestamp,
	ldgr.id AS deactFeeId, ldgr.type AS deactFeeType, ldgr.giftId AS deactFeeGiftId, ldgr.amount AS deactFeeAmount, ldgr.timestamp AS deactFeeTimestamp
FROM (
	SELECT id, type, giftId, amount, timestamp FROM ledgers
	WHERE type = "activation" AND amount > 0
) temp
LEFT JOIN ledgers ldgr ON temp.giftId = ldgr.giftId
WHERE ldgr.type = "deactivationFee";

UPDATE ledgers SET type = "deactivation" WHERE type = "activation" AND amount > 0;
*/
