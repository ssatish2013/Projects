<?php
require_once(dirname(__FILE__)."/../init.php");

// I borrowed some of the code below from David Walsh.
// http://davidwalsh.name/gmail-php-imap

$hostname = "{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX";
$username = "giftingapp-return@groupcard.com";
$password = "B0nceH4ndler";

//$inbox = imap_open($hostname,$username,$password);
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

$emails = imap_search($inbox,'ALL');
if(is_array($emails)){
	foreach($emails as $email_number){
		$overview = imap_fetch_overview($inbox,$email_number,0);
		$message = imap_fetchbody($inbox,$email_number,"");
		
		if(preg_match("/giftingapp-return\+([A-Za-z0-9_-]+)\+([a-z0-9]{20})@groupcard\.com/",$overview[0]->to,$matches)){
				if($matches[1]==Env::getEnvName()){
					$guid = $matches[2];
					
					$emails=emailModel::loadAll(array(
						"guid"=>$guid
					));
					
					if(sizeof($emails) && $email = $emails[0]){
						if(!$email->bouncedAt){
							$handledBounces = array('recipientDelivery');
							if(in_array($email->template, $handledBounces)){
								$workerName = "bounce".ucfirst($email->template)."EmailWorker";
								$w = new $workerName();
								$w->send($email->id);
							}
							
							// We've handled it (or decided we wouldn't handle it)
							$email->bouncedAt=date("Y-m-d H:i:s");
							$email->save();
							imap_delete($inbox,$email_number);
						} else {
							// We already handled a bounc for this message
							imap_delete($inbox,$email_number);
						}
					} else {
						// We don't have a record of it, guess we have nothing to do
						imap_delete($inbox,$email_number);
					}
					
				} else {
					// Do nothing, leave it for another handler to grab
				}
		} else {
			// This email wasn't addressed to a valid bounce handler, just delete it
			imap_delete($inbox,$email_number);
		}
	}
}