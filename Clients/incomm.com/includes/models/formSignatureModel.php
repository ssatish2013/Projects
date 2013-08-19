<?php
class formSignatureModel extends formSignatureDefinition{
	public function isValid(){
		if(strtotime($this->created) <= strtotime('-15 minutes')){
			return false;
		}
		
		if(!$this->used){
			$this->used=1;
			$this->save();
			return true;
		}
		return false;
	}
	
	public static function createSignature(){
		$sig = new formSignatureModel();
		$sig->sessionGuid = session_id();
		$sig->guid = randomHelper::guid(16);
		$sig->save();
		return $sig->guid;
	}
}