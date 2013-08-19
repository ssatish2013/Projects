<?php

class recipientGuidModel extends recipientGuidDefinition{
	
	public function isExpired(){
		if($this->expires <= date("Y-m-d H:i:s")){
			return true;
		} else {
			return false;
		}
	}

	public function save(){
		if(!$this->expires){
			$this->expires = date("Y-m-d H:i:s", strtotime("+1 day"));
		}
		parent::save();
	}
	
	public static function getClaimLinkExpire(){
		//we have two settings related to claim email link expire
		//old setting recipientGuidValidFor is set to 1 day on production
		//new setting reminderFreq determin when the first reminder email goes out, 
		//we don't want the claim link expired in this reminder email.
		$expire = date("Y-m-d H:i:s", strtotime(settingModel::getSetting('recipientGuid', 'recipientGuidValidFor')));
		$freq = settingModel::getSetting('reminderEmail', 'reminderFreq');
		//give extra two hours, in case reminder email delayed.  
		$freq =  $freq +2;
		//return the latest date, just in case the reminder email frequency is set too high, i.e. on qa env is 1 hour.  
		$expire = max($expire,date("Y-m-d H:i:s", strtotime("+$freq hour")));
		return $expire;
	}
}
