<?php
class optInHelper{
	public static function saveOptIns(){
		if(request::unsignedPost('optInEmail')){
			try{
				$opt = new optInModel();
				$opt->partner = globals::partner();
				$opt->email=request::unsignedPost('transactionFromEmail');
				$opt->save();
			} catch (Exception $e) {  }
		}
		
		if(request::unsignedPost('optInPhone')){
			try{
				$opt = new optInModel();
				$opt->partner = globals::partner();
				$opt->phone=request::unsignedPost('transactionPhoneNumber');
				$opt->save();
			} catch (Exception $e) {  }
		}
	}
	
	public static function adminList(){
		$from = date("Y-m-d",strtotime(request::unsignedPost('from')));
		$to = date("Y-m-d",strtotime(request::unsignedPost('to')));
		$type = "email";
		
		$safePartner = db::escape(globals::partner());
		
		if(request::unsignedPost('exportType')=="phone"){
			$type="phone";
		}
		header("Content-Type: text/plain");
		header('Content-Disposition: attachment; filename='.$type.'OptIn'.$from."_".$to."txt");
		
		$query = "SELECT * FROM `optIns` WHERE `time` >='$from 00:00:00' AND `time` <= '$to 23:59:59' AND `partner`='$safePartner'";
		$res = db::query($query);
		while($row=  mysql_fetch_assoc($res)){
			$x = new optInModel();
			$x->assignValues($row);
			
			print "\"$x->firstName\",\"$x->lastName\",\"$x->email\",\"$x->phone\"\r\n";
		}
	}
}