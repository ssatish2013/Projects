<?php
abstract class inventory{
	protected $product = null;
	protected $gift = null;
	
	public function __construct($product, $gift){
		$this->product = $product;
		$this->gift = $gift;
	}
	
	public function activate(){
		// You should override this
	}
	
	public function deactivate(){
		// You should override this
	}
	
	public function reverse(){
		// You should override this
	}
	
	
}