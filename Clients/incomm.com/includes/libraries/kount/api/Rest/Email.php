<?php

/**
 * RESTful request class for email
 * 
 * @category giftingapp
 * @package KountApi.Rest
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

namespace KountApi\Rest;

use KountApi\Rest as RestApi;

class Email extends RestApi {

	const STATUS_APPROVE = 'A';
	const STATUS_DECLINE = 'D';
	const STATUS_REVIEW = 'R';
	const STATUS_DELETE = 'X';

	// Translate old email status to new RESTful API email status
	private $_status = array(
		'approve' => self::STATUS_APPROVE,
		'review' => self::STATUS_REVIEW,
		'decline' => self::STATUS_DECLINE
	);

	public function __construct() {
		$settings = \settingModel::getPartnerSettings(null, 'kountConfig');
		$endpoint = $settings['apiEndpoint'];
		$certName = '/media/ram/' . $settings['apiCertificateName'];
		$certPass = $settings['apiCertificatePassword'];

		parent::__construct($endpoint, array(
			'sslKey' => $certName . '_key.pem',
			'sslCertificate' => $certName . '_cert.pem',
			'sslPassphrase' => $certPass
		));
	}

	public function addEmail($email, $status) {
		try {
			if (empty($status)) {
				$this->deleteEmail($email);
				return;
			}
			$this->_post(array(
				"email[{$email}]" => $this->_status[$status]
			));
		} catch (\Exception $e) {
			\log::error("Kount REST error while adding an email address {$email}", $e);
		}
	}

	public function deleteEmail($email) {
		try {
			$this->_post(array(
				"email[{$email}]" => self::STATUS_DELETE
			));
		} catch (\Exception $e) {
			\log::error("Kount REST error while searching an email address {$email}", $e);
		}
	}

	public function searchEmail($email) {
		try {
			$response = $this->_get(array(
				'email' => $email
			));
			return $response->result;
		} catch (\Exception $e) {
			\log::error("Kount REST API error while searching email {$email}", $e);
			return false;
		}
	}

}
