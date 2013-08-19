<?php 
require_once(Env::main()->includePath() . '/libraries/kount/src/autoload.php');

class Kount {

	public $fraudObj;
	private $kount;

	const AUTO_APPROVED = 'A';
	const AUTO_REVIEWED = 'R';
	const AUTO_DECLINED = 'D';

	public function __construct(fraud $fraudObj) {

		//grab the fraud object we were passed
		$this->fraudObj = $fraudObj;

		//set the kount object
		$this->kount = self::newInquiryRequest();
	}

	/*
	 * Create a new configured kount update request.
	 */
	private static function newUpdateRequest() {
		$kount = new Kount_Ris_Request_Update();
		self::configureRequest($kount);
		return $kount;
	}

	private static function newInquiryRequest() {
		$kount = new Kount_Ris_Request_Inquiry();
		self::configureRequest($kount);
        return $kount;
	}

	private static function configureRequest(Kount_Ris_Request $kount) {
		$certPrefix = settingModel::getSettingRequired('kountConfig', 'certificateName');
		$kount->setUrl(settingModel::getSettingRequired('kountConfig', 'Url'));
        $kount->setCertificate(
                '/media/ram/'.$certPrefix.'_cert.pem',
                '/media/ram/'.$certPrefix.'_key.pem',
                settingModel::getSetting('kountConfig', 'certificatePassword')
        );
	}

	//this plugin is unique that only under certain circumstances do we want
	//to actually reload the data
	public function doReset() {


		//grab the original fraud log and max attempts settings
		$fraudLog = dbMongo::findOne('fraudLogs', array('shoppingCartId' => $this->fraudObj->shoppingCart->id));
		$maxAttempts = settingModel::getSetting('kountConfig', 'maxAttempts');
		$attempts = 0;

		//if we have previous kount data
		@$kountData = $fraudLog['data']['Kount'];
		if(isset($kountData)) {
			if(isset($kountData['ATTEMPTS'])) {
				$attempts = $fraudLog['data']['Kount']['ATTEMPTS'];
			}

			//if the messages have changed, make a new call
			$prevMessages = explode(',', $kountData['MESSAGES']);
			foreach($this->fraudObj->messages as $message) {
				if(!in_array($message->id, $prevMessages)) {
					$attempts = 0;
				}
			}

			//if the payment has changed, make a new call
			if($kountData['PAYMENTID'] != $fraudLog['paymentId']) {
				$attempts = 0;
			}

		}

		//if attempts say we should reload, or there's no previous transaction
		//data, lets say we shoudl reset it
		if($maxAttempts > $attempts || !isset($kountData['TRAN'])) {
			return true;
		}
		return false;
	}

	public function getData() { 
		//attempt to load up the old fraud log
		$fraudLog = dbMongo::findOne('fraudLogs', array('shoppingCartId' => $this->fraudObj->shoppingCart->id));
		
		if($this->doReset()) {
			//might need to do an update call with an X call here instead of doing a
			//completely new query
			list($data, $response) = $this->doQuery();
		} else {
			$this->doUpdate();
			$data = $fraudLog['data']['Kount'];
		}
		
		//set static variables on the log for reference
		@$kountData = $fraudLog['data']['Kount'];
		
		//store data on the actual plugin for future reference
		$data['MESSAGES'] = implode(',', array_map(function($message) {
			return $message->id;
		}, $this->fraudObj->messages));
		$data['PAYMENTID'] = $fraudLog['paymentId'];
		if(isset($kountData['ATTEMPTS'])) {
			$data['ATTEMPTS'] = $kountData['ATTEMPTS'] + 1;
		} else {
			$data['ATTEMPTS'] = 1;
		}
		
		return $data;
	}

	//only called when we're updating or adding data
	public function doUpdate($kountTransactionId) {
		$this->kount = self::newUpdateRequest();
		$this->kount->setTransactionId($kountTransactionId);
		$this->addHeaderParams();
		$this->addUpdateParams($this->kount);
		
		$request  = $this->kount->getRequest();
		// Add Kount Update API Request Log
		$kountLog = kountHelper::saveApiLog($request, 'Update');
		
		$response = $this->kount->getResponse();
		log::debug("Kount update request response: " . $response);
		
		$formattedResponse = $this->_formatRawKountResponse($response);
		log::debug('Kount raw update response formatted: ' . print_r($formattedResponse, true));
		
		// Add Kount Update API Response Log
		kountHelper::updateApiLog($kountLog, $formattedResponse);
	}

