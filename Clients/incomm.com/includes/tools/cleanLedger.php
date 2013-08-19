<?php
require_once(dirname(__FILE__)."/../init.php");

$opts = getopt("", array("type:"));

//grab the type ('activation', 'activationFee', etc)
if(!isset($opts['type'])) {
	print "\nYou need to specify a type to clean up\n";
	exit(0);
}
$type = $opts['type'];

//remove all entries except the earliest
print "\nRemoving all but the earliest entry of all duplicate types of '$type'\n\n";

//SQL to grab all of the ones with multiple entreis
$sql = "
	SELECT `giftId`, COUNT(`giftid`) AS `count`
	FROM `ledgers`
	WHERE 
	`type`='$type'
	GROUP BY (`giftId`)
	HAVING `count` > 1
";

$result = db::query($sql);
if(mysql_num_rows($result) == 0) { 
	print "No rows to clean up!\n";
	exit(0);
}

print "found " . mysql_num_rows($result) . " entries\n\n";

//give some time to contemplate...
sleep(5);

//populate our gift ids
$giftIds = array();
while($row = mysql_fetch_assoc($result)){
	$giftIds[$row['giftId']] = $row['count'];
}

//go through all the gift ids
foreach($giftIds as $giftId => $count) { 

	//load all the ledger entries associated with it
	$ledgers = ledgerModel::loadAll(array(
		"type" => $type,
		"giftId" => $giftId
	),null,"`timestamp` DESC");


	//loop through all entries 
	$cur = 1;
	foreach($ledgers as $ledger) { 

		//if we're not on the last one, delete the ledger
		if($cur < $count) { 

			//make sure there's an id to delete
			if($ledger->id !== null && $ledger->id > 0) { 
				$deleteSql = "DELETE FROM `ledgers` WHERE `id`=".$ledger->id;
				print "Deleting " . $ledger->giftId . " - $type -  " . $ledger->timestamp . "\n";
				db::query($deleteSql);
			}
		}

		//otherwise this is the last entry, output it to make sure we still have some dat
		else {
			print "Fixed " . $ledger->giftId . " - $type -  " . $ledger->timestamp . "\n\n";
		}
		
		//increment the current counter
		$cur++;
	}
}
