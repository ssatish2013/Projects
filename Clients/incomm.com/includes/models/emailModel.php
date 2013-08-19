<?php
class emailModel extends emailDefinition{
	public static function getTemplates(){
		$safePartner = db::escape(globals::partner());
		$query = "select distinct(template) from emails where `partner`='$safePartner'";
		$result = db::memcacheMultiGet($query, "allEmailTemplates-".$safePartner);
		
		return array_map(function($x){$o = new stdClass;$o->name=$x['template'];return $o;},$result);
	}


	/**
	 * check the see if this email record has an "unsubscribe" record associated with it. This method only applies to "inviteReminder" templates
	 * TODO: Add a "Invite" model and db table and transition these attributes to there
	 * @return bool
	 */
	public function isSubscribedInviteReminder(){

		// check if the object has been hydrated
		if(null === $this->id){
			$this->load();
		}

		// this method only available for email obj w/template = 'inviteReminder'
		if('inviteReminder' !== $this->template) {
			return false;
		}

		// look for the corresponding "unsubscribe" email record for this gift/email. If it exists, they have unsubscribed
		$sql = "
			SELECT 	`id`
			FROM 	`emails`
			WHERE 	`emailDigest` = '$this->emailDigest'
			AND 	`giftId` = $this->giftId
			AND 	`template` = 'inviteReminderUnsubscribe'
		";

		$result = db::query($sql);
		$recordCount = mysql_num_rows($result);
		return (0 === $recordCount);

	}



	/**
	 * unsubscribe to reminders for this invitation
	 */
	public function unsubscribeInviteReminder(){

		if(null === $this->id){
			$this->load();
		}

		// unsubscribe only if the gift/email is still subscribed
		if($this->isSubscribedInviteReminder()) {
			// add an unsubscribe entry for this email/gift/template
			$worker = new inviteReminderUnsubscribeEmailWorker();
			$worker->send($this->id);
		}

	}
}