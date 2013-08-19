<?php

abstract class messageDefinition extends baseModel{
	public $id;
	public $userId;
	protected $productId; // Should be loaded from the gift, not the message
	public $shoppingCartId;
	public $giftId;
	public $transactionId;
	public $emailId;
	public $message;
	public $recordingId;
	public $videoLink;
	public $fromName;
	public $fromEmail;
	public $guid;
	public $amount;
	public $currency;
	public $status;
	public $refunded;
	public $isContribution;
	public $created;
	public $updated;
	public $facebookUserId;
	public $facebookAccessToken;
	public $twitterToken;
	public $twitterSecret;
	public $promoCode;

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
		'userId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'key'=>'MUL',
				'null'=>'YES',
				'default'=>null
			)
		),
		'shoppingCartId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'key'=>'MUL',
				'default'=>null
			)
		),
		'productId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'giftId'=>array(
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
				'key'=>'MUL',
				'null'=>'YES',
				'default'=>null
			)
		),
		'emailId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'message'=>array(
			'scheme'=>array(
				'type'=>'text',
				'null'=>'YES',
				'default'=>null
			)
		),
		'recordingId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'videoLink'=>array(
			'scheme'=>array(
				'type'=>'varchar(100)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'fromName'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'fromEmail'=>array(
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
		'guid'=>array(
			'scheme'=>array(
				'type'=>'varchar(16)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'amount'=>array(
			'scheme'=>array(
				'type'=>'decimal(6,2)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'currency'=>array(
			'scheme'=>array(
				'type'=>'varchar(6)',
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
		'refunded'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'isContribution'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1)',
				'null'=>'YES',
				'default'=>0
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
		'facebookUserId'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'facebookAccessToken'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'twitterToken'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'twitterSecret'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'promoCode'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		)			
	);

	protected $foreignTables = array(
		array(
			'table' => 'users',
			'foreignKey' => 'id',
			'localKey' => 'userId',
			'multiple' => false
		),
		array(
			'table' => 'gifts',
			'foreignKey' => 'id',
			'localKey' => 'giftId',
			'multiple' => false
		),
		array(
			'table' => 'shoppingCarts',
			'foreignKey' => 'id',
			'localKey' => 'shoppingCartId',
			'multiple' => false
		),
		array(
			'table' => 'promoTransactions',
			'foreignKey' => 'messageId',
			'localKey' => 'id',
			'multiple' => false
		)
	);

}
