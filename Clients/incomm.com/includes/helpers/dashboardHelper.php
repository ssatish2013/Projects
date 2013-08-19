<?php

class dashboardHelper {

	function __construct() {
		header("Content-Type: text/json");
	}

	public static function emptyArrayFactory( $amount ) {
		$arr = array();
		for ( $i = 0; $i < $amount; $i++ ) {
			$arr[$i] = 0;
		}
		return $arr;
	}

	public static function computeSpread( $spread, $results, $fromGMT, $untilGMT ) {

		$from = strtotime($fromGMT);
		$until = strtotime($untilGMT);
		$spreads = array(
			"today" => array(
				"dateChar" => "G",
				"arrayAmount" => 24
			),
			"week" => array(
				"dateChar" => "w",
				"arrayAmount" => 7
			),
			"month" => array(
				"dateChar" => "j",
				"arrayAmount" => 31
			)
		);
		$spreads["yesterday"] = $spreads["today"];

		if($spread == 'custom') { 
			if(($until - $from) <= 86400) {
				$spreads["custom"] = array(
					"dateChar" => "G",
					"arrayAmount" => 24
				);
			}
			else {
				$spreads["custom"] = array(
					"dateChar" => "j",
					"arrayAmount" => ($until - $from)/ 86400
				);
			}
		}

		// Iterate over results
		foreach( $results as $result ) {

			//assume we're incrementing by 1
			$add = 1;
			$dateGMT = strtotime($result['created']);
			$defaultTZ = date_default_timezone_get();
			date_default_timezone_set("America/New_York");

			//if we have a specific event value use that instead
			if(isset($result['eventValue'])) { 
				$add = $result['eventValue'];
			}

			if ( ! isset( $return[ $result['value'] ] ) ) {
				$return[ $result['value'] ] = self::emptyArrayFactory( $spreads[ $spread ]["arrayAmount"] );
			}
			
			if ( $spread !== "month" && $spreads[$spread]['dateChar'] !== "j") {
				$return[ $result['value']][( date($spreads[ $spread ]["dateChar"], $dateGMT ))] += $add;
			} else {
				$return[ $result['value']][ floor(( date( "U", $dateGMT - $from ) / 86400 ))] += $add;
			}

			date_default_timezone_set($defaultTZ);
		}

		if ( $spread == "week" ) {
			$defaultTZ = date_default_timezone_get();
			date_default_timezone_set("America/New_York");
			foreach( $return as  $key => &$arr ) {
				$day = date("w", $until );
				while( $day-- ){
					array_push( $arr, array_shift( $arr ));
				}
			}
			date_default_timezone_set($defaultTZ);
		}


		return $return;

	}

	/* COUNT BASED DASHBOARD GRAPHS */
	public static function flow() { 
		echo self::getEventCount('flow');
	}

	public static function dispute() { 
		echo self::getEventCount('dispute');
	}

	public static function promo() { 
		echo self::getEventCount('promo');
	}

	public static function kount() { 
		echo self::getEventCount('kount');
	}

	public static function screen() { 
		echo self::getEventCount('screen');
	}

	public static function design() { 
		echo self::getEventCount('design');
	}
	
	
	public static function incommXML() { 
		echo self::getEventCount('incommXML');
	}
	
	public static function incommGateway() { 
		echo self::getEventCount('incommGateway');
	}


	/* VALUE BASED DASHBOARD GRAPHS */
	public static function paypal() { 
		echo self::getEventValues('paypal');
	}

	public static function payment() { 
		echo self::getEventValues('payment');
	}

	public static function authorization() { 
		echo self::getEventValues('authorization');
	}

	public static function refund() { 
		echo self::getEventValues(array('refund', 'authorizationVoid'));
	}

	public static function getEventValues($key) {

		$eventTypes = array();
		if(is_array($key)) { 
			$eventTypes = $key;
		}
		else {
			$eventTypes[] = $key;
		}

		list($from, $until) = self::getTimes();
		$spread	= db::escape(request::unsignedPost("spread"));
		$secondsIn = array( 
			"hour" => 3600,
			"day" => 86400
		);
		
		$results = array();
		foreach($eventTypes as $key) { 
			//get all event ids we need
			$eventType = new eventTypeModel();
			$eventType->name = $key;
			$eventType->load('name');
			$events = eventModel::loadAll(array('typeId' => $eventType->id));

			$partner = db::escape( globals::partner() );
			$return = array();
			$resource;
			$temp;

			$eventIds = array_map(function($e) { return $e->id; }, eventModel::loadAll(array('typeId'=>$eventType->id)));

			$sql = "SELECT		( `l`.`created` ) AS `created`, " . 
					"`e`.`name` as `value`, " .
					"`l`.`value` as `eventValue` ".
					"FROM		`eventLogs` `l` " .
					"LEFT JOIN `events` `e` " .
					"ON `e`.`id`=`l`.`eventId` " .
					"WHERE		( `l`.`created` ) > '$from' " .
					"&&			( `l`.`created` ) < '$until' " .
					"&&			`l`.`partner` = '$partner' " .
					"&&			`l`.`eventId` IN(" .db::escape(implode(",",$eventIds)).");";
			$resource = db::query( $sql );

			while( $temp = mysql_fetch_assoc( $resource )) { 
				$results[] = $temp;
			}
		}

   	$return = self::computeSpread( $spread, $results, $from, $until );

		if(request::unsignedPost('download')) { 
			self::csv($return);
		}
		else {
			return json_encode( $return );
		}
	}

