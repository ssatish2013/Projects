<?php
require_once(dirname(__FILE__)."/../init.php");

$opts = getopt("", array("start:","end:"));

//setup dates to lookup
if(!isset($opts['start'])) {
	print "\nYou need to specify a start date\n";
	exit(0);
}
if(!isset($opts['end'])) {
	print "\nYou need to specify a end date\n";
	exit(0);
}

//getting dates from strings
$start = date('Y-m-d H:i:s', strtotime($opts['start']));
$end = date('Y-m-d H:i:s', strtotime($opts['end']));

//remove all entries except the earliest
print "\nChecking Transactions from $start to $end\n\n";

//SQL to grab all of the ones with multiple entreis
$sql = "
	SELECT `id`, `authorizationId`, `paymentMethodId` 
	FROM `transactions`
	WHERE 
	`authorizationId` IS NOT NULL AND
	`externalTransactionId` IS NULL AND
	`ccType` != 'Paypal' AND
	`created` >= '$start' AND 
	`created` <= '$end'
";

$result = db::query($sql);
if(mysql_num_rows($result) == 0) { 
	print "No rows to fix!\n";
	exit(0);
}

print "found " . mysql_num_rows($result) . " entries\n\n";

//give some time to contemplate...
sleep(5);

//populate our gift ids
$authIds = array();
while($row = mysql_fetch_assoc($result)){
	$authIds[] = array(
		'txnId' => $row['id'],
		'authId' => $row['authorizationId'],
		'method' => new paymentMethodModel($row['paymentMethodId'])
	);
}

//go through all the gift ids
foreach($authIds as $i => $obj) { 

	//setup our query string, this works for both GetTransactionDetails and TransactionSearch
	$nvpstr="&TRANSACTIONID=" . $obj['authId'];
	$nvpstr.="&STARTDATE=" . $start;
	$nvpstr.="&ENDDATE=" . $end;

	//make the transaction call
  $response = hash_call("GetTransactionDetails",$nvpstr, $obj['method']);

	//if the auth call is completed, it means we cashed it in
	if(strtoupper($response['PAYMENTSTATUS']) == 'COMPLETED') {

		//search for tha authorization id
  	$data = hash_call("TransactionSearch",$nvpstr, $obj['method']);

		//the first txn will be the transaction id, but we'll also make sure it's not the auth id
		if(isset($data['L_TRANSACTIONID0']) && $data['L_TRANSACTIONID0'] != $obj['authId']) { 
			$extTxnId = $data['L_TRANSACTIONID0'];

			//save the transaction
			$txn = new transactionModel($row['txnId']);
			$txn->externalTransactionId = $extTxnId;
			$txn->save();

			//mark the cart as complete if we didn't already
			$cart = new shoppingCartModel($txn->id);
			
			//first argument (response) isn't used... weird
			$cart->transactionComplete($txn);
		}
	}
}




/**
  * hash_call: Function to perform the API call to PayPal using API signature
  * @methodName is name of API  method.
  * @nvpStr is nvp string.
  * returns an associtive array containing the response from the server.
*/
function hash_call($methodName,$nvpStr, $method){

  //setting the curl parameters.
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,Env::main()->getPaypalEndpoint());
  curl_setopt($ch, CURLOPT_VERBOSE, 1);

  //turning off the server and peer verification(TrustManager Concept).
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch, CURLOPT_POST, 1);

  //NVPRequest for submitting to server
  $nvpreq="METHOD=".urlencode($methodName).
    "&VERSION=".urlencode('60.0').
    "&PWD=".urlencode($method->settings->apiPassword).
    "&USER=".urlencode($method->settings->apiUsername);

  $nvpreq .= "&SIGNATURE=".urlencode($method->settings->signature).$nvpStr;

  //setting the nvpreq as POST FIELD to curl
  curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

  //$apiLog = $->saveApiLog($nvpreq);
  $response = curl_exec($ch);
  //$this->updateApiLog($apiLog, $response);

  //convrting NVPResponse to an Associative Array
  $nvpResArray = paymentHelper::deformatNVP($response);

  if (curl_errno($ch)) {
	log::error("Curl call failed: " . curl_error($ch));
  } else {
    curl_close($ch);
  }

  return $nvpResArray;
}

