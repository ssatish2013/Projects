<?php
require_once(Env::main()->includePath() . '/libraries/kount/src/autoload.php');

class kountHelper {

	const STATUS_EDIT = "WORKFLOW_STATUS_EDIT";
	const NOTES_ADD = "WORKFLOW_NOTES_ADD";
	
	const APPROVE = 'A';
	const REVIEW = 'R';
	const ESCALATE = 'E';
	const DECLINE = 'D';

	/**
	 * Process an edit notifcation from kount. We only process two events at the moment, a review, or decline of an order.
	 */
	public static function ipnStatusEdit($event) { 
		$keyAttrs = $event->key->attributes();
		$value = (string) $event->new_value;
		$agent = (string) $event->agent;
		$shoppingCartId = $keyAttrs['order_number'];
		$transactionId = (string) $event->key;
		
		$transaction = new transactionModel();
		$transaction->shoppingCartId = $shoppingCartId;
		
		if (!$transaction->load()){ 
			throw new exception ("transaction id is invalid for shoppingCartId:$shoppingCartId");
			return;
		}
		
		log::info("Processing Kount notification: " . print_r($event, true));
		
		//if the shopping cart isn't a complete int
		//i.e. 1178D in the case of a decline, let's just return
		if(preg_match('/[^0-9]/',$shoppingCartId)) {
			return;
		} else {
			//otherwise we need to int up the shopping cart
			$shoppingCartId = (int) $shoppingCartId;
		}
		
		$cart = new shoppingCartModel($shoppingCartId);
		new eventLogModel("screen", "kountReview", $value, $cart->partner);
		
		if($value == kountHelper::APPROVE) { 
			screeningHelper::approveCart($shoppingCartId, $agent);
		}
		
		else if($value == kountHelper::DECLINE) { 
			screeningHelper::rejectCart($shoppingCartId, $agent);
		}
		
		$fraud = new fraud($transaction, $cart, null);
		$kount = new Kount($fraud);
		$kount->doUpdate($transactionId);
	}
	
	public static function saveApiLog($nvpArray, $method){ 
		log::debug('Successfully hit ' . get_class(self) . ': saveApiLog() method!');

		//remove sensitive data
		if(isset($nvpArray['ACCT'])) {
			$nvpArray['ACCT'] = 'REMOVED';
			$nvpArray['CVV2'] = 'REMOVED';
		}
		
		$apiLog = new apiLogModel();
		$apiLog->startTime = microtime();
		$apiLog->url = Env::main()->getKountEndPoint();
		$apiLog->call = $method;
		$apiLog->input = $nvpArray;
		$apiLog->partner = globals::partner();
		$apiLog->apiPartner = 'Kount';
		$apiLog->save();
		return $apiLog;
	}
	
	public static function updateApiLog($apiLog, $nvpResponse){ 
		/* @var $apiLog apiLogModel */
		$apiLog->response = $nvpResponse;
		$apiLog->responseTime = (microtime()- $apiLog->startTime);
		$apiLog->success = 1;
		$apiLog->save();
	}
	
	public static function ipnStatusNotes($event) { 
		$keyAttrs = $event->key->attributes();
		$value = (string) $event->new_value;
		$agent = (string) $event->agent;
		$shoppingCartId = $keyAttrs['order_number'];
		
		log::info("Processing Kount notification: " . print_r($event, true));
		
		//if the shopping cart isn't a complete int
		//i.e. 1178D in the case of a decline, let's just return
		if(preg_match('/[^0-9]/',$shoppingCartId)) {
			return;
		} else {
			//otherwise we need to int up the shopping cart
			$shoppingCartId = (int) $shoppingCartId;
		}
		
		if( (strtolower($agent) != "system@kount.com") && (strtolower($agent) != "system@kount.net") && (strtolower($agent) != "system@kount.ca") ) { 
			screeningHelper::noteCart($shoppingCartId, $value);
		}
	}
	
	public static function chargebackNotice($shoppingCartId) {
		$cart = new shoppingCartModel($shoppingCartId);
		$transaction = $cart->getTransaction();
		$fraudLog = dbMongo::findOne('fraudLogs', array('shoppingCartId' => $cart->id));
		$update = new Kount_Ris_Request_Update();
		// Set up Kount RIS API endpoint, certificate, key and passphrase
		self::_configureRequest($update);
		@$updateData = $fraudLog['data']['Kount'];

		if (isset($updateData)) { 
			$update->setSessionId($updateData['SESS']);
			$update->setTransactionId($updateData['TRAN']);
			$update->setOrderNumber($shoppingCartId);

			//was authorized at some point
			$update->setMack('N');
			$update->setAuth('A');

			$update->setRefundChargeback('C');
			$response = $update->getResponse();
			$status = $response->getErrorCode();

			// Log Kount RIS update response and status
			log::info("Kount RIS update for chargeback:\n-status: {$status}\n-response:\n{$response}\n");
		}
	}

	public static function refund($shoppingCartId) {
		$cart = new shoppingCartModel($shoppingCartId);
		$transaction = $cart->getTransaction();
		$fraudLog = dbMongo::findOne('fraudLogs', array('shoppingCartId' => $cart->id));
		$update = new Kount_Ris_Request_Update();
		// Set up Kount RIS API endpoint, certificate, key and passphrase
		self::_configureRequest($update);
		@$updateData = $fraudLog['data']['Kount'];

		if (isset($updateData)) { 
			$update->setSessionId($updateData['SESS']);
			$update->setTransactionId($updateData['TRAN']);
			$update->setOrderNumber($shoppingCartId);

			//was authorized at some point
			$update->setMack('N');
			$update->setAuth('A');

			$update->setRefundChargeback('R');
			$response = $update->getResponse();
			$status = $response->getErrorCode();

			// Log Kount RIS update response and status
			log::info("Kount RIS update for refund:\n-status: {$status}\n-response:\n{$response}\n");
		}
	}

	private static function _configureRequest(Kount_Ris_Request $request) {
		$certPrefix = settingModel::getSettingRequired('kountConfig', 'certificateName');
		$request->setUrl(settingModel::getSettingRequired('kountConfig', 'Url'));
		$request->setCertificate(
			'/media/ram/' . $certPrefix . '_cert.pem',
			'/media/ram/' . $certPrefix . '_key.pem',
			settingModel::getSetting('kountConfig', 'certificatePassword')
		);
	}

}
