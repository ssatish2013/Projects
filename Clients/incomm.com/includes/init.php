<?php

class Env {
	private static $self = null;
	private static $formerSelf = null;

	private static $version = "2.0.0";
	private static $paypalLive = 'https://api-3t.paypal.com/nvp';
	private static $paypalIpnLive = 'https://www.paypal.com/cgi-bin/webscr';
	private static $paypalSandbox = 'https://api-3t.sandbox.paypal.com/nvp';
	private static $paypalIpnSandbox = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	private static $kountSandbox = 'https://awc.test.kount.net';
	private static $kountLive = 'https://awc.kount.net';

	protected static $pluginTypes = array(
		"payment",
		"promoTrigger",
		"inventory",
		"s3"
	);

	protected static $environmentSetup = array(
		'tom' => 'setupDev',
		'kash'=>'setupDev',
		'jon'=>'setupDev',
		'matt'=>'setupDev',
		'aaron'=>'setupDev',
		'ralph'=>'setupDev',
		'rollie'=>'setupDev',
		'nolte'=>'setupDev',
		'jun'=>'setupDev',
		'satish'=>'setupDev',
		'frank'=>'setupDev',
		'qa'=>'setupQa',
		'review'=>'setupQa',
		'staging'=>'setupProd',
		'production'=>'setupProd',
		'reportDev'=>'setupReportDev',
		'reportProd'=>'setupReportProd'
	);

	// Paths
	protected $webRoot;
	protected $includePath;
	protected $controllerPath;
	protected $helperPath;
	protected $modelPath;
	protected $definitionPath;
	protected $libraryPath;
	protected $templatePath;
	protected $cachePath;

	// Environment variables
	protected $domain;
	protected $envName;
  	protected $envType;

	// Encryption key name
	protected $encryptKeyName;

	// Database connections
	protected $master = null;
	protected $slave = null;

	// Database credentials
	protected $masterDatabase;
	protected $masterServer;
	protected $masterUser;
	protected $masterPass;

	protected $slaveDatabase;
	protected $slaveServer;
	protected $slaveUser;
	protected $slavePass;

	//message queue vars
	protected $mqUser;
	protected $mqPass;
	protected $mqServer;

	//mongo variables
	protected $mongo;
	protected $mongoDb;
	protected $mongoServer;
	protected $mongoPort;
	protected $mongoUser;
	protected $mongoPass;
	protected $mongoDatabase;

	protected $memcache=null;
	
	// Paypal endpoint
	protected $paypalEndpoint;
	protected $paypalIpnEndpoint;
	protected $kountEndpoint;
	
	// Gateway endpoint
	protected $gatewayEndpoint;

	public $revisionNumber;

	/**
	 *
	 * @return Env
	 */

	public static function main(){
		if ( self::$self == null ) {
			self::$self = new Env();
		}

		return self::$self;
	}

	public static function swapMain(partialEnv $env){
		if(is_null(self::$formerSelf)){
			self::$formerSelf = self::$self;
		}
		self::$self = $env;
	}

	public static function restoreMain(){
		self::$self = self::$formerSelf;
		self::$formerSelf = null;
	}

	public static function getEnvironmentList(){
		return self::$environmentSetup;
	}

	public static function getEncryptKeyName(){
		return self::main()->encryptKeyName;
	}

	public static function getGatewayEndpoint(){
		return self::main()->gatewayEndpoint;
	}

	public static function getPaypalEndpoint(){
		//for partner in production, this setting allow it to use paypal sandbox.
		if ('1' == settingModel::getSetting('paypal', 'useSandbox')){
			return self::$paypalSandbox;
		}
		else{
			return self::main()->paypalEndpoint;
		}
	}

	public static function getPaypalIpnEndpoint(){
		//for partner in production, this setting allow it to use paypal sandbox.
		if ('1' == settingModel::getSetting('paypal', 'useSandbox')){
			return self::$paypalIpnSandbox;
		}
		else{
			return self::main()->paypalIpnEndpoint;
		}
	}
	
