<?php

abstract class eventLogDefinition extends baseModel {

	public $id;
	public $partner;
	public $eventId;
	public $created;
	public $value;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null,
				'extra'=> 'auto_increment',
				'key'=>'PRI'
			)
		),
		'partner' => array(
			'scheme' => array(
				'type' => 'varchar(64)',
				'null' => 'NO',
				'default' => null,
				'extra' => null
			)
		),
		'eventId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null,
				'extra'=> null
			)
		),
		'created'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'NO',
				'default'=>"CURRENT_TIMESTAMP",
				'extra'=>null
			)
		),
		'value'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		)
	);

}
