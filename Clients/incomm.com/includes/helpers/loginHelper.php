<?php
class loginHelper{
	public static function redirect(){
		if($_SESSION['redirectUrl']){
			$redirectUrl = $_SESSION['redirectUrl'];
			unset($_SESSION['redirectUrl']);

			header("Location: $redirectUrl");
			die();
		} else {
			// No redirect url so lets just send them to the account page
			view::Redirect('account');
		}
	}
	
	public static function isValidUser(userModel $user){
		$newUser = new userModel();
		$newUser->email = $user->email;
		$newUser->load();
		
		if(isset($newUser->password) && $newUser->password == md5($user->email . $user->password)){
			return true;
		} else {
			return false;
		}
	}
	
	public static function checkLogin(){
		if(isset($_SESSION['userId'])){
			return true;
		} else {
			return false;
		}
	}
	
	public static function forceLogin(){
		if(self::checkLogin()){
			$user = new userModel($_SESSION['userId']);
			return $user;
		} else {
			self::setRedirectUrl();
			view::Redirect('login');
		}
	}
	
	public static function setRedirectUrl($url = false){
		if($url){
			$_SESSION['redirectUrl'] = $url;
		} else {
			// @TODO Use the url builder here
			$_SESSION['redirectUrl'] = globals::getRedirectUrl();
		}
	}
	
	public static function logoutUrl(){
		return '/login/signout?sig=' . session_id();
	}
}