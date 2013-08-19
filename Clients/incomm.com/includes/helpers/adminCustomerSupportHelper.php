<?php

Env::includeLibrary('kount/api/Rest');
Env::includeLibrary('kount/api/Rest/Email');

use KountApi\Rest\Email as KountApiEmail;

class adminCustomerSupportHelper {

	public function __construct() {
		set_time_limit(120);
	}

	static public function search() {
		$searchType = "search" . ucfirst( request::unsignedPost("searchType") );
		return self::$searchType();
	}


	static private function searchRecentTransactions() {
		$limit = request::unsignedPost("searchLimit");

		//we _never_ want to pull ALL gifts
		if($limit == "") { $limit = 50; }

		$gifts = giftModel::loadAll(array(
			"partner" => globals::partner(),
			"envName" => Env::getEnvName()
		), $limit, 'id DESC');
		$formattedGifts = array();
		foreach($gifts as $gift) {
			$formattedGifts[] = self::returnGiftArray($gift, true);
		}

		echo json_encode($formattedGifts);

	}

	static private function searchAuthorizationId() {
		// Load up transaction
		$transaction = new transactionModel(array(
				"authorizationId" => request::unsignedPost("searchTerm")
		));
		if($transaction->id === null) {
			return json_encode(array());
		}

		$messages = messageModel::loadAll( array(
			"shoppingCartId" => $transaction->shoppingCartId
		));

		$gifts = array();
		foreach($messages as $message){
			$gift = new giftModel($message->giftId);
			if($gift->partner == globals::partner()){
				$gifts[] = self::returnGiftArray($gift, true);
			}
		}

		// return this as an array
		echo json_encode( $gifts );
	}

	static private function searchGiftGuid() {
		// Load up Gift
		$gift = new giftModel(array(
				"guid" => request::unsignedPost("searchTerm")
		));

		if(!$gift->id){
			// looks like this is a recipient guid, lets load that up
			$recipientGuid = new recipientGuidModel(array(
					"guid" => request::unsignedPost("searchTerm")
			));

			//Now load up the gift
			$gift = new giftModel($recipientGuid->giftId);
		}


		if ($gift->partner != globals::partner() || $gift->id === null){
			echo json_encode(array());
			return;
		}

		echo json_encode(array(
			adminCustomerSupportHelper::returnGiftArray($gift, true)
		));

	}

	static private function searchShoppingCartId() {
		$searchTerm = request::unsignedPost("searchTerm");
		$gifts = array();

		$cart = new shoppingCartModel($searchTerm);
		$cartGifts = $cart->getAllGifts();
		foreach($cartGifts as $gift) {
			if($gift->partner == globals::partner()) {
				$gifts[] = self::returnGiftArray($gift, true);
			}
		}

		echo json_encode($gifts);
	}

	// @deprecated
	static private function searchLastFour() {
		$searchTerm = request::unsignedPost("searchTerm");
		$searchLimit = request::unsignedPost("searchLimit");
		$gifts = array();

		$lastFourGifts = giftModel::getLastFourGifts($searchTerm, globals::partner(), $searchLimit);
		foreach($lastFourGifts as $gift) {
			if($gift->partner == globals::partner()) {
				$gifts[] = self::returnGiftArray($gift, true);
			}
		}

		echo json_encode($gifts);
	}


	static private function searchSenderEmailAddress() {
		set_time_limit(120);

		$searchTerm = trim(request::unsignedPost("searchTerm"));
		$limit = request::unsignedPost("searchLimit");

		// Grab user
		$user = new userModel( array(
			"email" => $searchTerm
		));

		$gifts = array();
		if (!empty($user->id)) {
			$userGifts = giftModel::getUserGifts($user->id, globals::partner(), $limit);
			foreach($userGifts as $gift) {
				$gifts[] = self::returnGiftArray($gift, true);
			}
		}

		// return this as an array
		echo json_encode($gifts);
	}

