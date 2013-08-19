<?php
abstract class promoTriggerDefinition extends baseModel{
	public $id;
	public $promoId;
	public $plugin;
	public $pluginData;
	public $partner;
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
		'promoId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'plugin'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'pluginData'=>array(
			'scheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			)
		),
		'partner'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'status'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		)
	);
}