<?php

/**
 * Simpble script for generating sales summary csv files by partner
 * 
 * @author Waltz.of.Pearls <rollie@groupcard.com, rma@incomm.com, rollie.ma@me.com>
 * @copyright Copyright (c) 2010 InComm Canada (http://www.incomm-canada.com)
 * @version 0.2.0
 * @tutorial Script usage
 *             $ sales.php [OPTION]
 *           Script options
 *             --start=DATE   [optional] Start month of the report,
 *                            it defaults to the first month of the current year
 *                            e.g. --start="2011-08-01"
 *             --end=DATE     [optional] End month of the report,
 *                            it defaults to the current monthe of the current year
 *                            e.g. --end="2012-02-01"
 *             --separate     [optional] Write data into separate files by partner,
 *                            it will write into one file if this option is not given
 * @example $ cd /var/www/giftingapp/production
 *          $ php includes/batch/report/sales.php
 *          $ php includes/batch/report/sales.php --start="2011-12-01"
 *          $ php includes/batch/report/sales.php --start="2011-12-01" --separate
 *          $ php includes/batch/report/sales.php --separate
 */

require_once dirname(__FILE__) . '/../../init.php';

$opts = getopt('', array(
	'start:',
	'end:',
	'separate',
	'help'
	));

if (isset($opts['help'])) {
	usage();
}

$separate = isset($opts['separate']);

// Prepare some date string templates for db queries and file names
$startTimestamp = !isset($opts['start'])
	? strtotime(date('Y-01-01'))
	: strtotime(date('Y-m-01', strtotime($opts['start'])));
$endTimestamp = !isset($opts['end'])
	? time()
	: strtotime(date('Y-m-01', strtotime($opts['end'])));
$startYear = date('Y', $startTimestamp);
$endYear = date('Y', $endTimestamp);

$reportingMonths = array();
$timestamp = $startTimestamp;
while ($timestamp <= $endTimestamp) {
	$reportingMonths[] = array(
		'start' => date('Y-m-01 00:00:00', $timestamp),
		'end' => date('Y-m-t 23:59:59', $timestamp)
		);
	$timestamp = strtotime(date('Y-m-d', $timestamp) . '+1 month');
}   //end while

// Get current environment name
$env = ENV::main()->getEnvName();
// List and assign all the ledger types needed in the summary
$ledgerTypes = array(
	'activation',
	'activationFee',
	'promoActivation',
	'deactivation',
	'deactivationFee',
	'promoDeactivation',
	'ndr',
	'ndrFee',
	'chargebackPartnerFee'
	);
// SQL templates for getting summary data from db for chargeback and other types
// of transaction like activation, deactivation or ndr
$sqls = array();
$sqls['others'] = 'SELECT SUM(l.amount) AS sumAmt,
		COUNT(1) AS numCnt
	FROM ledgers l
	LEFT JOIN gifts g
		ON l.giftId = g.id
	WHERE g.productId = "%s"
		AND l.`type` = "%s"
		AND l.timestamp BETWEEN "%s" AND "%s"';
$sqls['chargebackPartnerFee'] = 'SELECT SUM(l.amount) AS sumAmt,
		COUNT(1) AS numCnt
	FROM ledgers l
	LEFT JOIN messages m
		ON m.shoppingCartId = l.shoppingCartId
	LEFT JOIN gifts g
		ON m.giftId = g.id
	WHERE g.productId = "%s"
		AND l.`type` = "%s"
		AND l.timestamp BETWEEN "%s" AND "%s"';

// Fetch all the partners display name from language table
$partnerDisplayNames =& getPartnerDisplayNames();

// Fetch all the products from all the partners
$partnersProducts =& getPartnersProducts($partnerDisplayNames);

// Generate csv header line column names based on the months passed in the year
$salesReportHeaders =& getHeaderFields($reportingMonths);

