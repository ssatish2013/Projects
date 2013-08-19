<?php

abstract class designCategoryDefinition extends baseModel {

	public $id;
	public $designId;
	public $categoryId;
	public $partnerProductId; //deprecated

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
		'categoryId' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'YES',
				'default' => null
			)
		),
		'designId' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'YES',
				'default' => null
			)
		),
		'partnerProductId' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'YES',
				'default' => null
			)
		)
	);

}
