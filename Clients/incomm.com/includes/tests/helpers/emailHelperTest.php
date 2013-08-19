<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class EmailHelperTest extends PHPUnit_Framework_TestCase {

	public function testGoodAddresses(){
		$this->assertTrue(emailHelper::isValidEmail('matt@groupcard.com'));
		$this->assertTrue(emailHelper::isValidEmail('matt@groupcard.co.uk'));
		$this->assertTrue(emailHelper::isValidEmail('matt42@groupcard.com'));
		$this->assertTrue(emailHelper::isValidEmail('matt+spam@groupcard.com'));
		$this->assertTrue(emailHelper::isValidEmail('reallyreallyreallyreallyreallyreallylongname@groupcard.com'));
		$this->assertTrue(emailHelper::isValidEmail('matt@reallyreallyreallyreallyreallyreallylongdomain.com'));
		$this->assertTrue(emailHelper::isValidEmail('matt@g.r.o.u.p.c.a.r.d.com'));
		$this->assertTrue(emailHelper::isValidEmail('matt@192.168.0.1'));
	}
	
	public function testBadAddresses(){
		$this->assertFalse(emailHelper::isValidEmail('@groupcard.com'));
		$this->assertFalse(emailHelper::isValidEmail('matt.com'));
		$this->assertFalse(emailHelper::isValidEmail('matt@groupcard@groupcard.com'));
		$this->assertFalse(emailHelper::isValidEmail('matt@groupcard@groupcard.777'));
		$this->assertFalse(emailHelper::isValidEmail(''));
	}
	
	public function testStripEmailCharacters(){
		$this->assertEquals("heyo", emailHelper::stripEmailCharacters("hey\no"));
		$this->assertEquals("heyo", emailHelper::stripEmailCharacters("hey\ro"));
		$this->assertEquals('hey\"o\"', emailHelper::stripEmailCharacters('hey"o"'));
	}
	
	public function testStripNewLineCharacters(){
		$this->assertEquals("heyo", emailHelper::stripNewLineCharacters("hey\no"));
		$this->assertEquals("heyo", emailHelper::stripNewLineCharacters("hey\ro"));
	}
}