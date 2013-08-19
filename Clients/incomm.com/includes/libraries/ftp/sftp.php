<?php

namespace libtool\ftp;

use libtool\ftp\exception\nonExistentRemoteDirectoryException as NonExistentRemoteDirectoryException;

/**
 * SFTP connection tool kit
 *
 * This class contains the same name methods which override parent class (ftp) methods
 * to function as SFTP connection
 *
 * @category giftingapp
 * @package sftp
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.6.0
 * @uses libssh2.so, PECL ssh2
 * @see ftp
 */

class sftp extends \libtool\ftp {
	/**
	 * class constants
	 * error codes
	 */
	const ERROR_SSH2_CONNECT = 1101;
	const ERROR_SSH2_LOGIN = 1102;
	const ERROR_SSH2_NONSUPPORT = 1103;
	const ERROR_SFTP_CONNECT = 1104;
	const ERROR_SFTP_GET = 1105;
	const ERROR_SFTP_PUT = 1106;
	const ERROR_SFTP_DIR = 1107;

	/**
	 * class constants
	 * default options
	 */
	const DEFAULT_OPTION_PROTOCOL = 'sftp';
	const DEFAULT_OPTION_PORT = 22;

	/**
	 * SFTP resource link
	 * @var resource
	 * @access protected
	 */
	protected $_sftp = null;
	/**
	 * SSH2 connection resource link
	 * @var resource
	 * @access protected
	 */
	protected $_connection = null;

	/**
	 * Class constructor
	 *
	 * @param void
	 * @return void
	 * @access public
	 */
	public function __construct() {
		$this->_options['protocol'] = self::DEFAULT_OPTION_PROTOCOL;
		$this->_options['port'] = self::DEFAULT_OPTION_PORT;
	}

	/**
	 * Initialize SFTP subsystem
	 *
	 * @param void
	 * @return resource (to be used with ssh2.sftp:// fopen wrapper)
	 * @access public
	 * @throws sftp::ERROR_CONFIG_PARAMS
	 * @throws sftp::ERROR_SSH2_CONNECT
	 * @throws sftp::ERROR_SSH2_LOGIN
	 * @throws sftp::ERROR_SFTP_CONNECT
	 */
	public function connect() {
		if (count($this->_params) == 0) {
			throw new \Exception('SFTP connection parameters are undefined.', self::ERROR_CONFIG_PARAMS);
		}
		extract($this->_params);
		if (!function_exists('ssh2_connect')) {
			throw new \Exception('SSH2 function not supported.', self::ERROR_SSH2_NONSUPPORT);
		}
		$port = empty($this->_options['port']) ? self::DEFAULT_OPTION_PORT : $this->_options['port'];
		$connection = ssh2_connect($host, $port);
		if (!$connection) {
			throw new \Exception('SSH connection has failed!', self::ERROR_SSH2_CONNECT);
		}
		$login = @ssh2_auth_password($connection, $user, $pass);
		if (!$login) {
			throw new \Exception('Could not log into SSH server.', self::ERROR_SSH2_LOGIN);
		}
		$sftp = ssh2_sftp($connection);
		if (!$sftp) {
			throw new \Exception('SFTP connection has failed!', self::ERROR_SFTP_CONNECT);
		}
		$this->_connection = $connection;
		$this->_sftp = $sftp;

		return $sftp;
	}

	/**
	 * Destruct SFTP connection
	 *
	 * @param void
	 * @return void
	 * @access public
	 */
	public function close() {
		;
	}

	/**
	 * Check if remote SFTP directory exists
	 *
	 * @param void
	 * @return void
	 * @access public
	 * @throws libtool\ftp\exception\nonExistentRemoteDirectoryException
	 */
	public function chdir() {
		$directory = 'ssh2.sftp://' . $this->_sftp . '/' . trim($this->_params['fdir'], '/') . '/';
		$handle = @opendir($directory);
		if (!$handle) {
			throw new NonExistentRemoteDirectoryException(
				'SFTP directory does not exist on server ' . $this->_params['host'] . '.',
				self::ERROR_SFTP_DIR
			);
		}
		closedir($handle);
	}

