<?php
abstract class recipientGuidDefinition extends baseModel{
	public $id;
	public $giftId;
	public $guid;
	public $expires;
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
		'giftId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'guid'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'expires'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
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
		)
	);
}