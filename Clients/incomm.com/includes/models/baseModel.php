<?php
class baseModel {
	protected $dbFields = array();
	protected $originalValues = array();
	protected $keyCache = array(
		"encrypt" => array(),
		"decrypt" => array()
	);

	public static function digest($value,$className,$field){
		$globalSalt = file_get_contents("/media/ram/digestsalt");
		$salt = $globalSalt.$className.$field;
		return md5($salt.strtolower($value));
	}

	public static function encrypt($value){
		$keyName = Env::getEncryptKeyName();
		$chunkSize = 256;

		if ( ! isset( $keyCache["encrypt"][ $keyName ] ) ) {
			$keyFile = "/media/ram/privatekey.$keyName";
			if ( ! file_exists( $keyFile )) {
				return false;
			}
			$keyCache["encrypt"][ $keyName ] = file_get_contents($keyFile);
		} 

		$privateKey = $keyCache["encrypt"][ $keyName ];

		$keyFile = "/media/ram/publickey.$keyName";
		if(!file_exists($keyFile)){
			return false;
		}
		$publicKey = file_get_contents($keyFile);

		$parts = array($keyName);
		for($i=0;$i<strlen($value);$i+=$chunkSize){ // Break up into chunks
			$success = openssl_public_encrypt(substr($value,$i,$chunkSize),$res,$publicKey);
			$res = base64_encode($res);
			$parts[]=$res;
			if(!$success){
				return false;
			}
		}
		
		$res = implode("!",$parts);
		return $res;
	}

	public static function decrypt($value, $returnValueOnEncryptionError=false){
		$parts = explode("!",$value);
		$keyName = array_shift($parts);

		if ( ! isset( $keyCache["decrypt"][ $keyName ] ) ) {
			$keyFile = "/media/ram/privatekey.$keyName";
			if ( ! file_exists( $keyFile )) {
				if($returnValueOnEncryptionError)
					return $value;
				else return false;
			}
			$keyCache["decrypt"][ $keyName ] = file_get_contents($keyFile);
		} 

		$privateKey = $keyCache["decrypt"][ $keyName ];

		$return = "";

		foreach($parts as $part){
			$value = base64_decode($part);
			$success = openssl_private_decrypt($value, $res, $privateKey);
			$return.=$res;
			if(!$success){
				if($returnValueOnEncryptionError)
					return $value;
				else return false;
			}
		}
		return $return;
	}

	public function __construct( $param = null, $load = true ) {

		if ( isset( $param )) {
			if ( is_object( $param ) && property_exists( $param, "id" )) {
				$loaderId = str_replace("Model", "", get_class( $param )) . "Id";
				if ( property_exists( $this, $loaderId )) {
					$this->$loaderId = $param->id;
				}
			} else if ( is_array( $param ) ) {
				foreach( $param as $prop => $value ) {
					if ( property_exists( $this, $prop ) ) {
						if(is_null($value)){
							$this->$prop = NULL;
						} else {
							$this->$prop = $value;
						}
					}
				}
			} else if ( is_numeric( $param ) ) {
				$this->id = $param;
			} else if ( is_string( $param ) && property_exists( $this, 'guid' ) ) {
				$this->guid = $param;
			}

			if ( $load ) {
				$this->load();
			}
		}
	}

	public function __get($key){
		$getMethod = "_get".ucfirst($key);
		if(method_exists(get_class($this),$getMethod)){
			return $this->$getMethod();
		}
		return $this->$key;
	}
	public function __set($key,$value){
		$setMethod = "_set".ucfirst($key);
		if(method_exists(get_class($this),$setMethod)){
			$this->$setMethod($value);
		} else {
			throw new Exception("Sorry please use a setter:\n this is: " . get_class($this) . "\n key is: " . print_r($key, 1));
		}
	}
	public function __unset($key){
		$unsetMethod = "_unset".ucfirst($key);
		if(method_exists(get_class($this),$unsetMethod)){
			$this->$unsetMethod($this->$key);
		} else {
			throw new Exception('Sorry please build the unsetter for this');
		}
	}
	public function __isset($key){
		$issetMethod = "_isset".ucfirst($key);
		if(method_exists(get_class($this),$issetMethod)){
			return $this->$issetMethod($this->$key);
		} else {
			return isset($this->$key);
		}
	}

