<?php

abstract class messageItemDefinition extends baseModel{
	public $id;
	public $messageId;
	public $itemType;
	public $itemData;
	public $title;
	public $amount;
	public $amountCallback;
	public $seqNum;
	public $created;
	
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
			'messageId'=>array(
					'scheme'=>array(
							'type'=>'int(11) unsigned',
							'null'=>'NO'
					)
			),
			'itemType'=>array(
					'scheme'=>array(
							'type'=>'varchar(45)',
							'null'=>'NO'
					)
			),
			'itemData'=>array(
					'scheme'=>array(
							'type'=>'varchar(255)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'title'=>array(
					'scheme'=>array(
							'type'=>'varchar(255)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'amount'=>array(
					'scheme'=>array(
							'type'=>'decimal(8,2)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'amountCallback'=>array(
					'scheme'=>array(
							'type'=>'varchar(255)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'seqNum'=>array(
					'scheme'=>array(
							'type'=>'int(11)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'created'=>array(
					'scheme'=>array(
							'type'=>'timestamp',
							'null'=>'YES',
							'default'=>null
					),
					'properties'=>array(
							'createdTimestamp'=>true
					)
			),
	);
}