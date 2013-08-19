<?php
class partialEnv extends Env{
	public function __construct($envName){
		parent::__construct();
		$this->envName = $envName;
		
		// Include environment-specific settings
		if(isset(parent::$environmentSetup[$this->envName])){
			$envMethod = parent::$environmentSetup[$this->envName];
			if(method_exists($this,$envMethod)){
				$this->$envMethod();
			}
		}
	}
}