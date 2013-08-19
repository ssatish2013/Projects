<?php
abstract class fraudLogDefinition extends baseMongoModel{
	public $id;

	//how we look up the transaction
	public $shoppingCartId;			//purchase log
	public $giftId;							//claim log

	//transactional data
	public $userId;
	public $transactionId;
	public $messages;
	public $gifts;

	//fraud reference data
	public $logType;
	public $data;
	public $paymentId;
	public $paymentHash;
	public $isHot;
	public $isWarm;
	public $isTrusted;
	public $isScreened;
	public $isRejected;
	public $isSent;
	public $rulesUsed;
	public $ipAddress;
	public $currency;

	//other data
	public $created;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'key'=>true,
			)
		),
		'userId'=>array(
			'scheme'=>array(
				'type'=>'int'
			)
		),	
		'transactionId'=>array(
			'scheme'=>array(
				'type'=>'int'
			)
		),	
		'shoppingCartId'=>array(
			'scheme'=>array(
				'type'=>'int'
			)
		),	
		'currency'=>array(
		),
		'giftId'=>array(
		),
		'messages'=>array(
		),	
		'gifts'=>array(
		),	
		'logType'=>array(
		),
		'data'=>array(
		),

		//PII/Linking Info
		'userId'=>array(
		),
		'paymentId'=>array(
		),
		'paymentHash'=>array(
		),
		'ipAddress'=>array(
		),

		'isHot'=>array(
			'scheme'=>array(
				'default'=>false,
				'type'=>'bool'
			)
		),	
		'isWarm'=>array(
			'scheme'=>array(
				'default'=>false,
				'type'=>'bool'
			)
		),	
		'isTrusted'=>array(
			'scheme'=>array(
				'default'=>false,
				'type'=>'bool'
			)
		),	
		'isScreened'=>array(
			'scheme'=>array(
				'default'=>false,
				'type'=>'bool'
			)
		),	
		'isRejected'=>array(
			'scheme'=>array(
				'default'=>false,
				'type'=>'bool'
			)
		),	
		'isSent'=>array(
			'scheme'=>array(
				'default'=>false,
				'type'=>'bool'
			)
		),	
		'rulesUsed'=>array(
		),
		'created'=>array(
			'scheme'=>array(
				'type'=>'date',
			),
			'properties'=>array(
				'createdTimestamp'=>true
			)
		)
	);
}
