<?php

abstract class shippingOptionDefinition extends baseModel {
	public $id;
	public $carrierKey;
	public $serviceLevelKey;
	public $carrier;
	public $serviceLevel;
	public $shippingFee;

	protected $dbFields = array(
		'id' => array(
			'scheme' => array(
				'type'    => 'int(11) unsigned',
				'key'     => 'PRI',
				'null'    => 'NO',
				'default' => null,
				'extra'   => 'auto_increment'
			)
		),
		'carrierKey' => array(
			'scheme' => array(
				'type'    => 'varchar(16)',
				'null'    => 'YES',
				'default' => null,
				'key'     => 'MUL'
			)
		),
		'serviceLevelKey' => array(
			'scheme' => array(
				'type'    => 'varchar(16)',
				'null'    => 'YES',
				'default' => null
			)
		),
		'carrier' => array(
			'scheme' => array(
				'type'    => 'varchar(32)',
				'null'    => 'YES',
				'default' => null
			)
		),
		'serviceLevel' => array(
			'scheme' => array(
				'type'    => 'varchar(32)',
				'null'    => 'YES',
				'default' => null
			)
		),
		'shippingFee' => array(
			'scheme' => array(
				'type'    => 'decimal(6,2)',
				'null'    => 'YES',
				'default' => null
			)
		)
	);

}  //end shippingOptionDefinition
