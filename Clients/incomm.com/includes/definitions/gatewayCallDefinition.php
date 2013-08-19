<?php
abstract class gatewayCallDefinition extends baseModel{
	public $id;
	public $transactionID;
	public $dateTime;
	public $method;
	public $requestMessage;
	public $responseMessage;
	public $success;	
	public $giftGuid;

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
		'transactionID'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'NO',
				'default'=>NULL,
				'key'=>'MUL'
			)
		),
		'method'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>NULL
			)
		),
		'dateTime'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'NO',
				'default'=>NULL
			)
		),
		'method'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'NO',
				'default'=>NULL
			)
		),
		'requestMessage'=>array(
			'scheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>''
			)
		),
		'responseMessage'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>''
			),
			'properties'=>array(
				'encrypt'=>true
			)
		),
		'success'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'giftGuid'=>array(
			'scheme'=>array(
				'type'=>'varchar(20)',
				'null'=>'NO',
				'default'=>NULL
			)
		),	
	);
}
