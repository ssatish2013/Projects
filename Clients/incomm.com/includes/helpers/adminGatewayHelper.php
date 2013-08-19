<?php
class adminGatewayHelper{
	public static function requestActiveCode(){
		$request = new gatewayMessage(gatewayMessage::typeRequest);
		$request->amount = request::unsignedPost('amount');
		$request->currencyCode = request::unsignedPost('currency');
		$request->retailerName = request::unsignedPost('retailerName');
		$request->dateTime = request::unsignedPost('dateTime');
		$request->transactionID = request::unsignedPost('txnId');
		$request->upc = request::unsignedPost('upc');
		
		$gateway = new gatewayInventory(null,null);
		$response = $gateway->makeCall('requestActiveCode',$request);
		
		return json_encode(array(
			"request"=>$request->getMessage(),
			"response"=>$response->getMessage(),
			"newTxnId"=>'TEST'.str_replace('.', '', microtime(true)),
			"newDateTime"=>gatewayInventory::getDateTime()
		));
	}
	
	public static function returnActiveCode(){
		$request = new gatewayMessage(gatewayMessage::typeRequest);
		$request->amount = request::unsignedPost('amount');
		$request->currencyCode = request::unsignedPost('currency');
		$request->retailerName = request::unsignedPost('retailerName');
		$request->dateTime = request::unsignedPost('dateTime');
		$request->transactionID = request::unsignedPost('txnId');
		$request->upc = request::unsignedPost('upc');
		$request->code = request::unsignedPost('code');
		
		$gateway = new gatewayInventory(null,null);
		$response = $gateway->makeCall('returnActiveCode',$request);
		
		return json_encode(array(
			"request"=>$request->getMessage(),
			"response"=>$response->getMessage(),
			"newTxnId"=>'TEST'.str_replace('.', '', microtime(true)),
			"newDateTime"=>gatewayInventory::getDateTime()
		));
	}
}