<?php

class roleModel extends roleDefinition {

	public function hasPermission($permId) {

		//if we weren't passed a specific permission Id
		//lets look it up
		$perm = new permissionModel();
		if(!is_numeric($permId)) {
		  $perm->key = $permId;
		}
		else {
		  $perm->id = $permId;
		}

		//if we can't load it, they certainly don't 
		//have permission!
		//also check the status of the permission
		if(!$perm->load() || $perm->status != 1) {
		  return false;
		}

		//we have an Id, let's see if they have permission
		$rolePerm = new rolePermissionModel();
		$rolePerm->permissionId = $perm->id;
		$rolePerm->roleId = $this->id;

		//can't load it, no permission!
		if ( ! $rolePerm->load()) {
			return false;
		}
		else {
			return $rolePerm->value;
		}
	}
}
