<?php
require_once(dirname(__FILE__)."/../init.php");

$safeEnv = db::escape(Env::getEnvName());



/* ################################################################################################################################################################# */
$giftLife = 28; // days, this is how long we can reserve for a gift
$reminderSettings = settingModel::getPartnerSettings(null, 'reminderEmail');
$defaultFreq = $reminderSettings['reminderFreq']? $reminderSettings['reminderFreq'] : 72; // default reminder email frequency to 72 hours
$defaultMax = $reminderSettings['reminderMax']? $reminderSettings['reminderMax'] : 5; // default maximum reminder email per gift to 5
/*
	These two values are overridable by partner settings
	category='reminderEmail'; key='reminderFreq', 'reminderMax'
*/

/*
*The following query looks for any gift delivered before now and $giftLife days ago , which are still not claimed.
*It also check partner settings for the frequency and max emails per gift.
*The query should be scheduled to run at the smallest possible value of reminderFreq, which is 1 hour.
*By measuring the hours between the gift delivered and now, if total hours mod by frequency = 0 then it is overdue for a reminder email,
*until all reminder emails count for that gift reaches reminderMax.
*/
$sql = "
SELECT g.*
FROM `gifts` g
LEFT OUTER JOIN (SELECT CAST(`value` AS UNSIGNED) AS `freq`, `partner` FROM `settings` WHERE `key`='reminderFreq' AND `env`='$safeEnv') AS f ON g.`partner` = f.`partner`
LEFT OUTER JOIN (SELECT CAST(`value` AS UNSIGNED) AS `max`, `partner` FROM `settings` WHERE `key`='reminderMax' AND `env`='$safeEnv') AS m ON g.`partner` = m.`partner`
LEFT OUTER JOIN (SELECT `giftId`,COUNT(*) AS `sent` FROM `emails` WHERE `template` = 'recipientReminder' AND `giftId` IS NOT NULL GROUP BY `giftId`) AS s ON g.`id` = s.`giftId`
WHERE
  g.`envName`='$safeEnv' AND
	g.`claimed` IS NULL AND
	g.`paid` = 1 AND
	g.`created` >  DATE_ADD(NOW(), INTERVAL -$giftLife DAY) AND g.`created` < NOW()
  AND HOUR(TIMEDIFF(NOW(),delivered))>=COALESCE(f.freq,$defaultFreq) AND MOD(HOUR(TIMEDIFF(NOW(),delivered)),COALESCE(f.freq,$defaultFreq)) = 0
  AND COALESCE(s.`sent`,0) < COALESCE(m.`max`,$defaultMax)
";
log::debug($sql);
$result = db::query($sql);
while($i = mysql_fetch_assoc($result)){
	$gift = new giftModel();
	$gift->assignValues($i, true);

	$worker = new reminderEmailWorker();
	$worker->send($gift->id);


}




/* ################################################################################################################################################################# */
/**
 * inviteReminderEmailWorker: feed the inviteReminderEmailWorker with some records (all invitations for unclaimed gifts that have not been delivered whose invitees have not unsubscribed)
**/
// defaults
$defaultInviteReminderFreq = $reminderSettings['inviteReminderFreq']? $reminderSettings['inviteReminderFreq'] : 72; // default reminder email frequency to 72 hours
$defaultInviteReminderMax = $reminderSettings['inviteReminderMax']? $reminderSettings['inviteReminderMax'] : 3; // default maximum reminder email per invitee to 3
$INVITE_REMINDER_BUFFER = 6; // do not send out the invite reminder if the gift is scheduled to be delivered within this many hours


// (1)get all the active unclaimed group gifts that are not within 6 hours of delivery and the gift has not been refunded. We will reuse this array of active gift ids throughout to reduce the size of the result sets
// in subsequent queries
$sqlActiveGifts = "
SELECT 	g.`id`
FROM 	`gifts` g
		INNER JOIN `messages` m ON g.`id` = m.`giftId`
WHERE 	g.`envName` = '$safeEnv'
AND 	g.`giftingMode` = 1
AND 	g.`claimed` IS NULL
AND 	g.`deliveryDate` >= DATE_ADD(now(), INTERVAL $INVITE_REMINDER_BUFFER HOUR)
AND 	m.`isContribution` = 1
AND 	m.`refunded` IS NULL
";

$result = db::query($sqlActiveGifts);
$activeGifts = array();
while($rec = mysql_fetch_assoc($result)){
	$activeGifts[] = intval($rec['id']);
}
$activeGiftsString = implode(",", $activeGifts); // comma-delimited string of active gift ids



// (2)get all the invites for (1) that have a contribution with it. Invites are 'email' records where the 'template' = "invite". return all email records with this giftId/emailDigest pairing
$sqlInvitesWithContributions = "
SELECT 	e.`id`
FROM 	`emails` e
		INNER JOIN (SELECT ec.`emailDigest`, ec.`giftId` FROM `emails` ec INNER JOIN `messages` m ON ec.`id` = m.`emailId` WHERE ec.`template` = 'invite' AND m.`isContribution` = 1 AND m.`giftId` IN ($activeGiftsString)) c ON e.`emailDigest` = c.`emailDigest` AND e.`giftId` = c.`giftId`
