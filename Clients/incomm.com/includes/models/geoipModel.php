<?php

class geoipModel extends geoipDefinition {
	public static function getData ($ip = NULL) {

		if (is_null ($ip)) $ip = $_SERVER['HTTP_X_REAL_IP'];

		// Build the number that maxmind uses to lookup

		$ipNumber = self::ipToInt ($ip);

		//Grab the country for the number we generated

		$query = "SELECT country FROM geoips WHERE $ipNumber >= begin_num AND $ipNumber <= end_num";

		$country = db::memcacheGet($query, globals::partner() . '_' . globals::subpartner() . '_ipaddress_' . $ipNumber);

		return $country;

	}
	public static function canView($ip = null){
		if(settingModel::getSetting('access', 'maintenance')){
			View::Render('access/maintenance');
			return false;
		}

		if(!$ip){
			$ip = $_SERVER['HTTP_X_REAL_IP'];
		}

		$whitelist = settingModel::getSetting('geoip', 'whitelist');
		$blacklist = settingModel::getSetting('geoip', 'blacklist');
		if(!isset($whitelist) && !isset($blacklist)){
			//There are no lists for this partner so lets just return
			return true;
		}



		$country = self::getData ($ip);

		//check if its in the white list
		if(isset($whitelist)){
			$whitelistArray = explode(',', $whitelist);
			if(in_array($country['country'], $whitelistArray)){
				return true;
			} else {
				View::Render('access/blocked');
				return false;
			}
		}

		//check if its in the blacklist
		if(isset($blacklist)){
			$blacklistArray = explode(',', $blacklist);
			if(in_array($country['country'], $blacklistArray)){
				View::Render('access/blocked');
				return false;
			} else {
				return true;
			}
		}
		return true;
	}
	//check if controller and method is exempted from geo location restriction.
	//put a list like "method1,method2,..." in value, then matched method is exempted from geo checking.
	//put a "*" in value, then any method from that controller is exempted.
	public static function isExempted($controller,$method){
		$result = false;
		$ex = settingModel::getSetting('exemptedMethods', $controller);
		if ($ex == '*') return true;
		$methods = explode(',',$ex);
		$result = strlen($method)>0 && in_array($method,$methods);

		return $result;
	}
}
