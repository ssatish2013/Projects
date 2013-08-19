<?php
abstract class promoTransactionDefinition extends baseModel{
	public $id;
	public $promoId;
	public $messageId;
	public $promoTriggerId;
	public $discountAmount;

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
		'messageId'=>array(
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
		'discountAmount'=>array(
			'scheme'=>array(
				'type'=>'decimal(6,2) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		)
	);
}