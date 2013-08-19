<?php
require_once(dirname(__FILE__)."/../init.php");

use amqp_091 as amqp;
use amqp_091\protocol;
use amqp_091\wire;

Env::includeLibrary('amqphp/amqp');


class baseWorker extends amqp\SimpleConsumer {

	//private vars
	public $conn = null;						//connection object
	public $ch = null;							//channel to connection 

	//echange info
	public $exName = 'giftingapp';			//exchange to use
	public $exType = 'direct';					//type of exchange (direct, fanout, topic);

	//queue
	protected $queueName = null;							//queue
	protected $routingKey = null;

	//mq settings
	protected	$userName = 'guest';		//login to mq database
	protected $userPass = 'guest';		//password to mq database
	protected $host = 'localhost';		//host for mq
	protected $port = 5672;						//port for mq
	protected $vhost = '/';						//virtual host to use for mq

	/**************
		CONSTRUCTOR 
	***************/
	public function __construct($settings = array()) { 

		//get our MQ connection settings
		$this->userName = Env::getMq('user');
		$this->userPass = Env::getMq('pass');
		$this->host = Env::getMq('server');
		
		//override any vars
		foreach($settings as $setting => $value) { 
			$this->$setting = $value;
		}

		//setup our exchange based on environment
		$this->exName = Env::getEnvName();
		$this->vhost = Env::getEnvName();

		//setup the connection
		$config = array(
			'username'	=> $this->userName,
			'userpass'	=> $this->userPass,
			'vhost'			=> $this->vhost,
			'socketParams'	=> array( 
				'host'	=> $this->host,
				'port'	=> $this->port
			)
		);

		//setup the connection and grab the channel
		$this->conn = new amqp\Connection($config);

		$this->conn->connect();
		$this->ch = $this->conn->getChannel();

		$this->_bind();

		//ok, queue is setup, lets bind to it for consuming
		parent::__construct(array(
			'queue'			=> $this->queueName,
			'no-local'	=> true,
			'no-ack'		=> false,
			'exclusive'	=> false,
			'no-wait'		=> false
		));

	}

	public function subscribe() { 
		//subscribing to the channel using an infinite loop
		$this->ch->addConsumer($this);

		//watching the channel, this basically 'causes the process to "hang"
		//until we send it a cancel call
		$this->conn->select();

		//alright, done subscribing, we should clean up
		$this->ch->shutdown();
		$this->conn->shutdown();

	}

	/****************************************
		bind -
		binds the queue to the exchange using
		the routing key provided
	****************************************/
	private function _bind() { 

		//declare/create and invoke our exchange

		$exDecl	= $this->ch->exchange('declare', array(
			'type'		=> $this->exType,
			'durable'	=> true,
			'exchange'	=> $this->exName
		));
		$eDeclResponse = $this->ch->invoke($exDecl);

		//declare/create and invoke the queue
		$qDecl = $this->ch->queue('declare', array(
			'queue'				=> $this->queueName,
			'routing-key'	=> $this->routingKey,
			'exchange'		=> $this->exName,
			'durable'			=> true
		));
		$this->ch->invoke($qDecl);

		//bind to the exchange
		$qBind = $this->ch->queue('bind', array(
			'queue'				=> $this->queueName,
			'routing-key'	=> $this->routingKey,
			'exchange'		=> $this->exName
		)); 
		$this->ch->invoke($qBind);

	}

	/****************
		handleDelivery
	****************/
	public function handleDelivery (wire\Method $meth, amqp\Channel $chan) {
		/* You should always override this method */
		
		// An alternative to overriding this is to implement worker.
		if ($this instanceof worker) {
			$content = $meth->getContent();
			
			//if the message is "end", we kill our worker
			if ($content == 'end') {
				return array(amqp\CONSUMER_CANCEL, amqp\CONSUMER_ACK);
			} else {
				try {
					$this->doWork($content);
					return amqp\CONSUMER_ACK;
				} catch (Exception $e) {
					log::error("Exception while processing worker message: $content.", $e);
				}
			}
		} else {
			throw new Exception("You should override me.");
		}
	}
	
