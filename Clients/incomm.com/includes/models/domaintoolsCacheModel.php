<?php
class domaintoolsCacheModel extends domaintoolsCacheDefinition {
	const SECONDS_IN_DAY = 86400;
	
	private static function signRequest ($uri) {
        return hash_hmac('md5', self::getUsername () . self::signTimestamp () . $uri, self::getKey ());
	}
	
	private static function signTimestamp () {
		static $time;
		if (is_null ($time)) {
			$time = gmdate("Y-m-d\TH:i:s\Z");
		}
		return $time;
	}
	
	private static function getHost () {
		return self::getAPISettings ('apiHost');
	}
	
	private static function getUsername () {
		return self::getAPISettings ('apiUser');
	}
	
	private static function getKey () {
		return self::getAPISettings ('apiKey');
	}
	
	private static function getAPISettings ($key = NULL) {
		static $settings;
		if (is_null ($settings)) {
			$settings = settingModel::getPartnerSettings (globals::partner (), "domaintoolsConfig");
		}
		return is_null ($key) ? $settings : $settings[$key];
	}
	
	private static function getDomain ($domain) {
		$uri = '/v1/' . $domain . '/';
		$api = 'http://' . self::getHost () . $uri . '?api_username=' . self::getUsername () . '&signature=' . self::signRequest ($uri) . '&timestamp=' . self::signTimestamp ();
		return json_decode (file_get_contents ($api));
	}
	
	public static function getData ($domain) {
		// Grab the cached domain tools response if one exists
		$query = "SELECT `id`,`registrant`,`registrantCount`,`registered`,`expires` FROM `domaintoolsCaches` WHERE `domain`='" . mysql_real_escape_string ($domain) . "' AND `created` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()";
		$result = db::memcacheGet($query, globals::partner() . '_' . globals::subpartner() . '_domain_' . $domain);
		// check if there is a result in the database, otherwise query domaintools and cache the result
		if(array_key_exists ('registrant', $result) && !empty ($result['registrant'])) {
			return $result;
		} else {
			// query DomainTools
			try {
				$dt = self::getDomain ($domain);
				$dt = $dt->response;
				$dtCache = new domaintoolsCacheModel ();
				$dtCache->domain = $domain;
				$dtCache->registrant = $dt->registrant->name;
				$dtCache->registrantCount = $dt->registrant->domains;
				$dtCache->registered = $dt->registration->created;
				$dtCache->expires = $dt->registration->expires;
				$dtCache->save ();
				return array (
					'registrant' => (string) $dt->registrant->name,
					'registrantCount' => (int) $dt->registrant->domains,
					'registered' => (string) $dt->registration->created,
					'expires' => (string) $dt->registration->expires
				);
			} catch (exception $e) {
				// might be good to throw the error to the log here
			}
		}
		return array ('registrant' => 'UNKNOWN', 'registrantCount' => 0, 'created' => date ("Y-m-d H:i:s", 0), 'expires' => date ("Y-m-d H:i:s", 0));
	}
	public static function getRegistrant ($domain) {
		$data = self::getData ($domain);
		return $data['registrant'];
	}
	public static function getRegistrantCount ($domain) {
		$data = self::getData ($domain);
		return $data['registrantCount'];
	}
	public static function getAge ($domain) {
		$data = self::getData ($domain);
		return floor ((time () - strtotime ($data['registered'])) / self::SECONDS_IN_DAY);
	}
	public static function getExpires ($domain) {
		$data = self::getData ($domain);
		return floor ((strtotime ($data['expires']) - time ()) / self::SECONDS_IN_DAY);
	}
	public static function getDataID ($domain) {
		$data = self::getData ($domain);
		if (!array_key_exists ('id', $data)) {
			$data = self::getData ($domain); // that was a fresh copy, get one with an ID (cached)
		}
		return array_key_exists ('id', $data) ? $data['id'] : 0;
	}
}
