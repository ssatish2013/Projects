<?php

/* rawXml.php
 * This file generates a raw xml
 */
require_once(dirname(__FILE__)."/../../init.php");

/* Get options */
$regenerated = array();
$opts = getopt("s:e::p::t::x::");
$forceTransform = false;
$forceXml = false;

if(isset($opts['t'])) { 
	$forceTransform = true;
}

if(isset($opts['x'])) { 
	$forceXml = true;
}

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
		globals::forcePartnerRedirectLoaderForBatchScript($partner, null);  // Redirect loader doesn't matter for reports

		//get the report type for each partner
		$reports = settingModel::getPartnerSettings($partner, 'reportGenerateType');

		//go through each report by name
		foreach($reports as $reportName => $type) { 

			//grab the frequency we need to generate it at
			$frequency = explode(',',settingModel::getSetting('reportGenerateFreq',$reportName."Freq"));

			//daily
			if(in_array('daily', $frequency)) { 
				$rangeEnd = $rangeStart;
				ftpReport($rangeStart, $rangeEnd, $partner, $reportName, $type, 'daily');
			}

			//weekly
			if((date('l', strtotime($rangeStart)) == 'Sunday') && in_array('weekly', $frequency)) {
				$weekStart = date('Y-m-d', strtotime($rangeStart.' -6 Days'));
				ftpReport($weekStart, $rangeStart, $partner, $reportName, $type, 'weekly');
			}

			//monthly
			if((date('j', strtotime($rangeStart)) == date('t', strtotime($rangeStart))) && in_array('monthly', $frequency)) {
				$monthStart = date('Y-m-01', strtotime($rangeStart));
				ftpReport($monthStart, $rangeStart, $partner, $reportName, $type, 'monthly');
			}
		}
	}

	$rangeStart = date('Y-m-d', strtotime($rangeStart."+1 Day"));
}

print "PEAK MEMORY USAGE: " . memory_get_peak_usage() . "\n";
print "PEAK MEMORY USAGE: " . memory_get_peak_usage(true) . "\n\n";

function getSftp($partner) { 

	$conns = array();
	$ftp = settingModel::getPartnerSettings($partner, 'reportFtp');

	//if there's no ftp data set, return
	if(!isset($ftp['domain'])) { return false; }

	$domains = explode('|', $ftp['domain']);
	$usernames = explode('|', $ftp['username']);
	$passwords = explode('|', $ftp['password']);
	$remoteDirs = explode('|', $ftp['remoteDir']);

	foreach($domains as $i => $domain) { 
		$conn = ssh2_connect($domains[$i]);
		if(!ssh2_auth_password($conn, $usernames[$i], $passwords[$i])) { 
			print "UNABLE TO AUTH!\n";
			$conns[] = false;
		}
		else {
			$conns[] = $conn;
		}
	}

	$sftpConns = array();
	foreach($conns as $i => $conn) { 
		$sftpConns[] = array( 
			'conn' => ssh2_sftp($conn),
			'dir' => $remoteDirs[$i]
		);
	}
	return $sftpConns;
}
/*
 * Creates a report
 */
function ftpReport($rangeStart, $rangeEnd, $partner, $reportName, $type, $iteration) { 

	//need options passed in!
	global $opts;
	global $forceTransform;
	global $forceXml;
	global $regenerated;
	$remoteDir = settingModel::getSetting('reportFtp', 'remoteDir');

	//we need to fork this process because it has the potential to leave
	//a lot of memory left unfreed for a preiod of time
	$pid = pcntl_fork();

	if ($pid == -1) {
  	die('could not fork');
	} 

	//launch our parent process
	else if ($pid) {

		//wait for the child to return
    pcntl_waitpid($pid, $pid); //Protect against Zombie children

		//re-establish any of our connections that the child may have killed
		Env::getNewMasterDbConn();
		Env::getNewSlaveDbConn();
	} 

	//this is the child process, let's go!
	else {

	//create a new report object
	$report = new xmlReport(array(
		'startDate' => $rangeStart,
		'endDate' => $rangeEnd
	));

	//see if the xml file exists
	$xmlFile = $report->getXmlFilename();
	if(!file_exists($xmlFile) || $forceXml) {
		$report->generateXml(true);
	}

	//get variable filenames
	$localFile = $report->getPartnerFilename($partner, $reportName, $type, $iteration); 
	if(!file_exists($localFile) || $forceTransform) { 
		$report->doc->load($report->getXmlFilename());
		$report->doTransform($reportName, $type, $localFile, array(
			'selectedPartner' => $partner
		));
	}
	preg_match('/^.+?\/([^\/]+)$/', $localFile, $matches);
	$filename = $matches[1];

	//debug!
	print date('Y-m-d H:i:s', strtotime('Now')) . "\n";
	print "Starting ftp of $localFile\n -> $remoteDir$filename\n" ;
	try { 
		$sftps = getSftp($partner);
		if(!$sftps) { exit(0); }
		foreach($sftps as $sftp) { 

				if(!$sftp['conn']) { 
					print "Not setup for ftp\n";
					continue;
				}
				$remoteDir = $sftp['dir'];
				$conn = $sftp['conn'];

				$stream = fopen("ssh2.sftp://$conn/$remoteDir$filename", 'w');
				fwrite($stream, file_get_contents($localFile));
				fclose($stream);
		}

	}
	catch(Exception $e) { 
		print "EXCEPTION: $e\n";
	}

	//make sure we have a directory to put our transform in

	//finally do the transform, passing in our variable
	print "...sending\n";
	print "Finished ftp of $localFile " . date('Y-m-d H:i:s', strtotime('Now')) . "\n\n";

	//exit the child process, letting garbage collection happen for 
	//all of the xml, xsl, dom objects
	exit(0);
}}

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
