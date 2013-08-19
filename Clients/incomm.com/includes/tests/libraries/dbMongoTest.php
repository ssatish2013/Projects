<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class dbMongoTest extends PHPUnit_Framework_TestCase {

	public static $testUrl = 'thisisadbMongoLibraryUrl';
	public static $testCall= 'thisisadbMongoLibraryCall';
	public static $testApiPartner = 'thisisadbMongoLibraryApiPartner';

	public function testConnect(){
		$conn = Env::mongoConn();
		$this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $conn, "Connecting to Mongo");

		$db = ENV::mongoDb();
		$this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $conn, "Using Mongo DB");
	}

	public function testFind() {

		//make some new objects
		$apiLog = new apiLogModel();
		$apiLog->url = self::$testUrl;
		$apiLog->save();

		$apiLog = new apiLogModel();
		$apiLog->call = self::$testCall;
		$apiLog->save();

		$apiLog = new apiLogModel();
		$apiLog->apiPartner = self::$testApiPartner;
		$apiLog->save();

		$apiLog = new apiLogModel();
		$apiLog->apiPartner = self::$testApiPartner;
		$apiLog->call = self::$testCall;
		$apiLog->url = self::$testUrl;
		$apiLog->save();

		$findCriteria = array('url' => self::$testUrl);
		$results = dbMongo::find('apiLogs', $findCriteria);
		$this->assertEquals(2, count($results));

		$findCriteria = array('apiPartner' => self::$testApiPartner);
		$results = dbMongo::find('apiLogs', $findCriteria);
		$this->assertEquals(2, count($results));

		$findCriteria = array('call' => self::$testCall);
		$results = dbMongo::find('apiLogs', $findCriteria);
		$this->assertEquals(2, count($results));

		$findCriteria = array(
			'apiPartner' => self::$testApiPartner,
			'url'	=> self::$testUrl,
			'call' => self::$testCall
		);
		$results = dbMongo::find('apiLogs', $findCriteria);
		$this->assertEquals(1, count($results));

		$findCriteria = array('id' => $apiLog->id);
		$results = dbMongo::find('apiLogs', $findCriteria);
		$this->assertEquals(1, count($results));


	}
}
