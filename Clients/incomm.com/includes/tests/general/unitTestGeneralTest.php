<?php

require_once getenv("PWD") . '/includes/init.php';
ENV::includeTest('setup');

class UnitTestGeneralTest extends PHPUnit_Framework_TestCase {
	public function testModelCoverage(){
		foreach(unitTestingHelper::getAllTables() as $table){
			//$testFile = Env::includePath()."/tests/models/".(substr($table,0,-1))."Test.php";
			//$this->assertFileExists($testFile);
			
			$uth = new unitTestingHelper();
			try { 
				$this->assertTrue($uth->validateStructure(substr($table,0,-1)));
			}
			catch(Exception $e) { 
				echo "------------------------------------\n";
				echo "Structure mismatch on --- " . strtoupper($table).  " ---\n";
				echo "____________________________________\n";
				throw $e;
			}
		}
	}
}
