<?php
require_once(dirname(__FILE__)."/../../init.php");

Env::includeLibrary('kount/api/Rest');
Env::includeLibrary('kount/api/Rest/Email');

use KountApi\Rest\Email as KountApiEmail;

$opts = getopt("", array(
	"start:",
	"end:",
	"count:",
	"amount:",
	"refunds:",
	"chargebacks:",
	"mark",
	"csv",
	"api"
));

if(!isset($opts['start'])) { 
	print "
Whitelists users X days from the start date if they don't have a chargeback.
Usage:
  whitelistUsers.php -s<start date>
  whitelistUsers.php -s\"-90 days\"\n\n";
	exit();
}


//setup created part of the query
$start = date('Y-m-d H:i:s', strtotime($opts['start']));
$createdQuery = "`t`.`created` <= '$start' ";
if(isset($opts['end'])) { 
	$end = date('Y-m-d H:i:s', strtotime($opts['end']));
	$createdQuery .= " AND `t`.`created` >= '$end'";
}

$count = 3;
if(isset($opts['count'])) { 
	$count = $opts['count'];
}

$amount = 0;
if(isset($opts['amount'])) { 
	$amount = $opts['amount'];
}

$refunds = 5;
if(isset($opts['refunds'])) { 
	$refunds= $opts['refunds'];
}

$chargebacks = 0;
if(isset($opts['chargebacks'])) { 
	$chargebacks= $opts['chargebacks'];
}

$mark = false;
if(isset($opts['mark'])) { 
	$mark = true;
}

//first lets get all the processed transactions 
$SQL = "
SELECT COUNT(DISTINCT `t`.`id`) as numTxns, `t`.`fromEmailDigest`, SUM(`m`.`amount`) AS amount, `t`.`id`, `m`.`userId` as userId,
SUM(case when `t`.`refunded` IS NOT NULL then 1 else 0 end) as refunds,
SUM(case when `t`.`chargedback` IS NOT NULL then 1 else 0 end) as chargebacks
FROM `transactions` t
LEFT JOIN `messages` `m` ON `m`.`transactionId` = `t`.`id` 
LEFT JOIN `users` `u` ON `m`.`userId` = `u`.`id` 
WHERE
`u`.`badUser` IS NULL AND
`u`.`goodUser` IS NULL AND ".
$createdQuery . "
GROUP BY `t`.`fromEmailDigest`
HAVING COUNT(`t`.`fromEmailDigest`) > $count AND
SUM(`m`.`amount`) > $amount AND
SUM(case when `t`.`refunded` IS NOT NULL then 1 else 0 end) <= $refunds AND
SUM(case when `t`.`chargedback` IS NOT NULL then 1 else 0 end) <= $chargebacks
ORDER BY amount DESC;
";

//use the slave
$result = db::query($SQL, false);
$goodUsers = array();

//go through our results
while($i = mysql_fetch_assoc($result)) { 

	//get the user rom the query
	$txn = new transactionModel($i['id']);
	$user = new userModel($i['userId']);

	//add the user to our array of emails
	if($txn->fromEmail != '') { 
		$i['email'] = $txn->fromEmail;
		$goodUsers[] = $i;
	}

}

//if we want a csv
if(isset($opts['csv'])) { 
	print "TXNS,AMNT,USER,EMAL,RFND,CHBK\n";
	foreach($goodUsers as $user) { 
		print $user['numTxns'].','.$user['amount'].','.$user['userId'].','.$user['email'].','.
		$user['refunds'].",".$user['chargebacks']."\n";
	}
}

if(isset($opts['api'])) { 
	foreach($goodUsers as $user) { 
		//add to kount      
		$api = new KountApiEmail();
		$api->addEmail($user->email, 'approve');
		if(isset($opts['mark'])) { 
			$user = new userModel();
			$user->getUserByEmail($user->email);
			$user->goodUser = date('Y-m-d H:i:s');
			$user->save();
		}
	}
}
