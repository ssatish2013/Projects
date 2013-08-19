<?php
abstract class geoipDefinition extends ipHelper {
	public $id;
	public $begin_ip;
	public $end_ip;
	public $begin_num;
	public $end_num;
	public $country;
	public $name;
	
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
		'country'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'name'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		)
	);
	
}
