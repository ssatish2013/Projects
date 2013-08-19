<?php

abstract class partnerProductsDefinition extends baseModel {

	public $id;
	public $productId;
	public $partner;
	public $designId;

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
		'productId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO'
			)
		),
		'partner' => array(
			'scheme' => array(
				'type' => 'varchar(255)',
				'null' => 'NO'
			)
		),
		'designId' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'NO'
			)
		),
	);

}
