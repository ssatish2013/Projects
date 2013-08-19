<?php
abstract class sessionDefinition extends baseModel{
	public $id;
	public $guid;
	public $data;
	public $expires;

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
		'guid'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'data'=>array(
			'scheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			)
		),
		'expires'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			)
		)
	);
}