<?php
class adminMigrationHelper{
	public static function getEnvList(){
		$envs = Env::getEnvironmentList();
		$list = array();
		foreach($envs as $env=>$method){
			$e = new partialEnv($env);
			Env::swapMain($e);
			$type = $e->getEnvType();
			if(!isset($list[$type]) || !is_array($list[$type])){
				$list[$type]=array();
			}
			$list[$type][]=$e->getEnvName();
		}
		Env::restoreMain();
		return $list;
	}
	
	public static function moveSettings($src,$dst,$partner,$category,$key){
		$result = false;
		try{
			$srcEnv = new partialEnv($src);
			$dstEnv = new partialEnv($dst);


			Env::swapMain($srcEnv);
			$setting = new settingModel();
			$setting->category=$category;
			$setting->key=$key;
			if($partner){
				$setting->env=$src;
				$setting->partner=$partner;
			}
			if($setting->load('category,key,env,partner',"AND",false)){
				// The goal is to copy / update
				$doDelete = false;
			} else {
				// The goal is to delete
				$doDelete = true;
			}

			Env::swapMain($dstEnv);
			if($doDelete){
				$destroyedSetting = new settingModel();
				$destroyedSetting->category=$category;
				$destroyedSetting->key=$key;
				if($partner){
					$destroyedSetting->env=$dst;
					$destroyedSetting->partner=$partner;
				}
				$destroyedSetting->load('category,key,env,partner',"AND",false);
				$result=$destroyedSetting->destroy(true);
			} else {
				$newSetting = new settingModel();
				$newSetting->category=$category;
				$newSetting->key=$key;
				if($partner){
					$newSetting->env=$dst;
					$newSetting->partner=$partner;
				}
				// First try to load...
				$newSetting->load('category,key,env,partner',"AND",false);
				$newSetting->encrypted = $setting->encrypted;
				$newSetting->value = $setting->value;
				$result=$newSetting->save();
			}
		} catch(Exception $e){
			Env::restoreMain();
			return false;
		}
		
		Env::restoreMain();
		return $result;
	}
	
	public static function moveLanguage($src,$dst,$partner,$language,$name){
		$result = false;
		try{
			$srcEnv = new partialEnv($src);
			$dstEnv = new partialEnv($dst);

			Env::swapMain($srcEnv);
			$l = new languageModel();
			if($language != "[default]"){
				$l->language = $language;
			}
			$l->name = $name;
			if($partner){
				$l->partner = $partner;
			}
			if($l->load('language,name,partner',"AND",false)){
				// The goal is to copy / update
				$doDelete = false;
			} else {
				// The goal is to delete
				$doDelete = true;
			}
			
			Env::swapMain($dstEnv);
			if($doDelete){
				$dL = new languageModel();
				if($language != "[default]"){
					$dL->language = $language;
				}
				$dL->name = $name;
				if($partner){
					$dL->partner = $partner;
				}
				$dL->load('language,name,partner',"AND",false);
				$result=$dL->destroy(true);
			} else {
				$newL = new languageModel();
				if($language != "[default]"){
					$newL->language = $language;
				}
				$newL->name = $name;
				if($partner){
					$newL->partner = $partner;
				}
				// First try to load...
				$newL->load('language,name,partner',"AND",false);
				$newL->value = $l->value;
				$result=$newL->save();
			}

		} catch(Exception $e){
			Env::restoreMain();
			return false;
		}
		
		Env::restoreMain();
		return $result;
	}
	
	public static function compareEnvSettings($src,$dst){
		$srcEnv = new partialEnv($src);
		$dstEnv = new partialEnv($dst);
		
		$partner = globals::partner();
		if($partner){
			$partners = array($partner);
		} else {
			$partners = array();
			
			Env::swapMain($srcEnv);
			$query="SELECT DISTINCT `partner` FROM `settings` WHERE `partner` IS NOT NULL AND `partner` != ''";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				$partners[]=$row['partner'];
			}
			
			Env::swapMain($dstEnv);
			$query="SELECT DISTINCT `partner` FROM `settings` WHERE `partner` IS NOT NULL AND `partner` != ''";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				$partners[]=$row['partner'];
			}
			
