<?php

/**
 * Physical delivery skel abstract class
 * 
 * @category giftingapp
 * @package libtool.physicalDelivery
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

namespace libtool\physicalDelivery;

use libtool\physicalDelivery;
use libtool\physicalDelivery\exception\pgpEncryptionException as PgpEncryptionException;
use libtool\physicalDelivery\exception\pgpDecryptionException as PgpDecryptionException;

abstract class skel {
	const EXCP_PGP_ENCRYPT = 160001;
	const EXCP_PGP_DECRYPT = 160002;

	const EXT_XML = 'xml';
	const EXT_PGP = 'xml.pgp';
	const EXT_GPG = 'xml.gpg';

	protected $_alreadyTornDown = false;
	protected $_reader = null;
	protected $_writer = null;

	abstract public function setup(\stdClass $settings);
	abstract public function teardown();

	public function __construct(\stdClass $settings) {
		if (count((array)$settings) > 0) {
			$this->setup($settings);
		}
	}  //end __construct

	public function __destruct() {
		if (!$this->_alreadyTornDown) {
			$this->teardown();
		}
	}  //end __destruct

	public function encrypt($uid, $xml, $pgp) {
		$cmd = "/usr/bin/gpg -o {$pgp} -e -r {$uid} {$xml} 2>&1";
		$return = shell_exec($cmd);
		// Return is null, no error, log as info
		if (is_null($return)) {
			\log::info("Generated PGP (GPG) encrypted file {$pgp}");
		}
		// Otherwise, log as error
		else {
			$message = "PGP (GPG) encryption error: {$return}";
			\log::error($message);
			throw new PgpEncryptionException($message, self::EXCP_PGP_ENCRYPT);
		}
	}  //end encrypt

	public function decrypt($pass, $pgp, $xml) {
		// GPG options:
		// -o            Write output to file
		// -d            Decrypt the file given on the command line
		// --batch       Use batch mode. Never ask, do not allow interactive commands.
		// --passphrase  PGP key passphrase
		$cmd = "/usr/bin/gpg -o {$xml} -d --batch --passphrase {$pass} {$pgp} 2>&1";
		$return = shell_exec($cmd);
		\log::info("Decrypted PGP (GPG) file {$pgp} and outputted to {$xml}");
		//$message = "PGP (GPG) decryption error: {$return}";
		//\log::error($message);
		//throw new PgpDecryptionException($message, self::EXCP_PGP_ENCRYPT);
	}  //end decrypt

	public function sendRequest($ftp, $local, $remote) {
		$ftp->put($local, $remote);
		$ftp->close();
	}  //end sendRequest

	public function receiveResponse($ftp, $folder, $regex = '') {
		$remoteFiles = $ftp->nlist();
		if (!empty($regex)) {
			$remoteFiles = preg_grep($regex, $remoteFiles);
		}
		foreach ($remoteFiles as $i => $file) {
			if (!$file) {
				unset($remoteFiles[$i]);
				continue;
			}
			$remote = './'.$file;
			$local = $folder . $file;
			$ftp->get($remote, $local);
			if (!file_exists($local) || @filesize($local) == 0) {
				unset($remoteFiles[$i]);
				continue;
			}
		}
		return $remoteFiles;
	}  //end receiveResponse

	protected function _reader() {
		if (!isset($this->_reader)) {
			$this->_reader = new \XMLReader();
		}

		return $this->_reader;
	}  //end _reader

	protected function _writer() {
		if (!isset($this->_writer)) {
			$this->_writer = new \XMLWriter();
		}

		return $this->_writer;
	}  //end _writer

	protected function _filepath($extension = self::EXT_XML) {
		$calledClass = get_called_class();
		$pathToFile = physicalDelivery::PATH . \Env::getEnvName() . '/'
			. $calledClass::DIR_NAME . '/' . strtolower(substr($extension, -3)) . '/';
		if (!file_exists($pathToFile)) {
			mkdir($pathToFile, 0755, true);
		}

		return $pathToFile;
	}  //end _filepath

}  //end skel
