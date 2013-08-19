<?php

class kountApi {

	const EMAIL_APPROVE = 'approve';
	const EMAIL_REVIEW = 'review';
	const EMAIL_DECLINE = 'decline';

	private $client = null;

	public function __construct() {
		$settings = settingModel::getPartnerSettings(null, 'kountConfig');

		$url = $settings['apiUrl'];
		$certificateName = "/media/ram/" . $settings['apiCertificateName'];
		$certificatePass = $settings['apiCertificatePassword'];

		$this->client = new SoapClient($url, array(
			'soap_version' => SOAP_1_1,
			'exceptions' => true,
			'trace' => 1,
			'cache_wsdl' => WSDL_CACHE_NONE,
			'local_cert' => $certificateName . '_combined.pem',
			'passphrase' => $certificatePass
		)); 
	}

	/* EMAIL FUNCTIONS */

	public function addEmail($email, $status) {
		if ($status == '') {
			$this->deleteEmail($email);
			return;
		}

		if ($status != self::EMAIL_APPROVE &&
			$status != self::EMAIL_REVIEW &&
			$status != self::EMAIL_DECLINE) {
			throw new Exception("Invalid status type provided to kountApi::addEmail");
		}
		$this->client->emailsSave(array(
			'email' => array(
				'_' => $email,
				'status' => $status
			)
		));
	}

	public function deleteEmail($email) {
		$this->client->emailsDelete(array(
			'email' => $email
		));
	}

	public function searchEmail($email) {
		try {
			$result = $this->client->emailsSearch(array(
				'search' => $email
			));

			if ($result->successes >= 1) {
				if (isset($result->results->email)) {
					return $result->results->email->status;
				}
			}
		} catch (SoapFault $e) {
			log::error("SoapFault while searching email address <{$email}> in Kount."
				. "\n{$e->getMessage()}");
		}
		return false;
	}

}
