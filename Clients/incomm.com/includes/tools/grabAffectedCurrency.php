<?php
require_once(dirname(__FILE__)."/../init.php");

$query = "
	SELECT  G.id as gId, 
        G.created as gCreated,
        G.currency as gCurrency,
        M.amount, M.refunded,
        P.id as pId,
        P.currency as pCurrency,
        P.description,
        G.claimed,
				G.recipientNameCrypt as RecipientNameCrypt,
				G.recipientEmailCrypt as RecipientEmailCrypt,
				U.firstNameCrypt as PurchaserFirstNameCrypt,
				U.lastNameCrypt as PurchaserLastNameCrypt,
				U.emailCrypt as PurchaserEmailCrypt,
				I.pan as Serial,
				I.pinCrypt as PinCrypt
FROM    gifts G
JOIN    products P ON G.productId=P.id
JOIN    messages M ON G.id = M.giftId
JOIN		users U ON M.userId = U.id
JOIN		reservations R on G.id = R.giftId
JOIN		inventorys I on R.inventoryId = I.id
WHERE   G.currency != P.currency
AND     paid = 1
";

$result = db::query($query);


$results = array();

while($row = mysql_fetch_assoc($result)){
	$obj=$row;
	foreach($obj as $k=>$v){
		if(substr($k,-5)=="Crypt"){
			$obj[substr($k,0,-5)]=baseModel::decrypt($v);
			unset($obj[$k]);
		}
	}
	$results[]=$obj;
}

$headers = array_keys($results[0]);
echo implode(",",array_map(function($x){return '"'.str_replace(array('\\','"'),array('\\\\','\\"'),$x).'"';},$headers))."\n";

foreach($results as $r){
	echo implode(",",array_map(function($x){return '"'.str_replace(array('\\','"'),array('\\\\','\\"'),$x).'"';},$r))."\n";
}