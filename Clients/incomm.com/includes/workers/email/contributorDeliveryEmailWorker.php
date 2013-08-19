<?php
require_once(dirname(__FILE__)."/../../init.php");

class contributorDeliveryEmailWorker extends baseWorker implements worker { 

	protected $queueName = 'contributorDeliveryEmailQueue';
	protected $routingKey = 'contributorDelivery';

	public function doWork($content){
		$gift = new giftModel($content);
		$design = new designModel($gift->designId);
		
		//for self gifting , don't send out contributor email
		if ($gift->giftingMode == giftModel::MODE_SELF){
			log::info("This is self gifting gift, no contributor email required. gift#$gift->id");
			return;
		}

		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);

		view::SetObject($gift);
		view::SetObject($design);

		foreach ($gift->getMessages() as $message) {
			/* @var messageModel $message */
			$user = new userModel($message->userId);
			
			if (!$user->email) {
				log::warn("No email set for user#$user->id contributor message not sent. I won't try this again.");
				return;
			}
			
			$transaction = $message->getShoppingCart()->getTransaction();
			view::SetObject($transaction);
			
			$mailer = new mailer();
			$mailer->giftId = $gift->id;
			$mailer->messageId = $message->id;
			$mailer->transactionId = $transaction->id;
			$mailer->workerData = $content;
			$mailer->recipientName = ucfirst($user->firstName) . ' ' . ucfirst($user->lastName);
			$mailer->recipientEmail = $user->email;
			$mailer->template = 'contributorDelivery';
			$mailer->send();
			
			log::info("Sent contribution message for gift#$gift->id to $user->email.");
		}
	}
}
