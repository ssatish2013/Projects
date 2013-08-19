<?php
class adminEmailHelper extends dashboardHelper{
	public static function emailData(){
		list($from, $until) = self::getTimes();
		$spread	= db::escape(request::unsignedPost("spread"));
		
		$queryParts = array(
			"SENT"=>"",
			"OPENED"=>" AND `e`.`openedAt` IS NOT NULL",
			"CLICKED"=>" AND `e`.`clickedAt` IS NOT NULL",
			"BOUNCED"=>" AND `e`.`bouncedAt` IS NOT NULL",
		);
		
		$safePartner = db::escape(globals::partner());
		$safeTemplate = db::escape(request::unsignedPost('action'));
		
		$results = array();
		
		foreach($queryParts as $k=>$queryPart){
			$query = "select `e`.`sentAt` as `created`, '$k' as `value` FROM `emails` `e` WHERE `e`.`sentAt`>='$from' AND `e`.`sentAt`<'$until' AND `e`.`partner`='$safePartner' AND `e`.`template`='$safeTemplate'".$queryPart;
			$resource = db::query( $query );

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
}