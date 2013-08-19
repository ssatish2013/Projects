<?php

class userRoleModel extends userRoleDefinition {
	
	public static function getUserRoles($userId) {
		$userRoles = array();
		
		

		//just grab the role id
		$query = 'SELECT `roleId` FROM `userRoles` WHERE `userId`='.$userId.' AND `enabled`=1';
		$result = db::query($query);
		$partner = globals::partner();

		//loop through what we found
		while( $row = mysql_fetch_assoc( $result )) {

			//grab the role object and put it in our
			//return array
			$role = new roleModel();
			$role->id = $row['roleId'];

			if($role->load() && $role->status == 1) { 
				//show their role if there's...
				//no restricted partner
				//there's no partner set
				//the restricted partner is the same as the current one
				if((!$role->restrictedToPartner) || !$partner || $partner==$role->restrictedToPartner){
					$userRoles[] = $role;
				}
			}
		}
		return $userRoles;
	}

	public static function getAllUserRoles($userId) {
		$userRoles = array();
		
		

		//just grab the role id
		$query = 'SELECT `roleId` FROM `userRoles` WHERE `userId`='.$userId.' AND `enabled`=1';
		$result = db::query($query);

		//loop through what we found
		while( $row = mysql_fetch_assoc( $result )) {

			//grab the role object and put it in our
			//return array
			$role = new roleModel();
			$role->id = $row['roleId'];

			if($role->load() && $role->status == 1) { 
				$userRoles[] = $role;
			}
		}
		return $userRoles;
	}
}
