<?php
class globals{
	protected static $isUnitTest = false;
	protected static $partner = null;
	protected static $subpartner = null;
	protected static $redirectLoader = null;
	protected static $controller = null;
	protected static $method = null;
	protected static $methodType = null;
	protected static $_lang = null;

	public static function isUnitTest($value=null){
		if($value !==null){
			self::$isUnitTest=$value;
		}
		return self::$isUnitTest;
	}
	
	public static function redirectLoader($value=null){
		if($value !== null){
			self::$redirectLoader=$value;
			$_SESSION['redirectLoader']=$value;
		} else if(isset($_SESSION['redirectLoader']) && $_SESSION['redirectLoader']){
			self::$redirectLoader=$_SESSION['redirectLoader'];
		} 
		return self::$redirectLoader;
	}

	public static function partner($value=null){
		if($value !== null && (self::$isUnitTest || empty (self::$partner))){
			self::$partner = $value;
		} else if(self::$partner){
			return self::$partner;
		} else {
			// Get the current partner and set it on env::main()
			$domainParts = @explode(".",$_SERVER['SERVER_NAME']);
			$dashParts = @explode("-",$domainParts[0]);
			$safeLocalDomain = @strtolower($dashParts[0]);

			self::$partner = partnerLoaderModel::getPartner(partnerLoaderModel::TYPE_DOMAIN, $safeLocalDomain);

			return self::$partner;
		}
	}

	public static function controller($value=null){
		if($value !== null) {  
			self::$controller = $value;
		} else if(self::$controller){
			return self::$controller;
		} 
		return self::$controller;
	}
	
	public static function method($value=null){
		if($value !== null) {  
			self::$method = $value;
		} else if(self::$method){
			return self::$method;
		} 
		return self::$method;
	}
	
	public static function methodType($value=null){
		if($value !== null) {  
			self::$methodType = $value;
		} else if(self::$methodType){
			return self::$methodType;
		} 
		return self::$methodType;
	}
	
	public static function subpartner($value=null){
		if($value !==null){
			self::$subpartner = $value;
		} else if(self::$subpartner){
			return self::$subpartner;
		} else {
			// If promotion is set and blank or invalid, the promo will be cleared
			$guid = '';
			if(isset($_GET['promotion'])){
				$_SESSION['promoGuid'] = $_GET['promotion'];
				$guid = $_GET['promotion'];
			} else {
				$guid = isset($_SESSION['promoGuid']) ? $_SESSION['promoGuid'] : null;
			}
			
			if($guid){
				$promoSubpartner = new promoSubpartnerModel(array("partner"=>self::partner(),"guid"=>(string)$guid));
				if($promoSubpartner->id && $promoSubpartner->isActive()){
					self::$subpartner = partnerLoaderModel::getPartner(partnerLoaderModel::TYPE_PROMOTION, self::partner().'-'.$promoSubpartner->guid);
				} else {
					self::$subpartner ='';
				}
			}
			
			// Try redirect loader
			if(!self::$subpartner && self::redirectLoader()){
				self::$subpartner = partnerLoaderModel::getPartner(partnerLoaderModel::TYPE_SUBSITE, self::partner().'-'.self::redirectLoader());
			}
			
			return self::$subpartner;
		}
	}
	
	public static function forcePartnerRedirectLoaderForBatchScript($partner,$redirectLoader, $language = 'en'){
		self::$partner = $partner;
		self::$redirectLoader = $redirectLoader;
		
		// Set the log partner context.
		log::$defaultEntry->context->partner = $partner;
		
		// We need to re-init language to update it for the partner
		language::init($partner, $language);
	}

	public static function getRedirectUrl(){
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Setter or getting for i18n language
	 *
	 * By default, if there is no language set from web page, then grab the value
	 * from database or memcache (settings->ui->defaultLanguage), or set as 'en'
	 * if database value is not available either
	 *
	 * @access public
	 * @param mixed $lang string or null
	 * @return string
	 * @static
	 */
	public static function lang($lang = null) {
		// Method parameter $lang is not null
		// - Method is called as a setter
		if (isset($lang)) {
			self::$_lang = $lang;
		}
		// Method parameter $lang is null
		// - Method is called as a getter
		// - Class static var $_lang is null, means getter method is called
		//   for the first time, so we need to get lang value from static method
		//   language::lang() 
		elseif (!isset(self::$_lang)) {
			self::$_lang = language::lang(isset($_GET['language']) ? $_GET['language'] : null);
		}
		// Invisible condition else { ... }
		// - Method is called as a getter
		// - Getter method has been called before, so class static var
		//   has been set already

		return self::$_lang;
	}
}
