<?php
abstract class promoTrigger{
	/**
	 *
	 * @property messageModel $message
	 * @property promoTriggerModel $promoTrigger
	 */

	public $promoTrigger;
	protected $message;
	private $promo = null;

	public function __construct($data,promoTriggerModel $promoTrigger,messageModel $message){
		$this->promoTrigger = $promoTrigger;
		$this->message = $message;
	}

	/**
	 * Returns the promotion associated with this promoTrigger
	 *
	 * @return promoModel 
	 */
	public function getPromo(){
		if($this->promo ===null){
			$this->promo = new promoModel($this->promoTrigger->promoId);
		}
		return $this->promo;
	}
	
	public function consume(){
		// Any special code that needs to happen on consumption should be placed here
	}

	public function isConsumed(){
		$promoTransaction = new promoTransactionModel();
		$promoTransaction->promoTriggerId = $this->promoTrigger->id;
		$promoTransaction->messageId = $this->message->id;
		if($promoTransaction->load()){
			return true;
		}
		return false;
	}
	
	public function isActive(){
		return false;
	}

	public function getDiscountAmount(){
		if(!$this->isActive() && !$this->isConsumed()){
			return 0;
		}

		$promo = $this->getPromo();
		$disc = 0;
		// check for type of discount, could be percentage or dollar amount
		// dollar amount has higher priority if both fields have been set
		// all existent promo can still use percentage discount without any changes.  
		if ($promo->discountAmount>0){
			//if discount amount > message amount, then message amount is returned, result in a zero total checkout. 
			$disc = ($promo->discountAmount > $this->message->amount) ? 0 : $promo->discountAmount;
		}
		else{
			//Percentage discount rounded up to nearest 1/100th of a cent
			$disc = ceil($this->getPromo()->discountPercent * $this->message->amount) / 100;
		}

		return $disc;
	}
	
	public function getBonusAmount(){
		if(!$this->isActive() && !$this->isConsumed()){
			return 0;
		}
	
		$promo = $this->getPromo();
		return $promo->bonusAmount?$promo->bonusAmount:0;
	}
	
	public function getTitle(){
		return $this->getPromo()->getTitle();
	}
	
	public function getDescription(){
		return $this->getPromo()->getDescription();
	}
	

	public static function compare(promoTrigger $a,promoTrigger $b){
		return $b->getDiscountAmount() - $a->getDiscountAmount();
	}

	public static function compareBonus(promoTrigger $a,promoTrigger $b){
		return $b->getBonusAmount() - $a->getBonusAmount();
	}
	
}