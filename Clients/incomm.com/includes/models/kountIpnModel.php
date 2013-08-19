<?php 

class kountIpnModel extends kountIpnDefinition {

	public static function addEvent($event) { 
		$ipn = new kountIpnModel();
		$ipn->event = (string) $event->name;
		$ipn->key = self::vipEmailEditsToUppercase((string) $event->key, $ipn->event);
		$ipn->oldValue = (string) $event->old_value;
		$ipn->newValue = (string) $event->new_value;
		$ipn->agent = (string) $event->agent;
		$ipn->occurred= self::dateTimeStringToUtc((string) $event->occurred);
		$ipn->orderNumber = $event->key->attributes()->order_number;
		$ipn->data = $event->asXml();
		$ipn->save();
	}
	
	protected static function vipEmailEditsToUppercase($key, $eventName) {
		if(in_array($eventName, array("DMC_EMAIL_ADD", "DMC_EMAIL_EDIT", "DMC_EMAIL_DELETE")))
			return strtoupper($key);
		else return $key;
	}
	
	protected static function dateTimeStringToUtc($dateTimeString, $dateTimeZoneName="America/Vancouver", $outputFormat="Y-m-d H:i:s") {
		$utcTz = new DateTimeZone("UTC");
		$inputTz = new DateTimeZone($dateTimeZoneName);
		$date = new DateTime($dateTimeString, $inputTz);
		$date->setTimeZone($utcTz);
		return $date->format($outputFormat);
	}
}
