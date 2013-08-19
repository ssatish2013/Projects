<?php
class promoTriggerModel extends promoTriggerDefinition{
	/**
	 * @return promoTrigger
	 */
	public static function getActiveTrigger( messageModel $message=null ) {
		
		// First check if we already have a transaction for this message...
		// if we have a transaction, the trigger is hard-tied, return it
		$promoTransaction = new promoTransactionModel();
		$promoTransaction->messageId = $message->id;
		if($promoTransaction->load()){
			$promoTrigger = new promoTriggerModel($promoTransaction->promoTriggerId);
			$triggerPluginName = $promoTrigger->plugin;
			$triggerPlugin = new $triggerPluginName($promoTrigger->pluginData,$promoTrigger,$message);
			return $triggerPlugin;
		}
		
		
		// Otherwise we don't have a transaction...
		
		$safePartner = db::escape(globals::partner());
		$triggers = array();
		$query = "SELECT t.* FROM `promoTriggers` t, `promos` p WHERE t.`partner`='$safePartner' AND t.`status`='1' AND t.`promoId` = p.`id` AND p.`status`='1' AND p.`startDate`<NOW() and p.`stopDate`>NOW()";
		
		$result = mysql_query($query);

		while($row = mysql_fetch_assoc($result)){
			$promoTrigger = new promoTriggerModel();
			$promoTrigger->assignValues($row);
			
			// Check to see if the promotion is active
			$promo = new promoModel($promoTrigger->promoId);
			if(!$promo->isValid($message)){
				continue; // if the promotion isn't valid, neither is the promo trigger
			}
			

			$triggerPluginName = $promoTrigger->plugin;
			$triggerPlugin = new $triggerPluginName($promoTrigger->pluginData,$promoTrigger,$message);
			$triggers[] = $triggerPlugin;
		}

		usort($triggers,array('promoTrigger','compare'));
		if(sizeof($triggers)==0){
			return null;
		}

		if($triggers[0]->getDiscountAmount() > 0){
			return $triggers[0];
		}
		
		//if no discount promo found so far, check if bonus amount promo exists
		usort($triggers,array('promoTrigger','compareBonus'));
		if($triggers[0]->getBonusAmount() > 0){
			return $triggers[0];
		}

		return null;
	}
}
