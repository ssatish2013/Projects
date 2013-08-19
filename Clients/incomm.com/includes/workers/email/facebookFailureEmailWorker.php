<?php

require_once(dirname(__FILE__)."/../../init.php");

class facebookFailureEmailWorker extends baseWorker implements worker {

	protected $queueName = 'facebookFailureEmailQueue';
	protected $routingKey = 'facebookFailureEmail';

	public function doWork($giftId) {
		$gift = new giftModel($giftId);
		if (!$gift->recipientFacebookId) {
			// This should not be in this queue if there is no fbID
			log::error("gift $giftId did not have a facebookId, it should not have been queued!");
			return;
		}
		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);

		//original message
		$message = $gift->getCreatorMessage();
		$creator = new userModel($message->userId);
		$token = $message->facebookAccessToken;
		$user = new userModel($message->userId);

		view::Set('gift',$gift);

		$mailer = new mailer();
		$mailer->giftId = $gift->id;
		$mailer->workerData = $giftId;
		$mailer->recipientName = ucfirst($user->firstName) . ' ' . ucfirst($user->lastName);
		$mailer->recipientEmail = $user->email;
		$mailer->template = 'facebookFailure';
		$mailer->send();
		log::info("Sent facebook failure email to facebook id $gift->recipientFacebookId");
	}
}
