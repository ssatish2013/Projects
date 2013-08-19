<?php
class baseMongoModel {

	protected $dbFields = array();
	protected $originalValues = array();

	public function __construct($id=null){
		if(isset($id)){
			$this->id=$id;
			$this->load();
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
      throw new Exception('Sorry please use a setter for ' . $setMethod);
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

	public function assignValues($u, $decrypt = true){
		if(is_array($u)){
			foreach($u as $k=>$v){
        $realKey = substr($k,0,-5);
        if($decrypt && strtolower(substr($k,-5)) == "crypt" && $this->dbFields[$realKey]['properties']['encrypt']){
          if(property_exists($this,$realKey)){
            $this->$realKey = self::decrypt($v);
          }
        } 
				if(property_exists($this,$k)){
					if (isset($v)) {
						$this->$k = $v;
					}
				}
			}
		}
	}

	private function formatArray($arr) { 
		foreach($arr as $key => $value) { 
			
			//dots are not allowed in field names
			if(strpos($key, ".") !== false) { 
				$newKey = str_replace(".", "_", $key);
				$arr[$newKey] = $value;
				unset($arr[$key]);
				$key = $newKey;
			}

			if(is_numeric($value)) { 
				$arr[$key] = floatval($value);
			} else if (is_object($value)) {
				$arr[$key] = $value; // will use __toString()
			} else if(is_array($value)) { 
				$arr[$key] = $this->formatArray($arr[$key]);
			}
			else if(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) { 
				$arr[$key] = new MongoDate(strtotime($arr[$key]));
			}
			else {
				$arr[$key] = $value;
			}

		}
		return $arr;
	}

	public function save() {

		//first off let's make sure we have all of the correct
		//types set
		foreach($this->dbFields as $key => $field) { 
			if(isset($field['scheme']['type'])) { 
				switch($field['scheme']['type']) { 
					case 'int': 
						$this->$key = intval($this->$key);
						break;
					case 'float':
						$this->$key = floatval($this->$key);
						break;
					case 'date':
						//if it's a current timestamp
						if(
							(isset($field['properties']['createdTimestamp']) && $this->$key === null) ||   
							(isset($field['properties']['updatedTimestamp']))
						) {
							$this->$key = new MongoDate(strtotime("now"));
						}
						else {
							$this->$key = new MongoDate(strtotime($this->$key));
						}
						break;
				}
			}
			//if it's an array, we want to make sure
			//it's contents is formatted properly
			else if(is_array($this->$key)) {
				$this->$key = $this->formatArray($this->$key);
			}
		}

		if($this->id==0) {
			return $this->insert();
		} else {
			return $this->update();
		}
	}

	public function getModelName(){
		return substr(get_class($this),0,-5);
	}

	public function getTableName(){
		return $this->getModelName().'s';
	}

	public function load($key = null) {
		$queryObj = array();
		$resultObj = array();
		$table = $this->getTableName();

		//if we already have an id, let's look it up
		if(isset($this->id)) { 
			$queryObj['id']= $this->id;
			$resultObj = dbMongo::findOne($table, $queryObj);
		}


		//otherwise lets use our query array
		else { 
			$keys = explode(",",$key);
			foreach($keys as $key) { 
				if(isset($this->$key)) { 
					if(@$this->dbFields[$key]['properties']['digest']){
						$queryObj[$key.'Digest'] = $this->$key;
					}
					else {
						$queryObj[$key] = $this->$key;
					}
				}
			}
			$resultObj = dbMongo::findOne($table, $queryObj);
		}

		if(count($resultObj)) { 
			$this->assignValues($resultObj,true);
			$this->setOriginalValues();
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
			if ( $k != "id" && isset( $this->dbFields[$k] )) {
				$update[] = $k;
			}
			// As long as we're looping through, we should mark for update any fields with the property 'updatedTimestamp'
			if(@$this->dbFields[$k]['properties']['updatedTimestamp']){
				$updatedTimestamps[]=$k;
			}
		}
		$table = $this->getTableName();
		if ( sizeof( $update ) > 0 ) {

			$updateObj = array();
			foreach($updatedTimestamps as $tsKey){
				$this->$tsKey=new MongoDate(strtotime("now")); 
				$updateObj[$tsKey] = $this->$tsKey;
			}

			foreach($update as $u){

				$key = '';
				$value = '';
				if(@$this->dbFields[$u]['properties']['createdTimestamp']) { 
					continue;
				}
				if(@$this->dbFields[$u]['properties']['encrypt'] || @$this->dbFields[$u]['properties']['digest']){
          if(@$this->dbFields[$u]['properties']['encrypt']){
            $key = $u."Crypt";
            $value = baseModel::encrypt($this->$u);
          }
          if(@$this->dbFields[$u]['properties']['digest']){
            $key = $u."Digest";
            $value = baseModel::digest($this->$u,$this->getModelName(),$u);
          }
        } 
				else {
					$key = $u;
					$value = $this->$u;
				}
				$updateObj[$key] = $value;
			}

			$response = dbMongo::update($table, 
				array('_id' => new MongoId($this->id)),
				$updateObj
			);
			if($response['ok'] == 1) { return true; }
			return false;
		} else {
			return true; // If there's nothing to update, it's successful.
		}
	}

	protected function insert(){

		$values = get_object_vars($this);
		foreach($values as $k=>$v){
			if(@$this->dbFields[$k]['properties']['createdTimestamp']){
				$this->$k=new MongoDate(strtotime("now"));
			}
		}

		$table = $this->getTableName();
		$object = array();

		foreach($values as $k=>$v){

			//assuming no encryption for now
			if(isset($this->$k)&&isset($this->dbFields[$k])){
				if(@$this->dbFields[$k]['properties']['encrypt'] || @$this->dbFields[$k]['properties']['digest']){
          if(@$this->dbFields[$k]['properties']['digest']){
            $key = $k."Digest";
            $val = baseModel::digest($this->$k,$this->getModelName(),$k);
          }
          if(@$this->dbFields[$k]['properties']['encrypt']){
            $key = $k."Crypt";
            $val = baseModel::encrypt($this->$k);
          }
				}
				else {
					$key = $k;
					$val = $this->$k;
				}
				$object[$key] = $val;
			}

			//if we didn't set a value AND it has a default value, let's set it
			else if(!isset($object[$k]) && isset($this->dbFields[$k]['scheme']['default'])) {
				$object[$k] = $this->dbFields[$k]['scheme']['default'];
			}


		}
		list($response, $returnObj) = dbMongo::insert($table, $object);
		$this->id = $returnObj['_id']->__toString();
		$this->setOriginalValues();
		if($response['ok'] == 1) { return true; }
		return false;
	}
	
}
