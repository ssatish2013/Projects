<?php
class cssHelper{
	public static function urlGen($params){
		$url = "/css/build/".globals::partner()."-".$params['stylesheet'].".css";
		
		$file = basename($url, ".css");
		
		$md5 = memcacheHelper::get("md5css".$file);
		if($md5){
			$url.="?v=".$md5;
		} else {
			// We must've just updated the file, or it needs updating.... return the timestamp
			$url.="?v=".microtime(true);
		}
		
		return $url;
	}
}