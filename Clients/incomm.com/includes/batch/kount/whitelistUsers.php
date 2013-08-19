<?php
require_once(dirname(__FILE__)."/../../init.php");

Env::includeLibrary('kount/api/Rest');
Env::includeLibrary('kount/api/Rest/Email');

use KountApi\Rest\Email as KountApiEmail;

$opts = getopt("s:");

if(!isset($opts['s'])) { 
	print "
Whitelists users X days from the start date if they don't have a chargeback.
X days is the settings table under the variable 'timeUntilWhitelist'
Usage:
  whitelistUsers.php -s<start date>
  whitelistUsers.php -s\"-90 days\"\n\n";
	exit();
}


$end = date('Y-m-d H:i:s', strtotime($opts['s']));
$start = date('Y-m-d H:i:s', strtotime($opts['s'] . ' -26 hours'));

//first lets get all the processed transactions 
$SQL = "
SELECT id FROM `transactions`
WHERE
`externalTransactionId` IS NOT NULL AND
`refunded` IS NULL AND
`created` >= '$start' AND `created` <= '$end'
";

$result = db::query($SQL);
while($i = mysql_fetch_assoc($result)) { 

	//grab the transaction info
	$trans = new transactionModel($i['id']);

	//create a usermodel, and load it by email
	$user = new userModel();
	$user->getUserByEmail($trans->fromEmail);

	//if we haven't processed them before
	if($user->goodUser === null && $user->badUser === null) {

		//not marked yet, lets see if there's a chargeback
		$ledger = new ledgerModel();
		$ledger->shoppingCartId = $trans->shoppingCartId;
		$ledger->type = 'chargebackHold';

		//we were able to load, that means there's a chargeback
		if($ledger->load()) { 
			$user->badUser = date('Y-m-d H:i:s');
		}

		//unable to find a chargeback, looks good
		else {

			//add to kount
			$api = new KountApiEmail();
			$api->addEmail($user->email, 'approve');
			$user->goodUser = date('Y-m-d H:i:s');
		}

		//save good/bad user info
		$user->save();
	}
}
