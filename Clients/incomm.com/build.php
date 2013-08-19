<?php

$VERSION="2.0.0";
$NAME="giftingapp";

system("rm -f webroot/js/all-*.js");
//download the latest fb all.js
system("wget -O webroot/js/fb-en.us.all.js http://connect.facebook.net/en_US/all.js");

print "Creating webroot/js/all-debug.js\n";
$jsFiles = file("webroot/js/package-all.txt");
foreach ($jsFiles as $file) {
	$file = trim($file);
	if ($file && $file[0] != '#') {
		system("cd webroot/js && cat $file >> all-debug.js");
	}
}

print "Minifying all-debug.js to all.js.\n";
system("(cd webroot/js && java -jar ../../yuicompressor-2.4.7.jar all-debug.js -o all-$VERSION.js)");

