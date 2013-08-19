<?php

class paypalPayment{

	const fakePaymentFailLastName = "Fail";

	public $response;

	public function pay($transaction){
		/* @var $transaction transactionModel */
		//skip authorize zero amount transaction
		//although pay method is not used anywhere for this plugin, just in case
		$isZero = ($transaction->amount == 0);
		// Month must be padded with leading zero
		$transaction->expirationMonth = str_pad($transaction->expirationMonth, 2, '0', STR_PAD_LEFT);

		/* Construct the request string that will be sent to PayPal.
		   The variable $nvpstr contains all the variables and is a
		   name value pair string with & as a delimiter */
		if(!$isZero){
			$nvpstr="&PAYMENTACTION=Sale" .
			"&EMAIL=" . urlencode($transaction->fromEmail) .
			"&AMT=" . urlencode($transaction->amount) .
			"&CREDITCARDTYPE=" . urlencode($transaction->ccType) .
			"&ACCT=" . urlencode($transaction->creditCard) .
			"&EXPDATE=" . urlencode($transaction->expirationMonth . $transaction->expirationYear) .
			"&CVV2=" . urlencode($transaction->cvv) .
			"&FIRSTNAME=" . urlencode($transaction->firstName) .
			"&LASTNAME=" . urlencode($transaction->lastName) .
			"&STREET=" . urlencode($transaction->address) .
			"&CITY=" . urlencode($transaction->city) .
			"&STATE=" . urlencode($transaction->state) .
			"&ZIP=" . urlencode($transaction->zip) .
			"&COUNTRYCODE=" . urlencode($transaction->country) .
			"&CURRENCYCODE=" . urlencode($transaction->currency);

			if($transaction->address2){
				$nvpstr .= "&STREET2=" . urlencode($transaction->address2);
			}
		}

		$ledgers = array();
		$shoppingCart = new shoppingCartModel($transaction->shoppingCartId);
		foreach ( messageModel::loadAll( $shoppingCart ) as $message) {
			/* @var $message messageModel */
			$ledger = new ledgerModel();
			$ledger->amount = $message->getDiscountedPrice();
			$ledger->type = ledgerModel::typePayment;
			$ledger->shoppingCartId = $shoppingCart->id;
			$ledger->messageId = $message->id;
			$ledger->currency = $shoppingCart->currency;
			$ledger->startAudit();
			$ledgers[] = $ledger;
		}

		//skip api call
		if ($isZero){
			ledgerModel::saveAllLedgers($ledgers);
			return;
		}
		$this->response = $this->hash_call("doDirectPayment",$nvpstr);

		$ack = strtoupper($this->response["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){
			ledgerModel::saveAllLedgers($ledgers);
			return;
		} else if($ack == 'FAILURE' || $ack == 'FAILUREWITHWARNING'){
			ledgerModel::removeAllLedgers($ledgers);
			throw new paymentException('Payment Failure');
		} else {
			//@TODO Send us a email, something we weren't looking for happened :-(
			ledgerModel::removeAllLedgers($ledgers);
			throw new paymentUnknownException('Unknown Payment Error');
		}
	}

	public function authorize(transactionModel $transaction){
		//skip authorize zero amount transaction
		if ($transaction->amount == 0) {
			//be consistent on logging
			new eventLogModel("authorization", strtoupper($transaction->currency), $transaction->amount);
			return;
		}
		// Month must be padded with leading zero
		$transaction->expirationMonth = str_pad($transaction->expirationMonth, 2, '0', STR_PAD_LEFT);

		/* Construct the request string that will be sent to PayPal.
		   The variable $nvpstr contains all the variables and is a
		   name value pair string with & as a delimiter */
		$nvpstr="&PAYMENTACTION=Authorization" .
		"&EMAIL=" . urlencode($transaction->fromEmail) .
		"&AMT=" . urlencode($transaction->amount) .
		"&CREDITCARDTYPE=" . urlencode($transaction->ccType) .
		"&ACCT=" . urlencode($transaction->creditCard) .
		"&EXPDATE=" . urlencode($transaction->expirationMonth . $transaction->expirationYear) .
		"&CVV2=" . urlencode($transaction->cvv) .
		"&FIRSTNAME=" . urlencode($transaction->firstName) .
		"&LASTNAME=" . urlencode($transaction->lastName) .
		"&STREET=" . urlencode($transaction->address) .
		"&CITY=" . urlencode($transaction->city) .
		"&STATE=" . urlencode($transaction->state) .
		"&ZIP=" . urlencode($transaction->zip) .
		"&COUNTRYCODE=" . urlencode($transaction->country) .
		"&CURRENCYCODE=" . urlencode($transaction->currency);

		if($transaction->address2){
			$nvpstr .= "&STREET2=" . urlencode($transaction->address2);
		}

		$this->response = $this->hash_call("doDirectPayment",$nvpstr);

		$ack = strtoupper($this->response["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){
			new eventLogModel("authorization", strtoupper($transaction->currency), $transaction->amount);
			return;
		} else if($ack == 'FAILURE' || $ack == 'FAILUREWITHWARNING'){
			throw new paymentException('Payment Failure');
		} else {
			throw new paymentUnknownException('Unknown Payment Error');
		}
	}

	public function capture(messageModel $message, transactionModel $transaction){
		$gift = new giftModel($message->giftId);

		/* Construct the request string that will be sent to PayPal.
		   The variable $nvpstr contains all the variables and is a
		   name value pair string with & as a delimiter */
		$nvpstr="&AUTHORIZATIONID=$transaction->authorizationId" .
		"&AMT=" . urlencode($transaction->amount) .
		"&CURRENCYCODE=" . urlencode($transaction->currency) .
		"&COMPLETETYPE=Complete";

		$ledgers = array();
		$shoppingCart = new shoppingCartModel($transaction->shoppingCartId);
		foreach ( messageModel::loadAll( $shoppingCart ) as $message) {
			/* @var $message messageModel */
			$ledger = new ledgerModel();
			$ledger->amount = $message->getDiscountedPrice();
			$ledger->type = ledgerModel::typePayment;
			$ledger->shoppingCartId = $shoppingCart->id;
			$ledger->messageId = $message->id;
			$ledger->currency = $shoppingCart->currency;
			$ledger->startAudit();
			$ledgers[] = $ledger;
		}
		//only do api call if amount>0
		if ($transaction->amount>0){
			$this->response = $this->hash_call("DoCapture",$nvpstr);
			$ack = strtoupper($this->response["ACK"]);
		}
		else{
			//fake response
			$ack = 'SUCCESS';
		}

		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){
			ledgerModel::saveAllLedgers($ledgers);
			new eventLogModel("payment", strtoupper($transaction->currency), $transaction->amount);
			return;
		} else if($this->response['L_ERRORCODE0'] == '10628'){
			// Paypal wants us to try that again... sucks but ok
			ledgerModel::removeAllLedgers($ledgers);
			$this->capture($message, $transaction);
		} else if($ack == 'FAILURE' || $ack == 'FAILUREWITHWARNING'){
			ledgerModel::removeAllLedgers($ledgers);
			throw new paymentException('Payment Failure');
		} else {
			//@TODO Send us a email, something we weren't looking for happened :-(
			ledgerModel::removeAllLedgers($ledgers);
			throw new paymentUnknownException('Unknown Payment Error');
		}
	}

	public function voidAuthorization(transactionModel $transaction){
		if ( $transaction->amount > 0 && substr($transaction->authorizationId, 0, 4) != "FAKE") {
			$nvpstr="&AUTHORIZATIONID=$transaction->authorizationId";
			$this->response = $this->hash_call("DoVoid",$nvpstr);
			$ack = strtoupper($this->response["ACK"]);
		}
		elseif (strtoupper($transaction->lastName) == strtoupper(self::fakePaymentFailLastName)) {
			$ack == 'FAILURE';  // Fail fake payment because of QA Lastname trigger
		}
		else $ack = "SUCCESS";  // Fake payment => SUCCESS

		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){
			new eventLogModel("authorizationVoid", "Void" . $transaction->currency, $transaction->amount);
			//$transaction->authorizationId = null;
			//$transaction->authorizationTime = null;
			//$transaction->save();
			log::info("Voided $transaction.");
		} else if($ack == 'FAILURE' || $ack == 'FAILUREWITHWARNING'){
			throw new paymentRefundException('DoVoid failure');
		} else {
			throw new paymentUnknownException('Unknown Payment Error');
		}
	}

