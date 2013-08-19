<?php

class logHelper {
	public static function formatEntry($d) {
		$date = date('Y-m-d H:i:s', $d->date);
		$level = $d->level;
		$ts = str_repeat(' ', strlen($date) + 7);
		$caller = '';
		if (isset($d->caller)) {

			$caller = $d->caller->class . $d->caller->type . $d->caller->function . '()' . ':' . $d->caller->line;

			//caller = doc.caller.file + ':' + doc.caller.line;
		}

		$level = str_pad(strtoupper($level), 5, ' ');
		$verbose = false;
		if ($verbose) {
			$props = array();
			if ($d->context) {
				foreach($d->context as $key=>$val)
					$props[] = "$key=$val";
			}
			$context = implode(',', $props);
			$t = "$date $level $caller $context\n";
			$t.= "$ts$d->message\n";
		} else {
			$t = "$date $level $caller $d->message\n";
		}
		if (isset($d->exception) && $d->exception) {
			$t.= "$ts" . $d->exception->message . "\n";
			$t.= "$tsthrown from " . $d->exception->file . ' (' . $d->exception->line . "):\n";
			for ($i=0;$i < count($d->exception->stackTrace); $i++) {
				$t.= "$ts " . $d->exception->stackTrace[i] . "\n";
			}

		}
		return $t;
	}

}


