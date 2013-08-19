<?php

class giftController {

	public static $defaultMethod = "home";

	public function __construct() {
		if(!globals::partner()){
			echo "Invalid Partner";
			throw new invalidPartnerException();
		}

		$fbCreds = settingModel::getPartnerSettings(null, 'facebook');

		$fbSettings = array(
			'appId' => isset($fbCreds['appId']) ? $fbCreds['appId'] : null
		);

		view::Set('fbSettings', $fbSettings);
		view::set("ui", settingModel::getPartnerSettings(null, "ui"));
		view::set("settingsArray", get_object_vars( view::get("settings")));
		view::set("jsLang", array(
				"phraseChooseYourOwnAmount"=>  languageModel::getString("phraseChooseYourOwnAmount")
		));
	}

	public function cartGet() {
		view::Redirect("cart", "checkout");
	}

	public function productsGet() {
		$mode = request::get('mode');
		$messageGuid = request::url( "messageGuid" );
		if ( $messageGuid ){
			$message = new messageModel( $messageGuid );
			$gift = new giftModel( $message->giftId );
			$mode = $gift->giftingMode;
			view::set("messageGuid", $messageGuid);
			view::set("did",$gift->designId);
		}

		switch ($mode){
			case giftModel::MODE_GROUP:
			case giftModel::MODE_SINGLE:
			case giftModel::MODE_SELF:
			case giftModel::MODE_VOUCHER:
				break;
			default:
				log::warn("Gifting mode is unknown, redirect to home page");
				$_SESSION['flashMessage'] = 'Please choose a gifting mode to continue.';
				view::Redirect('gift', 'home');
		}
		//get all products/designs at once and store in page js.
		$products = array();
		$defaultproducts = null;
		//if there are already gifts in shopping cart, only same currency products are allowed.
		if (shoppingCartModel::getCount()>0){
			$shoppingCart   = new shoppingCartModel();
			$cur = $shoppingCart->currency;
			$products[$cur] = productGroupModel::getDesignAndGroups($cur);
			$defaultCurrency = $cur;
		}
		else{
			foreach(productGroupModel::getProductCurrencies() as $cur=>$isDafault){
				$products[$cur] = productGroupModel::getDesignAndGroups($cur);
				if ($isDafault) $defaultCurrency = $cur;
			}
		}
		$uiSettings = settingModel::getPartnerSettings(null, 'ui');
		$page = array (
				'products'=> $products,
				'defaultCurrency'=>$defaultCurrency,
				'designCategories'=>designModel::getAllCategories(),
				'customScenes'=> designModel::loadAllScenes(),
				'productsPerPage'=> isset($uiSettings['productsPerPage']) ? $uiSettings['productsPerPage'] : 12,
				'forceLogoUpload'=> isset($uiSettings['forceLogoUpload']) ? $uiSettings['forceLogoUpload'] : 0
		);

		view::set("jsLang", array(
				"selectCategory"=>  languageModel::getString("selectCategory")
		));
		view::set("_PAGE", $page);
		view::set("giftingMode", $mode);
		view::set("isProductPage", 1);
		view::Render("gift/products.tpl");
	}

	public function inviteGet() {
		$guid = request::url( "guid" );
		$messageguid = request::url( "messageGuid" );

		$gift = new giftModel();
		$gift->guid = $guid;
		if (!$gift->load()){
			throw new exception ("gift guid is invalid in invite link, guid:$guid");
			return;
		}

		$message = new messageModel();
		$message->guid = $messageguid;

		if (!$message->load()){
			throw new exception ("message guid is invalid in invite link, guid:$messageguid");
			return;
		}

		$messages = $gift->getAllMessages();

		if (count($messages) > 0){
			foreach($messages as $message) {
				if( strcmp($message->guid, $messageguid) == 0 ) {
					if( ($message->refunded == null) || ($message->refunded == "") ) {
						view::set('shoppingCart',$message->getShoppingCart());
						view::set('gift', $gift);
						view::set('message', $message);
						view::Render("gift/invite.tpl");

						break;
					} else {
						view::RenderError( languageModel::getString("contributeError"), languageModel::getString("contributeErrorTitle"), languageModel::getString("contributeErrorMsg"), "", "/gift/home", "", languageModel::getString("contributeErrorOkText") );

						break;
					}
				}
			}
		}
		else{
			throw new exception ("can't find paid message of this gift, guid:$guid");
			return;
		}
	}

