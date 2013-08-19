<?php
class userModel extends userDefinition{

	protected $roles;
	protected $facebookId;
	protected $facebookAccessToken;
	
	protected static $minPasswordLength = 8;
	protected static $maxPasswordLength = 16;
	protected static $specialCharaters = "!@#$%^&*()";

	public function getRoles() {
		return $this->roles == NULL ?
			( $this->roles = userRoleModel::getUserRoles( $this->id )) :
			$this->roles;
	}

	public function getAllRoles() {
		return $this->roles == NULL ?
			( $this->roles = userRoleModel::getAllUserRoles( $this->id )) :
			$this->roles;
	}

	public function hasPermission( $permId ) {

		//see if their role(s) has the permission
		$roles = $this->getRoles();
		foreach($roles as $role) { 
			//$perm->id here is passing in as a string
			if($role->hasPermission($permId)) {
				return true;
			}
		}
		return false;
	}

	public function getUserByEmail($email){
		$this->email = strtolower($email);
		$this->load('email');
		if(!$this->id){
			$this->save();
		}
	}

	public function assignFromTransaction($transaction) {
		$this->getUserByEmail($transaction->fromEmail);
		$this->firstName = $transaction->firstName;
		$this->lastName = $transaction->lastName;
		$this->save();
	}
	
	public function validateAllButPassword(&$errors=null) {
		$isValid = true;
		$errors = array();
		if(empty($this->firstName)) {
			$errors['firstName'] = "Must have a first name";
			$isValid = false;
		}
		if(empty($this->lastName)) {
			$errors['lastName'] = "Must have a last name";
			$isValid = false;
		}
		$isValid = self::isValidEmail($this->email, $errors) && $isValid;
		
		return $isValid;
	}
	
	public static function isValidPassword($password, &$errors=null) {
		if(!preg_match('/^.*(?=.{'.self::$minPasswordLength.','.self::$maxPasswordLength.'}$)(?=.*\d)(?=.*[A-Z])(?=.*['.preg_quote(self::$specialCharaters).']).*$/', $password)) {
			$errors['password'] = "Invalid Password";
			return false;
		}
		return true;
	}
	
	public static function isValidEmail($email, &$errors=null, $allowBlank=false) {
		$isValid = true;
		if(is_null($errors)) $errors = array();
		if(empty($email)) {
			$errors['email'] = "Must have an email";
			$isValid = false;
		}
		elseif(!preg_match("/@/", $email) || !preg_match("/\./", $email)) {
			$errors['email'] = "Invalid email address";
			$isValid = false;
		}
		return $isValid;
	}
}
