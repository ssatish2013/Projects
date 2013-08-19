<?php
class messageModel extends messageDefinition {

	private $design=null;
	private $gift=null;
	private $selectedShippingOption = null;
	private $shippingDetail = null;
	public $shoppingCart = null;
	private $user = null;

	public function _setGift($val){
		$this->gift= $val;
	}

	public function _getProductId(){
		return $this->getGift()->productId;
	}

	public function _getBonusAmount(){
		$bonusItem = $this->getMessageItems(messageItemModel::TYPE_BONUS);
		return count($bonusItem) > 0
			? $bonusItem[0]->amount
			: 0;
	}

	public function getActivePromoTrigger(){
		$trigger=promoTriggerModel::getActiveTrigger($this);
		return $trigger;
	}

	public function getActivePromotion(){
		/* Load the promo if there is one */
		$trigger = $this->getActivePromoTrigger();

		if(!is_null($trigger)){
			return $trigger->getPromo();
		}
		return null;
	}

	public function getDiscountedPrice(){
		/* Return the total someone will pay for the gift */
		return $this->amount + $this->getFeesTotal() - $this->getAmountOfDiscount();
	}

	public function getAmountOfDiscount($fromtrigger=false){
		/* Try to get the promoTransaction first if the messasge isPaid
		 * And if can't found a promoTransaction for this active message
		 * which means there is no promo code entered, return 0
		 *
		 * Unless we specifically ask to calculate discount from trigger,
		 * The only place we need to do so is in promoHelper::recordPromoTransaction()
		 * which message isPaid = ture, and there is no promoTransaction saved yet.
		 * */
		if ($this->isPaid() && !$fromtrigger){
			$promoTransaction = new promoTransactionModel();
			$promoTransaction->messageId = $this->id;
			if(!$promoTransaction->load()){
				return 0;
			}
			//return pre-recorded discount amount.
			else{
				return $promoTransaction->discountAmount;
			}
		}

		/* Return just the amount of the discount */
		$trigger=promoTriggerModel::getActiveTrigger($this);
		if(is_null($trigger)){
			return 0;
		} else {
			return $trigger->getDiscountAmount();
		}
	}

