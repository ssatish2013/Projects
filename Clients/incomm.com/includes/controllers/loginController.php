<?php

class loginController {

	public static $defaultMethod = 'loginGet';

	public function loginGet(){
		if(isset($_SESSION['userId'])){
			// The user is already logged in, lets just send them on their way
			loginHelper::redirect();
		}
		view::Render('login/login');
	}
	
	public function loginPost(){
		$user = new userModel();
		if($user->validate()){
			if(loginHelper::isValidUser($user)){
				$newUser = new userModel();
				$newUser->email = $user->email;
				$newUser->load();

				$_SESSION['userId'] = $newUser->id;
				loginHelper::redirect();
			}
			
			Env::main()->validationErrors['generalError'] = 'Sorry your email/password is invalid.  ' .
				'Please try again or <a href="/login/reset">reset</a> your password';
		}
		view::Render('login/login');
	}
	
	public function resetGet(){
		view::Render('login/reset');
	}
	
	public function resetPost(){
		$user = new userModel();
		$user->email = request::post('userEmail');
		$user->load();
		if(isset($user->id)){
			$w = new passwordResetEmailWorker();
			$w->send(json_encode(array(
				'userId' => $user->id,
				'partner' => globals::partner(),
				'redirectLoader' => globals::redirectLoader()
			)));
			
			view::Set('emailSent', true);
		} else {
			Env::main()->validationErrors['generalError'] = 'Sorry the email address you entered is not valid';
		}
		view::Render('login/reset');
	}
	
	public function passwordGet(){
		if(request::url('guid')){
			$user = new userModel();
			$user->passwordResetGuid = request::url('guid');
			$user->load();
			if($user->passwordResetExpires < date("Y-m-d H:i:s")){
				Env::main()->validationErrors['generalError'] = 'Sorry the link you are using has expired';
				view::Render('login/reset');
				return;
			}
			view::SetObject($user);
			view::Render('login/password');
		} else {
			view::Redirect('login');
		}
	}
	
	public function passwordPost(){
		$user = new userModel();
		$user->passwordResetGuid = request::post('passwordResetGuid');
		$user->load();
		view::SetObject($user);
		if($user->passwordResetExpires < date("Y-m-d H:i:s")){
			Env::main()->validationErrors['generalError'] = 'Sorry the link you are using has expired';
			view::Render('login/reset');
			return;
		}
		if(request::post('password') != request::post('password2')){
			Env::main()->validationErrors['generalError'] = 'Sorry, the passwords you entered did not match.  Please try again.';
			view::Render('login/password');
			return;
		}
		
		$password = request::post('password');
		$errorMessages = array();
		if(!userModel::isValidPassword($password, $errorMessages)) {
			Env::main()->validationErrors['generalError'] = "Password not saved. " . $errorMessages['password'];
			view::Render('login/password');
			return;
		}

		$user->password = md5($user->email . request::post('password'));
		$user->save();
		$_SESSION['userId'] = $user->id;
		loginHelper::redirect(); // Send them where ever they should go
	}
	
	public function signoutGet(){
		$signature = request::get('sig');
		if($signature == session_id()){
			unset($_SESSION['userId']);
		}
		view::Redirect('login');
	}
}
