<?php

class randomHelper {

	public static function guid( $length=12, $charset='abcdefghijkmnpqrstuvwxyz23456789' ) {
		$ret = '';
		for($i=0;$i<$length;$i++){
			$ret .= substr($charset,mt_rand(0,strlen($charset)-1),1);
		}

		return is_numeric( $ret ) ? call_user_func_array( array( $this, 'guid' ), func_get_args() ) : $ret;
	}
}
