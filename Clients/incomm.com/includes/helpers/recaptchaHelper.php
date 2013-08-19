<?php
/* function to verify recaptcha challenges 
 * see https://developers.google.com/recaptcha/docs/verify
 * 
 */
class recaptchaHelper {
	public static function verify($challenge,$user_response){
		// Post to recaptcha verify api
		$params =  array();
		$params['privatekey'] = settingModel::getSetting('recaptcha', 'privatekey');
		$params['remoteip'] = $_SERVER['HTTP_X_REAL_IP'];
		$params['challenge'] = $challenge;
		$params['response'] = $user_response;
		
		$url = 'http://www.google.com/recaptcha/api/verify';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		if ($response === FALSE) {
			log::error("recaptcha curl_request failed: url=$url: " . curl_error($ch));
			throw new Exception("curl error");
		}
		return 	explode("\n",$response);
	}	
}