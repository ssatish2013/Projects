<?php
abstract class userRoleDefinition extends baseModel{
	public $id;
	public $partner;
	public $userId;
	public $roleId;
	public $enabled;
	public $created;
	protected $value;

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
		'partner'=>array(
			'scheme'=>array(
				'type'=>'varchar(128)',
				'null'=>'YES',
				'default'=>null,
			)
		),
		'userId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'roleId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null
			)
		),
		'enabled'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'NO',
				'default'=>1
			)
		),
		'created'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'NO',
				'default'=>'CURRENT_TIMESTAMP'
			)
		)
	);

	protected $dbIndexes = array(

	);
}
