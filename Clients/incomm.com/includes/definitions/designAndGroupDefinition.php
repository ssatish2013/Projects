<?php

abstract class designAndGroupDefinition extends baseModel {

	public $id;
	public $designId;
	public $productGroupId;

	protected $dbFields = array(
		'id' => array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'key'=>'PRI',
				'null'=>'NO',
				'default'=>null,
				'extra'=>'auto_increment'
			)
		),
		'designId' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'YES',
				'default' => null
			)
		),
		'productGroupId' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'YES',
				'default' => null
			)
		),
	);

}
