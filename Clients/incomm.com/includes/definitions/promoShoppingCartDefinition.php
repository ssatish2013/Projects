<?php
abstract class promoShoppingCartDefinition extends baseModel{
	public $id;
	public $promoId;
	public $promoTriggerId;
	public $shoppingCartId;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null,
				'key'=>'PRI',
				'extra'=>'auto_increment'
			)
		),
		'promoId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'promoTriggerId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'shoppingCartId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		)
	);
}