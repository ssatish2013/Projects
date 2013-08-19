<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class LedgerAuditTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('ledgerAudit'));
	}

	public function testSetTimestamp(){
		$ledgerAudit = new ledgerAuditModel();
		$ledgerAudit->timestamp = date("Y-m-d H:i:s");
		$this->assertEmpty($ledgerAudit->timestamp);
	}
	
	public function testRemoveAudit(){
		$ledgerAudit = new ledgerAuditModel();
		$ledgerAudit->type = 'payment';
		$ledgerAudit->amount = '25';
		$ledgerAudit->shoppingCartId = 123;
		$ledgerAudit->currency = 'USD';
		$ledgerAudit->messageId = 123;
		$ledgerAudit->save();

		$this->assertGreaterThan(0,$ledgerAudit->id);
		
		$ledgerAudit->removeAudit();
		
		$deletedAudit = new ledgerAuditModel();
		$deletedAudit->id = $ledgerAudit->id;
		$this->assertFalse($deletedAudit->load());
		
		$didError = FALSE;
		try{
			$ledgerAudit->removeAudit();
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
	}
}