	public function allowGuestInvitePost() {
		$giftGuid   = request::unsignedPost("giftGuid");
		$allow    = request::unsignedPost("value");

		$gift = new giftModel();
		$gift->guid = $giftGuid;
		if ($gift->load()){
			$gift->allowGuestInvite = $allow;
			$gift->save();
			echo( json_encode( array( 'success' => true )));
		}
		else{
			echo( json_encode( array( 'success' => false )));
		}

	}


	public function orderConfirmGet() {
		$shoppingCart = new shoppingCartModel();
		view::setObject( $shoppingCart );
		view::Render("gift/orderconfirm.tpl");
	}

	public function contributeConfirmGet() {
		view::Render("gift/contributeconfirm.tpl");
	}


	public function checkoutErrorGet() {
		view::RenderError( languageModel::getString("orderError"), languageModel::getString("checkoutErrorTitle"),
			languageModel::getString("checkoutErrorMsg"), "/gift/home", "/gift/cart",
			languageModel::getString("sendEmailButtonCancel"), languageModel::getString("returnCheckout") );
	}

	public function homeGet() {
		$shoppingCart   = new shoppingCartModel();

		$group		= giftModel::MODE_GROUP;
		$single		= giftModel::MODE_SINGLE;
		$self		= giftModel::MODE_SELF;
		$voucher	= giftModel::MODE_VOUCHER;

		//pass dialog link id to page js
		$autoDialog = request::url('help');
		if ($autoDialog)
			view::set('autoDialog', $autoDialog);

		view::set('group', $group);
		view::set('single', $single);
		view::set('self', $self);
		view::set('voucher', $voucher);

		view::Render("gift/home.tpl");
	}

	public function helpGet() {
		$ui = settingModel::getPartnerSettings(globals::partner(), "ui");
		view::set("ui", $ui);
		view::set("videoFlag", $ui["hasCustomVideoOption"]);
		view::set("audioFlag", $ui["hasCustomRecordingOption"]);

		view::Render("gift/help.tpl");
	}
	
