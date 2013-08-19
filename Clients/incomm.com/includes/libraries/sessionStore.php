<?php
class sessionStore{
	private static $self = null;
	private $savePath = null;
	private $sessionLength = null;

	public static function init(){

		if(self::$self===null){
			self::$self = new sessionStore();
		}

		self::$self->sessionLength = get_cfg_var("session.gc_maxlifetime");
		session_set_save_handler(
			array(self::$self,'open'),
			array(self::$self,'close'),
			array(self::$self,'read'),
			array(self::$self,'write'),
			array(self::$self,'destroy'),
			array(self::$self,'gc')
		);

		//everything is destroyed after the session has been written
		//so now we still have our db object!
		register_shutdown_function("session_write_close");
		session_start();
		if ((!array_key_exists ('referer', $_SESSION) || empty ($_SESSION['referer'])) && array_key_exists ('HTTP_REFERER', $_SERVER)) {
			if (!empty ($_SERVER['HTTP_REFERER']) && parse_url ($_SESSION['HTTP_REFERER'], PHP_URL_HOST) != (array_key_exists ('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : "")) {
				$_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
			}
		}
	}

	//called first when session_start() is called
	public function open($savePath,$sessionName){
		$this->savePath = $savePath;
		return true;
	}

	//called after write
	public function close(){
		return true;
	}

	//called after open.  Return "" if we cannot load a session
	public function read($guid){
		$session = new sessionModel();
		$session->guid = $guid;
		if($session->load()) { ;
			return $session->data;
		}
		else {
			return "";
		}
	}

	//called just before page destruction
	//save the session and update the expire time
	public function write($guid,$data){
		$session = new sessionModel();
		$session->guid = $guid;
		$session->load();

		$session->expires = date('Y-m-d H:i:s',strtotime('+'.$this->sessionLength.' seconds'));
		$session->data = $data;
		$session->save();
		return true;
	}

	//called only explicityly when session_destroy is called
	public function destroy($guid){
		$query = 'DELETE FROM `sessions` WHERE `guid`="'.db::escape($guid).'"'; 
		db::query($query);
		return true;
	}

	//never explicitly called
	//can potentially call it above after close or before read
	public function gc($maxLifetime){
		$query = 'DELETE FROM `sessions` WHERE `expires` <= "'.date('Y-m-d H:i:s').'" ' . 
		'LIMIT 50';
		db::query($query);
		return true;
	}

	//used by a cron to clean up old expired sessions
	public static function expire($num = 50) { 
		$query = 'DELETE FROM `sessions` WHERE `expires` <= "'.date('Y-m-d H:i:s').'" '.
		'LIMIT '.$num;
		db::query($query);
		return mysql_affected_rows();
	}

}
