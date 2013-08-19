<?php

require_once(dirname(__FILE__)."/../init.php");

class redemptionImportCsvWorker extends baseWorker implements worker {

	protected $queueName = 'redemptionImportCsvQueue';
	protected $routingKey = 'redemptionImportCsv';	

	public function doWork($content) {
		log::error("Attempting to import logs for $content");
		$content = json_decode($content, true);
		
		$importUrl = settingModel::getSetting('redemptionImportPathUrl', $content['key']);
		$filename = $content['filename'];
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$importUrl.$filename);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_FTP_USE_EPSV,1);

		$csv = curl_exec($ch);
		
		redemptionImportHelper::parseCsv($content['key'],$csv);
	}
}
