<?php
class shoppingCartModel extends shoppingCartDefinition {
	private $transaction = null;
	private $messages = null;
	private static $_count = null;
	
	public function __construct($id = null) {
		if(isset($id)){
			return parent::__construct($id);
		}
		$this->partner = globals::partner();
		$this->sessionGuid = session_id();
		$this->isCurrent = 1;
		$this->load();
		$this->setReferer ();
		if(!$this->id){
			$this->save();
		}
	}
	
	public function setReferer () {
		if (array_key_exists ('referer', $_SESSION) && !empty ($_SESSION['referer'])) {
			$this->referer = $_SESSION['referer'];
		}
	}
	
	public function reserveAllProducts(){
		try{
			/* @var $message messageModel */
			foreach($this->getAllMessages() as $message){
				$gift = $message->getGift();
				$gift->getProduct()->getReservation($gift, $message->amount + $message->bonusAmount);
			}
		} catch(Exception $e){
			$this->cleanUpReservations();
			throw $e;
		}
	}
	
	public function cleanUpReservations(){
		try{
			foreach($this->getAllMessages() as $message){
				/* @var $gift giftModel */
				$gift = $message->getGift();
				if($gift->unverifiedAmount == 0){
					// Only unreserve if we're the last gift
					$gift->getProduct()->getReservation($gift)->unreserve();
				}
			}
		} catch (Exception $e){
			// This isn't that big of a deal, it'll get cleaned up by a worker later
		}
	}

	public function loadForDisplay() {
		$this->getTotal();
		$this->getAllMessages();
		foreach( $this->messages as &$message ) {
			$message->getDesign(); 
		}
	}
	
	public function assignCurrencyFromMessage($message){
		if(isset($message->currency)){
			if(!isset($this->currency) || $this->currency == $message->currency){
				$this->currency = $message->currency;
			} else {
				throw new Exception('Sorry, the currency on this message is different from the shopping cart.');
			}
		} else {
			throw new Exception('Sorry, the message doesn\'t have a currency assigned.');
		}
	}
	
	public function promotionComplete(){
		$this->messages = messageModel::loadAll( $this );
		foreach($this->messages as $message){
			/* @var $message messageModel */
			promoHelper::recordPromoTransaction($message);
		}
		return;
	}
	
	
	/*
	 * @TODO I think we can remove transactionComplete now, we are going to be using
	 * isPaid and isAuthorized from now on.
	 */
	
	public function transactionComplete(transactionModel $transaction){
		//Update the shopping cart and all the message to reflect the payment has been made
		$this->messages = messageModel::loadAll( $this );
		foreach ($this->messages as $message) {
			/* @var $message messageModel */
			$message->transactionId = $transaction->id;
			$message->save();
			$gift = new giftModel($message->giftId);
			$gift->paid = true;
			$gift->save();
		}

		//whoops! If it's paid for, it definitely isn't current anymore!
		//(mostly for paypal transactions where there's no auth)
		$this->isCurrent = 0;
		return $this;
	}

	public function getTotal(){
		return array_reduce( $this->getAllMessages(), function( $total, $message ) {
			/* @var $message messageModel */
			return $total += $message->getDiscountedPrice();
		}, 0);

	}

	public function getAllMessages(){
		if($this->messages===null){
			$this->messages = messageModel::loadAll( array('shoppingCartId'=>$this->id) );
		}
		return $this->messages;
	}

	public function getAllGifts() { 
		$messages = $this->getAllMessages();
		$gifts = array();
		$giftIds = array();

		foreach($messages as $message) { 
			$giftIds[] = $message->giftId;
		}
		
		foreach(array_unique($giftIds) as $giftId) { 
			$gifts[] = new giftModel($giftId);
		}
		return $gifts;
	}
	
	public function getSupportedPaymentPlugins() {
		$paymentMethods = paymentMethodModel::loadAll(array(
			'partner' => $this->partner,
		), null, 'displayOrder');
		$supportedPaymentMethods = array();
		foreach($paymentMethods as $paymentMethod) {
			if($paymentMethod->supportsCurrency($this->currency))
				$supportedPaymentMethods[] = $paymentMethod;
		}
		return $supportedPaymentMethods;
	}
	
	public function forceCheckoutOnCreate(){
		// Check the messages to see if any of them are "contributions, if so force the person to checkout
		if(!$this->canCreate()){
			$_SESSION['flashMessage'] = "Sorry, you will need complete this purchase before you can send another gift.";
			view::Redirect('gift', 'cart');
		}
	}
	
	public function forceCheckoutOnContribute(){
		// Check the messages to see if any of them are "contributions, if so force the person to checkout
		if(!$this->canContribute()){
			$_SESSION['flashMessage'] = "Sorry, you will need complete this purchase before you can send another gift.";
			view::Redirect('cart', 'index');
		}
	}
	
	public function canContribute(){
		$this->getAllMessages();
		if(count($this->messages)){
			if(!$this->editingContributeMessage()){
				return false;				
			}
		}
		return true;
	}
	
	private function editingContributeMessage(){
		if($this->messages[0]->guid == request::url('messageGuid')){
			return true;
		}
		return false;
	}


	public function canCreate(){
		$this->getAllMessages();
		foreach ($this->messages as $message) {
			/* @var $message messageModel */
			if($message->isContribution){
				return false;
			}
		}
		return true;
	}
	
	public function messageCount(){
		if($this->messages===null){
			$this->messages = messageModel::loadAll( $this );
		}
		return count($this->messages);
	}

	public static function getCount() {
		if (!isset(self::$_count)) {
			$shoppingCart = new self();
			self::$_count = count(messageModel::loadAll($shoppingCart));
		}
		return self::$_count;
	}

	public function getTransaction(){
		if(!$this->transaction){
			$this->transaction = new transactionModel();
			$this->transaction->shoppingCartId = $this->id;
			$this->transaction->load();
		}
		return $this->transaction;
	}

	public function isPaid(){
		$this->getTransaction();
		return $this->transaction->isPaid();
	}

	public function hasPhysicalDeliverableGift() {
		$sql = 'SELECT g.id FROM shoppingCarts sc
				LEFT JOIN messages m ON m.shoppingCartId = sc.id
				LEFT JOIN gifts g ON m.giftId = g.id
				WHERE g.physicalDelivery = 1 AND sc.id = ' . (int)$this->id;
		$rs = db::query($sql);
		return (mysql_num_rows($rs) > 0);
	}
}
