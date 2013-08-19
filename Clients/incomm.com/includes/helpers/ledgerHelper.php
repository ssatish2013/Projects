<?php

//*** NOTE *** 

class ledgerHelper {

	public static function addNdr($giftId) { 

		//first see if we have NDR's enabled for the partner
		$ndrPercentage = settingModel::getSetting('fees','ndrPercent');
		if(!isset($ndrPercentage) || $ndrPercentage == 0) { return; }

		//ok, doing a refund
		$gift = new giftModel($giftId);

		//if the gift has no amount on it, it was most likely
		//already refunded, so we don't want to log this in the ledger
		if($gift->paidAmount <= 0) { return; }

		//grab inventory
		$inventory = $gift->getInventory();

		//refund the main fee
    $ledger = new ledgerModel();
    $ledger->amount = $gift->paidAmount * $ndrPercentage;
    $ledger->giftId = $gift->id;
    $ledger->currency = $gift->currency;
    $ledger->type = ledgerModel::typeNdr;
    $ledger->startAudit();
    $ledger->save();


    //next see if they're paying us a percentage
    $ndrFeePercentage = settingModel::getSetting('fees','ndrFeePercent');
    if(!isset($ndrFeePercentage) || $ndrFeePercentage == 0) { return; }

    $feeLedger = new ledgerModel();
    $feeLedger->amount = $gift->paidAmount * ($inventory->activationMargin/-100) * $ndrFeePercentage;
    $feeLedger->giftId = $gift->id;
    $feeLedger->currency = $gift->currency;
    $feeLedger->type = ledgerModel::typeNdrFee;
    $feeLedger->startAudit();
    $feeLedger->save();


	}
}
