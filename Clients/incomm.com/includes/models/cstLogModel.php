<?php
class cstLogModel extends cstLogDefinition{
	
	public function getGift() {
		return new giftModel( $this->giftId );
	}
	
	public function getShoppingCart() {
		return new shoppingCartModel($this->shoppingCartId);
	}
	
	public static function getRelated($giftId) {
		
		$gift = new giftModel($giftId);
		$params = array('giftId' => $gift->id);
		$shoppingCarts = $gift->getShoppingCarts(true);
		$messages = $gift->getMessages(true);
		$transactions = $gift->getAllTransactions();
		$fromEmails = $gift->getAllContributorEmails();
		
		if(!empty($shoppingCarts))
			$params['shoppingCartId'] = array_map(array(__CLASS__, 'mapModelIdToParameterArray'), $shoppingCarts);
		if(!empty($messages))
			$params['messageId'] = array_map(array(__CLASS__, 'mapModelIdToParameterArray'), $messages);
		if(!empty($transactions))
			$params['transactionId'] = array_map(array(__CLASS__, 'mapModelIdToParameterArray'), $transactions);
		if(!empty($fromEmails))
			$params['fromEmail'] = $fromEmails;
		
		$logs = self::loadAll($params, null, 'timestamp ASC', null, 'OR', true);
		return $logs;
	}
	
	protected static function mapModelIdToParameterArray($model) {
		return $model->id;
	}
}