// Initialize arrays and variables used for summary and stat
$salesReportData = array();
$reportDataLineItems = array();
$ledgerSummaries = array();
$lineGrandTotalCount = 0;
$lineGrandTotalAmount = 0.00;
$individualPartnerGrandTotals = array();
$allPartnersGrandTotals = array('col-2' => '', 'col-1' => 'Grand Total:');
// Iterate through partenrs and generate the actual report data by different product
foreach ($partnersProducts as $partner => $products) {
	print '- Collecting sales report data for partner ' . $partner . ' ...' . PHP_EOL;
	$salesReportData[$partner] = array();
	$individualPartnerGrandTotals[$partner] = array('col-2' => '', 'col-1' => 'Grand Total:');
	// Iterate through the products under each partner
	foreach ($products as $productId => $productInfo) {
		$lineGrandTotalCount = 0;
		$lineGrandTotalAmount = 0.00;
		// ===== Create array for holding report data for each line =====
		$reportDataLineItems = array();
		// ===== CSV COLUMN - Partner =====
		$reportDataLineItems[] = $productInfo['partnerDisplayName'];
		quoteFieldContainsComma($reportDataLineItems);
		// ===== CSV COLUMN - ProductName =====
		$reportDataLineItems[] = empty($productInfo['product'])
			? $productInfo['guid']
			: $productInfo['product'];
		quoteFieldContainsComma($reportDataLineItems);
		// Iterate through all the available months in the reports
		//while ($month <= $currentMonth) {
		foreach ($reportingMonths as $month) {
			$ledgerSummaries = array();
			// Iterate through all the available ledger types for the same product
			foreach ($ledgerTypes as $ledger) {
				$ledgerSummaries[$ledger] = sumUpIndividualLedgerType(
					$ledger,
					$productId,
					$month['start'],
					$month['end']
					);
			}   //end foreach
			// ===== CSV COLUMN - TxnNumberCount =====
			// activation - deactivation - ndr - chargeback
			$reportDataLineItems[] = $ledgerSummaries['activation']['numCnt']
				- $ledgerSummaries['deactivation']['numCnt']
				- $ledgerSummaries['ndr']['numCnt'];
				- $ledgerSummaries['chargebackPartnerFee']['numCnt'];
			$lineGrandTotalCount += current($reportDataLineItems);
			sumUpGrandTotals($individualPartnerGrandTotals, $allPartnersGrandTotals, $reportDataLineItems, $partner);
			quoteFieldContainsComma($reportDataLineItems);
			// ===== CSV COLUMN - TxnTotalAmount =====
			// -1 * (activation + activationFee) - (deactivation + deactivationFee) - (ndr + ndrFee) - chargebackPartnerFee
			$reportDataLineItems[] =
				-1 * ($ledgerSummaries['activation']['sumAmt'] + $ledgerSummaries['activationFee']['sumAmt'])
				- ($ledgerSummaries['deactivation']['sumAmt'] + $ledgerSummaries['deactivationFee']['sumAmt'])
				- ($ledgerSummaries['ndr']['sumAmt'] + $ledgerSummaries['ndrFee']['sumAmt'])
				- $ledgerSummaries['chargebackPartnerFee']['sumAmt'];
			$lineGrandTotalAmount += current($reportDataLineItems);
			sumUpGrandTotals($individualPartnerGrandTotals, $allPartnersGrandTotals, $reportDataLineItems, $partner);
			formatTotalAmountField($reportDataLineItems);
			quoteFieldContainsComma($reportDataLineItems);
		}   //end while
		// ===== CSV COLUMN - GrandTotalTxnCount =====
		$reportDataLineItems[] = $lineGrandTotalCount;
		sumUpGrandTotals($individualPartnerGrandTotals, $allPartnersGrandTotals, $reportDataLineItems, $partner);
		quoteFieldContainsComma($reportDataLineItems);
		// ===== CSV COLUMN - GrandTotalTxnAmount =====
		$reportDataLineItems[] = $lineGrandTotalAmount;
		sumUpGrandTotals($individualPartnerGrandTotals, $allPartnersGrandTotals, $reportDataLineItems, $partner);
		formatTotalAmountField($reportDataLineItems);
		quoteFieldContainsComma($reportDataLineItems);
		// Add new product collection to partner
		$salesReportData[$partner][] = $reportDataLineItems;
	}   //end foreach
}   //end foreach

formatGrandTotalAmounts($individualPartnerGrandTotals, $allPartnersGrandTotals);

// Assign which directory the reports are being generated to
$reportFileNameTpl = '/var/reports/' . $env . '/sales/%s-' . $startYear
	. ($startYear != $endYear ? '-' . $endYear : '') . '.csv';

