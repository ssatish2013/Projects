<?php

namespace libtool;

/**
 * FTP connection tool kit
 * 
 * @category giftingapp
 * @package ftp
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.5.5
 */

class ftp {
	/**
	 * class constants
	 * error code
	 */
	const ERROR_FTP_CONNECT = 1001;
	const ERROR_FTP_LOGIN = 1002;
	const ERROR_FTP_GET = 1003;
	const ERROR_FTP_PUT = 1004;
	const ERROR_DB_QUERY = 2004;
	const ERROR_CONFIG_PARAMS = 3001;

	/**
	 * class constants
	 * default options
	 */
	const DEFAULT_OPTION_PROTOCOL = 'ftp';
	const DEFAULT_OPTION_PORT = 21;

	/**
	 * FTP resource link
	 * @var resource
	 * @access protected
	 */
	protected $_ftp = null;
	/**
	 * Class internal variable set
	 * for magic __get() and __set()
	 * @var array
	 * @access protected
	 */
	protected $_params = array();
	/**
	 * FTP options
	 * @var array
	 * @access protected
	 */
	protected $_options = array(
		'protocol'      => self::DEFAULT_OPTION_PROTOCOL,
		'port'          => self::DEFAULT_OPTION_PORT,
		'timeout'       => 30,
		'makeDirectory' => true,
		'deleteAfter'   => true
		);

	/**
	 * Class constructor
	 *
	 * @param void
	 * @return void
	 * @access public
	 */
	public function __construct() {
		;
	}

	/**
	 * Magic get method
	 *
	 * @param string $name
	 * @return mixed
	 * @access public
	 * @magic
	 */
	public function __get($name) {
		$value = null;
		switch ($name) {
			case 'host':
			case 'user':
			case 'pass':
			case 'fdir':
				if (isset($this->_params[$name])) {
					$value = $this->_params[$name];
				}
				break;
			case 'protocol':
			case 'port':
			case 'timeout':
			case 'makeDirectory':
			case 'deleteAfter':
				if (isset($this->_options[$name])) {
					$value = $this->_options[$name];
				}
				break;
			default:
				break;
		}

		return $value;
	}

	/**
	 * Magic set method
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 * @access public
	 * @magic
	 */
	public function __set($name, $value) {
		switch ($name) {
			case 'host':
			case 'user':
			case 'pass':
			case 'fdir':
				$this->_params[$name] = $value;
				break;
			case 'protocol':
			case 'port':
			case 'timeout':
			case 'makeDirectory':
			case 'deleteAfter':
				$this->_options[$name] = $value;
				break;
			default:
				break;
		}
	}

	/**
	 * Opens an FTP connection
	 *
	 * @param void
	 * @return resource (FTP stream)
	 * @access public
	 * @throws ftp::ERROR_CONFIG_PARAMS
	 * @throws ftp::ERROR_FTP_CONNECT
	 * @throws ftp::ERROR_FTP_LOGIN
	 */
	public function connect() {
		if (count($this->_params) == 0) {
			throw new \Exception('FTP connection parameters are undefined.', self::ERROR_CONFIG_PARAMS);
		}
		extract($this->_params);
		$port = empty($this->_options['port']) ? self::DEFAULT_OPTION_PORT : $this->_options['port'];
		$ftp = ($this->_options['protocol'] == 'ftps')
			? @ftp_ssl_connect($host, $port, $this->_options['timeout'])
			: @ftp_connect($host, $port, $this->_options['timeout']);
		if (!$ftp) {
			throw new \Exception('FTP connection has failed!', self::ERROR_FTP_CONNECT);
		}
		$login = @ftp_login($ftp, $user, $pass);
		if (!$login) {
			throw new \Exception('Could not log into FTP server.', self::ERROR_FTP_LOGIN);
		}
		if ($this->_options['protocol'] == 'ftps') {
			// http://www.elitehosts.com/blog/php-ftp-passive-ftp-server-behind-nat-nightmare/
			ftp_pasv($ftp, true);
		}
		$this->_ftp = $ftp;

		return $ftp;
	}

	/**
	 * Closes an FTP connection
	 *
	 * @param void
	 * @return void
	 * @access public
	 */
	public function close() {
		ftp_close($this->_ftp);
	}

	/**
	 * Changes the current directory on a FTP server
	 *
	 * @param void
	 * @return void
	 * @access public
	 * @throws ftp::ERROR_CONFIG_PARAMS
	 */
	public function chdir() {
		if (!isset($this->_params['fdir'])) {
			throw new \Exception('FTP connection parameters are undefined.', self::ERROR_CONFIG_PARAMS);
		}
		if (!empty($this->_params['fdir'])) {
			if (!@ftp_chdir($this->_ftp, $this->_params['fdir']) && $this->_options['makeDirectory']) {
				$this->_mkdir($this->_params['fdir']);
			}
		}
	}

	/**
	 * Creates a FTP directory
	 *
	 * @param string $path
	 * @param integer $mode
	 * @return boolean
	 * @access public
	 */
	protected function _mkdir($path, $mode = 0755) {
		$dirs = explode('/', $path);
		$path = '';
		$retval = true;
		foreach ($dirs as $d) {
			$path .= '/' . $d;
			if (!@ftp_chdir($this->_ftp, $path)) {
				@ftp_chdir($this->_ftp, '/');
				if (!@ftp_mkdir($this->_ftp, $path)) {
					$retval = false;
					break;
				} else {
					@ftp_chmod($this->_ftp, $mode, $path);
				}
				@ftp_chdir($this->_ftp, $path);
			}
		}
		return $retval;
	}

