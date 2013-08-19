<?php

class shippingDetailModel extends shippingDetailDefinition {
	public $shippingOption = null;

	public function loadShippingOption() {
		if (!isset($this->shippingOption)) {
			$this->shippingOption = new shippingOptionModel($this->shippingOptionId);
		}
	}  //end loadShippingOption

	public function validateGiftCreate($formType=null) {
		if (parent::validate($formType)) {
			$this->address2 = request::unsignedPost('shippingDetailAddress2')
				? request::unsignedPost('shippingDetailAddress2')
				: null;
			return true;
		} else {
			return false;
		}
	}  //end validateGiftCreate

}  //end shippingDetailModel
