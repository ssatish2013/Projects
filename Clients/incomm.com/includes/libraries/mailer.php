<?php

require_once('Mail.php'); // Maybe fix this later
require_once('Mail/mime.php');

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class mailer {
	public $recipientEmail;
	public $recipientName;
	public $template;
	
	public $giftId = null;
	public $messageId = null;
	public $userId = null;
	public $transactionId = null;
	
	public $workerData;
	
	private $senderEmail;
	private $senderName;
	private $replyToEmail;
	private $subject;

	/*---------------------------------------------------------------------*/
	/*-------------------------- Public Functions -------------------------*/
	/*---------------------------------------------------------------------*/

	public function send(){

		error_reporting(E_ALL ^ E_NOTICE); //Pear::Mail package sucks, turn off strict to not blow up
		
		$settings = settingModel::getPartnerSettings();

		// @TODO we should get this setup for all partners so we don't need to check
		// the array to see what server to use.

		$this->senderEmail = $settings['senderEmailAddress'];
		$this->replyToEmail = $settings['replyToAddress'];
		$this->senderName = $settings['senderName'];

		$smtp = Mail::factory('smtp',
			array (
				'host' => "173.45.231.199",
				'port' => '25',
				'auth' => false,
				'username' => "",
				'password' => ""
			)
		);

		if(!isset($this->recipientEmail)){
			throw new Exception('No recipient email set');
		}

		if(!isset($this->template)){
			throw new Exception('No template set, use quick send for sending just text');
		}

		$email = new emailModel();
		$email->sentAt = date("Y/m/d H:i:s");
		$email->guid = randomHelper::guid(20);
		$email->template = $this->template;
		$email->partner = globals::partner();
		$email->giftId = $this->giftId;
		$email->messageId = $this->messageId;
		$email->userId = $this->userId;
		$email->transactionId = $this->transactionId;
		$email->email = $this->recipientEmail;
		$email->save();
		
		view::Set('template', $this->template);
		view::Set('guid', $email->guid);
		$subjectSource = view::ReturnRender('email/subject');

		$htmlSource = emailHelper::trackAllLinks(view::ReturnRender('email/html'),$email->guid);
		$textSource = view::ReturnRender('email/text');

		// String out the newline chars to make these emails safe :-)
		$from = '"' . emailHelper::stripEmailCharacters($this->senderName) . '" ' . 
						'<' . emailHelper::stripEmailCharacters($this->senderEmail) . '>';
		$from = trim($from);

		$to = '';
		if($this->recipientName){
			$to = '"' . emailHelper::stripEmailCharacters($this->recipientName) . '" ';
		}
		$to .= '<' . emailHelper::stripEmailCharacters($this->recipientEmail) . '>';
		$to = trim($to);

		$headers = array (
			'From' => $from,
			'To' => $to,
			'Cc' => '',
			'x-gca-env' => Env::main()->getEnvName(),
			'x-gca-template' => $this->template,
			'x-worker-data' => $this->workerData,
			'x-sender-override' => $from,
			'x-email-guid' => $email->guid,
			'Return-Path' => 'giftingapp-return+'.Env::main()->getEnvName().'+'.$email->guid.'@groupcard.com',
			'Sender' => $from,
			'Subject' => trim(emailHelper::stripNewLineCharacters($subjectSource)),
			'Reply-To' => trim('<' . emailHelper::stripNewLineCharacters($this->replyToEmail) . '>')
		);

		if($htmlSource){
			// handle multipart
			$message = new Mail_mime(array('head_charset' => 'UTF-8', 'html_charset' => 'UTF-8', 'text_charset' => 'UTF-8'));
			$message->setTXTBody($textSource);
			$message->setHTMLBody($htmlSource);
			$body = $message->get();
			$headers = $message->headers($headers);
		} else {
			throw new Exception('There is no html version of this email, please correct it');
		}
		
		$recipients = emailHelper::stripNewLineCharacters($this->recipientEmail);

		$mail = $smtp->send($recipients, $headers, $body);

		if (PEAR::isError($mail)) {
			$err = $mail->getMessage();
			throw new Exception("Error while sending to $to\n$err\n\nMessage:$textSource\n\n");
		}
	}

	public function quickSend($text, $address = null, $subject = 'Quick Email Send'){
		
		if(!$address){
			return;
		}
		
		//error_reporting(E_ALL ^ E_NOTICE); //Pear::Mail package sucks, turn off strict to not blow up
		
    $smtp = Mail::factory('smtp',
      array (
        'host' => "173.45.231.199",
        'port' => '25',
        'auth' => false,
        'username' => "",
        'password' => ""
      )
    );

		$headers = array (
			'From' => trim("\"System\" <dev@groupcard.com>"),
			'To' => trim("<$address>"),
			'Cc' => '',
			'Sender' => trim("\"System\" <dev@groupcard.com>"),
			'Subject' => trim($subject),
		);

		$mail = $smtp->send($address, $headers, $text);

		if (PEAR::isError($mail)) {
			// do something on error
			$err = $mail->getMessage();
			trigger_error("Error while sending to $toemail\n$err\n\nMessage:$textSource\n\n");
			return false;
		}
	}
}
