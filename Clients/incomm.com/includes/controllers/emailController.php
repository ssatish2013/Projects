<?php
class emailController{
	public function link(){
		// extract data from the tracking object hash
		$obj = emailHelper::decodeTrackingObject(request::url('o'));
		$emailGuid = $obj['guid'];
		$redirectUrl = $obj['url'];

		// retrieve the email associated with the tracking object
		$email = new emailModel(array('guid' => $emailGuid));
		$email->load();

		// mark the email as read
		if( null !== $email->id ){
			if(!$email->clickedAt){
				$email->clickedAt = date("Y/m/d H:i:s");
				$email->save();
			}
		}

		if($redirectUrl){
			$redirectUrl .= '/eg/' . $emailGuid;

			// check for an unsubscribe in the URL parameters and unsubscribe if found
			if("1" === request::url('unsubscribe') && null !== $email->id){
				$email->unsubscribeInviteReminder();
				$redirectUrl .= "/dialog/unsubscribed"; // notify the controller to display an "unsubscribed" dialog (show them the message even if it is already unsubscribed so they know for sure)
			}

			header("Location: " . $redirectUrl); // redirect to contribution page
		}
	}
}