<?php

class slcPayment {

	private static $_cryptoIv = null;
	private static $_partner = null;

	public static function calculateFingerprint($paymentConfig, shoppingCartModel $cart) {
		$secret = self::getSetting('secret');
		$login = self::getLogin();
		$data = "{$login},{$paymentConfig},{$cart->id},"
			. number_format($cart->getTotal(), 2,'.','')
			. ",{$secret}";
		$hash = hash('sha256', $data);
		log::debug("sha256 hash for \"{$data}\" is {$hash}");
		return $hash;
	}

	public static function calculateRelayFingerprint($partner, shoppingCartModel $cart, $authCode) {
		$relaySecret = self::getSetting("relaySecret");
		$login = self::getLogin();
		$data = "{$login},{$partner},{$cart->id},"
			. number_format($cart->getTotal(), 2,'.','')
			. ",{$authCode},{$relaySecret}";
		$hash = hash("sha256", $data);
		log::debug("sha256 hash for \"$data\" is $hash");
		return $hash;
	}

	public static function isEnabled() {
		$enabled = self::getSetting('enabled');
		return (isset($enabled) && $enabled == '1') ? true : false;
	}

	public static function getLogin() {
		return self::getSetting('login');
	}

	public static function getSetting($key) {
		return settingModel::getSettingRequired('slc', $key);
	}

	public static function getPostUrl() {
		return self::getSetting('postUrl');
	}

	/**
	 * Generate (if this method is called at the first time or parameter
	 * $regenerate is given true value) an IV (Initialization Vector)
	 * using simple random guid algorithm and return it.
	 *
	 * Normally IV should be made by mcrypt IV functions mcrypt_get_iv_size()
	 * and mcrypt_create_iv(); however, mcrypt IV generation function
	 * does not create URL friendly strings. Random guid can be easily
	 * embeded to URL querying string.
	 *
	 * @param boolean $regenerate
	 * @param integer $length
	 * @return string
	 * @access public
	 * @static
	 */
	public static function getCryptoIv($regenerate = false, $length = 8) {
		if (is_null(self::$_cryptoIv) || $regenerate) {
			self::$_cryptoIv = randomHelper::guid($length);
		}
		return self::$_cryptoIv;
	}

	/**
	 * Encrypt sensitive data such as shopping cart with mcrypt triple des function
	 * at cbc mode. PHP extension Mcrypt, function mcrypt_cbc() and linux libmcrypt
	 * are requred. Encrypted data will be encoded with base64_encode().
	 *
	 * PHP only pads zeros to the input string, whereas dot-Net has PKCS7 padding by
	 * default. SecurePay has changed padding mode to zero to accept encrypted data
	 * from giftingapp.
	 *
	 * PKCS7 padding is more secured than the default zero padding. I would recommend
	 * changing zero padding to PKCS7 padding in the future if higher security level
	 * of implementation is needed.
	 *
	 * @param shoppingCartModel $cart
	 * @return string
	 * @throws Exception
	 * @access public
	 * @static
	 */
	public static function encryptTransactionData(shoppingCartModel $cart) {
		$key = self::getSetting('encryptionKey');
		$iv = self::getCryptoIv();
		$input = self::_buildTransactionDataQuery($cart);
		if (!function_exists('mcrypt_cbc')) {
			throw new Exception('Mcrypt extension and libmcrypt are required for SecurePay.');
		}
		$encryptedData = mcrypt_cbc(MCRYPT_3DES, $key, $input, MCRYPT_ENCRYPT, $iv);
		$encodedString = base64_encode($encryptedData);
		log::debug("\n==========\n- Original transdata:\n{$input}\n\n"
			. "- IV: {$iv}\n\n"
			. "- 3des encrypted and base64 encoded data:\n{$encodedString}\n==========");
		return $encodedString;
	}

	/**
	 * Build http querying string as original input data from transaction
	 * data encryption.
	 *
	 * @param shoppingCartModel $cart
	 * @return string
	 * @access private
	 * @static
	 */
	private static function _buildTransactionDataQuery(shoppingCartModel $cart) {
		//we need to determine currency by gifts in the cart
		//we have an enforced policy that all gifts in cart must have same currency,
		//so always get currency code from first gift in cart.
		$gifts = $cart->getAllGifts();
		if (count($gifts)==0){
			throw new Exception('Can not find any gift in the cart, thus can not determine currency for SecurePay.');
		}
		$queryData = array(
			'tx_payment_config' => self::getPaymentConfig(),
			'tx_sequence_id'    => $cart->id,
			'tx_callback_id'    => $cart->id,
			'tx_amount'         => number_format($cart->getTotal(), 2,'.',''),
			'tx_type'           => 'AUTHONLY',
			'tx_currency_code'  => $gifts[0]->currency,
			'tx_fingerprint'    => self::calculateFingerprint(self::getPaymentConfig(), $cart),
			'tx_ip_address'     => $_SERVER['HTTP_X_REAL_IP'],
			'tx_user_agent'     => $_SERVER['HTTP_USER_AGENT'],
			'tx_environment'    => Env::getEnvName()
		);
		return http_build_query($queryData);
	}
	
	public static function getPaymentConfig() {
		$env = Env::getEnvName();
		$isFakeIframe = self::getSetting('iframeSrc') == self::getSetting('fakeIframeSrc');
		
		if ($env == 'production') return self::$_partner = globals::partner();
		if ($isFakeIframe) return 'fakeiframe';
		else return $env;
	}
	
	/**
	 * Return real partner name for real iframe and fake partner name for fake iframe
	 *
	 * @return string - partner name, real or fake 'test' for fake iframe
	 * @access public
	 * @static
	 */
	public static function getPartner() {
		if (is_null(self::$_partner)) {
			if (self::getSetting('iframeSrc') == self::getSetting('fakeIframeSrc')
				&& Env::getEnvName() != 'production') {
				// ONLY for testing fake iframe
				//
				// SecurePay only has configs for a small set of parnters,
				// so we return a fake partner which has already been configured
				// at SecurePay side to have it return billing form for
				// generating fake iframe
				self::$_partner = 'test';
			} else {
				self::$_partner = globals::partner();
			}
		}
		return self::$_partner;
	}

	/**
	 * Decrypt transaction data which was encrypted with 3des mcrypt_cbc() function.
	 * It also parse the query string like data into an PHP array
	 *
	 * @param string $input - base64 encoded and 3des encrypted data
	 * @param string $iv - initialization vector that was used to encrypted the data
	 * @return array
	 * @access public
	 * @static
	 */
	public static function decryptTransactionData($input, $iv) {
		$key = self::getSetting('encryptionKey');
		$decodedData = base64_decode($input);
		$decryptedString = mcrypt_cbc(MCRYPT_3DES, $key, $decodedData, MCRYPT_DECRYPT, $iv);
		parse_str($decryptedString, $output);
		log::debug("\n==========\n- Original transdataiv: {$iv}\n\n"
			. "- Original transdata:\n{$input}\n\n"
			. "- Decrypted string:\n{$decryptedString}\n\n"
			. "- String parsed array:\n" . print_r($output, true) . "\n==========");
		return $output;
	}

}
