<?php
abstract class permissionDefinition extends baseModel{
	public $id;
	public $key;
	public $name;
	public $status;
	public $description;

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
		'key'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'NO',
				'default'=>null,
				'key'=>'UNI'
			)
		),
		'name'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'NO',
				'default'=>null
			)
		),
		'status'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'NO',
				'default'=>1
			)
		),
		'description'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
	);

	protected $dbIndexes = array(

	);
}
