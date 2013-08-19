<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class UserTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('user'));
	}
	
	public function testGetUserByEmail(){
		$user = new userModel();
		$user->getUserByEmail('testGetUserByEmail@groupcard.com');
		$this->assertGreaterThan(0, $user->id);
		
		$user2 = new userModel();
		$user2->getUserByEmail('testGetUserByEmail@groupcard.com');
		
		$this->assertEquals($user->id, $user2->id);
	}
}
