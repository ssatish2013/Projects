<?php
class ipnHelper {
	public static function completed ($ipnData) {
		if (strtolower ($ipnData['txn_type']) == "express_checkout") {
			$transaction = new transactionModel();
			$transaction->externalTransactionId = $ipnData['txn_id'];
			$transaction->load();
			if ($transaction->id > 0 && $transaction->refunded == NULL && $transaction->chargedback == NULL) {
				$transaction->status = 1;
				// load shopping cart and set status
				$cart = $transaction->getShoppingCart ()->transactionComplete ($transaction)->save ();
				$transaction->save ();
				return true;
			}
		}
		return false;
	}
	
	public static function failed ($ipnData) {
		$transaction = new transactionModel();
		$transaction->externalTransactionId = $ipnData['txn_id'];
		if ($transaction->load () && $transaction->refunded == NULL && $transaciton->chargedback == NULL) {
			// remove the transaction's completed status so it is marked as failed/refunded
			paymentHelper::updateRefundStatus ($transaction->getShoppingCart (), $transaction);
			$gifts = $transaction->getShoppingCart ()->getAllGifts ();
			foreach ($gifts as $gift) {
				if ($gift->isClaimed ()) {
					// if it's a single gift, try to deactivate the card
					if (count ($gift->getAllMessages ()) == 1) {
						paymentHelper::refundGift ($gift->id, NULL);
					}
					// if it's a group gift, we agreed to eat the charge as per my skype conversation with the business team.
					// this is a very unlikely scenario because of the limited % of group gifts, the low number of failed ACH payments
					// and finally because only verified paypal accounts with backup funding methods will be auto-completed, otherwise
					// we wait until receiving the "Complete" message from PayPal.
				}
			}
		}
	}
	
	public static function refunded ($ipnData) {
		$transaction = new transactionModel();
		$transaction->externalTransactionId = $ipnData['parent_txn_id'];
		if ($transaction->load () && $transaction->refunded == NULL) {
			// the remote api to refund is not necessary because the refund was initiated on the paypal side, this IPN call is just a confirmation
			// sometimes we have to find a transaction and refund it directly in paypal, this is to update our platform with those changes
			$gifts = $transaction->getShoppingCart ()->getAllGifts ();
			foreach ($gifts as $gift) {
				if ($gift->isClaimed ()) {
					// try to deactivate the card if this is the only contributor
					if (count ($gift->getAllMessages ()) == 1) {
						$gift->getAndDeactivateInventory ();
					}
					// if this is a claimed group gift, we agreed to eat the charge as per my skype conversation with the business team.
				}
				$refundEmailWorker = new refundEmailWorker();
				if ($gift->delivered) {
					// For delivered gifts, send a refund notice to the recipient.
					$refundEmailWorker->send (json_encode (array ('giftId'=>$gift->id, 'userId'=>NULL)));
				} else {
					// Otherwise, just the creator
					$refundEmailWorker->send (json_encode (array ('transactionId' => $transaction->id, 'userId=' => NULL)));
				}
			}
			paymentHelper::updateRefundStatus ($transaction->getShoppingCart (), $transaction);
		}
	}
	
	public static function chargebackNotice($ipnData){
		$transaction = new transactionModel();
		$transaction->externalTransactionId = $ipnData['parent_txn_id'];
		$transaction->load();

		$ledger = new ledgerModel();
		$ledger->amount = $ipnData['mc_fee'] + $ipnData['mc_gross'];
		$ledger->type = 'chargebackHold';
		$ledger->shoppingCartId = $transaction->getShoppingCart()->id;
		$ledger->currency = $ipnData['mc_currency'];
		$ledger->startAudit();
		$ledger->save();

		//mark the the transaction as charged back
		$transaction->chargedback = $ledger->timestamp;
		$transaction->save();
		
		$messages = $transaction->getShoppingCart()->getAllMessages();
		foreach ($messages as $message) {
			/* @var $message messageModel */
			$gift = $message->getGift();
			try { 
				$gift->getAndDeactivateInventory();
			}
			catch(deactivationException $e) { 
				ledgerHelper::addNdr($gift->id);
			}
		}

		kountHelper::chargebackNotice($transaction->getShoppingCart()->id);
	}
	
	public static function cancelReversal($ipnData){
		$transaction = new transactionModel();
		$transaction->externalTransactionId = $ipnData['parent_txn_id'];
		$transaction->load();
		
		$ledger = new ledgerModel();
		$ledger->amount = $ipnData['mc_fee'] + $ipnData['mc_gross'];
		$ledger->type = 'chargebackHoldReversal';
		$ledger->shoppingCartId = $transaction->getShoppingCart()->id;
		$ledger->currency = $ipnData['mc_currency'];
		$ledger->startAudit();
		$ledger->save();
	}
	
	public static function chargeback($ipnData){
		$transaction = new transactionModel();
		$transaction->externalTransactionId = $ipnData['parent_txn_id'];
		$transaction->load();
		
		// This ledger is for the payment reversal
		$ledger = new ledgerModel();
		$ledger->amount = $ipnData['mc_gross'];
		$ledger->type = 'chargeback';
		$ledger->shoppingCartId = $transaction->getShoppingCart()->id;
		$ledger->currency = $ipnData['mc_currency'];
		$ledger->startAudit();
		$ledger->save();

		// This is for the chargeback fee
		$ledger = new ledgerModel();
		$ledger->amount = $ipnData['payment_fee'];
		$ledger->type = 'chargebackFee';
		$ledger->shoppingCartId = $transaction->getShoppingCart()->id;
		$ledger->currency = $ipnData['mc_currency'];
		$ledger->startAudit();
		$ledger->save();
		
		// If the partner refunds us for chargebacks
		$shoppingCart = $transaction->getShoppingCart();
		globals::partner($shoppingCart->partner);
		$chargebackRefundPercent = settingModel::getSetting('fees', 'chargebackRefundPercent');
		if($chargebackRefundPercent != 0){
			$ledger = new ledgerModel();
			$ledger->amount = $ipnData['payment_fee'] * $chargebackRefundPercent;
			$ledger->type = 'chargebackPartnerFee';
			$ledger->shoppingCartId = $shoppingCart->id;
			$ledger->currency = $ipnData['mc_currency'];
			$ledger->startAudit();
			$ledger->save();					
		}
	}

	public static function newCase($ipnData){
		$reason = "UNKNOWN";

		//avoid any errors so we can return 200 to paypal
		try { 
			$reason = $ipnData['reason_code'];
		} catch(Exception $e) { 
			$reason = "UNKNOWN";
		}
		new eventLogModel("dispute", "paypal", $reason);

	}
}
