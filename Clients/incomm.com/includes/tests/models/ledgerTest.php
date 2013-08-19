<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class LedgerTest extends PHPUnit_Framework_TestCase {

	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('ledger'));
	}

	public function testSetTimestamp(){
		$ledger = new ledgerModel();
		$ledger->timestamp = date("Y-m-d H:i:s");
		$this->assertEmpty($ledger->timestamp);
	}

	public function testUpdate(){
		$ledger = new ledgerModel();
		$ledger->type = 'payment';
		$ledger->shoppingCartId = 123;
		$ledger->currency = 'USD';
		$ledger->messageId = 123;
		$ledger->startAudit();
		$ledger->save();

		$didError = FALSE;
		try{
			$ledger->type = 'paymentFee';
			$ledger->save();
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
		
		$didError = FALSE;
		try{
			$ledger->type = 'BlablaBlabla';
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
	}
}
