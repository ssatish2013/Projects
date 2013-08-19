<?php

require_once(dirname(__FILE__) . "/../init.php");

class redemptionImportWorker extends baseWorker implements worker {

	protected $queueName = 'redemptionImportQueue';
	protected $routingKey = 'redemptionImport';

	public function doWork($content) {
		log::info("Attempting to import logs for $content");
		$importUrl = settingModel::getSetting('redemptionImportPathUrl', $content);
		$fileRegEx = settingModel::getSetting('redemptionImportFileRegEx', $content);
		$products = settingModel::getSetting('redemptionImportProducts', $content);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $importUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FTP_USE_EPSV, 1);

		$res = curl_exec($ch);

		preg_match_all("/((Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+[0-9]+\s+[0-9]+:[0-9]+\s+)($fileRegEx)/", $res, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$updated = date("Y-m-d H:i:s", strtotime($match[1]));
			$filename = $match[3];

			$needsUpdate = false;

			$rFile = new externalRedemptionFileModel();
			$rFile->filename = $filename;
			$rFile->path = $importUrl;
			if (!$rFile->load()) {
				$needsUpdate = true;
			} else if (strtotime($rFile->lastUpdated) != strtotime($updated)) {
				$needsUpdate = true;
			}

			if ($needsUpdate) {
				$rFile->lastUpdated = $updated;
				$rFile->save();

				$worker = new redemptionImportCsvWorker();
				$worker->send(json_encode(array(
					"key" => $content,
					"filename" => $filename
				)));

				log::info("Sending $filename to CSV worker");
			}
		}
	}
}
