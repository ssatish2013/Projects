<?php

class giftModel extends giftDefinition {

	protected $paidAmount;
	protected $activationAmount;
	protected $unverifiedAmount;
	protected $unverifiedContributorCount;
	protected $messages;
	protected $recordings;
	protected $design;
	protected $inventory;
	protected $reservation;
	protected $approved;
	protected $rejected;
	protected $screenedBy;
	protected $screenedNotes;

	private $product;
	private $carts = null;
	private $_facebookName = null;

	const DELIVERY_EMAIL = 'email';
	const DELIVERY_SOCIAL = 'social';
	const DELIVERY_PHYSICAL = 'physical';
	const DELIVERY_TWITTER = 'twitter';
	const DELIVERY_MOBILE = 'mobile';

	const MODE_GROUP = 1;
	const MODE_SINGLE = 2;
	const MODE_SELF = 3;
	const MODE_VOUCHER = 4;

	public function __construct($id = null){
		if($id===null){
			$this->envName = Env::getEnvName();
		}
		$this->recordings = NULL;
		call_user_func_array("parent::__construct", func_get_args() );
	}

	public function getShoppingCarts($getAll = false, $ignoreIncomplete = false) {

		if(is_null($this->carts)) {
			$messages = array();
			if($getAll) {
				$messages = $this->getAllMessages();
			}
			else {
				$messages = $this->getMessages();
			}

			$carts = array();
			foreach($messages as $message) {
				$cart = $message->getShoppingCart();
				//check if the cart exists, messages without a shopping cart id were found in production. this is for cautious
				if (!$cart->id) continue;
				//if the flag to ignore incomplete shopping cart is set, and this is the contributor message and didn't finish checkout
				//don't include it in the list.
				if ($ignoreIncomplete && $message->isContribution && !$cart->approved) continue;
				$carts[] = $cart;
			}
			$this->carts = $carts;
		}

		return $this->carts;
	}

	public function isInCart() {
		// Determines whether the gift is already added in the cart
		$messageGuid = request::url("messageGuid");
		$shoppingCart = new shoppingCartModel();
		foreach($shoppingCart->getAllMessages() as $msg) {
			if (strcmp($msg->guid, $messageGuid) == 0) {
				return true;
			}
		}
		return false;
	}

	public function _getApproved() {
		//to check if this gift is approved, we need to exclude those incompleted contributor message,
		//otherwise the whole gift can't be claimed because one of the contribute message(cart) is not approved
		$carts = $this->getShoppingCarts(true,true);
		foreach($carts as $cart) {
			//if a cart has not been approved yet or a cart has been rejected
			if(is_null($cart->approved) || !is_null($cart->rejected)) {
				return false;
			}
		}
		return true;
	}

	public function _getRejected() {
		$carts = $this->getShoppingCarts(true);
		foreach($carts as $cart) {
			if(!is_null($cart->rejected)) {
				return true;
			}
		}
		return false;
	}

	public function _getScreenedBy() {
		$carts = $this->getShoppingCarts(true);
		$screenedBy = array();
		foreach($carts as $cart) {
			if(!is_null($cart->screenedBy)) {
				$screenedBy[] = $cart->screenedBy;
			}
		}
		return implode(',',$screenedBy);
	}

	public function _getScreenedNotes() {
		$carts = $this->getShoppingCarts(true);
		$screenedNotes = array();
		foreach($carts as $cart) {
			if(!is_null($cart->screenedNotes)) {
				$screenedNotes[] = $cart->screenedNotes;
			}
		}
		return implode(',',$screenedNotes);
	}

