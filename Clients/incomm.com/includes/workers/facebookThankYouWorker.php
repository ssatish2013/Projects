<?php

require_once(dirname(__FILE__) . "/../init.php");
require_once(dirname(__FILE__) . "/../libraries/facebook/facebook.php");

class facebookThankYouWorker extends baseWorker implements worker {

	protected $queueName = 'facebookThankYouQueue';
	protected $routingKey = 'facebookThankYou';

	public function doWork($content) {
		$obj = json_decode($content);
		$message = new messageModel($obj->messageId);
		$gift = new giftModel($message->giftId);
		$design = new designModel($gift->designId);
		globals::forcePartnerRedirectLoaderForBatchScript($obj->partner, $gift->redirectLoader, $gift->language);

		$fbCreds = settingModel::getPartnerSettings($obj->partner, 'facebook');

		$facebook = new Facebook(array(
			'appId' => $fbCreds['appId'],
			'secret' => $fbCreds['secret']
		));

		try {
			$data = array(
				'access_token' => $obj->accessToken,
				'picture' => $design->smallSrc,
				'description' => $obj->message,
				'name' => languageModel::getString('partnerDisplayName') . ' ' . languageModel::getString('giftCardNoun') . 's',
				'link' => view::getFullUrl('facebook', 'login', array('guid' => $gift->guid))
			);
			$request = $facebook->api('/' . $obj->wallId . '/feed', 'POST', $data);

			log::info("Sent delivery gift {$giftId} to {$gift->recipientFacebookId}'s wall");
		} catch (Exception $e) {
			log::error("Failed to process Facebook thank you for gift {$giftId} to Facebook ID {$gift->recipientFacebookId}. Data:" . json_encode($data), $e);
		}
	}
}
