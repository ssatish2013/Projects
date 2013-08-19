<?php
abstract class emailDefinition extends baseModel{
	public $id;
	public $partner;
	public $template;
	public $guid;
	public $sentAt;
	public $openedAt;
	public $clickedAt;
	public $bouncedAt;
	public $giftId;
	public $messageId;
	public $userId;
	public $transactionId;
	public $email;


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
				'null'=>'NO',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'template'=>array(
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
				'default'=>null,
				'key'=>'UNI'
			)
		),
		'sentAt'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null,
				//'key'=>'MUL'
			)
		),
		'openedAt'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'clickedAt'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'bouncedAt'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'giftId'=>array(
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
		'userId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'transactionId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null,
			)
		),
		'email'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'digestScheme'=>array(
				'type'=>'char(32)',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
				'digest'=>true
			)
		),
		
	);
}
