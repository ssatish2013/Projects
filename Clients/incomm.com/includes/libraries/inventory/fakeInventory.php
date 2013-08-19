<?php

class fakeInventory extends inventory {

	public function activate() {
		// If inventorys table has no inventory left for the product then
		// dynamically insert a new inventory record with randomly generated
		// PIN and PAN code
		$this->_generateNewInventoryRecord();
		// Reserve an unused inventory for the gift
		$inventory = $this->product->getReservation($this->gift)->getInventory();
		if ($inventory->activationTime) {
			return $inventory;
		}

		$promoLedgers = promoHelper::startLedgersForGift($this->gift, true);

		$ledger = new ledgerModel();
		$ledger->amount = $this->gift->paidAmount * -1;
		$ledger->giftId = $this->gift->id;
		$ledger->currency = $this->gift->currency;
		$ledger->type = ledgerModel::typeActivation;
		$ledger->startAudit();


		$inventory->activationTime = date("Y/m/d H:i:s");
		$inventory->activationMargin = $this->product->defaultMargin;
		$inventory->activationAmount = $this->gift->activationAmount;


		$feeLedger = new ledgerModel();
		$feeLedger->amount = $this->gift->paidAmount * ($inventory->activationMargin / 100);
		$feeLedger->giftId = $this->gift->id;
		$feeLedger->currency = $this->gift->currency;
		$feeLedger->type = ledgerModel::typeActivationFee;
		$feeLedger->startAudit();

		ledgerModel::saveAllLedgers($promoLedgers);
		$ledger->save();
		$feeLedger->save();
		$inventory->save();

		return $inventory;
	}

	public function getInactive() {
		// If inventorys table has no inventory left for the product then
		// dynamically insert a new inventory record with randomly generated
		// PIN and PAN code
		$this->_generateNewInventoryRecord();
		// Reserve an unused inventory for the gift
		$inventory = $this->product->getReservation($this->gift)->getInventory();
		if ($inventory->activationTime) {
			return $inventory;
		}

		$inventory->activationTime = date("Y/m/d H:i:s");
		$inventory->activationMargin = $this->product->defaultMargin;
		$inventory->activationAmount = $this->gift->activationAmount;

		$inventory->save();

		return $inventory;
	}

	public function deactivate() {
		$inventory = $this->product->getReservation($this->gift)->getInventory();
		if (!$inventory->activationTime || $inventory->deactivationTime) {
			return $inventory;
		}

		$redemption = new externalRedemptionModel();
		$redemption->inventoryId = $inventory->id;
		if ($redemption->load()) {
			throw new deactivationException("Cannot deactivate redeemed gift");
			return;
		}

		$promoLedgers = promoHelper::startLedgersForGift($this->gift, false);

		$ledger = new ledgerModel();
		$ledger->amount = $this->gift->paidAmount;
		$ledger->giftId = $this->gift->id;
		$ledger->currency = $this->gift->currency;
		$ledger->type = ledgerModel::typeDeactivation;
		$ledger->startAudit();


		$inventory->deactivationTime = date("Y/m/d H:i:s");


		$feeLedger = new ledgerModel();
		$feeLedger->amount = $this->gift->paidAmount * ($inventory->activationMargin / -100);
		$feeLedger->giftId = $this->gift->id;
		$feeLedger->currency = $this->gift->currency;
		$feeLedger->type = ledgerModel::typeDeactivationFee;
		$feeLedger->startAudit();

		ledgerModel::saveAllLedgers($promoLedgers);
		$ledger->save();
		$feeLedger->save();
		$inventory->save();

		return $inventory;
	}

	private function _generateNewInventoryRecord() {
		$inventorys = inventoryModel::loadAll(array(
			'productId' => $this->product->id,
			'giftId' => null
		), 1);
		// If inventorys table has no inventory left for the product then
		// dynamically insert a new inventory record with randomly generated
		// PIN and PAN code
		if (count($inventorys) == 0) {
			$pin = mt_rand(1000000, 9999999);
			$pan = substr(str_replace('.', '', (string)microtime(true)), 0, 14)
				. mt_rand(1000000, 9999999);

			$auxData = new stdClass();
			$auxData->PIN = $pin;
			$auxData->PAN = $pan;

			$inventory = new inventoryModel();
			$inventory->productId = $this->product->id;
			$inventory->pin = $pin;
			$inventory->pan = $pan;
			$inventory->auxData = $auxData;
			$inventory->activationMargin = $this->product->defaultMargin;
			$inventory->save();
			// Release memory
			unset($inventory);
		}
		// Release more memory
		unset($inventorys);
	}

}
