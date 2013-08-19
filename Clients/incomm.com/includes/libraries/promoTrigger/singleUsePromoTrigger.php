<?php
final class singleUsePromoTrigger extends promoTrigger{
	private $couponUsed = null;
	
	public function isActive(){
		$enteredCoupon = $this->message->promoCode;
		$promoCode = promoHelper::getSingleUsePromo($enteredCoupon);
		if($promoCode && $promoCode->promoTriggerId == $this->promoTrigger->id && !$promoCode->redeemed && !$this->isUsedInMessage($enteredCoupon)){
			$this->couponUsed = $enteredCoupon;
			return true;
		}
		return false;
	}
	
	//addtional check if the code is used in any message line items
	//return false for those items created 24 hours ago, 
	//so entered code which is never redeemed at checkout can be re-used again.
	public function isUsedInMessage($code){
		$found = false;
		$items = messageModel::loadAll(array('itemType'=>messageItemModel::TYPE_DISCOUNT,itemData'=>$code));
		foreach($items as $item){
			$hours = abs(time()-strtotime($item->created))/60/60;
			$found = ($hours>24)?false:true; 
		}
		return $found;		
	}
	
	public function consume(){
		if($this->couponUsed){
			$singleUse=promoHelper::getSingleUsePromo($this->couponUsed);
			if($singleUse){
				$singleUse->redeemed = date("Y-m-d H:i:s");
				$singleUse->save();
			}
		}
	}
	
	public function getTitle(){
		return $this->getPromo()->getTitle()." (Code: ".$this->getCouponUsed().")";
	}
	
	public function getCouponUsed(){
		return $this->couponUsed;
	}
}