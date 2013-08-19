<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class SettingTest extends PHPUnit_Framework_TestCase {

	//default values we'll be using throughout the tests
	//it's easier to change in one spot than many

	//namespace the partner we'll be using for our tests
	public static $settingsPartner = 'settingTestPartner';

	//namespace the category we'll be using for the tests
	public static $settingsCategory = 'settingTestPartnerCategory';

	//other values that get set and we need to compare against
	public static $settingsDefaultValues;
	public static $settingsDefaultCategoryValues;
	public static $settingsPartnerValues;
	public static $settingsPartnerCategoryValues;

	public static function setupBeforeClass() { 

		//create  a default setting
		$setting = new settingModel();
		$setting->key = 'defaultSetting';
		$setting->value = 'defaultValue';
		$setting->save();

		//create  a default encrypted setting
		$setting = new settingModel();
		$setting->key = 'defaultEncryptedSetting';
		$setting->category = self::$settingsCategory;
		$setting->value = 'defaultEncryptedValue';
		$setting->encrypted = 1;
		$setting->save();

		//what default settings should look like
		self::$settingsDefaultValues = array(
			'defaultSetting'	=> 'defaultValue',
			'defaultEncryptedSetting'	=> 'defaultEncryptedValue'
		);

		//what default category settings should look like
		self::$settingsDefaultCategoryValues = array(
			'defaultEncryptedSetting'	=> 'defaultEncryptedValue'
		);

		//create  a partner setting
		$setting = new settingModel();
		$setting->key = 'defaultSetting';
		$setting->value = 'partnerValue';
		$setting->partner = self::$settingsPartner;
		$setting->save();

		//create  a partner encrypted setting
		$setting = new settingModel();
		$setting->key = 'defaultEncryptedSetting';
		$setting->value = 'partnerEncryptedValue';
		$setting->category = self::$settingsCategory;
		$setting->partner = self::$settingsPartner;
		$setting->encrypted = 1;
		$setting->save();

		//what settings should look like
		self::$settingsPartnerValues = array(
			'defaultEncryptedSetting'	=> 'partnerEncryptedValue',
			'defaultSetting'	=> 'partnerValue'
		);

		//what the category ettings should look like
		self::$settingsPartnerCategoryValues = array(
			'defaultEncryptedSetting'	=> 'partnerEncryptedValue'
		);
	}

	/*** Test Structure ***/
	public function testStructure(){
		$uth = new unitTestingHelper();
		$this->assertTrue($uth->validateStructure('setting'));
	}

	/*** Test getPartnerSettings ***/
	public function testGetPartnerSettings() {

		//get partner specific settings
		$settings = settingModel::getPartnerSettings(self::$settingsPartner);

		//get default settings.  We only need to check the ones 
		//that we setup in here in this test.  There may be others
		//setup by other tests
		$settings = settingModel::getPartnerSettings('somerandompartnerthatdoesntexist');
		foreach($settings as $setting => $value) { 
			if(isset(self::$settingsDefaultValues[$setting])) { 
				$this->assertEquals(self::$settingsDefaultValues[$setting], $value);
			}
		}

		//test partner specific category settings
		$settings = settingModel::getPartnerSettings(self::$settingsPartner, self::$settingsCategory);
		$this->assertEquals(self::$settingsPartnerCategoryValues, $settings);

		//get default settings.  We only need to check the ones 
		//that we setup in here
		$settings = settingModel::getPartnerSettings('somerandompartnerthatdoesntexist', self::$settingsCategory);
		foreach($settings as $setting => $value) { 
			if(isset(self::$settingsDefaultCategoryValues[$setting])) { 
				$this->assertEquals(self::$settingsDefaultCategoryValues[$setting], $value);
			}
		}
	}

	/*** Test Value Manipulation ***/
	public function testValue() { 
		$firstValue = 'firstValue';
		$secondValue = 'secondValue';
		$firstEncValue = baseModel::encrypt($firstValue);
		$secondEncValue = baseModel::encrypt($secondValue);

		//just used to namespace this setting so it doesn't get pulled in by default
		$partner = 'testValue';

		//create a new model
		$setting = new settingModel();
		$setting->partner = $partner;
		$setting->encrypted = 1;

		//test blank encryption
		$setting->value = '';
		$this->assertEquals($setting->value, '', "test blank value");

		//reset the value, should still be encrypted
		$setting->value = $firstValue;
		$this->assertEquals($setting->value, baseModel::decrypt($firstEncValue), "changing the initial value");

		//changing more values
		$setting->value = $secondValue;
		$this->assertEquals($setting->value, baseModel::decrypt($secondEncValue), "changing the value more while encrypted");

		//decrypting
		$setting->encrypted = 0;
		$this->assertEquals($setting->value, $secondValue, "decrypting changed value");

		//setting to another value
		$setting->value = $firstValue;
		$this->assertEquals($setting->value, $firstValue, "changed value even more");

		//encrypting last value
		$setting->encrypted = 1;
		$this->assertEquals($setting->value, baseModel::decrypt($firstEncValue), "changing the initial value");

	}

	/*** Test Encryption Manipulation ***/
	public function testEncryption() { 
		$value = 'testValue';
		$encValue = baseModel::encrypt($value);
		
		//just used to namespace this setting so it doesn't get pulled in by default
		$partner = 'testEncryption';							

		//default setter
		$setting = new settingModel(); 
		$setting->partner = $partner;
		$setting->value = $value;
		$this->assertEquals($setting->value, $value, "set value on new object, not encrypted");

		//encrypting value
		$setting->encrypted = 1;
		$this->assertEquals($setting->value, baseModel::decrypt($encValue), "changed encrypted, should be encrypted");

		//say we accidentally re-set encrypted to 1
		$setting->encrypted = 1;
		$setting->encrypted = 1;
		$setting->encrypted = 1;
		$this->assertEquals($setting->value, baseModel::decrypt($encValue), "shouldn't be be re-encrypting");

		//now decrypting the value
		$setting->encrypted = 0;
		$this->assertEquals($setting->value, $value, "value should be decrypted");

		//accidentally set encrypted to 0 again
		$setting->encrypted = 0;
		$setting->encrypted = 0;
		$setting->encrypted = 0;
		$this->assertEquals($setting->value, $value, "value shouldn't be re-decrypted");

		//test lots of changes
		$setting->encrypted = 1;
		$setting->encrypted = 0;
		$setting->encrypted = 1;
		$setting->encrypted = 1;
		$setting->encrypted = 0;
		$setting->encrypted = 1;
		$setting->encrypted = 0;
		$this->assertEquals($setting->value, $value, "whenever set to zero, should be decrypted");
	}

	/*** Test Saving Encrypted Field ***/
	public function testSave() { 

		$testValue = '~!@#$$%^&**(())_+';
		$partner = 'testSave';

		//creating an unencrypted setting and saving it
		$setting = new settingModel();
		$setting->partner = $partner;
		$setting->value = $testValue;
		$setting->save();

		//check mysql query
		$query = 'SELECT `value` FROM `settings` WHERE `id`=' . $setting->id;
		$result = db::query($query);
		$this->assertEquals(1, mysql_num_rows($result), "make sure we only have 1 row");
		$row = mysql_fetch_assoc($result);
		$this->assertEquals($testValue, $row['value'], "value in the db should be the same");

		//creating an encrypted setting and saving it
		$setting = new settingModel();
		$setting->partner = $partner;
		$setting->encrypted = 1;
		$setting->value = $testValue;
		$setting->save();

		//check the mysql query
		$query = 'SELECT `value` FROM `settings` WHERE `id`=' . $setting->id;
		$result = db::query($query);
		$this->assertEquals(1, mysql_num_rows($result), "make sure we only have 1 row");
		$row = mysql_fetch_assoc($result);
		$this->assertFalse(($testValue == $row['value']), "value in db should be encrypted");
		$this->assertEquals($testValue, baseModel::decrypt($row['value']), "decrypted value should be equal to our value");

		//if we save with an env but not a partner, should get an exception
		$exception = false;
		$setting = new settingModel();
		$setting->env = Env::main()->envName();
		try {
			$setting->save();
		}
		catch(Exception $e) { 
			$exception = true;
		}
		$this->assertTrue($exception);
		
	}

}
