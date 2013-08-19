#!/usr/bin/php
<?php
chdir (dirname (__FILE__) . "/../..");
require_once("./includes/init.php");
if (isset ($argv[1]) && $argv[1] == "-h") {
	echo "Command Usage:\n";
	echo __FILE__ . " [--level=debug] [--date-from='1 minutes ago'] [--match=key=value] [--nofollow] [--verbose]\n";
	echo "	Example Match Key/Value Pairs:\n";
	echo "	--match=env=production\n";
	echo "See Also: https://dev.giftingapp.com/index.php/Logging\n";
	exit (0);
}
interface Formatter {
	function format($entry);
}

class SimpleFormatter implements Formatter {
	public $verbose = false;

	function format($entry) {
		$d = $entry;
		$verbose = $this->verbose;

		$date = date('Y-m-d H:i:s', (isset($d['date']) && is_object($d['date'])) ? $d['date']->sec : -1);
		$level = isset($d['level']) ? $d['level'] : '[null]';
		//$ts = str_repeat(' ', strlen($date) + 7);
		$ts = str_repeat(' ', 4);
		$caller = '';
		if (isset($d['caller'])) {

			//$caller = $d['caller']['class'] . $d['caller']['type'] . $d['caller']['function'] . '()' . ':' . $d['caller']['line'];

			$caller = $d['caller']['file'] . ':' . $d['caller']['line'];
		}

		$level = str_pad(strtoupper($level), 1, ' ');
		if ($verbose) {
			$props = array();
			if ($d['context']) {
				foreach($d['context'] as $key=>$val)
					$props[] = "$key=$val";
			}
			$context = implode(',', $props);
			print "$date $level $caller $context\n";
			print "$ts$d[message]\n";
		} else {
			$message = $d["message"];
			// Add some padding for multiline logs.
			$message = str_replace("\n", "\n    ", $message);
				
			print "$date $level $caller $message\n";
		}

		if (isset($d['exception']) && $d['exception']) {
			$message = isset($d['exception']['message']) ? $d['exception']['message'] : null;
			$className = $d['exception']['className'];
			print "$ts$className: $message\n";
			print $ts . "thrown from " . $d['exception']['file'] . ' (' . $d['exception']['line'] . "):\n";

			for ($i=0;$i < count($d['exception']['stackTrace']); $i++) {
				print "$ts" . $d['exception']['stackTrace'][$i] . "\n";
			}
		}
	}
}


class Tailer {
	private $mongoDb;

	public $criterion = array();
	public $level = null;
	public $verbose = false;
	public $dateFrom = null;

	public function __construct(MongoDb $mongo) {
		$this->mongoDb = $mongo;
	}

	public function tail() {
		$mongoDb = $this->mongoDb;

		$formatter = new SimpleFormatter();
		$formatter->verbose = $this->verbose;

		// Fetch the previous 5 entries
		$logs = $mongoDb->logs;

		print "Using mongo collection \"logs\" which has " . $logs->count() . " documents.\n";

		$levels = array('debug', 'info', 'warn', 'error', 'fatal');
		if ($this->level == 'info') {
			$levels = array('info', 'warn', 'error', 'fatal');
		} else if ($this->level == 'warn') {
			$levels = array('warn', 'error', 'fatal');
		} else if ($this->level == 'error') {
			$levels = array('error', 'fatal');
		}
		$query = array('level' => array('$in' => $levels));
		$limit = 10;
		if ($this->dateFrom) {
			print "Tailing from: ".date('r', $this->dateFrom) . "\n";
			$query['date'] = array('$gte' => new MongoDate($this->dateFrom));
			$limit = 10000;
		}

		/*
		 *
		*/
		foreach ($this->criterion as $criteria) {
			$matchType = $criteria[0];
			list($key, $value) = explode('=', substr($criteria, 1));
			// print "$key = $value - $criteria\n";
			$query[$key] = $value;
		}

		$cursor = $logs->find($query)->sort(array('$natural' => -1))->limit($limit);

		$lastDate = time();
		$docs = array();
		while($cursor->hasNext()) {
			$doc = $cursor->getNext();
			$docs[] = $doc;
		}
		$docs = array_reverse($docs);
		foreach ($docs as $doc) {
			$formatter->format($doc);
			$lastDate = $doc['date']->sec;
		}

		if ($this->follow) {
			$this->tailFrom($logs, $lastDate, $query, $formatter);
		}
	}


	function tailFrom($coll, $lastDate, $query, Formatter $formatter) {
		if (!$lastDate) {
			$cursor = $coll->find($query)->sort(array('$natural' => -1))->limit(1);
			$lastDate = time();
			if ($cursor->hasNext()) {
				$doc = $cursor->getNext();
				$lastDate = $doc['date']->sec;
			}
		}

		$lastMongoDate = new MongoDate($lastDate);
		$cursor = $coll->find(
				array_merge($query,
						array(
								'date' => array(
										'$gte' => $lastMongoDate
								))
				)
		);

		try {
			$cursor = $cursor->tailable(true);
			while (true) {
				if ($cursor->hasNext()) {
					$doc = $cursor->getNext();
					$formatter->format($doc);
				} else {
					sleep(1);
				}
			}
		} catch(MongoCursorException $e) {
			print "ERROR: " . $e->getMessage() . "\n";
		}
	}
}

$mongoDb = Env::mongoDb();

$opts = getopt("", array('env:', 'nofollow', 'verbose', 'level:', 'date-from:', 'match:'));

$level = (isset($opts['level']) && $opts['level']) ? $opts['level'] : 'debug';
$dateFrom = isset($opts['date-from']) ? strtotime($opts['date-from']) : null;
$follow = isset($opts['nofollow']) ? false : true;
$matches = isset($opts['match']) ? is_array($opts['match']) ? $opts['match'] : array($opts['match']) : array();
$criterion = array();
foreach($matches as $match) {
	$criterion[] = "=$match";
}

$tailer = new Tailer($mongoDb);
$tailer->follow = isset($opts['nofollow']) ? false : true;
$tailer->criterion = $criterion;
$tailer->level = strtolower($level);
$tailer->dateFrom = $dateFrom;
$tailer->tail();
