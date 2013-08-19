<?php

set_time_limit(300);
ini_set('memory_limit', '6400000M');

class adminController {

	public static $defaultMethod = 'index';

	private $user;

	function __construct() {
		view::set('isAjax', utilityHelper::isAjax() );
		view::set('stylesheet', 'admin');

		// If the user is not logged in lets have them do that now
		$this->user = loginHelper::forceLogin();
		adminHelper::getSideBarOptions($this->user);
		view::Set('sideBar', adminHelper::$sideBar);
		view::Set('user', $this->user);

		// Enforce permissions
		$method = ucfirst( view::get('method') );
		if ( ! $this->user->hasPermission( 'admin' . $method )) {
			Env::main()->validationErrors['generalError'] = "Sorry you don't have permission for this area.";
			view::render('admin/index');
			throw new Exception('insufficient admin permissions');
		}
	}

	public function index() {
		view::render('admin/index');
	}

	public function passwordResetGet(){
		view::Set('statusMessage', '');
		view::render('admin/passwordReset');
	}

	public function passwordResetPost(){
		$user = new userModel();
		$password = request::unsignedPost('password');
		$errorMessages = array();
		
		if(userModel::isValidPassword($password, $errorMessages)) {
			$this->user->password = md5($this->user->email . $password);
			$this->user->save();
			view::Set("statusMessage", "Your password has been reset.");
		}
		else {
			view::Set("statusMessage", "Password not saved. " . $errorMessages['password']);
		}
		
		view::Set('user',$this->user);
		view::render('admin/passwordReset');
	}

	public function designs() {
		view::set( "designs", designModel::loadAllByPartner());

		view::set( 'designCategories', designModel::getAllCategories() );
		view::set( 'allProductGroups', productGroupModel::getAllProductGroups());
	    view::render('admin/covers');
	}

	public function designsPost() {
        $method = request::unsignedPost("action");
        echo adminDesignsHelper::$method();
	}

	public function languagePost() {
		$method = request::unsignedPost("action");
		echo adminLanguageHelper::$method();
	}

	public function customerSupportPost() {
		$method = request::unsignedPost("action");
		echo adminCustomerSupportHelper::$method($this->user->id);
	}

	public function customerSupportGet() {
		view::render('admin/customerSupport');
	}

	public function language() {
		view::render('admin/language');
	}

	public function migrationGet(){
		view::set('isProduction', env::getEnvType()=="production");
		view::set('envArray',  adminMigrationHelper::getEnvList());
		view::render('admin/migration');
	}

	public function migrationPost(){
		header("Content-Type: application/json");
		$src = request::unsignedPost('src');
		$dst = request::unsignedPost('dst');
		switch(request::unsignedPost('type')){
			case 'settings':
				echo adminMigrationHelper::compareEnvSettings($src, $dst);
				break;
			case 'language':
				echo adminMigrationHelper::compareEnvLanguage($src, $dst);
				break;
		}
	}

	public function doMigrationPost(){
		$success=false;
		switch(request::unsignedPost('mode')){
			case 'settings':
				$success=adminMigrationHelper::moveSettings(
					request::unsignedPost('src'),
					request::unsignedPost('dst'),
					request::unsignedPost('partner'),
					request::unsignedPost('cat'),
					request::unsignedPost('key')
				);
				break;
			case 'language':
				$success=adminMigrationHelper::moveLanguage(
					request::unsignedPost('src'),
					request::unsignedPost('dst'),
					request::unsignedPost('partner'),
					request::unsignedPost('cat'),
					request::unsignedPost('key')
				);
				break;
		}

		header("Content-Type: application/json");
		echo json_encode(array('success'=>$success));
	}

	public function settingsPost() {
		$key = request::unsignedPost("key");
		$value = request::unsignedPost("value");
		$category = request::unsignedPost("category");
		$encrypted = request::unsignedPost("category");
		$setting = new settingModel( array(
			"category" =>  $category,
			"key" => $key,
			"encrypted" => $encrypted,
			"partner" => globals::partner(),
			"env" => env::main()->envName()
		));
		$setting->value = $value;
		$setting->save();
	}

	public function settingsGet() {
		$settings = settingModel::getCategorizedByPartnerForAdminToolOnly();
		view::set( "settings", $settings );
		view::render('admin/settings');
	}