	//actually paid amount
	public function _getPaidAmount() {
		$this->messages = null; // We need to make sure the amount is correct for these so reload them
		$this->getMessages();

		$total = 0;
		foreach ($this->messages as $message) {
			/* @var $message messageModel */
			if($message->isPaid()){
				$total += $message->getDiscountedPrice();
			}
		}
		return $total;
	}
	//actually activated amount
	public function _getActivationAmount() {
		$this->messages = null; // We need to make sure the amount is correct for these so reload them
		$this->getMessages();

		$total = 0;
		foreach ($this->messages as $message) {
			/* @var $message messageModel */
			if($message->isPaid()){
				$total += ($message->amount + $message->bonusAmount);
			}
		}
		return $total;
	}
	//amount before activation
	public function _getUnverifiedAmount() {
		$this->messages = null; // We need to make sure the amount is correct for these so reload them
		$this->getMessages();

		$total = 0;
		foreach ($this->messages as $message) {
			/* @var $message messageModel */
			if($message->shouldDisplay()){
				$total += ($message->amount + $message->bonusAmount);
			}
		}
		return $total;
	}
	//number of contributor before activation
	public function _getUnverifiedContributorCount(){
		$this->messages = null; // We need to make sure the amount is correct for these so reload them
		$this->getMessages();

		$total = 0;
		foreach ($this->messages as $message) {
			/* @var $message messageModel */
			if($message->shouldDisplay()){
				$total ++;
			}
		}
		return $total;
	}

	public function getCurrentRecipientGuid(){
		$safeId = mysql_real_escape_string($this->id);
		$query = "select * from recipientGuids where giftId='$safeId' order by expires desc limit 1";
		$result = db::query($query);
		$recipientGuid = new recipientGuidModel();
		if($row = mysql_fetch_assoc($result)){
			$recipientGuid->assignValues($row);
			if($recipientGuid->isExpired()){
				$recipientGuid = new recipientGuidModel();
				$recipientGuid->giftId = $this->id;
				$recipientGuid->expires = date("Y-m-d H:i:s", strtotime("+1 day"));
				$recipientGuid->guid = randomHelper::guid(16);
				$recipientGuid->save();
			}
		} else {
			$recipientGuid = new recipientGuidModel();
			$recipientGuid->giftId = $this->id;
			$recipientGuid->expires = date("Y-m-d H:i:s", strtotime("+1 day"));
			$recipientGuid->guid = randomHelper::guid(16);
			$recipientGuid->save();
		}

		return $recipientGuid->guid;
	}

	public function getAndActivateInventory(){
		$this->getMessages();
		/*
		 * Capture each payment for the gift.
		 */
		foreach ($this->messages as $message) {
			/* @var $message messageModel */
			if(!$message->isPaid() && $message->amount > 0){
				$transaction = new transactionModel();
				$transaction->shoppingCartId = $message->getShoppingCart()->id;
				$transaction->load();
				//skip fake authrization or refunded transaction
				if (substr($transaction->authorizationId, 0, 4) == "FAKE" || $transaction->refunded) {
					continue;
				}
				$payment = new payment();
				$payment->loadPlugin($transaction->paymentMethodId);
				//in case of capture failure, mark the transaction and shoppingcart as void
				try{
					$payment->plugin->capture($message, $transaction);
				}
				catch(Exception $e){
					log::error("Captured payment failed for gift#$this->id, message#$message->id, excluding this message from activation.");
					paymentHelper::updateRefundStatus($message->getShoppingCart(), $transaction);
					continue;
				}

				$transaction->transactionComplete($payment->plugin->response);
				$message->getShoppingCart()->transactionComplete($transaction);
				$transaction->save();
				$message->getShoppingCart()->save();
				log::info("Captured payment for gift#$this->id, message#$message->id.");
			} else {
				log::info("Already captured gift#$this->id, message#$message->id.");
			}
		}
		if ($this->_getActivationAmount() <= 0){
			log::error("Zero activation amount for gift#$this->id, activation abort.");
			throw new paymentException('Payment Failure');
		}

		$inventoryPlugin = $this->getProduct()->getInventoryPlugin($this);
		log::info("Activating inventory for gift $this->id, inventory plugin = " . get_class($inventoryPlugin));
		return $inventoryPlugin->activate();
	}

