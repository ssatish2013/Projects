<?php

require_once(dirname(__FILE__) . "/../init.php");

//include our namespaces
use amqp_091 as amqp;
use amqp_091\protocol;
use amqp_091\wire;

class sampleWorker extends baseWorker {

	protected $queueName = 'sampleQueue';
	protected $routingKey = 'sample';

	public function handleDelivery(wire\Method $meth, amqp\Channel $chan) {
		//get content of the message, maybe we have some json?
		$content = $meth->getContent();

		//if the message is "end", we kill our worker
		if ($content == 'end') {
			return array(amqp\CONSUMER_CANCEL, amqp\CONSUMER_ACK);
		}

		//maybe there's a condition where we want to reject the message and put it back in the queue ?
		else if ($content == 'reject') {
			return amqp\CONSUMER_REJECT;
		}

		//otherwise we're ok to handle it
		else {
			//do our process here then acknowledge the message was received
			//and processed ok
			return amqp\CONSUMER_ACK;
		}
	}
}
