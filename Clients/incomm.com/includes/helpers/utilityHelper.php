<?php
class utilityHelper{

	static public function stripNonIntegers($number){
		$number = str_split($number);
		$returnValue = '';
		foreach($number as $char){
			if(is_numeric($char)){
				$returnValue .= $char;
			}
		}
		return $returnValue;
	}
    static public function camelToSpace( $string ) {
        return preg_replace('/([A-Z])/', " $1", $string);
    }

    static public function slashToCamel( $string ) {
        return preg_replace_callback('/(\/)([A-Za-z])/', function( $matches ) {
           return strtoupper( $matches[2] );
        }, $string);
    }
	
	public static function isGetRequest(){
		return isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] == 'GET';
	}

	public static function getRequestType() {
		if ( isset( $_SERVER['REQUEST_METHOD'] )) {
			return $_SERVER['REQUEST_METHOD'];
		}
	}
	
	public static function getCreditCardType($ccNumber) {
		$card_type = false;
		$card_regexes = array(
			"/^4\d{12}(\d\d\d){0,1}$/" => "VISA",
			"/^5[12345]\d{14}$/"       => "MASTERCARD",
			"/^3[47]\d{13}$/"          => "AMEX",
			"/^6011\d{12}$/"           => "DISCOVER",
			"/^30[012345]\d{11}$/"     => "DINERS",
			"/^3[68]\d{12}$/"          => "DINERS",
		);
		
		foreach ($card_regexes as $regex => $type) {
			if (preg_match($regex, $ccNumber)) {
				$card_type = $type;
				break;
			}
		}
		return $card_type;
	}
	
	public static function printToPage($arr){
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
	}

	public static function isAssocArray($arr) { 
		return (is_array($arr) && count(array_filter(array_keys($arr),'is_string')) == count($arr));
	}
	
	public static function dirListing($dir){
		$retval = array();

		// add trailing slash if missing
		if(substr($dir, -1) != "/") $dir .= "/";

		// open pointer to directory and read list of files
		$d = @dir($dir) or die("getFileList: Failed opening directory $dir for reading");
		while(false !== ($entry = $d->read())) {
			// skip hidden files
			if($entry[0] == ".") continue;
			if(!is_dir("$dir$entry") && is_readable("$dir$entry")) {
				$retval[] = "$dir$entry";
			}
		}
		$d->close();
		return $retval;
	}
	public static function isAjax(){
		return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest";
	}
	
	public static function currencyToSymbol( $currency = 'USD' ) {
		$returnVal = '';
		switch ($currency){ 
			case 'USD':
			case 'CAD':
			case 'AUD':
			case 'NZD':
				$returnVal = '$';	// United States, Canadian, Australian, New Zealand dollar
				break;
			case 'JPY':
			case 'CNY':
				$returnVal = '&yen;';	// Japanese Yen, Chinese Yuan
				break;
			case 'INR':
				$returnVal = '&#8377;';	// Indian Rupee
				break;
			case 'CHF':
				$returnVal = '&#8355;';	// Swiss Franc
				break;
			case 'SEK':
			case 'DKK':
			case 'NOK':
				$returnVal = 'kr';	// Swedish, Danish, Norwegian krone
				break;
			case 'GBP':
				$returnVal = '&pound;';	// Great Britain pound
				break;
			case 'EUR':
				$returnVal = '&euro;';	// Euros
				break;
			case 'satish':
				$returnVal = 'SAT';	// Satish money
				break;
			default :
				$returnVal = '$';	// Default Currency
				break;
		}
		return $returnVal;
	}
	
	public static function arrayRecursiveDiff($aArray1, $aArray2) {
		$aReturn = array();

		foreach ($aArray1 as $mKey => $mValue) {
			if ($aArray2 && array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					$aRecursiveDiff = utilityHelper::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
				if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
				} else {
					if ($mValue != $aArray2[$mKey]) {
						$aReturn[$mKey] = $mValue;
					}
				}
			} else {
				$aReturn[$mKey] = $mValue;
			}
		}
		return $aReturn;
	}
}
