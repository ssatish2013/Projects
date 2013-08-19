<?php

class accountController {

	public static $defaultMethod = 'mainGet';

	public function mainGet(){
		$user = loginHelper::forceLogin();
		$isAdmin = 0;
		if($user->hasPermission('adminIndex')) { 
			$isAdmin = 1;
		}
		view::Set('isAdmin', $isAdmin);
		view::Set('userId', $user->id);
		view::Render('account/main');
	}
}