	public function createGet($did=null,$gid=null,$m=null) {
		// This is used for prefilling a facebook user
		$search = request::get("search");
		view::set( "search", $search );

		/*
		 * Check to see if the shopping cart already has a "contribution" and if
		 * so have them check out before they can create a "new" gift
		 */
		$shoppingCart = new shoppingCartModel();
		//Turn off forcecheckout, because the reason of this checking is unclear.
		//$shoppingCart->forceCheckoutOnCreate();


		$design = null;
		$product = null;
		$mode = null;
		$productGroupId = null;

		// If editing an existing message, load it by GUID
		$messageGuid = request::url( "messageGuid" );
		if ( $messageGuid ) {
			$message = new messageModel( $messageGuid );
			$user = new userModel( $message->userId );
			$gift = new giftModel( $message->giftId );

			$designId = request::get("did");
			$productGroupId = request::get("gid");

			//if messageGuid and did, gid both passed in
			//means user created the message and back to the product selector
			//in this case we need to switch to new design and group user re-selected
			if ($designId){
				$design = new designModel();
				$design->id = $designId;
				if (!$design->load()){
					throw new Exception("Can't load design, id: $designId");
					return;
				}
				$gift->designId = $design->id;
				$gift->save();
				if ($gift->productGroupId == $productGroupId) {
					$product = $gift->getProduct();
				}
				else {
					$message->amount = null;
					$message->save();
				}
			}
			else{
				$design = $gift->getDesign();
				$productGroupId = $gift->productGroupId;
				$product = $gift->getProduct();
			}

			$mode = $gift->giftingMode;
			$shippingDetail = new shippingDetailModel(array(
					'giftId' => $message->giftId,
					'shoppingCartId' => $message->shoppingCartId
			));

			view::setObject( $message );
			view::setObject( $gift );
			view::setObject( $user );
			view::setObject($shippingDetail);

			$deliveryDate = new DateTime($gift->deliveryDate, new DateTimeZone("UTC"));
			$deliveryDate->setTimezone(new DateTimeZone($gift->defaultTimeZoneKey));
			view::set('deliveryDate', $deliveryDate->format('Y-m-d H:i:s'));

			if(Date('Y-m-d', strtotime($gift->deliveryDate)) == Date('Y-m-d', strtotime("now"))) {
				view::set('deliverNow', true);
			}

		}
		// new gift, we should have the design and product group selected already
		else{
			$designId = $did?$did:request::get("did");
			$productGroupId = $gid?$gid:request::get("gid");
			$mode = $m?$m:request::get("mode");

			$design = new designModel();
			$design->id = $designId;
			if (!$design->load()){
				throw new Exception("Can't load design, id: $designId");
				return;
			}

 		}

 		$group = new productGroupModel();
 		$group->id = $productGroupId;
 		if (!$group->load()){
 			throw new Exception("Can't load product group, id: $productGroupId");
 			return;
 		}

 		view::set('currencySymbol', UtilityHelper::currencyToSymbol($group->currency));
 		view::set('currency', $group->currency);
 		view::set( 'fixedProducts', $group->getProducts(0));
 		view::set( 'openProducts', $group->getProducts(1));

 		// set the first fixed product as the default
 		foreach($group->getProducts(0) as $key => $val){
 			view::set( 'defaultAmount', $key);
 			break;
 		}

	        view::setObject( $shoppingCart );

		view::set('design', $design);
		view::set( "product", $product);
		view::set( "productGroup", $group);
        	view::set( 'customScenes', designModel::loadAllScenes() );

		//geo whitelists
		$geoSettings = settingModel::getPartnerSettings(null, 'geoip');
		if (isset($geoSettings['whitelist']))
			view::set('geoWhitelist', str_replace(',','|',$geoSettings['whitelist']));
		if (isset($geoSettings['blacklist']))
			view::set('geoBlacklist', str_replace(',','|',$geoSettings['blacklist']));

		$uiSettings = settingModel::getPartnerSettings(null, 'ui');
		// Physical delivery states, provinces and countries
		if ( intval($design->isPhysical) == 1 ) {
			view::Set('states', countriesHelper::getStates());
			view::Set('provinces', countriesHelper::getProvinces());
			view::Set('topCountries', countriesHelper::getTopCountries());
			view::Set('countries', countriesHelper::getCountries());
		}

		log::debug("Rendering gift/create.tpl messageGuid=" . ($messageGuid ? $messageGuid : 'none'));

		$subtemp = '';
		switch($mode){
			case giftModel::MODE_GROUP:
				$subtemp = '_groupcreate';
				break;
			case giftModel::MODE_SINGLE:
				$subtemp = '_singlecreate';
				break;
			case giftModel::MODE_SELF:
				$subtemp = '_selfcreate';
				break;
			case giftModel::MODE_VOUCHER:
				$subtemp = '_vouchercreate';
				break;
			default:
				log::warn("Gifting mode is unknown, redirect to home page");
				$_SESSION['flashMessage'] = 'Please choose a gifting mode to continue.';
				view::Redirect('gift', 'home');
		}

		$country = geoipModel::getData();

		if( ($country["country"] == "US") || ($country["country"] == "CA") ) {
			$tzValues = array("America/Los_Angeles", "America/New_York", "America/Denver", "America/Chicago", "America/Halifax", "America/Adak");
		} else {
			$tzValues = DateTimeZone::listIdentifiers();
		}

		$dateTime = new DateTime();

		$count = 0;
		$fullcount = 0;
		$offsetArr[$fullcount] = "";

		foreach($tzValues as $tzValue) {
			$tz = new DateTimeZone($tzValue);
			$dateTime->setTimeZone($tz);
			$tzKey = $dateTime->format('T');
			$tzOffset1 = $tz->getOffset(new DateTime("now"));
			$tzOffset = $tzOffset1 / 3600;

			if($tzOffset > 0) {
				$tzOffset = "+" . $tzOffset;
			}

			$tzLocationArray = $tz->getLocation();
			$tzLocation = $tzLocationArray['country_code'];

			$tzFlag = false;

			if ( $messageGuid ) {
				$tzFlag = ( trim($gift->timeZoneKey) == trim($tzValue) ) ? true : ( ( trim($gift->defaultTimeZoneKey) == trim($tzValue) ) ? true : false );
			}

			if(!in_array($tzOffset, $offsetArr)) {
				$tzArray[$count] = array('key' => $tzKey, 'value' => $tzValue, 'loc' => $tzLocation, 'offset' => $tzOffset, 'flag' => $tzFlag);
				$count++;
			}

			$offsetArr[$fullcount] = $tzOffset;
			$fullcount++;
		}

		view::set('tzCountry', $country["country"]);
		view::set('timeZoneValues', $tzArray);
		view::set('giftingmode', $subtemp);
		view::set('mode', $mode);
		view::set("jsLang", array(
				"datePickerNow"=>  languageModel::getString("datePickerNow")
		));
		view::set('unusedCoupons', array_key_exists ("coupons", $_SESSION) && is_array ($_SESSION['coupons']) ? array_keys ($_SESSION['coupons']) : array ());
		view::Render( 'gift/create' );
	}

