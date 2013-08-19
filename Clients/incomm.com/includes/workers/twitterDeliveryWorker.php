<?php

require_once(dirname(__FILE__) . "/../init.php");
Env::includeLibrary("OAuth");

class twitterDeliveryWorker extends baseWorker implements worker {

	protected $queueName = 'twitterDeliveryQueue';
	protected $routingKey = 'twitterDelivery';

	public function doWork($giftId) {
		$gift = new giftModel($giftId);
		if (!$gift->recipientTwitter) {
			// This should not be in this queue if there is no fbID
			log::error("gift $giftId did not have a twitter handle, it should not have been queued!");
			return true;
		}
		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);

		$message = $gift->getCreatorMessage();

		view::Set('recipientGuid', self::createGuid($gift));

		$text = languageModel::getString('twitterDeliveryMessage');

		$callback_url = view::GetDirectFullUrl('twitter', 'callback', array());

		$consumer_key = settingModel::getSetting('twitter', 'key');
		$consumer_secret = settingModel::getSetting('twitter', 'secret');

		$oauth_request_token = "https://api.twitter.com/oauth/request_token";
		$oauth_authorize = "https://api.twitter.com/oauth/authorize";
		$oauth_access_token = "https://api.twitter.com/oauth/access_token";

		$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
		$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret, $callback_url);

		$token = $message->twitterToken;
		$secret = $message->twitterSecret;
		$url = "https://api.twitter.com/1.1/direct_messages/new.json";

		list($uid, $other) = explode('-', $token);

		$params = array(
			"screen_name" => $gift->recipientTwitter,
			"text" => $text
		);

		$acc_token = new OAuthConsumer($token, $secret, 1);
		$req_req = OAuthRequest::from_consumer_and_token($test_consumer, $acc_token, "POST", $url, $params);
		$req_req->sign_request($sig_method, $test_consumer, $acc_token);

		$oc = new OAuthCurl();
		$reqData = $oc->fetchData($url, "POST", $req_req->to_postdata());
		if ($reqData['http_code'] == 200) {
			log::error("Failed to deliver twitter message for Gift #{$gift->id} to {$gift->recipientTwitter}");
		}
	}

	public function createGuid($gift) {
		$recipientGuid = new recipientGuidModel();
		$recipientGuid->giftId = $gift->id;
		$recipientGuid->guid = randomHelper::guid(16);
		$recipientGuid->expires = recipientGuidModel::getClaimLinkExpire();
		$recipientGuid->save();
		return $recipientGuid;
	}
}
