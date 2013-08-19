<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class UtilityHelperTest extends PHPUnit_Framework_TestCase {

	public function testIsGetRequest(){
		$this->assertFalse(utilityHelper::isGetRequest());
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertFalse(utilityHelper::isGetRequest());
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->assertTrue(utilityHelper::isGetRequest());
	}
	
	public function testGetRequestType(){
		$_SERVER['REQUEST_METHOD'] = "POST";
		$this->assertEquals("POST", utilityHelper::getRequestType());
		
		$_SERVER['REQUEST_METHOD'] = "GET";
		$this->assertEquals("GET", utilityHelper::getRequestType());
		
		$_SERVER['REQUEST_METHOD'] = null;
		$this->assertEquals("", utilityHelper::getRequestType());
	}
	
	public function testPrintToPage(){
		$a = array("key" => "value");
		ob_start();
		utilityHelper::printToPage($a);
		$output = ob_get_clean();
		$this->assertEquals(40, strlen($output));
	}
	
	public function testCamelToSpace(){
		$thisIsCamelCased = 'thisIsCamelCased';
		$this->assertEquals('this Is Camel Cased', utilityHelper::camelToSpace($thisIsCamelCased));
	}
	
	public function testSlashToCamel(){
		$slash = 'this/is/going/to/camel';
		$this->assertEquals('thisIsGoingToCamel', utilityHelper::slashToCamel($slash));
	}
	
	public function testDirListing(){
		$dir = utilityHelper::dirListing(getcwd());
		$this->assertGreaterThan(0, count($dir));
	}
	
	public function testIsAjax(){
		$this->assertFalse(utilityHelper::isAjax());
		$_SERVER['HTTP_X_REQUESTED_WITH'] = "XMLHttpRequest";
		$this->assertTrue(utilityHelper::isAjax());
	}
	
	public function testCurrencyToSymbol(){
		$this->assertEquals('&pound;', utilityHelper::currencyToSymbol('GBP'));	// Great Britain pound
		$this->assertEquals('&euro;', utilityHelper::currencyToSymbol('EUR'));	// Euros
		$this->assertEquals('$', utilityHelper::currencyToSymbol('USD'));	// United States dollar
		$this->assertEquals('$', utilityHelper::currencyToSymbol('CAD'));	// Canadian dollar
		$this->assertEquals('&yen;', utilityHelper::currencyToSymbol('JPY'));	// Japanese Yen
		$this->assertEquals('&yen;', utilityHelper::currencyToSymbol('CNY'));	// Chinese Yuan
		$this->assertEquals('&#8377;', utilityHelper::currencyToSymbol('INR'));	// Indian Rupee
		$this->assertEquals('&#8355;', utilityHelper::currencyToSymbol('CHF'));	// Swiss Franc
		$this->assertEquals('kr', utilityHelper::currencyToSymbol('SEK'));	// Swedish krona
		$this->assertEquals('kr', utilityHelper::currencyToSymbol('DKK'));	// Danish krone
		$this->assertEquals('kr', utilityHelper::currencyToSymbol('NOK'));	// Norwegian krone
		$this->assertEquals('$', utilityHelper::currencyToSymbol('AUD'));	// Australian dollar
		$this->assertEquals('$', utilityHelper::currencyToSymbol('NZD'));	// New Zealand dollar
		$this->assertEquals('$', utilityHelper::currencyToSymbol(''));		// Default currency symbol
		$this->assertEquals('SAT', utilityHelper::currencyToSymbol('satish'));	// Satish's own currency symbol
	}
}