	//gets event and simply counts up the data
	public static function getEventCount($key) {

    $eventTypes = array();
    if(is_array($key)) { 
      $eventTypes = $key;
    }
    else {
      $eventTypes[] = $key;
    }

		list($from, $until) = self::getTimes();
		$spread	= db::escape(request::unsignedPost("spread"));
		$secondsIn = array( 
			"hour" => 3600,
			"day" => 86400
		);

    $results = array();
		foreach($eventTypes as $key) { 

			//get all event ids we need
			$eventType = new eventTypeModel();
			$eventType->name = $key;
			$eventType->load('name');
			$events = eventModel::loadAll(array('typeId' => $eventType->id));

			$partner = db::escape( globals::partner() );
			$return = array();
			$resource;
			$temp;

			$eventIds = array_map(function($e) { return $e->id; }, eventModel::loadAll(array('typeId'=>$eventType->id)));

			$sql = "SELECT    ( `l`.`created` ) AS `created`, " .
					"`l`.`value` as `value` ".
					"FROM   `eventLogs` `l` " .
					"LEFT JOIN `events` `e` " .
					"ON `e`.`id`=`l`.`eventId` " .
					"WHERE    ( `l`.`created` ) > '$from' " .
					"&&     ( `l`.`created` ) < '$until' " .
					"&&     `l`.`partner` = '$partner' " .
					"&&     `l`.`eventId` IN(" .db::escape(implode(",",$eventIds)).");";
			$resource = db::query( $sql );

			while( ( $temp = mysql_fetch_assoc( $resource )) && ( $results[] = $temp ) ){}
		}

		$return = self::computeSpread( $spread, $results, $from, $until );

		if(request::unsignedPost('download')) { 
			self::csv($return);
		}
		else {
			return json_encode( $return );
		}
	}

	public static function csv($data) { 

		$offset = request::unsignedPost("tzOffset");
		$fromEST	= strtotime(db::escape(request::unsignedPost("from"))." 00:00:00");
		$untilEST	= strtotime(db::escape(request::unsignedPost("until"))." 23:59:59");
    $spread = request::unsignedPost("spread");
    $action = request::unsignedPost("action");

		//different data for different spreads
    $spreads = array(
      "today" => array(
        "dateChar" => "Y-m-d H:i:s",
				"step" => "+1 Hour",	
        "numRows" => 24,
				"filename" => $action . '_' . date('Y-m-d', $fromEST)

      ),
      "week" => array(
        "dateChar" => "Y-m-d",
				"step" => "+1 Day",
        "numRows" => 7,
				"filename" => $action . '_' . date('Y-m-d', $fromEST) . '_' . date('Y-m-d', $untilEST)
      ),
      "month" => array(
        "dateChar" => "Y-m-d",
				"step" => "+1 Day",
        "numRows" => 31,
				"filename" => $action . '_' . date('Y-m-d', $fromEST) . '_' . date('Y-m-d', $untilEST)
      )
    );
		$spreads["yesterday"] = $spreads["today"];


		if($spread == 'custom') { 
			if(($untilEST - $fromEST) <= 86400) {
				$spreads["custom"] = array(
					"dateChar" => "Y-m-d H:i:s",
					"step" => "+1 Hour",
					"numRows" => 24,
					"filename" => "custom_". $action . '_' . date('Y-m-d', $fromEST)
				);
			}
			else {
				$spreads["custom"] = array(
					"dateChar" => "Y-m-d",
					"step" => "+1 Day",
					"numRows" => ($untilEST - $fromEST)/ 86400,
					"filename" => "custom_" . $action . '_' . date('Y-m-d', $fromEST) . '_' . date('Y-m-d', $untilEST-86400)
				);
			}
		}

		//set attachemnt headers
		header('Pragma: public');
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename='.$spreads[$spread]['filename'].'.csv');
		$csv = fopen('php://output', 'w');

		//setup our initial columns
		$cols = array();
		$rows = array();
		$cols[] = 'date';
		foreach($data as $set => $values) { 
			$cols[] = $set;
		}
		fputcsv($csv,$cols);

		//setup our date
		$curDate = $fromEST;

		//run through all rows we have data for
		for($i=0; $i<$spreads[$spread]['numRows']; $i++) { 

			//start a new column for this row
			$col = array();
			foreach($cols as $key) { 

				//if it's a date, make it a date and increment
				if($key == 'date') { 
					$col[] = date($spreads[$spread]['dateChar'], $curDate);
					$curDate = strtotime($spreads[$spread]['step'], $curDate);
				}

				//otherwise just pop in the data
				else {
					$col[] = $data[$key][$i];
				}
			}

			//output the row
			fputcsv($csv, $col);
		}

		fclose($csv);
	}

	public static function getTimes() {
		$from	= db::escape(request::unsignedPost("from"));
		$until	= db::escape(request::unsignedPost("until"));
		$defaultTZ = date_default_timezone_get();
		date_default_timezone_set("America/New_York");
		$fromEST = strtotime($from.' 00:00:00');
		$untilEST = strtotime($until.' 23:59:59');
		date_default_timezone_set($defaultTZ);
		$fromGMT = date('Y-m-d H:i:s', $fromEST);
		$untilGMT = date('Y-m-d H:i:s', $untilEST);
		
		return array($fromGMT, $untilGMT);
	}
}
