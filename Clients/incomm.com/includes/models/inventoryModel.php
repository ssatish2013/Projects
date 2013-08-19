<?php
class inventoryModel extends inventoryDefinition{
	protected $product = null;
	
	public function _setAuxData($val){
		$this->auxData = json_encode($val);
	}
	
	public function _getAuxData(){
		return json_decode($this->auxData);
	}
	
	public function getProduct() {
		$product = new productModel($this->productId);
		return $product;
	}
}