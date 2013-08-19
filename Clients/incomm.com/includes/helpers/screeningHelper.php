<?php

//*** NOTE ***
//All functions that are called in the xslt need to be
//defined as public static since we have no class instance
//to callthem from

class screeningHelper {

	public static function approveCart($shoppingCartId, $agent) {
		$cart = new shoppingCartModel($shoppingCartId);

		//already approved, rejected or refunded, fuggedaboudeit
		if($cart->rejected !== null || $cart->getTransaction()->refunded !== null) {
			return;
		}

		$cart->approved = date('Y-m-d H:i:s', strtotime("now"));
		$cart->screenedBy = $agent;
		$cart->save();

		$transaction = new transactionModel(array('shoppingCartId' => $shoppingCartId));

		//send out recipient email via Delivery worker
		$worker = new recipientDeliveryEmailWorker();

		//see if any gifts on the cart are ready to be sent
		$gifts = $cart->getAllGifts();
		foreach($gifts as $gift) {

			//gift is approved, remove from the screening queue. The delivery batch script will now pick it up.
			if($gift->approved) {
				$gift->inScreeningQueue = 0;
				$gift->save();
			}

			//if the gift has already been delivered, let's send out
			//an email notifying the user
			if($gift->addedToDeliveryQueue == 1) {
				$worker->send(json_encode(array('giftId' => $gift->id, 'email' => $gift->recipientEmail)));
			}
		}
		log::info("Shopping cart $cart->id approved by agent $agent");
	}

	public static function rejectCart($shoppingCartId, $agent) {

		$cart = new shoppingCartModel($shoppingCartId);

		//already rejected or refunded, fuggedaboudeit
		if($cart->getTransaction()->refunded !== null) {
			return;
		}

		//first refund it
		$paymentHelper = new paymentHelper();
		$success = true;

		try {
			//paymentHelper also sends out the refund email(s)
			$gifts = $cart->getAllGifts();
			$messages = $cart->getAllMessages();
			$contribution = null;
			foreach ($messages as $message){
				if ($message->isContribution){
					$contribution = $message;
					break;
				}
			}
			//for contributor shopping cart , only refund the contribution
			//leave the original gift untouched.
			if ($contribution){
				$paymentHelper->refundMessage($contribution);
			}
			//otherwise, refund everything in the shopping cart.
			else{
				$paymentHelper->refundGift($gifts[0]->id);
			}
			log::info("Cart $cart->id rejected and refunded by agent $agent");
		}
		catch(paymentRefundException $e) {
			//refund error, should notify the screener and give them the reason
			log::error("Refund failed.", $e);
			$success = false;
		}
		catch(paymentUnknownException $e) {
			//random payment error, notify the screener and let them know what's going on
			log::error("Payment refund failed.", $e);
			$success = false;
		}
		catch(Exception $e) {
			log::error("Payment refund failed.", $e);
			$success = false;
		}

		if($success) {
			$cart->rejected = date('Y-m-d H:i:s', strtotime("now"));
			$cart->screenedBy = $agent;
			$cart->save();
			$gifts = $cart->getAllGifts();

			foreach($gifts as $gift) {
				//mark the gift as screened
				$gift->inScreeningQueue = 0;
				$gift->save();
			}
		}

	}

	public static function noteCart($shoppingCartId, $notes) {
		$cart = new shoppingCartModel($shoppingCartId);

		//if refunded, fuggedaboudeit
		if($cart->getTransaction()->refunded !== null) {
			return;
		}

		$cart->screenedNotes = $notes;
		$cart->save();
	}
}
