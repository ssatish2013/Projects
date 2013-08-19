<?php
class gatewayInventory extends inventory{
	private $retailName = "Groupcard";

	public function setRetailName($retailName){
		$this->retailName = $retailName;
	}

	public static function getDateTime(){
		$tz = date_default_timezone_get();
		date_default_timezone_set("GMT");
		$m = microtime(true);
		$m -= floor($m);
		$m = substr(sprintf("%.3f",$m),1);
		$dateTime = preg_replace("/\+.*?$/",$m,date('c'))."Z";
		date_default_timezone_set($tz);
		return $dateTime;
	}

	private function buildUrl($method){
		return Env::getGatewayEndpoint().$this->getResourcePath($method);
	}

	private function getAccessId(){
		return settingModel::getSetting('inCommGateway', 'accessId');
	}

	private function getResourcePath($method){
		return "/v1/".$method;
	}

	private function getHeaders($method){
		$key = settingModel::getSetting('inCommGateway', 'accessKey');

		$dateTime = self::getDateTime();
		$contentType = "application/json";
		$resourcePath = $this->getResourcePath($method);

		$toSign = $dateTime.$contentType.$resourcePath;

		$signature = base64_encode(hash_hmac('sha1',$toSign,$key,true));

		$headers = array();
		$headers[] = "Accept-Language: en-us";
		$headers[] = "Authorization: Incomm ".base64_encode($this->getAccessId()).":".$signature;
		$headers[] = "Content-Type: $contentType";
		$headers[] = "Accept: application/json";
		$headers[] = "X-Incomm-DateTime: ".$dateTime;

		return $headers;
	}

