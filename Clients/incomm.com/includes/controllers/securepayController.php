<?php

class securepayController {

	public function __construct() {
		if (globals::method() == 'fake' && Env::main()->getEnvName() == 'production') {
			//check settings for allow fake checkout on production.
			if ('1' != settingModel::getSetting('slc', 'allowFake')) throw new NotFoundException();
		}
	}

	/**
	 * Fake securepay gateway.
	 */
	public function fakePost() {
		$reqs = $_REQUEST;

		// Extract values from important elements and put them in $params
		$params = array(
			'tx_transdataiv'   => $reqs['transdataiv'],
			'tx_first_name'    => $reqs['firstName'],
			'tx_last_name'     => $reqs['lastName'],
			'tx_phone_number'  => $reqs['phoneNumber'],
			'tx_email'         => $reqs['email'],
			'tx_email_confirm' => $reqs['confirmEmail'],
			'tx_address'       => $reqs['address1'],
			'tx_address2'      => $reqs['address2'],
			'tx_city'          => $reqs['billingcity'],
			'tx_zip'           => $reqs['billingzip'],
			'tx_country_code'  => $reqs['country'],
			'agree'            => $reqs['agreeterm'],
			'tx_card_name'     => $reqs['cardName'],
		);
		switch ($reqs['country']) {
			case 'US': $region = $reqs['transactionStateList'];    break;
			case 'CA': $region = $reqs['transactionProvinceList']; break;
			default:   $region = $reqs['region'];                  break;
		}
		$params['tx_state_code'] = $params['tx_region'] = $region;

		// Decrypt transaction data and merge them into $params
		$transdata = slcPayment::decryptTransactionData(
			$reqs['transdata'],
			$reqs['transdataiv']
		);
		$params = array_merge($params, $transdata);

		// A fake authorization id.
		$authId = 'FAKE' . time();

		// Fingerprint, we can use our SLC helper.
		$cart = new shoppingCartModel($reqs['tx_callback_id']);
		$fingerprint = slcPayment::calculateRelayFingerprint(
			$params['tx_payment_config'],
			$cart,
			$authId
		);

		// Add faked out securepay generated params.
		$params['success'] = '1';
		$params['authorization_code'] = $authId;
		$params['Processor_TIMESTAMP'] = date('Y-m-d\TH:i:s\Z');
		$params['Processor_CORRELATIONID'] = 'FAKE147ad31689ba2';
		$params['Processor_ACK'] = 'Success';
		$params['Processor_VERSION'] = '60.0';
		$params['Processor_BUILD'] = '3288089';
		$params['Processor_AMT'] = $params['tx_amount'];
		$params['Processor_CURRENCYCODE'] = $params['tx_currency_code'];
		$params['Processor_AVSCODE'] = 'X';
		$params['Processor_CVV2MATCH'] = 'M';
		$params['Processor_TRANSACTIONID'] = '6VV74241RH985070E';
		$params['relay_fingerprint'] = $fingerprint;
		$params['cc_hash'] = '443630CZ8YAP0UYRJ131';
		$params['cc_last4'] = '2406';

		// Post to the relay url which is /payments
		$relayUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/payments';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_URL, $relayUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		if ($response === FALSE) {
			log::error("curl_request failed: url={$relayUrl}: " . curl_error($ch));
			throw new Exception('curl error');
		}

		// Echo back the response from the relay.
		log::debug('Relay response: ' . print_r($response, true));
		echo $response;
	}

	/**
	 * Fake securepay iframe
	 */
	public function fakeGet() {
		$url = slcPayment::getSetting('fakeIframeFetchUrl')
			. '?' . http_build_query($_GET);
		log::debug("Construct FAKE SecurePay iframe fetched from url: {$url}");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		echo preg_replace(
			array(
				'/(<form.+action=")([^"]+)(")/',
				'/(test)(\-)([^\.]+)(\.giftingapp\.com)/',
				'/(<img.+src=")([^"]*blank\.png)(")/'
			),
			array(
				'${1}' . slcPayment::getSetting('fakeIframePostUrl') . '${3}',
				globals::partner() . '${2}' . Env::getEnvName() . '${4}',
				'${1}//gca-common.s3.amazonaws.com/assets/blank.png${3}'
			),
			$response
		);
	}

}
