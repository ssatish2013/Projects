<?php

require_once(dirname(__FILE__) . "/../../init.php");

class reminderEmailWorker extends baseWorker implements worker {

	protected $queueName = 'reminderEmailQueue';
	protected $routingKey = 'reminder';

	public function doWork($content) {
		try{
			$data = json_decode($content, true);
			$giftId = 0;
			$email = null;
			if (is_numeric($content)) {
				$giftId = $content;
			} else {
				$data = json_decode($content, true);
				$giftId = $data['giftId'];
				$email = $data['email'];
			}

			$gift = new giftModel($giftId);
			$design = new designModel($gift->designId);

			//skip recipient reminder if this gift's all messages are refunded
			$allrefunded = true;
			foreach($gift->getAllMessages() as $msg){
				$allrefunded = $allrefunded && ($msg->refunded)?true:false;
			}

			if (!$allrefunded) {
				$recipientGuid = new recipientGuidModel();
				$recipientGuid->giftId = $gift->id;
				$recipientGuid->expires = date("Y-m-d H:i:s", strtotime("+1 day"));
				$recipientGuid->guid = randomHelper::guid(16);
				$recipientGuid->save();

				globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);

				view::SetObject($recipientGuid);
				view::SetObject($gift);
				view::SetObject($design);
				view::Set('messages', $gift->getAllMessages());
				$mailer = new mailer();
				$mailer->workerData = $content;
				$mailer->giftId = $gift->id;
				$mailer->recipientName = $gift->recipientName;
				if ($email === null) {
					$mailer->recipientEmail = $gift->recipientEmail;
				} else {
					$mailer->recipientEmail = $email;
				}
				$mailer->template = 'recipientReminder';
				$mailer->send();
			}

			//skip reminder for purchaser if this gift is self-gifting mode
			if ($gift->giftingMode == giftModel::MODE_SELF) return true;

			$purchaserEmails = array();
			foreach (messageModel::loadAll(array('giftId' => $gift->id)) as $message) {
				$transaction = new transactionModel();
				$transaction->shoppingCartId = $message->shoppingCartId;
				//only send reminder to purchaser whose transaction is not refunded
				if ($transaction->load() && !$transaction->refunded) {
					$purchaserEmails[] = array(
						'email' => $transaction->fromEmail,
						'name' => $transaction->firstName . ' ' . $transaction->lastName
					);
				}
			}

			view::SetObject($gift);
			view::Set('messages', $gift->getAllMessages());


			foreach ($purchaserEmails as $purchaserEmail) {
				$mailer = new mailer();
				$mailer->giftId = $gift->id;
				$mailer->workerData = $content;
				$mailer->recipientName = $purchaserEmail['name'];
				if($email===null){
					$mailer->recipientEmail = $purchaserEmail['email'];
				} else {
					$mailer->recipientEmail = $email;
				}
				$mailer->template = 'purchaserReminder';
				$mailer->send();
			}
		}
		catch (Exception $e) {
			log::warn('reminderEmailWorker encountered errors:', $e);
		}
	}
}