	public function settings() {
		view::render('admin/settings');
	}

	public function products() {
		view::set('products',productModel::getAll());
	  view::render('admin/products');
	}

	public function inventoryGet() {
		$product = new productModel(request::url('productId'));
		if(!$product->id){
			View::Redirect('admin', 'products');
		}
		view::SetObject($product);
		view::render('admin/inventory');
	}

	public function inventoryPost() {
		set_time_limit(0);
		$product = new productModel(request::url('productId'));
		if(!$product->id){
			View::Redirect('admin', 'products');
		}
		$csv = request::post('csv');
		$tmpFile = tempnam(sys_get_temp_dir(),"csv_");
		file_put_contents($tmpFile,$csv);

		ini_set("auto_detect_line_endings",true);
		$csvObj = new csv();
		$csvObj->load($tmpFile);
		$csvObj->connect();

		$h=$csvObj->getHeaders();


		$successCount = 0;
		for($i=0;$i<$csvObj->countRows();$i++){
			$row = $csvObj->getRow($i);

			$vals = array();

			foreach($row as $k=>$v){
				$vals[$h[$k]]=$v;
			}


			if($vals['pan']&&$vals['pin']){
				$inv = new inventoryModel();
				$inv->productId = $product->id;
				$inv->pin = $vals['pin'];
				$inv->pan = $vals['pan'];
				unset($vals['pin']);
				unset($vals['pan']);
				$auxData = new stdClass();
				foreach($vals as $k=>$v){
					$auxData->$k = $v;
				}

				$inv->auxData = $auxData;

				try{
					$inv->save();
					$successCount++;
				} catch (Exception $e){}

			}
		}

		if($successCount <1){
			Env::main()->validationErrors['generalError'] = "Please enter valid CSV";
			view::SetObject($product);
			view::set('csv',$csv);
			view::render('admin/inventory');
			return;
		} else {
			echo($successCount . " inventory rows added successfully");
		}

	}

	public function encryptGet(){
		view::Render('admin/encrypt');
	}

	public function encryptPost(){
		view::Set('encrypted',baseModel::encrypt(request::unsignedPost('data')));
		view::Render('admin/encrypt');
	}

	/*
	 * Below are the "helper" functions for the admin tools, maybe we should move
	 * those somewhere else at some point?
	 */

	public function refundGiftPost(){
		// @TODO This has not been tested with a frontend so ping me if there are any problems
		$giftId = new giftModel(request::post('giftId'));
		$helper = new paymentHelper();
		$helper->refundGift($giftId);
		// @TODO return some json
	}

	public function dashboardPost() {
		$method = request::unsignedPost("action");
		echo dashboardHelper::$method();
	}

	public function dashboardGet() {
		$events = eventTypeModel::loadAll();
		$permittedEvents = array();
		foreach($events as $event) {
			if($this->user->hasPermission('dashboard'.ucfirst($event->name))) {
				$permittedEvents[] = $event;
			}
		}
		view::set('events', $permittedEvents);
		view::render('admin/dashboard');
	}

	public function emailPost() {
		echo adminEmailHelper::emailData();
	}

	public function emailGet() {
		$templates = emailModel::getTemplates();

		view::set('events', $templates);
		view::render('admin/dashboard');
	}

	public function usersGet() {
		$user = $this->user;

		//grab all roles this user has access to create
		$roles = roleModel::loadAll();
		$partner = globals::partner();
		$canCreate = array();
		foreach($roles as $role) {
			if($this->user->hasPermission('canCreateRole'.ucfirst($role->name))) {
				if(!$role->restrictedToPartner || $partner == $role->restrictedToPartner) {
					$canCreate[$role->name] = $role->id;
				}
			}
		}

		//grab users this person has access to admin
		$visibleUsers = array();
		foreach($canCreate as $name => $id) {
			$users = userRoleModel::loadAll(array(
				'roleId' => $id,
				'enabled' => 1
			));
			foreach($users as $userRole) {
				$visibleUserIds[] = $userRole->userId;
			}
		}

		$visibleUsers = array();
		foreach(array_unique($visibleUserIds) as $id) {
			$visibleUsers[] = new userModel($id);
		}

		usort($visibleUsers, function($a, $b) {
			return -1 * strcmp($a->created,$b->created);
		});
		$visibleUsers = array_slice($visibleUsers, 0, 20);

		view::set("canCreate", $canCreate);

		view::set( "roles", $roles );
		view::set( "users", $visibleUsers );
		view::render("admin/users");
	}

