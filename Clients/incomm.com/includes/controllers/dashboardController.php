<?php

class dashboardController {

	public static $defaultMethod = 'workers';
	public $ajax;

	public function __construct() {
		$this->ajax = utilityHelper::isAjax();
		if(Env::getEnvType() == 'production'){
			view::Redirect('/');
		}
	}

	public function workersGet() {
		view::Redirect('dashboard/internalTools');
	}
	
	public function internalToolsGet(){
		$workersDir = workersHelper::getWorkers();
		$emailWorkersDir = workersHelper::getEmailWorkers();
		$batchDir = batchHelper::getBatchListing();
		
		$workersInfo = array();
		$emailWorkersInfo = array();
		$batchInfo = array();
		
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
			$a = array();
			$a['name'] = $workerName;
			$worker = new $workerName();
			$a['count'] = $worker->getNumberOfWorkers();
			$workersInfo[] = $a;
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
			$a = array();
			$a['name'] = $workerName;
			$worker = new $workerName();
			$a['count'] = $worker->getNumberOfWorkers();
			$emailWorkersInfo[] = $a;
		}
	
		foreach ($batchDir as $batch) {
			//Get the file name
			$parts = explode('/', $batch);
			$batchFileName = end($parts);
			$batchFileName = str_replace('.php', '', $batchFileName);
			$a = array();
			$a['name'] = $batchFileName;
			$batchInfo[] = $a;
		}

		if ( $this->ajax ) {
			echo json_encode(array( true ));
		} else {
			view::Set('batchInfo', $batchInfo);
			view::Set('workerInfo', $workersInfo);
			view::Set('emailWorkerInfo', $emailWorkersInfo);
			view::Render('dashboard/internalTools');		
		}
	}
	
	public function stopWorkerPost(){
		$workerType = request::unsignedPost('workerName');
		$worker = new $workerType();
		$count = $worker->getNumberOfWorkers();

		// Output buffering to stop stdout that get mixed up with json
		ob_start();
		$worker->stopWorker($count);
		ob_end_clean();

		if ( $this->ajax ) {
			echo json_encode(array(
				"count" => $worker->getNumberOfWorkers()
			));
		} else {
			view::Redirect('dashboard/internalTools');
		}
	}
	
	public function startWorkerPost(){
		$workerType = request::unsignedPost('workerName');
		$workerName = str_replace('Worker', '', $workerType);
		chdir(Env::includePath() . '/..');
		exec("export FRESH_ENV='" . ENV::main()->envName() . "'; /usr/bin/php " . 'includes/workers/start.php ' . $workerName . ' 1 > /dev/null &');
		// Give the worker a second to start ;)
		sleep(1);

		if ( $this->ajax ) {
			$worker = new $workerType();
			echo json_encode(array(
				"count" => $worker->getNumberOfWorkers()
			));
		} else {
			view::Redirect('dashboard/internalTools');
		}
	}
	
	public function restartWorkerPost(){
		$workerType = request::unsignedPost('workerName');
		$worker = new $workerType();
		$count = $worker->getNumberOfWorkers();

		// Output buffering to stop stdout that get mixed up with json
		ob_start();
		$worker->stopWorker($count);
		ob_end_clean();

		$workerName = str_replace('Worker', '', $workerType);
		chdir(Env::includePath() . '/..');
		exec("export FRESH_ENV='" . ENV::main()->envName() . "'; /usr/bin/php " . 'includes/workers/start.php ' . $workerName . ' 1 > /dev/null &');
		if ( $this->ajax ) {
			echo json_encode(array( true ));
		} else {
			view::Redirect('dashboard/internalTools');
		}
	}

	public function restartAllPost(){
		workersHelper::restartAllWorkers();

		if ( $this->ajax ) {
			echo json_encode(array( true ));
		} else {
			view::Redirect('dashboard/internalTools');
		}
	}
	
	public function startbatchPost(){
		$batchName = request::unsignedPost('batchName');
		$batchName = $batchName . '.php';
		chdir(Env::includePath() . '/..');
		exec("export FRESH_ENV='" . ENV::main()->envName() . "'; /usr/bin/php " . 'includes/batch/' . $batchName . ' > /dev/null');
		
		if ( $this->ajax ) {
			echo json_encode(array( true ));
		} else {
			view::Redirect('dashboard/internalTools');
		}
	}
}
