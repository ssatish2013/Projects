<?php
require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class PromoTriggerTest extends PHPUnit_Framework_TestCase {
	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('promoTrigger'));
	}

	public function testGetActiveTrigger(){
		$message = new messageModel();
		
		// with no active promo triggers, null should be returned
		$this->assertNull(promoTriggerModel::getActiveTrigger($message));

		$promo = new promoModel();
		$promo->discountPercent=10;
		$promo->maxBudget=20;
		$promo->maxUsesPerCC=5;
		$promo->maxUsesPerIP=5;
		$promo->maxUsesPerUser=5;
		$promo->minSpend=5;
		$promo->partner='triggerPartner';
		$promo->productLimited=1;
		$promo->status = 0;
		$promo->startDate=date('Y/m/d H:i:s',strtotime("-1 second"));
		$promo->stopDate=date('Y/m/d H:i:s',strtotime("+1 week"));
		$promo->save();

		

		$pt = new promoTriggerModel();
		$pt->partner='triggerPartner';
	}
}