	public function makeCall($method, gatewayMessage $gatewayMessage){
		// Until InComm fixes the gateway to allow us to repeat calls...
		// If we've already done this successfully, just return the successful message.
		$gatewayCall = null;

		if($this->gift){
			$gatewayCall = new gatewayCallModel();
			$gatewayCall->success=1;
			$gatewayCall->giftGuid = $this->gift->guid;
			$gatewayCall->method = $method;
			if($gatewayCall->load()){
				$responseMessage = new gatewayMessage(gatewayMessage::typeResponse);
				$responseMessage->setMessage($gatewayCall->responseMessage);
				return $responseMessage;
			}

			$gatewayCall = new gatewayCallModel();
			$gatewayCall->method = $method;
			if($this->gift){
				$gatewayCall->giftGuid = $this->gift->guid;
			}
			$gatewayCall->transactionID = $gatewayMessage->transactionID;
			$gatewayCall->dateTime = $gatewayMessage->dateTime;
		}

		$url = $this->buildUrl($method);

		$apiLog = new apiLogModel();
		$apiLog->startTime = microtime();
		$apiLog->url = $url;
		$apiLog->call = $method;
		$apiLog->input = json_decode($gatewayMessage->getMessage(),true);
		$apiLog->partner = globals::partner();
		$apiLog->apiPartner = 'InCommGateway';
		$apiLog->save();


		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		$headers = $this->getHeaders($method);
		$request = $gatewayMessage->getMessage();
		if($gatewayCall){
			$gatewayCall->requestMessage = $request;
			$gatewayCall->save();
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Make the request.
		log::info("Starting incomm gateway curl request to $url");
		$response = curl_exec($ch);
		if ($response === FALSE) {
			log::error("curl_request failed: url=$url, headers=".implode(',',$headers).", request=$request: " . curl_error($ch));
			throw new inventoryException("Gateway communication error.");
		}
		$responseInfo = curl_getinfo($ch);
		$statusCode = $responseInfo['http_code'];
		log::info("Gateway curl request completed. HTTP Status: $statusCode");
		if ($statusCode != 200) {
			log::error("curl_request failed: url=$url, headers=".implode(',',$headers).", request=$request, response=$response" . curl_error($ch));
			throw new inventoryException("Gateway communication error.");
		}
		curl_close($ch);


		if($gatewayCall){
			$gatewayCall->responseMessage = $response;
		}

		$responseMessage = new gatewayMessage(gatewayMessage::typeResponse);
		$responseMessage->setMessage($response);


		$apiLog->success=0;
		if($responseMessage->isSuccess()){
			$apiLog->success = 1;
			if($gatewayCall){
				$gatewayCall->success = 1;
			}
		}

		if($gatewayCall){
			$gatewayCall->save();
		}

		$apiLog->responseTime = microtime();
		$apiLog->response = json_decode($response,true);
		$apiLog->save();

		return $responseMessage;
	}

	public function activate(){
		$reservation = $this->product->getReservation($this->gift);

		$inventory = new inventoryModel();
		$inventory->giftId = $this->gift->id;
		$inventory->productId = $this->product->id;
		if($inventory->load()){
			if(!$inventory->activationTime){
				throw new inventoryException("Unexpected Error: Inventory served by gateway wasn't initially activated.");
			}
			if($inventory->deactivationTime){
				throw new inventoryException("Unexpected Error: The inventory has been deactivated and cannot be reactivated.");
			}
			return $inventory;
		}

		// If we didn't return inventory....

		promoHelper::setupRetailerForGift($this->gift, &$this);

		$promoLedgers = promoHelper::startLedgersForGift($this->gift, true);

		$ledger = new ledgerModel();
		$ledger->amount = $this->gift->paidAmount *-1;
		$ledger->giftId = $this->gift->id;
		$ledger->currency = $this->gift->currency;
		$ledger->type = ledgerModel::typeActivation;
		$ledger->startAudit();

		$inventory->activationTime = date("Y/m/d H:i:s");
		$inventory->activationMargin = $this->product->defaultMargin;
		$inventory->activationAmount = $this->gift->activationAmount;

		$feeLedger = new ledgerModel();
		$feeLedger->amount = $this->gift->paidAmount * ($inventory->activationMargin/100);
		$feeLedger->giftId = $this->gift->id;
		$feeLedger->currency = $this->gift->currency;
		$feeLedger->type = ledgerModel::typeActivationFee;
		$feeLedger->startAudit();


		// Begin Actual Call

		$request = new gatewayMessage(gatewayMessage::typeRequest);
		$request->amount = $this->gift->activationAmount;
		$request->currencyCode = $this->product->currency;
		$request->retailerName = $this->retailName;
		$request->dateTime = self::getDateTime();
		$request->transactionID = $this->gift->guid.randomHelper::guid();
		$request->upc = $this->product->upc;

		$response = $this->makeCall('requestActiveCode',$request);

		// End Actual Call


		if(!$response->isSuccess()){
			ledgerModel::removeLedger($ledger);
			ledgerModel::removeLedger($feeLedger);
			ledgerModel::removeAllLedgers($promoLedgers);
			new eventLogModel("incommGateway", 'gatewayResponse', 'activationFailure');
			throw new inventoryException("Error activating inventory.  Response code: $response->code");
		}
		new eventLogModel("incommGateway", 'gatewayResponse', 'activationSuccess');

		$inventory->pan=null;
		if($response->pan){
			$inventory->pan = $response->pan;
		}
		$inventory->pin = $response->pin;
		$auxData = new stdClass();
		$auxData->serialNum = $response->serialNum;
		$auxData->vendorSerialNumber = $response->vendorSerialNum;
		$auxData->authID = $response->authID;
		$auxData->enrichedData = $response->enrichedData;
		$auxData->code = $response->code;

		$enrichedData = explode("|",$response->enrichedData);
		foreach($enrichedData as $data){
			list($key,$value) = explode(":",$data);
			$auxData->$key = $value;
		}

		$inventory->auxData = $auxData;


		ledgerModel::saveAllLedgers($promoLedgers);

		$ledger->save();
		$feeLedger->save();
		$inventory->save();

		$reservation->inventoryId = $inventory->id;
		$reservation->save();

		return $inventory;

	}


	public function getInactive(){
		$reservation = $this->product->getReservation($this->gift);

		$inventory = new inventoryModel();
		$inventory->giftId = $this->gift->id;
		$inventory->productId = $this->product->id;
		if($inventory->load()){
			if(!$inventory->activationTime){
				throw new inventoryException("Unexpected Error: Inventory served by gateway wasn't initially activated.");
			}
			if($inventory->deactivationTime){
				throw new inventoryException("Unexpected Error: The inventory has been deactivated and cannot be reactivated.");
			}
			return $inventory;
		}

		// If we didn't return inventory....

		promoHelper::setupRetailerForGift($this->gift, &$this);

		// No ledgers for inactive product

		$inventory->activationTime = date("Y/m/d H:i:s");
		$inventory->activationMargin = $this->product->defaultMargin;
		$inventory->activationAmount = $this->gift->activationAmount;

		// Begin Actual Call

		$request = new gatewayMessage(gatewayMessage::typeRequest);
		$request->amount = $this->gift->getCreatorMessage()->amount;
		$request->currencyCode = $this->product->currency;
		$request->retailerName = $this->retailName;
		$request->dateTime = self::getDateTime();
		$request->transactionID = $this->gift->guid.randomHelper::guid();
		$request->upc = $this->product->upc;

		$response = $this->makeCall('requestInactiveCode',$request);

		// End Actual Call


		if(!$response->isSuccess()){
			new eventLogModel("incommGateway", 'gatewayResponse', 'activationFailure');
			throw new inventoryException("Error activating inventory.  Response code: $response->code");
		}
		new eventLogModel("incommGateway", 'gatewayResponse', 'activationSuccess');

		$inventory->pan=null;
		if($response->pan){
			$inventory->pan = $response->pan;
		}
		$inventory->pin = $response->pin;
		$auxData = new stdClass();
		$auxData->serialNum = $response->serialNum;
		$auxData->vendorSerialNumber = $response->vendorSerialNum;
		$auxData->authID = $response->authID;
		$auxData->enrichedData = $response->enrichedData;
		$auxData->code = $response->code;

		$enrichedData = explode("|",$response->enrichedData);
		foreach($enrichedData as $data){
			list($key,$value) = explode(":",$data);
			$auxData->$key = $value;
		}

		$inventory->auxData = $auxData;

		$inventory->save();

		$reservation->inventoryId = $inventory->id;
		$reservation->save();

		return $inventory;

	}



	public function deactivate(){
		$reservation = $this->product->getReservation($this->gift);

		$inventory = new inventoryModel();
		$inventory->giftId = $this->gift->id;
		$inventory->productId = $this->product->id;
		if($inventory->load()){
			if(!$inventory->activationTime){
				throw new inventoryException("Unexpected Error: Inventory served by gateway wasn't initially activated.");
			}
			if($inventory->deactivationTime){
				return $inventory;
			}
		}

		// If we didn't return inventory....

		promoHelper::setupRetailerForGift($this->gift, &$this);

		$promoLedgers = promoHelper::startLedgersForGift($this->gift, false);

		$ledger = new ledgerModel();
		$ledger->amount = $this->gift->paidAmount;
		$ledger->giftId = $this->gift->id;
		$ledger->currency = $this->gift->currency;
		$ledger->type = ledgerModel::typeDeactivation;
		$ledger->startAudit();

		$inventory->deactivationTime = date("Y/m/d H:i:s");


		$feeLedger = new ledgerModel();
		$feeLedger->amount = $this->gift->paidAmount * ($inventory->activationMargin/-100);
		$feeLedger->giftId = $this->gift->id;
		$feeLedger->currency = $this->gift->currency;
		$feeLedger->type = ledgerModel::typeDeactivationFee;
		$feeLedger->startAudit();


		// Begin Actual Call

		$request = new gatewayMessage(gatewayMessage::typeRequest);
		$request->amount = $this->gift->activationAmount;
		$request->currencyCode = $this->product->currency;
		$request->retailerName = $this->retailName;
		$request->dateTime = self::getDateTime();
		$request->transactionID = $this->gift->guid.randomHelper::guid();
		$request->upc = $this->product->upc;
		if(settingModel::getSetting('gatewayUsePanAsReturnCodeField', $this->product->upc)){
			$request->code = $inventory->pan;
		}
		//some partner's code for return is stored in $inventory->pin
		elseif(settingModel::getSetting('gatewayUsePinAsReturnCodeField', $this->product->upc)){
			$request->code = $inventory->pin;
		}
		else {
			$request->code = $inventory->auxData->code;
		}
		//if the gift subjected to refund hasn't been claimed, there will be no pan code from gateway
		//in this case, simply skip gateway call and keep all ledgers as if the call successed.
		if (!$request->code){
			log::info("No pan code found for this gift, returnActiveCode gateway call is skipped.");
			ledgerModel::saveAllLedgers($promoLedgers);
			$ledger->save();
			$feeLedger->save();
			$inventory->save();
			return $inventory;
		}
		$response = $this->makeCall('returnActiveCode',$request);

		// End Actual Call


		if(!$response->isSuccess(false)){
			ledgerModel::removeLedger($ledger);
			ledgerModel::removeLedger($feeLedger);
			ledgerModel::removeAllLedgers($promoLedgers);
			new eventLogModel("incommGateway", 'gatewayResponse', 'deactivationFailure');
			throw new deactivationException("Error deactivating inventory.  Response code: $responseCode");
		}
		new eventLogModel("incommGateway", 'gatewayResponse', 'deactivationSuccess');

		ledgerModel::saveAllLedgers($promoLedgers);

		$ledger->save();
		$feeLedger->save();
		$inventory->save();

		return $inventory;
	}
}
