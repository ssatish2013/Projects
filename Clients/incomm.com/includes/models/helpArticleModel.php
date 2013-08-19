<?php
class helpArticleModel extends helpArticleDefinition {

	public static function getArticle($name, $lang = 'en') {

		$help = new helpArticleModel();
		$help->language = $lang;
		$help->name = $name;
		if(globals::partner() !== null) { 
			$help->partner = globals::partner();
		}


		//look for the partner specific one
		if($help->load('language,name,partner', 'AND', false)) { 
			return $help;
		}

		//try default
		$help->partner = null;
		if($help->load('language,name,partner', 'AND', false)) { 
			return $help;
		}
		return false;
	}
}
