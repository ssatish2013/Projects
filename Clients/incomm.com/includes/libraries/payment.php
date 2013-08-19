<?php

class payment {

	public $plugin;
	public $paymentMethodId;
	
	public function loadPlugin($id = null, $paymentType = null) {
		if (isset($id)) {
			$paymentMethod = new paymentMethodModel($id);
		} else {
			$paymentMethod = new paymentMethodModel();
			$paymentMethod->partner = globals::partner();
			$paymentMethod->pluginName = is_null($paymentType)
				? request::unsignedPost('paymentType')
				: $paymentType;
			if (!$paymentMethod->load()) {
				throw new Exception('Sorry, could not load the payment method');
			}
		}
		$this->paymentMethodId = $paymentMethod->id;
		$this->plugin = new $paymentMethod->pluginName;
		$this->plugin->settings = $paymentMethod->settings;
	}
}