WHERE 	e.`template` = 'invite'
AND 	e.`giftId` IN ($activeGiftsString)
";



// (3)get all the invitations (1) that have no contribution linked to it.
$sqlInvitesWithNoContributions = "
SELECT 		e.`id`
FROM 		`emails` e
WHERE 		e.`template` = 'invite'
AND 		e.`giftId` IN ($activeGiftsString)
AND 		e.`id` NOT IN ($sqlInvitesWithContributions)
GROUP BY 	e.`giftId`, e.`emailDigest`
HAVING 		MIN(e.`sentAt`)
";



// (4)get all the invitations from (1) with the following stats: number of reminders sent, date of most recent reminder, next reminder date (calculated), boolean for whether or not the invitee has unsubscribed from reminders
// the next reminder is calculated based on the gift delivery date, date the first invite was sent out, and the number of reminders the partner has configured to be sent. The intervals are divided evenly.
$sqlActiveInvitesWithNoContributionsStats = "
SELECT 	a.`giftId`,
		a.`emailDigest`,
		g.`deliveryDate`,
		a.`sentAt`,
		COALESCE(r.`reminderCount`, 0) AS `reminderCount`,
		m.`max` AS reminderMax,
		f.`freq` AS reminderFreq,
		s.`lastReminderSent`,
		o.`giftId` IS NOT NULL AS `isUnsubscribed`,
		DATE_ADD(a.`sentAt`, INTERVAL ((COALESCE(r.`reminderCount`, 0) + 1) * FLOOR(ABS(TIMESTAMPDIFF(HOUR, DATE_ADD(g.`deliveryDate`, INTERVAL -1 DAY), a.`sentAt`))/COALESCE(m.`max`, $defaultInviteReminderMax))) HOUR) AS 'nextReminderDate'
FROM 	`emails` a
		INNER JOIN ($sqlInvitesWithNoContributions) enc ON a.`id` = enc.`id`
		INNER JOIN `gifts` g ON a.`giftId` = g.`id`
		LEFT OUTER JOIN (SELECT CAST(`value` AS UNSIGNED) AS `max`, `partner` FROM `settings` WHERE `key`='inviteReminderMax' AND `env`='$safeEnv') m ON g.`partner` = m.`partner`
		LEFT OUTER JOIN (SELECT CAST(`value` AS UNSIGNED) AS `freq`, `partner` FROM `settings` WHERE `key`='inviteReminderFreq' AND `env`='$safeEnv') f ON g.`partner` = f.`partner`
		LEFT OUTER JOIN (SELECT COUNT(`id`) AS reminderCount, `giftId`, `emailDigest` FROM `emails` WHERE `template` = 'inviteReminder' AND `giftId` IN ($activeGiftsString) GROUP BY `giftId`, `emailDigest`) r ON r.`giftId` = a.`giftId` AND r.`emailDigest` = a.`emailDigest`
		LEFT OUTER JOIN (SELECT MAX(`sentAt`) AS `lastReminderSent`, `giftId`, `emailDigest` FROM `emails` WHERE `template` = 'inviteReminder' AND `giftId` IN ($activeGiftsString) GROUP BY `giftId`, `emailDigest`) s ON s.`giftId` = a.`giftId` AND s.`emailDigest` = a.`emailDigest`
		LEFT OUTER JOIN (SELECT `giftId`, `emailDigest` FROM `emails` WHERE `giftId` IN ($activeGiftsString) AND `template` = 'inviteReminderUnsubscribe') o ON a.`giftId` = o.`giftId` AND a.`emailDigest` = o.`emailDigest`
WHERE 	a.`giftId` IN ($activeGiftsString)
AND 	a.`template` = 'invite'
GROUP BY a.`giftId`, a.`emailDigest`
";


// (5)retrieve the email id of all the invitations that have not exceeded the max # of reminders, have not unsubscribed, have not received a reminder in the past FREQ hours, and is scheduled to receive their next reminder
$sql = "
SELECT 	e.`id` as emailId
FROM 	`emails` e
		INNER JOIN ($sqlActiveInvitesWithNoContributionsStats) s ON e.`giftId` = s.`giftId` AND e.`emailDigest` = s.`emailDigest`
		INNER JOIN `gifts` g ON e.`giftId` = g.`id`
WHERE 	g.`envName` = '$safeEnv'
AND 	s.`reminderCount` < COALESCE(s.`reminderMax`, $defaultInviteReminderMax)
AND 	s.`isUnsubscribed` = 0
AND 	(s.`lastReminderSent` IS NULL OR s.`lastReminderSent` < DATE_ADD(now(), INTERVAL -COALESCE(s.`reminderFreq`, $defaultInviteReminderFreq) HOUR))
AND 	s.nextReminderDate <= now()
";

log::debug($sql);
$result = db::query($sql);
while($rec = mysql_fetch_assoc($result)){
	$emailId = intval($rec['emailId']);

	$worker = new inviteReminderEmailWorker();
	$worker->send($emailId);
}
/* ################################################################################################################################################################# */