	static function searchWithTransaction(transactionModel $transaction){
		// Grab messages associated with transaction

		$messages = messageModel::loadall( array(
			"transactionId" =>	$transaction->id
		));


		$gifts = array();
		foreach($messages as $message){
			$gift = new giftModel($message->giftId);
			if($gift->partner == globals::partner()){
				$gifts[]=$gift;
			}
		}

		return json_encode( $gifts );
	}

	static private function searchRecipientEmailAddress() {

		$searchTerm = request::unsignedPost("searchTerm");
		$searchLimit = request::unsignedPost("searchLimit");

				// Grab gifts associted with messages
		$recipGifts = giftModel::loadAll( array(
			"recipientEmail" => $searchTerm,
			"partner"=>globals::partner()
		), "$searchLimit", "created DESC");

		$gifts = array();
		foreach($recipGifts as $gift) {
			$gifts[] = self::returnGiftArray($gift, true);
		}

				echo json_encode( $gifts );
	}

	static public function loadGift() {
		$giftId = request::unsignedPost("giftId");

		$gift = new giftModel($giftId);
		if($gift->partner != globals::partner()){
			unset($gift);
		}

		echo json_encode( self::prepareGift( $gift ));
	}

	static public function prepareGift( $gift ) {
		$cardStatus		= adminCustomerSupportHelper::getCardStatus($gift);
		$giftStatus		= adminCustomerSupportHelper::getGiftStatus($gift);
		$paymentStatus	= adminCustomerSupportHelper::getPaymentStatus($gift);
		$redeemed = adminCustomerSupportHelper::getGiftRedemption($gift);
		$cstLogs = adminCustomerSupportHelper::getCstLogs($gift);
		$shippingDetail = adminCustomerSupportHelper::getShippingDetail($gift);
		$api = new KountApiEmail();
		$kountSettings = settingModel::getPartnerSettings(null, 'kountConfig');

		$canRefund = 0;
		$user = loginHelper::forceLogin();
		if($user->hasPermission('canRefund')) {
			$canRefund = 1;
		}

		$canEditVip = 0;
		if($user->hasPermission('canEditVip')) {
			$canEditVip = 1;
		}

		$canKount = 0;
		if($user->hasPermission('canKount')) {
			$canKount = 1;
		}

		$messages = $gift->getAllMessages();
		$formattedMessages = array();
		$shoppingCarts = array();
		foreach($messages as $message) {
			$obj = get_object_vars($message);
			$obj['shoppingCart'] = $message->getShoppingCart();
			$obj['transaction'] = $obj['shoppingCart']->getTransaction();
			$obj['promo'] = $message->getActivePromotion();
			$obj['emailVipStatus'] = $api->searchEmail($obj['transaction']->fromEmail);
			$shoppingCarts[] = $message->shoppingCartId;
			$formattedMessages[] = $obj;
		}

		//grab transactions from the carts
		//(because refunded messages we remove the transaction Id from)
		$shoppingCarts = array_unique($shoppingCarts);
		$transactions = array();
		foreach($shoppingCarts as $cartId) {
			$txn = new transactionModel;
			$txn->shoppingCartId = $cartId;
			if($txn->load('shoppingCartId')) {
				$transactions[] = $txn->id;
			}
		}
		$transactions = array_unique($transactions);

		return array(
			"gift"			=> $gift,
			"shippingDetail" => $shippingDetail,
			"deliveryDate" => $gift->deliveryDate, //for some reason deliveryDate is protected, so it won't json encode
			"recipientVipStatus" => $api->searchEmail($gift->recipientEmail),
			"reservation"	=> $gift->getReservation(),
			"inventory"		=> $gift->getInventory(),
			"messages"		=> $formattedMessages,
			"design"		=> $gift->getDesign(),
			"cstLogs"       => $cstLogs,
			"screenedBy"	=> $gift->screenedBy,
			"screenedNotes"	=> $gift->screenedNotes,
			"redeemed"      => $redeemed,
			"amount"		=> $gift->unverifiedAmount,
			"cardStatus"	=> $cardStatus,
			"giftStatus"	=> $giftStatus,
			"paymentStatus"	=> $paymentStatus,
			"isAuthorized"	=> !in_array('Not Authorized / Voided', $paymentStatus),
			"isRefunded"	=> in_array('Refunded', $paymentStatus),
			"isChargeback"	=> in_array('Chargeback', $paymentStatus),
			"kountVars" => $kountSettings,
			"canRefund" => $canRefund,
			"canEditVip" => $canEditVip,
			"canKount" => $canKount,
			"giftAmount" => ($gift->claimed)? $gift->getInventory()->activationAmount : $gift->unverifiedAmount,
			"contributors" => $gift->unverifiedContributorCount
		);
	}

