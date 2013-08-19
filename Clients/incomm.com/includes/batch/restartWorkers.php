<?php
require_once(dirname(__FILE__)."/../init.php");

workersHelper::restartAllWorkers();

echo "\n\nAll workers restarted :-)\n\n";