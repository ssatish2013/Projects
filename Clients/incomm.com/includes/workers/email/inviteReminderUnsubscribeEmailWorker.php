<?php
require_once(dirname(__FILE__) . "/../../init.php");

class inviteReminderUnsubscribeEmailWorker extends baseWorker implements worker {

	protected $queueName = 'inviteReminderUnsubscribeEmailQueue';
	protected $routingKey = 'inviteReminderUnsubscribe';

	/**
	 * send an email to the invitee to tell him that he will no longer receive invite reminders for this gift.
	 * *note* emails are logged in the "emails" table. The log entry for the invitee denotes that they've unsubscribed from the reminders for this gift.
	 * @param int $emailId  the email id of the invite that the user is unsubscribing from
	 */
	public function doWork($emailId){

		$email = new emailModel($emailId);

		$giftId = $email->giftId;
		$gift = new giftModel($giftId);

		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);

		// retrieve data from the models
		$giftCreatorMessage = $gift->getCreatorMessage();
		$giftCreatorName = $giftCreatorMessage->fromName;
		$giftCreatorEmail = $giftCreatorMessage->fromEmail;
		$inviteeEmail = $email->email;
		$giftRecipientName = $gift->recipientName;

		// setup data for the view
		view::setObject($gift);
		view::set('giftId', $giftId);
		view::set('giftRecipientName', $giftRecipientName);
		view::set('giftCreatorName', $giftCreatorName);
		view::set('giftCreatorEmail', $giftCreatorEmail);
		view::set('giftTitle', $gift->title);
		view::set('giftImageSrc', $gift->getDesign()->mediumSrc);
		view::set('emailId', $emailId);
		view::set('inviteeEmail', $inviteeEmail);


		// This email record is REQUIRED to track if the invitee has unsubscribed to this gift.
		// send an email reminder to the invitee to notify them that they will no longer receive contribution reminders for this gift.
		$mailer = new mailer();
		$mailer->giftId = $giftId;
		$mailer->recipientEmail = $inviteeEmail;
		$mailer->template = 'inviteReminderUnsubscribe';
		$mailer->send();

	}
}
