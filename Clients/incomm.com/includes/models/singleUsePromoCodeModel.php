<?php
class singleUsePromoCodeModel extends singleUsePromoCodeDefinition{
	public function getFormatted(){
		return $this->prefix.$this->pin.$this->code;
	}
}