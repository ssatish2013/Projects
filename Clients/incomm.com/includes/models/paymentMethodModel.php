<?php
class paymentMethodModel extends paymentMethodDefinition{
	
	const ALLOW_ALL_CURRENCIES_VALUE = "ALLOW_ALL";
	const SECUREPAY_PLUGIN_NAME = "paypalPayment";
	const PAYPAL_EXPRESS_PLUGIN_NAME = "paypalExpressPayment";
	
	public function _getSettings(){
		return json_decode($this->settings);
	}
	
	public function _setSettings($value){
		$this->settings = json_encode($value);
	}
	
	public function supportsCurrency($currencyCode) {
		$supportedCurrencies = explode(',', $this->supportedCurrencies);
		if(in_array(self::ALLOW_ALL_CURRENCIES_VALUE, $supportedCurrencies))
			return true;
		elseif(in_array($currencyCode, $supportedCurrencies))
			return true;
		else 
			return false;
	}
}