// Write headers for non-separate sales report
if (!$separate) {
	$fp = fopen(sprintf($reportFileNameTpl, 'all'), 'wb');
	fwrite($fp, implode(', ', $salesReportHeaders) . PHP_EOL);
	fclose($fp);
}   //end if

foreach ($salesReportData as $partner => $lines) {
	// Create regular or appendable file pointer
	if ($separate) {
		// Create report file, file pointer and write csv header line
		//
		// Use different file names for each partner so partner sales
		// report data will be written to separate files
		$fp = fopen(sprintf($reportFileNameTpl, $partner), 'wb');
		fwrite($fp, implode(', ', $salesReportHeaders) . PHP_EOL);
	} else {
		// Create appendable file pointer
		//
		// Use the same file name so all the lines will be written
		// to the same file
		$fp = fopen(sprintf($reportFileNameTpl, 'all'), 'ab');
	}   //end if
	// Iterate through report data lines
	foreach ($lines as $index => $items) {
		// Write report data line
		fwrite($fp, implode(', ', $items) . PHP_EOL);
	}   //end foreach
	$individualPartnerGrandTotals[$partner]['col-1'] = (isset($partnerDisplayNames[$partner])
		? $partnerDisplayNames[$partner]
		: $partner) . ' Grand Total:';
	// Write partner grand totals
	fwrite($fp, implode(', ', $individualPartnerGrandTotals[$partner]) . PHP_EOL);
	// Close the file to release the resource taken for file pointer
	fclose($fp);
}   //end foreach

// Write all partners grand totals for non-separete report
if (!$separate) {
	$fp = fopen(sprintf($reportFileNameTpl, 'all'), 'ab');
	fwrite($fp, implode(', ', $allPartnersGrandTotals) . PHP_EOL);
	fclose($fp);
}   //end if

/**
 * Fetch all the partners display name from language table
 *
 * @return array by ref 
 */
function & getPartnerDisplayNames() {
	$names = array();
	$sql = 'SELECT partner, value
			FROM languages
			WHERE `name` = "partnerDisplayName"
				AND partner IS NOT NULL';
	$rs = db::query($sql, false);
	while ($fields = mysql_fetch_assoc($rs)) {
		$names[$fields['partner']] = $fields['value'];
	}   //end while

	return $names;
}   //end function

/**
 * Fetch all the products from all the partners
 *
 * @global string $env
 * @param array &$partnerDisplayNames
 * @return array by ref 
 */
function & getPartnersProducts(array &$partnerDisplayNames) {
	global $env;

	$products = array();
	$sql = 'SELECT p.id, p.guid, p.description, g.partner
		FROM gifts g
		LEFT JOIN products p
			ON g.productId = p.id
		WHERE g.productId IS NOT NULL
			AND g.envName =  "%s"
		GROUP BY g.productId';
	$rs = db::query(sprintf($sql, $env), false);
	// Iterate through db result set and assign results to a php assoc array
	while ($fields = mysql_fetch_assoc($rs)) {
		if (!isset($products[$fields['partner']])) {
			$products[$fields['partner']] = array();
		}   //end if
		$products[$fields['partner']][$fields['id']] = array(
			'guid' => $fields['guid'],
			'product' => $fields['description'],
			'partnerDisplayName' => isset($partnerDisplayNames[$fields['partner']])
				? $partnerDisplayNames[$fields['partner']]
				: $fields['partner']
			);
	}   //end while
	mysql_free_result($rs);

	return $products;
}   //end function

/**
 * Get CSV header line fields in an array
 *
 * @param array &$months
 * @staticvar array $headers
 * @return array 
 */
function & getHeaderFields(array &$months) {
	static $headers = array();

	if (count($headers) > 0) {
		return $headers;
	}   //end if
	// Generate csv header line column names based on the months passed in the year
	$headers[] = 'Partner';
	$headers[] = 'ProductName';
	foreach ($months as $m) {
		// Prefix is like January2012 or December2011
		$columnNamePrefix = date('FY', strtotime($m['start']));
		$headers[] = $columnNamePrefix . 'TxnNumberCount';
		$headers[] = $columnNamePrefix . 'TxnTotalAmount';
	}   //end foreach
	$headers[] = 'GrandTotalTxnCount';
	$headers[] = 'GrandTotalTxnAmount';

	return $headers;
}   //end function

