<?php
abstract class traceAuditDefinition extends baseModel{
	public $id;
	public $inventoryId;
	public $created;
	public $updated;
	public $requestFields;
	protected $requestMicroTimestamp; // Should not be set directly
	protected $requestReadableTimestamp; // Should not be set directly
	public $responseFields;
	protected $responseMicroTimestamp; // Should not be set directly
	protected $responseReadableTimestamp; // Should not be set directly
	public $responseCode;
	public $isReversed;
	public $isReversalHardFail;
	

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
		'inventoryId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null
			)
		),
		'created'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'updated'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'requestFields'=>array(
			'scheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>''
			)
		),
		'requestMicroTimestamp'=>array(
			'scheme'=>array(
				'type'=>'char(20)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'requestReadableTimestamp'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'responseFields'=>array(
			'scheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>''
			)
		),
		'responseMicroTimestamp'=>array(
			'scheme'=>array(
				'type'=>'char(20)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'responseReadableTimestamp'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'responseCode'=>array(
			'scheme'=>array(
				'type'=>'char(4)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'isReversed'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'isReversalHardFail'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
	);
}