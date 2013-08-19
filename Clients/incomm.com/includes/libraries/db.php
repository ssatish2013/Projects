<?php
class db {

	public static $unitTestDB = null;
	
	static function query($sql, $useMaster = true) {

		if(self::$unitTestDB){
			$rs = mysql_query($sql, self::$unitTestDB);
		}elseif($useMaster){
			$rs = mysql_query($sql, Env::masterDbConn());
		} else {
			$rs = mysql_query($sql, Env::slaveDbConn());
		}
		if(mysql_errno()) { 
			$errNo = mysql_errno();			
			switch($errNo){
				
				
				case 2006: // If the link dies, reestablish it
					if($useMaster){
						Env::getNewMasterDbConn();
					} else {
						Env::getNewSlaveDbConn();
					}
					return self::query($sql, $useMaster);
				break;
				
				
				default: // In all other cases, throw an exception
					throw new Exception('SQL Error:' . $errNo. ' - ' . mysql_error() . ' SQL: ' . $sql);
					
					
			}
		}

		return $rs;
	}
	
	static function begin() {
		self::query("begin");
	}
	
	static function rollback() {
		self::query("rollback");
	}
	
	static function commit() {
		self::query("commit");
	}

	static function escape($string) {
		return mysql_real_escape_string($string,Env::masterDbConn());
	}

	static function memcacheGet($sql, $key) {
		$result = memcacheHelper::get($key);
		if(!$result || self::$unitTestDB){
			$rs = self::query($sql);
			$result = mysql_fetch_assoc($rs);
			if($result){
				memcacheHelper::set($key,$result);
			}
		}
		return $result;
	}

	static function memcacheMultiGet($sql, $key) {
		/*
		 * This does not return a mysql result object!
		 * It returns an array with all the rows
		 */
		$result = memcacheHelper::get($key);
		if(!$result || self::$unitTestDB){
			$rs = self::query($sql);
			$result = array();
			while($i = mysql_fetch_assoc($rs)){
				$result[] = $i;
			}
			if(count($result) > 0){
				memcacheHelper::set($key, $result);
			}
		}
		return $result;
	}
}
