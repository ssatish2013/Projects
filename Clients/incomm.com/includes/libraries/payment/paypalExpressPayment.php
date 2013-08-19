<?php

class paypalExpressPayment{

	public $response;

	function getCheckoutUrl(shoppingCartModel $shoppingCart){

		$returnURL = View::GetFullUrl('cart', 'paypalExpress', null);
		$cancelURL = View::GetFullUrl('cart', '', null);

		$nvpstr = '&AMT=' . $shoppingCart->getTotal() .
			'&L_DESC0=' . urlencode(languageModel::getString('giftCardName')) .
			'&L_AMT0=' . $shoppingCart->getTotal() .
			'&NOSHIPPING=2' .
			'&ALLOWNOTE=0' .
			'&RETURNURL=' . urlencode($returnURL) .
			'&CANCELURL=' . urlencode($cancelURL) .
			'&ALLOWEDPAYMENTMETHODTYPE=InstantPaymentOnly' .
			'&CURRENCYCODE=' . $shoppingCart->currency;

		/* Make the call to PayPal to set the Express Checkout token
		 If the API call succeded, then redirect the buyer to PayPal
		to begin to authorize payment.  If an error occured, show the
		resulting errors
		*/
		$resArray = $this->hash_call("SetExpressCheckout",$nvpstr);

		$ack = strtoupper($resArray["ACK"]);

		if($ack=="SUCCESS"){
			// Redirect to paypal.com here
			$token = urldecode($resArray["TOKEN"]);
			$shoppingCart->paypalExpressToken = $token;
			$shoppingCart->save();
			$location = settingModel::getSetting('paypal','paypalUrl') . '/cgi-bin/websrc?cmd=_express-checkout&token='. $token;
		} else  {
			//Take them back to the payment page to correct the error
			$location = View::GetUrl('cart', 'checkout');
		}
		log::debug("express url = " . $location);
		return $location;
	}

	function getDetails($token){
		$resArray = $this->hash_call("GetExpressCheckoutDetails","&TOKEN=$token");
		return $resArray;
	}

	function pay(transactionModel $transaction, shoppingCartModel $shoppingCart){

		$nvpHeader = '&TOKEN=' . $shoppingCart->paypalExpressToken .
			'&PAYERID=' . $shoppingCart->paypalExpressPayerId .
			'&PAYMENTACTION=SALE' .
			'&CURRENCYCODE=' . $shoppingCart->currency .
			'&AMT=' . $shoppingCart->getTotal() .
			'&DESC=' . urlencode(languageModel::getString('giftCardName'));

		$ledgers = array();
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

		$this->response = $this->hash_call("DoExpressCheckoutPayment",$nvpHeader);

		$ack = strtoupper($this->response["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){
			ledgerModel::saveAllLedgers($ledgers);
			new eventLogModel("paypal", strtoupper($transaction->currency), $transaction->amount);
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

	/**
	 * hash_call: Function to perform the API call to PayPal using API signature
	 * @methodName is name of API  method.
	 * @nvpStr is nvp string.
	 * returns an associtive array containing the response from the server.
	 */
	function hash_call($methodName,$nvpStr){

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
		"&PWD=".urlencode($this->settings->apiPassword).
		"&USER=".urlencode($this->settings->apiUsername);

		$nvpreq .= "&SIGNATURE=".urlencode($this->settings->signature).$nvpStr;

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);

		$apiLog = $this->saveApiLog($nvpreq);
		$response = curl_exec($ch);
		$this->updateApiLog($apiLog, $response);

		//convrting NVPResponse to an Associative Array
		$nvpResArray = paymentHelper::deformatNVP($response);

		if (curl_errno($ch)) {
			//Bad
		} else {
			curl_close($ch);
		}

		return $nvpResArray;
	}

	/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
	 * It is usefull to search for a particular key and displaying arrays.
	 * @nvpstr is NVPString.
	 * @nvpArray is Associative Array.
	 */

	public function saveApiLog($nvpstr){
		$nvpArray = paymentHelper::deformatNVP($nvpstr);
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
		$apiLog->responseTime = ($apiLog->startTime - microtime());
		$apiLog->success = 1;
		$apiLog->save();
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
		$feeLedger->type = 'refundFee';
		$feeLedger->shoppingCartId = $shoppingCart->id;
		$feeLedger->currency = $shoppingCart->currency;
		$feeLedger->startAudit();


		$this->response = $this->hash_call("RefundTransaction",$nvpstr);

		log::info("Paypal refund: " . print_r($this->response, 1));

		$ack = strtoupper($this->response["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING")  {
			new eventLogModel("refund", $transaction->currency, $transaction->amount);
			ledgerModel::saveAllLedgers($ledgers);
			$feeLedger->amount = $this->response['FEEREFUNDAMT'];
			$feeLedger->save();
		} else if($ack == 'FAILURE' || $ack == 'FAILUREWITHWARNING'){
			$ledges[] = $feeLedger;
			ledgerModel::removeAllLedgers($ledgers);
			throw new paymentRefundException('Refund failure');
		} else {
			// @TODO Send us a email, something we weren't looking for happened :-(
			// @TODO Jon just added a ledgerModel->removeAudit so use that
			$ledges[] = $feeLedger;
			ledgerModel::removeAllLedgers($ledgers);
			throw new paymentUnknownException('Unknown refund error');
		}
	}

	public function getAvsStreet() {
		return paymentHelper::AVS_STREET_UNAVAILABLE;
	}

	public function getAvsZip() {
		return paymentHelper::AVS_ZIP_UNAVAILABLE;
	}

	public function getCvv() {
		return paymentHelper::CVV_UNAVAILABLE;
	}

	public function getTransactionDetails($transactionId){

		$transaction = new transactionModel();
		$transaction->externalTransactionId = $transactionId;
		$transaction->load();
		$shoppingCart = new shoppingCartModel($transaction->shoppingCartId);

		$ledger = new ledgerModel();
		$ledger->type = 'paymentFee';
		$ledger->shoppingCartId = $shoppingCart->id;
		$ledger->currency = $shoppingCart->currency;
		$ledger->startAudit();

		$nvpstr="&TRANSACTIONID=$transactionId";
		$this->response = $this->hash_call("GetTransactionDetails",$nvpstr);

		$ledger->amount = -1 * $this->response['FEEAMT'];

		$ack = strtoupper($this->response["ACK"]);
		if($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING"){
			$ledger->save();
			return;
		} else if($ack == 'FAILURE' || $ack == 'FAILUREWITHWARNING'){
			ledgerModel::removeAllLedgers(array($ledger));
			throw new paymentGetDetailsException('getTransactionDetails Failure');
		} else {
			//@TODO Send us a email, something we weren't looking for happened :-(
			ledgerModel::removeAllLedgers(array($ledger));
			throw new paymentUnknownException('Unknown getTransactionDetails Error');
		}
	}
}
