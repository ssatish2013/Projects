<?php

require_once(dirname(__FILE__)."/../../init.php");

class receiptEmailWorker extends baseWorker implements worker {

	protected $queueName = 'receiptEmailQueue';
	protected $routingKey = 'receiptEmail';

	public function doWork($content) {
		$transaction = new transactionModel($content);

		$shoppingCart = new shoppingCartModel($transaction->shoppingCartId);
		$messages = $shoppingCart->getAllMessages();
		if (!$messages) {
			log::error("Cart#$shoppingCart->id has no messages, no gifts, no receipt sent.");
			return;
		}
		$creatorGift = $messages[0]->getGift();
		$lang = $creatorGift->language;
		globals::forcePartnerRedirectLoaderForBatchScript($creatorGift->partner, $creatorGift->redirectLoader, $lang);
		//fetch shopping cart and total, avoid stale data 
		$transaction->getShoppingCart()->getTotal();
		view::SetObject($transaction);
		view::set("transactionId", $transaction->authorizationId);
		$mailer = new mailer();
		$mailer->giftId = $creatorGift->id;
		$mailer->workerData = $content;
		$mailer->recipientName = ucfirst($transaction->firstName) . ' ' . ucfirst($transaction->lastName);
		$mailer->recipientEmail = $transaction->fromEmail;
		$mailer->template = 'receipt';
		$mailer->send();
		log::info("Receipt email sent for gift#$creatorGift->id to $transaction->fromEmail.");
	}
}
