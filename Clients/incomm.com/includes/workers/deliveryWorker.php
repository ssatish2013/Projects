<?php

require_once(dirname(__FILE__) . "/../init.php");

class deliveryWorker extends baseWorker implements worker {

	protected $queueName = 'deliveryQueue';
	protected $routingKey = 'delivery';

	public function doWork($content) {
		try {
			$gift = new giftModel($content);
			if ($gift->unverifiedAmount || $gift->paidAmount) { //Make sure there is value on this gift 
					if ($gift->emailDelivery || $gift->facebookDelivery || $gift->twitterDelivery) {
						self::createGuid($gift);
						$r = new recipientDeliveryEmailWorker();
						$r->send($gift->id);

						// If we have contributors, kick off a contributor email worker.
						if ($gift->getMessages()) {
							$c = new contributorDeliveryEmailWorker();
							$c->send($gift->id);
						}
					}

					/*
					 *  @todo move sms functionality to its own worker
					 */
					try {
						$gift->sendSMS();
					} catch (Exception $e) {
						// We really should do something here... but the frontend validation is lacking, so we'll do nothing for now
					}

					if ($gift->facebookDelivery) {
						$f = new facebookDeliveryWorker();
						$f->send($gift->id);
						log::info("Sent Facebook delivery for gift {$gift->id}");
					}
					if ($gift->twitterDelivery) {
						$t = new twitterDeliveryWorker();
						$t->send($gift->id);
						log::info("Sent Twitter delivery for gift {$gift->id}");
					}
			} else {
				// There is no gift amout :-(
				$gift->paid = null;
				$gift->save();
				log::error("No gift amount for gift {$gift->id}, unverifiedAmount={$gift->unverifiedAmount}");
			}
		} catch (Exception $e) {
			log::error("Failed to delivery gift {$gift->id}", $e);
		}
	}

	public function createGuid($gift) {
		$recipientGuid = new recipientGuidModel();
		$recipientGuid->giftId = $gift->id;
		$recipientGuid->guid = randomHelper::guid(16);
		$recipientGuid->expires = recipientGuidModel::getClaimLinkExpire();
		$recipientGuid->save();
	}
}