	public static function getKountEndPoint() { 
		if(self::getEnvType() != "production") { 
			//this method grabs the kountUrl from the settings table
			if(settingModel::getSetting('kount', 'useSandbox') == '1') { 
				return self::$kountSandbox;
			} else { 
				return self::$kountLive;
			}
		} else { 
			return self::$kountLive;
		}
	}
	
	public static function getEnvName() {
		return self::main()->envName;
	}

	public static function getEnvType() {
		return self::main()->envType;
	}

	public static function cachePath(){
		return self::main()->cachePath;  // The getter for templatePath
	}

	public static function templatePath(){
		return self::main()->templatePath;  // The getter for templatePath
	}

	public static function includePath(){
		return self::main()->includePath;  // Getter for includePath
	}

	public static function webRootPath(){
		return self::main()->webRoot;  // Getter for webroot
	}

	public static function envName(){
		return self::main()->envName;  // Getter for includePath
	}

	public static function includeLibrary($library){
		return include_once(self::main()->libraryPath.'/'.$library.'.php');
	}

	public static function includeTest($test){
		return @include_once(self::main()->includePath.'/tests/'.$test.'.php');
	}

	public static function includePlugin($plugin){
		foreach(self::$pluginTypes as $pT){
			$pTSuffix = ucfirst($pT);
			if(substr($plugin, -1*strlen($pTSuffix))==$pTSuffix){
				if(@include_once(self::main()->libraryPath.'/'.$pT.'/'.$plugin.'.php')){
					return true;
				}
			}
		}

		return false; // nothing was included
	}

	public static function memcache(){
		if(self::main()->memcache===null){
			self::main()->memcache = new Memcache();
			self::main()->memcache->connect('localhost', 11211);
		}
		return self::main()->memcache;
	}

	public static function masterDbConn(){
		if(Env::main()->master===null){
			Env::main()->master = mysql_connect(Env::main()->masterServer, Env::main()->masterUser, Env::main()->masterPass);
			mysql_set_charset('utf8');
			mysql_select_db(Env::main()->masterDatabase,Env::main()->master);
		}
		return Env::main()->master;
	}

	public static function getMasterDbName(){
		return Env::main()->masterDatabase;
	}

	public static function getNewMasterDbConn() {
			Env::main()->master = mysql_connect(Env::main()->masterServer, Env::main()->masterUser, Env::main()->masterPass, true);
			mysql_set_charset('utf8');
			mysql_select_db(Env::main()->masterDatabase,Env::main()->master);
			return Env::main()->master;
	}

	public static function slaveDbConn(){
		if(Env::main()->slave===null){
			Env::main()->slave = mysql_connect(Env::main()->slaveServer, Env::main()->slaveUser, Env::main()->slavePass);
			mysql_set_charset('utf8');
			mysql_select_db(Env::main()->slaveDatabase,Env::main()->slave);
		}
		return Env::main()->slave;
	}

	public static function getNewSlaveDbConn() {
			Env::main()->slave = mysql_connect(Env::main()->slaveServer, Env::main()->slaveUser, Env::main()->slavePass, true);
			mysql_set_charset('utf8');
			mysql_select_db(Env::main()->slaveDatabase,Env::main()->slave);
			return Env::main()->slave;
	}

	public static function mongoConn() {
		if(Env::main()->mongo===null) {
			Env::main()->mongo = dbMongo::connect();
		}
		return Env::main()->mongo;
	}

	public static function mongoDb() {
		if(Env::main()->mongoDb===null) {
			Env::main()->mongoDb = dbMongo::selectDb();
		}
		return Env::main()->mongoDb;
	}

	public static function getMongo($var) {
		$var = 'mongo'.ucfirst($var);
		return Env::main()->$var;
	}

	public static function getMq($var) {
		$var = 'mq'.ucfirst($var);
		return Env::main()->$var;
	}

