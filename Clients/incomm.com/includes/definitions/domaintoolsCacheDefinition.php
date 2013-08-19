<?php
abstract class domaintoolsCacheDefinition extends baseModel {
	public $id;
	public $domain;
	public $registrant;
	public $registrantCount;
	public $registered;
	public $expires;
	public $created;
	
	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null,
				'key'=>'PRI',
				'extra'=>'auto_increment'
			)
		),
		'domain'=>array(
			'scheme'=>array(
				'type'=>'varchar(300)',
				'null'=>'NO',
				'default'=>''
			)
		),
		'registrant'=>array(
			'scheme'=>array(
				'type'=>'varchar(200)',
				'null'=>'NO',
				'default'=>''
			)
		),
		'registrantCount'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'registered'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'expires'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
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
		)
	);
}
