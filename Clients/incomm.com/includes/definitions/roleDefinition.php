<?php
abstract class roleDefinition extends baseModel{
	public $id;
	public $name;
	public $restrictedToPartner;
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
		'name'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'NO',
				'key'=>'UNI',
				'default'=>null
			)
		),
		'restrictedToPartner'=>array(
			'scheme'=>array(
				'type'=>'varchar(30)',
				'null'=>'YES',
				'default'=>NULL
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
				'default'=>NULL
			)
		)
	);

	protected $dbIndexes = array(

	);
}
