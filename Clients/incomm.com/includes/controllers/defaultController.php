<?php
class defaultController {
	public static $defaultMethod="index";

	public function index(){
		View::ExternalRedirect(View::GetFullUrl('gift'));
	}
}
