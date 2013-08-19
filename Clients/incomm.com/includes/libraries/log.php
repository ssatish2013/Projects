<?php
/**
 * Global logger class.
 */
class log {
	/* The default LogEntry, this object is cloned for each log statement. */
	public static $defaultEntry;

	public static $appendFunction = 'log::fileAppender';

	public static $file = null;
	
	private static $uname = null;

	public static function debug($message, Exception $exception = null) {
		$entry = self::newDefaultEntry('debug', $message, $exception);
		self::process($entry);
	}

	public static function warn($message, Exception $exception = null) {
		$entry = self::newDefaultEntry('warn', $message, $exception);
		self::process($entry);
	}

	public static function error($message, Exception $exception = null) {
		$entry = self::newDefaultEntry('error', $message, $exception);
		self::process($entry);
	}

	public static function fatal($message, Exception $exception = null) {
		$entry = self::newDefaultEntry('fatal', $message, $exception);
		self::process($entry);

	}

	public static function info($message, Exception $exception = null) {
		$entry = self::newDefaultEntry('info', $message, $exception);
		self::process($entry);
	}

	private static function process(LogEntry $entry) {
		if ($entry->exception) {
			$entry->exception = self::exception2Object($entry->exception);
		}

		$entry->date = time();

		$bt = debug_backtrace();
		$caller = new stdClass();
		$stack = $bt[1];
		$caller->file = $stack['file'];
       	$caller->function = $stack['function'];
       	$caller->line = $stack['line'];
       	$caller->type = isset($stack['type']) ? $stack['type'] : '';
       	$caller->class = isset($stack['class']) ? $stack['class'] : '';
		$entry->caller = $caller;

		call_user_func(self::$appendFunction, $entry);
	}

	private static function newDefaultEntry($level, $message, $exception) {
		$entry = clone self::$defaultEntry;
		$entry->level = $level;
		$entry->env = Env::main()->envName();
		$entry->message = $message;
		$entry->exception = $exception;

		$program = php_sapi_name() == 'cli' ? php_sapi_name() : php_sapi_name();

		$entry->program = $program;
		$entry->host = gethostname();
		
		return $entry;
	}

	public static function exception2Object(Exception $e) {
		$ex = new stdClass();
		$ex->message = $e->getMessage();

		// manually add the first file/line since the stack trace doesn't include it
		$ex->file = $e->getFile();
		$ex->line = $e->getLine();
		$ex->className = get_class($e);
		$ex->stackTrace = preg_split("/\n/", $e->getTraceAsString());
		return $ex;
	}

	public static function phpAppender($entry) {
		$m = "$entry->message";
        if ($entry->exception) {
            $e = $entry->exception;
            $m.= " - $e->className: $e->message\n";
        }
	
		$type = E_USER_ERROR;
		if (in_array($entry->level, array('debug', 'info'))) {
			$type = E_USER_NOTICE;	
		}
		trigger_error($m, $type);
	}

	public static function fileAppender($entry) {
		$file = self::$file;
		$s = date('Y-m-d H:i:s', $entry->date) . ' ' . self::entryToString($entry);
		file_put_contents($file, $s, FILE_APPEND);
	}

	public static function entryToString($entry) {
		$program = '-';
		$partner = '-';
		if (isset($entry->context) && isset($entry->context->program)) {
			$program = $entry->context->program;	
		}
		if (isset($entry->context) && isset($entry->context->partner)) {
			$partner = $entry->context->partner;
		}
		$caller = $entry->caller;
		$file = basename($caller->file);
		$level = strtoupper($entry->level);
		$m = "$level $program $partner $file:$caller->line $entry->message\n";
        if ($entry->exception) {
			$e = $entry->exception;
            $m.= "    $e->className: $e->message\n";
            // manually add the first file/line since the stack trace doesn't include it
            $m.= "    thrown from ".$e->file . "(" . $e->line . "):\n";
			foreach ($e->stackTrace as $stack) {
				$m.= "    $stack\n";
			}
        }
		return $m;
	}

	public static function mongoEntry($entry) {
		$entry->date = new MongoDate(); // Store in ms since epoch.
		return $entry;
	}
}

class LogEntry {
	/* This contains contextual information for log entries, e.g., current user, or program  being run */
	public $context = null;

	public $date = null;
	public $level = null;
	public $message = null;

	public $exception = null;

	public $caller = null;
}

/* Set the default entry */
log::$defaultEntry = new LogEntry();
log::$defaultEntry->context = new stdClass();
