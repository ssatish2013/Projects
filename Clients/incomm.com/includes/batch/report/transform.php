<?php

/* rawXml.php
 * This file generates a raw xml
 */
require_once(dirname(__FILE__)."/../../init.php");

/* Get options */
$regenerated = array();
$opts = getopt("s:e::p::f::");
if(!isset($opts['s'])) { 
  usage(); 
}

if(!isset($opts['e'])) {
  $opts['e'] = $opts['s'];
}

$start = $opts['s'];
$end = $opts['e'];
$partners = array();

function partnerHasOldReportVersion($partner) {
	$partnerReportVersions = settingModel::getPartnerSettings($partner, 'reportGenerateVersion');
	return $partnerReportVersions['reportGenerateVersion'] == "1.0";
}

//if we're just generating for one partner
if(isset($opts['p'])) { 
	$partners = array($opts['p']);
}
else {
	//get all partners that we need reports for
	$partners = array_filter(
		array_keys(settingModel::getPartnerSettings(null, 'reportActive'), "1"), 
		'partnerHasOldReportVersion'
	);
}

//initialize the start range
$rangeStart = $start;

//go through all dates
while(strtotime($rangeStart) <= strtotime($end)) { 

	//cycle through all partners that want reports
	foreach($partners as $partner) { 

		//force the partner for the time being
		globals::forcePartnerRedirectLoaderForBatchScript($partner, null); // Redirect Loader is unimportant for reporting

		//get the report type for each partner
		$reports = settingModel::getPartnerSettings($partner, 'reportGenerateType');

		//go through each report by name
		foreach($reports as $reportName => $type) { 

			//grab the frequency we need to generate it at
			$frequency = explode(',',settingModel::getSetting('reportGenerateFreq',$reportName."Freq"));

			//daily
			if(in_array('daily', $frequency)) { 
				$rangeEnd = $rangeStart;
				createReport($rangeStart, $rangeEnd, $partner, $reportName, $type, 'daily');
			}

			//weekly
			if((date('l', strtotime($rangeStart)) == 'Sunday') && in_array('weekly', $frequency)) {
				$weekStart = date('Y-m-d', strtotime($rangeStart.' -6 Days'));
				createReport($weekStart, $rangeStart, $partner, $reportName, $type, 'weekly');
			}

			//monthly
			if((date('j', strtotime($rangeStart)) == date('t', strtotime($rangeStart))) && in_array('monthly', $frequency)) {
				$monthStart = date('Y-m-01', strtotime($rangeStart));
				createReport($monthStart, $rangeStart, $partner, $reportName, $type, 'monthly');
			}
		}
	}

	$rangeStart = date('Y-m-d', strtotime($rangeStart."+1 Day"));
}


/*
 * Creates a report
 */
function createReport($rangeStart, $rangeEnd, $partner, $reportName, $type, $iteration) { 

	//need options passed in!
	global $opts;
	global $regenerated;

	//create a new report object
	$report = new xmlReport(array(
		'startDate' => $rangeStart,
		'endDate' => $rangeEnd
	));

	//get variable filenames
	$localFile = $report->getPartnerFilename($partner, $reportName, $type, $iteration); 
	$xmlFile = $report->getXmlFilename();

	//debug!
	print date('Y-m-d H:i:s', strtotime('Now')) . "\n";
	print "Starting transform of $xmlFile -> $localFile\n" ;
	print "...loading $xmlFile\n";

	//whoops! no xml file, lets generate.  Or! if we want to force it to re-generate
	if((!file_exists($xmlFile) || isset($opts['f'])) && !isset($regenerated[$xmlFile])) { 
		$report->generateXml(true);
		$regenerated[$xmlFile] = true;
	}
	$report->doc->load($xmlFile);

	//make sure we have a directory to put our transform in
	$localDir = preg_replace('/[^\/]+$/','', $localFile) ;	
	if(!file_exists($localDir)) { 
		mkdir($localDir, 0777, true);
	}

	//finally do the transform, passing in our variable
	print "...transforming\n";
	$report->doTransform($reportName, $type, $localFile, array(
		'selectedPartner' => $partner
	));
	print "Finished transform to $localFile " . date('Y-m-d H:i:s', strtotime('Now')) . "\n\n";
}

function usage() {
	print "usage:
	php rawXml.php -s[start string]
	options:
	  -s Start date string in strtotime format
	  -e End date string in strtotime format (optional)
		-p Partner to generate the reports for
		-f Force XML files to regenerate when running
		\n";
	exit();
}
