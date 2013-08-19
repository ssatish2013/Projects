<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class DesignTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('design'));
	}
	
	public function testLoadAllByPartner(){
		globals::partner('DesignTesting');
		
		$design1 = new designModel();
		$design1->partner = 'DesignTesting';
		$design1->status = 1;
		$design1->save();
		$design2 = new designModel();
		$design2->partner = 'DesignTesting';
		$design2->status = 1;
		$design2->save();
		$design3 = new designModel();
		$design3->partner = 'SubpartnerDesignTesting';
		$design3->status = 1;
		$design3->save();
		
		$designs = designModel::loadAllByPartner();		
		$this->assertEquals('2', count($designs));
		
		globals::subpartner('SubpartnerDesignTesting');
		
		$designs = designModel::loadAllByPartner();		
		$this->assertEquals('1', count($designs));
	}
}
