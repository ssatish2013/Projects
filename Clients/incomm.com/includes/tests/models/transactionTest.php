<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class TransactionTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('transaction'));
	}
	
	public function testSetCreditCard(){
		$transaction = new transactionModel();
		$transaction->creditCard = '5105105105105100';
		$this->assertEquals($transaction->creditCard, '5105105105105100');
		$this->assertEquals($transaction->ccLastFour, '5100');
		$this->assertEquals($transaction->ccType, 'MASTERCARD');
	}
	
	public function testAssignPaymentMethod(){
		$paymentMethod = new paymentMethodModel();
		$paymentMethod->partner = 'testingPartner';
		$paymentMethod->pluginName = 'paypalPayment';
		$paymentMethod->save();
		
		$transaction = new transactionModel();
		
		$didError = FALSE;
		try{
			$transaction->assignPaymentMethod($paymentMethod);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
		
		$payment = new payment();
		$payment->loadPlugin($paymentMethod->id);
		
		$transaction->assignPaymentMethod($payment);
		$this->assertEquals($transaction->paymentMethodId, $paymentMethod->id);
	}
	
	public function testAssignShoppingCart(){
		$shoppingCart = new shoppingCartModel();
		unset($shoppingCart->id);
		
		$transaction = new transactionModel();
		
		$didError = FALSE;
		try{
			$transaction->assignShoppingCart($shoppingCart);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);

		$shoppingCart = new shoppingCartModel();
		$shoppingCart->currency = 'USD';

		$transaction->assignShoppingCart($shoppingCart);
		$this->assertEquals($transaction->currency, 'USD');
		$this->assertEquals($transaction->amount, '', 'Once shopping cart does amount we need to fix this test');
		$this->assertEquals($transaction->status, 0);
		$this->assertEquals($transaction->shoppingCartId, $shoppingCart->id);
	}
}
