<?php

require_once getenv("PWD") . '/includes/init.php';

//class TestSetup extends UnitTestCase {
class SetupTest {
	public static $isSetup = 0;

	function  __construct() {
		if(SetupTest::$isSetup == 1){
			return;
		}
		error_reporting(E_ALL);
		ini_set('display_errors','On');
		$tables = array();
		$sql = array();

		/*
		 * Build all the sql to recreate the database
		 */
		$query = 'SHOW TABLES';
		$result = db::query($query);
		while($i = mysql_fetch_array($result)){
			if ($i[0] == "cstLogs") continue;
			$tables[] = $i[0];
			$q = "SHOW CREATE TABLE `" . $i[0] . "`";
			$r = db::query($q);
			while($s = mysql_fetch_array($r)){
				$sql[] = $string=preg_replace("/AUTO_INCREMENT=[0-9]+/i","",$s[1]);
			}
		}

		/*
		 * Select the testing db
		 */
		$testConnection = mysql_connect('sql1.int.groupcard.com', 'giftingapp-test', 'test-password');
		if(mysql_select_db('test-' . ENV::main()->envName())){
			db::$unitTestDB = $testConnection;
			/*
			 * Drop all the old tables so we can start fresh
			 */
			foreach ($tables as $table){
				$query = "DROP TABLE IF EXISTS `$table`";
				try{
					db::query($query);
				} catch (Exception $e){
					echo $e;
				}
				echo mysql_error();
			}

			/*
			 * Rebuld the table
			 */
			foreach ($sql as $query){
				db::query($query);
				echo mysql_error();
			}

			self::$isSetup = 1;
			globals::isUnitTest(true);
		} else {
			echo 'Oh shit, good thing I was checking to see if we selected the test db';
			die();
		}

		/*
		* Mongo DB
		*/
		dbMongo::$unitTestDB = 'test-'.ENV::main()->envName();
		$testDbName = dbMongo::$unitTestDB;

		//sropping old collections
		$mongo = ENV::mongoConn();
		$db = $mongo->$testDbName;

		//grab list of collections and drop them
		$collections = $db->listCollections();
		foreach($collections as $collection) {
			$collection->drop();
		}
	}
}


//mock server settings
$_SERVER['HTTP_X_REAL_IP'] = '96.60.102.154';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.65 Safari/534.24';

new SetupTest();
