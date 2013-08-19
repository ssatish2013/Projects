<?php

class voucherController {

	public static $defaultMethod = 'index';
	private $_claim = null;

	public function __construct() {
		if (!globals::partner()) {
			echo 'Invalid Partner';
			throw new invalidPartnerException();
		}
		$this->_claim = new claimController();
		view::set('ui', settingModel::getPartnerSettings(null, 'ui'));
		view::set('settingsArray', get_object_vars(view::get('settings')));
	}

	public function printGet() {
		$displayVoucherPage = false;
		if (!is_null(request::url('mguid'))) {
			$message = new messageModel(request::url('mguid'));
			if (!empty($message->giftId)) {
				$gift = new giftModel($message->giftId);
				$recipientGuid = new recipientGuidModel();
				$recipientGuid->giftId = $gift->id;
				$recipientGuid->guid = randomHelper::guid(16);
				$recipientGuid->expires = date('Y-m-d H:i:s', strtotime('+1 year'));
				$recipientGuid->save();
				$displayVoucherPage = true;
			}
		} else {
			// Load recipient guid
			$recipientGuid = new recipientGuidModel(request::url('guid'));
			// Load gift
			if (!empty($recipientGuid->guid)) {
				$gift = new giftModel($recipientGuid->giftId);
				$message = array_shift($gift->getMessages());
				if ($gift->isClaimed() || !$recipientGuid->isExpired()) {
					$displayVoucherPage = true;
				} else {
					// crap, show the page where we resend the email
					$this->_claim->expiredGet($recipientGuid);
					return;
				}
			}
		}
		if ($displayVoucherPage) {
			// Get current partner
			$partner = globals::partner();
			// Pass guid and gift to template
			view::Set('partner', $partner);
			view::SetObject($recipientGuid);
			view::SetObject($gift);
			view::SetObject($message);
			// Awesome show the voucher page and load PIN/PAN with ajax
			view::Render('voucher/print');
		} else {
			// ruh roh, this isn't a valid recipient guid
			throw new NotFoundException('No valid recipient or message guid found for gift voucher.');
		}
	}
	
	public function sendSmsPost() {
		$message = new messageModel(request::unsignedPost('messageGuid'));
		$gift = new giftModel($message->giftId);
		$gift->recipientPhoneNumber = request::unsignedPost('phoneNumber');
		$gift->save();
		$gift->sendSMS($message);
	}

}