	public function getInactiveInventory(){
		$inventoryPlugin = $this->getProduct()->getInventoryPlugin($this);
		return $inventoryPlugin->getInactive();
	}

	public function getAndDeactivateInventory(){
		return $this->getProduct()->getInventoryPlugin($this)->deactivate();
	}

	public function getProduct(){
		return $this->product ?: ( $this->product = new productModel( $this->productId ) );
	}
	
	public function getContributorCount($paidOnly=true) {
		return count($this->getMessages(!$paidOnly));
	}

	public function getMessages($showAll=false) {
		$messages = $this->messages ?: ( $this->messages = messageModel::loadAll( array('giftId' => $this->id) ) );
		$displayMessages = array();
		if(!$showAll) {
		  foreach($messages as $message){
		    if($message->shouldDisplay()){
		      $displayMessages[]=$message;
		    }
		  }
		  return $displayMessages;
		}
		else return $messages;
	}

	public function getAllMessages() {
		$messages = messageModel::loadAll( array('giftId' => $this->id) );
		return $messages;
	}

	public function getTransactions($allMessages=false) {
		$transactions = array();
		foreach($this->getMessages($allMessages) as $message)
			$transactions[] = $message->getShoppingCart()->getTransaction();
		return $transactions;
	}

	public function getAllTransactions() {
		return $this->getTransactions(true);
	}

	public function getContributorEmails($allMessages=false) {
		$emails = array();
		foreach($this->getTransactions($allMessages) as $transaction)
			$emails[] = $transaction->fromEmail;
		return $emails;
	}

	public function getAllContributorEmails() {
		return $this->getContributorEmails(true);
	}

	public function getDesign() {
		$design = $this->design ?: ($this->design = new designModel($this->designId));
		$ageOfImage = (int) round((time() - strtotime($design->created)) / 86400);
		if ($design->isCustom == 1 && $ageOfImage >= 90) {   // 90 Days
			$endpointAndPrefix = 'https://gc-fgs.s3.amazonaws.com/cards/';
			$design->largeSrc = "{$endpointAndPrefix}card-expired-large.png";
			$design->mediumSrc = "{$endpointAndPrefix}card-expired-large.png";
			$design->smallSrc = "{$endpointAndPrefix}card-expired-small.png";
		}

		return $design;
	}

	public function getReservation() {
		return $this->reservation ?: ( $this->reservation = new reservationModel( $this) );
	}

	public function getInventory() {
		$reservation = $this->getReservation();
		return $this->inventory ?: ( $this->inventory = new inventoryModel( $reservation->inventoryId) );
	}

	public function validateGiftCreate($formType=null) {
		if(parent::validate($formType)){
			$this->partner = globals::partner();
			if(globals::redirectLoader()){
				$this->redirectLoader = globals::redirectLoader();
			}
			$this->guid = randomHelper::guid(16);
			return true;
		} else {
			return false;
		}
	}

	public function assignDeliveryMethod() {
		// Define a map between giftDeliveryMethod request value and delivery method
		// columns (or properties) in this class (giftModel)
		$maps = array(
			'email' => 'emailDelivery',
			'social' => 'facebookDelivery',
			'physical' => 'physicalDelivery',
			'twitter' => 'twitterDelivery'
		);
		// Assign value of post request parameter giftDeliveryMethod to a variable
		$request = request::unsignedPost('giftDeliveryMethod');
		// Assign default delivery method values (which are false)
		foreach ($maps as $prop) {
			$this->$prop = false;
		}
		// If value of giftDeliveryMethod is a string (element is a radio button)
		if (is_string($request) && isset($maps[$request])) {
			$this->$maps[$request] = true;
		}
		// Elements are checkboxes (giftDeliveryMethod[]), then iterate through them
		elseif (is_array($request)) {
			foreach ($request as $req) {
				if (!isset($maps[$req])) {
					continue;
				}
				$this->$maps[$req] = true;
			}
		}
	}

