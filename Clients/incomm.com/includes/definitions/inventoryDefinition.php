<?php
abstract class inventoryDefinition extends baseModel{
	public $id;
	public $productId;
	public $giftId;
	public $terminalId;
	public $locationId;
	public $pan;
	public $pin;
	protected $auxData; // stored as json
	public $activationMargin;
	public $activationAttemptTime;
	public $activationTime;
	public $deactivationTime;
	public $exceptionTime;
	public $activationAmount;

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
		'productId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>null
			)
		),
		'giftId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null,
				'key'=>'UNI'
			)
		),
		'terminalId'=>array(
			'scheme'=>array(
				'type'=>'char(8)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'locationId'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'pan'=>array(
			'scheme'=>array(
				'type'=>'varchar(24)',
				'null'=>'YES',
				'default'=>null,
				'key'=>'UNI'
			)
		),
		'pin'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'NO',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true
			)
		),
		'auxData'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true
			)
		),
		'activationMargin'=>array(
			'scheme'=>array(
				'type'=>'decimal(8,4)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'activationAttemptTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'activationTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'deactivationTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'exceptionTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'activationAmount'=>array(
			'scheme'=>array(
				'type'=>'decimal(8,2)',
				'null'=>'YES',
				'default'=>null
			)
		),
	);

  protected $foreignTables = array(
    array(
      'table' => 'gifts',
      'foreignKey' => 'id',
      'localKey' => 'giftId',
      'multiple' => false
    ),
  );

}
