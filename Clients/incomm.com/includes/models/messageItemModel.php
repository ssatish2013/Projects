<?php
class messageItemModel extends messageItemDefinition {
	const TYPE_DISCOUNT = 'TYPE_DISCOUNT';
	const TYPE_BONUS = 'TYPE_BONUS';
	const TYPE_FEE = 'TYPE_FEE';
	const TYPE_TAX = 'TYPE_TAX';
	
	public static function createDiscountItem(MesssageModel $msg,promoTrigger $promoTrigger){
		if (!isset($promoTrigger) || !isset($msg->promoCode)) return null;
		$item = new messageItemModel();
		$item->messageId = $msg->id;
		$item->itemType = self::TYPE_DISCOUNT;
		$item->itemData = $msg->promoCode;
		$item->amount = $promoTrigger->getDiscountAmount();
		$item->title = $promoTrigger->getTitle();
		$item->assignSeqNum();
		$item->save();
		return $item; 
	}
	
	public static function createBonusItem(MesssageModel $msg,promoTrigger $promoTrigger){
		if (!isset($promoTrigger) || !isset($msg->promoCode)) return null;
		$item = new messageItemModel();
		$item->messageId = $msg->id;
		$item->itemType = self::TYPE_BONUS;
		$item->itemData = $msg->promoCode;
		$item->amount = $promoTrigger->getBonusAmount();
		$item->title = $promoTrigger->getTitle();
		$item->assignSeqNum();
		$item->save();
		return $item;
	}
	
	public static function createFeeItem(MesssageModel $msg,productFeeModel $fee){
		if (!isset($fee) || $fee->calcFeeAmount($msg)==0) return null;
		$item = new messageItemModel();
		$item->messageId = $msg->id;
		$item->itemType = self::TYPE_FEE;
		$item->itemData = $fee->id;
		$item->amount =$fee->calcFeeAmount($msg);
		$item->title = $fee->title;
		$item->assignSeqNum();
		$item->save();
		return $item;
	}
	
	public function getMessage(){
		return new messageModel($this->id);
	}

	public function assignSeqNum(){
	}
}