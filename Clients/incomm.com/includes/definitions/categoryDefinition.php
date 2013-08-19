<?php

abstract class categoryDefinition extends baseModel {

	public $id;
	public $parentId;
	public $partner;
	public $status;
	public $weight;
	public $name;
	public $default;
	public $isDeleted;
	public $thirdparty;

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
		'parentId' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'YES',
				'default' => null
			)
		),
		'partner' => array(
			'scheme' => array(
				'type' => 'varchar(255)',
				'null' => 'YES',
				'default' => null
			)
		),
		'status' => array(
			'scheme' => array(
				'type' => 'tinyint(2)',
				'null' => 'NO',
				'default' => 0
			)
		),
		'weight' => array(
			'scheme' => array(
				'type' => 'int(11) unsigned',
				'null' => 'NO',
				'default' => 0
			)
		),
		'name' => array(
			'scheme' => array(
				'type' => 'varchar(255)',
				'null' => 'YES',
				'default' => null
			)
		),
		'default' => array(
			'scheme' => array(
				'type' => 'tinyint(2)',
				'null' => 'NO',
				'default' => 0
			)
		),
		"isDeleted" => array(
			"scheme" => array(
				"type" => "int(1) unsigned",
				"null" => "NO",
				"default" => 0
			)
		),
		'thirdparty' => array(
			'scheme' => array(
				'type' => 'int(11)',
				'null' => 'NO',
				'default' => 0
			)
		)
	);

}
