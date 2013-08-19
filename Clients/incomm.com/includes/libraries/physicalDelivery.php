<?php

/**
 * Physical delivery main class
 * 
 * @category giftingapp
 * @package libtool
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

namespace libtool;

use libtool\cli;
use libtool\ftp;
use libtool\ftp\sftp;

class physicalDelivery {
	/**
	 * Class constants for database master / slave switch
	 */
	const USE_MASTER = true;
	const USE_SLAVE = false;

	const PATH = '/var/physical-delivery/';

	private static $_ftp = null;

	public static function run() {
		// Import - incomming
		self::runImporter();
		self::runErrorHandler();
		// Export - outgoing
		self::runExporter();
	}  //end run

	public static function __callStatic($name, array $arguments) {
		if (preg_match('/^run([a-zA-Z0-9_]+)$/', $name, $matches)) {
			$class = '\\' . __CLASS__ . '\\' . lcfirst($matches[1]);
			if (!class_exists($class)) {
				return false;
			}
			$method = '_get' . ucfirst($matches[1]) . 'Settings';
			if (!method_exists(__CLASS__, $method)) {
				return false;
			}
			self::_ftp();
			$instance = new $class(self::$method());
			unset($instance);
		}
	}  //end __callStatic

	private static function _getImporterSettings() {
		// Connect to SFTP and change remote directory
		self::$_ftp->fdir = \settingModel::getSetting('physicalDelivery', 'ftpImportingFolder');
		self::$_ftp->connect();
		self::$_ftp->chdir();
		// Create collection for settings
		$settings = new \stdClass();
		$settings->ftp = self::$_ftp;
		$settings->pgpDecryptPassphrase = \settingModel::getSetting('physicalDelivery', 'pgpDecryptPassphrase');
		return $settings;
	}  //end _importerSettings

	private static function _getExporterSettings() {
		// Connect to SFTP and change remote directory
		self::$_ftp->fdir = \settingModel::getSetting('physicalDelivery', 'ftpExportingFolder');
		self::$_ftp->connect();
		self::$_ftp->chdir();
		// Create collection for settings
		$settings = new \stdClass();
		$settings->ftp = self::$_ftp;
		$settings->xmlIndentation = \settingModel::getSetting('physicalDelivery', 'xmlIndentation');
		$settings->organizationName = \settingModel::getSetting('physicalDelivery', 'organizationName');
		$settings->timeZone = \settingModel::getSetting('reportTimeZone', 'reportTimeZone');
		$settings->pgpEncryptUid = \settingModel::getSetting('physicalDelivery', 'pgpEncryptUid');
		return $settings;
	}  //end _exporterSettings

	private static function _getErrorHandlerSettings() {
		// Change remote SFTP directory
		self::$_ftp->fdir = \settingModel::getSetting('physicalDelivery', 'ftpImportingFolder');
		self::$_ftp->connect();
		self::$_ftp->chdir();
		// Create collection for settings
		$settings = new \stdClass();
		$settings->ftp = self::$_ftp;
		$settings->pgpDecryptPassphrase = \settingModel::getSetting('physicalDelivery', 'pgpDecryptPassphrase');
		$settings->organizationName = \settingModel::getSetting('physicalDelivery', 'organizationName');
		$settings->emailErrorReport = \settingModel::getSetting('physicalDelivery', 'emailErrorReport');
		$settings->emailErrorReportAddress = \settingModel::getSetting('physicalDelivery', 'emailErrorReportAddress');
		return $settings;
	}  //end _errorHandlerSettings

	public static function query($sql) {
		return \db::query($sql, self::_useMasterOrSlaveDbConn());
	}  //end _query

	private static function _useMasterOrSlaveDbConn() {
		switch (\Env::getEnvName()) {
			case 'staging':
			case 'production':
				return self::USE_MASTER;
			default:
				return self::USE_MASTER;
		}
	}  //end _useMasterDbConn

	private static function _ftp() {
		$ftpConnection = \settingModel::getSetting('physicalDelivery', 'ftpConnection');
		$ftp = new sftp;
		extract($ftp->extractConnectionString($ftpConnection));
		$ftp->host = $host;
		$ftp->user = $user;
		$ftp->pass = $pass;
		$ftp->fdir = '/';
		$ftp->timeout = 8;
		// Remove remote file after it's downloaded from SFTP
		// By default deleteAfter is true
		//$ftp->deleteAfter = false;
		self::$_ftp = $ftp;
	}  //end _ftp

}  //end physicalDelivery
