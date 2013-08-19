<?php

/**
 * CLI tool io stream plugin
 * 
 * @category giftingapp
 * @package libtool.cli
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

// Package libtool.cli
namespace libtool\cli;

class ioStream {
	public function __construct() {
		
	}  //end __construct

	public function stderr($err) {
		$message = '';
		if (is_object($err)) {
			$message = PHP_EOL . '[[EXCEIPTION]]' . PHP_EOL
				. '- Caught an exception from file ' . $err->getFile() . ' on line ' . $err->getLine() . PHP_EOL
				. '- Stack trace:' . PHP_EOL
				. '  ' . str_replace(PHP_EOL, PHP_EOL.'  ', $err->getTraceAsString()) . PHP_EOL
				. '- Message:' . PHP_EOL
				. '  ' . str_replace(PHP_EOL, PHP_EOL.'  ', $err->getMessage()) . PHP_EOL
				. '[[/EXCEIPTION]]' . PHP_EOL . PHP_EOL;
		} elseif (is_string($err)) {
			$message = 'Error: ' . $err . PHP_EOL;
		}
		fwrite(STDERR, $message);
	}  //end stderr

	public function stdout($out) {
		fwrite(STDOUT, $out . PHP_EOL);
	}  //end stdout

}  //end ioStream
