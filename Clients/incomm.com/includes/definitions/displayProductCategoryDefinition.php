<?php
abstract class displayProductCategoryDefinition extends baseModel {
	public $id;
	public $partner;
	public $currency;
	public $isPhysical;
	public $format;
	public $hasOpen;
	public $openMin;
	public $openMax;
	public $openProductGuid;
	public $sortOrder;
	

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
		'partner' => array(
			'scheme' => array(
				'type' => 'varchar(255)',
				'null' => 'NO',
				'key'  => 'MUL',
				'default' => '',
			)
		),
		'currency' => array(
			'scheme' => array(
				'type' => 'char(3)',
				'null' => 'NO',
				'default' => '',
			)
		),
		'isPhysical' => array(
			'scheme' => array(
				'type' => 'tinyint(1) unsigned',
				'null' => 'NO',
				'default' => 0,
			)
		),
		'format' => array(
			'scheme' => array(
				'type' => 'varchar(32)',
				'null' => 'YES',
				'default' => null,
			)
		),
		'hasOpen' => array(
			'scheme' => array(
				'type' => 'tinyint(1) unsigned',
				'null' => 'NO',
				'default' => 0,
			)
		),
		'openMin' => array(
			'scheme' => array(
				'type' => 'int(11)',
				'null' => 'YES',
				'default' => null,
			)
		),
		'openMax' => array(
			'scheme' => array(
				'type' => 'int(11)',
				'null' => 'YES',
				'default' => null,
			)
		),
		'openProductGuid' => array(
			'scheme' => array(
				'type' => 'varchar(32)',
				'null' => 'YES',
				'default' => null,
			)
		),
		'sortOrder' => array(
			'scheme' => array(
				'type' => 'int(11)',
				'null' => 'NO',
				'default' => 0,
			)
		),
	);

}
