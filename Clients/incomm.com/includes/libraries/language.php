<?php
class language {
	public static $key=array();
	protected static $_default = null;

	public static function init($partner = null, $language = 'en'){
		$safeLanguage = db::escape($language);

		if(!$partner){
			$partner = globals::partner();
		}

		$subpartner = globals::subpartner();
		if($subpartner){
			$query = "SELECT * FROM languages WHERE
								(partner='$partner' || partner='$subpartner' || partner IS NULL)
								AND (language is null || language = '$safeLanguage')
								ORDER BY name";
		} else {
			$query = "SELECT * FROM languages WHERE
								(partner='$partner' || partner IS NULL)
								AND (language is null || language = '$safeLanguage')
								ORDER BY name,language asc";
		}

		$result = db::memcacheMultiGet($query, 'partnerLanguage_' . $partner . '_' . $subpartner . '_' . $safeLanguage);

		$definedSubArr = array(); // Subpartner
		$definedArr = array(); // Items that are specifically designed on partner/language
		$defaultPhraseArr = array(); // Items that are defined by GroupCard for the language

		foreach ($result as $row) {
			if($row['partner'] == $subpartner && ($subpartner)){
				$definedSubArr[$row['name']] = $row;
			} else if($row['partner'] == $partner && ($partner)){
				$definedArr[$row['name']] = $row;
			} else {
				$defaultPhraseArr[$row['name']] = $row;
			}
		}

		$allKeys = array_merge(array_keys($definedSubArr),array_keys($definedArr),array_keys($defaultPhraseArr));
		foreach($allKeys as $key){
			if(isset($definedSubArr[$key])){
				self::$key[$key]=$definedSubArr[$key];
			} else if(isset($definedArr[$key])){
				self::$key[$key]=$definedArr[$key];
			} else if(isset($defaultPhraseArr[$key])){
				self::$key[$key]=$defaultPhraseArr[$key];
			}
		}
	}

	/**
	 * Get i18n language name for multilingual support
	 *
	 * @access public
	 * @param mixed $lang string or null depends on input value
	 * @return string
	 * @static
	 */
	public static function lang($lang = null) {
		// $lang is $_GET['language'] passed from globals::lang()
		// i18n language is set by user from web page, so we add it
		// to session
		if (isset($lang)) {
			$_SESSION['language'] = db::escape($lang);
		}
		// By default, if there is no any i18n language set, then grab
		// default language setting from database or memcache
		elseif (!isset($_SESSION['language'])) {
			$_SESSION['language'] = self::_default();
		}

		return $_SESSION['language'];
	}

	/**
	 * Get default i18n language name
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getDefault() {
		return self::_default();
	}

	/**
	 * Get i18n default language nane from database or memcache
	 *
	 * If database value is not available then return 'en'
	 *
	 * @access protected
	 * @return string
	 * @static
	 */
	protected static function _default() {
		if (!isset(self::$_default)) {
			$ui = settingModel::getPartnerSettings(globals::partner(), 'ui');
			self::$_default = isset($ui['defaultLanguage']) ? $ui['defaultLanguage'] : 'en';
		}

		return self::$_default;
	}
}
