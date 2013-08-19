<?php
class languageModel extends languageDefinition {

	public static function getString( $key ){
		return view::main()->fetch('eval:'.language::$key[$key]['value']);
	}
 
	public static function smartyTemplate( $tpl_name, &$tpl_source, $smarty_obj ) {
		$tpl_source = language::$key[$tpl_name]['value']; // Surpress this in case it doesn't exist
		return true;
	}

	public static function smartyTimestamp( $tpl_name, &$tpl_timestamp, $smarty_obj  ) {
		//$tpl_timestamp = time();
		$tpl_timestamp = time()+5000;
		return true;
	}

	public static function smartySecure( $tpl_name, &$smarty_obj ) { return true; }

	public static function smartyTrusted( $tpl_name, &$smarty_obj ) {}

}
