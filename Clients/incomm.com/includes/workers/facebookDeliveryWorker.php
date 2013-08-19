<?php

require_once(dirname(__FILE__) . "/../init.php");
require_once(dirname(__FILE__) . "/../libraries/facebook/facebook.php");

class facebookDeliveryWorker extends baseWorker implements worker {

	protected $queueName = 'facebookDeliveryQueue';
	protected $routingKey = 'facebookDelivery';

	public function doWork($giftId) {
		$gift = new giftModel($giftId);
		if (!$gift->recipientFacebookId) {
			// This should not be in this queue if there is no fbID
			log::error("gift $giftId did not have a facebookId, it should not have been queued!");
			return true;
		}
		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);

		$fbCreds = settingModel::getPartnerSettings($gift->partner, 'facebook');

		$facebook = new Facebook(array(
			'appId' => $fbCreds['appId'],
			'secret' => $fbCreds['secret']
		));

		//original message
		$message = $gift->getCreatorMessage();
		$design = new designModel($gift->designId);

		$creator = new userModel($message->userId);
		$token = $message->facebookAccessToken;

		view::Set('template', 'delivery');
		view::Set('gift', $gift);
		view::Set('message', $message);
		$objlink = view::GetDirectFullUrl('facebook', 'giftObj', array('guid' => $gift->guid));
		$actionmsg = "@[$gift->recipientFacebookId], $message->message";
		try {
			$data = array(
				'access_token' => $token,
				'gift' => $objlink,
				'message' => $actionmsg
			);
			$request = $facebook->api('/me/'.$fbCreds['appName'].':'.$fbCreds['action'], 'POST', $data);
			log::info("Sent delivery gift $giftId to $gift->recipientFacebookId's wall");
		} catch (Exception $e) {
			log::error("Failed to post delivery gift $gift->id to $gift->recipientFacebookId's wall, data: " . json_encode($data), $e);

			$f = new facebookFailureEmailWorker();
			$f->send($gift->id);
		}
	}
}
