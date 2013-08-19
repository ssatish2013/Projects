<?php
class cartController {

	public static $defaultMethod = 'index';

	public function __construct() {
		if(!globals::partner()){
			echo 'Invalid Partner';
			throw new invalidPartnerException();
		}

		$fbCreds = settingModel::getPartnerSettings(null, 'facebook');

		$fbSettings = array(
			'appId' => isset($fbCreds['appId']) ? $fbCreds['appId'] : null
		);

		view::Set('fbSettings', $fbSettings);
		view::set('ui', settingModel::getPartnerSettings(null, 'ui'));
		view::set('settingsArray', get_object_vars( view::get('settings')));
		view::set('jsLang', array(
			'phraseChooseYourOwnAmount' => languageModel::getString('phraseChooseYourOwnAmount')
		));
	}

	public function indexGet() {
		$shoppingCart = new shoppingCartModel();
		$messages = $shoppingCart->getAllMessages();
		foreach($messages as $message) {
			date_default_timezone_set($message->getGift()->defaultTimeZoneKey);
			if($message->isContribution) {
				view::Set('isContribution', 1);
			}

			$tzName = $message->getGift()->getTimeZone();
			$deliveryDate = new DateTime($message->getGift()->deliveryDate, new DateTimeZone("UTC"));
			$deliveryDate->setTimezone(new DateTimeZone($message->getGift()->defaultTimeZoneKey));
			if( ($message->getGift()->timeZoneKey != null) || (date("Y-m-d") != $deliveryDate->format("Y-m-d")) ) {
				$isToday = false;
			} else {
				$isToday = true;
			}

			view::set('timeZoneName', $tzName);
			view::set('deliveryDate', $deliveryDate->format('Y-m-d H:i:s'));
			view::set('isToday', $isToday);
		}

		view::SetObject($shoppingCart);

		if ($messages) {
			view::SetObject(new userModel($messages[0]->userId));
		}

		view::Render('cart/index');
		$_SESSION['cartTotal'] = $shoppingCart->getTotal();
	}
	
	public function modifyGiftPost() { 
		try { 
			$messageGuid = request::unsignedPost('messageGuid');
			view::Redirect('gift', 'create', array( 'messageGuid' => $messageGuid ) );
		} catch (Exception $e) { 
			log::error("Unable to modify gift with messageGuid: " . $messageGuid);
		}
	}
	
	public function removeFromCartPost() {
		try{
			$messageGuid = request::unsignedPost('messageGuid');
			$message = new messageModel();
			$message->guid = $messageGuid;
			if ($message->load()) {
				$message->shoppingCartId=null;
				$message->save();
				//remove all message items if message removed from cart
				$message->removeItems(true);
			}
			$shoppingCart = new shoppingCartModel();
			if (!$shoppingCart->getTotal()) {
				$shoppingCart->currency = null;
				$shoppingCart->save();
			}
		} catch (Exception $e) {}
		view::Redirect('cart', '');
	}

	public function checkoutGet() {
		date_default_timezone_set('America/New_York');
		
		$shoppingCart = new shoppingCartModel();
		$messages = $shoppingCart->getAllMessages();

		// Detect users GEO country with MaxMind IP database
		// Also fall back to settings.ui.defaultCount in case of failure
		$geo = geoipModel::getData();
		$ui = settingModel::getPartnerSettings(null, 'ui');

		$kountParams = http_build_query(array(
			'm' => settingModel::getSetting('kountConfig', 'Merchant_Id'),
			's' => $shoppingCart->id
		));
		$securepayParams = http_build_query(array(
			'tx_config' => slcPayment::getLogin(),
			'tx_transdataiv' => slcPayment::getCryptoIv(),
			'tx_transdata' => slcPayment::encryptTransactionData($shoppingCart),
			'tx_httphost' => $_SERVER['HTTP_HOST'],
			'tx_geocountry' => empty($geo['country'])
				? $ui['defaultCountry']
				: $geo['country'],
			'tx_version' => microtime(true)
		));

		foreach ($messages as $message) {
			if ($message->isContribution) {
				view::Set('isContribution', 1);
			}
		}

		view::SetObject($shoppingCart);
		if ($messages) {
			view::SetObject(new userModel($messages[0]->userId));
		}
		
		$supportedPaymentPluginNames = array_map(function($paymentMethod) {
			return $paymentMethod->pluginName;
		}, $shoppingCart->getSupportedPaymentPlugins());
		
		//view::Set('supportedPaymentPluginNames', array(paymentMethodModel::SECUREPAY_PLUGIN_NAME, paymentMethodModel::PAYPAL_EXPRESS_PLUGIN_NAME));
		view::Set('supportedPaymentPluginNames', $supportedPaymentPluginNames);
		view::Set('messages', $shoppingCart->getAllMessages());
		view::Set('kountParams', $kountParams);
		view::Set('securepayIframeSrc', slcPayment::getSetting('iframeSrc'));
		view::Set('securepayParams', $securepayParams);

		view::render('cart/checkout');
	}

