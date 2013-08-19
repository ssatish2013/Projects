<?php

env::includeLibrary("facebook/facebook");

class facebookController {

	public function install(){
		$facebookCredentials = settingModel::getPartnerSettings(null, 'facebook');
		$appId      = $facebookCredentials['appId'];

		$url = "http://www.facebook.com/dialog/pagetab?app_id=$appId&redirect_uri=".view::GetDirectFullUrl('facebook', 'installed');
		view::ExternalRedirect($url);
	}

	public function installed(){
		$facebookCredentials = settingModel::getPartnerSettings(null, 'facebook');
		$appId      = $facebookCredentials['appId'];
		$category = 'facebookpage';
		$value = request::get('tabs_added');
			//facebook allows install app to multiple page at same time, all install pages' id are in tabs_added parameter.
			//e.g.: /facebook/installed?tabs_added[483401765055641]=1&tabs_added[384051661690475]=1
			foreach($value as $pageid=>$taborder) {
				//uniqe key for page-app installation
				$pagekey = $pageid.'-'.$appId;
				//check if previously we recorded the same page-app, remove if found any.
				$destroyedSetting = new settingModel();
				$destroyedSetting->category=$category;
				$destroyedSetting->key=$pagekey;
				if ($destroyedSetting->load('category,key',"AND",false)){
					$destroyedSetting->destroy(true);
				}
				//create a new setting map the page-app to our partner-env, so later we can redirect correctly when the page-app loaded.
				$pagesetting = new settingModel();
				$envPath =  globals::partner();
				if(Env::main()->envName() != 'production'){
					$envPath = $envPath . '-' . Env::main()->envName();
				}
				//this new setting need to be accessible by all partners, so leave the partner and env null.
				$pagesetting->category = $category;
				$pagesetting->key = $pagekey;
				$pagesetting->value = $envPath;
				$pagesetting->save();
			}
		//go to home page when install finished
		$giftController = new giftController();
		$giftController->homeGet();
	}


	public function insecureRoute($url){
		$url = implode('/',$url);
		view::set('facebookLoginUrl',view::GetFullUrl() . $url);
		view::Render('facebook/login');
	}


	public function pageError(){
		view::RenderError( languageModel::getString("facebookError"),
						languageModel::getString("fbPageAppErrorTitle"),
						languageModel::getString("fbPageAppErrorDesc"),"", "/gift/home",
						 "", languageModel::getString("returnHomePage") );
	}

