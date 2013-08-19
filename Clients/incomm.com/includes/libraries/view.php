<?php

ENV::includeLibrary('view/Smarty.class');

class view extends Smarty {
	private static $self = null;

	public static function main(){
		return self::$self ?: ( self::$self = new view() );
	}

	public static function Set($key, $value=NULL){

		if ( is_array( $key )) {
			foreach ( $key as $k => $v)
			self::main()->assign( $k, $v );
		} else {
			self::main()->assign( $key, $value );
		}
	}
	
	public static function Get( $key ) {
		return self::main()->getTemplateVars( $key );
	}
	
	public static function Clear ( $key ) {
		self::main()->clearAssign( $key );
	}

	public static function stripTagsExcept( $text, $allowTags ) {
		return strip_tags( $text, $allowTags );
	}

	public static function SetObject($obj){
		if(!is_object($obj)){
			return;
		}

		$key = substr(get_class($obj),0,-5);
		self::main()->assign($key,$obj);
	}

	public static function ReturnRender( $template ) {

		$rendered = "";

		if(strpos($template, '?')){
			// check to see if this has cache busting, and strip it if needed.
			$template = str_split($template, strpos($template, '?'));
			$template = $template[0];
		}

		$paths = array(
			Env::templatePath(). '/' . $template . '.tpl',
			Env::templatePath(). '/' . $template
		);

		$found = false;
		foreach( $paths as $path ) {
			if ( file_exists( $path ) ) {
				$rendered = trim( self::main()->fetch( $path ));
				$found = true;
				break;
			}
		}
		if (!$found) {
			throw new Exception("Template $template does not exist.");
		}

		return $rendered;
	}

	public static function Render( $template, $contentType = "text/html" ){
		if(substr($template,-4)==".tpl"){
			$template = substr($template,0,-4);
		}
		$uname = posix_uname();
		self::main()->assign('nodeName', $uname['nodename']);
		self::main()->assign("templateName", $template );
		self::main()->assign("__currentUser", get_current_user());

		//see if there's help for the template
		$article = utilityHelper::slashToCamel($template);
		$help = helpArticleModel::getArticle($article);
		if($help && strlen ($help->value) > 0) {
			self::main()->assign("helpPage", $article);
			self::main()->assign("helpContent", $help);
		}

		// Check to see if this is a mobile phone and get have a mobile template for this page
		if(mobileBrowserHelper::isMobile()){
			if(file_exists(Env::includePath() . "/views/mobile/" . $template . ".tpl")){
				$template = "mobile/" . $template;
			}
		}

		//setup our header before we render out template
		header('Content-type: ' . $contentType . '; charset=utf-8');
		echo self::main()->ReturnRender( $template );
	}

	public static function RenderJson($obj){
		echo(json_encode($obj));
	}

	public static function EscapeVar($var){
		return htmlentities($var, ENT_QUOTES, 'UTF-8');
	}

	public static function RenderError($errorLabel=null, $errorTitle=null, $errorMsg=null, $cancelUrl=null, $okUrl=null, $cancelText, $okText) {
		self::Set("errorLabel", $errorLabel);
		self::Set("errorTitle", $errorTitle);
		self::Set("errorMsg", $errorMsg);
		self::Set("cancelUrl", $cancelUrl);
		self::Set("okUrl", $okUrl);
		self::Set("cancelText", $cancelText);
		self::Set("okText", $okText);
		
		self::Render("common/error");
	}

	public static function Redirect($controllerName=null,$methodName=null,$parameters=null){
		$redirectUrl=self::GetUrl($controllerName,$methodName,$parameters);
		header("Location: $redirectUrl");
		die();
	}
	
	public static function ExternalRedirect($url){
		header("Location: $url");
		die();
	}

	public static function GetUrl( $controllerName = null, $methodName = null, $parameters = null ){
		$parts = array();		
		if ( $controllerName ) {
			$parts[] = $controllerName;

			if ( $methodName ) {
				$parts[] = $methodName;

				if ( $parameters ) {
          if ( is_array( $parameters )) {
            $keys = array_keys( $parameters );
            $firstKey = array_shift( $keys );
            if ( count( $parameters ) && is_array( $parameters ) && ! is_numeric( $firstKey ))  {
                foreach( $parameters as $k => $v ) {
                  $parts[] = $k;
                  $parts[] = $v;
              }
            }
          } else if ( is_string( $parameters )) {
            $parts[] = $parameters;
          }
				}
			}
		}
		return '/' . implode( '/', $parts );
	}
	