	public function _setDefaultTimeZone() {
		// Set the default time zone when the delivery date has been disabled
		$timezonedefault = request::unsignedPost('currentTimeZone');
		$this->defaultTimeZoneKey = $timezonedefault;
	}

	public function _setDeliveryDate($value) {
		$date_mm   = (intval(request::unsignedPost ('date_mm')) > 9) ? request::unsignedPost ('date_mm') : "0" . intval(request::unsignedPost ('date_mm'));
		$date_dd   = (intval(request::unsignedPost ('date_dd')) > 9) ? request::unsignedPost ('date_dd') : "0" . intval(request::unsignedPost ('date_dd'));

		$date      = request::unsignedPost('date_yyyy') . "-" . $date_mm . "-" . $date_dd;
		$timeShift = request::unsignedPost('timeShift');
		$timezonedefault = request::unsignedPost('currentTimeZone');

		date_default_timezone_set($timezonedefault);

		if(request::unsignedPost('timeHour') != "") {
			$time	      = (intval(request::unsignedPost('timeHour')) > 9)
						? request::unsignedPost('timeHour') . ":" . request::unsignedPost('timeMin') . ":" . "00"
						: "0" . intval(request::unsignedPost('timeHour')) . ":" . request::unsignedPost('timeMin') . ":" . "00";
		} else {
			$timestamp    = strtotime("now");

			if(date("Y-m-d") == $date) {
				$time = (intval(date("H", $timestamp)) > 9)
						? intval(date("H", $timestamp)) . ":" . intval(date("i", $timestamp)) . ":" . "00"
						: "0" . intval(date("H", $timestamp)) . ":" . intval(date("i", $timestamp)) . ":" . "00";
			} else {
				if( intval($this->giftingMode) < 3 ) {
					$time = "07:00:00";
				} else {
					$date = Date('Y-m-d', strtotime("-1 day"));
					$time = (intval(date("H", $timestamp)) > 9)
						? intval(date("H", $timestamp)) . ":" . intval(date("i", $timestamp)) . ":" . "00"
						: "0" . intval(date("H", $timestamp)) . ":" . intval(date("i", $timestamp)) . ":" . "00";
				}
			}
		}

		if(request::unsignedPost('timeHour') != "") {
			$timeZone = (request::unsignedPost('timeZone')) ? request::unsignedPost('timeZone') : $timezonedefault;
		} else {
			(date("Y-m-d") == $date) ? $timeZone = null : $timeZone = $timezonedefault;
		}

		$datetime = $date . " " . $time;
		$timestamp = strtotime($datetime);

		date_default_timezone_set("UTC");
		$this->deliveryDate = date("Y-m-d H:i:s", $timestamp);
		$this->timeZoneKey  = $timeZone;
		$this->defaultTimeZoneKey = $timezonedefault;
	}

	/**
	 * return the delivery date for this gift
	 * @param bool $returnLocalTime 			do you want the date in the user's local time?
	 * @return string
	 */
	public function getDeliveryDate($returnLocalTime=false) {
		$tz = ($this->timeZoneKey) ? $this->timeZoneKey : $this->defaultTimeZoneKey;

		$timezoneUtc = new DateTimeZone("UTC");
		$timezoneLocal = new DateTimeZone($tz);
		$dateUtc = new DateTime(date('Y-m-d H:i:s', strtotime($this->deliveryDate)), $timezoneUtc);

		if($returnLocalTime){
			$dateLocal = $dateUtc->setTimezone($timezoneLocal)->format('Y-m-d H:i:s');
			return date('Y-m-d H:i:s', strtotime($dateLocal));
		} else {
			return date('Y-m-d H:i:s', strtotime($dateUtc));
		}
	}

