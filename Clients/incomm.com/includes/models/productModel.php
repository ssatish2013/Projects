<?php
class productModel extends productDefinition{
	public static function getAll(){
		$products = array();
		$query = "SELECT * FROM `products`";
		$result = db::query($query);
		while($row = mysql_fetch_assoc($result)){
			$product = new productModel();
			$product->assignValues($row);
			$products[]=$product;
		}
		
		return $products;
	}
	
	public function getReservation(giftModel $gift, $amount=0){
		$reservation = new reservationModel();
		$reservation->giftId = $gift->id;
		$reservation->productId = $this->id;
		
		if($gift->unverifiedAmount + $amount > $this->maxAmount){
			throw new reservationException("Unable to make reservation, amount mismatch for gift#$gift->id, " .
					"maxAmount={$this->maxAmount}, unverifiedAmount=$gift->unverifiedAmount, amount=$amount");
		}
		
		if($reservation->load()){
			return $reservation;
		} else {
			$safeProductId = db::escape($this->id);
			$query = "SELECT count(`id`) FROM `inventorys` WHERE `giftId` IS NOT NULL AND `productId`='$safeProductId'";
			$result = db::query($query);
			$count = mysql_result($result, 0);
			
			if($reservation->save()){
				return $reservation;
			} else {
				throw new reservationException("Unable to make reservation, save() returned false.");
			}
		}	
	}
	
	public function getInventoryPlugin(giftModel $gift){
		$pluginType = $this->inventoryPlugin."Inventory";
		if(!class_exists($pluginType)){
			throw new exception("$pluginType Plugin does not exist");
		}
		return new $pluginType($this,$gift);
	}
	
	public function getDisplayName(){
		//get the partner product display name.
		$name = languageModel::getString($this->displayName);

		return $name ? $name : $this->displayName;

	}
	
	public function getDisplayTerms () {
		// get the partner product terms
		$name = languageModel::getString ($this->displayTerms);
		return $name ? $name : languageModel::getString ("cardTerms");
	}
	
}