<?php
abstract class rolePermissionDefinition extends baseModel{
	public $id;
	public $roleId;
	public $permissionId;
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
		'roleId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'permissionId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null
			)
		),
		'value'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1)',
				'null'=>'NO',
				'default'=>0
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