	public function doQuery() {
		$inquiry = $this->kount;
		$this->addHeaderParams();
		$this->addUpdateParams($this->kount);
		$this->addPaymentParams($this->kount, $this->fraudObj);
		$this->addProductParams();
		$this->addUserDefinedParams();
		
		$request = $this->kount->getRequest();
		// Add Kount Inquiry API Request Log
		$kountLog = kountHelper::saveApiLog($request, 'Inquiry');
		
		$response = $this->kount->getResponse();
		log::debug('Kount request response: ' . $response);
		
		// optional getter
		$warnings = $response->getWarnings();
		$score = $response->getScore();

		$formattedResponse = $this->_formatRawKountResponse($response);
		log::debug('Kount raw response formatted: ' . print_r($formattedResponse, true));

		$auto = $formattedResponse['AUTO'];
		new eventLogModel('kount', 'kountResponse', $auto);
		
		// Add Kount Inquiry API Response Log
		kountHelper::updateApiLog($kountLog, $formattedResponse);
		
		return array($formattedResponse, $response);
	}

	private function _formatRawKountResponse($raw) {
		$response = array();
		$lines = preg_split('/[\r\n]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($lines as $line) {
			list($key, $value) = explode('=', $line, 2);
			$response[$key] = $value;
		}
		return $response;
	}

	//add's update params
	public function addUpdateParams($kount) {
		//update specific fields
		$payment = $this->fraudObj->payment;
		$transaction = $this->fraudObj->transaction;
		$cart = $this->fraudObj->shoppingCart;

		if(($transaction->isAuthorized() || $transaction->isPaid()) || $transaction->isPayPalExpress()) {
			$kount->setOrderNumber($cart->id);
			$kount->setMack('Y');
			$kount->setAuth('A');
		} else {
			$kount->setOrderNumber($cart->id . 'D');
			$kount->setMack('N');
			$kount->setAuth('D');
		}

		if($transaction->isPayPalExpress()) {
			$kount->setAvsz(paymentHelper::AVS_ZIP_UNAVAILABLE);
			$kount->setAvst(paymentHelper::AVS_STREET_UNAVAILABLE);
		} else {
			$kount->setAvsz(paypalPayment::getAvsZip($transaction->avsCode));
			$kount->setAvst(paypalPayment::getAvsStreet($transaction->avsCode));
		}

		if(isset($transaction)) {
			$cvv = $transaction->cvv2Match ? paymentHelper::CVV_MATCH : paymentHelper::CVV_NO_MATCH;
			$kount->setCvvr($cvv);


			//check card or paypal
			//PENC and DRIV not used
			//apparently we can only update if it's a paypal payment
			if($transaction->isPayPalExpress()) {
				$kount->setPaypalPayment($transaction->paypalPayerId);

				$status = strtoupper($transaction->paypalDetails['PAYERSTATUS']);
	
				//using the cvv as an indicator of status
				if($status == 'VERIFIED') {
					$kount->setCvvr(paymentHelper::CVV_MATCH);
				}
				else {
					$kount->setCvvr(paymentHelper::CVV_NO_MATCH);
				}
			}
		}
	}
	
	public function addHeaderParams() {
		$kount = $this->kount;
		$cart = $this->fraudObj->shoppingCart;
		$kount->setOrderNumber($cart->id);
		$kount->setSessionId($cart->id);

		$fraudLog = dbMongo::findOne('fraudLogs', array('shoppingCartId' => $this->fraudObj->shoppingCart->id));
		if(isset($fraudLog['data']['Kount']['TRAN']) &&  get_class($this->kount) == 'Kount_Ris_Request_Update') {
			$kount->setTransactionId($fraudLog['data']['Kount']['TRAN']);
		} else if(get_class($this->kount) != 'Kount_Ris_Request_Update') {
			$kount->setWebsite(settingModel::getSettingRequired('kountConfig', 'Website'));
		}
	}

