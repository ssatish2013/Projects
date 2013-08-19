<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class EncodingHelperTest extends PHPUnit_Framework_TestCase {

	public function testUrlSafeBase64Encode(){
		$this->assertEquals(encodingHelper::urlSafeBase64Encode('{"data":"isGood"}'), 'eyJkYXRhIjoiaXNHb29kIn0');
	}
	
	public function testUrlSafeBase64Decode(){
		$this->assertEquals(encodingHelper::urlSafeBase64Decode('eyJkYXRhIjoiaXNHb29kIn0'), '{"data":"isGood"}');
	}
}