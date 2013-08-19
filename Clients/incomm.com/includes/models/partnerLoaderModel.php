<?php
class partnerLoaderModel extends partnerLoaderDefinition {

	const TYPE_FACEBOOK = 'facebook';
	const TYPE_DOMAIN = 'domain';
	const TYPE_PROMOTION = 'promotion';
	const TYPE_SUBSITE = 'subsite';

	public static function getPartner($type, $value) {

		//make our query
		$query = 'SELECT `partner` FROM `partnerLoaders` WHERE ' . 
		'`type`="'.db::escape($type).'" AND ' .
		'`value`="'.db::escape($value).'"';
		$result = db::memcacheGet($query, 'partnerLoader'.ucfirst($type).ucfirst($value));

		//if we didn't find a partner
		if($result === FALSE) { 
			return FALSE;
		}

		//otherwise load up the partner
		$partner = $result['partner'];
		return $partner;
	}
	
	public static function getAllPartners() { 
		//make our query
		$query = 'SELECT DISTINCT `partner` FROM `partnerLoaders` ORDER BY `partner` ASC';
		$result = db::memcacheMultiGet($query, 'allPartners');
		return $result;

	}
}
