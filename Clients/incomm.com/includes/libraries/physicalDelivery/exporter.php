<?php

/**
 * Physical delivery order request exporting class
 * 
 * @category giftingapp
 * @package libtool.physicalDelivery
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

namespace libtool\physicalDelivery;

use libtool\physicalDelivery;

class exporter extends skel {
	// Single tab
	const XML_INDENT_STRING = "\t";
	const IDX_ATTRIBUTE = '#attr#';
	const FMT_DT_FILENAME = 'YmdHis';
	const FMT_DT_TIMESTAMP = 'm/d/Y H:i:sA';
	const DIR_NAME = 'requests';

	protected $_settings = null;
	private $_messageTimestamp = null;
	private $_messageId = null;
	private $_filename = null;
	private $_hasEmptyRecordSet = false;

	public function __construct(\stdClass $settings) {
		parent::__construct($settings);
		$this->writeXmlElements();
	}  //emd __construct

	public function __destruct() {
		parent::__destruct();
	}  //end __destruct

	public function setup(\stdClass $settings) {
		$this->_settings = $settings;
		$this->_calculateAndAdjustDateTime();
		$this->_writer()->openUri($this->_filepath());
		$this->_setIndent();
		$this->_writer()->startDocument('1.0', 'utf-8');
		$this->_writer()->startElement('Request');
		$this->_writer()->writeAttribute('xmlns', 'http://PBP.BizTalk.CFS.Schemas.OrderCreateRequest');
	}  //end setup

	public function teardown() {
		$this->_writer()->endElement();
		$this->_writer()->flush();
		// Only generate PGP file and sent it to SFTP when the order list
		// is not empty (request file should contain at least one order)
		if (!$this->_hasEmptyRecordSet) {
			$this->encrypt();
			$this->sendRequest();
		}
		// Otherwise remove the XML file we just created
		else {
			unlink($this->_filepath(parent::EXT_XML));
		}
	}  //end teardown

	public function writeXmlElements() {
		$this->_writeMessageHeader();
		$this->_writeOrderList();
	}  //end writeXmlElements

	public function encrypt() {
		parent::encrypt(
			$this->_settings->pgpEncryptUid,  // Encryption key UID
			$this->_filepath(parent::EXT_XML),  // Source file
			$this->_filepath(parent::EXT_PGP)   // Output file
		);
	}  //end _encrypt

	public function sendRequest() {
		// Instance of SFTP (or FTP) class
		$ftp = $this->_settings->ftp;
		// Path to local source file
		$local = $this->_filepath(parent::EXT_PGP);
		// Path to remote destination file
		$remote = $ftp->fdir . '/' . basename($local);
		parent::sendRequest($ftp, $local, $remote);
	}  //end sendRequest

	private function _writeMessageHeader() {
		// Construct element array
		$elements = array(
			'MessageHeader' => array(
				self::IDX_ATTRIBUTE => array('xmlns' => ''),
				'MessageType' => 'OrderCreateRequest',
				'MessageVersion' => '1.0',
				'MessageID' => $this->_messageId(),
				'MessageTimeStamp' => $this->_messageTimestamp(),
				'From' => $this->_settings->organizationName,
				'To' => 'CFS',
				'Sender' => $this->_settings->organizationName,
				'SourceFileName' => '',
			)
		);
		// Write elements to xml
		$this->_writeArrayElementsToXml($elements);
	}  //end writeMessageHeader

	/**
	 * $elements = array(
	 *   'OrderList' => array(
	 *     self::IDX_ATTRIBUTE => array('xmlns' => ''),
	 *     'Order[0]' => array(
	 *       'PartnerID' => '',
	 *       'OrderID' => '',
	 *       'Shipping' => array(
	 *         'ServiceLevel' => 'FirstClass',
	 *         'ShippingCarrier' => 'USPS',
	 *         'ShipTo' => array(
	 *           'Name' => '',
	 *           'CompanyName' => '',
	 *           'Address1' => '',
	 *           'Address2' => '',
	 *           'City' => '',
	 *           'State' => '',
	 *           'ZipCode' => '',
	 *           'Country' => '',
	 *         )  //end ShipTo
	 *       ),  //end Shipping
	 *       'LineItemList' => array(
	 *         'LineItem[0]' => array(
	 *           'LineItemSequence' => '',
	 *           'LineItemID' => '',
	 *           'Quantity' => '1',
	 *           'PartList' => array(
	 *             'Part[0]' => array(
	 *               'PartType' => 'GiftCard',
	 *               'PartSKU' => '',
	 *               'Quantity' => '1',
	 *               'AttributeList' => array(
	 *                 'Attribute[0]' => array(
	 *                   self::IDX_ATTRIBUTE => array('Type' => 'FaceValue'),
	 *                   'Value' => ''
	 *                 )  //end Attribute[0]
	 *               )  //end AttributeList
	 *             ),  //end Part[0]
	 *             'Part[1]' => array(
	 *               'PartType' => 'Carrier',
	 *               'PartSKU' => '',
	 *               'Quantity' => '1',
	 *               'TextFieldList' => array(
	 *                 'TextField[0]' => array(
	 *                   self::IDX_ATTRIBUTE => array('Type' => 'Text1'),
	 *                   'Text' => ''
	 *                 ),  //end TextField[0]
	 *                 'TextField[1]' => array(
	 *                   self::IDX_ATTRIBUTE => array('Type' => 'Text2'),
	 *                   'Text' => ''
	 *                 ),  //end TextField[1]
	 *                 'TextField[2]' => array(
	 *                   self::IDX_ATTRIBUTE => array('Type' => 'Text3'),
	 *                   'Text' => ''
	 *                 )  //end TextField[2]
	 *               )  //end TextFieldList
	 *             )  //end Part[1]
	 *           )  //end PartList
	 *         )  //end LineItem[0]
	 *       )  //end LineItemList
	 *     )  //end Order[0]
	 *   )  //end OrderList
	 * );
	 */
	private function _writeOrderList() {
		// Construct element array
		$elements = array('OrderList' => array(self::IDX_ATTRIBUTE => array('xmlns' => '')));
		$rs =& $this->_fetchPhysicalDeliveryOrders();
		// If record set is empty then break and return
		if (mysql_num_rows($rs) == 0) {
			$this->_hasEmptyRecordSet = true;
			return false;
		}
		// Gathering data and construct order list collection
		$index = 0;
		while ($fields = mysql_fetch_assoc($rs)) {
			$gift = new \giftModel();
			$gift->assignValues($fields, true);
			if ($gift->unverifiedAmount || $gift->paidAmount) {
				// Load shipping details
				$shippingDetail = new \shippingDetailModel(array('giftId' => $gift->id));
				$shippingDetail->orderException = null;
				// Capture credit card payment
				try {
					$this->_captureCreditCardPayment($gift);
				} catch (\Exception $e) {
					// Log settlement failure to order exception, so that it
					// can be displayed in CST shipping detail section
					$shippingDetail->orderException = 'SttlementFailed';
					$shippingDetail->save();
					// Log error to mongodb
					\log::error("Failed to capture funds for gift#{$gift->id}", $e);
					continue;
				}
				// Save order file key
				$shippingDetail->orderFileKey = $this->_filename();
				$shippingDetail->save();
				// Construct array elements
				$elements['OrderList']["Order[{$index}]"] = array(
					'PartnerID' => $this->_settings->organizationName,
					'OrderID' => $shippingDetail->giftId,
					'Shipping' => array(),
					'LineItemList' => array('LineItem[0]' => array())
				);
				$this->_constructShippingSubset(
					$elements['OrderList']["Order[{$index}]"]['Shipping'],
					$gift, $shippingDetail
				);
				$this->_constructLineItemSubset(
					$elements['OrderList']["Order[{$index}]"]['LineItemList']['LineItem[0]'],
					$gift
				);
				\log::info("Sent physical delivery Gift {$gift->id} to fulfillment centre.");
				$index++;
			}
			$gift->addedToDeliveryQueue = true;
			$gift->save();
		}
		if (count($elements['OrderList']) > 1) {
			// Write elements to xml
			$this->_writeArrayElementsToXml($elements);
		} else {
			$this->_hasEmptyRecordSet = true;
		}
		return true;
	}  //end _writeOrderList

	private function & _fetchPhysicalDeliveryOrders() {
		$now = date('Y-m-d H:i:s');
		$sql = 'SELECT * FROM gifts 
				WHERE envName = "%s" AND physicalDelivery = 1
					AND delivered IS NULL AND deliveryDate <= "' . $now . '"
					AND addedToDeliveryQueue IS NULL AND paid IS NOT NULL
					AND (inScreeningQueue IS NULL OR inScreeningQueue = 0)';
		$rs = physicalDelivery::query(sprintf(
			$sql,
			\db::escape(\Env::getEnvName())
		));

		return $rs;
	}  //end _fetchPhysicalDeliveryOrders

	private function _captureCreditCardPayment(\giftModel $gift) {
		$messages = $gift->getMessages();
		foreach ($messages as $message) {
			// Skip the paid (captured) messages
			if ($message->isPaid()) {
				\log::info("Already captured gift#{$gift->id}, message#{$message->id}.");
				continue;
			}
			// Load transaction
			$transaction = new \transactionModel();
			$transaction->shoppingCartId = $message->getShoppingCart()->id;
			$transaction->load();
			// Skip the FAKE payment
			if (substr($transaction->authorizationId, 0, 4) == 'FAKE') {
				continue;
			}
			// Load credit card payment plugin and capture the funds
			$payment = new \payment();
			$payment->loadPlugin($transaction->paymentMethodId);
			$payment->plugin->capture($message, $transaction);
			// Update messages and transactions tables to complete the settlement
			$transaction->transactionComplete($payment->plugin->response);
			$message->getShoppingCart()->transactionComplete($transaction);
			$transaction->save();
			$message->getShoppingCart()->save();
			\log::info("Captured payment for gift#{$gift->id}, message#{$message->id}.");
		}
	}  //end _captureCreditCardPayment

	private function _constructShippingSubset(array & $shipping, \giftModel $gift, \shippingDetailModel $shippingDetail) {
		$shippingOption = new \shippingOptionModel($shippingDetail->shippingOptionId);
		$shipping = array(
			'ServiceLevel' => $shippingOption->serviceLevelKey,
			'ShippingCarrier' => $shippingOption->carrierKey,
			'ShipTo' => array(
				'Name' => $gift->recipientName,
				'CompanyName' => $shippingDetail->companyName,
				'Address1' => $shippingDetail->address,
				'Address2' => $shippingDetail->address2,
				'City' => $shippingDetail->city,
				'State' => $shippingDetail->state,
				'ZipCode' => $shippingDetail->zip,
				'Country' => isset(\constantsHelper::$countries[$shippingDetail->country])
					? \constantsHelper::$countries[$shippingDetail->country]
					: $shippingDetail->country,
			)
		);
	}  //end _constructShippingSubset

	private function _constructLineItemSubset(array & $lineItem, \giftModel $gift) {
		$product = new \productModel($gift->productId);
		$messages = $gift->getMessages();
		$sender = $message = '';
		if (count($messages) > 0) {
			// Single or self gift
			$sender = $messages[0]->fromName;
			$message = str_replace(array("\r", "\t", "\n"), '', $messages[0]->message);
			// Group gift
			if (isset($messages[1])) {
				$parts = array();
				for ($i = count($messages); $i--; ) {
					$parts[] = str_replace(array("\r", "\t", "\n"), '', $messages[$i]->message)
						. (($i > 0) ? "\r\n\r\n-" . $messages[$i]->fromName : '');
				}
				$message = implode("\r\n\r\n", $parts);
			}
		}
		$lineItem = array(
			'LineItemSequence' => '1',
			'LineItemID' => $gift->id,
			'Quantity' => '1',
			'PartList' => array(
				'Part[0]' => array(
					'PartType' => 'GiftCard',
					'PartSKU' => $product->upc,
					'Quantity' => '1',
					'AttributeList' => array(
						'Attribute[0]' => array(
							self::IDX_ATTRIBUTE => array('Type' => 'FaceValue'),
							'Value' => $gift->unverifiedAmount
						)  //end Attribute[0]
					)  //end AttributeList
				), //end Part[0]
				'Part[1]' => array(
					'PartType' => 'Carrier',
					'PartSKU' => $this->_templateName($gift),
					'Quantity' => '1',
					'TextFieldList' => array(
						'TextField[0]' => array(
							self::IDX_ATTRIBUTE => array('Type' => 'Text1'),
							'Text' => $sender
						), //end TextField[0]
						'TextField[1]' => array(
							self::IDX_ATTRIBUTE => array('Type' => 'Text2'),
							'Text' => $message
						), //end TextField[1]
						'TextField[2]' => array(
							self::IDX_ATTRIBUTE => array('Type' => 'Text3'),
							'Text' => ''
						)  //end TextField[2]
					)  //end TextFieldList
				)  //end Part[1]
			)  //end PartList
		);
	}  //end _constructLineItemSubset

	private function _writeArrayElementsToXml(array & $elements) {
		// name => children ( child_1, child_2, ..., child_n )
		$name = key($elements);
		$children = current($elements);
		// Start a new element
		$this->_writer()->startElement($name);
		// Iterate through the values of the current array elements
		foreach ($children as $childName => $childValue) {
			// Write element attribute
			if ($childName == self::IDX_ATTRIBUTE) {
				foreach ($childValue as $attrName => $attrValue) {
					$this->_writer()->writeAttribute($attrName, $attrValue);
				}
			}
			// Element contains children, recursively iterate
			elseif (is_array($childValue)) {
				$grandchildren = array(preg_replace('/\[\d+\]$/', '', $childName) => $childValue);
				$this->_writeArrayElementsToXml($grandchildren);
			}
			// Finally at the last dimension, write the element to xml
			else {
				// For the values with line breaks, use CDATA
				if (preg_match('/(?:(?:\r\n|\r|\n)\s*)/s', $childValue)) {
					$this->_writer()->startElement($childName);
					$this->_writer()->writeCData($childValue);
					$this->_writer()->endElement();
				}
				// Otherwise just use regular elements
				else {
					$this->_writer()->writeElement($childName, $childValue);
				}
			}
		}
		// End the element
		$this->_writer()->endElement();
	}  //end _writeArrayElementsToXml

	private function _setIndent() {
		if ($this->_settings->xmlIndentation == '1') {
			$this->_writer()->setIndent(true);
			$this->_writer()->setIndentString(self::XML_INDENT_STRING);
		}
	}  //end _setIndent

	private function _calculateAndAdjustDateTime() {
		$timestamp = time();
		$defaultTimeZone = date_default_timezone_get();
		date_default_timezone_set($this->_settings->timeZone);
		$this->_filename($timestamp);
		$this->_messageTimestamp($timestamp);
		$this->_messageId($timestamp);
		date_default_timezone_set($defaultTimeZone);
	}

	protected function _filepath($extension = parent::EXT_XML) {
		$pathToFile = parent::_filepath($extension);
		return ($pathToFile . $this->_filename() . '.' . $extension);
	}  //end _filepath

	private function _filename($timestamp = null) {
		if (!isset($this->_filename) || isset($timestamp)) {
			$timestamp = $timestamp ?: time();
			$this->_filename = $this->_settings->organizationName . '_Orders_'
				. date(self::FMT_DT_FILENAME, $timestamp);
		}
		return $this->_filename;
	}  //end _filename

	private function _messageTimestamp($timestamp = null) {
		if (!isset($this->_messageTimestamp) || isset($timestamp)) {
			$timestamp = $timestamp ?: time();
			$this->_messageTimestamp = date(self::FMT_DT_TIMESTAMP, $timestamp);
		}
		return $this->_messageTimestamp;
	}  //end _messageTimestamp

	private function _messageId($timestamp = null) {
		if (!isset($this->_messageId) || isset($timestamp)) {
			$timestamp = $timestamp ?: time();
			$this->_messageId = floor($timestamp / 86400);
		}
		return $this->_messageId;
	}  //end _messageId

	private function _templateName(\giftModel $gift) {
		return $gift->partner . '.docx';
	}  //end _templateName

}  //end export
