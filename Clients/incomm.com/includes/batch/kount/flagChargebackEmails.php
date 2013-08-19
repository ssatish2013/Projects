<?php
require_once(dirname(__FILE__)."/../../init.php");

Env::includeLibrary('kount/api/Rest');
Env::includeLibrary('kount/api/Rest/Email');

use KountApi\Rest\Email as KountApiEmail;

$opts = getopt("", array());
$ledger = new ledgerModel();
$ledger->type = 'chargebackHold';
$api = new KountApiEmail();

//go through all chargebacks
foreach(ledgerModel::loadAll(array('type' => 'chargebackHold')) as $chbkLedger) { 
	$shoppingCartIds[] = $chbkLedger->shoppingCartId;
};
$shoppingCartIds = array_unique($shoppingCartIds);

//go through shoppingCarts
$emails = array();
foreach($shoppingCartIds as $shoppingCartId) { 
	$cart = new shoppingCartModel($shoppingCartId);
	$transaction = new transactionModel();
	$transaction->shoppingCartId = $shoppingCartId;
	$transaction->load();

	$emails[] = $transaction->fromEmail;

	foreach($cart->getAllGifts() as $gift) { 
		$emails[] = $gift->recipientEmail;
		foreach($gift->getAllMessages() as $message) { 
			$user = new userModel($message->userId);
			$emails[] = $user->email;
		}
	}
}

$emails = array_unique($emails);

foreach($emails as $email) { 
	if($email == '') { continue; }
	print "Adding $email\n";
	$api->addEmail($email, 'decline');
}
print "\n";

