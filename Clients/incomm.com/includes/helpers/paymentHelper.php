<?php
/**
 * High level functions around processing gifts and shopping carts. This is more than just payment,
 * it also schedules delivery and refund emails and such. It should probably be split and renamed to cartManager and
 * giftManager.
 */
class paymentHelper {

	const AVS_ZIP_MATCH = 'M';
	const AVS_ZIP_NO_MATCH = 'N';
	const AVS_ZIP_UNAVAILABLE = 'X';
	const AVS_STREET_MATCH = 'M';
	const AVS_STREET_NO_MATCH = 'N';
	const AVS_STREET_UNAVAILABLE = 'X';
	const CVV_MATCH = 'M';
	const CVV_NO_MATCH = 'N';
	const CVV_UNAVAILABLE = 'X';

	/**
	 * Called after a successfull authorization.
	 * $transaction is a
	 */
	public static function checkoutShoppingCart(shoppingCartModel $shoppingCart, transactionModel $transaction) {
		log::info("Begin processing payment for cart#$shoppingCart->id.");

		db::begin();
		try {
			//setup a new user based on our transaction
			$user = new userModel();
			$user->assignFromTransaction($transaction);
			$shoppingCart->reserveAllProducts();

			// Mark the transaction as paid (transactionModel::isPaid() uses externalTransactionID...)
			$transaction->status = 0;
			$transaction->authorizationTime = date("Y-m-d H:i:s");
			// Month must be padded with leading zero
			$transaction->expirationMonth = str_pad($transaction->expirationMonth, 2, '0', STR_PAD_LEFT);

			$transaction->save();

			if ($transaction->optIn) {
				optInModel::add($shoppingCart->partner,
						$transaction->firstName, $transaction->lastName,
						$transaction->phoneNumber, $transaction->fromEmail);
			}

			// Process the payment transaction.

			//skip kount query if the transaction is 0 amount.
			$isZero = ($transaction->amount == 0);
			if (!$isZero) {
				$fraud = new fraud($transaction,  $shoppingCart, null);
				$kount = new Kount($fraud);
				$fraud->isScreened = false;
				list($data, $kountResponse) = $kount->doQuery();
				$kount->log($data, $kountResponse->getAuto());
				if ($kountResponse->getAuto() === 'R') {
					log::info("Kount status: REVIEW");
					$fraud->isScreened = true;
				} else if ($kountResponse->getAuto() === 'D') {
					db::commit(); // Still commit, this is
					log::info("Cart#$shoppingCart->id declined by Kount.");

					screeningHelper::rejectCart($shoppingCart->id, "system");

					// Remove the authorize transaction record.
					$transaction->destroy(true);
					return;
				}
			}

			foreach(messageModel::loadAll($shoppingCart) as $message) {
				$message->assignToUser($user);
				$message->save();

				// Mark all gifts paid.
				$gift = new giftModel($message->giftId);
				$gift->paid = true;
				$gift->save();
			}

			// Ledger entry for this payment.
			$ledgers = array();
			foreach ( messageModel::loadAll( $shoppingCart ) as $message) {
				/* @var $message messageModel */
				$ledger = new ledgerModel();
				$ledger->amount = $message->getDiscountedPrice();
				$ledger->type = ledgerModel::typePayment;
				$ledger->shoppingCartId = $shoppingCart->id;
				$ledger->messageId = $message->id;
				$ledger->currency = $shoppingCart->currency;
				$ledger->startAudit();
				$ledgers[] = $ledger;
			}
			ledgerModel::saveAllLedgers($ledgers);

			$shoppingCart->promotionComplete();
			$shoppingCart->save();

			// If the order is screened, send an order confirmation email.
			if (!$isZero && $fraud->isScreened) {
				foreach($shoppingCart->getAllGifts() as $gift) {
					$gift->inScreeningQueue = 1;
					$gift->save();
				}
				$flaggedOrderWorker = new flaggedOrderEmailWorker();
				$flaggedOrderWorker->send($transaction->id);
				// The order is approved.
			} else {
				// Mark the cart as approved by fraud.
				$shoppingCart->approved = date('Y-m-d H:i:s');
				$shoppingCart->rejected = null;
				$shoppingCart->screenedBy = "Fraud System";
				$shoppingCart->save();
				//need to commit db changes at this point, so the email work can get updated data.
				db::commit();

				// Kick off the receipt worker job.
				$receiptWorker = new receiptEmailWorker();
				$receiptWorker->send($transaction->id);
			}
			db::commit();

			log::info("Successfully processed cart#$shoppingCart->id.");
		} catch (Exception $e) {
			db::rollback();
			throw $e;
		}
	}