	public function createPost() {
		$shoppingCart = new shoppingCartModel();

		// If were editing an existing gift, load that
		$messageGuid = request::url( "messageGuid" );

		if ( $messageGuid ) {
			$message = new messageModel( $messageGuid );
			$gift = new giftModel( $message->giftId );
			$mode = $gift->giftingMode;
			$shippingDetail = new shippingDetailModel(array(
					'giftId' => $message->giftId,
					'shoppingCartId' => $message->shoppingCartId
			));
			// check referrer, app scan csrf issue
			if (!array_key_exists ('HTTP_REFERER', $_SERVER) || parse_url ($_SERVER['HTTP_REFERER'], PHP_URL_HOST) != $_SERVER['SERVER_NAME']) {
				throw new exception ("create - referral check failed."); // added for app scan CSRF report
				return;
			}
		} else {
			$message = new messageModel();
			$gift = new giftModel();
			$mode = request::unsignedPost("giftGiftingMode");
			$shippingDetail = new shippingDetailModel();
		}
		
		switch($mode){
			case giftModel::MODE_GROUP:
				$validator = 'createGroupForm';
				break;
			case giftModel::MODE_SINGLE:
				$validator = 'createSingleForm';
				break;
			case giftModel::MODE_SELF:
				$validator = 'createSelfForm';
				break;
			case giftModel::MODE_VOUCHER:
				$validator = 'createVoucherForm';
				break;
			default:
				log::warn("Gifting mode is unknown, redirect to home page");
				$_SESSION['flashMessage'] = 'Please choose a gifting mode to continue.';
				view::Redirect('gift', 'home');
		}


		$validGift = $gift->validateGiftCreate($validator);
		$validMessage = $message->validateGiftCreate($validator);
		$validShippingDetail = $shippingDetail->validateGiftCreate($validator);
		$gift->_setDefaultTimeZone();

		/* Does the form validation and assigns all properties on the message
		 * If it fails it will set the things it needs to on the view and render the page again
		 */

		if ( $validGift && $validMessage && $validShippingDetail) {
			$product = new productModel($gift->productId);
			//get currency from post (selected product group)
			$gift->currency = $message->currency;

			$user = new userModel();

			facebookHelper::populateFacebookOnMessage($message);
			//facebook id
			$recipientFacebookId = request::unsignedPost('giftFacebookUID');
			if(isset($recipientFacebookId)) {
				$gift->recipientFacebookId = $recipientFacebookId;
			}

			twitterHelper::populateTwitterOnMessage($message);

			$recipientTwitter = request::unsignedPost('giftRecipientTwitter');
			if(isset($recipientTwitter)){
				$gift->recipientTwitter = $recipientTwitter;
			}

			$gift->assignDeliveryMethod();
			$gift->language = isset($_SESSION['language']) ? $_SESSION['language'] : null;
			$gift->save();

			// Assign the recording to a gift (if applicable)
			$recording = request::unsignedPost ('recordClient');
			if (!empty ($recording)) {
				$message->setRecording ($recording);
			}

			// YouTube Link
			$videoLink = request::unsignedPost ('giftVideoLink');
			if (formValidationHelper::validateVideoLink ()) {
				$message->setVideoLink ($videoLink);
			}
			// Apply the promo code (if applicable)
			$message->promoCode = request::unsignedPost('messagePromoCode');
			$message->assignToGift($gift);
			$message->save();
			
			$shoppingCart = new shoppingCartModel();
			$message->assignToShoppingCart($shoppingCart);
			$shoppingCart->assignCurrencyFromMessage($message);
			$shoppingCart->save();
			$message->save();
			log::info("Added message to cart: message $message->id, cart $shoppingCart->id.");
			
			if ($gift->physicalDelivery) {
				// Assign default shipping option (USPS)
				$shippingDetail->shippingOptionId = shippingOptionModel::findDefaultOptionId();
				// Assign gift id and shopping cart id
				$shippingDetail->giftId = $gift->id;
				$shippingDetail->shoppingCartId = $shoppingCart->id;
				// Save (insert new) shipping details
				$shippingDetail->save();
			}

			log::info("Saved gift $gift->id, product $gift->productId.");

			view::Redirect('cart', 'index');
		} else {
			// sucks this didn't validate ;-(
			$this->createGet(request::unsignedPost('giftDesignId'),request::unsignedPost('giftProductGroupId'),$mode);
		}
	}

