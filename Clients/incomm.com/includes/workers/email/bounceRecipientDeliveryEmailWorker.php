<?php

require_once(dirname(__FILE__)."/../../init.php");

class bounceRecipientDeliveryEmailWorker extends baseWorker implements worker {

	protected $queueName = 'bounceRecipientDeliveryEmailQueue';
	protected $routingKey = 'bounceRecipientDeliveryEmail';

	public function doWork($emailId) {
		$emails = emailModel::loadAll(array("id"=>$emailId));
		if (sizeof($emails) && $email = $emails[0]) {
			$gifts = giftModel::loadAll(array("id"=>$email->giftId));
			if (sizeof($gifts) && $gift = $gifts[0]) {
				if ($message = $gift->getCreatorMessage()) {
					if ($user = $message->getUser()) {
						globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);
						view::Set('gift',$gift);
						$mailer = new mailer();
						$mailer->giftId = $gift->id;
						$mailer->workerData = $emailId;
						$mailer->recipientEmail = $user->email;
						//$mailer->recipientEmail = 'tom@groupcard.com';
						$mailer->template = 'bounceRecipientDelivery';
						$mailer->send();
						log::info("Sent bounce email for gift $gift->id to $user->email");
					}
				}
			}
		} else {
			log::warn("Email not found for queued bounce worker task for gift $gift->id email $emailId.");
		}
	}
}
