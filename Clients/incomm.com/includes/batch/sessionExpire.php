<?php
require_once(dirname(__FILE__)."/../init.php");

while(sessionStore::expire()) { 
	//expriring 50 entries at a time
}