	/*****************
		startWorker
	*****************/
	//forks out the number of workers you tell it
	public function startWorker($num = 1) {
		
    $i = 0;
		$className = get_class($this);
    while($i < $num) {

      //first we need to fork
      $pid = pcntl_fork();
      if($pid == -1) {
        die("could not fork");
      }
      else if($pid) {
        //parent
      }
      else {
        if(posix_setsid() == -1) {
          die("could not detach from terminal");
        }

        /*
        //if we want to store the pid
        $posid = posix_getpid();
        $fp = fopen("/var/run/process.pid", "w");
        fwrite($fp, $posid);
        fclose($fp);
        */

        //create worker and work until we say so 
        $worker = new $className(array(
          'queueName'   => $this->queueName,
          'routingKey'  => $this->routingKey
        ));

        //subscribe forever
        $worker->subscribe();
        exit();
      }

      $i++;
      print "Forked Consumer $i\n";
      log::debug("Started worker - " . get_class($this));
    }

	}

	/*****************
		stopWorker
	*****************/
	//stops the number of workers you tell it by 
	//sending them 'end' messages
	public function stopWorker($num = 1) { 

		$publishParams = array(
			'content-type' => 'text/plain',
			'content-encoding' => 'UTF-8',
			'routing-key' => $this->routingKey,
			'mandatory' => false,
			'immediate' => false,
			'priority'	=> 10,
			'exchange' => $this->exName
		);

		$stopWorker = $this->ch->basic('publish', $publishParams);
		$stopWorker->setContent('end');


    //declare/create and invoke the queue
    $qDecl = $this->ch->queue('declare', array(
      'queue'       => $this->queueName,
      'routing-key' => $this->routingKey,
      'exchange'    => $this->exName,
			'durable'			=> 'true'
    ));

		$i = 0;
		while($i < $num) { 

			//check to see if there are still consumers attached ot the queue
    	$queueData = $this->ch->invoke($qDecl);
			$consumerCount = $queueData->getField('consumer-count');
			if($consumerCount  == 0) { 
				print "No more consumers left to stop, exiting\n";
				$i = $num;
			}
			else {
				$this->ch->invoke($stopWorker);
				$i++;
				print "Stopped consumer $i\n$consumerCount consumers are left\n\n";

				//need a sleep in here so the mq can realize that
				//a consumer is disconnected
				sleep(1);
			}
		}

	}


	public function send($message) {
		if ($this->routingKey != "dummy") {
			log::info("Queueing message for $this->exName/$this->routingKey, worker " . get_class($this));
		}
		
		//standard publish params
    $publishParams = array(
      'content-type' => 'text/plain',
      'content-encoding' => 'UTF-8',
      'routing-key' => $this->routingKey,
      'mandatory' => true,
			'delivery-mode'	=> 2,
      'immediate' => false,
			'priority'	=> 1,
      'exchange' => $this->exName
    );

    $basicMessage = $this->ch->basic('publish', $publishParams);
    $basicMessage->setContent($message);
		$this->ch->invoke($basicMessage);

	}

	public function recover() { 

    $basicMessage = $this->ch->basic('recover', array('requeue'=>true));
		$this->ch->invoke($basicMessage);
	}
	
	public function getNumberOfWorkers(){
    //declare/create and invoke the queue
    $qDecl = $this->ch->queue('declare', array(
      'queue'       => $this->queueName,
      'routing-key' => $this->routingKey,
      'exchange'    => $this->exName,
			'durable'			=> 'true'
    ));

    $queueData = $this->ch->invoke($qDecl);
		return $queueData->getField('consumer-count');		
	}

	/**************
		~DESTRUCTOR	
	***************/
	//cleaning up the connection between our consumer and the mq
	public function __destruct() { 


		//if they're already closed, we don't want any warnings
		/*
		try { 
			@ $this->ch->shutdown();
			@ $this->conn->shutdown();
		}
		catch(Exception $e) { 
			//so long as everything is shut down, we're cool
		}
		*/
	}
		
		

}

