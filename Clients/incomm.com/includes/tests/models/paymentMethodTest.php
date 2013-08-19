<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class PaymentMethodTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('paymentMethod'));
	}
	
	public function testSetSettings(){
		$paymentMethod = new paymentMethodModel();
		$obj = new stdClass();
		$obj->a = 'b';
		$obj->c = 'd';
		$paymentMethod->settings = $obj;
		$this->assertEquals($paymentMethod->settings->a, 'b');
		$this->assertEquals($paymentMethod->settings->c, 'd');
	}
}