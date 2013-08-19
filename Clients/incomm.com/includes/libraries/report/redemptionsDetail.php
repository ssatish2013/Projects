<?php

/**
 * Reporting tool - redemptions detail class
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

class redemptionsDetail extends skel {
	protected $_headers = array(
		'Merchant' => array(), 
		'Country' => array('quoteData'=>true), 
		'City' => array('quoteData'=>true), 
		'State' => array('quoteData'=>true), 
		'Zip' => array('quoteData'=>true), 
		'Terminal' => array(), 
		'Vendor' => array(), 
		'DCMSID' => array(), 
		'Product' => array(),
		'Denomination' => array('decimalPrecision'=>2, 'padPrecision'=>2), 
		'Currency' => array(), 
		'SerialNumber' => array('removeLeadingZeros'=>true), 
		'Trans Date' => array(), 
		'Trans Time' => array(), 
		'Trans DateTime' => array(),
		'Action' => array(), 
		'Sign' => array(), 
		'TranAmount' => array('decimalPrecision'=>2, 'padPrecision'=>2), 
		'RefNo' => array(), 
		'Location' => array(), 
		'Week ID' => array(), 
		'Month ID' => array(), 
		'Discount Amount' => array(),
		'Net Sale' => array(), 
		'UPC' => array(), 
		'Fee Rate' => array(), 
		'Fee Amount' => array('removeLeadingZeros'=>true), 
		'Net Fee Sales' => array('decimalPrecision'=>2), // no padding of precision
		'Transaction Fee' => array(), 
		'Total Net Sales' => array('decimalPrecision'=>2), // no padding of precision
		'Contributors' => array(),
		'GiftingMode' => array(),
	);

	// Added GROUP BY g.id to remove duplicate entries produced by duplicate transactions
	protected $_sqls = array(
		parent::SQL_RED => 'SELECT i.giftId, g.partner, i.locationId, i.terminalId, i.activationAmount,
			t.countryCrypt, t.cityCrypt, t.stateCrypt, t.zipCrypt, p.dcmsId, p.description,
			g.currency, i.pan, er.redemptionTime, m.id AS messageId, i.activationMargin, p.upc, count(m.id) AS `contributors`,
			CASE g.giftingMode
				WHEN 1 THEN "group"
				WHEN 2 THEN "single"
				WHEN 3 THEN "self"
				WHEN 4 THEN "voucher"
				ELSE g.giftingMode
			END AS "giftingMode"
			FROM externalRedemptions er
				LEFT JOIN inventorys i ON er.inventoryId = i.id
				LEFT JOIN gifts g ON i.giftId = g.id
				LEFT JOIN messages m ON g.id = m.giftId
				LEFT JOIN shoppingCarts sc ON m.shoppingCartId = sc.id
			RIGHT JOIN transactions t ON sc.id = t.shoppingCartId
			LEFT JOIN products p ON g.productId = p.id
			WHERE g.partner = "%s" AND er.redemptionTime >= "%s" AND er.redemptionTime <= "%s"
			GROUP BY g.id
			ORDER BY er.id ASC',
		parent::SQL_STD => 'SELECT l.type AS ledgerType, l.giftId, g.partner, i.locationId, i.terminalId,
				t.countryCrypt, t.cityCrypt, t.stateCrypt, t.zipCrypt, p.dcmsId, p.description, l.amount,
				l.currency, i.pan, MAX(l.timestamp) AS timestamp, i.activationMargin, i.activationAmount,
				p.upc, COUNT(m.id) as `contributors`,
				CASE g.giftingMode
					WHEN 1 THEN "group"
					WHEN 2 THEN "single"
					WHEN 3 THEN "self"
					WHEN 4 THEN "voucher"
					ELSE g.giftingMode
				END AS "giftingMode"
			FROM ledgers l
				LEFT JOIN gifts g ON l.giftId = g.id
				LEFT JOIN reservations r ON g.id = r.giftId
				LEFT JOIN inventorys i ON r.inventoryId = i.id
				LEFT JOIN messages m ON g.id = m.giftId
				LEFT JOIN shoppingCarts sc ON m.shoppingCartId = sc.id
				RIGHT JOIN transactions t ON sc.id = t.shoppingCartId
				LEFT JOIN products p ON g.productId = p.id
			WHERE l.type = "%s" AND g.partner = "%s" AND i.pan IS NOT NULL
			GROUP BY i.pan
			HAVING timestamp >= "%s" AND timestamp <= "%s"
			ORDER BY l.id ASC',
		parent::SQL_CBK => 'SELECT g.id AS giftId, g.partner, i.locationId, i.terminalId, l.amount,
				t.countryCrypt, t.cityCrypt, t.stateCrypt, t.zipCrypt, p.dcmsId, p.description,
				l.currency, i.pan, MAX(l.timestamp) AS timestamp, p.upc, COUNT(m.id) AS `contributors`,
				CASE g.giftingMode
					WHEN 1 THEN "group"
					WHEN 2 THEN "single"
					WHEN 3 THEN "self"
					WHEN 4 THEN "voucher"
					ELSE g.giftingMode
				END AS "giftingMode"
			FROM ledgers l
				LEFT JOIN shoppingCarts sc ON l.shoppingCartId = sc.id
				LEFT JOIN transactions t ON sc.id = t.shoppingCartId
				LEFT JOIN messages m ON m.shoppingCartId = sc.id
				LEFT JOIN gifts g ON g.id = m.giftId
				LEFT JOIN reservations r ON g.id = r.giftId
				LEFT JOIN inventorys i ON r.inventoryId = i.id
				LEFT JOIN products p ON g.productId = p.id
			WHERE l.type = "%s" AND sc.partner = "%s" AND i.pan IS NOT NULL
			GROUP BY i.pan
			HAVING timestamp >= "%s" AND timestamp <= "%s"
			ORDER BY l.id ASC'
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
			// - Render and write row
			$this->_putCsv($this->$rowMethod($fields));
		}
		$this->_giftIds = array();
		mysql_free_result($rs);
	}  //end _fetchAndWrite

	private function _redRow(array & $fields) {
		$activationAmount = (double)$fields['activationAmount'];
		$discountAmount = $this->_discountAmount($fields);
		$fees = ($activationAmount - $discountAmount) * (double)$fields['activationMargin'] / 100;
		$fees = number_format($fees, 2, '.', '');
		$timestamp = strtotime($fields['redemptionTime']);
		return array(
			'GroupCard',									// merchant
			\baseModel::decrypt($fields['countryCrypt']),	// purchase address info
			\baseModel::decrypt($fields['cityCrypt']),		// purchase address info
			\baseModel::decrypt($fields['stateCrypt']),		// purchase address info
			\baseModel::decrypt($fields['zipCrypt']),		// purchase address info
			$fields['terminalId'],							// terminal id
			$fields['partner'],								// partner/vendor
			$fields['dcmsId'],								// dcmsid
			$fields['description'],							// product description
			$activationAmount,								// denomination
			$fields['currency'],							// currency
			$fields['pan'],									// serial number
			parent::_date('m/d/y', $timestamp),				// date and time
			parent::_date('H:i', $timestamp),				// date and time
			parent::_date('m/d/y H:i', $timestamp),			// date and time
			'X',											// action
			1,												// sign
			$activationAmount,								// tran amount
			'N/A',											// refNo
			$fields['locationId'],							// location id
			\reportHelper::getWeekId(parent::_date('Y-m-d H:i:s', $timestamp)),	// week id
			\reportHelper::getMonthId(parent::_date('Y-m-d H:i:s', $timestamp)),// month id
			$discountAmount,								// discount amount
			($activationAmount - $discountAmount),			// net sale
			$fields['upc'],									// upc
			((double)$fields['activationMargin'] / 100),	// fee rate
			$fees,											// fee amount
			($activationAmount - $discountAmount - $fees),	// net fee amount
			0,												// transaction amount
			($activationAmount - $discountAmount - $fees),	// net amount
			$fields['contributors'],						// number of contributors
			$fields['giftingMode'],
		);
	}  //end _redRow

	private function _stdRow(array & $fields) {
		$type = $fields['ledgerType'];
		$amount = (double)$fields['amount'];
		$fees = $this->_calculateFees($fields);
		$timestamp = strtotime($fields['timestamp']);
		return array(
			'GroupCard',									// merchant
			\baseModel::decrypt($fields['countryCrypt']),	// purchase address info
			\baseModel::decrypt($fields['cityCrypt']),		// purchase address info
			\baseModel::decrypt($fields['stateCrypt']),		// purchase address info
			\baseModel::decrypt($fields['zipCrypt']),		// purchase address info
			$fields['terminalId'],							// terminal id
			$fields['partner'],								// partner/vendor
			$fields['dcmsId'],								// dcmsid
			$fields['description'],							// product description
			(($amount < 0) ? -1 * $amount : $amount),		// denomination
			$fields['currency'],							// currency
			$fields['pan'],									// serial number
			parent::_date('m/d/y', $timestamp),				// date and time
			parent::_date('H:i', $timestamp),				// date and time
			parent::_date('m/d/y H:i', $timestamp),			// date and time
			$this->_actions[$type],							// action
			(($amount < 0) ? 1 : -1),						// sign
			$fields['activationAmount'],					// tran amount
			'N/A',											// refNo
			$fields['locationId'],							// location id
			\reportHelper::getWeekId(parent::_date('Y-m-d H:i:s', $timestamp)),	// week id
			\reportHelper::getMonthId(parent::_date('Y-m-d H:i:s', $timestamp)),// month id
			0,												// discount amount
			(-1 * $amount),									// net sale
			$fields['upc'],									// upc
			$fees['feeRate'],								// fee rate
			$fees['feeAmt'],								// fee amount
			$fees['netFeeAmt'],								// net fee amount
			$fees['txnAmt'],								// transaction amount
			$fees['netAmt'],								// net amount
			$fields['contributors'],						// number of contributors
			$fields['giftingMode'],
		);
	}  //end _stdRow

	private function _cbkRow(array & $fields) {
		// Gathering chargeback row data
		$amount = (double)$fields['amount'];
		$timestamp = strtotime($fields['timestamp']);
		return array(
			'GroupCard',									// merchant
			\baseModel::decrypt($fields['countryCrypt']),	// purchase address info
			\baseModel::decrypt($fields['cityCrypt']),		// purchase address info
			\baseModel::decrypt($fields['stateCrypt']),		// purchase address info
			\baseModel::decrypt($fields['zipCrypt']),		// purchase address info
			$fields['terminalId'],							// terminal id
			$fields['partner'],								// partner/vendor
			$fields['dcmsId'],								// dcmsid
			$fields['description'],							// product description
			0,												// denomination
			$fields['currency'],							// currency
			$fields['pan'],									// serial number
			parent::_date('m/d/y', $timestamp),				// date and time
			parent::_date('H:i', $timestamp),				// date and time
			parent::_date('m/d/y H:i', $timestamp),			// date and time
			'C',											// action
			-1,												// sign
			0,												// card amount
			'N/A',											// refNo id
			$fields['locationId'],							// location id
			\reportHelper::getWeekId(parent::_date('Y-m-d H:i:s', $timestamp)),	// week id
			\reportHelper::getMonthId(parent::_date('Y-m-d H:i:s', $timestamp)),// month id
			0,												// discount amount
			0,												// net sale
			$fields['upc'],									// upc
			0,												// fee rate
			0,												// fee amount
			0,												// net fee sales
			(-1 * $amount),									// transaction fee
			(-1 * $amount),									// total net sales fee
			$fields['contributors'],						// number of contributors
			$fields['giftingMode'],
		);
	}  //end _cbkRow

	private function _discountAmount(array & $fields) {
		// Discount amount
		$sql = 'SELECT SUM(discountAmount) FROM promoTransactions WHERE messageId = %d';
		$rs = parent::_query(sprintf($sql, $fields['messageId']));
		$discountAmount = 0;
		if (mysql_num_rows($rs) > 0) {
			list($discountAmount) = mysql_fetch_row($rs);
		}
		mysql_free_result($rs);

		return (double)$discountAmount;
	}  //end _discountAmount

	private function _calculateFees(array & $fields) {
		$ledgerFeeType = array(
			parent::LDGR_ACT => parent::LDGR_ACT_FEE,
			parent::LDGR_DEACT => parent::LDGR_DEACT_FEE,
			parent::LDGR_NDR => parent::LDGR_NDR_FEE
		);
		$sql = 'SELECT amount FROM ledgers
				WHERE type = "%s" AND giftId = "%d"
					AND timestamp BETWEEN "%s" AND "%s"';
		$rs = parent::_query(sprintf(
			$sql,
			$ledgerFeeType[$fields['ledgerType']],
			$fields['giftId'],
			$this->_reportTzDateTimes[self::DT_DATETIME_START],
			$this->_reportTzDateTimes[self::DT_DATETIME_END]
		));
		list($fee) = mysql_fetch_row($rs);
		$fees = array(
			'feeRate'   => ($fields['ledgerType'] == parent::LDGR_ACT)
				? ((double)$fields['activationMargin'] / 100)
				: (-1 * (double)$fields['activationMargin'] / 100),
			'feeAmt'    => $fee,
			'netFeeAmt' => -1 * ((double)$fields['amount'] + (double)$fee),
			'txnAmt'    => 0,
			'netAmt'    => -1 * ((double)$fields['amount'] + (double)$fee)
		);

		return $fees;
	}  //end _calculateFees

}  //end redemptionDetail