			$partners = array_unique($partners);
		}
		
		$diffPartnerSettings = array();
		foreach($partners as $partner){
			// Partner Settings Src
			Env::swapMain($srcEnv);
			$srcDb = Env::getMasterDbName();
			$srcPartnerSettings = array();
			$query = "SELECT * FROM `settings` WHERE `partner`='".db::escape($partner)."' AND `env`='".db::escape($src)."'";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				if($row['encrypted']){
					$srcPartnerSettings[$row['category']."-.-".$row['key']]=baseModel::decrypt($row['value']);
				} else {
					$srcPartnerSettings[$row['category']."-.-".$row['key']]=$row['value'];
				}
			}


			// Partner Settings Dst
			Env::swapMain($dstEnv);
			$dstDb = Env::getMasterDbName();
			$dstPartnerSettings = array();
			$query = "SELECT * FROM `settings` WHERE `partner`='".db::escape($partner)."' AND `env`='".db::escape($dst)."'";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				if($row['encrypted']){
					$dstPartnerSettings[$row['category']."-.-".$row['key']]=baseModel::decrypt($row['value']);
				} else {
					$dstPartnerSettings[$row['category']."-.-".$row['key']]=$row['value'];
				}
			}

			$diffPartnerSettings[$partner] = array();
			foreach(array_merge(array_diff_assoc($srcPartnerSettings,$dstPartnerSettings),array_diff_assoc($dstPartnerSettings,$srcPartnerSettings)) as $key=>$value){
				list($category,$key) = explode("-.-",$key);
				if(!isset($diffPartnerSettings[$partner][$category])||!is_array($diffPartnerSettings[$partner][$category])){
					$diffPartnerSettings[$partner][$category]=array();
				}
				$diffPartnerSettings[$partner][$category][$key]=array(
						$srcPartnerSettings[$category."-.-".$key],
						$dstPartnerSettings[$category."-.-".$key]
				);
			}
			
			if(sizeof($diffPartnerSettings[$partner])==0){
				unset($diffPartnerSettings[$partner]);
			}
		
		}
		
		// Default Settings Src
		Env::swapMain($srcEnv);
		$srcDefaultSettings = array();
		$query = "SELECT * FROM `settings` WHERE `partner` IS NULL AND `env` IS NULL";
		$result = db::query($query);
		while($row = mysql_fetch_assoc($result)){
			if($row['encrypted']){
				$srcDefaultSettings[$row['category']."-.-".$row['key']]=baseModel::decrypt($row['value']);
			} else {
				$srcDefaultSettings[$row['category']."-.-".$row['key']]=$row['value'];
			}
		}
		
		// Default Settings Dst
		Env::swapMain($dstEnv);
		$dstDefaultSettings = array();
		$query = "SELECT * FROM `settings` WHERE `partner` IS NULL AND `env` IS NULL";
		$result = db::query($query);
		while($row = mysql_fetch_assoc($result)){
			if($row['encrypted']){
				$dstDefaultSettings[$row['category']."-.-".$row['key']]=baseModel::decrypt($row['value']);
			} else {
				$dstDefaultSettings[$row['category']."-.-".$row['key']]=$row['value'];
			}
		}
		
		$diffDefaultSettings = array();
		foreach(array_merge(array_diff_assoc($srcDefaultSettings,$dstDefaultSettings),array_diff_assoc($dstDefaultSettings,$srcDefaultSettings)) as $key=>$value){
			list($category,$key) = explode("-.-",$key);
			if(!isset($diffDefaultSettings[$category])||!is_array($diffDefaultSettings[$category])){
				$diffDefaultSettings[$category]=array();
			}
			$diffDefaultSettings[$category][$key]=array(
					$srcDefaultSettings[$category."-.-".$key],
					$dstDefaultSettings[$category."-.-".$key]
			);
		}
		
		$return = json_encode(array(
			"type"=>"settings",
			"partner"=>$diffPartnerSettings,
			"global"=>$diffDefaultSettings,
			"src"=>$src,
			"dst"=>$dst,
			"srcDb"=>$srcDb,
			"dstDb"=>$dstDb
		));
		
		Env::restoreMain();
		
		return $return;
	}
	
	public static function compareEnvLanguage($src,$dst){
		$srcEnv = new partialEnv($src);
		$dstEnv = new partialEnv($dst);
		
		$partner = globals::partner();
		if($partner){
			$partners = array($partner);
		} else {
			$partners = array();
			
			Env::swapMain($srcEnv);
			$query="SELECT DISTINCT `partner` FROM `languages` WHERE `partner` IS NOT NULL AND `partner` != ''";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				$partners[]=$row['partner'];
			}
			
			Env::swapMain($dstEnv);
			$query="SELECT DISTINCT `partner` FROM `languages` WHERE `partner` IS NOT NULL AND `partner` != ''";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				$partners[]=$row['partner'];
			}
			
			$partners = array_unique($partners);
		}
		
		$diffPartnerLanguage = array();
		foreach($partners as $partner){
			// Partner Language Src
			Env::swapMain($srcEnv);
			$srcDb = Env::getMasterDbName();
			$srcPartnerLanguage = array();
			$query = "SELECT * FROM `languages` WHERE `partner`='".db::escape($partner)."'";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				$srcPartnerLanguage[$row['language']."-.-".$row['name']]=$row['value'];
			}

			// Partner Language Dst
			Env::swapMain($dstEnv);
			$dstDb = Env::getMasterDbName();
			$dstPartnerLanguage = array();
			$query = "SELECT * FROM `languages` WHERE `partner`='".db::escape($partner)."'";
			$result = db::query($query);
			while($row = mysql_fetch_assoc($result)){
				$dstPartnerLanguage[$row['language']."-.-".$row['name']]=$row['value'];
			}


			$diffPartnerLanguage[$partner] = array();
			foreach(array_merge(array_diff_assoc($srcPartnerLanguage,$dstPartnerLanguage),array_diff_assoc($dstPartnerLanguage,$srcPartnerLanguage)) as $key=>$value){
				list($lang,$name) = explode("-.-",$key);
				if($lang==""){
					$lang="[default]";
				}
				if(!isset($diffPartnerLanguage[$partner][$lang])||!is_array($diffPartnerLanguage[$partner][$lang])){
					$diffPartnerLanguage[$partner][$lang]=array();
				}
				$diffPartnerLanguage[$partner][$lang][$name]=array(
						$srcPartnerLanguage[$key],
						$dstPartnerLanguage[$key]
				);
			}
			if(sizeof($diffPartnerLanguage[$partner])==0){
				unset($diffPartnerLanguage[$partner]);
			}
		}
		
		// Default Language Src
		Env::swapMain($srcEnv);
		$srcDefaultLanguage = array();
		$query = "SELECT * FROM `languages` WHERE `partner` IS NULL";
		$result = db::query($query);
		while($row = mysql_fetch_assoc($result)){
			$srcDefaultLanguage[$row['language']."-.-".$row['name']]=$row['value'];
		}
		
		// Default Language Dst
		Env::swapMain($dstEnv);
		$dstDefaultLanguage = array();
		$query = "SELECT * FROM `languages` WHERE `partner` IS NULL";
		$result = db::query($query);
		while($row = mysql_fetch_assoc($result)){
			$dstDefaultLanguage[$row['language']."-.-".$row['name']]=$row['value'];
		}
			
		$diffDefaultLanguage = array();
		foreach(array_merge(array_diff_assoc($srcDefaultLanguage,$dstDefaultLanguage),array_diff_assoc($dstDefaultLanguage,$srcDefaultLanguage)) as $key=>$value){
			list($lang,$name) = explode("-.-",$key);
			if($lang==""){
				$lang="[default]";
			}
			if(!isset($diffDefaultLanguage[$lang])||!is_array($diffDefaultLanguage[$lang])){
				$diffDefaultLanguage[$lang]=array();
			}
			$diffDefaultLanguage[$lang][$name]=array(
					$srcDefaultLanguage[$key],
					$dstDefaultLanguage[$key]
			);
		}
		
		$return = json_encode(array(
			"type"=>"language",
			"partner"=>$diffPartnerLanguage,
			"global"=>$diffDefaultLanguage,
			"src"=>$src,
			"dst"=>$dst,
			"srcDb"=>$srcDb,
			"dstDb"=>$dstDb
		));
		
		Env::restoreMain();
		
		return $return;
	}
}