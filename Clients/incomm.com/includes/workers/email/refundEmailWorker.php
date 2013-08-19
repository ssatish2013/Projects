<?php

require_once(dirname(__FILE__)."/../../init.php");

class refundEmailWorker extends baseWorker implements worker {

	protected $queueName = 'refundEmailQueue';
	protected $routingKey = 'refundEmail';

	public function doWork($originalContent) {
		// Note, because this method can be called multiple times from this worker, we clear the things we previously set on the view
		view::clear(array('transaction','purchaserRefund','recipientRefund')); 
		$content = json_decode($originalContent, true);
		if (isset($content['transactionId'])) { 
			try{
				$transaction = new transactionModel( $content['transactionId'] );
				$shoppingCart = new shoppingCartModel( $transaction->shoppingCartId );
				$messages = $shoppingCart->getAllMessages();
				if (count($messages) == 0) {
					throw new Exception("No messages from shoppingCart#{$shoppingCart->id} and transaction#{$transaction->id}");
				}
				globals::forcePartnerRedirectLoaderForBatchScript($shoppingCart->partner, null); // @TODO, look into getting redirect loader here.... shouldn't be important for this email
				view::SetObject($transaction); // Note cleared above
				view::SetObject($messages[0]); // $message
				view::set('purchaserRefund', true); // Note cleared above
				view::set('transactionId', $content['authorizationId']);
				$mailer = new mailer();
				$mailer->userId = $content['userId']; 
				$mailer->transactionId = $transaction->id;
				$mailer->workerData=$originalContent;
				$mailer->recipientName = ucfirst($transaction->firstName) . ' ' . ucfirst($transaction->lastName);
				$mailer->recipientEmail = $transaction->fromEmail;
				$mailer->template = 'refund';
				$mailer->send();
			} catch (Exception $e){
				log::error("Failed to send refund email for Transation #{$transaction->id} to {$transaction->fromEmail}.", $e);
			}
		}
		else if (isset($content['giftId'])) { 
			try{
				$gift = new giftModel($content['giftId']);
				globals::forcePartnerRedirectLoaderForBatchScript($gift->partner, $gift->redirectLoader, $gift->language);
				view::set('recipientRefund', true); // Note cleared above
				$mailer = new mailer();
				$mailer->giftId = $gift->id;
				$mailer->userId = $content['userId'];
				$mailer->workerData = $originalContent;
				$mailer->recipientName = $gift->recipientName;
				$mailer->recipientEmail = $gift->recipientEmail;
				$mailer->template = 'refund';
				$mailer->send();
			} catch (Exception $e){
				log::error("Failed to sent refund email for Gift #{$gift->id} to {$gift->recipientEmail}.");
			}
		} else {
			log::error('no transaction Id or gift Id specified');
		}
	}
}
