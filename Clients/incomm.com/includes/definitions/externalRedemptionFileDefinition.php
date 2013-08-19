<?php
abstract class externalRedemptionFileDefinition extends baseModel{
	public $id;
	public $path;
	public $filename;
	public $lastUpdated;

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
		'path'=>array(
			'digestScheme'=>array(
				'type'=>'char(32)',
				'null'=>'NO',
				'default'=>null,
				'key'=>'MUL'
			),
			'properties'=>array(
				'digest'=>true
			)
		),
    'filename'=>array(
      'scheme'=>array(
        'type'=>'varchar(255)',
        'null'=>'NO',
        'default'=>null
      )
    ),
		'lastUpdated'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		)
	);	

}
