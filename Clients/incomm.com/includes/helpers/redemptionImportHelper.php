<?php
class redemptionImportHelper{
	public static function parseCsv($key,$csv){
		$tmpFile = tempnam(sys_get_temp_dir(),"csv_");
		
		file_put_contents($tmpFile,$csv);

		ini_set("auto_detect_line_endings",true);
		$csv = new csv;
		$csv->load($tmpFile);

		$csv->connect();

		$h = $csv->getHeaders();

		$panKey = array_search(settingModel::getSetting('redemptionImportPanKey', $key),$h);
		$dateKey = array_search(settingModel::getSetting('redemptionImportDateKey', $key),$h);
		$timeKey = array_search(settingModel::getSetting('redemptionImportTimeKey', $key),$h);
		
		if($panKey===false || $dateKey === false || $timeKey === false){
			return false;
		}
		
		$products = explode(',',settingModel::getSetting('redemptionImportProducts', $key));
		
		for($i=0;$i<=$csv->countRows();$i++){
			$row = $csv->getRow($i);
			
			if($row[$panKey] && $row[$dateKey] && $row[$timeKey]){
				
				$inventory = new inventoryModel();
				$inventory->pan = str_pad($row[$panKey], settingModel::getSetting('redemptionImportPanPad', $key), '0', STR_PAD_LEFT);
				$inventory->productId = $products;
				
				log::info("Load query: " . $inventory->getLoadQuery());

				if($inventory->load()){
					// Because incomm uses eastern time, grrr...
					date_default_timezone_set("America/New_York");
					$time=strtotime(substr($row[$dateKey],3,3).substr($row[$dateKey],0,3).substr($row[$dateKey],6)." ".$row[$timeKey]);
					date_default_timezone_set("GMT+0");

					// Because incomm uses a date format of m-d-Y for some strange reason, grrr...
					$timestamp = date("Y-m-d H:i:s",$time);

					$red = new externalRedemptionModel();
					$red->inventoryId=$inventory->id;
					$red->load();
					$red->redemptionTime = $timestamp;
					$red->save();
					new eventLogModel("import", "redemption", $inventory->id);

				}
				
			}
		}
	}
}
