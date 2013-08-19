<?php

class paymentsController {

	public static $defaultMethod = 'main';

	public function mainGet() {
		
	}
	
	/**
	 * Process a relay response from SecurePay.
	 */
	public function mainPost() {
		$partner = globals::partner();
		$envName = $this->_validateEnvironment($_REQUEST);
		$rawPost = file_get_contents('php://input');
		log::debug("Request:\n" . print_r($_REQUEST, true)
			. "Raw post data:\n=========\n{$rawPost}\n========");
		
		if ($envName == Env::getEnvName() || $envName == 'production') {
			$this->_handleSameEnvRequest($partner, $envName);
		} else {
			log::info("Handling relay request from different environment: {$envName}");
			$this->_handleDiffEnvRequest($partner, $envName, $rawPost);
		}
	}

	private function _handleSameEnvRequest($partner, $envName) {
		$cartId = request::unsignedPost('tx_sequence_id');

		log::info('Validating requests.');
		// Package up the variables, and send to the SecurePay module to process.
		$transaction = $this->_validateSecurePayRequest($partner, $_REQUEST);
		if ($transaction->success) {
			$shoppingCart = new shoppingCartModel($transaction->shoppingCartId);
			$payment = new payment();
			$payment->loadPlugin(null, 'paypalPayment');
			$transaction->assignPaymentMethod($payment);
			paymentHelper::checkoutShoppingCart($shoppingCart, $transaction);
			// Mark the shopping cart as not current, so when the redirection
			// for a successful transaction to checkout confirm page fails,
			// the shopping cart will get cleared and the gift will not stuck
			// in the cart
			$shoppingCart->isCurrent = 0;
			$shoppingCart->save();
		}
		
		// Success, output the redirect HTML.
		$redirectUrl = paymentHelper::confirmUrl($partner, $cartId, $envName);
		
		log::info("Processed relay response, setting redirect to {$redirectUrl}");
		view::set('redirectUrl', $redirectUrl);
		view::Render('cart/_securepayRedirect');
	}

	private function _handleDiffEnvRequest($partner, $envName, $rawPost) {
		$url = 'https://' . $partner . '-' . $envName . '.giftingapp.com/payments';
		// Initiate a new cURL request session
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $rawPost);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// Execute the cURL request
		log::info("Starting SecurePay relay curl request (dev only) to {$url}");
		$response = curl_exec($ch);
		// Handle cURL request errors
		if ($response === FALSE) {
			log::error("Relay cURL request (dev only) failed: url={$url}, request={$rawPost}: " . curl_error($ch));
			throw new Exception("Relay cURL request failed.");
		}
		$info = curl_getinfo($ch);
		$status = $info['http_code'];
		log::info("Relay cURL request (dev only) completed. HTTP Status: {$status}");
		if ($status != 200) {
			log::error("Relay cURL request (dev only) failed: url={$url}, request={$rawPost}, response={$response}" . curl_error($ch));
			throw new Exception("Relay cURL request failed.");
		}
		// Close the cURL session
		curl_close($ch);
		// Echo out the response which was fetched from $url
		//
		// Response should contain a html page that will redirect to the
		// checkout confirmation page on the correct environment
		echo $response;
	}

	private function _validateSecurePayRequest($partner, $params) {
		// Validate request.
		if (!isset($params['tx_sequence_id'])) {
			throw new validationException('tx_sequence_id missing in request.');
		}
		$cartId = $params['tx_sequence_id'];
		if (!isset($params['tx_fingerprint'])) {
			throw new validationException('tx_fingerprint missing in request.');
		}
		$relayFingerprint = $params['relay_fingerprint'];
		
		if (!isset($params['authorization_code'])) {
			throw new validationException('authorization_code missing in params.');
		}
		$authorizationCode = $params['authorization_code'];
		
		// Check that shopping cart exists.
		$cart = new shoppingCartModel($cartId);
		if (!$cart->partner) {
			throw new validationException("Shopping cart#{$cartId} does not exist.");
		}
		
		if (!$cart->getAllMessages()) {
			throw new validationException("cart#{$cart->id} has no messages, we should not even be here.");
		}
		
		// Validate fingerprint.
		$myFingerprint = slcPayment::calculateRelayFingerprint(
			slcPayment::getPaymentConfig(), 
			$cart,
			$authorizationCode
		);
		if ($myFingerprint != $relayFingerprint) {
			log::warn("Fingerprint mismatch, tx_sequence_id = {$cartId} {$myFingerprint} != {$relayFingerprint}");
			throw new validationException('Invalid request; fingerprint mismatch.');
		}
		
		log::info("Valid payment notification for cart#{$cart->id}.");
		
		$transaction = new transactionModel();
		$transaction->currency = $params['tx_currency_code'];
		$transaction->amount = $params['tx_amount'];
		$transaction->authorizationId = $params['authorization_code'];
		$transaction->authorizationTime = null;
		$transaction->firstName = $params['tx_first_name'];
		$transaction->lastName = $params['tx_last_name'];
		$transaction->fromEmail = $params['tx_email'];
		$transaction->phoneNumber = $params['tx_phone_number'];
		$transaction->address = $params['tx_address'];
		$transaction->address2 = $params['tx_address2'];
		$transaction->city = $params['tx_city'];
		$transaction->state = $params['tx_state_code'];
		$transaction->zip = $params['tx_zip'];
		$transaction->country = $params['tx_country_code'];
		$transaction->creditCard = trim($params['cc_hash']);
		$transaction->ccLastFour = isset($params['cc_last4']) ? $params['cc_last4'] : '0000';
		$transaction->ccType = null;
		$transaction->cvv2Match = isset($params['Processor_CVV2MATCH']) ? $params['Processor_CVV2MATCH'] : '';
		$transaction->setIpAddress ($params['tx_ip_address']);
		$transaction->domaintoolsCacheId = $transaction->getEmailDomainID ();
		$transaction->userAgent = urldecode($params['tx_user_agent']);
		$transaction->refunded = null;
		$transaction->shoppingCartId = $cart->id;
		$transaction->optIn = isset($params['tx_optin']) ? $params['tx_optin'] == 'true' : false; 
		$transaction->success = $params['success'];
		
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
			throw new validationException("A transaction has already been recorded for cart#{$cart->id}.");
		}
		
		return $transaction;
	}

	private function _validateEnvironment($req) {
		if (isset($req['tx_environment'])) {
			$reqEnv = $req['tx_environment'];
			$envs = Env::getEnvironmentList();
			if (isset($envs[$reqEnv])) {
				return $reqEnv;
			}
		}
		return Env::getEnvName();
	}

}
