<?php

class adminPermissionsHelper {

	public static function newRole() {

		// Grab form data
		$name= request::unsignedPost("name");
		$restrictedToPartner = request::unsignedPost("restrictedToPartner");
		$id = request::unsignedPost("id");
		$description = request::unsignedPost("description");

		// Create user
		$role = new roleModel();
		$isNew = 1;
		if($id > 0) {
			$role->id = $id;
			$role->load();
			$isNew = 0;
		}
		$role->name = $name;
		$role->description = $description;
		if($restrictedToPartner != "") { 
			$role->restrictedToPartner = $restrictedToPartner;
		}
		else {
			$role->restrictedToPartner = NULL;
		}
		$role->save();

		$roleArr = get_object_vars( $role);
		$roleArr['isNew'] = $isNew;


		//save permissions
		$permissions = permissionModel::loadAll(array("status" => 1));
		foreach($permissions as $permission) { 
			$key = $permission->key;
			$value = request::unsignedPost($key);

			$rolePermission = new rolePermissionModel();
			$rolePermission->roleId = $id;
			$rolePermission->permissionId = $permission->id;
			$rolePermission->load('roleId,permissionId');
			$rolePermission->value = $value;
			$rolePermission->save();
		}

		echo json_encode( $roleArr );
	}

	public static function roleStatusUpdate() {
		$roleId = request::unsignedPost("roleId");
		$role = new roleModel($roleId);
		$status = $role->status;

		if($role->status) { 
			$role->status = 0;
		}
		else {
			$role->status = 1;
		}
		$role->save();
		echo json_encode(array("status" => $role->status));
	}

	public static function getRolePermissions() { 
		$roleId = request::unsignedPost("roleId");
		$role = new roleModel($roleId);
		$rolePermissions = array();
		foreach(rolePermissionModel::loadAll(array("roleId" => $roleId)) as $rolePermission) { 
			if($rolePermission->value) { 
				$rolePermissions[strval($rolePermission->permissionId)] = 1;
			}
		}


		$return = array(
			'rolePermissions' => $rolePermissions,
			'permissions' => permissionModel::loadAll(array('status' => 1))
		);
		echo json_encode($return);
	}

	/* Permissions */
	public static function newPermission() {

		// Grab form data
		$id = request::unsignedPost("id");
		$name= request::unsignedPost("name");
		$key = request::unsignedPost("key");
		$description = request::unsignedPost("description");

		// Create user
		$permission = new permissionModel();
		$isNew = 1;
		if($id > 0) {
			$permission->id = $id;
			$permission->load();
			$isNew = 0;
		}
		$permission->key = $key;
		$permission->name = $name;
		$permission->description = $description;
		$permission->save();

		$permissionArr = get_object_vars( $permission);
		$permissionArr['isNew'] = $isNew;

		echo json_encode( $permissionArr );
	}

	public static function permissionStatusUpdate() {
		$permissionId = request::unsignedPost("permissionId");
		$permission = new permissionModel($permissionId);
		$status = $permission->status;

		if($permission->status) { 
			$permission->status = 0;
		}
		else {
			$permission->status = 1;
		}
		$permission->save();
		echo json_encode(array("status" => $permission->status));
	}

	public static function getUserRoles() { 

		$email = request::unsignedPost("email");
		$user = new userModel();
		$user->email = strtolower($email);
		$return = array(
			"email" => $email, 
			"found" => 0,
			"userId" => 0,
			"userRoles" => array(),
			"roles" => roleModel::loadAll(array("status" => 1))
		);
		if($user->load('email')) { 
			$return["found"] = 1;
			$return["userId"] = $user->id;
			foreach($user->getRoles() as $role) { 
				$return["userRoles"][$role->id] = 1;
			}
		}

		echo json_encode($return);
	}

	public static function editUserRoles() { 
		$userId = request::unsignedPost("userId");
		if($userId == 0) { 
			return;
		}
		$roles = roleModel::loadAll(array("status" => 1));

		foreach($roles as $role) { 
			$value = request::unsignedPost("role_".$role->id);
			$userRole = new userRoleModel();
			$userRole->userId = $userId;
			$userRole->roleId = $role->id;
			$userRole->load();
			$userRole->enabled = $value;
			$userRole->save();
		}

	}

}
