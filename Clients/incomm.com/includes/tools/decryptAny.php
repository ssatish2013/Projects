#!/usr/bin/php
<?php
chdir (dirname (__FILE__) . "/../..");
require_once ("./includes/init.php");

$encryption = '';
if (isset($argv[1]) && $argv[1] == "-h") {
	echo "Command Options:\n";
	echo __FILE__ . " encryptedValue\n";
	echo "Note: You need to escape all exclamation points (!) in the encryptedValue input by prepending all exclamation points (!) with a backslash (\\).\n";
	echo "See Also: https://dev.giftingapp.com/index.php/PII_Data_Encrytion/Decryption\n";
	exit (0);
} else if (isset($argv[1])) {
	$encryption = $argv[1];
} else {
	echo "Invalid command.\nTry: " . __FILE__ . " -h\n";
	exit (1);
}

$decryption = baseModel::decrypt($encryption);

echo PHP_EOL, $decryption, PHP_EOL, PHP_EOL;
