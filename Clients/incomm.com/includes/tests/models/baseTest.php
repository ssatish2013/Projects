<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class testingModel extends baseModel{
	public $message;
	protected $protectedVar = "test";
	protected $protectedVarWithOverrides;
	
	public function _getProtectedVarWithOverrides(){
		if(@isset($this->protectedVarWithOverrides)){
			return $this->protectedVarWithOverrides;
		} else {
			return NULL;
		}
	}
	public function _setProtectedVarWithOverrides($value){
		$this->protectedVarWithOverrides = $value;
	}
	public function _unsetProtectedVarWithOverrides(){
		unset($this->protectedVarWithOverrides);
	}
	public function _issetProtectedVarWithOverrides(){
		return @isset($this->protectedVarWithOverrides);
	}
}

class BaseTest extends PHPUnit_Framework_TestCase {
	
	public static $updateUserId;

	public function testEncryptDecrypt(){
		$string = 'This is a known string';
		$encryptedValue = baseModel::encrypt($string);
		$decryptedValue = baseModel::decrypt($encryptedValue);
		$this->assertEquals($string, $decryptedValue);
	}

	public function testEncryptAndDigestLoad(){
		$setting = new settingModel();
		$setting->category = 'facebook';
		$setting->key = 'appId';
		$setting->value = 'testAppId123';
		$setting->save(); 

		$saveUser = new userModel();
		$saveUser->email = 'matt@groupcard.com';
		$saveUser->password = 'ASDF123QRNP';
		$saveUser->firstName = 'Chester';
		$saveUser->lastName = 'McTESTER';
		$saveUser->save();
		self::$updateUserId = $saveUser->id;

		$user = new userModel(self::$updateUserId);
		$this->assertEquals($user->email, 'matt@groupcard.com');
		$this->assertEquals($user->firstName, 'Chester');
		$this->assertEquals($user->lastName, 'McTESTER');


		$userDigestLoad = new userModel();
		$userDigestLoad->email = 'matt@groupcard.com';
		$userDigestLoad->load();

		$this->assertEquals($userDigestLoad->email, 'matt@groupcard.com');
		$this->assertEquals($user->firstName, 'Chester');
		$this->assertEquals($user->lastName, 'McTESTER');

		// Test that digest is case insensitive

		$userDigestLoadCase = new userModel();
		$userDigestLoadCase->email = 'mAtT@gRoupCard.cOm';
		$userDigestLoadCase->load();

		$this->assertEquals($userDigestLoadCase->email, 'matt@groupcard.com');
		$this->assertEquals($user->firstName, 'Chester');
		$this->assertEquals($user->lastName, 'McTESTER');
	}

	public function testMultiLoad(){
		$user = new userModel();
		$user->email = 'matt@groupcard.com';
		$user->password = 'ASDF123QRNP';
		$user->load('email,password');

		$this->assertEquals($user->email, 'matt@groupcard.com');

		$user2 = new userModel();
		$user2->email = array('matt@groupcard.com', 'jon@groupcard.com');
		$user2->load();

		$this->assertEquals($user2->email, 'matt@groupcard.com');
	}

	public function testUpdate(){
		
		$user = new userModel(self::$updateUserId);
		$user->email = 'jon@groupcard.com';
		$user->firstName = 'jon';
		
		$user->save();
		$userId = $user->id;

		// Just load the model
		$newUserObject = new userModel($userId);
		$this->assertEquals($newUserObject->email, 'jon@groupcard.com');
		$this->assertEquals($newUserObject->firstName, 'jon');

		// Test that update doesn't do anything when it shouldn't
		$newUserObject->save();
		$this->assertEquals($newUserObject->email, 'jon@groupcard.com');
		$this->assertEquals($newUserObject->firstName, 'jon');


		//Now unset something
		$newUserObject->firstName = NULL;
		$newUserObject->save(); // Test that update doesn't do anything when it shouldn't
		$this->assertEquals($newUserObject->email, 'jon@groupcard.com');
		$this->assertEquals($newUserObject->firstName, NULL);
		

		// Make sure when we load this object again its in the same state.
		$newUser2 = new userModel();
		$newUser2->email = 'jon@groupcard.com';
		$newUser2->firstName = NULL;
		$newUser2->load('email,firstName', "and", FALSE);

		$this->assertEquals($newUser2->email, 'jon@groupcard.com');
		$this->assertEquals($newUser2->firstName, NULL);

		$user = new userModel();
		$user->email = 'testdude@groupcard.com';
		$user->firstName = "test";
		$user->save();
		$user->lastName = 'dude';
		$user->save();
	}

	public function testFailedLoad(){
		$user = new userModel();
		$user->email = 'matt@groupcard.com';
		$user->load();

		$this->assertEquals($user->email, 'matt@groupcard.com');
	}

	public function testGetAndSet(){
		$test = new testingModel();
		$test->protectedVarWithOverrides = "test";
		$this->assertEquals($test->protectedVarWithOverrides, "test");
		$this->assertEquals($test->protectedVar, "test");
		
		$didError = FALSE;
		try{
			$test->protectedVar = "test";
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
	}

	public function testIsetAndUnset(){
		$test = new testingModel();

		$this->assertTrue(isset($test->protectedVar));
		$this->assertFalse(isset($test->protectedVarWithOverrides));

		$test->protectedVarWithOverrides = "test";
		$this->assertTrue(isset($test->protectedVarWithOverrides));

		unset($test->protectedVarWithOverrides);
		$this->assertFalse(isset($test->protectedVarWithOverrides));
		
		$didError = FALSE;
		try{
			unset($test->protectedVar);
		} catch (Exception $e){
			$didError = TRUE;
		}
		$this->assertTrue($didError);
	}

	public function testDefaultValues() { 

		//create a settings model since encrypted should default to 0
		$setting = new settingModel();

		//just used so this doesn't intersect with other tests
		$setting->partner = 'testDefaultValues';		
		$setting->save();

		$this->assertEquals($setting->encrypted, 0, "testing default value for baseModel");
	}
	
	public function testValidate(){
		$array = array("formName" => 'unitTest', 'testingMessage' => 'test message');
		request::setUnsignedPost($array);
		
		globals::partner('baseTest');
		// Need to add a settings entry for this :-(
		$settings = new settingModel();
		$settings->partner = 'baseTest';
		$settings->category = 'unitTestForm';
		$settings->key = 'validateTestingMessage';
		$settings->value = 1;
		$settings->save();
		$settings->id = '';
		$settings->key = 'validateTestingMessageFail';
		$settings->save();

		$testing = new testingModel();
		$testing->validate();
		$this->assertEquals($testing->message, 'test message');
	}
}