	public function addUserDefinedParams() {
		$cart = $this->fraudObj->shoppingCart;
		$transaction = $this->fraudObj->transaction;
		$messages = $this->fraudObj->messages;
		$gifts = $this->fraudObj->gifts;

		$kount = $this->kount;
		$minMessageLength = 1000;
		$maxMessageLength = 0;
		$promos = array();
		foreach($messages as $message) {
			if(strlen($message->message) < $minMessageLength) {
				$minMessageLength = strlen($message->message);
			}

			if(strlen($message->message) > $maxMessageLength) {
				$maxMessageLength = strlen($message->message);
			}

			if($message->getAmountOfDiscount() > 0) {
				$promos[] = $message->getActivePromoTrigger()->getTitle();
			}
		};
		$hasFacebookId = 0;
		if($message->facebookUserId != '' && $message->facebookUserId !== null) {
			$hasFacebookId = 1;
		}

		$kount->setUserDefinedField('maxMessageLength', $maxMessageLength);
		$kount->setUserDefinedField('minMessageLength', $minMessageLength);
		$kount->setUserDefinedField('hasFacebookId', $hasFacebookId);
		$kount->setUserDefinedField('facebookId', $message->facebookUserId);
		$kount->setUserDefinedField('shoppingCartId', $cart->id);
		$kount->setUserDefinedField('numMessages', count($messages));
		$kount->setUserDefinedField('env', Env::getEnvName());
		if (!empty ($cart->referer)) {
			$kount->setUserDefinedField ('httpReferer', $cart->referer);
			$kount->setUserDefinedField ('httpRefererHost', parse_url ($cart->referer, PHP_URL_HOST));
		}
		$kount->setUserDefinedField('ipAddress', $transaction->ipAddress);
		$kount->setUserDefinedField('ipAddressOrgName', $transaction->getIPOrgName ());
		$kount->setUserDefinedField('ipAddressOrgHandle', $transaction->getIPOrgHandle ());
		$kount->setUserDefinedField('creatorEmailDomain', $transaction->getEmailDomain ());
		$kount->setUserDefinedField('creatorEmailRegistrant', $transaction->getEmailDomainRegistrant ());
		$kount->setUserDefinedField('creatorEmailDomainCount', $transaction->getEmailDomainRegistrantCount ());
		$kount->setUserDefinedField('creatorEmailDomainAge', $transaction->getEmailDomainAge ());
		$kount->setUserDefinedField('creatorEmailDomainExpires', $transaction->getEmailDomainExpires ());
		if (count ($gifts)) {
			$gift = array_shift ($gifts);
			$kount->setUserDefinedField('recipientEmailDomain', $gift->getEmailDomain ());
			$kount->setUserDefinedField('recipientEmailRegistrant', $gift->getEmailDomainRegistrant ());
			$kount->setUserDefinedField('recipientEmailDomainCount', $gift->getEmailDomainRegistrantCount ());
			$kount->setUserDefinedField('recipientEmailDomainAge', $gift->getEmailDomainAge ());
			$kount->setUserDefinedField('recipientEmailDomainExpires', $gift->getEmailDomainExpires ());
			$gift->domaintoolsCacheId = $gift->getEmailDomainID ();
			$gift->save ();
		}
		foreach ($gifts as $gift) {
			$gift->domaintoolsCacheId = $gift->getEmailDomainID ();
			$gift->save ();		
		}
		$kount->setUserDefinedField('nslookup', gethostbyaddr($transaction->ipAddress));
		$kount->setUserDefinedField('promos', implode(',', array_unique($promos)));
		$kount->setUserDefinedField('contributorCount', max(array_map(
			function($gift) { return $gift->getContributorCount(); },
			$cart->getAllGifts()
		)));
		
		//gift params
		$kount->setUserDefinedField('giftIds', implode(',', array_map(function($gift) {
			return $gift->id;
		}, $gifts)));
		$kount->setUserDefinedField('recipientEmails', implode(',', array_map(function($gift) {
			return $gift->recipientEmail;
		}, $gifts)));
		$kount->setUserDefinedField('recipientFacebookIds', implode(',', array_map(function($gift) {
			return $gift->recipientFacebookId;
		}, $gifts)));
		$kount->setUserDefinedField('giftDesigns', implode(',', array_map(function($gift) {
			return $gift->getDesign()->alt;
		}, $gifts)));

		//message params
		$kount->setUserDefinedField('messageIds', implode(',', array_map(function($message) {
			return $message->id;
		}, $messages)));
		$kount->setUserDefinedField('message', implode(';', array_map(function($message) {
			return $message->message;
		}, $messages)));

	}

	private function addPaymentParams($riskRequest, fraud $fraud) {

		//some initial vars
		$shoppingCart = $fraud->shoppingCart;
		$user = $fraud->user;
		$transaction = $fraud->transaction;
			
		//calculate total in pennies
		$totl = $fraud->transaction->amount;
		if($fraud->shoppingCart->currency != 'JPY') { 
			$totl *= 100;
		} else { 
			$totl = intval($totl);
		}

		$riskRequest->setCurrency($shoppingCart->currency);
		$riskRequest->setTotal($totl);
		$riskRequest->setEmail($transaction->fromEmail);
		$riskRequest->setName($transaction->firstName . ' ' . $transaction->lastName);
		$riskRequest->setBillingAddress(
				$transaction->address,
				$transaction->address2,
				$transaction->city,
				$transaction->state,
				$transaction->zip,
				$transaction->country,
				'',''
		);
		$riskRequest->setBillingPhoneNumber($transaction->phoneNumber);
		$riskRequest->setShippingEmail($fraud->gifts[0]->recipientEmail);
		$riskRequest->setShippingName($fraud->gifts[0]->recipientName);

		//check card or paypal
		//PENC and DRIV not used
		if($transaction->isPayPalExpress()) {
			$riskRequest->setPaypalPayment($transaction->paypalPayerId);
		} else {
			$riskRequest->setKhashPaymentEncoding(true);
			$riskRequest->setCardPayment($transaction->creditCard);
			$riskRequest->setPaymentTokenLast4($transaction->ccLastFour);
		}
			
		$riskRequest->setUnique($user->id);
		$riskRequest->setEpoch(time($user->created));
		$riskRequest->setIpAddress($transaction->ipAddress);
		$riskRequest->setUserAgent($transaction->userAgent);

		$riskRequest->setMack('Y');
		$riskRequest->setAuth('A');
	}

