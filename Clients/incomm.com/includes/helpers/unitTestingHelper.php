<?php

class unitTestingHelper {
	function getAllTables(){
		$tables = array();
		$result=db::query('show full tables where Table_Type = "BASE TABLE"');  // Don't return database views
		while($row=mysql_fetch_row($result)){
			$tables[]=$row[0];
		}
		return $tables;
	}
	
	function validateStructure($modelName){
		$result = db::query('describe ' . $modelName . 's');
		$table = array();
		while($i = mysql_fetch_assoc($result)){
			$field = $i['Field'];
			$type = $i['Type'];
			foreach ($i as $key => $value) {

				//if it's an integer, we want to compare against an integer
				if(strtolower($key) == 'default' && $value !== null && preg_match('/int/',$type)) {
					$value = intval($value);
				}

				if($key != 'Field' && ($value || $value === "0")){ // Make sure a value exists and the field name is not Field
					$table[$field][strtolower($key)] = $value;
				}
			}
		}

		// $table now has the struture that is in the DB
		
		$modelDef = $modelName . 'Model';
		$modelHolder = new $modelDef;
		$model = array();
		foreach ($modelHolder->dbFields as $fieldKey => $fieldValue) {
			// Checking for Digest and Crypt 
			if(isset($fieldValue['scheme'])){
				foreach ($fieldValue['scheme'] as $key => $value) {
					if($value || $value === "0"){
						$model[($fieldKey)][$key] = $value;
					}
				}
			}
			if(isset($fieldValue['encryptScheme'])){
				foreach ($fieldValue['encryptScheme'] as $key => $value) {
					if($value || $value === "0"){
						$model[($fieldKey. 'Crypt')][$key] = $value;
					}
				}
			}
			if(isset($fieldValue['digestScheme'])){
				foreach ($fieldValue['digestScheme'] as $key => $value) {
					if($value || $value === "0"){
						$model[($fieldKey. 'Digest')][$key] = $value;
					}
				}
			}
		}

		foreach ($model as $key => $value){
			if(count(array_diff($value, $table[$key]))){
				echo "\nModel: $modelDef, property: $key \n";
				print_r(array_diff($value, $table[$key]));
				return FALSE;
			}
		}
		
		foreach ($table as $key => $value){
			if(count(array_diff($value, $model[$key]))){
				echo "\nTable: $modelDef, property: $key \n";
				print_r(array_diff($value, $model[$key]));
				return FALSE;
			}
		}

		return true;
	}

	function fraudSetup() { 
    $setting = new settingModel();
    $setting->key = 'startScore';
    $setting->category = 'fraudDecisionSetting';
    $setting->partner = globals::partner();
    $setting->value = 100;
    $setting->save();

    $setting = new settingModel();
    $setting->key = 'lowWeight';
    $setting->category = 'fraudDecisionSetting';
    $setting->partner = globals::partner();
    $setting->value = 10;
    $setting->save();

    $setting = new settingModel();
    $setting->key = 'medWeight';
    $setting->category = 'fraudDecisionSetting';
    $setting->partner = globals::partner();
    $setting->value = 25;
    $setting->save();

    $setting = new settingModel();
    $setting->key = 'highWeight';
    $setting->category = 'fraudDecisionSetting';
    $setting->partner = globals::partner();
    $setting->value = 50;
    $setting->save();
	}

}
