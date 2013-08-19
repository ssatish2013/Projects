<?php
class formValidationHelper {
	
	/*
	 * messageModel helpers
	 */
	
	public static function validateMessageAmount() {
		$amount = request::unsignedPost('messageAmount');
		if ($amount == "custom") {
			$amount = request::unsignedPost("amountCustomText");
		}
		
		$productId = request::unsignedPost('giftProductId');
		$guid = request::unsignedPost('giftGuid');
		try{
			//from create page
			if ($productId){
				$messageGuid = request::url("messageGuid");
				$message = new messageModel($messageGuid);
				$gift = $messageGuid ? $message->getGift() : null;
				$product = new productModel($productId);
			}
			//from contribute page
			else if($guid){
				$gift = new giftModel($guid);
				$product =  $gift->getProduct();
				$messageGuid = '';
			}
			else{
				return false;
			}
			
			$total = 0;
			if ($gift){
				foreach ($gift->getMessages() as $m){
					//don't sum current message amount in total, use user posted amount instead.
					if ($m->guid!=$messageGuid){
						$total += $m->amount;
					}
				}
			}
			
			if ($product->currency == "JPY") { 
				$amount = intval($amount);
			}
			
			$total += $amount;
			return ($total>0 && $total>=$product->minAmount && $total<=$product->maxAmount);
		}
		catch(Exception $e){
			log::error("can't validate message amount",$e);
			return false;
		}
	}
	
	public static function validateMessageFromName(){
		return self::isUnsignedElementSet('messageFromName');
	}
	
	public static function validateMessageFromEmail(){
		if(emailHelper::isValidEmail(request::unsignedPost('messageFromEmail'))){
			return true;
		} else {
			return false;
		}
	}
	
	public static function validateMessageMessage(){
		return self::isUnsignedElementSet('messageMessage');
	}
	
	public static function validateMessageCurrency(){
		return true;
	}

	/*
	 * giftModel helpers
	 */
	
	public static function validateGiftProductId(){
		try{
			$product = new productModel();
			$product->id = request::unsignedPost('giftProductId');
			if($product->load()){
				return true;
			}
		}
		catch (Exception $e){
		}
		return false;
	}
	
	public static function validateGiftRecipientName(){
		return self::isUnsignedElementSet('giftRecipientName');
	}
	
	public static function validateGiftRecipientEmail(){
		if(request::unsignedPost('giftDeliveryMethod') != "physical"){
			return emailHelper::isValidEmail(request::unsignedPost('giftRecipientEmail'));
		} else {
			return true;
		}
	}

	public static function validateGiftRecipientAddress1(){
		if(request::unsignedPost('giftDeliveryMethod') == "physical"){
			return self::isUnsignedElementSet('giftRecipientAddress1');
		} else {
			return true;
		}
	}

	public static function validateGiftRecipientCity(){
		if(request::unsignedPost('giftDeliveryMethod') == "physical"){
			return self::isUnsignedElementSet('giftRecipientCity');
		} else {
			return true;
		}
	}

	public static function validateGiftRecipientState(){
		if(request::unsignedPost('giftDeliveryMethod') == "physical"){
			return self::isUnsignedElementSet('giftRecipientState');
		} else {
			return true;
		}
	}

	public static function validateGiftRecipientZip(){
		if(request::unsignedPost('giftDeliveryMethod') == "physical"){
			return self::isUnsignedElementSet('giftRecipientZip');
		} else {
			return true;
		}
	}

