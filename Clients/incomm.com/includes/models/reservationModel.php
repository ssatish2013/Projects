<?php
class reservationModel extends reservationDefinition{
	private $product=null;
	private $inventory=null;
	
	public function setProduct(productModel $product){
		$this->product = $product;
	}
	
	public function getProduct(){
		if($this->product === null){
			throw new Exception('Attempt to retreive product on reservation before it was set');
		}
		return $this->product;
	}
	
	public function getInventory(){
		if($this->inventory===null){
			if(!$this->inventoryId){
				$safeGiftId = db::escape($this->giftId);
				$safeProductId = db::escape($this->productId);
				$query = "UPDATE `inventorys` SET `giftId` = '$safeGiftId' WHERE `productId` ='$safeProductId' AND `giftId` IS NULL LIMIT 1";
				db::query($query);
				$inventory = new inventoryModel();
				$inventory->giftId=$this->giftId;
				if(!$inventory->load()){
					throw new inventoryException("Error fetching inventory");
				}
				$this->inventoryId = $inventory->id;
				$this->save();
				$this->inventory = $inventory;
			} else {
				$this->inventory = new inventoryModel($this->inventoryId);
			}
		}
		return $this->inventory;
	}
	
	public function unreserve(){
		if(!$this->id){
			throw new Exception("You must load a reservation before unreserving it");
		}
		$safeId = db::escape($this->id);
		
		// Clear the reservation if it hasn't been used
		$query = "UPDATE `reservations` SET `clearTime`=NOW() WHERE `id`='$safeId` AND `inventoryId` IS NULL";
		db::query($query);
		
		// It's most important that if we HAVE a reservation we reserve the pin, so we do that last
		// It's not the end of the world if we set both timestamps, as long as we're isolating correctly
		$query = "UPDATE `reservations` SET `isolationTime`=NOW() WHERE `id`='$safeId` AND `inventoryId` IS NOT NULL";
		db::query($query);
	}
	
}