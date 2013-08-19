<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class PartnerLoaderTest extends PHPUnit_Framework_TestCase {

	public static $acmeFacebookValue;
	public static $acmeDomainValue;

	/*** SETUP ***/
	//run before this class is instantiated
	public static function setUpBeforeClass() { 

		//setup partner acme loaders
		$loader = new partnerLoaderModel();
		$loader->type = partnerLoaderModel::TYPE_FACEBOOK;
		$loader->value = '4UTty3wsLaG3qPwo5E0LqCnQrzfzQBg00Q1einzB4';
		$loader->partner = 'acme';
		$loader->save();
		self::$acmeFacebookValue = $loader->value;

		//setup partner acme loaders
		$loader = new partnerLoaderModel();
		$loader->type = partnerLoaderModel::TYPE_DOMAIN;
		$loader->value = 'acme';
		$loader->partner = 'acme';
		$loader->save();
		self::$acmeDomainValue = $loader->value;

	}

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('partnerLoader'));
	}

	public function testConstants() { 

		//these shouldn't change, but if they do, we're in trouble.
		$this->assertEquals(partnerLoaderModel::TYPE_FACEBOOK, 'facebook');
		$this->assertEquals(partnerLoaderModel::TYPE_DOMAIN, 'domain');
	}

	public function testGetPartner() { 

		//facebook
		$partner = partnerLoaderModel::getPartner(partnerLoaderModel::TYPE_FACEBOOK, self::$acmeFacebookValue);
		$this->assertEquals('acme', $partner);

		//domain
		$partner = partnerLoaderModel::getPartner(partnerLoaderModel::TYPE_DOMAIN, self::$acmeDomainValue);
		$this->assertEquals('acme', $partner);

		//false
		$partner = partnerLoaderModel::getPartner('notype', 'novalue');
		$this->assertEquals(FALSE, $partner);
	}

	/*** TEARDOWN ***/
	public static function tearDownAfterClass() { 
		//do we need to delete anything? base model doesn't let us so we'd have
		//to do it from sql
	}
}
