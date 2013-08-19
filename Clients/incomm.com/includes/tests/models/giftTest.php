<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class GiftTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('gift'));
	}

	public function testGetAmount(){
		// test the empty values for get amount
		$gift = new giftModel();
		$this->assertEquals(0, $gift->unverifiedAmount);
		$this->assertEquals(0, $gift->paidAmount);
	}

	public function testPaidGetAmount(){
		// test amount stuff with paid transactions

		$gift = new giftModel();
		$gift->save();

		$shoppingCart1 = new shoppingCartModel();
		$shoppingCart1->isCurrent = 0;
		$shoppingCart1->save();
		$shoppingCart2 = new shoppingCartModel();
		$shoppingCart2->save();

		$message1 = new messageModel();
		$message2 = new messageModel();
		$message1->shoppingCartId = $shoppingCart1->id;
		$message1->giftId = $gift->id;
		$message1->amount = 10;
		$message2->shoppingCartId = $shoppingCart2->id;
		$message2->giftId = $gift->id;
		$message2->amount = 20;
		$message1->save();
		$message2->save();

		$transaction1 = new transactionModel();
		$transaction2 = new transactionModel();
		$transaction1->externalTransactionId = 'abc123';
		$transaction1->shoppingCartId = $shoppingCart1->id;
		$transaction1->ccType ='Paypal';

		$transaction2->shoppingCartId = $shoppingCart2->id;
		$transaction2->authorizationId = 'xyz';
		$transaction2->authorizationTime = date("Y-m-d H:i:s", strtotime('-1 minute'));

		$transaction1->save();
		$transaction2->save();

		// unverified amount is the paid + the authorized
		$this->assertEquals(20, $gift->unverifiedAmount);
		$this->assertEquals(0, $gift->paidAmount);
	}

	public function testValidateGiftCreate(){
		globals::partner('GiftTesting');
		$gift = new giftModel();

		$this->assertTrue($gift->validateGiftCreate());
		$this->assertStringMatchesFormat('%s', $gift->guid);
		$this->assertEquals($gift->partner, 'GiftTesting');

		//Add a setting and change the request so it tries to validate and fails
		$setting = new settingModel();
		$setting->partner = 'GiftTesting';
		$setting->category = 'testForm';
		$setting->key = 'validateGiftRecipientName';
		$setting->value = 1;
		$setting->save();

		$array = array('formName' => 'test');
		request::setUnsignedPost($array);

		$this->assertFalse($gift->validateGiftCreate());
	}

	public function testGetCurrentRecipientGuid(){
		$gift = new giftModel();
		$gift->save();
		$guid = $gift->getCurrentRecipientGuid();
		$guid2 = $gift->getCurrentRecipientGuid();
		$this->assertGreaterThan(0, strlen($guid));
		$this->assertEquals($guid, $guid2);

		$expired = new recipientGuidModel(array('guid' => $guid));
		$expired->expires = date("Y-m-d H:i:s", strtotime("-1 minute"));
		$expired->save();

		$newGuid = $gift->getCurrentRecipientGuid();
		$this->assertNotEquals($guid, $newGuid);
	}

	public function testIsFutureDeliveryDate(){
		$gift = new giftModel();
		$date = getdate(strtotime("-1 day"));
		$array = array('date_yyyy' => $date['year'], 'date_mm'=>$date['mon'], 'date_dd'=>$date['mday']);
		request::setUnsignedPost($array);
		//TODO: _setDeliveryDate ignores passed in argument at this moment, read from post instead.
		//this will be changed next phase
		$gift->_setDeliveryDate();
		$this->assertFalse($gift->isFutureDelivery());

		$date = getdate(strtotime("+1 day"));
		$array = array('date_yyyy' => $date['year'], 'date_mm'=>$date['mon'], 'date_dd'=>$date['mday']);
		request::setUnsignedPost($array);
		$gift->_setDeliveryDate();
		$this->assertTrue($gift->isFutureDelivery());
	}

	public function testAssignDeliveryMethod(){
		$gift = new giftModel();

		$array = array('giftDeliveryMethod' => 'email');
		request::setUnsignedPost($array);

		$gift->assignDeliveryMethod();
		$this->assertTrue($gift->emailDelivery);
		$this->assertFalse($gift->facebookDelivery);

		$array = array('giftDeliveryMethod' => 'social');
		request::setUnsignedPost($array);

		$gift->assignDeliveryMethod();
		$this->assertFalse($gift->emailDelivery);
		$this->assertTrue($gift->facebookDelivery);
	}

	public function testGetDesign(){
		$design = new designModel();
		$design->save();

		$gift = new giftModel();
		$gift->designId = $design->id;
		$newDesign = $gift->getDesign();

		$this->assertEquals($design->id, $newDesign->id);
	}
}