	public function usersPost() {
        $method = request::unsignedPost("action");
        echo adminUsersHelper::$method();
	}

	public function fraudLogsGet(){
		view::Render('admin/fraudLogs');
	}

	public function fraudLogsPost(){

		$searchBy = request::unsignedPost('searchBy');
		$value = request::unsignedPost('searchParam');
		$query = array();
		switch($searchBy) {
			case 'giftId':
				$query = array('gifts.id' => (int) $value);
				break;
			case 'shoppingCartId':
				$query = array('shoppingCartId' => (int) ($value));
				break;
			case 'claimedGiftId':
				$query = array('giftId' => (int) $value);
				break;
			}

		$results = array();
		if(count($query)) {
			$results = dbMongo::find('fraudLogs', $query);
			$table = adminHelper::arrayToTable($results);
			view::Set('searchParam', $value);
			view::Set('table', $table);
		}

		view::Render('admin/fraudLogs');
	}

	public function monitoringGet(){
		$events = eventMonitorModel::loadAll();
		$namedEvents = array_map(function($eventObj) {
			// add the name to each of the objects so its easier to use the admin tool.
			if($eventObj->eventTypeId){
				$eventType = new eventTypeModel($eventObj->eventTypeId);
				$eventObj->name = $eventType->name;
			} else {
				$event = new eventModel($eventObj->eventId);
				$eventType = new eventTypeModel($event->typeId);
				$eventObj->name = $eventType->name . '+' . $event->name;
			}
			return $eventObj;
		}, $events);
		view::set('eventTypeDropDown', eventTypeModel::loadAll());
		view::set('eventDropDown', eventModel::loadAll());
		view::Set('events', $namedEvents);
		view::Render('admin/monitoring');
	}

	public function monitoringPost(){
		$monitor; // init this so we can use it outside the if block scope
		if(request::unsignedPost('id')){
			$monitor = new eventMonitorModel(request::unsignedPost('id'));
		} else {
			$monitor = new eventMonitorModel();
			if(request::unsignedPost('eventTypeId')){
				$monitor->eventTypeId = request::unsignedPost('eventTypeId');
			} else {
				$monitor->eventId = request::unsignedPost('eventId');
			}
		}

		$monitor->enabled = request::unsignedPost('enabled');
		$monitor->minimumPercent = request::unsignedPost('minimumPercent');
		$monitor->maximumPercent = request::unsignedPost('maximumPercent');
		$monitor->minimumHardLimit = request::unsignedPost('minimumHardLimit');
		$monitor->maximumHardLimit = request::unsignedPost('maximumHardLimit');
		$monitor->compareStartTime = request::unsignedPost('compareStartTime');
		$monitor->compareEndTime = request::unsignedPost('compareEndTime');
		$monitor->currentStartTime = request::unsignedPost('currentStartTime');
		$monitor->currentEndTime = request::unsignedPost('currentEndTime');
		$monitor->save();

		if(!utilityHelper::isAjax()){
			$this->monitoringGet();
		}
	}

	public function permissionsGet() {
		view::set('roles', roleModel::loadAll());
		view::set('permissions', permissionModel::loadAll());
		view::set('partners', partnerLoaderModel::getAllPartners());
		view::Render('admin/permissions');
	}


  public function permissionsPost() {
        $method = request::unsignedPost("action");
        echo adminPermissionsHelper::$method();
  }


	public function gatewayGet(){
		view::Set('txnId','TEST'.str_replace('.', '', microtime(true)));
		view::Set('dateTime',  gatewayInventory::getDateTime());
		view::Render('admin/gateway');
	}

	public function gatewayPost(){
		$method = request::unsignedPost("method");
		echo adminGatewayHelper::$method();
	}

