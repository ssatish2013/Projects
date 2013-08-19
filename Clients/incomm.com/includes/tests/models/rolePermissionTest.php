<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class rolePermissionTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('rolePermission'));
	}

	public function testCreate() { 
		$rolePerm = new rolePermissionModel();
		$rolePerm->value = 1;
		$rolePerm->save();
		$this->assertTrue($rolePerm->value);
	}
}
