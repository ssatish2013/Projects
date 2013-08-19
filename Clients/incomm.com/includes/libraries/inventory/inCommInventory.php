<?php
class inCommInventory extends inventory{
	private $terminalId = "12522001";
	private $locationId = "12522GroupCard";
	
	public function setTerminalId($terminalId){
		$this->terminalId = $terminalId;
	}
	
	public function setLocationId($locationId){
		$this->locationId = $locationId;
	}
	
	private function getMessage($inventory){
		$traceAudit = new traceAuditModel();
		$traceAudit->created = date("Y/m/d H:i:s"); // we have to change something for save to work
		$traceAudit->save();
		
		$settings = settingModel::getPartnerSettings(null, 'inventoryPlugins');
		if(isset($settings['inCommTraceAuditIdShift'])){
			$traceAuditIdShift = $traceAudit->id + $settings['inCommTraceAuditIdShift'];
		} else {
			$traceAuditIdShift = $traceAudit->id + 900000000000;
		}
		
		
		
		$xmlMessage = new inCommXMLMessage();
		
		// Message Type Indicator (MTI)
		$xmlMessage->setField(0, "0200"); // For Activation and Deactivation
		
		// Primary Account Number
		$xmlMessage->setField(2, $inventory->pan);
		
		$txnAmount = $this->gift->activationAmount;
		
		// Transaction Amount (In Pennies)
		$txnAmount *=100;
		$txnAmount = str_pad($txnAmount,12,"0",STR_PAD_LEFT);
		$xmlMessage->setField(4,$txnAmount);
		
		$originalTZ = date_default_timezone_get();
		date_default_timezone_set("GMT");
		// Transmission Date & Time
		$xmlMessage->setField(7,date("Ymd")."T".date("His")."Z"); // Transaction time in CCYYMMDDThhmmssZ format)
		
		// System Trace Audit Number
		$xmlMessage->setField(11, $traceAuditIdShift);
		
		date_default_timezone_set("America/Chicago");
		// Local Transaction Time
		$xmlMessage->setField(12, date("HisO"));
		// Local Transaction Date
		$xmlMessage->setField(13, date("Ymd"));
		date_default_timezone_set($originalTZ);
		
		
		// Terminal ID
		$xmlMessage->setField(41,$this->terminalId);
		$inventory->terminalId=$this->terminalId;
		
		// ID Code (Location)
		$xmlMessage->setField(42,$this->locationId);
		$inventory->locationId=$this->locationId;
		
		// Currency Code
		$xmlMessage->setField(49, $inventory->getProduct()->currency);
		
		// Product Data (UPC)
		$xmlMessage->setField(54, $inventory->getProduct()->upc);
		
		return array($xmlMessage, $traceAudit);
	}
	
	private function makeCall($xmlMessage, $traceAudit){
		$traceAudit->beforeRequest($xmlMessage->getMessage());
		
		$ch = curl_init();
		$pluginSettings = settingModel::getPartnerSettings(null, 'inventoryPlugins');
		
		$endpoint = settingModel::getSettingRequired('inventoryPlugins', 'inCommEndpoint');
		
		
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		
		$request = $xmlMessage->getMessage();
		
		$apiLog = new apiLogModel();
		$apiLog->startTime = microtime();
		$apiLog->url = $endpoint;
		$apiLog->call = $xmlMessage->getField(3);
		$apiLog->input = $xmlMessage->getFieldsArray();
		$apiLog->partner = globals::partner();
		$apiLog->apiPartner = 'InCommXML';
		$apiLog->save();
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("msg"=>base64_encode($request))));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$msg = curl_exec($ch);
		
		$msg = base64_decode($msg);
		
		$result = new inCommXMLMessage();
		$result->setMessage($msg);
		
		$apiLog->responseTime = microtime();
		$apiLog->response = $result->getFieldsArray();
		$apiLog->save();
		
		$traceAudit->afterRequest($result->getMessage(),$result->getField(39));
		
		return $result;
	}
	
	public function activate(){
		$reservation = $this->product->getReservation($this->gift);
		$inventory = $reservation->getInventory();
		if($inventory->activationTime){
			return $inventory;
		}
		
		if($inventory->activationAttemptTime && time()-strtotime($inventory->activationAttemptTime)<60){
			// If they've attempted this within the past 60 seconds, slow down
			throw new inventoryException("Error activating inventory.  Too many activation attempts.");
		}
		
		$inventory->activationAttemptTime = date("Y/m/d H:i:s");
		$inventory->save();
		
		promoHelper::setupLocationForGift($this->gift, $this);
		$promoLedgers = promoHelper::startLedgersForGift($this->gift, true);
		
		$ledger = new ledgerModel();
		$ledger->amount = $this->gift->paidAmount * -1;
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
		
		
		
		list($xmlMessage, $traceAudit) = $this->getMessage($inventory);
		// Processing code
		$xmlMessage->setField(3, "189090"); // Card activation
		$result = $this->makeCall($xmlMessage, $traceAudit);
		
		$responseCode = $result->getField(39);
		
		if($responseCode != "00" && $responseCode != "21"){
			ledgerModel::removeLedger($ledger);
			ledgerModel::removeLedger($feeLedger);
			ledgerModel::removeAllLedgers($promoLedgers);
			new eventLogModel("incommGateway", 'xmlResponse', 'activationFailure');
			throw new inventoryException("Error activating inventory.  Response code: $responseCode");
		}
		new eventLogModel("incommGateway", 'xmlResponse', 'activationSuccess');
		
		ledgerModel::saveAllLedgers($promoLedgers);
		
		$ledger->save();
		$feeLedger->save();
		$inventory->save();
		
		$reservation->inventoryId = $inventory->id;
		$reservation->save();
		
		return $inventory;
	}
	
	public function deactivate(){
		$inventory = $this->product->getReservation($this->gift)->getInventory();
		if(!$inventory->activationTime || $inventory->deactivationTime){
			return $inventory;
		}
		
		promoHelper::setupLocationForGift($this->gift, $this);
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
		
		
		list($xmlMessage, $traceAudit) = $this->getMessage($inventory);
		// Processing code
		$xmlMessage->setField(3, "289090"); // Card deactivation
		$result = $this->makeCall($xmlMessage, $traceAudit);
		
		$responseCode = $result->getField(39);
		
		if($responseCode != "00" && $responseCode != "21"){
			ledgerModel::removeLedger($ledger);
			ledgerModel::removeLedger($feeLedger);
			new eventLogModel("incommGateway", 'xmlResponse', 'deactivationFailure');
			throw new deactivationException("Error deactivating inventory.  Response code: $responseCode");
		}
		
		new eventLogModel("incommGateway", 'xmlResponse', 'deactivationSuccess');
		
		ledgerModel::saveAllLedgers($promoLedgers);
		$ledger->save();
		$feeLedger->save();
		$inventory->save();
		
		return $inventory;
	}
	
	public function reverse(){
		
	}
}