	/**
	 * return the time remaining until delivery
	 * @param string $timeFormat 	format mask used to return how you want the time remaining returned to you. If no format is passed in then return an array data
	 * 							 	[d] [h] [m] [s] - time remaining until the delivery date (in days, hours, minutes, seconds)
	 * 								[D] - total days remaining until delivery
	 * 								[H] - total hours remaining until delivery
	 * 								[M] - total minutes remaining until delivery
	 * 								[S] - total seconds remaining until delivery
	 * 								"estimate" - will return a general estimate of how much time is left until delivery
	 * @return string
	 */
	public function getTimeUntilDelivery($timeFormat=null) {
		$TO_MINUTES = 60;
		$TO_HOURS = 3600;
		$TO_DAYS = 86400;

		if(null === $this->deliveryDate) {
			return ''; // no delivery date for this gift
		}

		$secondsRemainingUntilDelivery = intval(strtotime($this->deliveryDate) - strtotime(date('Y-m-d H:i:s')));
		$minutesRemainingUntilDelivery = intval(floor($secondsRemainingUntilDelivery / $TO_MINUTES));
		$hoursRemainingUntilDelivery = intval(floor($secondsRemainingUntilDelivery / $TO_HOURS));
		$daysRemainingUntilDelivery = intval(floor($secondsRemainingUntilDelivery / $TO_DAYS));

		$daysUntilDelivery = $daysRemainingUntilDelivery;
		$hoursUntilDelivery = intval(floor(($secondsRemainingUntilDelivery % ($daysUntilDelivery * $TO_DAYS)) / $TO_HOURS));
		$minutesUntilDelivery = intval(floor(($secondsRemainingUntilDelivery % ($hoursUntilDelivery * $TO_HOURS)) / $TO_MINUTES));
		$secondsUntilDelivery = intval(floor(($secondsRemainingUntilDelivery % 60)));

		if(null === $timeFormat) {

			return array(
				'd' => $daysUntilDelivery,				// remaining time (days)
				'h' => $hoursUntilDelivery,				// remaining time (hours)
				'm' => $minutesUntilDelivery,			// remaining time (minutes)
				's' => $secondsUntilDelivery,			// remaining time (seconds)
				'D' => $daysRemainingUntilDelivery,		// remaining days
				'M' => $minutesRemainingUntilDelivery,	// remaining minutes
				'H' => $hoursRemainingUntilDelivery,	// remaining hours
				'S' => $secondsRemainingUntilDelivery	// remaining seconds
			);

		} else if('estimate' === $timeFormat) {

			if($daysRemainingUntilDelivery > 0) {
				$unit = (1 === $daysRemainingUntilDelivery) ? 'Day' : 'Days';
				return $daysRemainingUntilDelivery . ' ' . $unit;
			} else if($hoursRemainingUntilDelivery > 0) {
				$unit = (1 === $hoursRemainingUntilDelivery) ? 'Hour' : 'Hours';
				return $hoursRemainingUntilDelivery . ' ' . $unit;
			} else if($minutesRemainingUntilDelivery > 0) {
				$unit = (1 === $minutesRemainingUntilDelivery) ? 'Minute' : 'Minutes';
				return $minutesRemainingUntilDelivery . ' ' . $unit;
			} else {
				$unit = (1 === $secondsRemainingUntilDelivery) ? 'Second' : 'Seconds';
				return $secondsRemainingUntilDelivery . ' ' . $unit;
			}

		} else {

			$timeFormat = str_replace('[d]', $daysUntilDelivery, $timeFormat);
			$timeFormat = str_replace('[h]', $hoursUntilDelivery, $timeFormat);
			$timeFormat = str_replace('[m]', $minutesUntilDelivery, $timeFormat);
			$timeFormat = str_replace('[s]', $secondsUntilDelivery, $timeFormat);
			$timeFormat = str_replace('[D]', $daysRemainingUntilDelivery, $timeFormat);
			$timeFormat = str_replace('[M]', $minutesRemainingUntilDelivery, $timeFormat);
			$timeFormat = str_replace('[H]', $hoursRemainingUntilDelivery, $timeFormat);
			$timeFormat = str_replace('[S]', $secondsRemainingUntilDelivery, $timeFormat);

			$timeFormat = str_replace('[days]', (1 === $daysUntilDelivery) ? 'Day' : 'Days', $timeFormat);
			$timeFormat = str_replace('[hours]', (1 === $hoursUntilDelivery) ? 'Hour' : 'Hours', $timeFormat);
			$timeFormat = str_replace('[minutes]', (1 === $minutesUntilDelivery) ? 'Minute' : 'Minutes', $timeFormat);
			$timeFormat = str_replace('[seconds]', (1 === $secondsUntilDelivery) ? 'Second' : 'Seconds', $timeFormat);
			$timeFormat = str_replace('[DAYS]', (1 === $daysRemainingUntilDelivery) ? 'Day' : 'Days', $timeFormat);
			$timeFormat = str_replace('[HOURS]', (1 === $hoursRemainingUntilDelivery) ? 'Hour' : 'Hours', $timeFormat);
			$timeFormat = str_replace('[MINUTES]', (1 === $minutesRemainingUntilDelivery) ? 'Minute' : 'Minutes', $timeFormat);
			$timeFormat = str_replace('[SECONDS]', (1 === $secondsRemainingUntilDelivery) ? 'Second' : 'Seconds', $timeFormat);

			return $timeFormat;
		}
	}


