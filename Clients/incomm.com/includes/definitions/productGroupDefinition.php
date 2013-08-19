<?php
abstract class productGroupDefinition extends baseModel{
	public $id;
	public $partner;
	public $title;
	public $description;
	public $currency;
	public $isCustomizable;
	public $pinDisplay;
	public $status;

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
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'title'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'description'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'currency'=>array(
			'scheme'=>array(
				'type'=>'char(3)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'isCustomizable'=>array(
				'scheme'=>array(
						'type'=>'int(1) unsigned',
						'null'=>'NO',
						'default'=>0
				)
		),
		'pinDisplay'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'status'=>array(
				'scheme'=>array(
						'type'=>'int(11) unsigned',
						'null'=>'YES',
						'default'=>null
				)
		),
	);
}