	private function setOriginalValues(){
		$this->originalValues = get_object_vars($this);
	}

	public function getPiiValues($doCrypt = true) { 
		$values = array();
		foreach($this->dbFields as $field => $fieldValues) { 
				
			if($doCrypt) { 
				if(isset($fieldValues['properties'])) {
					if(@$fieldValues['properties']['encrypt']) { 
						$values[$field.'Crypt'] = self::encrypt($this->$field);
					}

					if(@$fieldValues['properties']['digest']) { 
						$values[$field.'Digest'] = self::digest($this->$field,substr(get_class($this),0,-5),$field);
					}
				}
				else {
					$values[$field] = $this->$field;
				}
			}
			else {
				$values[$field] = $this->$field;
			}
		}
		return $values;
	}


	public function assignValues($u,$decrypt=true){
		if(is_array($u)){
			foreach($u as $k=>$v){
				$realKey = substr($k,0,-5);
				if( $decrypt && strtolower(substr($k,-5)) == "crypt" && $this->dbFields[$realKey]['properties']['encrypt']){
					if(property_exists($this,$realKey)){
						$returnValueOnEncryptionError = array_key_exists('returnValueOnEncryptionError', $this->dbFields[$realKey]['properties'])
							&& $this->dbFields[$realKey]['properties']['returnValueOnEncryptionError'];
						$this->$realKey = self::decrypt($v, $returnValueOnEncryptionError);
					}
				} else {
					if(property_exists($this,$k)){
						if (isset($v)) {
							
							//it's defined as an integer, lets _make_ it an integer
							if(	isset($this->dbFields[$k]['scheme']['type']) &&
									preg_match('/int/', $this->dbFields[$k]['scheme']['type'])
							) { 
								$v = intval($v);
							}
							$this->$k = $v;
						}
					}
				}
			}
			$this->setOriginalValues();
		}
	}
	
	public function destroy($confirm) {
		if(!$confirm || !$this->id){
			return false;
		}
		$table = $this->getTableName();
		$query = "DELETE FROM `$table` WHERE `id`=".db::escape($this->id)." LIMIT 1";
		db::query($query);
		unset($this->id);
		
		return mysql_errno()==0;
	}

	public function save() {

		//setup old values
		$old = $this->originalValues;
		unset($old['dbFields']);
		unset($old['originalValues']);
		unset($old['keyCache']);

		$obj = null;
		if($this->id==0) {
			$obj = $this->insert();
		} else {
			$obj = $this->update();
		}

		//setup new values 
		$new = get_object_vars($this);
		unset($new['dbFields']);
		unset($new['originalValues']);
		unset($new['keyCache']);

		actionModel::logChanges($this->getTableName(), $new, $old, array('id' => $this->id));
		return $obj;
	}

	public function getLoadQuery( $key = null, $operand = "AND", $ignoreNull = true, $limit = 1, $orderBy = null ) {
		
		$table = $this->getTableName();

		if(!$key){
			$keys = array();
			foreach($this->dbFields as $key=>$value){
				if(isset($this->$key)){
					$keys[] = $key;
				}
			}
		} else {
			$keys = explode(",",$key);
		}
		$parts = array();
		foreach($keys as $key){
			$newParts = array();

			if(!is_array($this->$key)){
				$values = array($this->$key);
			} else {
				$values = $this->$key;
			}

			foreach($values as $value){
				if(@$this->dbFields[$key]['properties']['digest']){
					$value = db::escape(self::digest($value,substr(get_class($this),0,-5),$key));
					$key = db::escape($key."Digest");
				} else {
					if(isset($this->$key)){
						$value = db::escape($value);
					}
					$key = db::escape($key);
				}
				
				if($ignoreNull||$value!==null){
					$newParts[] = "`$key`='$value'";
				} else {
					$newParts[] = "`$key` IS NULL";
				}
			}

			if(sizeof($newParts)==1){
				$parts=array_merge($parts,$newParts);
			} else {
				$parts[]="(".implode(" OR ",$newParts).")";
			}

		}


		$query = "SELECT * FROM `$table` ";

		if ( ! empty( $parts ) ) {
			$query .= " WHERE ". implode(" $operand ",$parts);
		}

		if ( $orderBy ) {
			$query .=" ORDER BY $orderBy ";
		}

		if( $limit ) {
			$query .=" LIMIT $limit";
		}
		return $query;
	}

