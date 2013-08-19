<?php

class encodingHelper {

  public static function urlSafeBase64Encode($string) {
    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($string));
  }

  public static function urlSafeBase64Decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
	}
}
