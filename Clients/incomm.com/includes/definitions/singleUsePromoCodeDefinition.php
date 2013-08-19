<?php

abstract class singleUsePromoCodeDefinition extends baseModel {

	public $id;
	public $prefix;
	public $code;
	public $pin;
	public $promoTriggerId;
	public $created;
	public $redeemed;

	protected $dbFields = array(
		'id' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'NO',
				'key'  => 'PRI',
				'default' => null,
				'extra'=> 'auto_increment'
			)
		),
		'prefix' => array(
			'scheme' => array(
				'type' => 'varchar(8)',
				'null' => 'YES',
				'key' =>'MUL',
				'default' => null
			)
		),		
		'code' => array(
			'scheme' => array(
				'type' => 'varchar(5)',
				'null' => 'YES',
				'default' => null
			)
		),
		'pin' => array(
			'scheme' => array(
				'type' => 'int(4) unsigned zerofill',
				'null' => 'YES',
				'default' => null
			)
		),
		'promoTriggerId' => array(
			'scheme' => array(
				'type' => 'int(11)',
				'null' => 'YES',
				'default' => null
			)
		),
		'created' => array(
			'scheme' => array(
				'type' => 'timestamp',
				'null' => 'YES',
				'default' => null
			)
		),
		'redeemed' => array(
			'scheme' => array(
				'type' => 'timestamp',
				'null' => 'YES',
				'default' => null
			)
		),
		
	);

}
