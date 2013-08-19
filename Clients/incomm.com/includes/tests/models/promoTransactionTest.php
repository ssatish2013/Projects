<?php
require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class PromoTransactionTest extends PHPUnit_Framework_TestCase {
	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('promoTransaction'));
	}
}