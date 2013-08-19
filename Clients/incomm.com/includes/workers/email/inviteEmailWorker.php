<?php
require_once(dirname(__FILE__)."/../../init.php");

class inviteEmailWorker extends baseWorker implements worker { 

	protected $queueName = 'inviteEmailQueue';
	protected $routingKey = 'inviteEmail';

	public function doWork($content) {
		try{
			$msg = json_decode($content);
			$gift = new giftModel();
			$gift->guid = $msg->giftGuid;
			$gift->load();
			if (!$gift->id) {
				throw new Exception("Unable to find gift by guid: $msg->giftGuid");
			}
				
			globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);
				
			view::SetObject($gift);
			view::Set('senderName', $msg->senderName);
			view::Set('message', $msg->message);
				
			$mailer = new mailer();
			$mailer->giftId = $gift->id;
			$mailer->workerData = $content;
			$mailer->recipientName = $msg->address;
			$mailer->recipientEmail = $msg->address;
			$mailer->template = 'invite';
			$mailer->send();
			log::info("Invite email sent for gift $gift->id, to $msg->address");
			return true;
		} catch (Exception $e) {
			$message = $e->getMessage();
			// If this is a validation failure, don't try again, it ain't gonna work a second time.
			if (preg_match("/.*validation failed.*/i", $message)) {
				log::warn("Failed to send invite email.", $e);
			} else {
				throw $e;
			}
		}
	}
}
