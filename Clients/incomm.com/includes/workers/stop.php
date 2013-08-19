<?php
require_once(dirname(__FILE__)."/../init.php");

/* MAIN PROCESSING - starting and stopping the worker below */
$workerName = '';
if(isset($argv[1])) { 
	$workerName = $argv[1];
}

if($workerName == '') { 
	die("You need to specify a worker to stop!\n");
}

$num = 1;
if(isset($argv[2])) { 
$num = $argv[2];
}

//create a new worker object
$workerClass = $workerName.'Worker';
$worker = new $workerClass();

$worker->stopWorker($num);
