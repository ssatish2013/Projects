<?php
class gatewayMessage{
	const typeRequest = 'RetailTransactionRequest';
	const typeResponse = 'RetailTransactionResponse';
	
	private $_type=null;
	
	public $amount;
	public $currencyCode;
	public $dateTime;
	public $imageRequest;
	public $retailerName;
	public $tcLang;
	public $tcVersion;
	public $transactionID;
	public $upc;
	public $authID;
	public $code;
	public $imageURL;
	public $pan;
	public $pin;
	public $responseCode;
	public $responseText;
	public $serialNum;
	public $tcText;
	public $vendorSerialNum;
	public $enrichedData; // Have been seeing this in response, will store
	
	public function __construct($type){
		$this->_type = $type;
	}
	
	public function isSuccess($pinRequired = true){
		// Success if the pin evaluates to true and the responseCode is 0 (type and value or 21
		return (!$pinRequired || !!$this->pin) && ($this->responseCode === 0 || $this->responseCode === "0" || $this->responseCode == 21);
	}
	
	public function getMessage(){
		// We need to get an array of the keys for the PUBLIC variables only
		// A great way to do this is by creating an anonymous function
		$vars = array_keys(call_user_func(
			function($obj){
				return get_object_vars($obj);
			}, $this		
		));	
		
		$msg = array();
		foreach($vars as $var){
			if(!is_null($this->$var)){
				$msg[$var]=$this->$var;
			}
		}
		
		$msg = array($this->_type=>$msg);
		return json_encode($msg);
		
	}
	
	public function setMessage($json){
		$array = json_decode($json,true);
		
		$array = $array[$this->_type];
		foreach($array as $key=>$value){
			if(property_exists('gatewayMessage', $key)){
				$this->$key=$value;
			}
		}
	}
}