	public function refundMessage($message, $userId){
		log::info("Refunding messahe#$message->id as adminUserId:$userId.");
		$success = true;
		$shoppingCart = new shoppingCartModel($message->shoppingCartId);
		//only want paid carts
		if(!$shoppingCart->getTransaction()->isAuthorized()) {
			log::debug("Cart#$shoppingCart->id is not authorized, skipping.");
			return false;
		}
		//only allow contributor message to refund individually
		if($message->isContribution != 1) {
			log::debug("Can't refund non-contributor's message#$message->id, skipping.");
			return false;
		}

		//refund is not allowed for claimed gift
		if($message->getGift()->claimed){
			log::debug("Can't refund message from a claimed gift, message#$message->id, skipping.");
			return false;
		}

		$innerMessages = messageModel::loadAll( $shoppingCart );
		foreach($innerMessages as $innerMessage){
			/* @var $innerMessage messageModel */
			$shoppingCartIds[] = $innerMessage->shoppingCartId;
			//exclude this message's gift , any other gift which might share shopping cart
			//with this refunding message, get refunded.
			if ($innerMessage->giftId != $message->giftId){
				$giftIds[] = $innerMessage->giftId;
			}
		}

		$giftIds = array_unique($giftIds);
		$shoppingCartIds = array_unique($shoppingCartIds);

		foreach($giftIds as $id){
			$gift = new giftModel($id);
			try {
				$gift->getAndDeactivateInventory();
				actionModel::logChanges('deactivate', array('deactivated' => 1), array(), array('giftId' => $id));
			} catch(deactivationException $e) {
				ledgerHelper::addNdr($id);
			} catch(Exception $e) {
				log::error("Unhandled Exception.", $e);
			}

			//refunded, so let's take it out of the screening queue if it's in there
			$gift->inScreeningQueue = 0;
			$gift->save();

			// For delivered gifts, send a refund notice to the recipient.
			if ($gift->delivered) {
				$refundEmailWorker = new refundEmailWorker();
				$refundEmailWorker->send(json_encode(array('giftId'=>$gift->id)));
			}
		}

		// Refund each shopping cart that has this gift in it.
		// This works because if a shopping cart contains a contribution, that's ALL
		// it will contain - exactly one contribution, no other items.
		foreach ($shoppingCartIds as $id) {
			//only send out the email if we can refund
			$transaction1 = new transactionModel();
			$transaction1->shoppingCartId = $id;
			$transaction1->load('shoppingCartId');

			if(self::refundShoppingCart($id)) {
				$transaction = new transactionModel();
				$transaction->shoppingCartId = $id;
				$transaction->load('shoppingCartId');
				$refundEmailWorker = new refundEmailWorker();
				$refundEmailWorker->send(json_encode(
						array(
								'transactionId'=>$transaction->id,
								'authorizationId' => $transaction1->authorizationId,
								'userId' => $userId,
						)
				));
				actionModel::logChanges('refund', array('refunded' => 1), array(), array(
						'shoppingCartId' => $id,
						'transactionId' => $transaction->id
				));
			}
			else $success = false;
		}
		if($success)
			log::info("message#$message->id refunded.");
		else
			log::info("message#$message->id refund failed.");
		return $success;

	}


	public function refundGift($giftId, $userId){
		log::info("Refunding gift#$giftId as adminUserId:$userId.");
		$success = true;
		$shoppingCartIds = array();
		$giftIds = array();

		$gift = new giftModel($giftId);
		$messages = messageModel::loadAll( $gift );
		foreach($messages as $message) {
			$shoppingCart = new shoppingCartModel($message->shoppingCartId);

			//only want paid carts
			if(!$shoppingCart->getTransaction()->isAuthorized()) {
				log::debug("Cart#$shoppingCart->id is not authorized, skipping.");
				continue;
			}
			$innerMessages = messageModel::loadAll( $shoppingCart );
			/*
			 * We do the double loop to make sure that we are getting all the child
			* transactions for the gift
			* If the above message is paid for (regarding the shopping cart) all of the
			* messages that belong to it should also be paid for
			*/
			foreach($innerMessages as $innerMessage){
				/* @var $innerMessage messageModel */
				$shoppingCartIds[] = $innerMessage->shoppingCartId;
				$giftIds[] = $innerMessage->giftId;
			}
		}

		/*
		 * Now that we have all the shopping cart ids we can start doing the
		 * refunds to paypal. These are set and forget, the worker will take care
		 * of notifing someone if we cannot refund the transactions
		 */


		$giftIds = array_unique($giftIds);
		$shoppingCartIds = array_unique($shoppingCartIds);

		foreach($giftIds as $id){
			$gift = new giftModel($id);
			try {
				$gift->getAndDeactivateInventory();
				actionModel::logChanges('deactivate', array('deactivated' => 1), array(), array('giftId' => $id));
			} catch(deactivationException $e) {
				ledgerHelper::addNdr($id);
			} catch(Exception $e) {
				log::error("Unhandled Exception.", $e);
			}

			//refunded, so let's take it out of the screening queue if it's in there
			$gift->inScreeningQueue = 0;
			$gift->save();

			// For delivered gifts, send a refund notice to the recipient.
			if ($gift->delivered) {
				$refundEmailWorker = new refundEmailWorker();
				$refundEmailWorker->send(json_encode(array('giftId'=>$gift->id, 'userId'=>$userId)));
			}
		}

		// Refund each shopping cart that has this gift in it.
		// This works because if a shopping cart contains a contribution, that's ALL
		// it will contain - exactly one contribution, no other items.
		foreach ($shoppingCartIds as $id) {
			//only send out the email if we can refund
			$transaction1 = new transactionModel();
			$transaction1->shoppingCartId = $id;
			$transaction1->load('shoppingCartId');

			if(self::refundShoppingCart($id)) {
				$transaction = new transactionModel();
				$transaction->shoppingCartId = $id;
				$transaction->load('shoppingCartId');
				$refundEmailWorker = new refundEmailWorker();
				$refundEmailWorker->send(json_encode(
						array(
							'transactionId'=>$transaction->id,
							'authorizationId' => $transaction1->authorizationId,
							'userId' => $userId,
						)
				));
				actionModel::logChanges('refund', array('refunded' => 1), array(), array(
						'shoppingCartId' => $id,
						'transactionId' => $transaction->id
				));
			}
			else $success = false;
		}
		if($success)
			log::info("Gift#$gift->id refunded.");
		else
			log::info("Gift#$gift->id refund failed.");
		return $success;
	}

