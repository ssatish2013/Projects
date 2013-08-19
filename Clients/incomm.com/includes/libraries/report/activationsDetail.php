<?php

/**
 * Reporting tool - activations detail report
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

class activationsDetail extends skel {
	protected $_headers = array(
		'Merchant' => array(),
		'Location' => array(), 
		'Merchant Country' => array(), 
		'Country' => array('quoteData'=>true),
		'City' => array('quoteData'=>true), 
		'State' => array('quoteData'=>true), 
		'Zip' => array('quoteData'=>true), 
		'Terminal' => array(),
		'Vendor' => array(), 
		'DCMSID' => array(), 
		'Product' => array(), 
		'Denomination' => array(), 
		'Currency' => array(), 
		'SerialNumber' => array('removeLeadingZeros'=>true), 
		'TransDate' => array(),
		'TransTime' => array(), 
		'TransDateTime' => array(), 
		'Action' => array(), 
		'Sign' => array(), 
		'CardAmount' => array(), 
		'LogID' => array(), 
		'ReservationId' => array(),
		'WeekId' => array(), 
		'MonthId' => array(), 
		'Discount Amount' => array(), 
		'Net Sale' => array(), 
		'Fee Rate' => array(), 
		'Fee Amount' => array(), 
		'Net Fee Sales' => array(),
		'Transaction Fee' => array(), 
		'Total Net Sales' => array(), 
		'UPC' => array(), 
		'Contributors' => array(),
		'GiftingMode' => array(),
	);

	protected $_sqls = array(
		parent::SQL_STD => 'SELECT l.type AS ledgerType, l.giftId, g.partner, i.locationId, i.terminalId,
				t.countryCrypt, t.cityCrypt, t.stateCrypt, t.zipCrypt, p.dcmsId, p.description, l.amount,
				l.currency, i.pan, MAX(l.timestamp) AS timestamp, r.id AS reservationId, i.activationMargin,
				p.upc, t.id AS transactionId, count(m.id) AS `contributors`,
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
				l.currency, i.pan,MAX(l.timestamp) AS timestamp, r.id AS reservationId, p.upc, count(m.id) AS `contributors`,
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
			WHERE l.type = "%s" AND i.pan IS NOT NULL
			GROUP BY i.pan
			HAVING timestamp >= "%s" AND g.partner = "%s" AND timestamp <= "%s"
			ORDER BY l.id ASC'
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
			parent::IDX_RUN_METHOD => '_stdRow'
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
		$rs = parent::_query($this->_sql($sqlType, $ledgerType));
		while ($fields = mysql_fetch_assoc($rs)) {
			// - Render and write row
			$this->_putCsv($this->$rowMethod($fields));
		}
		$this->_giftIds = array();
		mysql_free_result($rs);
	}  //end _fetchAndWrite

	private function _stdRow(array & $fields) {
		if ($this->_skipStdRow($fields)) {
			return array();
		}
		$type = $fields['ledgerType'];
		$amount = (double)$fields['amount'];
		$discountAmount = $this->_discountAmount($fields);
		$amountAfterDiscount = $amount - $discountAmount;
		$fees = $this->_calculateFees($fields);
		$timestamp = strtotime($fields['timestamp']);
		return array(
			'GroupCard',					//merchant
			$fields['locationId'],			//location id
			'US',							//merchant country
			\baseModel::decrypt($fields['countryCrypt']),	//purchase address info
			\baseModel::decrypt($fields['cityCrypt']),		//purchase address info
			\baseModel::decrypt($fields['stateCrypt']),		//purchase address info
			\baseModel::decrypt($fields['zipCrypt']),		//purchase address info
			$fields['terminalId'],			//terminal id
			$fields['partner'],				//partner/vendor
			$fields['dcmsId'],				//dcmsid
			$fields['description'],			//product description
			(($type == parent::LDGR_ACT) ? -1 * $amountAfterDiscount : $amountAfterDiscount),	//denomination
			$fields['currency'],			//currency
			$fields['pan'],					//serial number
			parent::_date('m/d/y', $timestamp),				//date and time
			parent::_date('H:i', $timestamp),				//date and time
			parent::_date('m/d/y H:i', $timestamp),			//date and time
			$this->_actions[$type],			//action
			(($amount < 0) ? 1 : -1),		//sign
			(-1 * $amountAfterDiscount),	//card amount
			'N/A',							//log id
			$fields['reservationId'],		//reservation id
			\reportHelper::getWeekId(parent::_date('Y-m-d H:i:s', $timestamp)),	//week id
			\reportHelper::getMonthId(parent::_date('Y-m-d H:i:s', $timestamp)),//month id
			$discountAmount,				//discount amount
			(-1 * $amount),					//net sale
			((double)$fields['activationMargin'] / 100),	//fee rate
			$fees['feeAmt'],				//fee amount
			$fees['netFeeAmt'],				//net fee amount
			$fees['txnAmt'],				//transaction amount
			$fees['netAmt'],				//net amount
			$fields['upc'],					//upc
			$fields['contributors'],		// number of contributors
			$fields['giftingMode'],
		);
	}  //end _stdRow

	private function _cbkRow(array & $fields) {
		if ($this->_skipCbkRow($fields)) {
			return array();
		}
		$amount = (double)$fields['amount'];
		$timestamp = strtotime($fields['timestamp']);
		return array(
			'GroupCard',					//merchant
			$fields['locationId'],			//location id
			'US',							//merchant country
			\baseModel::decrypt($fields['countryCrypt']),	//purchase address info
			\baseModel::decrypt($fields['cityCrypt']),		//purchase address info
			\baseModel::decrypt($fields['stateCrypt']),		//purchase address info
			\baseModel::decrypt($fields['zipCrypt']),		//purchase address info
			$fields['terminalId'],			//terminal id
			$fields['partner'],				//partner/vendor
			$fields['dcmsId'],				//dcmsid
			$fields['description'],			//product description
			0,								//denomination
			$fields['currency'],			//currency
			$fields['pan'],					//serial number
			parent::_date('m/d/y', $timestamp),				//date and time
			parent::_date('H:i', $timestamp),				//date and time
			parent::_date('m/d/y H:i', $timestamp),			//date and time
			'C',							//action
			-1,								//sign
			0,								//card amount
			'N/A',							//log id
			$fields['reservationId'],		//reservation id
			\reportHelper::getWeekId(parent::_date('Y-m-d H:i:s', $timestamp)),	//week id
			\reportHelper::getMonthId(parent::_date('Y-m-d H:i:s', $timestamp)),//month id
			0,								//discount amount
			0,								//net sale
			0,								//fee rate
			0,								//fee amount
			0,								//net fee amount
			(-1 * $amount),					//transaction amount
			(-1 * $amount),					//net amount
			$fields['upc'],					//upc
			$fields['contributors'],		// number of contributors
			$fields['giftingMode'],
		);
	}  //end _cbkRow

	private function _discountAmount(array & $fields) {
		$discountAmount = 0;
		if ($fields['ledgerType'] == parent::LDGR_NDR) {
			return $discountAmount;
		}
		$sql = 'SELECT amount FROM ledgers WHERE type = "%s" AND giftId = %d';
		if ($fields['ledgerType'] == parent::LDGR_ACT) {
			$sql = sprintf($sql, parent::LDGR_PROMO_ACT, $fields['giftId']);
		} elseif ($fields['ledgerType'] == parent::LDGR_DEACT) {
			$sql = sprintf($sql, parent::LDGR_PROMO_DEACT, $fields['giftId']);
		}
		$rs = parent::_query($sql);
		if (mysql_num_rows($rs) > 0) {
			list($discountAmount) = mysql_fetch_row($rs);
		}
		mysql_free_result($rs);

		return (double)$discountAmount;
	}  //end _discountAmount

	private function _calculateFees(array & $fields) {
		$fees = array('feeAmt' => 0, 'netFeeAmt' => 0, 'txnAmt' => 0, 'netAmt' => 0);
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
		// Timestamp of activationFee or deactivationFee entry must be between the period
		// the reporting start and end dates
		if (mysql_num_rows($rs) == 0) {
			return $fees;
		}
		// Extract value from first item in returned record set
		list($fee) = mysql_fetch_row($rs);
		mysql_free_result($rs);
		// Calculate and assign fee amounts to $fees collection
		$fees['feeAmt'] = $fee;
		$fees['netFeeAmt'] = -1 * ((double)$fields['amount'] + (double)$fee);
		$fees['netAmt'] = -1 * ((double)$fields['amount'] + (double)$fee);

		return $fees;
	}  //end _calculateFees

	private function _skipStdRow(array & $fields) {
		$gid = (int)$fields['giftId'];
		$tid = (int)$fields['transactionId'];
		// Skip the lines not only do them have null values on pan but have
		// the same values on fee and total fields
		// - 2 (or more, usually 2) reservation for one gift
		// - one of the reservations has inventoryId
		// - another one has null value on inventoryId field
		if (empty($fields['pan'])) {
			$sql = 'SELECT id FROM reservations
					WHERE giftId = ' . $gid . ' AND inventoryId IS NOT NULL';
			$rs = parent::_query($sql);
			if (mysql_num_rows($rs) > 0) {
				return true;
			}
		}
		// Skip duplicate line caused by duplicate transaction records
		// - 2 (or more, usually 2) transactions for one shopping cart
		else {
			if (isset($this->_giftIds[$gid]) && ($this->_giftIds[$gid] != $tid)) {
				return true;
			}
			$this->_giftIds[$gid] = $tid;
		}
		// Leave those redundant line caused by incorrect deactivation ledger type
		// [act + actFee and act (should be deact) + deactFee ]
		return false;
	}  //end _skipStdRow

	private function _skipCbkRow(array & $fields) {
		if (isset($this->_giftIds[$fields['giftId']])) {
			return true;
		}
		$this->_giftIds[$fields['giftId']] = $fields['giftId'];
		return false;
	}  //end _skipCbkRow

}  //end activationDetail
