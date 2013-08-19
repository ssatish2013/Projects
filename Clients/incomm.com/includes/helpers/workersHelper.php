<?php
class workersHelper {
	private static $timestamp = null;
	
	public static function getWorkers(){
		$dir = Env::main()->includePath() . '/workers/';
		return utilityHelper::dirListing($dir);
	}
	
	public static function getEmailWorkers(){
		$dir = Env::main()->includePath() . '/workers/email/';
		return utilityHelper::dirListing($dir);
	}
	
	public static function restartAllWorkers(){
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
			$count = $worker->getNumberOfWorkers();
			$worker->stopWorker($count);
			$workerName = str_replace('Worker', '', $workerName);
			chdir(Env::includePath() . '/..');
			exec('/usr/bin/php ' . 'includes/workers/start.php ' . $workerName . ' 1 > /dev/null &');
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
			$count = $worker->getNumberOfWorkers();
			$worker->stopWorker($count);
			$workerName = str_replace('Worker', '', $workerName);
			chdir(Env::includePath() . '/..');
			exec('/usr/bin/php ' . 'includes/workers/start.php ' . $workerName . ' 1 > /dev/null &');
		}
	}
}
