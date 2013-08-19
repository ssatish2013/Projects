<?php
class barcodeHelper{
	public static function displayByRecipientGuid($guid, $activate=true, $pinInBarcode=false){
		
		Env::includeLibrary("barcode/BCGColor");
		Env::includeLibrary("barcode/BCGBarcode");
		Env::includeLibrary("barcode/BCGDrawing");
		Env::includeLibrary("barcode/BCGFont");
		
		$overload = request::get('inventory');
		
		try{
			if(!$overload){
				$recipientGuid = new recipientGuidModel();
				$recipientGuid->guid=$guid;

				if(!($recipientGuid->load())){
					throw new barcodeException("Unable to load recipient guid");
				}

				if($recipientGuid->isExpired()){
					throw new barcodeException("Recipient guid is expired");
				}

				$gift = new giftModel();
				$gift->id = $recipientGuid->giftId;
				if($gift->giftingMode==giftModel::MODE_VOUCHER){
					$activate=false;
				}

				if(!($gift->load())){
					throw new barcodeException("Unable to load gift");
				}

				if($activate){
					$inventory = $gift->getAndActivateInventory();
				} else {
					$inventory = $gift->getInactiveInventory();
				}
			} else {
				$inventory = json_decode($overload);
			}
			
			$barcodeSettings = settingModel::getPartnerSettings(null, 'barcode');
			
			$returnIm  = imagecreatetruecolor($barcodeSettings['imageWidth'],$barcodeSettings['imageHeight']);
			// Make white background color transparent
			$whiteBg = imagecolorallocate($returnIm, 255, 255, 255);
			imagefill($returnIm, 0, 0, $whiteBg);
			imagecolortransparent($returnIm, $whiteBg);
			
			if(! Env::includeLibrary('barcode/BCG'.$barcodeSettings['codeType'].'.barcode')){
				throw new barcodeException("Unable to load barcode library");
			}
			
			$color_white = new BCGColor(255, 255, 255);
			$color_black = new BCGColor(0, 0, 0);
			$codebar = 'BCG' .$barcodeSettings['codeType'];
			$code_generated = new $codebar();
			
			if(@$barcodeSettings['checksum']){
				$code_generated->setChecksum($barcodeSettings['checksum']);
			}
			
			$code_generated->setThickness($barcodeSettings['thickness']);
			$code_generated->setScale($barcodeSettings['resolution']);
			$code_generated->setForegroundColor($color_black);
			$code_generated->setFont(0); // no label, if we need one, we gen ourselves
			
			$code = self::barcodeString($inventory, $barcodeSettings);
			
			$code_generated->parse($code);
			$drawing = new BCGDrawing('', $color_white);
			$drawing->setBarcode($code_generated);
			$drawing->draw();
			
			// This is an ugly hack because apparently you cannot return an image up two levels?
			$bcIm = $drawing->get_im();
			
			imagecopy($returnIm,$bcIm,$barcodeSettings['codeX'],$barcodeSettings['codeY'],0,0,imagesx($bcIm),imagesy($bcIm));
			
			$textStrings = settingModel::getPartnerSettings(null, 'barcodeText');
			$textPositions = settingModel::getPartnerSettings(null, 'barcodeTextPosition');
			
			$keys = array_keys(array_intersect_key($textStrings, $textPositions));
			if (!$pinInBarcode) {
				$keys = array_filter($keys, function($var) {
					return ($var != 'pin');
				});
			}
			
			
			view::set('inventory',$inventory); // used for fetching string from DB below
			foreach($keys as $key){
				$position = $textPositions[$key];
				list($xpos,$ypos)=explode(',',$position);
				
				$string=view::main()->fetch('string:'.$textStrings[$key]);
				
				imagestring($returnIm, $barcodeSettings['font'], $xpos, $ypos, $string, imagecolorallocate($returnIm,0,0,0));
			}
			
			
			
		} catch (barcodeException $e){
			$returnIm = imagecreatetruecolor(imagefontwidth(5) * strlen($e->getMessage()),  imagefontheight(5));
			imagefill($returnIm,0,0,imagecolorallocate($returnIm, 255, 255, 255));
			imagestring($returnIm, 5, 0, 0, $e->getMessage(), imagecolorallocate($returnIm, 0, 0, 0));
		}
		
		header('Content-Type: image/png');
		imagepng($returnIm);
	}
	
	public static function barcodeString($inventory, $barcodeSettings){
		$codeSource = $barcodeSettings['codeSource'];

		$parts = explode('->',$codeSource);
		$code = $inventory;
		foreach($parts as $part){
			$code = $code->$part;
		}
		return $code;
	}
}