/**
 * Automatically add quotes to the current item in the given array
 * if it contains any comma, and go to next item by default
 *
 * @param array &$items
 * @param boolean $next 
 * @return void 
 */
function quoteFieldContainsComma(array &$items, $next = true) {
	$items[key($items)] = strpos(current($items), ',')
		? '"' . current($items) . '"'
		: current($items);
	if ($next) {
		next($items);
	}   //end if
}   //end function

/**
 * Sum up grand totals for different total amount columns
 *
 * @param array &$individualGrandTotals
 * @param array &$allGrandTotals
 * @param array &$items 
 * @param string $partner 
 * @return void 
 */
function sumUpGrandTotals(array &$individualGrandTotals, array &$allGrandTotals, array &$items, $partner) {
	$key = 'col' . key($items);
	if (!isset($individualGrandTotals[$partner])) {
		$individualGrandTotals[$partner] = array();
	}   //end if
	if (!isset($individualGrandTotals[$partner][$key])) {
		$individualGrandTotals[$partner][$key] = 0.00;
	}   //end if
	if (!isset($allGrandTotals[$key])) {
		$allGrandTotals[$key] = 0.00;
	}   //end if
	$allGrandTotals[$key] += current($items);
	$individualGrandTotals[$partner][$key] += current($items);
}   //end function

/**
 * Format total amount field with 2 decimals
 *
 * @param array &$items 
 * @return void 
 */
function formatTotalAmountField(array &$items) {
	$items[key($items)] = number_format(current($items), 2, '.', '');
}   //end function

/**
 *
 * @global array $sqls
 * @param string $ledgerType
 * @param int $productId
 * @param string $startDateTime
 * @param string $endDateTime
 * @return array 
 */
function sumUpIndividualLedgerType($ledgerType, $productId, $startDateTime, $endDateTime) {
	global $sqls;

	$rs = db::query(sprintf(
		isset($sqls[$ledgerType]) ? $sqls[$ledgerType] : $sqls['others'],
		$productId,
		$ledgerType,
		$startDateTime,
		$endDateTime
		), false);
	$fields = mysql_fetch_assoc($rs);
	mysql_free_result($rs);
	// txn number count and txn amount total
	return array(
		'numCnt' => (int) $fields['numCnt'],
		'sumAmt' => is_null($fields['sumAmt']) ? 0.00 : (double) $fields['sumAmt']
		);
}   //end function

/**
 * Format grand total amounts for individual partner and all partners
 *
 * @param array &$individualGrandTotals
 * @param array &$allGrandTotals 
 * @return volid
 */
function formatGrandTotalAmounts(array &$individualGrandTotals, array &$allGrandTotals) {
	foreach ($allGrandTotals as $col => $amount) {
		if (!is_double($amount)) {
			continue;
		}   //end if
		$allGrandTotals[$col] = number_format($amount, 2, '.', '');
	}   //end foreach
	foreach ($individualGrandTotals as $partner => $grandTotals) {
		foreach ($grandTotals as $col => $amount) {
			if (!is_double($amount)) {
				continue;
			}   //end if
			$individualGrandTotals[$partner][$col] = number_format($amount, 2, '.', '');
		}   //end foreach
	}   //end foreach
}   //end function

/**
 * Print usage and exit
 *
 * @return void
 */
function usage() {
	print '
  Script usage
    $ sales.php [OPTION]
  Script options
    --start=DATE   [optional] Start month of the report,
                   it defaults to the first month of the current year
                   e.g. --start="2011-08-01"
    --end=DATE     [optional] End month of the report,
                   it defaults to the current monthe of the current year
                   e.g. --end="2012-02-01"
    --separate     [optional] Write data into separate files by partner,
                   it will write into one file if this option is not given
  Examples
    $ cd /var/www/giftingapp/production
    $ php includes/batch/report/sales.php
    $ php includes/batch/report/sales.php --start="2011-12-01"
    $ php includes/batch/report/sales.php --start="2011-12-01" --separate
    $ php includes/batch/report/sales.php --separate' . PHP_EOL . PHP_EOL;
	exit(0);
}   //end function
