<?php
require_once(dirname(__FILE__)."/../init.php");


//global vars
$fix = false;

//grab our options
$opts = getopt("", array("promo:","file:","fix"));

if(count($opts) == 0) { 
	usage();
	exit(0);
}

//see if there's a file
if(!isset($opts['file'])) { 
	usage();
	die("you must specify a filename\n");
}

//see if we're fixing it
if(isset($opts['fix'])) { 
	$fix = true;
}

//setup our input kount file to compare against (required)
$kountFile = dirname(__FILE__)."/".$opts['file'];
print "Grabbing file $kountFile";
if(($handle = fopen($kountFile, "r")) === FALSE) { 
	useage();
	die("Could not open $kountFile");
};


//grab the trigger for this actual promotion
preg_match('/.+?CODE: (.+?)\)/', $opts['promo'], $matches);
$trigger = new promoTriggerModel();
$trigger->pluginData = '["'.strtolower($matches[1]).'"]';
if(!$trigger->load('pluginData')) {
	useage();
	die('could not find a promo with a plugin matching  ' . '["'.strtolower($matches[1]).'"]');
}
$promoModel = new promoModel($trigger->promoId);

//grab headers for the files
$headerPos = fgetcsv($handle);
$headers = array();
foreach($headerPos as $pos => $header) { 
	$headers[$header] = $pos;
}

print "\nFinding promos matching: " . $opts['promo'] . "\n";
if($fix) { 
	print "and fixing them!\n";
}

//not fixing, no need to slam prod
else { 
	Env::getNewSlaveDbConn();
}


$promos = array();
$missingActivations = array();
$missingDeactivations = array();
$missingTransactions = array();
$fixedActivations = array();
$fixedDeactivations = array();
$fixedTransactions = array();
while($data = fgetcsv($handle)) { 
	if(!isset($data[$headers['PROMOS']])) { continue; }
	$promo = $data[$headers['PROMOS']]; 

	if(
		$promo == '' || 
		$promo != $opts['promo'] || 
		$data[$headers['AUTH']] != 'A' 
		//$data[$headers['PTYP']] != 'PYPL'
	) { continue;}

	$msgIds = explode(',', $data[$headers['MESSAGEIDS']]);
	$cartId = $data[$headers['ORDR']];

	//see if this has a promo ledger entry
	foreach($msgIds as $msgId) { 

		$message = new messageModel($msgId);

		//see if there's a proper transaction for it
		$promoTransaction = new promoTransactionModel();
		$promoTransaction->promoId = $trigger->promoId;
		$promoTransaction->messageId = $msgId;
		$promoTransaction->promoTriggerId = $trigger->id;

		//try to load
		if(!$promoTransaction->load('promoId,messageId,promoTriggerId')) { 

			//couldn't load, lets save it
			$promoTransaction->discountAmount = $message->amount * ($promoModel->discountPercent/100);
			if($fix) { $promoTransaction->save(); $fixedTransactions[] = $msgId;}

			//just keeping track of how many
			$missingTransactions[] = $msgId;
		}

		//grab the inventory
		$inventory = new inventoryModel();
		$inventory->giftId = $message->giftId;

		//not activated so it can't be deac'd or in the ledger.  Next!
		if(!$inventory->load('giftId') || $inventory->activationTime === null) { continue; }

		//grab the gift info for it
		$gift = new giftModel($message->giftId);

		//see if there's a proper ledger for it
		$ledger = new ledgerModel;
		$ledger->messageId = $msgId;
		$ledger->giftId = $message->giftId;
		$ledger->type = 'promoActivation';

		//couldn't load it
		if(!$ledger->load('type,messageId,giftId')) { 

			//save it (insertLedger because we can't set the timestamp using our model)
			$ledger->currency = $gift->currency;
			$ledger->amount = $promoTransaction->discountAmount;
			if($fix) { insertLedger($ledger, $inventory->activationTime); $fixedActivations[] = $msgId;}
			$missingActivations[] = $msgId;
		}

		
		if($inventory->deactivationTime === null) { continue; }
		$ledger = new ledgerModel;
		$ledger->messageId = $msgId;
		$ledger->giftId = $message->giftId;
		$ledger->type = 'promoDeactivation';
		if(!$ledger->load('type,messageId,giftId')) { 
			//print "added $msgId from cart $cartId\n";
			$ledger->currency = $gift->currency;
			$ledger->amount = -1*$promoTransaction->discountAmount;
			if($fix) { insertLedger($ledger, $inventory->deactivationTime); $fixedDeactivations[] = $msgId;}
			$missingDeactivations[] = $msgId;
		}
	}
}


print count($missingTransactions) . "  bad transactions found\n";
print count($missingActivations) . "  bad activations found\n";
print count($missingDeactivations) . "  bad deactivations found\n";

if($fix) { 
	print count($fixedTransactions) . "  bad transactions fixed\n";
	print count($fixedActivations) . "  bad activations fixed\n";
	print count($fixedDeactivations) . "  bad deactivations fixed\n";
}


function insertLedger($ledger, $timestamp) {
    $SQL = '
    INSERT INTO `ledgers`
    (`type`, `giftId`, `messageId`, `amount`, `currency`, `timestamp`)
    VALUES
    ("'.$ledger->type.'",'.$ledger->giftId.','.$ledger->messageId.','.$ledger->amount.',"'.$ledger->currency.'","'.$timestamp.'")
';
  db::query($SQL);
}

function usage() { 
print "

usage:
	php includes/tools/fixPromosFromKount.php [options]

options:
	--file  - Path to the kount csv file generated (from their reporting system) relative to the tools dir
	--promo - the name of the promotion in the Kount UDF field 'PROMOS'
	--fix   - (optional) if specified it will fix the txns, otherwise it will tell you what it's going to fix
\n";
}
