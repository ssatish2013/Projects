<?php

class ipnController {

	public function paypal(){
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		
		// Store each $_POST value in a NVP string: 1 string encoded and 1 string decoded
		foreach ($_POST as $key => $value){
			$value = urlencode(stripslashes($value));
			$req .= "&" . $key . "=" . $value;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, ENV::main()->getPaypalIpnEndpoint());
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

		$response = curl_exec($ch);

		if($response == 'VERIFIED'){
			$data = paymentHelper::deformatNVP($req);
			
			$ipn = new ipnModel();
			// Surpress warnings because some calls don't have all these values
			$ipn->transactionId = @$data['txn_id'];
			$ipn->parentTransactionId = @$data['parent_txn_id'];
			$ipn->paymentStatus = @$data['payment_status'];
			$ipn->reasonCode = @$data['reason_code'];
			$ipn->txnType = @$data['txn_type'];
			$ipn->data = json_encode($data);
			$ipn->save();
			
			if (strtolower(@$data['payment_status']) == 'completed') {
				// Mark the transaction as completed (only affects PayPal eCheck transactions)
				ipnHelper::completed ($data);
			} else if (strtolower(@$data['payment_status']) == 'failed' || strtolower(@$data['payment_status']) == 'denied') {
				// Transaction failed (only PayPal eCheck transactions)
				ipnHelper::failed ($data);
			} else if (strtolower(@$data['payment_status']) == 'refunded') {
				// Transaction refunded (need to capture if initiated via the PayPal web interface)
				ipnHelper::refunded ($data);
			} else if((strtolower(@$data['payment_status']) == 'reversed') && (strtolower($data['reason_code'] == 'chargeback'))){
				// Handle the chargeback notice, this is just a "hold" on the funds
				ipnHelper::chargebackNotice($data);
			} else if((strtolower(@$data['payment_status']) == 'canceled_reversal') && (strtolower($data['reason_code'] == 'chargeback'))){
				//Handle the release of fund after a chargeback, this is the release of those funds
				ipnHelper::cancelReversal($data);
			}
			
			if(strtolower(@$data['reason_code']) == 'chargeback_settlement'){
				//Handle the fee for the chargeback, this is where the payment is refunded and fees are added
				ipnHelper::chargeback($data);
			}
			
			if(strtolower(@$data['txn_type']) == 'new_case'){
				// flag the transaction as 'hot'
				ipnHelper::newCase($data);
			}
		}
	}

	function kount() {
		if(($xmlData = file_get_contents('php://input')) === false) {
			throw new Exception("No input data available.");
		}
		if (($xml = simplexml_load_string($xmlData)) === false) {
			throw new Exception("Failed to parse XML: $xmlData: " . print_r(libxml_get_errors(), true));
		}
		log::debug("Kount notification request:\n$xmlData");
		foreach($xml->event as $event) {
			kountIpnModel::addEvent($event);
			if($event->name == kountHelper::STATUS_EDIT) { 
				kountHelper::ipnStatusEdit($event);
			} else if($event->name == kountHelper::NOTES_ADD) {
				kountHelper::ipnStatusNotes($event);
			}
		}
	}
}
