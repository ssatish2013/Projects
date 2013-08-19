<?php

class claimController {

	const PAGE_CLAIM = 'claim';
	const PAGE_VOUCHER = 'voucher';

	public static $defaultMethod = 'index';
	
	public function __construct() {
		if(!globals::partner()){
			echo 'Invalid Partner';
			throw new invalidPartnerException();
		}
		
		$fbCreds = settingModel::getPartnerSettings(null, 'facebook');
		
		$fbSettings = array(
			'appId' => isset($fbCreds['appId']) ? $fbCreds['appId'] : null,
			'secret' => isset($fbCreds['secret']) ? $fbCreds['secret'] : null
		);
		
		view::Set('fbSettings', $fbSettings);
		view::set('ui', settingModel::getPartnerSettings(null, 'ui'));
		view::set('settingsArray', get_object_vars(view::get('settings')));
	}
	
	public function giftGet() {
		//force facebook setting
		$forceFacebookClaim = settingModel::getSetting('claim', 'forceFacebookClaim');
		view::Set('forceFacebookClaim', $forceFacebookClaim);
		// Load recipient guid
		$recipientGuid = new recipientGuidModel(request::url('guid'));
		// Load gift
		if (!empty($recipientGuid->guid)) {
			$gift = new giftModel($recipientGuid->giftId);
			$messages = $gift->getMessages();
			
			// Pass guid and gift to template
			view::SetObject($recipientGuid);
			view::SetObject($gift);
			
			if ($gift->isClaimed() || !$recipientGuid->isExpired()) { 
				// Pass pinDisplay to the template if the page is viewed by a mobile browser
				if (mobileBrowserHelper::isMobile()) { 
					$settings = settingModel::getPartnerSettings(null, 'pinDisplay');
					$pinDisplay = isset($settings['pinDisplay'])
						? $this->_pinDisplay($gift, $settings['pinDisplay'], self::PAGE_VOUCHER)
						: '';
					view::Set('pinDisplay', $pinDisplay);
				}
				// Awesome show the recipient page and load PIN/PAN with ajax
				
				view::Render('claim/gift');
			} else {
				// crap, show the page where we resend the email
				$this->expiredGet($recipientGuid);
			}
		} else {
			// ruh roh, this isn't a valid recipient guid
			throw new NotFoundException('No valid recipient guid found for gift claim.');
		}
	}

	// PIN, PAN or barcode setup
	// 1. barcode
	//    set settings.pinDisplay.pinDisplay to
	//    <img src="/barcode/display/recipientGuid/{$gift->getCurrentRecipientGuid()}" />
	// 2. PIN and PAN in text
	//    keep settings.pinDisplay.pinDisplay empty or remove it from partner's settings
	//    or set settings.pinDisplay.pinDisplay to something like
	//    'Gift Code: {$inventory->pan} <br /> Pin: {$inventory->pin}'
	// 3. PIN only but not formatted
	//    set settings.pinDisplay.pinDisplay to something like 'Pin: {$inventory->pin}'
	//    set settings.pinDisplay.pinOnly to '1'
	//    keep settings.pinDisplay.pinFormat empty or remove it from partner's settings
	// 4. PIN only with format
	//    set settings.pinDisplay.pinDisplay to something like 'Pin: {$inventory->pin}'
	//    set settings.pinDisplay.pinOnly to '1'
	//    set settings.pinDisplay.pinFormat to a specific format like 'nnnn-nnnn-nnnn'
	public function codeGet() {
		$data = array(
			'pan' => '', 'pin' => '', 'pinDisplay' => '', 'pinOnly' => false,
			'pinFormat' => '', 'formattedPin' => '', //'delayedDelivery' => false,
			'exception' => array('has' => false, 'type' => '', 'message' => '')
		);
		// Load gift id from recipientGuids tables
		$recipientGuid = new recipientGuidModel(request::url('guid'));
		$pageName = request::url('page');
		// Load gift
		$gift = new giftModel($recipientGuid->giftId);
		// Get PIN display pattern from settings table
		$pinDisplay = settingModel::getPartnerSettings(null, 'pinDisplay');
		// >>>>>>>>>> Gift and Inventory activation >>>>>>>>>>
		try {
			// 1. If gift is a in-store voucher
			if ($gift->giftingMode == giftModel::MODE_VOUCHER) {
				$inventory = $gift->getInactiveInventory();
			}
			// 2. Else if gift has not been claimed, activate gift and get gift inventory object
			elseif (!isset($gift->claimed)) {
				// Security check, this gift must be approved before we can claim in.
				// Since we only send a claim email out when a gift is approved, someone
				// is trying to mess with us.
				if (!$gift->approved) {
					throw new Exception(
						"Attempt to claim gift#{$gift->id} which is not approved.",
						500
					);
				}
				$gift->claimed = date('Y:m:d H:i:s');
				// If the gift was rejected/refunded
				if ($gift->unverifiedAmount <= 0) { 
					throw new fraudRejectException();
				}
				$inventory = $gift->getAndActivateInventory();
			}
			// 3. Otherwise, just get inventory object
			else {
				$inventory = $gift->getInventory();
			}
			if ($inventory) {
				log::info("Claimed gift#{$gift->id}");
				$gift->save();
				view::set('inventory', $inventory);
				$data['pan'] = view::main()->fetch('string:' . $pinDisplay['panSource']);
				$data['pin'] = view::main()->fetch('string:' . $pinDisplay['pinSource']);
			}
			$data['exception']['has'] = false;
		} catch (inventoryException $e) {
			// @todo, we need to do something to handle this manually
			log::error("Inventory exception occurred during activation for Gift#{$gift->id}.", $e);
			$data['exception'] = array('has' => true, 'type' => 'activation', 'message' => $e->getMessage());
		} catch (fraudRejectException $e) {
			log::error("Fraud reject exception occurred during activation for Gift#{$gift->id}.", $e);
			$data['exception'] = array('has' => true, 'type' => 'fraudReject',
				'message' => languageModel::getString ('claimError'));
		} catch (Exception $e) {
			// Log non-approval error differently (if error code is 500)
			$logMessage = ($e->getCode() == 500)
				? $e->getMessage()
				: "Unknown exception occurred during activation for Gift#{$gift->id}.";
			log::error($logMessage, $e);
			$data['exception'] = array('has' => true, 'type' => 'unknown',
				'message' => languageModel::getString ('claimError'));
		}
		// >>>>>>>>>> PIN, PAN and barcode related >>>>>>>>>>
		if (isset($pinDisplay['pinDisplay'])) {
			$data['pinDisplay'] = $this->_pinDisplay($gift, $pinDisplay['pinDisplay'], $pageName);
		}
		if (isset($pinDisplay['pinOnly'])) {
			$data['pinOnly'] = ($pinDisplay['pinOnly'] == '1');
		}
		if (isset($pinDisplay['pinFormat'])) {
			$data['pinFormat'] = $pinDisplay['pinFormat'];
			$data['formattedPin'] = view::pinFormat($data['pin'], $pinDisplay['pinFormat']);
		}
		// >>>>>>>>>> Third-party products related >>>>>>>>>>
		// Override per partner pinDisplay if this is third party product
		$product = $gift->getProduct();
		if ($product->thirdparty && $product->pinDisplay) {
			$pinLang = languageModel::getString($product->pinDisplay);
			if ($pinLang) {
				$data['pinDisplay'] = $this->_pinDisplay($gift, $pinLang, $pageName);
			}
		}
		// >>>>>>>>>> Voucher barcode related >>>>>>>>>>
		// Changes barcode display link from .../barcode/display/... to
		// .../barcode/voucher/... for in-store voucher
		if ($gift->giftingMode == giftModel::MODE_VOUCHER) {
			$data['pinDisplay'] = str_replace(
				'/barcode/display/',
				'/barcode/voucher/',
				$data['pinDisplay']
			);
		}
		// >>>>>>>>>> Output JSON return >>>>>>>>>>
		header('Content-Type: application/json');
		echo json_encode($data);
	}

