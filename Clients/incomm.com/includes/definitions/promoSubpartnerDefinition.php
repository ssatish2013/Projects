<?php
abstract class promoSubpartnerDefinition extends baseModel{
	public $id;
	public $promoId;
	public $partner;
	public $guid;

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
				'null'=>'NO',
				'default'=>null
			)
		),
		'partner'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'NO',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'guid'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'NO',
				'default'=>null
			)
		)
	);
}