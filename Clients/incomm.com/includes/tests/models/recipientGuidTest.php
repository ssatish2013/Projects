<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class RecipientGuidTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('recipientGuid'));
	}
	
	public function testIsExpired(){
		$rg = new recipientGuidModel();
		$rg->expires = date("Y-m-d H:i:s", strtotime("-1 day"));
		$this->assertTrue($rg->isExpired());
		
		$rg->expires = date("Y-m-d H:i:s", strtotime("+1 day"));
		$this->assertfalse($rg->isExpired());
	}
}