	public function addProductParams() {
			
		$shoppingCart = $this->fraudObj->shoppingCart;
		$messages = $shoppingCart->getAllMessages();
		$kount = $this->kount;
		$cart = array();

		//TODO may need to handle contributions differently because
		//they actually change the "product" on the gift
		$i = 0;
		foreach($messages as $message) {
			$price = $message->getDiscountedPrice();
			if($message->currency != 'JPY') { 
				$price *= 100;
			} else { 
				$price = intval($price);
			}
			
			$gift = $message->getGift();
			$product = $gift->getProduct();
			$promoName = '';
			$promo = $message->getActivePromotion();
			if($promo !== null) {
				$promoName = ' (' . $promo->title . ')';
			}

			$cart[] = new Kount_Ris_Data_CartItem(
					'eCard',
					$product->upc,
					$product->description.$promoName,
					1,
					$price
			);
		}
		$kount->setCart($cart);
	}

	public function log($data, $status) {
		$fraud = $this->fraudObj;
		$log = new fraudLogModel();
		// Create new log entry or load the existing one
		if ($fraud->fraudLogId !== null) { 
			$log->id = $fraud->fraudLogId;
			$log->load();
		}
		// Log metadata
		$log->logType = fraud::PURCHASE_TRIGGER;
		$log->userId = $fraud->user->id;
		$log->currency = $fraud->currency;
		$log->ipAddress = $_SERVER['HTTP_X_REAL_IP'];
		$log->transactionId = $fraud->transaction->id;
		$log->shoppingCartId = $fraud->shoppingCart->id;
		$log->messages = array();
		$log->gifts = array();
		$log->paymentId = $fraud->paymentId;
		foreach ($fraud->messages as $message) { 
			$log->messages[] = $message->getPiiValues();
		}
		foreach ($fraud->gifts as $gift) { 
			$log->gifts[] = $gift->getPiiValues();
		}
		// Main log data
		// Mostly Kount responded data
		if (!isset($log->data['AttemptsFraud'])) { 
			$log->data['AttemptsFraud'] = array(
				'attempts' => 0,
				'rejectReasons' => array()
			);
		}
		$fraud->data['AttemptsFraud'] = $log->data['AttemptsFraud'];
		$fraud->data['flags'] = $fraud->flags;
		$fraud->data['AttemptsFraud']['attempts'] = $log->data['AttemptsFraud']['attempts'] + 1;
		$fraud->data['AttemptsFraud']['rejectReasons'] = $log->data['AttemptsFraud']['rejectReasons'];
		// Kount data
		// Store data on the actual plugin for future reference
		$data['MESSAGES'] = implode(',', array_map(function($message) {
			return $message->id;
		}, $fraud->messages));
		$data['PAYMENTID'] = $fraud->paymentId;
		$data['ATTEMPTS'] = $fraud->data['AttemptsFraud']['attempts'];
		$fraud->data['Kount'] = $data;
		// Go through the old log (if there is one) and append any data
		// we previously collected
		foreach ($log->data as $key => $value) {
			if (!isset($fraud->data[$key])) {
				$fraud->data[$key] = $value;
			}
		}
		$log->data = $fraud->data;
		// Set evaluation status
		switch ($status) {
			case self::AUTO_REVIEWED:
				//set screened
				$fraud->isScreened = true;
				$log->isScreened = true;
				//force is sent (in case it was previously set)
				$fraud->isSent = false;
				$log->isSent = false;
				break;
			case self::AUTO_DECLINED:
				//set rejected
				$fraud->isRejected = true;
				$log->isRejected = true;
				//force is sent (in case it was previously set)
				$fraud->isSent = false;
				$log->isSent = false;
				break;
			case self::AUTO_APPROVED:
			default:
				$fraud->isSent = true;
				$log->isSent = true;
				break;
		}
		// Save the current log entry
		$log->save();
		$fraud->fraudLogId = $log->id;
		// Record to application wide log (taillog)
		log::debug("Fraud logId#{$log->id} for shoppingCartId#{$log->shoppingCartId}");
	}

}
