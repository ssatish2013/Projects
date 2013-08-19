<?php
class settingModel extends settingDefinition {

	private static $settings = array();

	/**********************
		PUBLIC METHODS
	**********************/
	public static function getCategorizedByPartnerForAdminToolOnly( $partner = null ) {

		$model = new settingModel();
		$categories = array();
		$partner = $partner ?: globals::partner();

		$query = "SELECT `" . implode("`, `", array_keys( $model->dbFields )) . "` FROM `settings`".
			" WHERE (`env`='".db::escape(ENV::main()->envName())."' || `env` IS NULL) AND ".
			"(`partner`='".db::escape($partner)."' || `partner` IS NULL) AND ".
			"`category` != 'productSettings'";
		
		$result = db::query( $query );

		while ( $setting = mysql_fetch_assoc( $result )) { 

			$category = $setting['category'];
			unset( $setting['category'] );

			$key = $setting['key'];
			unset( $setting['key'] );

			if ( $setting['encrypted'] ) {
				$setting['value'] = baseModel::decrypt( $setting['value'] );
			}

			if ( ! array_key_exists( $category, $categories ) ) {
				$categories[$category] = array();
			}

			if ( isset( $categories[ $category ][ $key ]  ) ) {
				if ( ! empty( $setting['env'] )  && ! empty( $setting['partner'] ) ) {
					$categories[ $category ][ $key ] = $setting;
				}
			} else {
				$categories[ $category ][ $key ] = $setting;
			}

		}

		return $categories;
	}

	public static function getPartnerSettings($partner = null, $category = null) {

		if(is_null($partner)||!$partner){
			$partner = globals::partner();
		}

		$subpartner = globals::subpartner();
		
		$query = "SELECT `id`,`partner`,`env`,`category`,`key`,`value`,`encrypted` FROM `settings` ".
			"WHERE ".
			"(`env`='".db::escape(ENV::main()->envName())."' || `env` IS NULL) AND ".
			"(`partner`='".db::escape($partner)."' || `partner`='".db::escape($subpartner)."' || `partner` IS NULL)";

		//if they searched for a category
		if($category !== null) { 
			$query .= " AND `category`='" . db::escape ($category) . "'";
		} else {
			$category = "";
		}
		
		$queryKey = "$partner.$subpartner.$category.Settings";
		$query .= " ORDER BY `category`, `key`";
		$result = db::memcacheMultiGet($query, $queryKey);

		$subpartnerSettings = array();
		$defaultSettings = array();
		$partnerSettings = array();

		foreach($result as $setting) { 

			//if it's encrypted, we need to decrypt it so we can use it
			if($setting['encrypted']) {
				$setting['value'] = baseModel::decrypt($setting['value']);
			}
			
			//if an env is set and partner equals the subpartner, its a subpartner setting
			if($setting['env'] && $setting['partner']==$subpartner && $setting['partner']!=""){
				$subpartnerSettings[$setting['key']] = $setting['value'];
			}

			//if an env and partner are set, it's a partner setting
			else if($setting['env'] && $setting['partner'] ) { 
				$partnerSettings[$setting['key']] = $setting['value'];
			}

			//if neither an env or partner are set, it's a default setting
			else if($setting['env'] === null && $setting['partner'] === null) { 
				$defaultSettings[$setting['key']] = $setting['value'];
			}

			//otherwise we're not quite sure how to load it
			else {
			}
		}

		//settings are loaded in order from left to right.
		//i.e. shared keys of default settings will get overwritten with partner settings
		// shared keys of partner will get overwritten with subpartner settings
		$allSettings = array_merge($defaultSettings, $partnerSettings, $subpartnerSettings);
		return $allSettings;
	}

	/**********************
		"PRIVATE" METHODS
	**********************/

	/*** save ***/
	//used to set the environment on a var if the partner is set, or vice versa
	public function save() { 

		//if there's a partner set on it, we need to make sure that the 
		//setting is associated with an environment
		if($this->env === null && $this->partner !== null) { 
			$this->env = ENV::main()->envName();
		}

		//if the partner is null, and we have an environment set on it
		//something is probably wrong
		if($this->partner === null && $this->env !== null) {
			throw new exception("If you're specifying an environment, you need to specify a partner");
		}
		return parent::save();
	}

	/*** setValue ***/
	//used to set the value according to the ecryption setting
	//on the object
	public function _setValue($value) { 

		if($this->encrypted) { 
			$this->value = baseModel::encrypt($value); 
		}
		else {
			$this->value = $value;
		}
		return $this->value;
	}

	/*** getValue ***/
	//used to get the value according to the ecryption setting
	//on the object
	public function _getValue() {
		if($this->encrypted) { 
			return baseModel::decrypt($this->value); 
		}
		else {
			return $this->value;
		}
	}

	/*** setEncrypted ***/
	//used to set the encrypted field on the object
	//also affects the current value stored
	public function _setEncrypted($enc) { 

		$this->encrypted = $enc;

		//try to decrypt
		$decrypt = baseModel::decrypt($this->value);

		//if encryption is set
		if($enc) { 

			//and the value isn't encrypted
			if($decrypt === FALSE && $this->value !== null) { 
				$this->value = baseModel::encrypt($this->value); 
			}
		}

		//otherwise encryption is not set
		else {

			//if the value is encrypted, we need to decrypt
			if($decrypt !== FALSE) {
				$this->value = $decrypt;
			}
		}

		return $enc;
	}

	public static function getSetting($category,$key){
		/*
		 * Note: the following may seem superfluous, but if we change partners in a batch script, it's necessary
		 */
		if(!($partner = globals::partner())){
			$partner = "--none--"; // we need an array index
		}
		
		if(!array_key_exists($partner, self::$settings)){
			self::$settings[$partner] = array();
		}
		
		if(!array_key_exists($category, self::$settings[$partner])){
			self::$settings[$partner][$category] = self::getPartnerSettings(null,$category);
		}
		return @self::$settings[$partner][$category][$key];
	}
	
	public static function getSettingRequired($category, $key) {
		$value = self::getSetting($category, $key);
		if ($value === null) {
			throw new Exception("Missing required setting: category=$category, key=$key, env=" . ENV::main()->envName());
		}
		return $value;
	}
}
