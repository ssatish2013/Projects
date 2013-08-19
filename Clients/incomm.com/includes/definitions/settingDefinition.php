<?php
abstract class settingDefinition extends baseModel{
	public $id;
	public $partner;
	public $env;
	public $category;
	public $key;
	protected $value;
	protected $encrypted;


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
		'partner'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'env'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'category'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'key'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'value'=>array(
			'scheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			)
		),
		'encrypted'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		)
	);

	protected $dbIndexes = array(

	);
}
