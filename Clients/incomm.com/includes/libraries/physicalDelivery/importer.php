<?php

/**
 * Physical delivery shipping confirmation importing class
 * 
 * @category giftingapp
 * @package libtool.physicalDelivery
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

namespace libtool\physicalDelivery;

use libtool\physicalDelivery;

class importer extends skel {
	const DIR_NAME = 'responses';

	/**
	 * Order line item fields
	 * array (
	 *   OrderID            => '3097518',
	 *   LineItemID         => '3097518',
	 *   CardNumber         => '6076629993298371',
	 *   FaceValue          => '161.88',
	 *   DateShipped        => '4/19/2012',
	 *   ShipServiceLevel   => 'FirstClass',
	 *   ShipCarrier        => 'USPS',
	 *   ShipTrackingNumber => ''
	 * )
	 * @var array
	 */
	public $orderLineItems = array();

	protected $_settings = null;

	private $_firstRead = true;
	private $_isReadable = null;
	private $_valueNodes = array(
        \XMLReader::TEXT => 1,
        \XMLReader::CDATA => 1
        );
	private $_whitespaceNodes = array(
        \XMLReader::WHITESPACE => 1,
        \XMLReader::SIGNIFICANT_WHITESPACE => 1
        );
	private $_documentTailNodes = array(
        \XMLReader::END_ELEMENT => 1
        );
	private $_regex = '/.*\.xml\.(pgp|gpg)$/i';
	private $_pgpResponses = array();
	private $_xmlResponses = array();

	public function __construct(\stdClass $settings) {
		parent::__construct($settings);
		$this->processXmlResponses();
	}  //emd __construct

	public function __destruct() {
		parent::__destruct();
	}  //end __destruct

	public function setup(\stdClass $settings) {
		$this->_settings = $settings;
		$this->_pgpResponses = $this->receiveResponse();
		\log::info('Received the following physical delivery shipping confirmations from SLC: '
			. implode(', ', $this->_pgpResponses));
		foreach ($this->_pgpResponses as $pgp) {
			$this->_xmlResponses[] = $this->decrypt($pgp);
		}
	}  //end setup

	public function teardown() {
		
	}  //end teardown

	public function decrypt($pgp) {
		$pathToXml = parent::_filepath(parent::EXT_XML);
		$xml = str_replace(parent::EXT_PGP, parent::EXT_XML, $pgp);
		if (!file_exists($pathToXml . $xml)) {
			parent::decrypt(
				$this->_settings->pgpDecryptPassphrase,  // GPG (PGP) decryption passphrase
				parent::_filepath(parent::EXT_PGP) . $pgp,  // Path to PGP file
				$pathToXml . $xml   // Path to output XML file
			);
		}
		\log::info('PGP file [' . $pgp . '] has been successfully decrypted'
			. ' to XML file [' . $xml . ']');
		return $xml;
	}  //end decrypt

	public function receiveResponse() {
		return parent::receiveResponse(
			$this->_settings->ftp,
			parent::_filepath(parent::EXT_PGP),
			$this->_regex
		);
	}  //end receiveResponse

	public function processXmlResponses() {
		foreach ($this->_xmlResponses as $xml) {
			$this->_reader()->open(parent::_filepath(parent::EXT_XML) . $xml);
			$this->_parseXmlElements();
			$this->_reader()->close();
			\log::info('XML physical delivery shipping confirmation [' . $xml . ']'
				. ' has been successfully processed.');
		}
	}  //end processXmlResponses

	private function _parseXmlElements() {
		while ($this->isReadable()) {
			// Parse XML and assign values to $this->orderLineItems as associative array
			$this->parse();
			// Skip this order if either of OrderId or LineItemID is not existing
			if (empty($this->orderLineItems['OrderId']) || empty($this->orderLineItems['LineItemID'])) {
				continue;
				$this->next();
			}
			// Update shipping details, gifts and send delivery notification email through worker
			$this->_updateShippingDetail();
			$this->_updateGift();
			$this->_workerDeliveryNotification();
			// Log info
			\log::info('XML physical delivery order [OrderId = ' . $this->orderLineItems['OrderId']
				. ' && LineItemID = ' . $this->orderLineItems['LineItemID'] . ']'
				. ' has been successfully processed. Also updated shippingDetails/gifts'
				. ' in database and sent delivery notification to sender');
			// Go to next order (permit to keep parsing the XML)
			$this->next();
		}
	}  //end _parseXmlElements

	private function _updateShippingDetail() {
		$item =& $this->orderLineItems;
		$shippingDetail = new \shippingDetailModel();
		$shippingDetail->giftId = $item['LineItemID'];
		if (!$shippingDetail->load()) {
			return false;
		}
		$shippingDetail->cardNumber = $item['CardNumber'];
		$shippingDetail->dateShipped = $this->_formattedShippedDate();
		$shippingDetail->trackingNumber = $item['ShipTrackingNumber'];
		$shippingDetail->save();
		return true;
	}  //end _updateShippingDetail

	private function _updateGift() {
		$gift = new \giftModel((int)$this->orderLineItems['LineItemID']);
		if (!is_null($gift->delivered)) { 
			return false;
		}
		$gift->delivered = date('Y-m-d H:i:s');
		$gift->save();
		return true;
	}  //end _updateGift

	private function _workerDeliveryNotification() {
		$worker = new \contributorDeliveryEmailWorker();
		$worker->send((int)$this->orderLineItems['LineItemID']);
	}  //end _workerDeliveryNotification

	private function _formattedShippedDate() {
		list($month, $day, $year) = explode('/', $this->orderLineItems['DateShipped']);
		return date('Y-m-d', strtotime($year . '-' . $month . '-' . $day));
	}  //end _formattedShippedDate

	public function isReadable() {
		if ($this->_firstRead || is_null($this->_isReadable)) {
			$this->_isReadable = $this->_reader()->read();
			$this->_firstRead = false;
		}
		if ($this->_isReadable && ($this->_reader()->depth <= 1)) {
			while (isset($this->_whitespaceNodes[$this->_reader()->nodeType])) {
				$this->_reader()->read();
			}
			if (isset($this->_documentTailNodes[$this->_reader()->nodeType])) {
				$this->_isReadable = false;
			}
		}
		return $this->_isReadable;
	}  //end isReadable

	public function parse() {
		$this->orderLineItems = array();
		$fieldName = '';
		while ($this->isReadable()) {
			// 0 ns0:ShippingConfirmation
			// |-- 1 OrderLineItem
			//   |-- 2 OrderID, LineItemID, CardNumber, FaceValue, DateShipped,
			//         ShipServiceLevel, ShipCarrier, ShipTrackingNumber
			if ($this->_reader()->depth == 2) {
				// Starting tag
				if ($this->_reader()->nodeType == \XMLReader::ELEMENT) {
					$fieldName = $this->_reader()->name;
					$this->orderLineItems[$fieldName] = '';
					$this->next();
					continue;
				}
				// Ending tag
				if ($this->_reader()->nodeType == \XMLReader::END_ELEMENT) {
					$fieldName = '';
					$this->next();
					continue;
				}
			}
			// Value
			if ($this->_reader()->depth == 3 && isset($this->_valueNodes[$this->_reader()->nodeType])
					&& $this->_reader()->hasValue)
			{
				$this->orderLineItems[$fieldName] = $this->_reader()->value;
			}
			$this->next();
		}
	}  //end parse

	public function next() {
		$this->_isReadable = null;
	}  //end next

}  //end import
