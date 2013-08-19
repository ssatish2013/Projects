<?php
class redirectController{
	public function handler($controllerName,$methodName,$parameters){
		$settings = settingModel::getPartnerSettings(null, 'redirect');
		$redirectUrl = isset($settings['baseUrl']) ? $settings['baseUrl'] : "";
		
		if(isset($controllerName)){
			$redirectUrl.="/".$controllerName;
			if($methodName){
				$redirectUrl.="/".$methodName;
				if($parameters){
					foreach($parameters as $k=>$v){
						$redirectUrl.="/$k/$v";
					}
				}
			}
		}
		
		view::ExternalRedirect($redirectUrl.($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
	}
}