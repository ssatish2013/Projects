<?php
abstract class arinCacheDefinition extends ipHelper {
	public $id;
	public $begin_ip;
	public $end_ip;
	public $begin_num;
	public $end_num;
	public $country;
	public $orgname;
	public $orghandle;
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
		'begin_ip'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'end_ip'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'begin_num'=>array(
			'scheme'=>array(
				'type'=>'bigint(20)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'end_num'=>array(
			'scheme'=>array(
				'type'=>'bigint(20)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'orgname'=>array(
			'scheme'=>array(
				'type'=>'varchar(200)',
				'null'=>'NO',
				'default'=>''
			)
		),
		'orghandle'=>array(
			'scheme'=>array(
				'type'=>'varchar(20)',
				'null'=>'NO',
				'default'=>''
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