	public function getTimeZone() {
		$tz = ($this->timeZoneKey) ? $this->timeZoneKey : $this->defaultTimeZoneKey;
		$tzName = "";

		switch($tz) {
			case "America/Los_Angeles" : $tzName = languageModel::getString('tzPacific');
						     break;
			case "America/New_York"	   : $tzName = languageModel::getString('tzEastern');
						     break;
			case "America/Denver"	   : $tzName = languageModel::getString('tzMountain');
						     break;
			case "America/Chicago"	   : $tzName = languageModel::getString('tzCentral');
						     break;
			case "America/Halifax"	   : $tzName = languageModel::getString('tzAtlantic');
						     break;
			case "America/Adak"	   : $tzName = languageModel::getString('tzHawaiian');
						     break;
			default			   : $tzName = str_replace("_", " ", $tz);
		}

		return $tzName;
	}

	public function isFutureDelivery() {
		return time() - strtotime($this->deliveryDate) < 0;
	}

	public function isClaimed () {
		return empty ($this->claimed) ? false : true;
	}

	public function isThanked () {
		return empty ($this->thanked) ? false : true;
	}

	public function sendSMS (messageModel $message = null) {
		if (!empty ($this->recipientPhoneNumber)) {
			Env::includeLibrary ('Twilio');
			$client = new Services_Twilio ('AC34d18ef689ad490e9c500ae82dd33553', '08422a07b38036e508047239731e9045');
			globals::partner ($this->partner);
			if (!is_null($message)) {
				$text = languageModel::getString('voucherSmsDeliveryText') . ' '
					. view::GetDirectFullUrl('voucher', 'print', array('mguid' => $message->guid));
			} else {
				$recipientGuid = new recipientGuidModel ();
				$recipientGuid->giftId = $this->id;
				$recipientGuid->guid = randomHelper::guid(16);
				$recipientGuid->expires = date ("Y-m-d H:i:s", strtotime("+1 year"));
				$recipientGuid->save ();
				$text = languageModel::getString('smsDeliveryText') . ' '
					. view::GetDirectFullUrl('claim', 'mobile', array('guid' => $recipientGuid->guid));
			}
			$message = $client->account->sms_messages->create (
				'6509249706', // From a valid Twilio number
				$this->recipientPhoneNumber, // Text this number
				$text
			);
		}
	}

