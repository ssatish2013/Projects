<?php
class demoController {
	public static $defaultMethod = 'index';
	
	public function index(){
		$domainParts = explode(".",$_SERVER['HTTP_HOST']);
		view::Set('subdomain',$domainParts[0]);
		
		if(file_exists(Env::templatePath().'/demo/'.globals::partner().".tpl")){
			view::Render('demo/'.globals::partner().".tpl");
		} else {
			view::Render('demo/default.tpl');
		}
	}
}