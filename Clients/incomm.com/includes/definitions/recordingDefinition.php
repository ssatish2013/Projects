<?php
abstract class recordingDefinition extends baseModel{
	public $id;
	public $giftId;
	public $recordingUrl;
	public $clientKey;
	public $expires;

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
		'recordingUrl'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'clientKey'=>array(
			'scheme'=>array(
				'type'=>'char(32)',
				'null'=>'YES',
				'default'=>null,
				'key' => 'UNI'
			)
		),
		'expires'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		)
	);
}
