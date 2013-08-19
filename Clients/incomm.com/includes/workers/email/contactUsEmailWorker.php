<?php

require_once(dirname(__FILE__)."/../../init.php");

class contactUsEmailWorker extends baseWorker implements worker { 
	
        protected $queueName = 'contactUsEmailQueue';
        protected $routingKey = 'contactUsEmail';
	
        public function doWork($content) { 
                $msg = json_decode($content);
		
		globals::forcePartnerRedirectLoaderForBatchScript($msg->partner, null);
		
		$recipientName	= trim($msg->recipientName);
		$recipientEmail = trim($msg->recipientEmail);
		
                view::Set('senderName', $msg->senderName);
		view::Set('senderEmail', $msg->senderEmail);
                view::Set('contactSubject', $msg->subject);
                view::Set('message', preg_replace('/<br(\s+)?\/?>/i', "\n", $msg->message) );
		
                $mailer = new mailer();
                $mailer->workerData = $content;
                $mailer->recipientName = $recipientName;
                $mailer->recipientEmail = $recipientEmail;
                $mailer->template = 'contact';
                $mailer->send();
		
                log::info("Contact email sent to $recipientEmail");
		
                return true;
        }
}

?>