	public function sendThankYouMessagePost() {
		$gift = new giftModel(array('guid' => request::unsignedPost('giftGuid')));
		//process thank you message
		$message = request::unsignedPost('message');
		if (!empty($message)) {
			$gift->sendThankYou($message);
		}
	}

	public function mobileGet() {
		// Activate gift card
		ob_start();
		$this->codeGet();
		$json = ob_get_clean();
		$code = json_decode($json);
		view::Set('code', $code);
		// Display claim page
		$this->giftGet();
	}

	public function mobilePost() {
		// Handle send thank you message post
		$this->sendThankYouMessagePost();
		// Display claim page
		$this->giftGet();
	}

	public function expiredGet(recipientGuidModel $recipientGuid = null) {
		// If the mothod is invoked by giftGet() method, $recipientGuid will
		// be an object, otherwise it will be empty, so we need to get the guid
		// from url
		if (empty($recipientGuid)) {
			$guid = request::url('guid');
			// Redirect to home page if there is no recipient guid found in url
			if (is_null($guid)) {
				view::Redirect('gift', 'home');
			}
			// Otherwise, load recipient guid object and continue
			$recipientGuid = new recipientGuidModel($guid);
		}
		$gift = new giftModel($recipientGuid->giftId);
		$gift->getMessages();
		view::SetObject($recipientGuid);
		view::SetObject($gift);
		view::Render('claim/expired');
	}

	public function expiredPost() {
		$recipientGuid = new recipientGuidModel(request::unsignedPost('recipientGuid'));
		// Lets send out a new recipient email to this guy
		$w = new recipientDeliveryEmailWorker();
		$w->send($recipientGuid->giftId);
		$gift = new giftModel($recipientGuid->giftId);
		$gift->getMessages();
		view::SetObject($recipientGuid);
		view::SetObject($gift);
		view::Render('claim/expired');
	}

	public function sendSmsPost() {
		$gift = new giftModel (array('guid' => request::unsignedPost('giftGuid')));
		$gift->recipientPhoneNumber = request::unsignedPost('phoneNumber');
		$gift->save();
		$gift->sendSMS();
	}

	private function _pinDisplay(giftModel $gift, $pinDisplayString, $pageName = null) {
		view::main()->assign('gift', $gift);
		view::main()->assign('inventory', $gift->getInventory());
		$pinDisplay = view::main()->fetch('string:' . $pinDisplayString);
		return ($pageName == self::PAGE_VOUCHER)
			? preg_replace('/(\/barcode\/display\/recipientGuid\/[^"]+)/', '\\1/pinInBarcode/1', $pinDisplay)
			: $pinDisplay;
	}

}