	public function validateGiftCreate($formType=null){
		if(parent::validate($formType)){
			// Add the userId
			$this->guid = randomHelper::guid(16);
			$this->status = 0;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return giftModel
	 */

	public function getGift() {
		return $this->gift ?: ( $this->gift = new giftModel( $this->giftId ) );
	}

	/**
	 * @return designModel
	 */

	public function getDesign() {
		return $this->design ?: ( $this->design = new designModel( $this->getGift()->designId ) );
	}

	public function getSelectedShippingOption() {
		if (!isset($this->selectedShippingOption)) {
			$shippingDetail = new shippingDetailModel();
			$shippingDetail->giftId = $this->giftId;
			$shippingDetail->shoppingCartId = $this->shoppingCartId;
			if (!$shippingDetail->load()) {
				return null;
			}
			$this->selectedShippingOption = new shippingOptionModel($shippingDetail->shippingOptionId);
		}
		return $this->selectedShippingOption;
	}

	public function getShippingDetail() {
		if (!isset($this->shippingDetail)) {
			$shippingDetail = new shippingDetailModel();
			$shippingDetail->giftId = $this->giftId;
			$shippingDetail->shoppingCartId = $this->shoppingCartId;
			$shippingDetail->load();
			$this->shippingDetail = $shippingDetail;
		}
		return $this->shippingDetail;
	}

	public function assignToGift($gift){
		if(isset($gift->id)){
			$this->giftId = $gift->id;
			$this->currency = $gift->currency;
		} else {
			throw new Exception('Sorry, you cant add a message to a gift that hasn\'t been saved!');
		}
	}

	public function assignToShoppingCart($shoppingCart){
		if(isset($shoppingCart->id)){
			if(!isset($shoppingCart->currency) || $this->currency == $shoppingCart->currency){
				$this->shoppingCartId = $shoppingCart->id;
			} else {
				throw new Exception('Sorry, the currency on this message is different from the shopping cart.');
			}
		} else {
			throw new Exception('Sorry, you cant add a message to a shoppingCart that hasn\'t been saved!');
		}
		//apply coupon when added to cart
		$this->applyCoupon();
		//also check if addtional fee applicable
		$this->applyFee();
	}

	public function assignToUser($user){
		if(isset($user->id)){
			$this->userId = $user->id;
		} else {
			throw new Exception('Sorry, you cant add a message to a user that hasn\'t been saved!');
		}
	}

	public function flagAsContribution(){
		$this->isContribution = true;
	}

	private function loadShoppingCart(){
		if(!$this->shoppingCart){
			$this->shoppingCart = new shoppingCartModel($this->shoppingCartId);
		}
	}

	public function setRecording ($client) {
		$recording = recordingModel::loadAll (array ("clientKey" => $client));
		if (count ($recording)) {
			foreach ($recording as $r) {
				$this->recordingId = $r->id;
			}
			$this->save ();
		}
		return $this;
	}

	public function getRecording () {
		if (!empty ($this->recordingId))
			return new recordingModel ($this->recordingId);
		return null;
	}

	public function setVideoLink ($link) {
		// turn a fully-qualified youtube URL into just the view link piece
		$old = $this->videoLink;
		if (empty ($link)) {
			$this->videoLink = "";
		} else {
			$matches = array ();
			preg_match ("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $link, $matches);
			if (count ($matches)) {
				$this->videoLink = $matches[0];
			}
		}
		if ($this->videoLink != $old) $this->save ();
		return $this;
	}

	public function _setAmount ($amount) {
		if ($amount == "custom") {
			$amount = request::unsignedPost("amountCustomText");
		}
		$this->amount = $amount;
	}

	/**
	 * @return shoppingCartModel
	 */
	public function getShoppingCart(){
		$this->loadShoppingCart();
		return $this->shoppingCart;
	}

	public function shouldDisplay(){
		$this->loadShoppingCart();
		if($this->shoppingCart->getTransaction()->isAuthorized() || $this->shoppingCart->isPaid()){
			return true;
		}
		return false;
	}

	public function isPaid(){
		$this->loadShoppingCart();
		return $this->shoppingCart->isPaid();
	}

	public function getUser(){
		if(!$this->user){
			$this->user = new userModel($this->userId);
		}
		return $this->user;
	}

	public function getMessageItems($itemType=null){
		$loadby = array('messageId'=>$this->id);
		if (isset($itemType)) $loadby['itemType']= $itemType;
		return messageItemModel::loadAll($loadby,null,'seqNum');
	}

	public function clearCouponItems(){
		$items = $this->getMessageItems();
		foreach($items as $item){
			if ($item->itemType == messageItemModel::TYPE_DISCOUNT || $item->itemType == messageItemModel::TYPE_BONUS){
				$item->destroy(true);
			}
		}
		$this->promoCode=null;
		$this->save();
	}

	public function applyCoupon(){
		//clear previous message item if found any
		$this->removeItems();
		if (!isset($this->promoCode)) return;
		$promoTrigger = $this->getActivePromoTrigger();
		if(!is_null($promoTrigger) && $promoTrigger->isActive()){
			//code checked is ok
			$promo = $promoTrigger->getPromo();
			if ($promo->bonusAmount>0){
				messageItemModel::createBonusItem($this,$promoTrigger);
			}
			else if ($promo->discountAmount>0 || $promo->discountPercent >0){
				messageItemModel::createDiscountItem($this,$promoTrigger);
			}
		}
	}

	public function applyFee(){
		//find if any applicable fee for this message
		$fees = productFeeModel::getApplicableFees($this->getGift()->productGroupId);
		foreach($fees as $fee){
			messageItemModel::createFeeItem($this, $fee);
		}
	}

	public function removeItems($clearcode = false){
		$items = $this->getMessageItems();
		foreach($items as $item){
				$item->destroy(true);
		}
		if ($clearcode){
			$this->promoCode=null;
			$this->save();
		}
	}

	public function getFeesTotal(){
		$fees = $this->getMessageItems(messageItemModel::TYPE_FEE);
		return array_reduce($fees, function($total,$fee){
			return $total+=$fee->amount;
		});
	}

	public function getFeesTitle(){
		$fees = $this->getMessageItems(messageItemModel::TYPE_FEE);
		$ret = array();
		foreach($fees as $fee){
			$ret[]=$fee->title;
		}
		return implode(',',$ret);
	}
}