	/**
	 * Downloads a file from the SFTP server
	 *
	 * @param string $remoteFile
	 * @param string $localFile
	 * @return void
	 * @access public
	 * @throws sftp::ERROR_FTP_GET
	 */
	public function get($remoteFile, $localFile) {
		$remotePath = 'ssh2.sftp://' . $this->_sftp . '/' . trim($this->_params['fdir'], '/') . '/' . $remoteFile;
		$remote = fopen($remotePath, 'rb');
		if (!$remote) {
			throw new \Exception('Could not open remote file: ' . $remoteFile, self::ERROR_FTP_GET);
		}
		$local = fopen($localFile, 'w');
		if ($local === false) {
			throw new \Exception('Could not open local file: ' . $localFile, self::ERROR_FTP_GET);
		}
		$res = true;
		$read = 0;
		$length = filesize($remotePath);
		while ($read < $length && ($buffer = fread($remote, $length - $read))) {
			$read += strlen($buffer);
			$res = fwrite($local, $buffer);
		}
		fclose($remote);
		fclose($local);
		if ($res === false) {
			throw new \Exception(
				'There was a problem while downloading remote file [' . $remoteFile . '] to [' . $localFile . '].',
				self::ERROR_FTP_GET
				);
		}
		if ($this->_options['deleteAfter']) {
			unlink($remotePath);
		}
	}

	/**
	 * Uploads a file to the SFTP server
	 *
	 * @param string $localFile
	 * @param string $remoteFile
	 * @return void
	 * @access public
	 * @throws sftp::ERROR_FTP_PUT
	 */
	public function put($localFile, $remoteFile) {
		$remotePath = 'ssh2.sftp://' . $this->_sftp . '/' . ltrim($remoteFile, '/');
		$remote = fopen($remotePath, 'w');
		if (!$remote) {
			throw new \Exception('Could not open remote file: ' . $remoteFile, self::ERROR_FTP_PUT);
		}
		$local = fopen($localFile, 'rb');
		if ($local === false) {
			throw new \Exception('Could not open local file: ' . $localFile, self::ERROR_FTP_PUT);
		}
		$res = true;
		while (!feof($local) && ($res !== false)) {
			$buffer = fread($local, 4096);
			$res = fwrite($remote, $buffer);
		}
		fclose($remote);
		fclose($local);
		if ($res === false) {
			throw new \Exception(
				'There was a problem while uploading local file [' . $localFile . '] to remote location [' . $remoteFile . ']',
				self::ERROR_FTP_PUT
				);
		}
	}

	/**
	 * Get a list of files in the given directory
	 *
	 * @param void
	 * @return array
	 * @access public
	 */
	public function nlist() {
		$tempLists = array();
		$directory = 'ssh2.sftp://' . $this->_sftp . '/' . trim($this->_params['fdir'], '/');
		$handle = opendir($directory);
		while (false !== ($file = readdir($handle))) {
			$path = $directory . '/' . $file;
			if ($file != '.' && $file != '..' && !is_dir($path)) {
				$tempLists[] = $file;
			}
		}
		closedir($handle);

		return $tempLists;
	}

	/**
	 * Scan SFTP recursively and extract all matched files
	 *
	 * @global array $tempLists
	 * @param string $pattern
	 * @param string $directory
	 * @return array
	 * @access public
	 */
	public function recursiveExtractor($pattern = '/.*/', $directory = null) {
		global $tempLists, $sftpRoot;

		if (!isset($tempLists)) {
			$tempLists = array();
			$sftpRoot = 'ssh2.sftp://' . $this->_sftp . '/' . trim($this->_params['fdir'], '/');
		}
		$directory = is_null($directory) ? $sftpRoot : $directory;
		$handle = opendir($directory);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path = $directory . '/' . $file;
				if (is_dir($path)) {
					$this->recursiveExtractor($pattern, $path);
				} else {
					if (preg_match($pattern, $file)) {
						$tempLists[] = str_replace($sftpRoot . '/', '', $path);
					}
				}
			}
		}
		closedir($handle);

		return $tempLists;
	}

}
