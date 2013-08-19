<?php
abstract class partnerLoaderDefinition extends baseModel{
	public $id;
	public $type;
	public $value;
	public $partner;

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
				'type'=>'varchar(128)',
				'null'=>'NO',
				'key'=>'MUL'
			)
		),
		'value'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'partner'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		)
	);

	protected $dbIndexes = array(

	);
}
