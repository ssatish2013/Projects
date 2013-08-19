<?php

class recordController {

	public static $defaultMethod = 'client';
	
	public function iframe () {
		$token = array_key_exists ("token", $_REQUEST) ? $_REQUEST['token'] : "";
		$client = array_key_exists ("client", $_REQUEST) ? $_REQUEST['client'] : "";
		$value = array_key_exists ("value", $_REQUEST) ? $_REQUEST['value'] : "";
		view::set ('token', $token);
		view::set ('client', $client);
		view::set ('value', $value);
		view::Render ('record/iframe');
	}
	
	public function client () {
		$field= $_REQUEST["field"];
		Env::includeLibrary('Twilio');
		$rand = md5 (rand (0, 999999));
		$token = new Services_Twilio_Capability ('AC34d18ef689ad490e9c500ae82dd33553', '08422a07b38036e508047239731e9045');
		$token->allowClientOutgoing ("AP8b4ac4d681504d5fa4dec037414f2140");
		$token->allowClientIncoming ($rand);
		$token = $token->generateToken ();
		echo json_encode (array (
			"value" => languageModel::getString ("recordPrompt"),
			"field" => $field,
			"token" => $token,
			"client" => $rand
		), JSON_FORCE_OBJECT);	
	}
	
	public function playback () {
		$client = array_key_exists ('client', $_REQUEST) ? $_REQUEST['client'] : "";
		if (!empty ($client)) {
			$r = new recordingModel ();
			$r->clientKey = $client;
			$r->load ();
			$recording = $r->recordingUrl;
			if (!empty ($recording)) {
				echo json_encode (array (
					"recording" => $recording
				), JSON_FORCE_OBJECT);
				return true;
			}
		}
		echo json_encode (array (
			"recording" => null
		));
		return false;
	}
	
	public function dial () {
		// code that forces the twilio service to initiate a connection between
		// the twilio application and the twilio client
		$client = array_key_exists ('client', $_REQUEST) ? $_REQUEST['client'] : NULL;
		if (is_null ($client)) return false;
		$fields = array (
			"From" => "+14144826011",
			"To" => "client:" . $client,
			"Url" => view::GetDirectFullUrl ("record", "ring") . "?client=" . urlencode ($client),
			"StatusCallback" => view::GetDirectFullUrl ("record", "capture") . "?client=" . urlencode ($client)
		);
		$t = curl_init("https://api.twilio.com/2010-04-01/Accounts/AC34d18ef689ad490e9c500ae82dd33553/Calls");
		curl_setopt ($t, CURLOPT_USERPWD, "AC34d18ef689ad490e9c500ae82dd33553:08422a07b38036e508047239731e9045");
		curl_setopt ($t, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($t, CURLOPT_POST, TRUE);
		curl_setopt ($t, CURLOPT_POSTFIELDS, http_build_query ($fields));
		curl_setopt ($t, CURLOPT_RETURNTRANSFER, TRUE);
		curl_exec ($t);
		echo json_encode (true);
	}

	public function capture () {
		// endpoint for call disconnect and record
		$client = array_key_exists ("client", $_REQUEST) ? $_REQUEST['client'] : NULL;
		$recording = array_key_exists ("RecordingUrl", $_REQUEST) ? $_REQUEST['RecordingUrl'] : NULL;
		if (empty ($recording)) {
			return self::ring ();
		}
		$r = new recordingModel ();
		$r->clientKey = $client;
		$r->load ();
		$r->recordingUrl = $recording;
		$r->expires = date ("Y-m-d H:i:s", time () + (60 * 60 * 24 * 60));
		$r->save ();
		echo '<?xml version="1.0" encoding="UTF-8" ?>
<Response>
<Say>Here\'s your message.</Say>
<Play>' . $recording . '</Play>
<Gather action="' . view::GetDirectFullUrl ("record", "ring") . '?client=' . $client . '" timeout="30" numDigits="1">
	<Say>' . languageModel::getString ("recordRetry") . '</Say>
</Gather>
<Hangup />
</Response>';
	}

	public function ring () {
		// first thing that gets hit when the application launches
		$client = array_key_exists ("client", $_REQUEST) ? $_REQUEST['client'] : NULL;
		echo '<?xml version="1.0" encoding="UTF-8" ?>
<Response>
<Say>' . languageModel::getString ("recordPrompt") . '</Say>
<Record action="' . view::GetDirectFullUrl ("record", "capture") . '?client=' . urlencode ($client) . '" method="GET" timeout="5" finishOnKey="#" maxLength="120" />
<Say>' . languageModel::getString ("recordError") . '</Say>
</Response>';
	}
}