	public function contributeGet($urlParts = array(), $guid = null) {
		$shoppingCart = new shoppingCartModel();

		$giftGuid = request::url('guid');
		$emailGuid = request::url('eg');

		$gift = new giftModel($guid ?: $giftGuid);

		// check to see if any dialogs should be displayed to the user
		switch(request::url('dialog')){
			case 'unsubscribed': // notify the invitee they have been unsubscribed to future confirmation reminders
				$showUnsubscribeInviteReminderDialog = true;
				break;
			default:
				$showUnsubscribeInviteReminderDialog = false;
				break;
		}
		view::set('showUnsubscribeInviteReminderDialog', $showUnsubscribeInviteReminderDialog);

		$status = 0;
		$messages = $gift->getAllMessages();

		if(count($messages) > 0) {
			foreach($messages as $message) {
				if(!$message->isContribute) {
					if( ($message->refunded == null) || ($message->refunded == "") ) {
						$status = 0; // If the gift has NOT been refunded, set the status to 0
						break;
					} else {
						$status = 1; // Else, set status to 1
					}
				}
			}
		} else {

			view::Redirect('gift', 'home');
		}

		if($status == 0) { // Run the underlying code snippet if the gift has NOT been refunded
			if ($gift->claimed) {
				view::setObject($gift);
				view::Render('gift/contributionExpired');
			} else {
				// If editing an existing message, load it by GUID
				$messageGuid = request::url('messageGuid');
				if (isset($messageGuid)) {
					$message = new messageModel($messageGuid);
					$user = new userModel($message->userId);
					$gift = new giftModel($message->giftId);
					// @TODO: Don't set message if it has been paid for
					view::setObject($message);
					view::setObject($user);
				}

				if (!isset($gift->id)) {
					// If no gift for this guid, forward to create page
					$_SESSION['flashMessage'] = languageModel::getString('giftErrorInvalidLink');
					view::Redirect('gift', 'home');
				}

				$messages = $gift->getAllMessages();
				$group = new productGroupModel($gift->productGroupId);

				view::set('currencySymbol', UtilityHelper::currencyToSymbol($group->currency));
				view::set('currency', $group->currency);
				view::set('fixedProducts', $group->getProducts(0));
				view::set('openProducts', $group->getProducts(1));
				view::set('defaultAmount', '0.00');
				view::set('emailGuid', $emailGuid);

				view::set('contributionAmount', $gift->unverifiedAmount);
				view::set('contributorCount', $gift->unverifiedContributorCount);

				$uiSettings = settingModel::getPartnerSettings(null, 'ui');
				$daysToShowCountDown = isset($uiSettings['daysToShowCountDown']) ? $uiSettings['daysToShowCountDown'] : 3;
				$daysToShowCountDown = intval($daysToShowCountDown);
				//calc count down
				$diff = strtotime($gift->deliveryDate) - time();
				$diff_min = floor($diff/60);
				$diff_hr = floor($diff/60/60);
				$diff_day = floor($diff/60/60/24);

				if ($diff_day>=0 && $diff_day<=$daysToShowCountDown){
					view::set('showCountDown',1);
					if ($diff_day>0){
						$countdown = $diff_day.'d';
					}
					else if ($diff_hr>0){
						$countdown = $diff_hr.'h';
					}
					else if ($diff_min>0){
						$countdown = $diff_min.'m';
					}
					view::set('countdown',$countdown);
				}
				else{
					view::set('showCountDown',0);
				}

				view::set('messages', $messages);
				view::setObject($gift);
				view::setObject($shoppingCart);
				view::Render('gift/contribute');
			}
		} else { // Else, display the custom error template
			view::RenderError( languageModel::getString("contributeError"), languageModel::getString("contributeErrorTitle"), languageModel::getString("contributeErrorMsg"), "", "/gift/home", "", languageModel::getString("contributeErrorOkText") );
		}
	}