	public function sendThankYou($thankYouText) {

		//thank you already sent, no need to spam!
		if($this->thanked !== null) { return; }

		//get emails associated with the gift
		foreach( messageModel::loadAll( $this ) as $message) {
			$user = new userModel($message->userId);

			$worker = new thankYouEmailWorker();
			$worker->send(json_encode(array(
				'recipientName' => $this->recipientName,
				'purchaserName' => $message->fromName,
				'purchaserEmail' => $user->email,
				'message' => $thankYouText,
				'messageId' => $message->id
			)));

			if(request::unsignedPost('facebook')){
				$worker = new facebookThankYouWorker();
				$worker->send(json_encode(array(
					'accessToken' => request::unsignedPost('fbAccessToken'),
					'partner' => globals::partner(),
					'wallId' => $message->facebookUserId,
					'message' => $thankYouText,
					'messageId' => $message->id
				)));
			}
		}

		$this->thanked = date('Y-m-d H:i:s', strtotime("now"));
		$this->save();
	}

	public function getCreatorMessage() {
		$messages = messageModel::loadAll( $this );
		foreach($messages as $message) {
			if($message->isContribution == 0) {
				/* @var $message messageModel */
				return $message;
			}
		}
		return false;
	}

	public function getRecipientFacebookName() {
		if (!isset($this->_facebookName) && (int)$this->recipientFacebookId != 0) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/' . $this->recipientFacebookId);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$json = json_decode(curl_exec($ch));
			curl_close($ch);
			$this->_facebookName = $json->name;
		}

		return $this->_facebookName;
	}

	public function getEmailDomain () {
		// returns just the domain portion of the e-mail address associated with the gift
		return substr ($this->recipientEmail, strpos ($this->recipientEmail, '@') + 1);
	}

	public function getRelatedCstLogs() {
		return cstLogModel::getRelated($this->id);
	}

	public static function getUserGifts($userId, $partner = null, $limit = null) {
		if($partner === null) {
			$partner = globals::partner();
		}

		$sql = "
			SELECT `g`.*
			FROM `messages` `m`
			LEFT JOIN `gifts` `g`
			ON `m`.`giftId` = `g`.`id`
			WHERE
			`m`.`userId` = ".db::escape($userId)." AND
			`g`.`partner` = '".db::escape($partner)."'
			ORDER BY `g`.`id` DESC
		";
		if($limit !== null && $limit != "") {
			$sql .= " LIMIT " . db::escape($limit);
		}

		$result = db::query($sql);
		$gifts = array();
		while($row = mysql_fetch_assoc($result)) {
			$gift = new giftModel();
			$gift->assignValues($row);
			$gifts[] = $gift;
		}

		return $gifts;
	}



  public static function getLastFourGifts($ccLastFour, $partner = null, $limit = null) {
    if($partner === null) {
      $partner = globals::partner();
    }

    $sql = "
			SELECT `g`.*
			FROM `transactions` `t`
			LEFT JOIN `messages` `m`
			ON `t`.`id`=`m`.`transactionId`
			LEFT JOIN `gifts` `g`
			ON `m`.`giftId` = `g`.`id`
			WHERE
			`t`.`ccLastFour` = '".db::escape($ccLastFour)."' AND
			`g`.`partner` = '".db::escape($partner)."'
    ";
    if($limit !== null && $limit != "") {
      $sql .= " LIMIT " . db::escape($limit);
    }

    $result = db::query($sql);
    $gifts = array();
    while($row = mysql_fetch_assoc($result)) {
      $gift = new giftModel();
      $gift->assignValues($row);
      $gifts[] = $gift;
    }

    return $gifts;
  }

  // count how many invite emails sent out so far
  public function getInviteSent(){
  	$emails = new emailModel();
  	return count($emails->loadAll( array('giftId' => $this->id,'template'=>'invite')));
  }

}
