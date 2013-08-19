<?php
abstract class cstLogDefinition extends baseModel{
	public $category;
	public $event;
	public $giftId;
	public $shoppingCartId;
	public $transactionId;
	public $messageId;
	public $fromEmail;
	public $timestamp;
	public $agent;
	public $description;

	protected $dbFields = array(
		'category'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'NO',
				'default'=>'Undefined',
			)
		),
		'event'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'NO',
				'default'=>'Undefined',
			)
		),
		'giftId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null,
			)
		),
		'shoppingCartId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null,
			)
		),
		'transcationId'=>array(
                        'scheme'=>array(
                                'type'=>'int(11) unsigned',
                                'null'=>'YES',
                                'default'=>null,
                        )
                ),
		'messageId'=>array(
                        'scheme'=>array(
                                'type'=>'int(11) unsigned',
                                'null'=>'YES',
                                'default'=>null,
                        )
                ),
		'fromEmail'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null,
			)
		),
		'timestamp'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'NO',
			),
			'properties'=>array(
				'createdTimestamp'=>true
			)
		),
		'agent'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
				'returnValueOnEncryptionError'=>true,
			),
		),
		'description'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
	);
}
