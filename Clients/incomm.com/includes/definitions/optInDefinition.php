<?php
abstract class optInDefinition extends baseModel{
	public $id;
	public $partner;
	public $time;
	public $firstName;
	public $lastName;
	public $email;
	public $phone;

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
		'time'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'createdTimestamp'=>true
			)
		),
			'firstName'=>array(
					'scheme'=>array(
							'type'=>'varchar(255)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'lastName'=>array(
					'scheme'=>array(
							'type'=>'varchar(255)',
							'null'=>'YES',
							'default'=>null
					)
			),
		'email'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'digestScheme'=>array(
				'type'=>'char(32)',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			),
			'properties'=>array(
				'encrypt'=>true,
				'digest'=>true
			)
		),
		'phone'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'digestScheme'=>array(
				'type'=>'char(32)',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			),
			'properties'=>array(
				'encrypt'=>true,
				'digest'=>true
			)
		),

	);
}
