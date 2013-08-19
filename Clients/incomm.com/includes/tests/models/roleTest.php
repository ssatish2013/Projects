<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class RoleTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('design'));
	}

	public function testFailLoad() { 
		$role = new roleModel();
		$this->assertFalse($role->hasPermission('thispermissiondoesnotexist'), 'fail to load permission in role model');
	}
}