	public function getModelName(){
		return substr(get_class($this),0,-5);
	}

	public function getTableName(){
		return db::escape($this->getModelName()).'s';
	}

	public static function loadAll( $param = null, $limit = null, $orderBy = null, $memcacheKey = null, $operand = 'and', $ignoreNull=false ) {

		$model = get_called_class();

		// Sanity check
		if ( $model === get_class() ) {
			throw new Exception("baseModel static method loadAll must be called from a model subclass");
		}
		$loader = new $model( $param, false );

		if(is_array($param)){
			$loadQuery = $loader->getLoadQuery(implode(',', array_keys($param)), $operand, $ignoreNull, $limit, $orderBy );
		} else {
			$loadQuery = $loader->getLoadQuery(null, $operand, $ignoreNull, $limit, $orderBy );
		}
		$ret = array();
		if($memcacheKey){
			$result = db::memcacheMultiGet( $loadQuery, $memcacheKey );
			foreach ( $result as $row) {
				$tmp = new $model();
				$tmp->assignValues( $row, true );
				$ret[] = $tmp;
			}
		} else {
			$result = db::query( $loadQuery );
			while ( $row = mysql_fetch_assoc( $result )) {
				$tmp = new $model();
				$tmp->assignValues( $row, true );
				$ret[] = $tmp;
			}

		}

		return $ret;
	}

	public function loadOrException ( $key=null,$operand="AND",$ignoreNull=true ) {
		if($result = $this->load( $key, $operand, $ignoreNull )){
			return $result;
		}
		throw new loadException("No such entity " . get_class($this) . ": key=" . 
				print_r($key, true) . ", operand=$operand, ignoreNull=$ignoreNull");
	}
	
	public function load( $key=null,$operand="AND",$ignoreNull=true ) {
		$query = $this->getLoadQuery($key, $operand, $ignoreNull);


		$result = db::query($query);
		if($row = mysql_fetch_assoc($result)){
			$this->assignValues($row,true);
			return true;
		} else {
			$this->setOriginalValues();
			return false;
		}
	}

	protected function update() {
		$values = get_object_vars( $this );
		$update = array();
		$updatedTimestamps = array();
		foreach( $values as $k => $v ) {
			if ( $k != "id" && $this->$k != $this->originalValues[$k] && isset( $this->dbFields[$k] )) {
				$update[] = $k;
			}

			// As long as we're looping through, we should mark for update any fields with the property 'updatedTimestamp'
			if(@$this->dbFields[$k]['properties']['updatedTimestamp']){
				$updatedTimestamps[]=$k;
			}
		}
		$table = $this->getTableName();
		if ( sizeof( $update ) > 0 ) {

			foreach($updatedTimestamps as $tsKey){
				$this->$tsKey=date("Y-m-d H:i:s");
				$update[]=$tsKey;
			}

			$query = "UPDATE `$table` SET ";
			$queryPart = array();
			foreach($update as $u){
				if(@$this->dbFields[$u]['properties']['encrypt'] || @$this->dbFields[$u]['properties']['digest']){
					if(@$this->dbFields[$u]['properties']['encrypt']){
						$key = db::escape($u."Crypt");
						$value = db::escape(self::encrypt($this->$u));
						$queryPart[]= "`$key`='$value'";
					}
					if(@$this->dbFields[$u]['properties']['digest']){
						$key = db::escape($u."Digest");
						$value = db::escape(self::digest($this->$u,$this->getModelName(),$u));
						$queryPart[]= "`$key`='$value'";
					}
				} else {
					$key = db::escape($u);
					$value = db::escape($this->$u);
					if($this->$u === null) {
						$queryPart[]= "`$key`= NULL";
					}
					else {
						$queryPart[]= "`$key`='$value'";
					}
				}
			}
			$query .= implode(",",$queryPart);
			$query .= " WHERE `id` = '$this->id' LIMIT 1";
			db::query($query);

			return mysql_errno()==0;
		} else {
			return true; // If there's nothing to update, it's successful.
		}
	}

