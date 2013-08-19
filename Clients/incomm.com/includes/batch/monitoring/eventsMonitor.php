<?php
	require_once(dirname(__FILE__)."/../../init.php");
	
	foreach (eventMonitorModel::loadAll(array('enabled' => 1)) as $eventMonitor) {
		/* @var $eventMonitor eventMonitorModel */
		if($eventMonitor->lastTriggered > date("Y-m-d H:i:s", strtotime("-1 hour"))){
			// No more then once an hour.
			continue;
		}
		$errors = array();
		if($eventMonitor->eventTypeId) {
			$eventType = new eventTypeModel($eventMonitor->eventTypeId);
			$eventIdObjs = eventModel::loadAll(array('typeId' => $eventMonitor->eventTypeId));
			$eventIds = array_map(function($event) { return $event->id; }, $eventIdObjs);
			$sql = "SELECT count(id) FROM eventLogs WHERE 'eventId' in (" . 
							implode(',', $eventIds) .
							") AND 'created' > '" . date("Y-m-d H:i:s", strtotime($eventMonitor->currentStartTime)) . "'" . 
							" AND 'created' < '" . date("Y-m-d H:i:s", strtotime($eventMonitor->currentEndTime)) . "'";
			$result = db::query($sql);
			$thisHour = mysql_fetch_array($result);
			$thisHour = $thisHour[0];
			$sql = "SELECT count(id) FROM eventLogs WHERE 'eventId' in (" . 
							implode(',', $eventIds) .
							") AND 'created' > '" . date("Y-m-d H:i:s", strtotime($eventMonitor->compareStartTime)) . "'" . 
							" AND 'created' < '" . date("Y-m-d H:i:s", strtotime($eventMonitor->compateEndTime)) . "'";
			$result = db::query($sql);
			$lastDay = mysql_fetch_array($result);
			$hourlyAverage = ($lastDay[0] / 24);
			
			if($thisHour < $hourlyAverage * $eventMonitor->minimumPercent){
				$errors[] = "Minimum percentage for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}
			if($thisHour > $hourlyAverage * $eventMonitor->maximumPercent){
				$errors[] = "Maximum percentage for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}
			if($thisHour < $eventMonitor->minimumHardLimit){
				$errors[] = "Minimum hard limit for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}
			if($thisHour > $eventMonitor->maximumHardLimit){
				$errors[] = "Maximum hard limit for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}
		} else if ($eventMonitor->eventId){
			$event = new eventModel($eventMonitor->eventId);
			$eventType = new eventTypeModel($event->typeId);
			$sql = "SELECT count(id) FROM eventLogs WHERE 'eventId' = " . $event->id .
							" AND 'created' > '" . date("Y-m-d H:i:s", strtotime($eventMonitor->currentStartTime)) . "'" . 
							" AND 'created' < '" . date("Y-m-d H:i:s", strtotime($eventMonitor->currentEndTime)) . "'";

			$result = db::query($sql);
			$thisHour = mysql_fetch_array($result);
			
			$sql = "SELECT count(id) FROM eventLogs WHERE 'eventId' = " . $event->id . 
							" AND 'created' > '" . date("Y-m-d H:i:s", strtotime($eventMonitor->compareStartTime)) . "'" . 
							" AND 'created' < '" . date("Y-m-d H:i:s", strtotime($eventMonitor->compateEndTime)) . "'";
			$result = db::query($sql);
			$lastDay = mysql_fetch_array($result);
			$hourlyAverage = ($lastDay[0] / 24);
			
			if($thisHour < $hourlyAverage * $eventMonitor->minimumPercent){
				$errors[] = "Minimum percentage for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}
			if($thisHour > $hourlyAverage * $eventMonitor->maximumPercent){
				$errors[] = "Maximum percentage for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}
			if($thisHour < $eventMonitor->minimumHardLimit){
				$errors[] = "Minimum hard limit for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}
			if($thisHour > $eventMonitor->maximumHardLimit){
				$errors[] = "Maximum hard limit for " . $eventType->name . " has been exceeded.  Value was $thisHour";
			}			
		}
		if(count($errors)){
			$eventMonitor->lastTriggered = date("Y-m-d H:i:s");
			$eventMonitor->save();
			$emails = settingModel::getSetting('monitoring', 'contactEmails');
			$emailsArray = explode(',', $emails);
			foreach ($emailsArray as $email) {
				$mailer = new mailer();
				$mailer->recipientName = 'System';
				$mailer->recipientEmail = $email;
				$mailer->template = 'system';
				view::set('message',print_r($errors,1));
				view::set('subject','[Monitoring] ERROR');
				$mailer->send();			
			}
		}
	}