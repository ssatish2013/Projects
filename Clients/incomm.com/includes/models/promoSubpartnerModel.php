<?php
class promoSubpartnerModel extends promoSubpartnerDefinition{
	private $promo = null;
	public function isActive(){
		if($this->promo===null){
			$this->promo = new promoModel($this->promoId);
			if($this->promo->partner != globals::partner()){
				return false;
			}
		}
		return $this->promo->isActive();
	}
}
