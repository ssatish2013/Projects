<?php
class productFeeModel extends productFeeDefinition{
	const FEE_AUDIO_FLAT = 'FEE_AUDIO_FLAT';
	const FEE_VIDEO_FLAT = 'FEE_VIDEO_FLAT';
	const FEE_SHIPPING_HANDLING_FLAT = 'FEE_SHIPPING_HANDLING_FLAT';
	const FEE_DELIVERY_FLAT = 'FEE_DELIVERY_FLAT';
	
	public static function getApplicableFees($productGroupId,$feeType=null){
		$loadby = array('productGroupId'=>$productGroupId,'status'=>1);
		if ($feeType) $loadby['feeType'] = $feeType;
		return productFeeModel::loadAll($loadby);
	}

	public function calcFeeAmount(MesssageModel $msg){
		$amount = 0;
		switch($this->feeType){
			//flat rates need no calculation, in future add formula here to 
			//calculate complex fee amount. e.g % fee, distance based fee etc.
			case self::FEE_AUDIO_FLAT:
				if (!empty($msg->recordingId)) $amount = $this->amount;
				break;
			case self::FEE_VIDEO_FLAT:
				if (!empty($msg->videoLink)) $amount = $this->amount;
				break;
			case self::FEE_SHIPPING_HANDLING_FLAT:
				//only charge S&H for the original message, not contribution messages.
				if ($msg->isContribution!=1 && $msg->getGift()->physicalDelivery==1) $amount = $this->amount;
				break;
			case self::FEE_DELIVERY_FLAT:
				$amount = $this->amount;
				break;
			default:
				log::warn('Unknown fee type, 0 returned. Fee Type:'.$this->feeType);
				break;
		} 
		return $amount;
	}
}