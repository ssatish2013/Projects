<?php

class adminUsersHelper {
	
	public static function editUser() {
		self::setupUserForm(false);
	}
	
	public static function newUser() {
		self::setupUserForm(true);
	}
	
	protected static function setupUserForm($createNew=true) {
		
		// Grab form data
		$userId 	= request::unsignedPost('userId');
		$first		= request::unsignedPost("first");
		$last		= request::unsignedPost("last");
		$email		= request::unsignedPost("email");
		$roleId		= request::unsignedPost("role");
		$sameroles = request::unsignedPost("sameRoles");
		
		// Create user
		$user = new userModel();
		
		$loaded = null;
		if($userId != "") {
			$user->id = $userId;
			$loaded = $user->load();
		}
		else {
			$user->email		= $email;
			$loaded = $user->load('email');
		}
		if ($createNew && $loaded) {
			echo json_encode(array('errors' => array('email' => 'A user already exists with that email address')));
			return;
		}
		
		$user->firstName	= $first;
		$user->lastName		= $last;
		$errorMessages = array();
		if ($user->validateAllButPassword($errorMessages)) {
			$user->save();
		}
		else {
			echo json_encode(array('errors' => $errorMessages));
			return;
		}
		
		if(!$loaded) {
			$w = new passwordResetEmailWorker();
			$w->send(json_encode(array(
					'userId' => $user->id,
					'partner' => globals::partner(),
					'redirectLoader' => globals::redirectLoader()
			)));
		
			//assign roles if the "Has Same Role As Me" is checked
			if ($sameroles == '1'){
				$me = new userModel();
				$me->id = $_SESSION['userId'];
				if ($me->load()){
					$myroles = $me->getAllRoles();
					foreach($myroles as $myrole){
						$userRole = new userRoleModel();
						$userRole->userId = $user->id;
						$userRole->roleId = $myrole->id;
						$userRole->load();
						$userRole->enabled = $value;
						$userRole->save();
					}
				}
			}
		
		}
		
		
		$roles = roleModel::loadAll(array("status" => 1));
		foreach($roles as $role) {
			$value = request::unsignedPost("role_".$role->id);
			if(isset($value)) {
				$userRole = new userRoleModel();
				$userRole->userId = $user->id;
				$userRole->roleId = $role->id;
				$userRole->load();
				$userRole->enabled = $value;
				$userRole->save();
			}
		}
		
		$userArr = get_object_vars( $user );
		$userArr["roles"] = $user->getAllRoles();
		
		echo json_encode( $userArr );
	}

	public static function disableUser() {
		$userId = request::unsignedPost("userId");
		$userRoles = userRoleModel::loadAll(array(
			'userId' => $userId
		));

		foreach($userRoles as $userRole) {
			$userRole->enabled = 0;
			$userRole->save();
		}
		echo json_encode(array("userId" => $userId));
	}

}
