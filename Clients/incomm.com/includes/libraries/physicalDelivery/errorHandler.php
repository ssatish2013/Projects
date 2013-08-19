<?php

/**
 * Physical delivery response error handling class
 * 
 * @category giftingapp
 * @package libtool.physicalDelivery
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

namespace libtool\physicalDelivery;

use libtool\physicalDelivery;

class errorHandler extends skel {
	const EXT_TXT = 'txt';
	const EXT_PGP = 'txt.pgp';
	const DIR_NAME = 'responses';

	private $_regex = '/.*\.txt\.(pgp|gpg)$/i';
	private $_pgpResponses = array();
	private $_txtResponses = array();
	private $_originalOrderFile = '';

	public function __construct(\stdClass $settings) {
		parent::__construct($settings);
		$this->processTxtResponses();
	}  //end __construct

	public function __destruct() {
		parent::__destruct();
	}  //end __destruct

	public function setup(\stdClass $settings) {
		$this->_settings = $settings;
		$this->_pgpResponses = $this->receiveResponse();
		foreach ($this->_pgpResponses as $pgp) {
			$this->_txtResponses[] = $this->decrypt($pgp);
		}
	}  //end setup

	public function teardown() {
		
	}  //end teardown

	public function decrypt($pgp) {
		$pathToTxt = parent::_filepath(self::EXT_TXT);
		$txt = str_replace(self::EXT_PGP, self::EXT_TXT, $pgp);
		if (!file_exists($pathToTxt . $txt)) {
			parent::decrypt(
				$this->_settings->pgpDecryptPassphrase,  // GPG (PGP) decryption passphrase
				parent::_filepath(self::EXT_PGP) . $pgp,  // Path to PGP file
				$pathToTxt . $txt   // Path to output TXT file
			);
		}
		return $txt;
	}  //end decrypt

	public function receiveResponse() {
		return parent::receiveResponse(
			$this->_settings->ftp,
			parent::_filepath(self::EXT_PGP),
			$this->_regex
		);
	}  //end receiveResponse

	public function processTxtResponses() {
		foreach ($this->_txtResponses as $txt) {
			$xml = simplexml_load_file(parent::_filepath(self::EXT_TXT) . $txt);
			$this->_originalOrderFile = $this->_extractOriginalOrderXmlFileName($txt);
			// No error, update shipping details and go to next txt file
			if ($xml->WorstError == 'NoError') {
				$this->_handleNoError();
				$this->_updateShippingDetail($xml);
				continue;
			}
			// Otherwise, process the error and sent error report through email
			$report = 'SLC has returned the following error(s) in alert file [' . $txt
				. ']:' . PHP_EOL . PHP_EOL;
			foreach ($xml->Error as $error) {
				$method = '_handle' . trim($error->Severity ?: 'UnknownError');
				// Process the error and return alert message
				$message = (method_exists($this, $method))
					? $this->$method($error)
					: $this->_handleUnknownError($error);
				// Update shipping details if error type is not BatchFail
				if ($error->Severity != 'BatchFail') {
					$this->_updateShippingDetail($error);
				}
				// Append error message to report message
				$report .= $message . PHP_EOL . PHP_EOL;
			}
			$this->_emailErrorReport($report);
		}
	}  //end processTxtResponses

	private function _handleNoError() {
		$message = '[NO_ERROR] Physical delivery order request [' . $this->_originalOrderFile
			. '] was successfully processed at fulfillment centre. Confirmation received at '
			. $this->_utcCurrentTimestamp() . PHP_EOL;
		\log::info($message);
	}  //end _handleNoError

	private function _handleRecoverable(\SimpleXMLElement $xml) {
		$message = '[RECOVERABLE] Physical delivery order request [' . $this->_originalOrderFile
			. '] contains the following recoverable issue received from fulfillment centre (@ '
			. $this->_utcCurrentTimestamp() . '):' . PHP_EOL . $this->_errorToString($xml);
		\log::warn($message);
		return $message;
	}  //end _handleRecoverable

	private function _handleRecordFail(\SimpleXMLElement $xml) {
		$message = '[RECORD_FAIL] Physical delivery order request [' . $this->_originalOrderFile
			. '] contains the following record fail error received from fulfillment centre (@ '
			. $this->_utcCurrentTimestamp() . '):' . PHP_EOL . $this->_errorToString($xml);
		\log::error($message);
		return $message;
	}  //end _handleRecordFail

	private function _handleBatchFail(\SimpleXMLElement $xml) {
		$message = '[BATCH_FAIL] Physical delivery order request [' . $this->_originalOrderFile
			. '] was failed to process at fulfillment centre with the following error (@ '
			. $this->_utcCurrentTimestamp() . '):' . PHP_EOL . $this->_errorToString($xml);
		\log::error($message);
		return $message;
	}  //end _handleBatchFail

	private function _handleUnknownError(\SimpleXMLElement $xml) {
		$message = '[UNKNOWN_ERROR] Physical delivery order request [' . $this->_originalOrderFile
			. '] contains the following UNKNOWN error received from fulfillment centre (@ '
			. $this->_utcCurrentTimestamp() . '):' . PHP_EOL . $this->_errorToString($xml);
		\log::warn($message);
		return $message;
	}  //end _handleUnknownError

	private function _utcCurrentTimestamp() {
		return date('Y-m-d \T H:i \U\T\C');
	}  //end _utcCurrentTimestamp

	private function _errorToString(\SimpleXMLElement $xml) {
		$error = '- Severity: ' . $xml->Severity . PHP_EOL . '- Order ID: ' . $xml->OrderId
			. PHP_EOL . '- Details:' . PHP_EOL . $xml->Details . PHP_EOL . '- Source:' . PHP_EOL
			. var_export($xml->Source, true) . PHP_EOL;
		return $error;
	}  //end _errorToString

	private function _updateShippingDetail(\SimpleXMLElement $xml) {
		$orderFileKey = trim(str_replace(parent::EXT_XML, '', $this->_originalOrderFile), '.');
		$details = (!empty($xml->OrderId))
			? \shippingDetailModel::loadAll(array('giftId' => $xml->OrderId))
			: \shippingDetailModel::loadAll(array('orderFileKey' => $orderFileKey));
		foreach ($details as $d) {
			$d->orderException = $xml->Severity ?: $xml->WorstError;
			$d->save();
		}
		return true;
	}  //end _updateShippingDetail

	private function _extractOriginalOrderXmlFileName($txtFileName) {
		$xmlFileName = '';
		$pattern = '/^(' . $this->_settings->organizationName . '_Orders_[0-9]{8,14})/i';
		if (preg_match($pattern, $txtFileName, $matches)) {
			$xmlFileName = $matches[1] . '.' . parent::EXT_XML;
		}
		return $xmlFileName;
	}  //end _extractOriginalOrderXmlFileName

	private function _emailErrorReport($message) {
		if ($this->_settings->emailErrorReport != '1') {
			return false;
		}
		$mailer = new \mailer();
		$mailer->quickSend(
			$message,
			$this->_settings->emailErrorReportAddress,
			'Physical Delivery Error!'
		);
		return true;
	}  //end _emailErrorReport

}  //end error
