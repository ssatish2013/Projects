<?php
class traceAuditModel extends traceAuditDefinition{
	public function beforeRequest($requestFields){
		$this->requestFields = $requestFields;
		$this->requestMicroTimestamp = microtime(true);
		$this->requestReadableTimestamp = date("Y/m/d H:i:s");
		$this->save();
	}
	
	public function afterRequest($responseFields,$responseCode){
		$this->responseFields=$responseFields;
		$this->responseCode=$responseCode;
		if(strlen($responseCode)==2){
			$this->responseMicroTimestamp = microtime(true);
			$this->responseReadableTimestamp = date("Y/m/d H:i:s");
			$this->save();
		}
	}
}