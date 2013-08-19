<?php
abstract class ipHelper extends baseModel {
	protected static function ipToInt ($ip) {
		// converts an IPv4 address to an integer value that can be searched in the table easily
		$ipParts = explode('.', $ip);
		return	(int) (16777216 * $ipParts[0]) +
				(65536 * $ipParts[1]) +
				(256 * $ipParts[2]) +
				$ipParts[3];
	}
}