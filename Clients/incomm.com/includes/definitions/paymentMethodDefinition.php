<?php
abstract class paymentMethodDefinition extends baseModel{
	public $id;
	public $partner;
	public $pluginName;
	protected $settings;
	public $created;
	public $supportedCurrencies;
	public $displayOrder;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'key'=>'PRI',
				'null'=>'NO',
				'default'=>null,
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
		'pluginName'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'settings'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
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
		'supportedCurrencies'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>"ALLOW_ALL",
			)
		),
		'displayOrder'=>array(
			'scheme'=>array(
				'type'=>'int(11)',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL',
			)
		),
	);
}