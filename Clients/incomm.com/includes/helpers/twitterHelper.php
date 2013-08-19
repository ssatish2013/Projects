<?php
class twitterHelper{
	public static function populateTwitterOnMessage(messageModel $message){
		$token = request::unsignedPost('twitterToken');
		$secret = request::unsignedPost('twitterSecret');
		
		if($token && $secret){
			$message->twitterToken=$token;
			$message->twitterSecret=$secret;
		}
	}
}