	protected function insert(){

		$values = get_object_vars($this);
		foreach($values as $k=>$v){
			if(@$this->dbFields[$k]['properties']['createdTimestamp']){
				$this->$k=date('Y-m-d H:i:s');
			}
		}
		$table = $this->getTableName();
		$keys = array();
		$vals = array();
		foreach($values as $k=>$v){
			if(isset($this->$k)&&isset($this->dbFields[$k])){
				if(@$this->dbFields[$k]['properties']['encrypt'] || @$this->dbFields[$k]['properties']['digest']){
					if(@$this->dbFields[$k]['properties']['digest']){
						$key = db::escape($k."Digest");
						$keys[]="`$key`";
						$val = db::escape(self::digest($this->$k,$this->getModelName(),$k));
						$vals[]="'$val'";
					}

					if(@$this->dbFields[$k]['properties']['encrypt']){
						$key = db::escape($k."Crypt");
						$keys[]="`$key`";
						$val = db::escape(self::encrypt($this->$k));
						$vals[]="'$val'";
					}
				} else {
					$key = db::escape($k);
					$keys[]="`$key`";
					$val = db::escape($this->$k);
					$vals[]="'$val'";
				}
			}

			//if we didn't set a value AND it has a default value, let's set it
			else if(!isset($this->$k) && isset($this->dbFields[$k]['scheme']['default'])) {
				$this->$k = $this->dbFields[$k]['scheme']['default'];
			}


		}
		$query = "INSERT INTO `$table` (";
		$query .= implode(",",$keys);
		$query .= ") VALUES (";
		$query .= implode(",",$vals);
		$query .= ")";

		db::query($query) or log::fatal(mysql_error());
		
		//populate object after it's been inserted
		if ( property_exists( $this, "id" )) {
			$idType = $this->dbFields['id']['scheme']['type'];
			if(preg_match('/int/', $idType)) { 
				$this->id = mysql_insert_id();
			}
		}

		//any default feilds that weren't set
		$this->setOriginalValues();
		return mysql_errno()==0;
	}
	
	public function validate($formType=null) {
		$class = $this->getModelName();
		$returnValue = TRUE;
		if(!$formType){
			$formType = request::unsignedPost('formName') . 'Form';
		}
		// get all of the validate functions for this formType
		$validateFunctions = settingModel::getPartnerSettings(globals::partner(), $formType);
		foreach($validateFunctions as $key => $value){
			// if this validate function is enabled and its for this type of class
			$property = lcfirst(str_replace('validate', '', $key));
			if($value && (strpos($property, $class) === 0)){ // Is this validator for this class?
				if(formValidationHelper::$key()){
					try{
						// If it passed validation lets assign it to the object right away
						$modelProperty = lcfirst(str_replace($class, '', $property));
						$setter = '_set' . ucfirst($modelProperty);
						if(method_exists($this, $setter)){
							// Because we are using $this magic setters dont work so we need to check 
							$this->$setter(request::unsignedPost($property));
						} else {
							$this->$modelProperty = request::unsignedPost($property);
						}
					} catch (Exception $e){
						// Hmmm, that property doesn't exist on this model ???
					}
				} else {
					if (preg_match('/^\$\{(.+)\}$/', $value, $matches)) {
						$value = languageModel::getString($matches[1]);
					}
					Env::main()->validationErrors[$property] = $value;
					$returnValue = False;
				}
			}
		}
		return $returnValue;
	}
}
