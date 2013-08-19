<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class ShoppingCartTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('shoppingCart'));
	}
	
	public function testAssignCurrencyFromMessage(){
		$shoppingCart = new shoppingCartModel();
		$message = new messageModel();
		
		$didError = FALSE;
		try{
			$shoppingCart->assignCurrencyFromMessage($message);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
		
		$message->currency = 'USD';
		$shoppingCart->assignCurrencyFromMessage($message);
		
		$this->assertEquals($shoppingCart->currency, $message->currency);
		
		$message->currency = 'GBP';

		$didError = FALSE;
		try{
			$shoppingCart->assignCurrencyFromMessage($message);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
	}
}