	/**
	 * Receipt page - redirected here after securepay is done.
	 */
	public function confirmGet() {
		$shoppingCart = new shoppingCartModel(request::url('shoppingCart'));

		if($shoppingCart->sessionGuid != session_id()){
			throw new Exception('Invalid session');
		}
		$shoppingCart->loadForDisplay();
		$messages = $shoppingCart->getAllMessages();
		if (!$messages) {
			throw new Exception("Cart#{$shoppingCart->id} has no messages; we shouldn't be here.");
		}
		$message = $messages[0];

		//log gift events/details
		foreach($messages as $message) {
			date_default_timezone_set($message->getGift()->defaultTimeZoneKey);
			$gift = new giftModel($message->giftId);
			$design = $gift->getDesign();
			new eventLogModel("design", "cardArt", $design->alt);
		}

		$transaction = new transactionModel();
		$transaction->shoppingCartId = $shoppingCart->id;
		$transaction->load();

		view::setObject($message);
		view::SetObject( $transaction );
		view::SetObject( $shoppingCart );

		log::info("Displaying confirmation page for shoppingCart#{$shoppingCart->id}.");
		if ($shoppingCart->rejected || $transaction->hasCheckoutError()) {
			log::info("Displaying checkout error page for shoppingCart#{$shoppingCart->id}.");
			view::RenderError(
				languageModel::getString('orderError'),
				languageModel::getString('checkoutErrorTitle'),
				languageModel::getString('checkoutErrorMsg'),
				'/gift/home',
				'/cart',
				languageModel::getString('sendEmailButtonCancel'),
				languageModel::getString('returnCheckout')
			);
		} else {
			if (!!$shoppingCart->isCurrent) {
				$shoppingCart->isCurrent = 0;
				$shoppingCart->save();
			}
			$transAmount = ( $transaction->isAuthorized() ) ? $transaction->amount : 0;
			new eventLogModel("authorization", strtoupper($transaction->currency), $transAmount);
			view::Render('cart/checkoutFinish');
		}
	}

	public function recaptchaPost(){
		$challenge = request::unsignedPost("challenge");
		$response = request::unsignedPost("response");
		$result = recaptchaHelper::verify($challenge, $response);
		if (is_array($result) && $result[0]=='true'){
			echo( json_encode( array( 'success' => true )));
		}
		else{
			echo( json_encode( array( 'success' => false,'result'=>$result[1])));
		}
	}

	public function zeroPost(){
		$shoppingCart = new shoppingCartModel();
		//make sure we have zero total non-empty cart
		if (!(shoppingCartModel::getCount()>0 && $shoppingCart->getTotal()==0)){
			log::warn('this cart is either empty or has total amount greater than zero, can not checkout using this method.');
			$this->indexGet();
		}

		log::info("Valid payment notification for cart #$cart->id.");

		// Package up the variables, get a zero amount transaction back.
		$transaction = $this->createZeroTransaction($shoppingCart , $_REQUEST);
		$payment = new payment();
		$payment->loadPlugin();
		$transaction->assignPaymentMethod($payment);
		paymentHelper::checkoutShoppingCart($shoppingCart, $transaction);


		// Success
		view::Redirect('cart','confirm',array('shoppingCart'=>$cart->id));


	}

	private function createZeroTransaction(shoppingCartModel $cart, $params){
		$transaction = new transactionModel();
		$transaction->currency = $cart->currency;
		$transaction->amount = 0;
		$transaction->authorizationId = 'ZERO'.time(); //use a fake auth id
		$transaction->authorizationTime = null;
		$transaction->firstName = $params["tx_first_name"];
		$transaction->lastName = $params["tx_last_name"];
		$transaction->fromEmail = $params["tx_email"];
		$transaction->phoneNumber = $params["tx_phone_number"];
		$transaction->ccType = null;
		$transaction->setIpAddress ($_SERVER['HTTP_X_REAL_IP']);
		$transaction->domaintoolsCacheId = $transaction->getEmailDomainID ();
		$transaction->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$transaction->refunded = null;
		$transaction->shoppingCartId = $cart->id;
		$transaction->optIn = isset($params['tx_optin']) ? $params['tx_optin'] == "true" : false;
		$transaction->success = true;

		// Check if there is already a recorded transaction for this cart.
		$transactionCheck = new transactionModel();
		$transactionCheck->shoppingCartId = $cart->id;
		if ($transactionCheck->load()) {
			/*
			 * Invalidate the shopping cart so the user doesn't get stuck with this cart.
			* This could happen if the payment was recorded, and refund sent etc. But for
			* some reason the user didn't get redirected to the receipt page properly.
			*/
			$cart->isCurrent = 0;
			$cart->save();

			throw new validationException("A transaction has already been recorded for cart#$cart->id.");
		}

		return $transaction;
	}

