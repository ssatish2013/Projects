<?php
class promoModel extends promoDefinition{
	public function isActive(){
		if(!$this->status || time() < strtotime($this->startDate) || time() > strtotime($this->stopDate)){
			return false;
		}


		if($this->maxBudget){
			$promoTransactions = promoTransactionModel::loadAll(array("promoId"=>$this->id));
			//check for bonus promotions, their total amount can't calculate from promoTransaction
			//instead, get the bonus amount from promo object multiply total number of transactions.
			if ($this->bonusAmount>0){
				$total = count($promoTransactions)*$this->bonusAmount;
				return !($total>=$this->maxBudget);
			}
			$total = array_reduce($promoTransactions, function($total,$txn){
				return $total+=$txn->discountAmount;
			});
			if($total >= $this->maxBudget){
				return false;
			}
		}

		return true;
	}

	public function isValid(messageModel $message){
		if(!$this->isActive()){
			return false;
		}

		if($this->productLimited && !in_array($message->productId,$this->getProductIds())){
			return false;
		}

		return true;
	}

	public function getProductIds(){
		return array_map(function($obj){return $obj->productId;},promoProductModel::loadAll(array("promoId"=>$this->id)));
	}

	public function getTitle(){
		return $this->title;
	}

	public function getDescription(){
		return $this->description;
	}
}