<?php
abstract class userDefinition extends baseModel{
	public $id;
	public $firstName;
	public $lastName;
	public $email;
	public $password;
	public $passwordResetGuid;
	public $passwordResetExpires;
	public $created;
	public $updated;
	public $goodUser;
	public $badUser;

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
		'firstName'=>array(
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
		'lastName'=>array(
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
		'password'=>array(
			'scheme'=>array(
				'type'=>'varchar(128)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'facebookId'=>array(
			'scheme'=>array(
				'type'=>'bigint(20)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'facebookAccessToken'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true
			)
		),
		'passwordResetGuid'=>array(
			'scheme'=>array(
				'type'=>'varchar(16)',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'passwordResetExpires'=>array(
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
		),
		'updated'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'updatedTimestamp'=>true
			)
		),
		'goodUser'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
		),
		'badUser'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
		)
	);
}
