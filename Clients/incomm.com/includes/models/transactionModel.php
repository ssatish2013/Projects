<?php
class transactionModel extends transactionDefinition{
	public $cvv;
	public $expirationMonth;
	public $expirationYear;
	public $paypalPayerId;
	public $paypalDetails;
	public $cvv2Match = null;
	public $avsCode = null;
	public $optIn = false;
	public $success = true;
	protected $creditCard;
	private $messages;
	
	// Transient vars.
	public $ipAddress;
	public $userAgent;
	
	public function _setCreditCard($value){
		$this->creditCard = $value;
		$this->ccLastFour = substr($value, '12', '4');
		$this->ccType = utilityHelper::getCreditCardType($value);
	}

  public function getShoppingCart() {
    return new shoppingCartModel( $this->shoppingCartId );
  }

	public function assignPaymentMethod($payment){
		if(isset($payment->paymentMethodId)){
			$this->paymentMethodId = $payment->paymentMethodId;
		} else {
			throw new Exception('Sorry, no paymentMethodId is assigned to this $payment');
		}
	}
	
	public function assignShoppingCart($shoppingCart){
		if(isset($shoppingCart->id)){
			$this->currency = $shoppingCart->currency;
			$this->amount = @$shoppingCart->amount;
			$this->status = 0;
			$this->shoppingCartId = $shoppingCart->id;
		} else {
			throw new Exception("Sorry, no id is assigned to this $shoppingCart");
		}
	}
	
	public function transactionComplete($response, $status = 1){
		$this->status = $status;
		$this->externalTransactionId = $response['TRANSACTIONID'];
	}
	
	public function authorizationComplete($response) {
		$this->authorizationTime = date("Y-m-d H:i:s");
		$this->authorizationId = $response['TRANSACTIONID'];
	}
	
	public function isAuthorized(){
		$validTime = date("Y-m-d H:i:s", strtotime("-29 days"));
		if($this->authorizationId && ($this->authorizationTime > $validTime) && !$this->refunded){
			// is this authorization valid?
			return true;
		}
		return $this->isPaid ();
	}
	
	public function isPaid(){
		if($this->externalTransactionId && $this->status && !$this->refunded){
			return true;
		}
		return false;
	}

	public function hasCheckoutError() {
		$isAuthorizationCreditCardTransaction = (($this->ccType != 'Paypal')
			&& !is_null($this->authorizationId));
		$isCompletedPayPalTransaction = (($this->ccType == 'Paypal')
			&& !is_null($this->externalTransactionId));
		return (!$isAuthorizationCreditCardTransaction && !$isCompletedPayPalTransaction);
	}

	public function assignFromPaypalExpress($details){
		$this->firstName = $details['FIRSTNAME'];
		$this->lastName = $details['LASTNAME'];
		$this->fromEmail = $details['EMAIL'];
		$this->phoneNumber = isset($details['PHONENUM']) ? $details['PHONENUM'] : null;
		$this->address = $details['SHIPTOSTREET'];
		if(isset($details['SHIPTOSTREET2'])) { 
			$this->address2 = $details['SHIPTOSTREET2'];
		}
		$this->city = $details['SHIPTOCITY'];
		$this->state = $details['SHIPTOSTATE'];
		$this->zip = $details['SHIPTOZIP'];
		$this->country = $details['SHIPTOCOUNTRYCODE'];
		$this->paypalPayerId = $details['PAYERID'];
		$this->ccType = 'Paypal';
		$this->paypalDetails = $details;
	}

	public function isPayPalExpress() {
		return (strtolower($this->ccType) == 'paypal');
	}

	public function getPaymentHash() { 

		$hash = "";
		//paypal txn
		if($this->isPayPalExpress()) { 
			$hash = $this->paypalPayerId;
		}

		//credit card
		else {
			$hash = substr($this->creditCard,0,6).substr($this->creditCard,-4,4);
		}

		return $hash;
	}
	
	public function setIpAddress ($ip = NULL) {
		$this->ipAddress = $ip;
		$this->arinCacheId = arinCacheModel::getDataID ($this->ipAddress);
		return $this;
	}
	
	public function getIPOrgName () {
		return arinCacheModel::getOrgName ($this->ipAddress);
	}

	public function getIPOrgHandle () {
		return arinCacheModel::getOrgHandle ($this->ipAddress);
	}
	
	public function getEmailDomain () {
		// returns just the domain portion of the e-mail address associated with the gift
		return substr ($this->fromEmail, strpos ($this->fromEmail, '@') + 1);
	}

	public function __toString() {
		return "[".get_class($this).": id=$this->id, authorizationId=$this->authorizationId, " .
			"externalTransactionId=$this->externalTransactionId]";	
	}
}