	public function route($url) {

		header("Content-Type: text/plain");

		$facebookCredentials = settingModel::getPartnerSettings(null, 'facebook');
		$appId      = $facebookCredentials['appId'];
		$secret     = $facebookCredentials['secret'];
		$requestId  = request::get('request_ids');
		if ( $requestId ) {
				$requestId = array_pop(explode(',',$requestId)); // @todo, fix this... for now, just use the last notification
			$facebook = new Facebook( array(
			'appId'  => $appId,
			'secret' => $secret
			));

			$user = $facebook->getUser();

			if ( $user ) {
			$accessToken = $facebook->getAccessToken();
			$request = $facebook->api( "$requestId", array('access_token' => $accessToken ));
			view::Redirect( "gift", "contribute", "guid/{$request['data']}" );

			} else {
					view::Set('facebookLoginUrl',$facebook->getLoginUrl());
					view::Render('facebook/login.tpl');
			}
		} else {
				//check if routing url has 'r' in it , which is an indicatation of a parnter-env redirect
				//e.g. https://apps.facebook.com/appname/r/partner-env will redirect to https://partner-env.giftingapp.com
				//TODO: remember to update all existent partner settings : redirect->baseUrl
				//		1. To use an unified FB app
				//		2. use https://apps.facebook.com/appname/r/partner-env format
				$domain = '';
				if (count($url)>1 && $url[0] == 'r'){
					//get redirect parnter-env part
					$envpath = $url[1];
					//consume the url parts so the rest can pass on.
					array_shift($url);
					array_shift($url);
					$domain = 'https://'.$envpath.'.giftingapp.com';
				}
				//get route key and see if we have the mapping in database
				else if (count($url)>1 && $url[0] == 'k'){
					$key = $url[1];
					$envpath = settingModel::getSettingRequired('facebookpage', $key);
					//consume the url parts so the rest can pass on.
					array_shift($url);
					array_shift($url);
					$domain = 'https://'.$envpath.'.giftingapp.com';
				}
				$url = $domain.'/'.implode('/',$url);
				view::ExternalRedirect($url.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
		}
	}

	public function tab() {
		//entry point for installed tab app on FB
		$facebookCredentials = settingModel::getPartnerSettings(null, 'facebook');
		$appId      = $facebookCredentials['appId'];
		$secret     = $facebookCredentials['secret'];

		$facebook = new Facebook( array(
				'appId'  => $appId,
				'secret' => $secret
		));
		//get installed page id
		$sr = $facebook->getSignedRequest();
		$pageid = $sr["page"]["id"];
		log::info('Page id from signed request: '. $pageid);

		if ($pageid){
			try {
				$pagekey = $pageid.'-'.$appId;
				$envpath = settingModel::getSettingRequired('facebookpage', $pagekey);
				if($envpath){
					log::info("redirect to partner-env mapped in setting: " . $envpath);
					view::ExternalRedirect('https://'.$envpath.'.giftingapp.com');
				}
				else{
					log::warn("envpath is empty, redirect cancelled. setting key:".$pagekey);
				}
			} catch (Exception $e) {
				log::error("Failed to retrieve installed page-app mapping from settings, key: ".$pagekey, $e);
			}
		}
		//page id is not present, try to use js solution, only works if FB secure browse enabled.
		else{
			view::Render('facebook/pageapp.tpl');
		}
	}

	public function login() {
		$gift = new giftModel(request::url('guid'));

		$facebookCredentials = settingModel::getPartnerSettings(null, 'facebook');
		$appId      = $facebookCredentials['appId'];
		$secret     = $facebookCredentials['secret'];
		$state = $gift->guid;
		$url = view::GetFullUrl('facebook', 'auth');

		$dialog_url = "https://www.facebook.com/dialog/oauth?client_id="
									. $appId . "&redirect_uri=" . urlencode($url)
									. '&state=' . $state . '&scope=email';
		echo("<script> top.location.href='" . $dialog_url . "'</script>");
	}

			public function authGet() {
		$facebookCredentials 	= settingModel::getPartnerSettings(null, 'facebook');
								$appId      		= $facebookCredentials['appId'];
								$secret     		= $facebookCredentials['secret'];
								$url 	    		= view::GetFullUrl('facebook', 'auth');

		$guid	    		= $_REQUEST['state'];
		$code 	    		= $_REQUEST['code'];

		$token_url = "https://graph.facebook.com/oauth/access_token?"
						 . "client_id=" . $appId . "&client_secret=" . $secret . "&redirect_uri=" . urlencode($url) . "&code=" . $code;

		$access_token = file_get_contents($token_url);

		$params = null;
		parse_str($access_token, $params);

		$graph_url = "https://graph.facebook.com/me?" . $access_token;

		$userInfo = json_decode(file_get_contents($graph_url));
		$user	  = $userInfo->id;
		$gift 	  = new giftModel($guid);
		$design   = $gift->getDesign();

		view::Set('metaTags', array(
			"og:title" => "A gift card for " . $gift->recipientName,
			"og:image" => $design->smallSrc,
			"fb:appId" => $appId,
			"og:url" => 'https://test-aaron.giftingapp.com'.$_SERVER['REQUEST_URI'],
			"og:type" => 'gift_card'
		));

		view::Set('namespaces', $namespaces);
		view::Set('user', $user);
		view::Set('recipient', $gift->recipientFacebookId);

		if( $user ) {
			if($gift->recipientFacebookId==$user){
				view::Set('facebookLoginUrl',view::GetFullUrl('claim', 'gift', array('guid'=>$gift->getCurrentRecipientGuid())));
			} else {
				view::RenderError( languageModel::getString("facebookError"), languageModel::getString("facebookErrorTitle"),
																 languageModel::getString("facebookErrorMsg"), "", "/gift/home",
																 "", languageModel::getString("returnHomePage") );
				die();
			}
		} else {
			view::RenderError( languageModel::getString("facebookError"), languageModel::getString("facebookErrorTitle"),
												 languageModel::getString("facebookErrorMsg"), "", "/gift/home",
												 "", languageModel::getString("returnHomePage") );
						die();
		}

		view::Render('facebook/auth.tpl');
		}

		public function securePic() {

				$lastWeek = strtotime("-7 days");
				$now = time();
				$tomorrow = strtotime("+1 day");

				header("Cache-Control: private, max-age=10800, pre-check=10800");
				header("Pragma: private");
				header("Expires: " . date( DATE_RFC822, $tomorrow ));
				if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) && ( strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) <  $tomorrow )) {
						// send the last mod time of the file back
						header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastWeek ).' GMT', true, 304);
						exit;
				}

				header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastWeek ) . ' GMT');
				header("Content-Type: image/jpeg");
				$uid = request::url("uid");
				$url="http://graph.facebook.com/" . preg_replace( "/[^0-9]/", "", $uid ) . "/picture?type=square";
				echo( file_get_contents ( $url ));
		}

	public function channel() {
		View::Render("facebook/channel");
	}

	public function giftObjGet(){
		$gift = new giftModel(request::url('guid'));
		$message = $gift->getCreatorMessage();
		$design = new designModel($gift->designId);
		$facebookCredentials = settingModel::getPartnerSettings(null, 'facebook');

		$appId      = $facebookCredentials['appId'];
		$state = $gift->guid;
		$url = view::GetFullUrl('facebook', 'auth');

		$dialog_url = "https://www.facebook.com/dialog/oauth?client_id="
									. $appId . "&redirect_uri=" . urlencode($url)
									. '&state=' . $state . '&scope=email';

		view::Set('appId',$facebookCredentials['appId']);
		view::Set('appName',$facebookCredentials['appName']);
		view::Set('objType',$facebookCredentials['objType']);
		view::Set('url', view::GetDirectFullUrl('facebook', 'giftObj', array('guid' => $gift->guid)));
		view::Set('title', languageModel::getString('giftCardName'));
		view::Set('msg', $message->message);
		//resampling design image
		$newsrc = facebookHelper::resampleImage($design->largeSrc);
		view::Set('img', $newsrc);
		view::Set('dialog_url',$dialog_url);
		view::Render("facebook/giftObj");
	}
}
