<?php
abstract class formSignatureDefinition extends baseModel{
	public $id;
	public $created;
	public $guid;
	public $sessionGuid;
	public $used;


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
		'created'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'NO',
				'default'=>'CURRENT_TIMESTAMP',
				'extra' => 'on update CURRENT_TIMESTAMP'
			)
		),
		'guid'=>array(
			'scheme'=>array(
				'type'=>'char(16)',
				'null'=>'NO',
				'default'=>null
			)
		),
		'sessionGuid'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'used'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		)
	);
}
