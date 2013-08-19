<?php
Env::includeLibrary("OAuth");
class twitterController{
	public function auth(){
		$callback_url = view::GetDirectFullUrl('twitter', 'callback', array()); 

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
	
	public function callback(){
		$callback_url = view::GetDirectFullUrl('twitter', 'callback', array());
		
		$consumer_key = settingModel::getSetting('twitter', 'key'); 
		$consumer_secret = settingModel::getSetting('twitter', 'secret');
		
		$oauth_request_token = "https://api.twitter.com/oauth/request_token"; 
		$oauth_authorize = "https://api.twitter.com/oauth/authorize"; 
		$oauth_access_token = "https://api.twitter.com/oauth/access_token"; 
		
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1(); 
		$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret, $callback_url); 
		$params = array(); 

		$acc_token = new OAuthConsumer($_SESSION['oauth_token'], $_SESSION['oauth_token_secret'], 1); 

		$acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $acc_token, "GET", $oauth_access_token); 
		$acc_req->sign_request($sig_method, $test_consumer, $acc_token); 

		$oc = new OAuthCurl(); 
		$reqData = $oc->fetchData("{$acc_req}&oauth_verifier={$_GET['oauth_verifier']}"); 

		parse_str($reqData['content'], $accOAuthData); 
		
		view::Set('oa',$accOAuthData);
		view::Render('twitter/callback');
	}
	
	public function isFollower(){
		header("Content-Type: application/json");
		
		$callback_url = view::GetDirectFullUrl('twitter', 'callback', array());
		
		$consumer_key = settingModel::getSetting('twitter', 'key'); 
		$consumer_secret = settingModel::getSetting('twitter', 'secret');
		
		$oauth_request_token = "https://api.twitter.com/oauth/request_token"; 
		$oauth_authorize = "https://api.twitter.com/oauth/authorize"; 
		$oauth_access_token = "https://api.twitter.com/oauth/access_token"; 
		
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1(); 
		$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret, $callback_url);
		
		$token = request::unsignedPost('token');
		$secret = request::unsignedPost('secret');
		$url = "https://api.twitter.com/1.1/friendships/exists.json";
		
		list($uid,$other)=explode('-',$token);
		
		$acc_token = new OAuthConsumer($token, $secret, 1);
		$params = array ("screen_name_a"=>request::unsignedPost('screenName'),"user_id_b"=>$uid);

		$req_req = OAuthRequest::from_consumer_and_token($test_consumer, $acc_token, "GET", $url, $params);
		$req_req->sign_request($sig_method, $test_consumer, $acc_token);

		$oc = new OAuthCurl(); 
		$reqData = $oc->fetchData($req_req->to_url());

		if($reqData['http_code']==200){
			echo $reqData['content'];
		} else {
			echo json_encode(false);
		}
	}
	
	public function followers(){
		$callback_url = view::GetDirectFullUrl('twitter', 'callback', array());
		
		$consumer_key = settingModel::getSetting('twitter', 'key'); 
		$consumer_secret = settingModel::getSetting('twitter', 'secret');
		
		$oauth_request_token = "https://api.twitter.com/oauth/request_token"; 
		$oauth_authorize = "https://api.twitter.com/oauth/authorize"; 
		$oauth_access_token = "https://api.twitter.com/oauth/access_token"; 
		
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1(); 
		$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret, $callback_url); 
	
		$token = request::unsignedPost('token');
		$secret = request::unsignedPost('secret');
		$url = "https://api.twitter.com/1.1/followers/ids.json";
		
		list($uid,$other)=explode('-',$token);
		
		$acc_token = new OAuthConsumer($token, $secret, 1);
		$params = array("cursor"=>-1,"user_id"=>$uid); 
		$ids = array();
		
		do{
			$req_req = OAuthRequest::from_consumer_and_token($test_consumer, $acc_token, "GET", $url, $params);
			$req_req->sign_request($sig_method, $test_consumer, $acc_token);

			$oc = new OAuthCurl(); 
			$reqData = $oc->fetchData($req_req->to_url());

			if($reqData['http_code']==200){
				$result = json_decode($reqData['content'],true);
				$ids = array_merge($ids,$result['ids']);
			}
		
		} while($params['cursor']=$result['next_cursor_str']);
		
		
		//only look up first 100 followers to avoid API rate limit.
		$omitted = count($ids) > 100 ? count($ids) - 100 : 0;
		$ids = array_slice($ids,0,100);
		
		$user_look_url = "https://api.twitter.com/1.1/users/lookup.json";
		$params = array("user_id"=>implode($ids,","));
		$req_req = OAuthRequest::from_consumer_and_token($test_consumer, $acc_token, "GET", $user_look_url, $params);

		$req_req->sign_request($sig_method, $test_consumer, $acc_token);
		$oc = new OAuthCurl();

		$reqData = $oc->fetchData($req_req->to_url());

				

		if($reqData['http_code']==200){

			echo "{\"users\":".$reqData['content'].",\"omitted\":".$omitted."}";

		}
		
	}
}