		static private function searchTransactionId() {
			// Load up transaction
			$transaction = new transactionModel(array(
					"externalTransactionId" => request::unsignedPost("searchTerm")
			));
			if($transaction->id === null) {
				return json_encode(array());
			}

			// Grab messages associated with transaction
			$messages = messageModel::loadAll( array(
				"transactionId" => $transaction->id
			));

			$gifts = array();
			foreach($messages as $message){
				$gift = new giftModel($message->giftId);
				if($gift->partner == globals::partner()){
					$gifts[]=self::returnGiftArray($gift, true);
				}
			}

			echo json_encode( $gifts );
		}


	static function returnGiftArray(giftModel $gift, $listOnly=false){
		$cardStatus = adminCustomerSupportHelper::getCardStatus($gift);
		$giftStatus = adminCustomerSupportHelper::getGiftStatus($gift);
		$paymentStatus = adminCustomerSupportHelper::getPaymentStatus($gift);

		$canRefund = 0;
		$user = loginHelper::forceLogin();
		if($user->hasPermission('canRefund')) {
			$canRefund = 1;
		}

		$result = array(
			//objects
			"gift" => $gift,
			"messages" => $gift->getMessages(),
			"screenedBy" => $gift->screenedBy,
			"screenedNotes" => $gift->screenedNotes,
			//variables
			"amount" => $gift->unverifiedAmount,
			"cardStatus" => $cardStatus,
			"giftStatus" => $giftStatus,
			"paymentStatus" => $paymentStatus,
			"canRefund" => $canRefund
		);
		if(!$listOnly) {
			$result["reservation"] = $gift->getReservation();
			$result["inventory"] = $gift->getInventory();
			$result["design"] = $gift->getDesign();
			$result["cstLogs"] = adminCustomerSupportHelper::getCstLogs($gift);
			$result["redeemed"] = adminCustomerSupportHelper::getGiftRedemption($gift);
			$result["isRefunded"] = in_array('Refunded', $paymentStatus);
			$result["isChargeback"] = in_array('Chargeback', $paymentStatus);
		}
		return $result;
	}

		/** A test method to send a copy of all emails to someone */
		static public function sendTestEmails($gift, $testEmail) {

			$messages = $gift->getMessages();
			$message = $messages[0];
			$tx = new transactionModel();
			$tx->shoppingCartId = $message->getShoppingCart()->id;
			$tx->load();
			$tx->fromEmail = $testEmail;
			$tx->save();

			// Flagged email / Receipt
			$flaggedOrderWorker = new flaggedOrderEmailWorker();
			$flaggedOrderWorker->send($tx->id);

			// Thank you
			$worker = new thankYouEmailWorker();
			$worker->send(json_encode(array(
							'recipientName' => 'Tom',
							'purchaserName' => $message->fromName,
							'purchaserEmail' => $testEmail,
							'message' => 'Thank you',
							'messageId' => $message->id
			)));

			// Receipt
			$receiptWorker = new receiptEmailWorker();
			$receiptWorker->send($tx->id);

			// Invite
			$worker = new inviteEmailWorker();
			$msg['address'] = $testEmail;
			$msg['giftGuid'] = $gift->guid;
			$msg['message'] = 'Message';
			$msg['senderName'] = 'Tom';
			$worker->send(json_encode($msg));

			// Contribution
			self::sendEmailByTemplate('contributorDelivery',$testEmail,$gift);

			//claim
			$claim = new recipientDeliveryEmailWorker();
			$claim->send(json_encode(array('giftId'=>$gift->id,'email'=>$testEmail)));

			// Refund
			$refundEmailWorker = new refundEmailWorker();
			$refundEmailWorker->send(json_encode(array('transactionId'=>$tx->id)));

			// Claim reminder - purchaser and recipient
			$worker = new reminderEmailWorker();
			$worker->send(json_encode(array('giftId'=>$gift->id,'email'=>$testEmail)));

			// Delivery failure
			self::sendEmailByTemplate('bounceRecipientDelivery',$testEmail,$gift);

			// Facebook failure
			self::sendEmailByTemplate('facebookFailure',$testEmail,$gift);
		}

