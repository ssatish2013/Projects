<?php

class shippingOptionModel extends shippingOptionDefinition {
	public static function findDefaultOptionId() {
		$defaultShippingOption = settingModel::getSetting('physicalDelivery', 'defaultShippingOption');
		list($carrier, $serviceLevel) = explode(':', $defaultShippingOption);
		$shippingOption = new shippingOptionModel();
		$shippingOption->carrierKey = $carrier;
		$shippingOption->serviceLevelKey = $serviceLevel;
		if ($shippingOption->load()) {
			return $shippingOption->id;
		}
		return 0;
	}  //end findDefaultOptionId

}  //end shippingOptionModel