	protected function __construct(){

		$this->webRoot = $_SERVER['DOCUMENT_ROOT'];

		if($this->webRoot){
			$this->includePath = dirname($this->webRoot)."/includes";
		} else {
			$this->includePath = getcwd()."/includes";
		}
		$this->controllerPath = $this->includePath.'/controllers';
		$this->helperPath = $this->includePath.'/helpers';
		$this->modelPath = $this->includePath.'/models';
		$this->definitionPath = $this->includePath.'/definitions';
		$this->libraryPath = $this->includePath.'/libraries';
		$this->workerPath = $this->includePath.'/workers';
		if(!defined('SMARTY_DIR')){
			define('SMARTY_DIR',$this->libraryPath.'/view/');
		}
		$this->templatePath = $this->includePath.'/views';
		$this->cachePath = $this->includePath.'/../cache';

		if(isset($_SERVER['SERVER_NAME'])){
			$this->domain = $_SERVER['SERVER_NAME'];
		}

		if(isset($_SERVER['ENVNAME']) && $_SERVER['ENVNAME']){
			$this->envName = trim($_SERVER['ENVNAME']);
		} else if(@getenv('FRESH_ENV')){
			$this->envName = trim(getenv('FRESH_ENV'));
		} else {
			preg_match("/\/([^\/]*?)\/includes$/",$this->includePath,$matches);
			$this->envName = $matches[1];
		}



		// Include environment-specific settings
		if(isset(self::$environmentSetup[$this->envName])){
			$envMethod = self::$environmentSetup[$this->envName];
			if(method_exists($this,$envMethod)){
				$this->$envMethod();
			}
		}
	}

	protected function setupDev(){
		$this->encryptKeyName		='dev';

		$this->masterDatabase		='giftingapp-dev';
		$this->masterServer			='sql1.int.groupcard.com';
		$this->masterUser				='giftingapp-dev';
		$this->masterPass				='dev-password';

		$this->slaveDatabase		='giftingapp-dev';
		$this->slaveServer			='sql2.int.groupcard.com';
		$this->slaveUser				='giftingapp-test';
		$this->slavePass				='test-password';

		$this->mqServer					='localhost';
		$this->mqUser 					='giftingapp-dev';
		$this->mqPass						='dev-password';

		$this->mongoServer			='mon1.int.groupcard.com';
		$this->mongoPort				='27017';
		$this->mongoUser				='giftingapp-dev';
		$this->mongoPass				='dev-password';
		$this->mongoDatabase		='giftingapp-dev';
		$this->envType          ='dev';

		$this->paypalEndpoint		= self::$paypalSandbox;
		$this->paypalIpnEndpoint	= self::$paypalIpnSandbox;

		$this->gatewayEndpoint  = 'https://rtg.sandbox.incomm.com';

		$this->kountEndPoint		= self::$kountSandbox;

		$this->revisionNumber		= time();
	}

	protected function setupQa(){
		$this->encryptKeyName		='dev';

		$this->masterDatabase		='giftingapp-review';
		$this->masterServer			='sql1.int.groupcard.com';
		$this->masterUser				='giftingapp-rev';
		$this->masterPass				='review-password';

		$this->slaveDatabase		='giftingapp-review';
		$this->slaveServer			='sql2.int.groupcard.com';
		$this->slaveUser				='giftingapp-rev';
		$this->slavePass				='rev-password';

		$this->mqServer					='localhost';
		$this->mqUser 					='giftingapp-rev';
		$this->mqPass						='rev-password';

		$this->mongoServer			='mon1.int.groupcard.com';
		$this->mongoPort				='27017';
		$this->mongoUser				='giftingapp-rev';
		$this->mongoPass				='rev-password';
		$this->mongoDatabase		='giftingapp-review';
		$this->envType          ='qa';

		$this->paypalEndpoint		= self::$paypalSandbox;
		$this->paypalIpnEndpoint	= self::$paypalIpnSandbox;

		$this->gatewayEndpoint  = 'https://rtg.sandbox.incomm.com';

		$this->kountEndPoint            = self::$kountSandbox;

		$this->revisionNumber        =file_get_contents($this->includePath."/../revision.txt");
	}

