<?php
class emailHelper{
	public static function isValidEmail($email){
		if ($email){
			if(preg_match("/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i",$email)){
				return true;
			}
		}
		return false;
	}
	
	public static function stripEmailCharacters($string){
		$string = preg_replace("/[\n\r]/","",$string);
		return preg_replace("/\"/","\\\"",$string);
	}
	
	static public function stripNewLineCharacters($string){
		return preg_replace("/[\n\r]/","",$string);
	}
	
	public static function trackAllLinks($html,$guid){
		$trackingHtml = "<img src='".view::GetDirectFullUrl('pixel', 't', array('guid'=>$guid))."' border='0' width='1' height='1' />";
		$html=preg_replace_callback('/(<a(.+?)href\s?=[\s'."'".'"]?)([^\s'."'".'">]+)/', function($matches) use ($guid){
			return $matches[1].emailHelper::generateTrackingLink($matches[3],$guid);
		}, $html);
		$html=preg_replace_callback('/<\/body>/i', function($matches) use ($trackingHtml){
			return $trackingHtml.$matches[0];
		}, $html, 1, $count);
		if(!$count){
			$html .= $trackingHtml;
		}
		return $html;
	}
	
	public static function generateTrackingLink($url,$guid){
		$salt = "giftingApp";
		
		$object = array(
			"url"=>$url,
			"guid"=>$guid
		);
		ksort($object);
		$sig = substr(md5($salt.json_encode($object)),0,10);
		$object['sig']=$sig;
		$o = encodingHelper::urlSafeBase64Encode(json_encode($object));
		//get language option stored with the gift.
		$urlparams = array("o"=>$o);
		$email = new emailModel();
		$email->guid = $guid;
		if($email->load('guid')){
			$gift = new giftModel($email->giftId);
			$urlparams['language'] = $gift->language ? $gift->language : 'en';
		}
		$returnUrl = view::GetDirectFullUrl("email", "link", $urlparams);
		return $returnUrl;
	}
	
	public static function decodeTrackingObject($o){
		$salt = "giftingApp";
		
		$obj = encodingHelper::urlSafeBase64Decode($o);
		$obj = json_decode($obj,true);
		$sig = $obj['sig'];
		unset($obj['sig']);
		$expected = substr(md5($salt.json_encode($obj)),0,10);
		if($sig==$expected){
			return $obj;
		}
		
		return false;
	}

}