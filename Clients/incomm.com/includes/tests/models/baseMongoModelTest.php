<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class baseMongoModelTest extends PHPUnit_Framework_TestCase {

	public static $insertedId;
	public static $insertedCall = 'baseMongoTestCall';
	public static $insertedApiPartner = 'baseMongoTestApiPartner';
	public static $insertedUrl = 'http://this.is.a/uniqueUrl?=13821k1';

	public function testInsert(){
		$apiLog = new apiLogModel();
		$this->assertTrue(($apiLog->id == 0), 'test id not set');
		$apiLog->call = self::$insertedCall;
		$apiLog->url = self::$insertedUrl;
		$apiLog->save();
		$this->assertFalse(($apiLog->id == 0), 'test id set');
		
		self::$insertedId = $apiLog->id;
	}

	public function testLoad() {

		//test loading by id
		$apiLog = new apiLogModel();
		$apiLog->id = self::$insertedId;
		$apiLog->load();
		$this->assertEquals($apiLog->call, self::$insertedCall);

		//load by direct id
		$apiLog = new apiLogModel(self::$insertedId);
		$this->assertEquals($apiLog->call, self::$insertedCall);

		//load by another key
		$apiLog = new apiLogModel();
		$apiLog->url = self::$insertedUrl;
		$apiLog->call = self::$insertedCall;
		$apiLog->load('url,call');
		$this->assertEquals($apiLog->id, self::$insertedId, 'verify key load id');
		$this->assertEquals($apiLog->call, self::$insertedCall, 'verify key load call');
		$this->assertEquals($apiLog->url, self::$insertedUrl, 'verify key load url');

		//load an id that doesn't exist
		$apiLog = new apiLogModel();
		$apiLog->id = 'thisIdDOESnotExist';
		$this->assertFalse($apiLog->load(), 'test failed load');

	}

	public function testUpdate() { 
		$apiLog = new apiLogModel();
		$apiLog->call = 'thisisanupdatetestcall';
		$apiLog->save();
		$id = $apiLog->id;
		$apiLog->apiPartner = self::$insertedApiPartner;
		$apiLog->save();
		$apiLog->url = null;
		$apiLog->save();

		$apiLog = new apiLogModel($id);
		$this->assertEquals($apiLog->call,'thisisanupdatetestcall');
		$this->assertEquals($apiLog->apiPartner, self::$insertedApiPartner);
		$this->assertEquals($apiLog->url, null); 
		
		$apiLog = new apiLogModel($id);
		$this->assertTrue($apiLog->save());
	}

	public function testEncryption() { 
		/* Needs to be rewritten
			 At the moment nothing uses this */
	}
}
