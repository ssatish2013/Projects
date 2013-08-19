<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class LanguageTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('language'));
	}
	
	public function testdefault(){
		$lang = new languageModel();
		$lang->name = 'testlang';
		$lang->value = 'blabla';
		$lang->language = 'en';
		$lang->status = 1;
		$lang->save();
		
		language::init('langTestPartner', 'en');
		$this->assertEquals('blabla', language::$key['testlang']['value']);
		
	}
	
	public function testPartnerLang(){
		$lang = new languageModel();
		$lang->name = 'testlang';
		$lang->partner = 'langTestPartner';
		$lang->value = 'blablaPartner';
		$lang->language = 'en';
		$lang->status = 1;
		$lang->save();

		language::init('langTestPartner', 'en');
		$this->assertEquals('blablaPartner', language::$key['testlang']['value']);

	}
}