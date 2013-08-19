<?php
abstract class designDefinition extends baseModel{
	public $id;
	public $alt;
	public $partner;
	public $guid;
	public $largeSrc;
	public $mediumSrc;
	public $smallSrc;
	public $status;
	public $created;
	public $updated;
	public $isDeleted;
	public $isCustom;
	public $isScene;
	public $isPhysical;
	public $isPhysicalOnly;
	public $sort;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'key'=>'PRI',
				'null'=>'NO',
				'default'=>null,
				'extra'=>'auto_increment'
			)
		),
		'partner'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'guid'=>array(
			'scheme'=>array(
				'type'=>'varchar(16)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'largeSrc'=>array(
			'scheme'=>array(
				'type'=>'text',
				'null'=>'YES',
				'default'=>null
			)
		),
		'mediumSrc'=>array(
			'scheme'=>array(
				'type'=>'text',
				'null'=>'YES',
				'default'=>null
			)
		),
		'smallSrc'=>array(
			'scheme'=>array(
				'type'=>'text',
				'null'=>'YES',
				'default'=>null
			)
		),
		'isDeleted'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'isCustom'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'isScene'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'isPhysical'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'isPhysicalOnly'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'status'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'sort'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null
			)
		),
		'alt'=>array(
			'scheme'=>array(
				'type'=>'varchar(256)',
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
		),
		'updated'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'updatedTimestamp'=>true
			)
		)
	);
}
