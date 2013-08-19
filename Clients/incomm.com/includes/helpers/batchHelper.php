<?php

class batchHelper {
	public static function getBatchListing(){
		$dir = Env::main()->includePath() . '/batch/';
		return utilityHelper::dirListing($dir);
	}
}
