<?php

/**
 * Reporting batch script
 * 
 * @category giftingapp
 * @package batch.report
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

require_once realpath(dirname(__FILE__) . '/../../init.php');

// Import libtool bootstrapping class
ENV::includeLibrary('bootstrap');

// Use class libtool\cli as cli
// Use class libtool\report as report
use libtool\cli;
use libtool\report;

try {
	report::run();
}
// Regular exception handling
// TODO: add custom logging class or exception handling tool
catch (Exception $e) {
	cli::ioStream()->stderr($e);
}
