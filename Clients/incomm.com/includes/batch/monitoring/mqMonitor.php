<?php
	require_once(dirname(__FILE__)."/../../init.php");

	$email = 'aaron@groupcard.com';
	$status = 0;
	//passthru("rabbitmqctl status", $status);

	//all good
	if($status == 0) { exit(0); }

  $mailer = new mailer();
  $mailer->recipientName = 'System';
  $mailer->recipientEmail = $email;
  $mailer->template = 'system';
	send('MQ DOWN!', 'The MQ is Down! Attempting to restart!', $mailer);

	passthru("/etc/init.d/rabbitmq-server start", $status);
	if($status != 0) { 
		print "sending...";
		$mailer->quickSend('MQ Restart Failed!', $email, 'MQ Monitoring -- RESTART FAILED!');
		print "done\n";
	}
	else {
		print "sending...";
		$mailer->quickSend('MQ Restart OK!', $email, 'MQ Monitoring -- RESTART OK!');
		print "done\n";
	}

	$mailer->quickSend('Workers Restarting!', $email, 'MQ Monitoring -- RESTARTING WORKERS !');
	try { 
		workersHelper::restartAllWorkers();
	}
	catch (Exception $e) { 
		$mailer->quickSend('Workers Restart FAILED?', $email, 'MQ Monitoring -- RESTART WORKERS FAILED?');
		exit(0);
	}
	$mailer->quickSend('Workers Restart OK!', $email, 'MQ Monitoring -- RESTART WORKERS OK!');


function send($subject, $message, $mailer) { 
	print "sending...";
	view::set('message',$message);
	view::set('subject','MQ Monitoring -- ' . $subject);
	$mailer->send();
	print "done!\n";
}
