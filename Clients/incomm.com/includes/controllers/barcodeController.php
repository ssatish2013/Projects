<?php
class barcodeController {

	public static $defaultMethod = 'index';
	private $_recipientGuid;
	private $_activate = true;
	private $_pinInBarcode;

	public function __construct() {
		$this->_recipientGuid = request::url('recipientGuid');
		$this->_pinInBarcode = (request::url('pinInBarcode') == '1');
	}

	public function index() {
		throw new exception('Invalid Access');
	}

	public function display() {
		barcodeHelper::displayByRecipientGuid(
			$this->_recipientGuid,
			$this->_activate,
			$this->_pinInBarcode
		);
	}

	public function voucher() {
		$this->_activate = false;
		barcodeHelper::displayByRecipientGuid(
			$this->_recipientGuid,
			$this->_activate,
			$this->_pinInBarcode
		);
	}

}