		static public function sendEmailByTemplate($template,$email,$gift){
			globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);
			view::Set('gift',$gift);
			$mailer = new mailer();
			$mailer->giftId = $gift->id;
			$mailer->workerData = $gift->id;
			$mailer->recipientEmail = $email;
			$mailer->template = $template;
			$mailer->send();
			log::info("Sent $template email for gift $gift->id to $email.");
		}

		/*
		 * Ajax functions
		 */
		static public function sendGift($userId) {
			$email = request::unsignedPost('resendEmail');
			$giftId = request::unsignedPost('giftId');
			$gift = new giftModel($giftId);

			log::debug("Sending gift as userId: $userId");

			if(emailHelper::isValidEmail($email)){
				$worker = new recipientDeliveryEmailWorker();
				$worker->send(json_encode(array(
					'giftId' => $giftId,
					'email' => $email,
					'userId' => $userId,
				)));


				// A way to test all the emails
				//self::sendTestEmails($gift, 'tom@groupcard.com');

				actionModel::logChanges('resend',
					array('email' => $email, 'resend' => 1),
					array('email' => $gift->recipientEmail),
					array('giftId' => $giftId)
				);

				echo(json_encode(array(
					'status' => 'success',
					'message' => 'Email Sent!'
				)));
			} else {
				echo(json_encode(array(
					'status' => 'error',
					'message' => $email.' is not a valid email address'
				)));
			}
		}

		static public function approveGift($userId) {
			$giftId = request::unsignedPost('giftId');
			screeningHelper::approveGift($giftId, $userId);
			echo(json_encode(array(
				'status' => 'success',
				'message' => 'Gift Approved and Email Sent!!'
			)));
		}

		static public function rejectGift($userId) {
			$giftId = request::unsignedPost('giftId');
			screeningHelper::rejectGift($giftId, $userId);
			echo(json_encode(array(
				'status' => 'success',
				'message' => 'Gift Rejected!'
			)));
		}

		static public function refundMessage($userId) {
			$messageId = request::unsignedPost('messageId');
			$message = new messageModel($messageId);

			$paymentHelper = new paymentHelper();
			$success = true;


			try {
				//paymentHelper also sends out the refund email(s)
				$success = $paymentHelper->refundMessage($message, $userId);
			}
			catch(paymentRefundException $e) {
				//refund error, should notify the screener and give them the reason
				log::error("Refund failed.", $e);
				$success = false;
			}
			catch(paymentUnknwonException $e) {
				//random payment error, notify the screener and let them know what's going on
				log::error("Refund failed.", $e);
				$success = false;
			}
			catch(Exception $e) {
				log::error("Refund failed.", $e);
				$success = false;
			}
			if($success) {
				echo(json_encode(array(
						'status' => 'success',
						'message' => "Payment Refunded!"
				)));
			}
			else{
				echo(json_encode(array(
						'status' => 'failure',
						'message' => "Payment Refund Failed!"
				)));
			}
		}

		static public function refundGift($userId) {
			$giftId = request::unsignedPost('giftId');
			$gift = new giftModel($giftId);

			$paymentHelper = new paymentHelper();
			$success = true;

			try {
				//paymentHelper also sends out the refund email(s)
				$success = $paymentHelper->refundGift($gift->id, $userId);
			}
			catch(paymentRefundException $e) {
				//refund error, should notify the screener and give them the reason
				log::error("Refund failed.", $e);
				$success = false;
			}
			catch(paymentUnknwonException $e) {
				//random payment error, notify the screener and let them know what's going on
				log::error("Refund failed.", $e);
				$success = false;
			}
			catch(Exception $e) {
				log::error("Refund failed.", $e);
				$success = false;
			}

			if($success) {
				//mark the gift as screened
				$gift->inScreeningQueue = 0;

				//maybe wanna add in refundedBy logging?
				//$gift->refundedBy= $userId;
				$gift->save();
				echo(json_encode(array(
					'status' => 'success',
					'message' => 'Gift Refunded!'
				)));
			}
			else {
				echo(json_encode(array(
					'status' => 'failure',
					'message' => 'Gift Refund Failed!'
				)));
			}
		}



		/*
		 * Actual Helper functions
		 */
		static public function getGiftRedemption($gift) {
			$inventory = $gift->getInventory();
			$redemption = new externalRedemptionModel();
			$redemption->inventoryId = $inventory->id;
			if($redemption->load('inventoryId')) {
				return $redemption->redemptionTime;
			}
			else {
				return "Unknown";
			}
		}

		static public function getCardStatus($gift) {
			$reservation = $gift->getReservation();
			$inventory = $gift->getInventory();
			$redemption = new externalRedemptionModel();
			$redemption->inventoryId = $inventory->id;

			/*** Card Status ***/
			$cardStatus = "Not Available";
			//doesn't exist yet
			if($redemption->load('inventoryId')) {
				$cardStatus = "Redeemed";
			}
			else if($inventory->deactivationTime !== null) {
				$cardStatus = "Deactivated";
			}
			else if($inventory->activationTime !== null) {
				$cardStatus = "Activated";
			}
			else if($reservation->reservationTime !== null) {
				$cardStatus = "Reserved (Not Activated)";
			}
			return $cardStatus;
		}

		static public function getGiftStatus($gift) {

			/*** Gift Status ***/
			$giftStatus = "Not Created";

			//reject/approved statuses
			if($gift->rejected) {
				$giftStatus = "Rejected";
			}
			else if($gift->approved) {
				$giftStatus = "Approved";
			}
			else if($gift->inScreeningQueue) {
				$giftStatus = "In Screening Queue";
			}

			//delivery statuses
			if($gift->delivered !== null) {
				$giftStatus .= ",Delivered";
			}
			else if($gift->paid) {
				$giftStatus .= ",Not Delivered";
			}

			return $giftStatus;
		}


	static public function getPaymentStatus($gift) {

		$messages = $gift->getMessages();

		/*** Gift Status ***/
		$statuses = array();

		// Grab messages associated with transaction
		foreach($messages as $message) {

			//want to show all chargebacks
			if(isset($message->chargeback) && $message->chargeback !== null) {
				$statuses[] = "Chargeback";
			}

			//go through payment statuses
			if($message->refunded !== null) {
				$statuses[] = "Refunded";
			}
			else if($message->isPaid()) {
				$statuses[] = "Paid";
			} else {
				$statuses[] = "Authorized";
			}
		}

		if(count($statuses) == 0) {
			$statuses[] = "Not Authorized / Voided";
		}

		return array_unique($statuses);
	}

	static public function getEmails($gift){
		$emails = emailModel::loadAll(array(
			"giftId"=>$gift->id
		));

		return $emails;
	}

	static public function getCstLogs(giftModel $gift){
		return $gift->getRelatedCstLogs();
	}

	static public function changeEmailStatus() {

		$email = request::unsignedPost("email");
		$status = request::unsignedPost("emailStatus");

		$api = new KountApiEmail();
		$api->addEmail($email, $status);
	}

	static public function getShippingDetail(giftModel $gift) {
		$shippingDetail = new shippingDetailModel();
		$shippingDetail->giftId = $gift->id;
		if (!$shippingDetail->load()) {
			return array();
		}
		$shippingDetail->loadShippingOption();
		return $shippingDetail;
	}
}
