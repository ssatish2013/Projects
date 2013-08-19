<?php
class langController {
	public function get() {
		$strings = request::get("strings");
		$return = array();
		if (!is_array ($strings)) $strings = array ($strings);
		foreach ($strings as $string) {
			$val = languageModel::getString ($string);
			if (!empty ($val))
				$return[$string] = $val;
		}
		echo json_encode ($return);
	}
}