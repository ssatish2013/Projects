<?php
class request {

	private static $get=array();
	private static $post=array();
	private static $cookie=array();
	private static $url=array();
	private static $validated = false;
	
	public static function init($urlParameters){
		self::$url = $urlParameters;
		self::$get = $_GET;
		self::$post = $_POST;
		self::$cookie = $_COOKIE;
    // @TODO Unsetting this variables breaks
    // the Facebook PHP library, find a better
    // way to enforce use of the request class
    // unset( $_GET, $_POST, $_REQUEST, $_COOKIE );
	}

	public static function url($key){
		return isset( self::$url[$key] ) ? self::$url[$key] : null;
	}

	public static function get($key){
		return @self::$get[$key];
	}

	public static function post($key){
		if(self::$validated){
			return @self::$post[$key];
		}
		if(@self::$post['formGuid']){
			$formSignature = new formSignatureModel();
			$formSignature->guid=self::$post['formGuid'];
			if($formSignature->load() && $formSignature->isValid()){
				self::$validated = true;
				return self::$post[$key];
			}
		}
	}

	public static function getPostVars() { 
		$keys = array();
		foreach(self::$post as $key => $value) { 
			$keys[] = $key;
		}
		return $keys;
	}

	public static function getCookieVars() { 
		$keys = array();
		foreach(self::$cookie as $key => $value) { 
			$keys[$key] = $value;
		}
		return $keys;
	}

	public static function unsignedPost($key){
		if(isset(self::$post[$key])){
			return self::$post[$key];
		}
	}

	public static function cookie($key){
		return @self::$cookie[$key];
	}

	public static function formSignature(){
		$formSignature = new formSignatureModel();
		$formSignature->guid=randomHelper::guid(16);
		$formSignature->save();

		return $formSignature->guid;
	}

	public static function setUnsignedPost($array){
		// BOOM, if this isn't run from the command line you can't do this ;-)
		if(globals::isUnitTest()){
			self::$post = $array;
		}
	}
	
	public static function setSignedPost($array){
		// BOOM, if this isn't run from the command line you can't do this ;-)
		if(globals::isUnitTest()){
			self::$post = $array;
			self::$validated = true;
		}
	}
	
	public static function removeValidatedFlag(){
		self::$validated = false;
	}
}
