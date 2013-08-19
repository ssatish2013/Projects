<?php
require_once(dirname(__FILE__)."/../init.php");

$safeEnv = db::escape(Env::getEnvName());

$now = date("Y-m-d H:i:s");
$sql = "
        SELECT * FROM gifts
        WHERE `envName`='$safeEnv' AND
                `delivered` IS NULL AND
                `deliveryDate` < '$now' AND
                `physicalDelivery` = 0 AND
                `addedToDeliveryQueue` IS NULL AND
                `paid` IS NOT NULL AND
                (`inScreeningQueue` IS NULL OR inScreeningQueue = 0)
        LIMIT 30";
$result = db::query($sql);

$queued = array();
while ($i = mysql_fetch_assoc($result)) {
	$gift = new giftModel();
	$gift->assignValues($i, true);
	if (!$gift->rejected && ($gift->unverifiedAmount || $gift->paidAmount)) {
		$worker = new deliveryWorker();
		$worker->send($gift->id);
		$gift->addedToDeliveryQueue = true;
		$gift->save();
		$queued[] = $gift->id;
		log::info("Added Gift $gift->id to the delivery queue.");
	} else {
		$gift->addedToDeliveryQueue = true; //Lets fix this later
		$gift->save();
	}
}
