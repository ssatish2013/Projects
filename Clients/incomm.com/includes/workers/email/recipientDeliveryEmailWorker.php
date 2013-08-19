<?php
require_once(dirname(__FILE__)."/../../init.php");

class recipientDeliveryEmailWorker extends baseWorker implements worker {

	protected $queueName = 'recipientDeliveryEmailQueue';
	protected $routingKey = 'recipientDelivery';

	public function doWork($content) {
		$data = json_decode($content, true);
		$giftId = 0;
		$email = null;
		if (is_numeric($content)) {
			$giftId = $content;
		} else {
			$data = json_decode($content, true);
			$giftId = $data['giftId'];
			$email = $data['email'];
			$userId = $data['userId'];
		}

		$gift = new giftModel($giftId);
		$design = new designModel($gift->designId);
		$user = new userModel($userId);

		$recipientGuid = new recipientGuidModel();
		$recipientGuid->giftId = $gift->id;
		$recipientGuid->expires = date("Y-m-d H:i:s", strtotime("+1 day"));
		$recipientGuid->guid = randomHelper::guid(16);
		$recipientGuid->save();

		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);
		
		foreach ($gift->getMessages() as $message) { 
			if( $message->isContribution == 0) { 
				$transaction = $message->getShoppingCart()->getTransaction();
				view::SetObject($transaction);
			}
		}
		
		view::SetObject($recipientGuid);
		view::SetObject($gift);
		view::SetObject($design);
		$mailer = new mailer();
		$mailer->giftId = $gift->id;
		$mailer->workerData = $content;
		$mailer->userId = $user->id;
		$mailer->recipientName = $gift->recipientName;
		if ($email === null) {
			$mailer->recipientEmail = $gift->recipientEmail;
		} else {
			$mailer->recipientEmail = $email;
		}
		$mailer->template = 'recipientDelivery';
		$mailer->send();

		if ($gift->delivered === null) {
			$gift->delivered = date("Y-m-d H:i:s");
			$gift->save();
		}
		log::info("Email delivery sent for gift#$gift->id to $mailer->recipientEmail.");
	}
}
