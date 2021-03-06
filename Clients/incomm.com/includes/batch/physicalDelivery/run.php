<?php

/**
 * Physical delivery importing & exporting batch script
 * 
 * @category giftingapp
 * @package batch.physicalDelivery
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

require_once realpath(dirname(__FILE__) . '/../../init.php');

// Import libtool bootstrapping class
\Env::includeLibrary('bootstrap');

use libtool\cli;
use libtool\physicalDelivery;

try {
	physicalDelivery::run();
}
// Regular exception handling
catch (Exception $e) {
	cli::ioStream()->stderr($e);
}
