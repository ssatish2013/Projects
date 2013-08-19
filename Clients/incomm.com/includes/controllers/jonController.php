<?php
class jonController{
public static $defaultMethod="index";


public function login(){
	$_SESSION['userId']=1;
}

public function loadBug(){

	header("Content-Type: text/plain");
	$gift = new giftModel();
	$gift->guid = "pd988xbwc6zjsecs";
	if($gift->load()){
		echo(json_encode($gift));
	} else {
		echo "Couldn't load gift\n";
	}
	
	echo("\n\n");
	$gift = new giftModel();
	$gift->guid="thisguiddoesnotexist";
	if($gift->load()){
		echo(json_encode($gift));
	} else {
		echo "Couldn't load gift\n";
	}
	
}

public function viewGet(){
	
	
	view::render('jon/view');
	echo view::Get('SCRIPT_NAME');
}

public function clearVars(){
	View::Set('key','value');
	print_r( View::main()->getTemplateVars() );
	View::clear(array('key'));
	print_r( View::main()->getTemplateVars() );
}

public function twitter() {
	Env::includeLibrary("OAuth");
	$giftId=2524;
	
		
		$gift = new giftModel($giftId);
		if(!$gift->recipientTwitter){
			// This should not be in this queue if there is no fbID
			log::error("gift $giftId did not have a twitter handle, it should not have been queued!");
			return true;
		}
		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);
		
		$message = $gift->getCreatorMessage();
		
		
		$text = "Testing 123";

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
		
		list($uid,$other)=explode('-',$token);
		
		$params = array(
				"screen_name"=>$gift->recipientTwitter,
				"text"=>$text
		);
		
		$acc_token = new OAuthConsumer($token, $secret, 1);
		$req_req = OAuthRequest::from_consumer_and_token($test_consumer, $acc_token, "POST", $url, $params);
		$req_req->sign_request($sig_method, $test_consumer, $acc_token);

		$oc = new OAuthCurl(); 
		$reqData = $oc->fetchData($url,"POST",$req_req->to_postdata());
		if($reqData['http_code']==200){
			return true;
		}
		
		return false;
}
	public function test(){
		
		$callback_url = View::GetDirectFullUrl('twitter', 'callback'); 

		$consumer_key = settingModel::getSetting('twitter', 'key'); 
		$consumer_secret = settingModel::getSetting('twitter', 'secret'); 

		$oauth_request_token = "https://api.twitter.com/oauth/request_token"; 
		$oauth_authorize = "https://api.twitter.com/oauth/authorize"; 
		$oauth_access_token = "https://api.twitter.com/oauth/access_token"; 

		$sig_method = new OAuthSignatureMethod_HMAC_SHA1(); 
		$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret, $callback_url); 

		$req_req = OAuthRequest::from_consumer_and_token($test_consumer, NULL, "GET", $oauth_request_token,array("oauth_callback"=>$callback_url));     
		$req_req->sign_request($sig_method, $test_consumer, NULL); 

		$oc = new OAuthCurl(); 
		$reqData = $oc->fetchData($req_req->to_url()); 

		parse_str($reqData['content'], $reqOAuthData); 

		$req_token = new OAuthConsumer($reqOAuthData['oauth_token'], $reqOAuthData['oauth_token_secret'], 1); 

		$acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $req_token, "GET", $oauth_authorize); 
		$acc_req->sign_request($sig_method, $test_consumer, $req_token); 

		$_SESSION['oauth_token'] = $reqOAuthData['oauth_token']; 
		$_SESSION['oauth_token_secret'] = $reqOAuthData['oauth_token_secret']; 

		Header("Location: $acc_req"); 
	}

	public function singleUse(){
		for($i=0;$i<100;$i++){
			$prefix='TEST-';
			$guid=randomHelper::guid(5,'BCDFGHJKLMNPQRSTVWXYZ');
			$pin = str_pad(rand(0,9999), 4, "0", STR_PAD_LEFT);
			
			$pc = new singleUsePromoCodeModel();
			$pc->code=$guid;
			$pc->pin=$pin;
			$pc->prefix=$prefix;
			$pc->save();
		}
	}

	public function regex(){
		$guid="1234567";
		$html ="<a href = http://www.google.com/>Google</a>";
		echo emailHelper::trackAllLinks($html, $guid);
	}
	
}

