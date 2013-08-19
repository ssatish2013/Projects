<?php

require_once(dirname(__FILE__)."/../../init.php");

class passwordResetEmailWorker extends baseWorker implements worker {

	protected $queueName = 'passwordResetEmailQueue';
	protected $routingKey = 'passwordReset';

	public function doWork($originalContent) {
		$content = json_decode($originalContent, true);
		globals::forcePartnerRedirectLoaderForBatchScript($content['partner'], $content['redirectLoader']);
		$user = new userModel($content['userId']);
		$user->passwordResetGuid = randomHelper::guid(16);
		$user->passwordResetExpires = date("Y-m-d H:i:s", strtotime("+4 hours"));
		$user->save();

		view::SetObject($user);
		
		try {
			$mailer = new mailer();
			$mailer->workerData = $originalContent;
			$mailer->recipientName = ucfirst($user->firstName) . ' ' . ucfirst($user->lastName);
			$mailer->recipientEmail = $user->email;
			$mailer->template = 'passwordReset';
			$mailer->send();
			log::info("Password reset email sent for user $user->id.");
		}
		catch (Exception $e) {
			log::info("{__CLASS__} failed sending password reset email to {$user->email}. Aborting.");
		}
	}
}
