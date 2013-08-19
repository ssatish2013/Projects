<?php

class helpController {

	public static $defaultMethod = 'article';
	public static $helpContents = array(
			array('title'=>'giftingOption1','text'=>'giftingOption1Msg'),
			array('title'=>'giftingOption2','text'=>'giftingOption2Msg'),
			array('title'=>'giftingOption3','text'=>'giftingOption3Msg'),
			array('title'=>'giftingOption4','text'=>'giftingOption4Msg')
	);
	public static $helpTopics = array(
			giftModel::MODE_SINGLE=>array(
					'title'=>'singleGifting',
					'text'=>'singleGiftingMsg'
			),
			giftModel::MODE_GROUP=>array(
					'title'=>'groupGifting',
					'text'=>'groupGiftingMsg'
			),
			giftModel::MODE_SELF=>array(
					'title'=>'selfGifting',
					'text'=>'selfGiftingMsg'
			)/*,
			giftModel::MODE_VOUCHER=>array(
					'title'=>'voucherGifting',
					'text'=>'voucherGiftingMsg'
			)*/
	);

	public function article() {

		//All of our pages use randomly signed pages,
		//this is the only way to reliably get the article
		//every single time
		$article= $_REQUEST["article"];

		$help = helpArticleModel::getArticle($article, globals::lang());
		if($help) {
			echo json_encode($help);
		}
		else {
			echo json_encode( array());
		}
	}

	public function faq() {
		view::render('help/faq');
	}

	public function contact() {
		view::render('help/contact');
	}

	public function contactconfirmPost() {

		$from    = request::unsignedPost('contactFromName');
		$email   = request::unsignedPost('contactEmail');
		$subject = request::unsignedPost('contactSubject');
		$message = nl2br(request::unsignedPost('contactMessage'));
		$countArr = settingModel::getPartnerSettings(globals::partner(), 'contactSupport');
		$count = 0;
		
		$worker = new contactUsEmailWorker();
		
		foreach($countArr as $key => $value) {
			$recipientEmailKey = 'recipientEmail' . ($count + 1);
			
			if($key == $recipientEmailKey) { 
				$count++;
			}
		}
		
		for($i=1; $i<=$count; $i++) { 
			$recipientName	       = 'recipientName' . $i;
			$recipientEmail	       = 'recipientEmail' . $i;
			
			$msg['partner']	       = globals::partner();
			$msg['senderName']     = $from;
			$msg['senderEmail']    = $email;
			$msg['recipientName']  = $countArr[$recipientName];
			$msg['recipientEmail'] = $countArr[$recipientEmail];
			$msg['index']	       = $i;
			
			$msg['subject']	       = $subject;
			$msg['message']	       = $message;

			$worker->send(json_encode($msg));
		}
		
                view::Set('senderName', $from);
                view::Set('senderEmail', $email);
		view::render('help/contactconfirm');
	}

	public function redemptionTerms() {
		$pid = array_key_exists ("pid", $_REQUEST) ? $_REQUEST['pid'] : 0;
		if ($pid > 0) {
			$product = new productModel ();
			$product->id = $pid;
			$product->load ();
			$terms = $product->getDisplayTerms ();
		} else {
			$terms = languageModel::getString ("cardTerms");
		}
		view::set ('terms', $terms);
		view::render('help/redemptionTerms');
	}

	public function terms() {
		view::render('help/terms');
	}

	public function privacy(){
		view::render('help/privacy');
	}

	//alternative views for links in email footer
	public function _faq() {
		view::Redirect('gift','home',array('help'=>'openfaq'));
	}

	public function _contact() {
		view::Redirect('gift','home',array('help'=>'opencontactform'));
	}

	public function _terms() {
		view::Redirect('gift','home',array('help'=>'openterm'));
	}

	public function _redemptionTerms() {
		view::Redirect('gift','home',array('help'=>'redemption'));
    }

	public function _privacy(){
		view::Redirect('gift','home',array('help'=>'openprivacy'));
	}
}