	public function refund($transactionId){
		$transaction = new transactionModel();
		$transaction->externalTransactionId = $transactionId;
		$transaction->load();

		$nvpstr = '&TRANSACTIONID=' . $transactionId;

		$ledgers = array();
		$shoppingCart = new shoppingCartModel($transaction->shoppingCartId);
		foreach ( messageModel::loadAll( $shoppingCart ) as $message) {
			/* @var $message messageModel */
			$ledger = new ledgerModel();
			$ledger->amount = -1 * $message->getDiscountedPrice();
			$ledger->type = ledgerModel::typeRefund;
			$ledger->shoppingCartId = $shoppingCart->id;
			$ledger->messageId = $message->id;
			$ledger->currency = $shoppingCart->currency;
			$ledger->startAudit();
			$ledgers[] = $ledger;
		}

		$feeLedger = new ledgerModel();
		$feeLedger->type = ledgerModel::typeRefundFee;
		$feeLedger->shoppingCartId = $shoppingCart->id;
		$feeLedger->currency = $shoppingCart->currency;
		$feeLedger->startAudit();

		$this->response = $this->hash_call("RefundTransaction",$nvpstr);
		$ack = strtoupper($this->response["ACK"]);

		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING")  {
			new eventLogModel("refund", $transaction->currency, $transaction->amount);
			ledgerModel::saveAllLedgers($ledgers);
			$feeLedger->amount = $this->response['FEEREFUNDAMT'];
			$feeLedger->save();
			log::info("Refunded transaction $transaction.");
		} else if($ack == 'FAILURE' || $ack == 'FAILUREWITHWARNING'){
			$ledges[] = $feeLedger;
			ledgerModel::removeAllLedgers($ledgers);
			$errorCode = $this->response['L_ERRORCODE0'];
			$errorMessage = $this->response['L_LONGMESSAGE0'];
			throw new paymentRefundException("Refund paypal request failed: $errorCode - $errorMessage.");
		} else {
			// @TODO Send us a email, something we weren't looking for happened :-(
			// @TODO Jon just added a ledgerModel->removeAudit so use that
			$ledges[] = $feeLedger;
			ledgerModel::removeAllLedgers($ledgers);
			throw new paymentUnknownException('Unknown refund error');
		}
	}

	/**
	  * hash_call: Function to perform the API call to PayPal using API signature
	  * @methodName is name of API  method.
	  * @nvpStr is nvp string.
	  * returns an associtive array containing the response from the server.
	*/
	function hash_call($methodName,$nvpStr){

		//setting the curl parameters.
		$ch = curl_init();
		$paypalUrl = Env::main()->getPaypalEndpoint();
		curl_setopt($ch, CURLOPT_URL, $paypalUrl);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//NVPRequest for submitting to server
		$nvpreq="METHOD=".urlencode($methodName).
			"&VERSION=".urlencode('60.0').
			"&PWD=".urlencode($this->settings->apiPassword).
			"&USER=".urlencode($this->settings->apiUsername);

		$nvpreq .= "&SIGNATURE=".urlencode($this->settings->signature).$nvpStr;

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

		$apiLog = $this->saveApiLog($nvpreq);

		$response = curl_exec($ch);
		if ($response === false) {
			log::error("curl_exec() failed: url=$paypalUrl, request=$nvpreq: " . curl_error($ch));
			throw new paymentException("Paypal request failed.");
		}
		$this->updateApiLog($apiLog, $response);

		//convrting NVPResponse to an Associative Array
		$nvpResArray = paymentHelper::deformatNVP($response);

		if (curl_errno($ch)) {
			error_log(curl_error($ch));
		} else {
			curl_close($ch);
		}

		return $nvpResArray;
	}

	public function saveApiLog($nvpstr){
		$nvpArray = paymentHelper::deformatNVP($nvpstr);

		//remove sensitive data
		if(isset($nvpArray['ACCT'])) {
			$nvpArray['ACCT'] = 'REMOVED';
			$nvpArray['CVV2'] = 'REMOVED';
		}

		$apiLog = new apiLogModel();
		$apiLog->startTime = microtime();
		$apiLog->url = Env::main()->getPaypalEndpoint();
		$apiLog->call = $nvpArray['METHOD'];
		$apiLog->input = $nvpArray;
		$apiLog->partner = globals::partner();
		$apiLog->apiPartner = 'PayPal';
		$apiLog->save();
		return $apiLog;
	}

	public function updateApiLog($apiLog, $nvpResponse){
		/* @var $apiLog apiLogModel */
		$apiLog->response = paymentHelper::deformatNVP($nvpResponse);
		$apiLog->responseTime = (microtime()- $apiLog->startTime);
		$apiLog->success = 1;
		$apiLog->save();
	}

	public static function getAvsStreet($code) {
		switch ($code) {
			case 'A':
			case 'B':
			case 'D':
			case 'F':
			case 'X':
			case 'Y':
				return paymentHelper::AVS_STREET_MATCH;
			case 'U':
			case 'S':
			case 'R':
			case 'I':
			case 'G':
				return paymentHelper::AVS_STREET_UNAVAILABLE;
			default:
				return paymentHelper::AVS_STREET_NO_MATCH;
		}
	}

	public static function getAvsZip($code) {
		switch ($code) {
			case 'X':
			case 'Y':
			case 'Z':
			case 'W':
			case 'F':
			case 'D':
				return paymentHelper::AVS_ZIP_MATCH;
			case 'U':
			case 'S':
			case 'R':
			case 'I':
			case 'G':
				return paymentHelper::AVS_ZIP_UNAVAILABLE;
			default:
				return paymentHelper::AVS_ZIP_NO_MATCH;
		}
	}

	public function getCvv() {
		$code = $this->response['CVV2MATCH'];
		if ($code == 'M') {
			return paymentHelper::CVV_MATCH;
		}
		else if ($code == 'N') {
			return paymentHelper::CVV_NO_MATCH;
		}
		else {
			return paymentHelper::CVV_UNAVAILABLE;
		}
	}
}
