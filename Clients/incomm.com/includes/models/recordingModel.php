<?php
class recordingModel extends recordingDefinition{
	private $gift=null;
	
	public function getGift () {
		return $this->gift ?: ($this->gift = new giftModel ($this->giftId));
	}
}