<?php
class optInModel extends optInDefinition{
	public static function add($partner, $firstName, $lastName, $phone, $email) {
		log::info("Saving opt-in first name $firstName.");
		$oi = new optInModel();
		$oi->partner = $partner;
		$oi->firstName = $firstName;
		$oi->lastName = $lastName;
		$oi->email = $email;
		$oi->phone = $phone;
		try {
			$oi->save();
		} catch (exception $e) {
			return NULL;
		}
		return $oi;
	}
}