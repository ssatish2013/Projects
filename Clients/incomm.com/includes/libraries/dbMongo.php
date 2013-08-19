<?php

class dbMongo {

	public static $unitTestDB = null;
	
	static function connect() {

		//setup initial vars
		$connectString = "";
		$dbName = ENV::getMongo('database');

		//if we're unit testing
		if(self::$unitTestDB) { 
//			$connectString = 'mongodb://test-user:test-password';
//			$connectString .= '@'.ENV::getMongo('server').':'.ENV::getMongo('port');
			$connectString = 'mongodb://'.ENV::getMongo('server').':'.ENV::getMongo('port');
			$connectString .= '/'.self::$unitTestDB;
			$dbName = self::$unitTestDB;
		}

		//if not, use env vars
		else {
//			$connectString = 'mongodb://'.ENV::getMongo('user').':'.ENV::getMongo('pass');
//			$connectString .= '@'.ENV::getMongo('server').':'.ENV::getMongo('port');
			$connectString = 'mongodb://'.ENV::getMongo('server').':'.ENV::getMongo('port');
			$connectString .= '/'.ENV::getMongo('database');
		}


		//try to establish a connection
		$m = new Mongo($connectString, array("replicaSet" => true));
		//TODO figure out slaveness and and how it works with replication
		//$m->setSlaveOkay(true);
		return $m;
	}
		
	static function selectDb() { 

		$dbName = ENV::getMongo('database');
		if(self::$unitTestDB) {
			$dbName = self::$unitTestDB;
		}

		$db = Env::mongoConn()->$dbName;
		return $db;
	}

	static function insert($table, $object) {

		$collection = Env::mongoDb()->$table;
		$response = $collection->insert($object, array("safe"	=> true));
		return array($response, $object);
	}

	static function findOne($table, $criteria) {
		$collection = Env::mongoDb()->$table;

		if(isset($criteria['id'])) { 
			$criteria['_id'] = new MongoId($criteria['id']);
			unset($criteria['id']);
		}
		$responseObj = $collection->findOne($criteria);

		//don't want the id
		if(isset($responseObj['_id'])) { 
			$responseObj['id'] = $responseObj['_id']->__toString();
			unset($responseObj['_id']);
		}
		return $responseObj;
	}

	static function find($table, $criteria, $sort = array(), $limit = null) { 
		$collection = Env::mongoDb()->$table;

		if(isset($criteria['id'])) { 
			$criteria['_id'] = new MongoId($criteria['id']);
			unset($criteria['id']);
		}
		$cursor = null;
		if($limit === null) { 
			$cursor = $collection->find($criteria)->sort($sort);
		} 
		else {
			$cursor = $collection->find($criteria)->sort($sort)->limit($limit);
		}
		$cursor->rewind();

		$resultObj = array();
		foreach($cursor as $doc) { 
			$doc['id'] = $doc['_id']->__toString();
			unset($doc['_id']);

			foreach($doc as $key => $value) { 

				//we're in PHP so let's assume we don't want MongoDate objects
				if(gettype($value) == 'object') { 

					//if it's a mongo date, lets convert it to a string date
					if(get_class($value) == 'MongoDate') { 
						$doc[$key] = date('Y-m-d H:i:s', $value->sec);
					}
				}
			}
			$resultObj[] = $doc;
		}
		return $resultObj;
	}

	static function update($table, $criteria, $object) {
		$collection = Env::mongoDb()->$table;
		$responseObj = $collection->update($criteria, array('$set' => $object), array('safe' => true));
		return $responseObj;
	}

	static function distinct($table, $key) { 
		$distincts = Env::mongoDb()->command(array("distinct" => $table, "key" => $key));
		return $distincts['values'];
	}

	static function mongoAppender($entry) {
		self::insert('logs', log::mongoEntry($entry));
	}
}
