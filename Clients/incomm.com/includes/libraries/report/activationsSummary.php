<?php

/**
 * Reporting tool - activations summary class
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

class activationsSummary extends skel {
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
		'Total Amount' => array(),
		'Total Discount Amount' => array(), 
		'Net Amount' => array(), 
		'Fee Rate' => array(), 
		'Fee Amount' => array('decimalPrecision'=>2), 
		'Net Fee Sales' => array(),
		'Total Transaction Fees' => array(), 
		'Total Net Fee Sales' => array(),
	);
	protected $_sqls = array(
		parent::SQL_STD => 'SELECT temp.*, COUNT(1) AS rowCount, SUM(temp.amount) AS rowSum,
				COUNT(DISTINCT temp.pan) AS distRowCount
			FROM (
				SELECT l.type AS ledgerType, l.giftId, g.partner, i.terminalId, l.currency,
					p.description, l.amount, i.locationId, i.activationMargin, p.upc, i.pan,
					MAX(l.timestamp) AS timestamp
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
		parent::SQL_CBK => 'SELECT g.id AS giftId, g.partner, i.terminalId, l.amount, l.currency,
				p.description, i.locationId, i.activationMargin, p.upc,
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
			GROUP BY i.locationId, p.upc, i.activationMargin',
	);
	protected $_runMethodMaps = array(
		'runActivations' => 'activation',
		'runDeactivations' => 'deactivation',
		'runNdrs' => 'non-deactivated redemption',
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

	public function runActivations() {
		$this->_fetchAndWrite(array(
			parent::IDX_RUN_SQL => parent::SQL_STD,
			parent::IDX_RUN_LEDGER => parent::LDGR_ACT,
			parent::IDX_RUN_METHOD => '_stdRow',
			parent::IDX_RUN_COND => 'AND l.amount < 0'
		));
	}  //end runActivations

	public function runDeactivations() {
		$this->_fetchAndWrite(array(
			parent::IDX_RUN_SQL => parent::SQL_STD,
			parent::IDX_RUN_LEDGER => parent::LDGR_DEACT,
			parent::IDX_RUN_METHOD => '_stdRow'
		));
	}  //end runDeactivations

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
		$extraCond = array_key_exists(parent::IDX_RUN_COND, $args) ? $args[parent::IDX_RUN_COND] : '';
		$rs = parent::_query($this->_sql($sqlType, $ledgerType, $extraCond));
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

	private function _stdRow(array & $fields) {
		// Fixes incorrect row count and sum caused by duplicate ledger entries
		// activation and activationFee
		$this->_adjustStdSummaryFields($fields);
		$type = $fields['ledgerType'];
		$amount = (double) $fields['amount'];
		$discountAmount = $this->_discountAmount($fields);
		$fees = $this->_calculateFees($fields);
		return array(
			'GroupCard',				//Merchant
			$fields['locationId'],		//Location
			'US',						//Merchant Country
			'N/A',						//Country
			'N/A',						//city
			'N/A',						//state
			'N/A',						//zip
			$fields['terminalId'],		//Terminal
			$fields['partner'],			//Vendor
			$fields['description'],		//Product
			$fields['upc'],				//UPC
			(($amount > 0) ? -1*$amount : $amount),				//Denomination
			$fields['currency'],								//Currency
			$this->_actions[$type],								//action
			$fields['rowCount'],								//Number
			(-1 * $fields['rowSum'] + $discountAmount),			//Total Amount
			$discountAmount,									//Discount Amount
			(-1 * $fields['rowSum']),							//Net Amount
			((double)$fields['activationMargin'] / 100),		//Fee Rate
			(($type == parent::LDGR_DEACT) ? -1*$fees : $fees),	//Fee Amount
			(-1 * $fields['rowSum'] + $fees),					//Net Fee Sale
			0,													//Transaction Fees
			(-1 * $fields['rowSum'] + $fees)					//Total Net Fee Sales
		);
	}  //end _stdRow

	private function _cbkRow(array & $fields) {
		$amount = (double)$fields['amount'];
		return array(
			'GroupCard',				//Merchant
			$fields['locationId'],		//Location
			'US',						//Merchant Country
			'N/A',						//Country
			'N/A',						//city
			'N/A',						//state
			'N/A',						//zip
			$fields['terminalId'],		//Terminal
			$fields['partner'],			//Vendor
			$fields['description'],		//Product
			$fields['upc'],				//UPC
			(($amount > 0) ? -1*$amount : $amount),	//Denomination
			$fields['currency'],		//Currency
			'C',						//Action
			$fields['rowCount'],		//Number
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

	private function _discountAmount(array & $fields) {
		$sql = 'SELECT amount FROM ledgers WHERE type = "%s" AND giftId = %d';
		switch ($fields['ledgerType']) {
			case parent::LDGR_ACT: $sql = sprintf($sql, parent::LDGR_PROMO_ACT, $fields['giftId']); break;
			case parent::LDGR_DEACT: $sql = sprintf($sql, parent::LDGR_PROMO_DEACT, $fields['giftId']); break;
			default: return 0;
		}
		$rs = parent::_query($sql);
		$discountAmount = 0;
		if (mysql_num_rows($rs) > 0) {
			list($discountAmount) = mysql_fetch_row($rs);
		}
		mysql_free_result($rs);

		return ((double)$discountAmount * $fields['rowCount']);
	}  //end _discountAmount

	private function _calculateFees(array & $fields) {
		$ledgerFeeType = array(
			parent::LDGR_ACT => parent::LDGR_ACT_FEE,
			parent::LDGR_DEACT => parent::LDGR_DEACT_FEE,
			parent::LDGR_NDR => parent::LDGR_NDR_FEE
		);
		$sql = 'SELECT amount FROM ledgers WHERE type = "%s" AND giftId = "%d"';
		$rs = parent::_query(sprintf(
			$sql,
			$ledgerFeeType[$fields['ledgerType']],
			$fields['giftId']
		));
		list($fee) = mysql_fetch_row($rs);

		return ((double)$fee * $fields['rowCount']);
	}  //end _calculateFees

	private function _adjustStdSummaryFields(array & $fields) {
		if ($fields['ledgerType'] == parent::LDGR_ACT
			&& $fields['rowCount'] != $fields['distRowCount'])
		{
			$fields['rowCount'] = $fields['distRowCount'];
			$fields['rowSum'] = (double)$fields['amount'] * (int)$fields['rowCount'];
		}
	}  //end _adjustSummaryFields

}  //end activationSummary
