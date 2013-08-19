<?php

abstract class eventDefinition extends baseModel {

	public $id;
	public $typeId;
	public $name;

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
		'typeId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null
			)
		),
		'name'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'NO',
				'default'=>null
			)
		)
	);

}
