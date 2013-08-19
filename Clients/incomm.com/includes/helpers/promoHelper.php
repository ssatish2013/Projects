<?php
class promoHelper{

	public static function getSingleUsePromo($code){
		$promoCode = new singleUsePromoCodeModel();
		$promoCode->prefix = substr($code,0,-9);
		$promoCode->code = substr($code,-5);
		$promoCode->pin = substr($code,-9,4);
		if($promoCode->load()){
			return $promoCode;
		}
		return null;
	}

	public static function recordPromoTransaction(messageModel $message){
		$promoTrigger = $message->getActivePromoTrigger();
		$promo = $message->getActivePromotion();

		if($promoTrigger !==null && $promo !==null){
			$promoTransaction = new promoTransactionModel();
			$promoTransaction->discountAmount = $message->getAmountOfDiscount(true);
			$promoTransaction->messageId = $message->id;
			$promoTransaction->promoId=$promo->id;
			$promoTransaction->promoTriggerId=$promoTrigger->promoTrigger->id;
			$promoTransaction->save();
			$promoTrigger->consume();
			new eventLogModel("promo", "promoTitle", $promo->title);
		}
		else {
			new eventLogModel("promo", "promoTitle", "Standard Txn");
		}
	}

	public static function startLedgersForGift(giftModel $gift, $isActivation){
		$ledgers = array();
		foreach($gift->getMessages() as $message){
			/* @var $message messageModel */
			$discountAmount = $message->getAmountOfDiscount();
			if($discountAmount > 0){
				$ledger = new ledgerModel();
				$ledger->amount = $discountAmount * ($isActivation ? 1:-1);
				$ledger->messageId = $message->id;
				$ledger->giftId = $message->getGift()->id;
				$ledger->currency = $message->getGift()->currency;
				$ledger->type = $isActivation?ledgerModel::typePromoActivation:ledgerModel::typePromoDeactivation;
				$ledger->startAudit();
				$ledgers[] = $ledger;
			}
		}
		return $ledgers;
	}

	public static function setupLocationForGift(giftModel $gift, inCommInventory $inventory){
		$messages = $gift->getMessages();
		foreach($messages as $message){
			/* @var $message messageModel */
			$discountAmount = $message->getAmountOfDiscount();
			if($discountAmount > 0){
				$promo=$message->getActivePromotion();
				if($promo && $promo->locationId && $promo->terminalId){
					$inventory->setLocationId($promo->locationId);
					$inventory->setTerminalId($promo->terminalId);
					return; // Gifts can only have 1 location / terminal id, so if its set, we're done
				}
			}
		}
	}

	public static function setupRetailerForGift(giftModel $gift, gatewayInventory $inventory){
		$messages = $gift->getMessages();
		//read default retail names from settings
		$retailName = settingModel::getSetting('inCommGateway', 'retailerName');
		$inventory->setRetailName($retailName);
		foreach($messages as $message){
			/* @var $message messageModel */
			$discountAmount = $message->getAmountOfDiscount();
			if($discountAmount > 0){
				$promo=$message->getActivePromotion();
				if($promo && $promo->retailerName){
					$inventory->setRetailName($promo->retailerName);
					return; // if found one then we switch to the special promo retailer name for API call.
				}
			}
		}
	}

	public static function getUnusedCoupons(shoppingCartModel $cart){
		//deprecated, we no longer store unused coupon
		$usedCoupons = array();
		return $unusedCoupons;
	}

	public static function getUsedCoupons(shoppingCartModel $cart){
		$usedCoupons = array();
		foreach($cart->getAllMessages() as $message){
			/* @var $message messageModel */
			$trigger = $message->getActivePromoTrigger();
			if($trigger){
				if(method_exists($trigger,"getCouponUsed")){
					$usedCoupons[]=$trigger->getCouponUsed();
				}
			}
		}
		return $usedCoupons;
	}

	public static function resetBonusAmount(shoppingCartModel $cart){
		//deprecated
	}

	public static function isValidCode($code,$pid){
		//fake message obj for validation
		$message = new messageModel();
		$message->id = -9999;
;		$message->amount = 100;
		$message->promoCode = $code;
		$gift = new giftModel();
		$gift->productId = $pid;
		$message->gift = $gift;

		$trigger = promoTriggerModel::getActiveTrigger($message);
		return !is_null($trigger);
	}
}