	public function contributePost(){

		$shoppingCart = new shoppingCartModel();

		//Turn off forcecheckout, because the reason of this checking is unclear.
		//$shoppingCart->forceCheckoutOnContribute();

		$message = new messageModel(request::unsignedPost('messageGuid'));
		$gift = new giftModel(request::unsignedPost('giftGuid'));
		$email = new emailModel(request::unsignedPost('emailGuid'));

		$validMessage = $message->validateGiftCreate('createMessage');
		/* Does the form validation and assigns all properties on the message
		 * If it fails it will set the things it needs to on the view and render the page again
		 */
		if ( $validMessage ) {
			$user = new userModel();
			facebookHelper::populateFacebookOnMessage($message);

			$message->assignToGift($gift);
			$message->assignToShoppingCart($shoppingCart);
			$message->flagAsContribution();
			$message->emailId = (!is_null($email->id)) ? $email->id : NULL;

			// Assign the recording to a gift (if applicable)
			$recording = request::unsignedPost ('recordClient');
			if (!empty ($recording)) {
				$message->setRecording ($recording);
			}

			// YouTube Link
			$videoLink = request::unsignedPost ('giftVideoLink');
			if (formValidationHelper::validateVideoLink ()) {
				$message->setVideoLink ($videoLink);
			}

			$message->promoCode = request::unsignedPost('messagePromoCode');

			$shoppingCart->assignCurrencyFromMessage($message);

			$shoppingCart->save();
			$message->save();

			view::Redirect('cart', 'index');
		} else {
			// sucks this didn't validate ;-(
			//
			// Could be caused by over-contributed amount which is exceeding the
			// maximum limit of the product
			//
			// Validation errors are stored in Env::main()->validationErrors, so
			// by calling method contributionGet(), validation errors will be
			// diesplayed on the top of the page before header area
			$this->contributeGet(
				array('guid' => $gift->guid),
				$gift->guid
			);
		}
	}

	public function preview() {
		$shoppingCart = new shoppingCartModel();

		$message = new messageModel( request::url('messageGuid') );
		$gift = new giftModel( $message->giftId );
		$design = new designModel( $gift->designId );
		date_default_timezone_set($gift->defaultTimeZoneKey);
		$deliveryDateText = date("M j, o", strtotime($gift->deliveryDate));

		view::Set('deliveryDateText', $deliveryDateText);
		view::setObject( $message );
		view::setObject( $gift );
		$parts = explode(" ", $gift->recipientName);
		view::set("recipientFirst", array_shift( $parts ));
		view::setObject( $design );
		view::setObject( $shoppingCart );
		view::Render('gift/preview');
	}

	public function addPromotionPost(){
		//deprecated
	}

	public function successPost() {
		$giftGuid   = request::unsignedPost("giftGuid");
		$senderName = request::unsignedPost("senderName");
		$addresses  = explode(',', request::unsignedPost("addresses"));
		$gift =  new giftModel($giftGuid);

		$message    = $gift->eventMessage? $gift->eventMessage : languageModel::getString('inviteDescription');

		$worker = new inviteEmailWorker();
		foreach ($addresses as $address){
			$msg['address'] = $address;
			$msg['giftGuid'] = $giftGuid;
			$msg['message'] = $message;
			$msg['senderName'] = $senderName;
			$worker->send(json_encode($msg));
		}

		echo( json_encode( array( 'success' => true )));
	}

	public function confirmGet() {
		$c = new cartController();
		$c->confirmGet();
	}

	/**
	 * SecurePay will redirect here when there is an error
	 * during the Relay Request, or internally in SecurePay.
	 */
	public function paymentErrorGet() {
		Env::main()->validationErrors['generalError'] = 'Sorry, there was a problem processing your request, please try again.';
		$this->cartGet();
	}

	public function getPaypalExpressUrl() {
		$payment = new payment();
		$payment->loadPlugin();
		$shoppingCart = new shoppingCartModel();
		$location = $payment->plugin->getCheckoutUrl($shoppingCart);
		view::ExternalRedirect($location);
	}