	public function apiLogsGet() {
		$partner = dbMongo::distinct('apiLogs', 'partner');
		sort($partner);
		view::set('partners', $partner);

		$apiPartner = dbMongo::distinct('apiLogs', 'apiPartner');
		sort($apiPartner);
		view::set('apiPartners', $apiPartner);

		$calls = dbMongo::distinct('apiLogs', 'call');
		sort($calls);
		view::set('apiCalls', $calls);

		view::Render('admin/apiLogs');
	}

  public function apiLogsPost() {
  	$method = request::unsignedPost("action");
    echo adminApiLogsHelper::$method();
  }

	public function actionLogsGet() {
		// Get restricted partner list from users role
		$restrictedToPartners = array();
		foreach ($this->user->roles as $role) {
			// Skip the role which its restricted partner is not set
			if (!isset($role->restrictedToPartner)) {
				continue;
			}
			$restrictedToPartners[] = $role->restrictedToPartner;
		}
		// Filter out the non-restricted partners when partner restriction is set
		$partner = count($restrictedToPartners) > 0
			? array_intersect($restrictedToPartners, dbMongo::distinct('actions', 'partner'))
			: dbMongo::distinct('actions', 'partner');
		// Sort partner by name and assign it to template variable
		sort($partner);
		view::set('partners', $partner);

		$area = dbMongo::distinct('actions', 'area');
		sort($area);
		view::set('areas', $area);

		view::Render('admin/actionLogs');
	}

  public function actionLogsPost() {
  	$method = request::unsignedPost("action");
    echo adminActionLogsHelper::$method();
  }



	public function paymentMethodGet() {
		view::Render('admin/paymentMethod');
	}

	public function paymentMethodPost() {
		$paymentMethod = new paymentMethodModel();
		$paymentMethod->partner = globals::partner();
		$paymentMethod->pluginName = 'paypalPayment';
		$settings = new stdClass();
		$settings->apiUsername = request::unsignedPost('apiUsername');
		$settings->apiPassword = request::unsignedPost('apiPassword');
		$settings->signature = request::unsignedPost('signature');
		$paymentMethod->settings = $settings;
		$paymentMethod->save();

		$paymentMethod2 = new paymentMethodModel();
		$paymentMethod2->partner = globals::partner();
		$paymentMethod2->pluginName = 'paypalExpressPayment';
		$paymentMethod2->settings = $settings;
		$paymentMethod2->save();

		$_SESSION['flashMessage'] = "Paymant Method has been saved.";
		view::redirect('admin/paymentMethod');
	}

	public function helpTextEditorGet(){
		$helpTextOptions = helpArticleModel::loadAll(array('partner' => null));
		$helpTexts = helpArticleModel::loadAll(array('partner' => globals::partner()));
		foreach($helpTexts as $help){
			unset($helpTextOptions[$help->name]);
		}
		view::set('helpTextOptions', $helpTextOptions);
		view::set('helpTexts', $helpTexts);
		view::render('admin/helpTextEditor');
	}

	public function helpTextEditorPOST(){
		$helpArticle = new helpArticleModel();
		$helpArticle->partner = globals::partner();
		$helpArticle->name = request::unsignedPost('articleType');
		$helpArticle->value = request::unsignedPost('value');
		$helpArticle->language = 'en';
		$helpArticle->save();
		$_SESSION['flashMessage'] = "Your help text has been updated, it will take up to 3 minutes propigate to all servers";
		view::Redirect( 'admin' );
	}

	public function editHelpTextPOST(){
		$helpArticle = new helpArticleModel(request::unsignedPost('id'));
		$helpArticle->value = request::unsignedPost('value');
		$helpArticle->save();
		return json_encode( array(
			'message' => "Help article saved successfully!"
		));
	}

	public function termsAndConditionsGet(){
		view::render('admin/termsAndConditions');
	}

	public function termsAndConditionsPost(){
		$terms = new languageModel(array('partner' => globals::partner(), 'name' => 'cardTerms'));
		$terms->value = request::unsignedPost('value');
		$terms->save();
		$_SESSION['flashMessage'] = "Your terms and conditions have been updated, it will take up to 3 minutes propigate to all servers";
		view::Redirect( 'admin' );
	}

	public function optInGet(){
		view::render('admin/optIn');
	}

	public function optInPost(){
		optInHelper::adminList();
	}
}