	public function paypalExpressRedirectPost() {
		$payment = new payment();
		$payment->loadPlugin();
		$shoppingCart = new shoppingCartModel();
		$location = $payment->plugin->getCheckoutUrl($shoppingCart);
		view::ExternalRedirect($location);
	}

	public function paypalExpressGet() {
		$shoppingCart = new shoppingCartModel();
		if ($shoppingCart->paypalExpressToken != request::get('token')) {
			env::main()->validationErrors['generalError'] = 'Sorry, there was a problem processing your request, please try again.';
			view::Redirect('gift', 'cart');
		}

		$shoppingCart->paypalExpressPayerId = request::get('PayerID');
		$shoppingCart->save();

        view::set('paypalConfirm', true);
		view::SetObject($shoppingCart);
		view::Render('cart/paypalConfirm');
	}

	public function paypalExpressPost() {
		$shoppingCart = new shoppingCartModel();
		$transaction = new transactionModel();

		$payment = new payment();
		$payment->loadPlugin();

		$transaction->assignPaymentMethod($payment);
		$transaction->assignShoppingCart($shoppingCart);
		$transaction->setIpAddress ($_SERVER['HTTP_X_REAL_IP']);
		$transaction->domaintoolsCacheId = $transaction->getEmailDomainID ();

		/*
		 * @TODO get the shopping cart returning the amount
		 * @TODO make sure the CC form does country for the card
		 */
		$transaction->amount = $shoppingCart->getTotal();

		try {
			$shoppingCart->reserveAllProducts();
			$details = $payment->plugin->getDetails($shoppingCart->paypalExpressToken);

			$transaction->assignFromPaypalExpress($details);
			//$fraud = new fraud($transaction, $shoppingCart, null);

			//setup a new user based on our transaction
			$user = new userModel();
			$user->assignFromTransaction($transaction);

			foreach (messageModel::loadAll($shoppingCart) as $message) {
				$message->assignToUser($user);
				$message->getGift ()->domaintoolsCacheId = $message->getGift ()->getEmailDomainID ();
				$message->getGift ()->save ();
				$message->save();
			}

			//call paypal checkout first, only if payment success then pass to kount.
			$payment->plugin->pay($transaction, $shoppingCart);
			$transaction->transactionComplete ($payment->plugin->response, 0);
			$transaction->save();

			$fraud = new fraud($transaction, $shoppingCart, null);
			$kount = new Kount($fraud);
			$fraud->isScreened = false;
			list($data, $kountResponse) = $kount->doQuery();
			$kount->log($data, $kountResponse->getAuto());
			if ($kountResponse->getAuto() === 'R') {
				log::info('Kount status: REVIEW');
				$fraud->isScreened = true;
			} else if ($kountResponse->getAuto() === 'D') {
				//refund if kount rejects the order
				try{
					$payment->plugin->refund($transaction->externalTransactionId);
					log::info("Cart#{$shoppingCart->id} declined by kount, refunded.");
					//remove the transaction so user can try again
					$transaction->destroy(true);
				}
				catch(Exception $e) {
					log::error("Refund failed.", $e);
				}
				env::main()->validationErrors['generalError'] = 'Sorry, there was a problem processing your request, please try again.';
				$this->indexGet();
				return;
			}

			$transaction->fraudLogId = $fraud->fraudLogId;
			$transaction->save();
			$fraud->transaction = $transaction;

			// $shoppingCart->transactionComplete($transaction); // now handled by IPN for PayPal Express Transactions
			$shoppingCart->promotionComplete();
			$shoppingCart->save();
			$fraud->shoppingCart = $shoppingCart;
			if (!$fraud->isScreened && array_key_exists ('PAYERSTATUS', $details) && strtolower ($details['PAYERSTATUS']) == 'verified' && strtolower (@$payment->plugin->response['PAYMENTTYPE']) == "instant") {
				$receiptWorker = new receiptEmailWorker();
				$receiptWorker->send ($transaction->id);
			} else {
				$flaggedOrderWorker = new flaggedOrderEmailWorker();
				$flaggedOrderWorker->send ($transaction->id);
			}
		} catch (Exception $e) {
			log::error('Error during paypal express.', $e);
		}

		if ($fraud->isScreened) {
			foreach($shoppingCart->getAllGifts() as $gift) {
				$gift->inScreeningQueue = 1;
				$gift->save();
			}
		} else if (!$fraud->isRejected) {
			$shoppingCart->approved = date('Y-m-d H:i:s');
			$shoppingCart->screenedBy = 'Fraud System';
			$shoppingCart->save();
		}

//		promoHelper::clearCoupons();
		view::Redirect('cart', 'confirm', array('shoppingCart' => $shoppingCart->id));
	}

}
