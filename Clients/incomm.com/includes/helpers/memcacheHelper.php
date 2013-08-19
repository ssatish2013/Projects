<?php
class memcacheHelper{
	public static function get($key){
		return Env::memcache()->get(ENV::main()->envName() . '_' . $key);
	}
	
	public static function set($key,$value,$expires=null){
		if(Env::main()->getEnvType() == 'dev'){
			$expires = 1;
		} else if(is_null($expires)){
			$expires = 180;
		}
		Env::memcache()->set(Env::main()->envName() . '_' . $key, $value, null, $expires);
	}
}