<?php
abstract class userAccessTokenDefinition extends baseModel{
	public $id;
	public $userId;
	public $applicationId;
	public $applicationName;
	public $applicationUserId;
	public $accessToken;
	public $created;
	public $updated;

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
		'userId'=>array(
			'scheme'=>array(
				'type'=>'int(11)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'applicationId'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'applicationName'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'applicationUserId'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'accessToken'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true
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
	);
}