	protected function setupProd(){
		$this->encryptKeyName		='crypt';

		$this->masterDatabase		='giftingapp-production';
		$this->masterServer			='sql1.int.groupcard.com';
		$this->masterUser				='giftingapp-prod';
		$this->masterPass				=file_get_contents('/media/ram/sqlPassword');

		$this->slaveDatabase		='giftingapp-production';
		$this->slaveServer			='sql2.int.groupcard.com';
		$this->slaveUser				='giftingapp-prod';
		$this->slavePass				=file_get_contents('/media/ram/sqlPassword');

		$this->mqServer					='rab1.int.groupcard.com';
		$this->mqUser 					='giftingapp-production';
		$this->mqPass						=file_get_contents('/media/ram/mqPassword');

		$this->mongoServer			='mon1.int.groupcard.com';
		$this->mongoPort				='27017';
		$this->mongoUser				='giftingapp-production';
		$this->mongoPass				=file_get_contents('/media/ram/mongoPassword');
		$this->mongoDatabase		='giftingapp-prod';
		$this->envType          ='production';

		$this->paypalEndpoint		= self::$paypalLive;
		$this->paypalIpnEndpoint	= self::$paypalIpnLive;

		$this->gatewayEndpoint  = 'https://rtg.incomm.com';

		$this->kountEndPoint            = self::$kountLive;

		$this->revisionNumber        =file_get_contents($this->includePath."/../revision.txt");
	}

	protected function setupReportDev() {
		$this->setupProd();
		$this->masterServer = 'rpt1.int.groupcard.com';
		$this->masterPass = file_get_contents('/media/ram/sqlPassProd');
		$this->slaveServer = 'rpt1.int.groupcard.com';
		$this->slavePass = file_get_contents('/media/ram/sqlPassProd');
		$this->envType = 'staging';
		$this->envName = 'staging';
	}

	protected function setupReportProd() {
		$this->setupProd();
		$this->masterServer = 'rpt1.int.groupcard.com';
		$this->slaveServer = 'rpt1.int.groupcard.com';
		$this->envType ='production';
		$this->envName = 'production';
	}

	public static function autoload($className){
		/*
		 * Walk through the include paths looking for className.php
		 * Had a bit of an issue with the current loader as @ was used
		 * to suppress errors in included scripts.
		 */
		$paths = array(
			'libraries/payment', 'libraries/fraud', 'controllers', 'helpers',
			'workers', 'workers/email', 'libraries', 'models', 'definitions',
			'exceptions', 'libraries/view/sysplugins', 'libraries/inventory',
			'libraries/promoTrigger', 'libraries/facebook'
		);
		$base = self::main()->includePath;

		// Special case for kount sdk classes
		if (preg_match('/^Kount_(Ris|Log|Util)_/', $className)) {
			return;
		}

		// Special case for smart plugins.
		if (stripos($className, 'smarty_') === 0) {
			$className = strtolower($className);
		}

		foreach ($paths as $path) {
			$fn = "$base/$path/$className.php";
			if (file_exists($fn)) {
                require_once($fn);
                return;
            }
		}

		$m = "autoload: $className not found in include_path: " . ini_get('include_path');
		log::warn($m);
	}

	public static function isDev() {
		return self::getEnvType() == "dev";
	}

	public static function getVersion() {
		return self::$version;
	}
}

function gc_error_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        //This error code is not included in error_reporting
        return;
	}

	if (globals::isUnitTest()) {
        error_log("$errfile:$errline: $errstr");
        return;
    }

	if ($errno == E_USER_ERROR) {
        log::error("PHP_ERROR ($errfile:$errline): $errstr", new Exception());
    } else {
        log::warn("PHP_WARNING ($errfile:$errline): $errstr", new Exception());
    }

    return true;
}
set_error_handler('gc_error_handler');


spl_autoload_register('Env::autoload');

// Set log appender to mongo. Disabled for now. logs/giftingapp.log is the default.
log::$appendFunction = 'dbMongo::mongoAppender';
//log::$file = Env::main()->includePath() . "/../../log/giftingapp.log";
log::$defaultEntry->context->program = basename($_SERVER["SCRIPT_NAME"]);

// Making sure that language is available to everything
language::init(globals::partner(), language::getDefault());