	public static function GetDirectFullUrl( $controllerName, $methodName, $parameters ){
		$envPath = '';
		if(Env::main()->envName() != 'production'){
			$envPath = '-' . Env::main()->envName();
		}
		
		$url = 'https://' . globals::partner() . $envPath . '.giftingapp.com' . self::GetUrl($controllerName, $methodName, $parameters);
		return $url;
	}
	
	public static function GetFullUrl( $controllerName, $methodName = null, $parameters = null ){
		$envPath = '';
		if(Env::main()->envName() != 'production'){
			$envPath = '-' . Env::main()->envName();
		}
		
		$url = 'https://' . globals::partner() . $envPath . '.giftingapp.com/_' . globals::redirectLoader() . self::GetUrl($controllerName, $methodName, $parameters);
		return $url;
	}

  public static function SmartyUrl( $params ) {
		if(array_key_exists( "direct", $params ) && $params['direct']){
			return @self::GetDirectFullUrl( $params['controller'], $params['method'], $params['params'] );
		}else if(array_key_exists( "full", $params ) && $params['full']){
			return @self::GetFullUrl( $params['controller'], $params['method'], $params['params'] );
		}
		return @self::GetUrl( $params['controller'], $params['method'], $params['params'] );
  }
	
	public static function pinFormat( $text, $format ) {
		$padLength = substr_count($format,'n');
		$text=str_pad($text, $padLength, '0', STR_PAD_LEFT);
		
		for($i=0;$i<strlen($text);$i++) {
		 $nIndex = strpos($format, "n");
		 $format[$nIndex] = $text[$i];
		}
		
		return $format;
	}

    /**
     * Convert the first char of each words in $value to uppercase.
     * If the setting 
     */
    public static function capitalizeName($value) {
        $settings = new settingsHelper();
        $capitalizeName  = $settings->ui->capitalizeName;
        return $capitalizeName ? ucwords($value) : $value;
    }
	
	public static function currencyToSymbol( $params ){
		return utilityHelper::currencyToSymbol($params['currency']);
	}
	
	public static function unsignedPost( $params ){
		return request::unsignedPost($params['key']);
	}

	public function __construct(){
		parent::__construct();

		$this->security_settings['PHP_TAGS']=false;
		$this->security_settings['ALLOW_CONSTANTS']=false;
		
		$this->security_settings['MODIFIER_FUNCS'] = array(
			"trim",
			"intval",
			"urlencode",
			"sizeof",
			"count",
			"substr",
			"in_array",
			"is_array"
		);

		// Temporary until we have a way for determining the current partner
		$this->security = true;
		$this->template_dir = Env::templatePath() . '/';
		$this->compile_dir	= Env::cachePath() . '/smarty/templates_c/';
		$this->config_dir		= Env::cachePath() . '/smarty/configs/';
		$this->cache_dir		= Env::cachePath() . '/smarty/cache/';
		$this->caching = false;
		$this->registerFilter('variable',array($this,'EscapeVar'));
		$this->registerPlugin("modifier","stripTagsExcept", "view::stripTagsExcept");
		$this->registerPlugin("modifier","pinFormat","view::pinFormat");
		$this->registerPlugin("modifier", "lightness","colorHelper::changeLuminosity");
		$this->registerPlugin("modifier", "slashToCamel","utilityHelper::slashToCamel");
		$this->registerPlugin("modifier", "capitalizeName", "view::capitalizeName");

		$this->registerPlugin( "function", "url", "view::SmartyUrl");
		$this->registerPlugin( "function", "currencyToSymbol", "view::currencyToSymbol");
		$this->registerPlugin( "function", "unsignedPost", "view::unsignedPost");

		$this->registerPlugin("modifier", "arithmetic","pixelHelper::arithmetic");
		$this->registerPlugin("function", "cssUrl","cssHelper::urlGen");
		$this->registerPlugin("function", "productDisplayName","productHelper::getDisplayName");

		$this->registerResource("lang", array(
			array("languageModel", "smartyTemplate"),
			array("languageModel", "smartyTimestamp"),
			array("languageModel", "smartySecure"),
			array("languageModel", "smartyTrusted")
		));

        // Variables loaded in every template
		$this->assign('partner', globals::partner() );
		
		$settings = new settingsHelper();
		$this->assign('settings', $settings);
		$lang = new languageHelper();
		$this->assign('lang', $lang);
		$this->assign('revisionNumber', Env::main()->revisionNumber);

		//workers (i.e. command line) use the view too! 
		//there's no session present then
		if(isset($_SESSION)) { 
			$this->assign('session', $_SESSION );
		}
		$this->assign('envName', env::main()->getEnvName() );
		$this->assign('envType', env::main()->getEnvType() );
	}
}
