<?php
abstract class sampleDefinition extends baseModel{
	public $id;
	public $name;
	public $birthday;
	public $shoeSize;
	public $createdAt;
	public $updatedAt;
	public $createdOrUpdatedAt;
	public $topSecret;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null,
				'extra'=>'auto_increment',
				'key'=>'PRI'
			)
		),
		'name'=>array(
			'scheme'=>array(
				'type'=>'varchar(50)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'birthday'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'shoeSize'=>array(
			'scheme'=>array(
				'type'=>'decimal(3,1)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'createdAt'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'createdTimestamp'=>true
			)
		),
		'updatedAt'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'updatedTimestamp'=>true
			)
		),
		'createdOrUpdatedAt'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'createdTimestamp'=>true,
				'updatedTimestamp'=>true
			)
		),
		'topSecret'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true
			)
		)
	);

	protected $dbIndexes = array(

	);
}