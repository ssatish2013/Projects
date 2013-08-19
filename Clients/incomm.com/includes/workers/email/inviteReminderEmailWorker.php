<?php

require_once(dirname(__FILE__) . "/../../init.php");

/**
 * this worker will send an email to an invitee reminding them to contribute to a gift
 */
class inviteReminderEmailWorker extends baseWorker implements worker {

	protected $queueName = 'inviteReminderEmailQueue';
	protected $routingKey = 'inviteReminder';

	/**
	 * sends an invite reminder for a specific invite
	 * @param int $emailId the email id of the invite that a reminder is to be sent out for (record in the email table with template = invite)
	 */
	public function doWork($emailId){

		$email = new emailModel($emailId);

		$giftId = $email->giftId;
		$gift = new giftModel($giftId);

		globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);
		$uiSettings = settingModel::getPartnerSettings($gift->partner, 'ui');

		// setup data for the view
		$giftCreatorName = $gift->getCreatorMessage()->fromName;
		$giftRecipientName = $gift->recipientName;

		// determine a rough estimate of how much time is left until delivery
		$timeUntilDeliveryEstimate = strtolower($gift->getTimeUntilDelivery('estimate'));

		// determine the delivery date in the recipient's time zone
		$giftDeliveryDateLocal = $gift->getDeliveryDate(true);

		view::set('giftId', $giftId);
		view::set('emailId', $emailId);
		view::SetObject($gift);
		view::set('giftCreatorName', $giftCreatorName);
		view::set('giftRecipientName', $giftRecipientName);
		view::set('giftImageSrc', $gift->getDesign()->mediumSrc);
		view::set('timeUntilDelivery', $timeUntilDeliveryEstimate);
		view::set('giftDeliveryDate', date($uiSettings['dateFormat'], strtotime($giftDeliveryDateLocal)));

		$mailer = new mailer();
		$mailer->giftId = $giftId;
		$mailer->recipientEmail = $email->email;
		$mailer->template = 'inviteReminder';
		$mailer->send();

	}
}
