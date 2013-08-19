<?php
abstract class shoppingCartDefinition extends baseModel{
	public $id;
	public $partner;
	public $currency;
	public $isCurrent;
	public $sessionGuid;
	public $paypalExpressToken;
	public $paypalExpressPayerId;
	public $created;
	public $updated;
	public $approved;
	public $referer;
	public $rejected;
	public $screenedBy;
	public $screenedNotes;
	
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
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'currency'=>array(
			'scheme'=>array(
				'type'=>'varchar(4)',
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
		'isCurrent'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'sessionGuid'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'key'=>'MUL',
				'default'=>null
			)
		),
		'paypalExpressToken'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'paypalExpressPayerId'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'screenedBy'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'screenedNotes'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'approved'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'rejected'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
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
		),
		'referer'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		)
	);

	protected $foreignTables = array(
		array(
			'table' => 'transactions',
			'foreignKey' => 'shoppingCartId',
			'localKey' => 'id',
			'multiple' => false
		),
		array(
			'table' => 'messages',
			'foreignKey' => 'shoppingCartId',
			'localKey' => 'id',
			'multiple' =>true 
		)
	);
}