	public function facebookDeliveryGet() {
		$gift = new giftModel( request::url('guid') );
		$design = new designModel( $gift->designId );
		$message = $gift->getCreatorMessage();

		view::Set('gift', $gift);
		view::Set('design', $design);
		view::Set('message', $message);

		view::Render('gift/facebookDelivery');
	}

	public function terms() {
		view::render('gift/terms.tpl');
	}

	public function customUpload() {
		// Come at me bro
		$temp = $_FILES['newCard'];

		// Check for errors
		if ( $temp['error'] > 0 ) {
				echo( '<textarea>' . json_encode(array(
					'valid' => false,
					'message' => 'It appears there was an error uploading your file!'
			)) . '</textarea>' );
			return;
		}

		// Get the path details
		$path_parts = pathinfo( $temp['name'] );

		// Y U NO EXTENSION
		switch ( strtolower( $path_parts['extension'] )) {
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg( $temp['tmp_name'] );
				break;
			case 'gif':
				$image = imagecreatefromgif( $temp['tmp_name'] );
				break;
			case 'png':
				$image = imagecreatefrompng( $temp['tmp_name'] );
				break;
			default:
				echo( '<textarea>' . json_encode(array(
					'valid' => false,
					'message' => 'Invalid filetype. We only accept jpg, gif or png file formats.'
				)) . '</textarea>' );
				return;
		}

		// MOAR DEETS
		list($width, $height) = getimagesize( $temp['tmp_name'] );
		ob_start();

		// Extension? Better drink my own piss
		switch ( strtolower( $path_parts['extension'] )) {
			case 'jpg':
			case 'jpeg':
				imagejpeg( $image, NULL, 90 );
				$type = "image/jpeg";
				break;
			case 'gif':
				imagegif( $image );
				$type = "image/gif";
				break;
			case 'png':
				imagepng($image, NULL, 2);
				$type = "image/png";
				break;
		}
		$output = ob_get_clean();
		$name = uniqid() . "." . $path_parts['extension'];

		// Initialize S3 library
		// FIXME get from settings
		$bucketName = 'gc-fgs';
		$keyId = "13XC7NDN0C35M66AR582";
		$secretKey = "mxk+DsUWIzg0n8OK+wp+Tw2ejNdAU8v0Tz9AQz89";
		$S3 = new S3( $keyId, $secretKey );
		$S3->putObject( $output, $bucketName, 'tmp/' . $name, S3::ACL_PUBLIC_READ, NULL, $type);

		echo( '<textarea>' . json_encode(array(
			'valid'		=> true,
			'url'		=> 'https://' . $bucketName . '.s3.amazonaws.com/tmp/' . $name,
			'width'		=> $width,
			'height'	=> $height
		)) . '</textarea>' );
	}

	public function customCropPost() {

		$imageUrl		= request::unsignedPost("image");
		$xStart		= request::unsignedPost("x");
		$xStop		= request::unsignedPost("x2");
		$yStart		= request::unsignedPost("y");
		$yStop		= request::unsignedPost("y2");

		// VALIDATE...
		// ALL THE URLS!
		if (
			strpos( $_SERVER['HTTP_REFERER'], 'giftingapp.com' ) !== FALSE &&
			strpos( $imageUrl, '//gc-fgs.s3.amazonaws.com/tmp/' ) !== FALSE
		) {
			adminDesignsHelper::upload( $imageUrl, array(
				"xStart"	=> $xStart,
				"xStop"		=> $xStop,
				"yStart"	=> $yStart,
				"yStop"		=> $yStop
			), true, false, 1, false );
		}
	}

	public function productListPost(){
		$currency = request::unsignedPost("currency");
		$categoryId = request::unsignedPost("categoryId");
		echo productHelper::getProductAndDesigns($currency, $categoryId);
	}

	public function productCatPost(){
		echo json_encode(designModel::getAllCategories());
	}

	public function productCurPost(){
		echo json_encode(productGroupModel::getProductCurrencies());
	}

	public function giftingoptionGet() {
		view::Render('gift/_giftingoption');
	}

	//simple validation if the code exists
	public function validatePromocodePost(){
		$promocode = request::unsignedPost('messagePromoCode');
		$productid = request::unsignedPost('param1');
		echo json_encode(promoHelper::isValidCode($promocode,$productid));
	}
}
