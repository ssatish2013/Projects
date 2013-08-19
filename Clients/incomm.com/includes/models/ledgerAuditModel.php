<?php
class ledgerAuditModel extends ledgerAuditDefinition{
	
	const removalExceptiomMessage = "Error removing the ledgerAudit row";
	
	public function _setTimestamp($value){
		return;
	}

	public function removeAudit(){
		$query = "DELETE FROM `ledgerAudits` WHERE `id` = '$this->id'";
		db::query($query);
		if(mysql_affected_rows() != 1){
			throw new Exception(self::removalExceptiomMessage);
		}
	}
}