	public static function validateGiftDeliveryDate(){
		date_default_timezone_set(request::unsignedPost('currentTimeZone'));
		
		$date_yyyy = (request::unsignedPost('date_yyyy') != "") ? request::unsignedPost('date_yyyy') : date ("Y");
		$date_mm   = (request::unsignedPost('date_mm') != "") ? request::unsignedPost('date_mm') : date ("m");
		$date_dd   = (request::unsignedPost('date_dd') != "") ? request::unsignedPost('date_dd') : date ("d");
		
		// @TODO after we get the front end setup make sure this is in the right format.
		$date = $date_yyyy . '/' . $date_mm . '/' . $date_dd;
		if (request::unsignedPost('giftDeliveryMethod') != "physical") {
			return strtolower( $date ) == (preg_match ('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $date) && strtotime($date) >= strtotime (date ("Y/m/d")) && strtotime ($date) < time () + 60 * 60 * 24 * 21);
		} else {
			// If this is physical delivery we are only allowing now
			return strtolower( $date ) == date ("m/d/Y");
		}
	}
	
	public static function validateGiftDeliveryTime(){
		// This method validates delivery time
		$timeHour    = request::unsignedPost('timeHour');
		$timeZone    = request::unsignedPost('timeZone');
		$timeInstant = request::unsignedPost('deliveryInstant');
		
		if( !isset($timeInstant) ) {
			if(is_numeric($timeHour)) {
				if( (intval($timeHour) < 0) || (intval($timeHour > 23)) || ($timeZone == "") ) {
					return false;
				} else {
					return true;
				}
			}
		} else {
			return true;
		}
	}
	
	public static function validateGiftDesignId(){
		$designId = request::unsignedPost('giftDesignId');
		$design = new designModel($designId);
		if ($design->isCustom) return true; // always accept custom design.
		$currency = request::unsignedPost('messageCurrency');
		$designs = productGroupModel::getDesignAndGroups($currency);
		foreach($designs as $row){
			if ($row['id'] == $designId)
				return true;
		}
		return false;
	}
	
	public static function validateGiftRecipientPhoneNumber() {
		$phoneNumber = request::unsignedPost( 'giftRecipientPhoneNumber' );
		$phoneNumber = utilityHelper::stripNonIntegers( $phoneNumber );
		if ( $phoneNumber && ( strlen( $phoneNumber ) > 9 ) && strlen( $phoneNumber ) <= 11 ) {
			//We are only checking for US phone numbers right now :-(
			return true;
		} else if (empty ($phoneNumber)) {

			return true;

		} else {
			return false;
		}

	}

	public static function validateGiftRecipientFacebookId() { 
		$deliveryMethod = request::unsignedPost('giftDeliveryMethod');
		$facebookId = request::unsignedPost('giftFacebookUID');
		
		//if it's a social delivery method, make sure we have a facebook id
		if($deliveryMethod == giftModel::DELIVERY_SOCIAL && $facebookId == 0) { 
			return false;
		}
		return true;
	}
	
	public static function validateGiftTitle(){

		return self::isUnsignedElementSet('giftTitle');

	}
	
	public static function validateGiftEventTitle(){
		return self::isUnsignedElementSet('giftEventTitle');
	}
	
	public static function validateGiftEventMessage(){
		return self::isUnsignedElementSet('giftEventMessage');
	}
	
	public static function validateGiftGiftingMode(){

		return self::isUnsignedElementSet('giftGiftingMode');

	}
	
	public static function validateGiftProductGroupId(){

		return self::isUnsignedElementSet('giftProductGroupId');

	}
	
	/*
	 * transationModel helpers
	 */
	
	public static function validateTransactionFirstName(){
		return self::isUnsignedElementSet('transactionFirstName');
	}

	public static function validateTransactionLastName(){
		return self::isUnsignedElementSet('transactionLastName');
	}
	
	public static function validateTransactionPhoneNumber(){
		$phoneNumber = request::unsignedPost('transactionPhoneNumber');
		$phoneNumber = utilityHelper::stripNonIntegers($phoneNumber);
		if($phoneNumber && (strlen($phoneNumber) > 9) && strlen($phoneNumber) <= 11){
			//We are only checking for US phone numbers right now :-(
			return true;
		} else {
			return false;
		}
	}
	
	public static function validateTransactionCreditCard(){
		return utilityHelper::getCreditCardType(request::unsignedPost('transactionCreditCard'));
	}
	
	public static function validateTransactionExpirationMonth(){
		$month = request::unsignedPost('transactionExpirationMonth');
		if($month >= 01 && $month <= 12){
			return true;
		} else {
			return false;
		}
	}
	
	public static function validateTransactionExpirationYear(){
		$year = request::unsignedPost('transactionExpirationYear');
		if($year >= date("Y") && $year <= date("Y", strtotime("+8 years"))){
			return true;
		} else {
			return false;
		}
	}
	
	public static function validateTransactionCvv(){
		$ccType = self::validateTransactionCreditCard();
		$cvv = request::unsignedPost('transactionCvv');
		if(($ccType == 'AMEX' && strlen($cvv) == 4) || strlen($cvv) == 3){
			return true;
		} else {
			return false;
		}
	}
	
	public static function validateTransactionAddress(){
		return self::isUnsignedElementSet('transactionAddress');
	}
	
	public static function validateTransactionAddress2(){
		return true;
	}
	
	public static function validateTransactionCity(){
		return self::isUnsignedElementSet('transactionCity');
	}
	
	public static function validateTransactionState(){
		return self::isUnsignedElementSet('transactionState');
	}

	public static function validateTransactionProvince(){
		return self::isUnsignedElementSet('transactionProvince');
	}
	
	public static function validateTransactionRegion(){
		return self::isUnsignedElementSet('transactionRegion');
	}
	
	public static function validateTransactionZip(){
		return self::isUnsignedElementSet('transactionZip');
	}
	
	public static function validateTransactionCountry(){
		return self::isUnsignedElementSet('transactionCountry');
	}
	
	public static function validateTransactionFromEmail(){
		if(emailHelper::isValidEmail(request::unsignedPost('transactionFromEmail'))){
			return true;
		} else {
			return false;
		}
	}


	/*
	 * Helper for a simple is this set
	 */
	
	public static function isUnsignedElementSet($element){
		if(request::unsignedPost($element)){
			return true;
		} else {
			return false;
		}
	}
	
	public static function isSignedElementSet($element){
		if(request::post($element)){
			return true;
		} else {
			return false;
		}
	}
	
	/*
	 * Helpers for login
	 */
	
	public static function validateUserEmail(){
		if(emailHelper::isValidEmail(request::post('userEmail'))){
			return true;
		} else {
			return false;
		}		
	}
	
	public static function validateUserPassword(){
		return self::isSignedElementSet('userPassword');
	}

	/*
	 * testingModel helpers
	 */
	
	public static function validateTestingMessage(){
		return true;
	}
	
	public static function validateTestingMessageFail(){
		return false;
	}

	

	public static function validateRecordClient () {

		return true;

	}

	

	public static function validateVideoLink () {

		$link = request::unsignedPost('giftVideoLink');

		if (empty ($link)) return true;

		if (preg_match ("/(http)(s?)(:\/\/)(.+)/", $link)) return true;

		if (preg_match ("/([a-zA-Z0-9\-\_]+)/", $link)) return true;

		return false;

	}
	
	public static function validateShippingDetailAddress() {

		if (self::_isPhysicalDelivery()) {

			return self::isUnsignedElementSet('shippingDetailAddress');

		}

		return true;

	} //end validateShippingDetailAddress

	

	public static function validateShippingDetailCity() {

		if (self::_isPhysicalDelivery()) {

			return self::isUnsignedElementSet('shippingDetailCity');

		}

		return true;

	} //end validateShippingDetailCity

	

	public static function validateShippingDetailState() {

		if (self::_isPhysicalDelivery()) {

			return self::isUnsignedElementSet('shippingDetailState');

		}

		return true;

	} //end validateShippingDetailState

	

	public static function validateShippingDetailZip() {

		if (self::_isPhysicalDelivery()) {

			return self::isUnsignedElementSet('shippingDetailZip');

		}

		return true;

	} //end validateShippingDetailZip

	

	public static function validateShippingDetailCountry() {

		if (self::_isPhysicalDelivery()) {

			return self::isUnsignedElementSet('shippingDetailCountry');

		}

		return true;

	} //end validateShippingDetailCountry

	

	private static function _isPhysicalDelivery() {

		return (request::unsignedPost('giftDeliveryMethod') == 'physical');

	} //end _isPhysicalDelivery
}
