<?php
class rolePermissionModel extends rolePermissionDefinition{
	
	public function _setValue($value) {
		$this->value = $value;
		return $this->value;
	}

	public function _getValue() { 
		return (bool) intval($this->value);
	}
}