	/**
	 * Downloads a file from the FTP server
	 *
	 * @param string $remoteFile
	 * @param string $localFile
	 * @return void
	 * @access public
	 * @throws ftp::ERROR_FTP_GET
	 */
	public function get($remoteFile, $localFile) {
		if (!ftp_get($this->_ftp, $localFile, $remoteFile, FTP_BINARY)) {
			throw new \Exception(
				'There was a problem while downloading remote file [' . $remoteFile . '] to [' . $localFile . '].',
				self::ERROR_FTP_GET
				);
		}
		if ($this->_options['deleteAfter']) {
			ftp_delete($this->_ftp, $remoteFile);
		}
	}

	/**
	 * Uploads a file to the FTP server
	 *
	 * @param string $localFile
	 * @param string $remoteFile
	 * @return void
	 * @access public
	 * @throws ftp::ERROR_FTP_PUT
	 */
	public function put($localFile, $remoteFile) {
		if (!ftp_put($this->_ftp, $remoteFile, $localFile, FTP_BINARY)) {
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
		return ftp_nlist($this->_ftp, '.');
	}

	/**
	 * Get a detailed list of files in the given directory
	 *
	 * @param string $directory
	 * @return array
	 * @access public
	 */
	public function rawlist($directory = '.') {
		return ftp_rawlist($this->_ftp, $directory);
	}

	/**
	 * Scan FTP recursively and extract all matched files
	 *
	 * @global array $tempLists
	 * @param string $pattern
	 * @param string $directory
	 * @return array
	 * @access public
	 */
	public function recursiveExtractor($pattern = '/.*/', $directory = '.') {
		global $tempLists;

		if (!isset($tempLists)) {
			$tempLists = array();
		}
		$rawlist = $this->rawlist($directory);
		$isDirectory = false;
		$regexLinux = '/^.{10}\s*\d{1,}+\s*[\d|\w]*\s*[\d|\w]*\s*\d*\s[a-zA-Z]{3}\s*[0-9]{1,2}\s*[0-9]{2}:[0-9]{2}\s(\s?.*)$/';
		$regexWindows = '/^\d*\-\d*\-\d*\s*\d*:\d*\w*\s*(<DIR>)?\s*\d*\s*(\s?.*)$/';
		foreach ($rawlist as $item) {
			// The current implementation uses linux/unix/windows raw details
			//
			// This could also be done with php ftp_systype() function incorporated
			// with preg_match() function
			if (preg_match($regexLinux, $item, $matches)) {
				// For linux or unix os (ftp_systype($this->_ftp) == 'UNIX')
				//
				// Unix raw details for each file in the list may look like this:
				// -rw-r--r--    1 507      507         97778 Nov 21 22:35 4df15e95217f8.pdf
				//
				// And unix raw details for sub-directory:
				// drwxr-xr-x    2 507      507          4096 Nov 21 22:35 sub-directory
				$file = $matches[1];
				$isDirectory = ($item{0} == 'd');
			} elseif (preg_match($regexWindows, $item, $matches)) {
				// For windows os (ftp_systype($this->_ftp) == 'Windows_NT')
				//
				// Unix raw details for each file in the list may look like this:
				// 11-21-11  02:33PM                97778 4df15e95217f8.pdf
				//
				// And unix raw details for sub-directory:
				// 11-21-11  02:33PM       <DIR>          sub-directory
				$file = $matches[2];
				$isDirectory = ($matches[1] == '<DIR>');
			} else {
				// If the file or directory raw details don't match the regular expression for
				// either linux/unix or windows, then skip the current file or directory
				//
				// This may have to be changed later, because the current way ignoring the unmatched
				// file or directory is not being logged anywhere or is not sent to notify anybody.
				continue;
			}
			$path = $directory . '/' . $file;
			if ($isDirectory) {
				$this->recursiveExtractor($pattern, $path);
			} else {
				if (preg_match($pattern, $file)) {
					$tempLists[] = $path;
				}
			}
		}
		return $tempLists;
	}

	/**
	 * Extract parts and parameters from FTP connection string
	 *
	 * @param string $connectionString
	 * @return array
	 * @access public
	 */
	public function extractConnectionString($connectionString) {
		$params = array('protocol' => '', 'port' => '', 'host' => '', 'user' => '', 'pass' => '', 'fdir' => '', );
		if (preg_match('/^(ftp|ftps|sftp):\/\/([^:]+):(.+)@([^\/|:]+)(:([^\/]+))?\/(.+)?$/', $connectionString, $matches)) {
			// options
			$params['protocol'] = $matches[1];
			$params['port'] = $matches[6];
			// params
			$params['host'] = $matches[4];
			$params['user'] = $matches[2];
			$params['pass'] = $matches[3];
			$params['fdir'] = isset($matches[7]) ? $matches[7] : '';
		}
		return $params;
	}

	/**
	 * Construct FTP connection string
	 *
	 * @param void
	 * @return string
	 * @access public
	 * @throws ftp::ERROR_CONFIG_PARAMS
	 */
	public function constructConnectionString() {
		if (count($this->_params) == 0) {
			throw new \Exception('FTP connection parameters are undefined.', self::ERROR_CONFIG_PARAMS);
		}
		$params = $this->_params;
		$options = $this->_options;
		$connectionString = '';
		$allEmpty = true;
		foreach ($params as $p) {
			if ($p != '') {
				$allEmpty = false;
				break;
			}
		}
		if (!$allEmpty) {
			$connectionString = $options['protocol'] . '://' . $params['user'] . ':' . $params['pass']
				. '@' . $params['host'] . ':' . $options['port'] . '/' . trim($params['fdir'], '/');
		}

		return $connectionString;
	}

}
