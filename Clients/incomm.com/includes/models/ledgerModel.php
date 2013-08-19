<?php
class ledgerModel extends ledgerDefinition{	
	const typePayment =										'payment';
	const typePaymentFee =								'paymentFee';
	const typeRefund =										'refund';
	const typeRefundFee =									'refundFee';
	const typeActivation =								'activation';
	const typeActivationFee =							'activationFee';
	const typeDeactivation =							'deactivation';
	const typeDeactivationFee =						'deactivationFee';
	const typeNdr =												'ndr';
	const typeNdrFee =										'ndrFee';
	const typeNdrPartnerFee =							'ndrPartnerFee';
	const typeChargebackHold =						'chargebackHold';
	const typeChargebackHoldReversal = 		'chargebackHoldReversal';
	const typeChargeback =								'chargeback';
	const typeChargebackFee =							'chargebackFee';
	const typeChargebackPartnerFee =			'chargebackPartnerFee';
	const typePromoActivation =						'promoActivation';
	const typePromoDeactivation =					'promoDeactivation';

	private $audit;
	
	public function _setType($type){
		$refl = new ReflectionClass('ledgerModel');
		$constants = array_keys($refl->getConstants());
		
		if(!in_array('type'.ucfirst($type),$constants)){
			throw new exception('Improper Type Value: '.ucfirst($type));
		}
		$this->type = $type;
	}

	public function _setTimestamp($value){
		return;
	}

	public function update(){
		throw new Exception('Sorry this cannot be updated.');
	}
	
	public function startAudit(){
		$audit = new ledgerAuditModel();
		$audit->giftId = $this->giftId;
		$audit->type = $this->type;
		$audit->shoppingCartId = $this->shoppingCartId;
		$audit->messageId = $this->messageId;
		$audit->amount = $this->amount;
		$audit->currency = $this->currency;
		$audit->reversalId = $this->reversalId;
		$audit->save();
		$this->audit = $audit;
	}
	
	public function save() {
		if(parent::save()){
			$this->audit->removeAudit();
		}
	}
	
	public static function removeLedger($ledger){
		$ledger->audit->removeAudit();
	}
	
	public static function saveAllLedgers($ledgersArray){
		foreach ($ledgersArray as $ledger) {
			/* @var $ledger ledgerModel */
			$ledger->save();
		}
	}
	
	public static function removeAllLedgers($ledgersArray){
		foreach ($ledgersArray as $ledger) {
			/* @var $ledger ledgerModel */
			$ledger->audit->removeAudit();
		}		
	}
}
