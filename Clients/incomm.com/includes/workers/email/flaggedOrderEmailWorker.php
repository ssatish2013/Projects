<?php

require_once(dirname(__FILE__)."/../../init.php");

class flaggedOrderEmailWorker extends baseWorker implements worker { 

	protected $queueName = 'flaggedOrderEmailQueue';
	protected $routingKey = 'flaggedOrderEmail';

	public function doWork($transactionId) {
		$transaction = new transactionModel($transactionId);
		if(isset($transaction->shoppingCartId)) {
			$shoppingCart = new shoppingCartModel($transaction->shoppingCartId);
			$messages = $shoppingCart->getAllMessages();
			if(!empty($messages)) {
				$creatorGift = $messages[0]->getGift();
				$gift = $creatorGift;
				
				globals::forcePartnerRedirectLoaderForBatchScript($creatorGift->partner, $creatorGift->redirectLoader, $gift->language);
		
				view::set("transactionId", $transaction->authorizationId);
				
				view::SetObject($gift);
				view::SetObject($transaction);
				$mailer = new mailer();
				$mailer->giftId = $gift->id;
				$mailer->workerData = $transactionId;
				$mailer->recipientName = ucfirst($transaction->firstName) . ' ' . ucfirst($transaction->lastName);
				$mailer->recipientEmail = $transaction->fromEmail;
				$mailer->template = 'flaggedOrder';
				$mailer->send();
				log::info("Sent flagged order email for cart#$shoppingCart->id.");
			}
		}
	}
}
