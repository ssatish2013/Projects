<?php
abstract class displayProductDefinition extends baseModel {
	public $id;
	public $partner;
	public $currency;
	public $isPhysical;
	public $displayAmount;
	public $productGuid;
	public $descriptionLanguageVariable;
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
				'default' => '',
			)
		),
		'currency' => array(
			'scheme' => array(
				'type' => 'char(3)',
				'null' => 'NO',
				'default' => 'USD',
			)
		),
		'isPhysical' => array(
			'scheme' => array(
				'type' => 'tinyint(1) unsigned',
				'null' => 'NO',
				'default' => 0,
			)
		),
		'displayAmount' => array(
			'scheme' => array(
				'type' => 'decimal(6,2) unsigned',
				'null' => 'NO',
				'default' => '0.00',
			)
		),

		'productGuid' => array(
			'scheme' => array(
				'type' => 'varchar(32)',
				'null' => 'NO',
				'default' => '',
			)
		),
		'descriptionLanguageVariable' => array(
			'scheme' => array(
				'type' => 'varchar(255)',
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
