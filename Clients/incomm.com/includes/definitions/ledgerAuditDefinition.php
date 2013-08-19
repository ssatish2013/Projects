<?php
abstract class ledgerAuditDefinition extends baseModel{
	public $id;
	public $type; // Protected because this value is restricted
	public $giftId;
	public $shoppingCartId;
	public $messageId;
	public $amount;
	public $currency;
	public $reversalId;
	protected $timestamp; // Protected because you cannot set this in code

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
		'type'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'NO',
				'default'=>null
			)
		),
		'giftId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'shoppingCartId'=>array(
			'scheme'=>array(
				'type'=>'int(11)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'messageId'=>array(
			'scheme'=>array(
				'type'=>'int(11)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'amount'=>array(
			'scheme'=>array(
				'type'=>'decimal(6,2)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'currency'=>array(
			'scheme'=>array(
				'type'=>'varchar(6)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'reversalId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'timestamp'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'createdTimestamp'=>true
			)
		)
	);
}
