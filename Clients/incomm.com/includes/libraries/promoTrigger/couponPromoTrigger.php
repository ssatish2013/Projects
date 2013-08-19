<?php
final class couponPromoTrigger extends promoTrigger{
	private $validCoupons = array();
	private $couponUsed = null;


	public function __construct($data,promoTriggerModel $promoTrigger,messageModel $message){
		if(!($this->validCoupons = json_decode($data,true))){
			throw new Exception('Plugin Data for Coupon Promo is Corrupt or Missing');
		}
		parent::__construct($data, $promoTrigger, $message);
	}

	public function isActive(){
		$enteredCoupon = $this->message->promoCode;
		foreach($this->validCoupons as $validCoupon){
				// Valid coupons starting and ending with / are regular expressions
				if(strlen($validCoupon)>2&&substr($validCoupon,0,1)=='/'&&substr($validCoupon,-1)=='/'){
					if(preg_match($validCoupon,$enteredCoupon)){
						$this->couponUsed=$enteredCoupon;
						return true;
					}
				} else if(trim(strtolower($enteredCoupon))==trim(strtolower($validCoupon))){
						$this->couponUsed=$enteredCoupon;
						return true;
				}
		}

		return false;
	}

	public function getCouponUsed(){
		return $this->couponUsed;
	}

	public function getTitle(){
		return $this->getPromo()->getTitle()." (Code: ".$this->getCouponUsed().")";
	}

}