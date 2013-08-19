<?php

/* rawXml.php
 * This file generates a raw xml
 */
require_once(dirname(__FILE__)."/../../init.php");



/* Grab options */
$opts = getopt("s:e::");
if(!isset($opts['s'])) { 
	usage();
}

if(!isset($opts['e'])) { 
	$opts['e'] = $opts['s']; 
}

//create the report
$report = new xmlReport(array(
	'startDate' => $opts['s'],
	'endDate' => $opts['e']
));

//generate xml
$report->generateXml(true);

function usage() {
	print "usage:
	php rawXml.php -s[start string]
	options:
	  -s Start date string in strtotime format
	  -e End date string in strtotime format (optional)\n\n";
	exit();
}
