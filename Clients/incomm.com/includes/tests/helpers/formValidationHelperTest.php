<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class FormValidationHelperTest extends PHPUnit_Framework_TestCase {

	/*
	 *  Most of these are just stubs, when we build the real helpers we need to update these.
	 */
	public function testValidateMessageAmount(){
		//message amount now requires product info. to validate.
		$product = new productModel();
		$product->minAmount = 5;
		$product->maxAmount = 50;
		$product->save();

		$array = array('messageAmount' => '10','giftProductId'=>$product->id);
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateMessageAmount());

		$array = array('messageAmount' => '0');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateMessageAmount());
	}

	public function testValidateMessageFromName(){
		$this->assertFalse(formValidationHelper::validateMessageFromName());

		$array = array('messageFromName' => 'Timmy');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateMessageFromName());
	}

	public function testValidateMessageFromEmail(){
		$this->assertFalse(formValidationHelper::validateMessageFromEmail());

		$array = array('messageFromEmail' => 'messageFromEmail@groupcard.com');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateMessageFromEmail());
	}


	public function testValidateMessageMessage(){
		$this->assertFalse(formValidationHelper::validateMessageMessage());

		$array = array('messageMessage' => 'The message');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateMessageMessage());
	}

	public function testValidateMessageCurrency(){
		$this->assertTrue(formValidationHelper::validateMessageCurrency());
	}

	public function testValidateGiftRecipientName(){
		$this->assertFalse(formValidationHelper::validateGiftRecipientName());

		$array = array('giftRecipientName' => 'Tommy');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateGiftRecipientName());
	}

	public function testValidateGiftRecipientEmail(){
		$array = array('giftRecipientEmail' => 'matt@groupcard.com');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateGiftRecipientEmail());

		$array = array('giftRecipientEmail' => 'matt.com');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateGiftRecipientEmail());
	}

	//public function testValidateGiftDeliveryDate(){
		//$this->assertFalse(formValidationHelper::validateGiftDeliveryDate());
		//$array = array('date_yyyy' => date ('Y'), 'date_mm' => date('m'), 'date_dd' => date('d'), 'currentTimeZone' => date_default_timezone_get());
		//request::setUnsignedPost($array);
		//$this->assertTrue(formValidationHelper::validateGiftDeliveryDate());
	//}

	public function testValidateGiftDesignId(){
		$this->assertFalse(formValidationHelper::validateGiftDesignId());
		//design now requires addtional setting to validate
		$design = new designModel();
		$design->partner = 'testingPartner';
		$design->status = 1;
		$design->save();
		//productGroups
		$group = new productGroupModel();
		$group->currency='USD';
		$group->partner ='testingPartner';
		$group->status = 1;
		$group->save();
		//product
		$product = new productModel();
		$product->fixedAmount = 10;
		$product->save();
		//productAndGroups
		$png = new productAndGroupModel();
		$png->productId = $product->id;
		$png->productGroupId = $group->id;
		$png->save();
		//designAndGroups
		$dng = new designAndGroupModel();
		$dng->designId = $design->id;
		$dng->productGroupId = $group->id;
		$dng->save();

		globals::partner('testingPartner');

		$array = array('giftDesignId' => $design->id,'messageCurrency'=>'USD');
		request::setUnsignedPost($array);

		$this->assertTrue(formValidationHelper::validateGiftDesignId());
	}

	public function testValidateGiftProductId(){
		$array = array('giftProductId' => -1);
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateGiftProductId());

		$product = new productModel();
		$product->save();
		$array = array('giftProductId' => $product->id);
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateGiftProductId(),"pid:$product->id");
	}

	public function testValidateGiftRecipientPhoneNumber(){
		// $this->assertFalse(formValidationHelper::validateGiftRecipientPhoneNumber());
		$array = array('giftRecipientPhoneNumber' => '123456789');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateGiftRecipientPhoneNumber());

		$array = array('giftRecipientPhoneNumber' => '123456789012');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateGiftRecipientPhoneNumber());

		$array = array('giftRecipientPhoneNumber' => '12345678911');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateGiftRecipientPhoneNumber());

		$array = array('giftRecipientPhoneNumber' => '1234567890');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateGiftRecipientPhoneNumber());
	}

	public function testValidateGiftRecipientFacebookId(){
		$this->assertTrue(formValidationHelper::validateGiftRecipientFacebookId());

		$array = array('giftDeliveryMethod' => giftModel::DELIVERY_SOCIAL, 'giftFacebookUID' => 0);
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateGiftRecipientFacebookId());
	}

	public function testValidateTransactionFirstName(){
		$this->assertFalse(formValidationHelper::validateTransactionFirstName());

		$array = array('transactionFirstName' => 'Matt');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionFirstName());
	}

	public function testValidateTransactionLastName(){
		$this->assertFalse(formValidationHelper::validateTransactionLastName());

		$array = array('transactionLastName' => 'Matt');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionLastName());
	}

	public function testValidateTransactionPhoneNumber(){
		$this->assertFalse(formValidationHelper::validatetransactionPhoneNumber());

		$array = array('transactionPhoneNumber' => '123456789');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validatetransactionPhoneNumber());

		$array = array('transactionPhoneNumber' => '123456789012');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validatetransactionPhoneNumber());

		$array = array('transactionPhoneNumber' => '12345678911');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validatetransactionPhoneNumber());

		$array = array('transactionPhoneNumber' => '1234567890');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validatetransactionPhoneNumber());
	}

	public function testValidateTransactionCreditCard(){
		$this->assertFalse(formValidationHelper::validateTransactionCreditCard());

		$array = array('transactionCreditCard' => '5105105105105100');
		request::setUnsignedPost($array);
		$this->assertEquals(formValidationHelper::validateTransactionCreditCard(), 'MASTERCARD');

		$array = array('transactionCreditCard' => '4111111111111111');
		request::setUnsignedPost($array);
		$this->assertEquals(formValidationHelper::validateTransactionCreditCard(), 'VISA');

		$array = array('transactionCreditCard' => '378282246310005');
		request::setUnsignedPost($array);
		$this->assertEquals(formValidationHelper::validateTransactionCreditCard(), 'AMEX');

		$array = array('transactionCreditCard' => '6011111111111117');
		request::setUnsignedPost($array);
		$this->assertEquals(formValidationHelper::validateTransactionCreditCard(), 'DISCOVER');

		$array = array('transactionCreditCard' => '38520000023237');
		request::setUnsignedPost($array);
		$this->assertEquals(formValidationHelper::validateTransactionCreditCard(), 'DINERS');
	}

	public function testValidateTransactionExpirationMonth(){
		$this->assertFalse(formValidationHelper::validateTransactionExpirationMonth());

		$array = array('transactionExpirationMonth' => '00');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateTransactionExpirationMonth());

		$array = array('transactionExpirationMonth' => '13');
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateTransactionExpirationMonth());

		$array = array('transactionExpirationMonth' => '12');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionExpirationMonth());
	}

	public function testValidateTransactionExpirationYear(){
		$this->assertFalse(formValidationHelper::validateTransactionExpirationYear());

		$array = array('transactionExpirationYear' => date("Y", strtotime("-1 year")));
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateTransactionExpirationYear());

		$array = array('transactionExpirationYear' => date("Y", strtotime("+9 years")));
		request::setUnsignedPost($array);
		$this->assertFalse(formValidationHelper::validateTransactionExpirationYear());

		$array = array('transactionExpirationYear' => date("Y"));
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionExpirationYear());

		$array = array('transactionExpirationYear' => date("Y", strtotime("+3 years")));
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionExpirationYear());
	}

	public function testValidateTransactionCvv(){
		$this->assertFalse(formValidationHelper::validateTransactionCvv());

		$array = array('transactionCvv' => '123', 'transactionCreditCard' => '5105105105105100');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionCvv());

		$array = array('transactionCvv' => '1234', 'transactionCreditCard' => '378282246310005');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionCvv());
	}

	public function testValidateTransactionAddress(){
		$this->assertFalse(formValidationHelper::validateTransactionAddress());

		$array = array('transactionAddress' => '123 main st');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionAddress());

	}

	public function testValidateTransactionAddress2(){
		$this->assertTrue(formValidationHelper::validateTransactionAddress2());
	}

	public function testValidateTransactionCity(){
		$this->assertFalse(formValidationHelper::validateTransactionCity());

		$array = array('transactionCity' => 'Miwlaukee');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionCity());
	}

	public function testValidateTransactionState(){
		$this->assertFalse(formValidationHelper::validateTransactionState());

		$array = array('transactionState' => 'WI');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionState());
	}

	public function testValidateTransactionProvince(){
		$this->assertFalse(formValidationHelper::validateTransactionProvince());

		$array = array('transactionProvince' => 'BC');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionProvince());
	}

	public function testValidateTransactionZip(){
		$this->assertFalse(formValidationHelper::validateTransactionZip());

		$array = array('transactionZip' => '55555');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionZip());
	}

	public function testValidationTransactionCountry(){
		$this->assertFalse(formValidationHelper::validateTransactionCountry());

		$array = array('transactionCountry' => 'US');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionCountry());
	}

	public function testValidationTransactionFromEmail(){
		$this->assertFalse(formValidationHelper::validateTransactionFromEmail());

		$array = array('transactionFromEmail' => 'messageFromEmail@groupcard.com');
		request::setUnsignedPost($array);
		$this->assertTrue(formValidationHelper::validateTransactionFromEmail());
	}

	public function testValidationUserEmail(){
		$this->assertFalse(formValidationHelper::validateUserEmail());

		$array = array('userEmail' => 'messageFromEmail@groupcard.com');
		request::setSignedPost($array);
		$this->assertTrue(formValidationHelper::validateUserEmail());
		request::removeValidatedFlag();
	}

	public function testValidationUserPassword(){
		$this->assertFalse(formValidationHelper::validateUserPassword());

		$array = array('userPassword' => 'heyo!');
		request::setSignedPost($array);
		$this->assertTrue(formValidationHelper::validateUserPassword());
		request::removeValidatedFlag();
	}

}
