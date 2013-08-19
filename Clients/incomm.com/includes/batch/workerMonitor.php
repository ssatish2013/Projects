<?php
require_once(dirname(__FILE__)."/../init.php");

log::info("Starting worker monitor");

$workersDir = workersHelper::getWorkers();
$emailWorkersDir = workersHelper::getEmailWorkers();

foreach ($workersDir as $workerDir) {
	//Get the file name
	$parts = explode('/', $workerDir);
	$workerFileName = end($parts); 
	$workerName = str_replace('.php', '', $workerFileName);
	if($workerName == 'baseWorker' || 
					$workerName == 'start' ||
					$workerName == 'stop'  ||
					$workerName == 'recover'){
		continue;
	}
	
	$worker = new $workerName();
	if(!$worker->getNumberOfWorkers()){
		chdir(Env::includePath() . '/..');
		exec("export FRESH_ENV='" . ENV::main()->envName() . "'; /usr/bin/php " . 'includes/workers/start.php ' . str_replace('Worker', '', $workerName) . ' 1 > /dev/null &');
	}
}

foreach ($emailWorkersDir as $workerDir) {
	//Get the file name
	$parts = explode('/', $workerDir);
	$workerFileName = end($parts); 
	$workerName = str_replace('.php', '', $workerFileName);
	if($workerName == 'baseWorker' || 
					$workerName == 'start' ||
					$workerName == 'stop'  ||
					$workerName == 'recover'){
		continue;
	}
	$worker = new $workerName();
	if(!$worker->getNumberOfWorkers()){
		chdir(Env::includePath() . '/..');
		exec("export FRESH_ENV='" . ENV::main()->envName() . "'; /usr/bin/php " . 'includes/workers/start.php ' . str_replace('Worker', '', $workerName) . ' 1 > /dev/null &');
	}
}
