<?php
abstract class giftDefinition extends domaintoolsHelper {
	public $id;
	public $envName;
	public $designId;
	public $productId;
	public $partner;
	public $redirectLoader;
	public $recipientName;
	public $recipientEmail;
	public $recipientPhoneNumber;
	public $recipientFacebookId;
	public $recipientTwitter;
	public $guid;
	public $currency;
	protected $deliveryDate;
	protected $timeZoneKey;
	protected $defaultTimeZoneKey;
	
	public $paid; // This is just a hint, for a real anwser please check $amount
	public $delivered;
	public $claimed;
	public $thanked;
	public $inScreeningQueue;
	public $addedToDeliveryQueue;
	public $emailDelivery;
	public $facebookDelivery;
	public $physicalDelivery;
	public $twitterDelivery;
	public $recipientAddress1;
	public $recipientAddress2;
	public $recipientCity;
	public $recipientState;
	public $recipientZip;
	public $language;

	public $created;
	public $updated;
	
	public $title;
	public $eventTitle;
	public $eventMessage;
	public $giftingMode;
	public $productGroupId;
	public $allowGuestInvite;
	public $domaintoolsCacheId;

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
		'envName'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'designId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
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
		'partner'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'redirectLoader'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'recipientName'=>array(
			'encryptScheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'encrypt'=>true,
			)
		),
		'recipientEmail'=>array(
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
		'recipientPhoneNumber'=>array(
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
    'recipientFacebookId'=>array(
      'scheme'=>array(
        'type'=>'bigint(20)',
        'null'=>'YES',
        'default'=>null
      )
    ),
		'recipientTwitter'=>array(
      'scheme'=>array(
        'type'=>'char(32)',
        'null'=>'YES',
        'default'=>null
      )
    ),
		'guid'=>array(
			'scheme'=>array(
				'type'=>'varchar(16)',
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
		'deliveryDate'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'timeZoneKey'=>array(
                        'scheme'=>array(
                                'type'=>'varchar(128)',
                                'null'=>'YES',
                                'default'=>null
                        )
                ),
		'defaultTimeZoneKey'=>array(
                        'scheme'=>array(
                                'type'=>'varchar(128)',
                                'null'=>'YES',
                                'default'=>null
                        )
                ),
		'paid'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'delivered'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'claimed'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'thanked'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'inScreeningQueue'=>array(
			'scheme'=>array(
				'type'=>'int(1)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'addedToDeliveryQueue'=>array(
			'scheme'=>array(
				'type'=>'int(1)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'emailDelivery'=>array(
			'scheme'=>array(
				'type'=>'int(1)',
				'null'=>'YES',
				'default'=>0
			)
		),
		'facebookDelivery'=>array(
			'scheme'=>array(
				'type'=>'int(1)',
				'null'=>'YES',
				'default'=>0
			)
		),
		'physicalDelivery'=>array(
			'scheme'=>array(
				'type'=>'int(1)',
				'null'=>'YES',
				'default'=>0
			)
		),
		'twitterDelivery'=>array(
			'scheme'=>array(
				'type'=>'int(1)',
				'null'=>'YES',
				'default'=>0
			)
		),
		'recipientAddress1'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'recipientAddress2'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'recipientCity'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'recipientState'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'recipientZip'=>array(
			'scheme'=>array(
				'type'=>'varchar(10)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'language'=>array(
                        'scheme'=>array(
                                'type'=>'char(2)',
                                'null'=>'YES',
                                'default'=>null
                        )
                ),
		'created'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
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
		'title'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'eventTitle'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'eventMessage'=>array(
			'scheme'=>array(
				'type'=>'text',
				'null'=>'YES',
				'default'=>null
			)
		),
		'giftingMode'=>array(
			'scheme'=>array(
				'type'=>'int(11)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'productGroupId'=>array(
			'scheme'=>array(
				'type'=>'int(11)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'allowGuestInvite'=>array(
			'scheme'=>array(
				'type'=>'int(11)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'domaintoolsCacheId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		)
	);
	
	protected $foreignTables = array(
		array(
			'table' => 'designs',
			'foreignKey' => 'id',
			'localKey' => 'designId',
			'multiple' => false
		),
		array(
			'table' => 'products',
			'foreignKey' => 'id',
			'localKey' => 'productId',
			'multiple' => false
		),
		array(
			'table' => 'messages',
			'foreignKey' => 'giftId',
			'localKey' => 'id',
			'multiple' =>true
		),
		array(
			'table' => 'reservations',
			'foreignKey' => 'giftId',
			'localKey' => 'id',
			'multiple' => false
		)
	);
}
