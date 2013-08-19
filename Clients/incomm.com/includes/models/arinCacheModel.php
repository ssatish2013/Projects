<?php

class arinCacheModel extends arinCacheDefinition {
	public static function getData ($ip = NULL) {
		if (is_null ($ip)) $ip = $_SERVER['HTTP_X_REAL_IP'];

		// Build the number that maxmind uses to lookup

		$ipNumber = self::ipToInt ($ip);

		//Grab the country for the number we generated

		$query = "SELECT `id`,`orgname`,`orghandle` FROM `arinCaches` WHERE " . $ipNumber . " >= `begin_num` AND " . $ipNumber . " <= `end_num` AND `created` BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()";

		$result = db::memcacheGet($query, globals::partner() . '_' . globals::subpartner() . '_ipaddress_' . $ipNumber);
		// check if there is a result in the database, otherwise query ARIN and cache the result
		if(array_key_exists ('orgname', $result) && !empty ($result['orgname'])) {
			return $result;
		} else {
			// query ARIN
			try {
				$arin = simplexml_load_string (file_get_contents ("http://whois.arin.net/rest/ip/" . urlencode ($ip)));
				$org = $arin->orgRef->attributes ();
				foreach ($arin->netBlocks->netBlock as $block) {
					$arinCache = new arinCacheModel ();
					$arinCache->begin_ip = (string) $block->startAddress;
					$arinCache->end_ip = (string) $block->endAddress;
					$arinCache->begin_num = self::ipToInt ((string) $block->startAddress);
					$arinCache->end_num = self::ipToInt ((string) $block->endAddress);
					$arinCache->orgname = (string) $org['name'];
					$arinCache->orghandle = (string) $org['handle'];
					$arinCache->save ();
				}
				return array ('orgname' => (string) $org['name'], 'orghandle' => (string) $org['handle']);
			} catch (exception $e) {
				// might be good to throw the error to the log here
			}
		}
		return array ('orgname' => 'UNKNOWN', 'orghandle' => 'UNKNOWN');
	}
	public static function getOrgName ($ip = NULL) {
		$data = self::getData ($ip);
		return $data['orgname'];
	}
	public static function getOrgHandle ($ip = NULL) {
		$data = self::getData ($ip);
		return $data['orghandle'];
	}
	public static function getDataID ($ip = NULL) {
		$data = self::getData ($ip);
		if (!array_key_exists ('id', $data)) {
			$data = self::getData ($ip); // that was a fresh copy, get one with an ID (cached)
		}
		return array_key_exists ('id', $data) ? $data['id'] : 0;
	}
}
