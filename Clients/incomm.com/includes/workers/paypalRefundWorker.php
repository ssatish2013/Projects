<?php
require_once(dirname(__FILE__)."/../init.php");

class paypalRefundWorker extends baseWorker implements worker {

	protected $queueName = 'paypalRefundQueue';
	protected $routingKey = 'paypalRefund';

	public function doWork($content) {
		log::info("Started work on shopping cart $content");
		$shoppingCart = new shoppingCartModel($content);
		
		$transaction = new transactionModel();
		$transaction->shoppingCartId = $shoppingCart->id;
		$transaction->load();
		
		log::info("Have transaction $transaction->id");
		
		$payment = new payment();
		$payment->loadPlugin($transaction->paymentMethodId);
		
		try {
			if ($transaction->externalTransactionId) {
				$payment->plugin->refund($transaction->externalTransactionId);
				$transaction->refunded = date("Y-m-d H:i:s");
				$transaction->save();
				$messages = messageModel::loadAll( $shoppingCart );
				foreach ($messages as $message) {
					/* @var $message messageModel */
					$message->amount = 0;
					$message->refunded = date("Y-m-d H:i:s");
					$message->save();
				}
			} else {
				$payment->plugin->voidAuthorization($transaction);
				$transaction->authorizationTime = null;
				$transaction->authorizationId = null;
				$transaction->save();
			}
		} catch (Exception $e) {
			// @TODO couldn't refund the transaction, is there any action we need to take?
			log::error("Failed to refund $transaction->externalTransactionId.", $e);
			return false;
		}
	}
}
