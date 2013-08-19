<?php

class countriesHelper {
	/**
	 * Return a list of countries taking the partners ui->billingCountries into consideration.
	 */
	public static function getCountries() {
		$uiSettings = settingModel::getPartnerSettings(globals::partner(), 'ui');
		if (isset($uiSettings['billingCountries']) && $uiSettings['billingCountries'] != '') {
			$countryCodes = explode(',', $uiSettings['billingCountries']);
			$countries = array();
			foreach ($countryCodes as $code) {
				$code = trim($code);
				$countries[$code] = constantsHelper::$countries[$code];
			}
			if ($countries == self::getTopCountries ()) {
				$countries = array ();
			}
		} else {
			$countries = constantsHelper::$countries;
		}
		return $countries;
	}
	
	public static function getTopCountries () {
		$uiSettings = settingModel::getPartnerSettings(globals::partner(), 'ui');
		if (isset($uiSettings['billingCountries']) && $uiSettings['billingCountries'] != '') {
			$countryCodes = explode(',', $uiSettings['billingCountries']);	
			$countries = array();
			foreach ($countryCodes as $code) {
				$code = trim($code);
				$countries[$code] = constantsHelper::$countries[$code];
			}
		} else {
			$countries = array (
				'US' => "United States",
				'CA' => "Canada",
				'GB' => "United Kingdom"
			);
		}
		return $countries;
	}

	/**
	 * Return a list of states taking the partners ui->billing countries into consideration (if US is one of them)
	 */
	public static function getStates() {
		return constantsHelper::$states;
	}
	
	/**
	 * Return a list of provinces taking the partners ui->billing countries into consideration (if CA is one of them)
	 */
	public static function getProvinces() {
		return constantsHelper::$provinces[globals::lang()];
	}
}
