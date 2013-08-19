<?php

/**
 * Promo report script
 *
 * @author Waltz.of.Pearls <rollie@groupcard.com, rma@incomm.com, rollie.ma@gmail.com>
 * @copyright InComm Canada
 * @version 0.1.0
 * @example promo.php -s"StartDate" -e"EndDate" -f"CSV" -r
 *
 * CreateDate Jan 17, 2012
 */

require_once realpath(dirname(__FILE__).'/../../init.php');


try {
	$report = simpleCsvReport::getInstance();

	// Options
	// -s  Start date, when the promo report generates from
	// -e  [optional] End date, when the promo report generates to
	//     Default value = start date "-s"
	// -r  [optional] Upload report file to remote FTP/SFTP
	//     Default value = remote upload disabled
	// -f  [optional] Force CSV file regeneration
	//     By default (if "-f" option is not specified on the command),
	//     regeneration will not happen if there is an existing file with
	//     the same file name
	// -p  [optional] partner identifier
	//     By default all partners will be selected
	$report->useDbMaster(true)->promoReportCli();
} catch (Exception $e) {
	print $e->getMessage() . PHP_EOL;
}
