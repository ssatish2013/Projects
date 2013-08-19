<?php
class settingsHelper{
	private $_isCategory = false;
	private $_categoryName = null;
	
	public function __get($name){
		if(!$this->_isCategory){
			$newSelf = new self();
			$newSelf->_isCategory=true;
			$newSelf->_categoryName=$name;
			return $newSelf;
		}
		return settingModel::getSetting($this->_categoryName, $name);
	}
}