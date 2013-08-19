<?php

class adminHelper {
	public static $sideBar;
	private static $sideBarOptions = array(
			"Designs"						=> "/admin/designs",
			"Language"					=> "/admin/language",
			"Settings"					=> "/admin/settings",
			"Screening"					=> "/admin/screening",
			"Inventory"					=> "/admin/inventory",
			"Dashboard"					=> "/admin/dashboard",
			"Email"							=> "/admin/email",
			"OptIn"						=> "/admin/optIn",
			"Gateway"			=> "/admin/gateway",
			"Fraud"							=> "/admin/fraud",
			"Reporting"					=> "/admin/reporting",
			"CustomerSupport"		=> "/admin/customerSupport",
			"Users"							=> "/admin/users",
			"Permissions"				=> "/admin/permissions",
			"FraudLogs"					=> "/admin/fraudLogs",
			"ResetYourPassword"	=> "/admin/passwordReset",
			"Monitoring"				=> "/admin/monitoring",
			"Migration"					=> "/admin/migration",
			"Api Logs"					=> "/admin/apiLogs",
			"HelpTextEditor"	=> "/admin/helpTextEditor",
			"TermsAndConditions"	=> "/admin/termsAndConditions",
			"Action Logs"				=> "/admin/actionLogs"
	);
	
	public static function getSideBarOptions( userModel $user ) {
		ksort(self::$sideBarOptions);
		foreach(self::$sideBarOptions as $key => $value){
			if($user->hasPermission('admin' . str_replace(" ", "", $key))){
				self::$sideBar[ utilityHelper::camelToSpace( $key )] = $value;
			}
		}
	}
	
	public static function arrayToTable($array, $header = ''){
		$tableString = '<table class="fraudLog">';
		if($header != '') { 
			$tableString .= "<thead><tr><th>$header</th></tr></thead>";
		}
		$tableString .= '<tbody>';
		foreach ($array as $key => $value) {
			if(is_array($value)){
				if($header != '') { $key = "$header.$key"; }
				$tableString .= adminHelper::arrayToTable($value, $key);
			} else if($header != '') {
				$tableString .= "<tr><td>$key</td><td>$value</td></tr>";
			}
			else { 
				$tableString .= '<table class="fraudLog"><tbody>' . 
												'<tr><td><strong>' . $key. ':</strong> ' . $value . '</td></tr>' .
												'</tbody></table>';
			}
		}
		$tableString .= '</tbody></table>';
		return $tableString;
	}
}
