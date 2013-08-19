<?php
abstract class transactionDefinition extends domaintoolsHelper {

	public $id;
	public $paymentMethodId;
	public $currency;
	public $amount;
	public $status;
	public $shoppingCartId;
	public $authorizationId;
	public $authorizationTime;
	public $externalTransactionId;
	public $firstName;
	public $lastName;
	public $fromEmail;
	public $phoneNumber;
	public $address;
	public $address2;
	public $city;
	public $state;
	public $zip;
	public $country;
	public $ccLastFour;
	public $ccType;
	public $fraudLogId;
	public $arinCacheId;
	public $domaintoolsCacheId;
	public $refunded;
	public $chargedback;
	public $created;
	public $updated;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'key'=>'PRI',
				'null'=>'NO',
				'default'=>null,
				'extra'=>'auto_increment'
			)
		),
		'paymentMethodId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'currency'=>array(
			'scheme'=>array(
				'type'=>'char(3)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'amount'=>array(
			'scheme'=>array(
				'type'=>'decimal(6,2) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'status'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'shoppingCartId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'authorizationId'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'authorizationTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'externalTransactionId'=>array(
			'scheme'=>array(
				'type'=>'varchar(128)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'firstName'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'lastName'=>array(
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
				'default'=>null,
				'key'=>'MUL'
			),
			'properties'=>array(
				'encrypt'=>true,
				'digest'=>true
			)
		),
		'phoneNumber'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'digestScheme'=>array(
				'type'=>'char(32)',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			),
			'properties'=>array(
				'encrypt'=>true,
				'digest'=>true
			)
		),
		'address'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'address2'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'city'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'state'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'zip'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'country'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'ccLastFour'=>array(
			'scheme'=>array(
				'type'=>'char(4)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'ccType'=>array(
			'scheme'=>array(
				'type'=>'varchar(8)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'fraudLogId'=>array(
			'scheme'=>array(
				'type'=>'varchar(24)',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'arinCacheId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'domaintoolsCacheId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'refunded'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'chargedback'=>array(
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
	);
}
