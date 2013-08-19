<?php
require_once(dirname(__FILE__)."/../init.php");

$urlSettings = settingModel::getPartnerSettings(null, 'redemptionImportPathUrl');

foreach($urlSettings as $key=>$value){
	$worker = new redemptionImportWorker();
	$worker->send($key);
}