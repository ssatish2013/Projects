<?php

/**
 * Reporting tool - redemptions summary class
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

class redemptionsSummary extends skel {
	protected $_headers = array(
		'Merchant' => array(), 
		'Location' => array(), 
		'Merchant Country' => array(), 
		'Country' => array(), 
		'City' => array(), 
		'State' => array(), 
		'Zip' => array(), 
		'Terminal' => array(),
		'Vendor' => array(), 
		'Product' => array(), 
		'UPC' => array(), 
		'Denomination' => array(), 
		'Currency' => array(), 
		'Action' => array(), 
		'Total Sold' => array(), 
		'Total Amount' => array('decimalPrecision'=>0),
		'Total Discount Amount' => array(), 
		'Net Amount' => array(), 
		'Fee Rate' => array(), 
		'Fee Amount' => array(), 
		'Net Fee Sales' => array(),
		'Total Transaction Fees' => array(), 
		'Total Net Fee Sales' => array(),
	);
	protected $_sqls = array(
		parent::SQL_RED => 'SELECT i.giftId, g.partner, i.locationId, i.terminalId, i.activationAmount,
				p.upc, p.description, m.id AS messageId, i.activationMargin, sc.currency,
				COUNT(1) AS rowCount, SUM(i.activationAmount) AS rowSum,
				COUNT(DISTINCT i.pan) AS distRowCount
			FROM externalRedemptions er
			LEFT JOIN inventorys i ON er.inventoryId = i.id
			LEFT JOIN gifts g ON i.giftId = g.id
			LEFT JOIN messages m ON g.id = m.giftId
			LEFT JOIN shoppingCarts sc ON m.shoppingCartId = sc.id
			RIGHT JOIN transactions t ON sc.id = t.shoppingCartId
			LEFT JOIN products p ON g.productId = p.id
			WHERE g.partner = "%s" AND er.redemptionTime >= "%s" AND er.redemptionTime <= "%s"
				AND i.activationMargin IS NOT NULL
			GROUP BY i.locationId, p.upc, i.activationMargin',
		parent::SQL_STD => 'SELECT temp.*, COUNT(1) AS rowCount, SUM(temp.amount) AS rowSum
			FROM (
				SELECT l.type AS ledgerType, l.giftId, g.partner, i.locationId, i.terminalId,
					p.description, l.currency, i.activationMargin, i.activationAmount, p.upc,
					l.amount, MAX(l.timestamp) AS timestamp
				FROM ledgers l
				LEFT JOIN gifts g ON l.giftId = g.id
				LEFT JOIN reservations r ON g.id = r.giftId
				LEFT JOIN inventorys i ON r.inventoryId = i.id
				LEFT JOIN messages m ON g.id = m.giftId
				LEFT JOIN shoppingCarts sc ON m.shoppingCartId = sc.id
				RIGHT JOIN transactions t ON sc.id = t.shoppingCartId
				LEFT JOIN products p ON g.productId = p.id
				WHERE l.type = "%s" AND g.partner = "%s"
					AND i.activationMargin IS NOT NULL
				GROUP BY l.giftId
				HAVING timestamp >= "%s" AND timestamp <= "%s"
			) temp
			GROUP BY temp.locationId, temp.upc, temp.activationMargin',
		parent::SQL_CBK => 'SELECT g.id AS giftId, g.partner, i.locationId, i.terminalId, l.amount,
				l.currency, i.pan, p.description, i.activationMargin, p.upc,
				COUNT(1) AS rowCount, SUM(l.amount) AS rowSum
			FROM ledgers l
			LEFT JOIN shoppingCarts sc ON l.shoppingCartId = sc.id
			LEFT JOIN transactions t ON sc.id = t.shoppingCartId
			LEFT JOIN messages m ON m.shoppingCartId = sc.id
			LEFT JOIN gifts g ON g.id = m.giftId
			LEFT JOIN reservations r ON g.id = r.giftId
			LEFT JOIN inventorys i ON r.inventoryId = i.id
			LEFT JOIN products p ON g.productId = p.id
			WHERE l.type = "%s" AND sc.partner = "%s" AND l.timestamp >= "%s" AND l.timestamp <= "%s"
				AND i.activationMargin IS NOT NULL
			GROUP BY i.locationId, p.upc, i.activationMargin'
	);
	protected $_runMethodMaps = array(
		'runRedemptions' => 'redemption',
		'runNdrs' =>  'non-deactivated redemption',
		'runChargebacks' => 'chargeback'
	);
	private $_giftIds = array();

	public function __construct(array $args) {
		parent::__construct($args);
	}  //end __construct

	public function __destruct() {
		parent::__destruct();
	}  //end __destruct

	public function initialize() {
		$this->_renderStartOutput();
	}  //end initialize

	public function finally() {
		report::send($this->_args[report::IDX_PARTNER], $this->_filename);
		$this->_renderEndOutput();
	}  //end finally

	public function runRedemptions() {
		$this->_fetchAndWrite(array(
			parent::IDX_RUN_SQL => parent::SQL_RED,
			parent::IDX_RUN_LEDGER => parent::LDGR_NOOP,
			parent::IDX_RUN_METHOD => '_redRow'
		));
	}  //end runRedemptions

	public function runNdrs() {
		$this->_fetchAndWrite(array(
			parent::IDX_RUN_SQL => parent::SQL_STD,
			parent::IDX_RUN_LEDGER => parent::LDGR_NDR,
			parent::IDX_RUN_METHOD => '_stdRow'
		));
	}  //end runNdrs

	public function runChargebacks() {
		$this->_fetchAndWrite(array(
			parent::IDX_RUN_SQL => parent::SQL_CBK,
			parent::IDX_RUN_LEDGER => parent::LDGR_CBK,
			parent::IDX_RUN_METHOD => '_cbkRow'
		));
	}  //end runChargebacks

	private function _fetchAndWrite(array $args) {
		$sqlType = $args[parent::IDX_RUN_SQL];
		$ledgerType = $args[parent::IDX_RUN_LEDGER];
		$rowMethod = $args[parent::IDX_RUN_METHOD];
		$rs = parent::_query($this->_sql($sqlType, $ledgerType));
		while ($fields = mysql_fetch_assoc($rs)) {
			// - Skip duplicate giftId
			if (isset($this->_giftIds[$fields['giftId']])) {
				continue;
			}
			$this->_giftIds[$fields['giftId']] = $fields['giftId'];
			// - Render and write row
			$this->_putCsv($this->$rowMethod($fields));
		}
		$this->_giftIds = array();
		mysql_free_result($rs);
	}  //end _fetchAndWrite

	private function _redRow(array & $fields) {
		$this->_adjustRedSummaryFields($fields);
		$discountAmount = $this->_discountAmountRed($fields);
		$feeRate = (double)$fields['activationMargin'] / 100;
		$fees = ($fields['rowSum'] - $discountAmount) * $feeRate;
		$fees = number_format($fees, 2, '.', '');
		return array(
			'GroupCard',					//Merchant
			$fields['locationId'],			//Location
			'US',							//Merchant Country
			'N/A',							//Country
			'N/A',							//City
			'N/A',							//State
			'N/A',							//Zip
			$fields['terminalId'],			//Terminal
			$fields['partner'],				//Vendor
			$fields['description'],			//Product
			$fields['upc'],					//UPC
			$fields['activationAmount'],	//Denomination
			$fields['currency'],			//Currency
			'X',							//Action
			$fields['rowCount'],			//Numeber
			$fields['rowSum'],				//Total Amount
			$discountAmount,				//Discount Amount
			($fields['rowSum'] - $discountAmount),			//Net Amount
			$feeRate,										//Fee Rate
			(-1 * $fees),									//Fee Amount
			($fields['rowSum'] - $discountAmount - $fees),	//Net Fee Sale
			0,												//Transaction Fees
			($fields['rowSum'] - $discountAmount - $fees)	//Total Net Fee Sales
		);
	}  //end _redRow

	private function _stdRow(array & $fields) {
		$type = $fields['ledgerType'];
		$discountAmount = $this->_discountAmountStd($fields);
		$feeRate = (double)$fields['activationMargin'] / 100;
		$fees = number_format((-1 * $fields['rowSum'] * $feeRate), 2, '.', '');
		return array(
			'GroupCard',					//Merchant
			$fields['locationId'],			//Location
			'US',							//Merchant Country
			'N/A',							//Country
			'N/A',							//City
			'N/A',							//State
			'N/A',							//Zip
			$fields['terminalId'],			//Terminal
			$fields['partner'],				//Vendor
			$fields['description'],			//Product
			$fields['upc'],					//UPC
			$fields['activationAmount'],	//Denomination
			$fields['currency'],			//Currency
			$this->_actions[$type],			//Action
			$fields['rowCount'],			//Numeber
			$fields['rowSum'],				//Total Amount
			$discountAmount,				//Discount Amount
			(-1 * $fields['rowSum']),		//Net Amount
			$feeRate,						//Fee Rate
			(-1 * $fees),					//Fee Amount
			(-1 * ($fields['rowSum'] - $fees)),	//Net Fee Sale
			0,									//Transaction Fees
			(-1 * ($fields['rowSum'] - $fees))	//Total Net Fee Sales
		);
	}  //end _stdRow

	private function _cbkRow(array & $fields) {
		return array(
			'GroupCard',				//Merchant
			$fields['locationId'],		//Location
			'US',						//Merchant Country
			'N/A',						//Country
			'N/A',						//City
			'N/A',						//State
			'N/A',						//Zip
			$fields['terminalId'],		//Terminal
			$fields['partner'],			//Vendor
			$fields['description'],		//Product
			$fields['upc'],				//UPC
			0,							//Denomination
			$fields['currency'],		//Currency
			'C',						//Action
			$fields['rowCount'],		//Numeber
			0,							//Total Amount
			0,							//Discount Amount
			0,							//Net Amount
			0,							//Fee Rate
			0,							//Fee Amount
			0,							//Net Fee Sale
			(-1 * $fields['rowSum']),	//Transaction Fees
			(-1 * $fields['rowSum'])	//Total Net Fee Sales
		);
	}  //end _cbkRow

	private function _discountAmountRed(array & $fields) {
		// Discount amount
		$sql = 'SELECT SUM(discountAmount) FROM promoTransactions WHERE messageId = %d';
		$rs = parent::_query(sprintf($sql, $messageId));
		$discountAmount = 0;
		if (mysql_num_rows($rs) > 0) {
			list($discountAmount) = mysql_fetch_row($rs);
		}
		mysql_free_result($rs);

		return (double)$discountAmount;
	}  //end _discountAmount

	private function _discountAmountStd(array & $fields) {
		switch ($fields['ledgerType']) {
			case parent::LDGR_ACT: $sql = 'SELECT amount FROM promoActivations WHERE giftId = %d'; break;
			case parent::LDGR_DEACT: $sql = 'SELECT amount FROM promoDeactivations WHERE giftId = %d'; break;
			case parent::LDGR_NDR: return $this->_discountAmountRed($fields);
			default: return 0;
		}
		$rs = parent::_query(sprintf($sql, $fields['giftId']));
		$discountAmount = 0;
		if (mysql_num_rows($rs) > 0) {
			list($discountAmount) = mysql_fetch_row($rs);
		}
		mysql_free_result($rs);

		return ((double)$discountAmount * $fields['rowCount']);
	}  //end _discountAmount

	private function _adjustRedSummaryFields(array & $fields) {
		if ($fields['rowCount'] != $fields['distRowCount']) {
			$fields['rowCount'] = $fields['distRowCount'];
			$fields['rowSum'] = (double)$fields['activationAmount'] * (int)$fields['rowCount'];
		}
	}  //end _adjustSummaryFields

}  //end redemptionSummary
