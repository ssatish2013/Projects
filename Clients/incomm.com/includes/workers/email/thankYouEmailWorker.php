<?php

require_once(dirname(__FILE__) . "/../../init.php");

class thankYouEmailWorker extends baseWorker implements worker {

	protected $queueName = 'thankYouEmailQueue';
	protected $routingKey = 'thankYouEmail';

	public function doWork($originalContent) {
		$content = json_decode($originalContent, true);
		$message = new messageModel($content['messageId']);
		$transaction = $message->getShoppingCart()->getTransaction();
		$gift = $message->getGift();
		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);

		view::set('purchaserName', $content['purchaserName']);
		view::set('purchaserEmail', $content['purchaserEmail']);
		view::set('recipientName', $content['recipientName']);
		view::set('thankYouText', $content['message']);
		view::SetObject($gift);
		view::SetObject($transaction);

		$mailer = new mailer();
		$mailer->giftId = $gift->id;
		$mailer->messageId = $message->id;
		$mailer->transactionId = $transaction->id;
		$mailer->workerData = $originalContent;
		$mailer->recipientName = $content['purchaserName'];
		$mailer->recipientEmail = $content['purchaserEmail'];
		$mailer->template = 'thankYou';
		$mailer->send();
		log::info("Thank you email sent for gift $gift->id.");
	}

}
