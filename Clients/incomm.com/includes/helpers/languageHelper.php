<?php
class languageHelper{
	
	public function __get($key){
		return languageModel::getString($key);
	}
}
