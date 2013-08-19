<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class MessageTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('message'));
	}
	
	public function testValidateGiftCreate(){
		$message = new messageModel();
		$this->assertTrue($message->validateGiftCreate());
		$this->assertStringMatchesFormat('%s', $message->guid);
		$this->assertEquals($message->status, '0');
		
		//Add a setting and change the request so it tries to validate and fails
		$setting = new settingModel();
		$setting->partner = 'MessageTesting';
		$setting->category = 'testForm';
		$setting->key = 'validateMessageFromName';
		$setting->value = 1;
		$setting->save();
		
		$array = array('formName' => 'test');
		request::setUnsignedPost($array);
		
		globals::partner('MessageTesting');
		$this->assertFalse($message->validateGiftCreate());
	}
	
	public function testAssignToGift(){
		$message = new messageModel();
		$gift = new giftModel();
		
		$didError = FALSE;
		try{
			$message->assignToGift($gift);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);

		$gift->id = '1';
		$message->assignToGift($gift);
		$this->assertEquals($message->giftId, '1');
	}
	
	public function testAssignToShoppingCart(){
		$message = new messageModel();
		$shoppingCart = new shoppingCartModel();
		$message->currency = 'USD';
		$shoppingCart->currency = 'GBP';
		
		$didError = FALSE;
		try{
			$message->assignToShoppingCart($shoppingCart);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
		
		$shoppingCart->currency = 'USD';
		$message->assignToShoppingCart($shoppingCart);
		$this->assertEquals($shoppingCart->id, $message->shoppingCartId);
		
		unset($shoppingCart->id);
		$didError = FALSE;
		try{
			$message->assignToShoppingCart($shoppingCart);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
	}
	
	public function testAssignToUser(){
		$message = new messageModel();
		$user = new userModel();
		
		$didError = FALSE;
		try{
			$message->assignToUser($user);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
		
		$user->save();
		$message->assignToUser($user);
		
		$this->assertEquals($user->id, $message->userId);
		
	}
}