	private function refundShoppingCart($shoppingCartId) {
		log::info("Refunding cart#{$shoppingCartId}.");
		$shoppingCart = new shoppingCartModel($shoppingCartId);

		$transaction = new transactionModel();
		$transaction->shoppingCartId = $shoppingCart->id;
		$transaction->loadOrException('shoppingCartId');

		$payment = new payment();
		$payment->loadPlugin($transaction->paymentMethodId);
		if ($transaction->externalTransactionId) {
			try {
				$payment->plugin->refund($transaction->externalTransactionId);
				// Update transactions.refunded, messages.refunded and RFCB field in Kount
				self::updateRefundStatus($shoppingCart, $transaction);
				return true;
			} catch(Exception $e) {
				self::_revertRefundStatus($shoppingCart);
				log::error("Refund failed for transaction #{$transaction->id}, externalTransactionId={$transaction->externalTransactionId}", $e);
				return false;
			}
		} else {
			try {
				$payment->plugin->voidAuthorization($transaction);
				//$transaction->authorizationTime = null;
				//$transaction->authorizationId = null;
				$transaction->save();
				// Update transactions.refunded, messages.refunded and RFCB field in Kount
				self::updateRefundStatus($shoppingCart, $transaction);
				return true;
			} catch (Exception $e) {
				// @TODO couldn't refund the transaction, is there any action we need to take?
				log::error("Couldn't void transaction #{$transaction->id}, authorizationId={$transaction->authorizationId}", $e);
				return false;
			}
		}
	}

	public static function updateRefundStatus(shoppingCartModel $shoppingCart,
		transactionModel $transaction)
	{
		$transaction->status = 0;
		$transaction->refunded = date('Y-m-d H:i:s');
		$transaction->save();
		$messages = messageModel::loadAll($shoppingCart);
		foreach ($messages as $message) {
			// @var $message messageModel
			$message->amount = 0;
			$message->refunded = date('Y-m-d H:i:s');
			$message->save();
		}
		kountHelper::refund($shoppingCart->id);
	}

	private static function _revertRefundStatus(shoppingCartModel $shoppingCart)
	{
		$shoppingCart->rejected = null;
		$shoppingCart->save();
	}

	/** This function will take NVPString and convert it to an Associative Array and it will decode the response.
	 * It is usefull to search for a particular key and displaying arrays.
	 * @nvpstr is NVPString.
	 * @nvpArray is Associative Array.
	 */

	public static function deformatNVP($nvpstr){

		$intial=0;
		$nvpArray = array();
		while(strlen($nvpstr)){
			//postion of Key
			$keypos= strpos($nvpstr,'=');
			//position of value
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr,$intial,$keypos);
			$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] =urldecode( $valval);
			$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
		}
		return $nvpArray;
	}

	public static function confirmUrl($partner, $cartId, $env = null) {
		// Success, output the redirect HTML.
		if (Env::getEnvType() == 'production') {
			$redirectUrl = 'https://' . $partner
				. '.giftingapp.com/cart/confirm/shoppingCart/' . $cartId;
		} else {
			$redirectUrl = 'https://' . $partner . '-'
				. (isset($env) ? $env : Env::getEnvName())
				. '.giftingapp.com/cart/confirm/shoppingCart/' . $cartId;
		}
		return $redirectUrl;
	}

}
