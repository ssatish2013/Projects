<?php

abstract class shippingDetailDefinition extends baseModel {
	public $id;
	public $giftId;
	public $shoppingCartId;
	public $shippingOptionId;
	public $companyName;
	public $address;
	public $address2;
	public $city;
	public $state;
	public $zip;
	public $country;
	public $cardNumber;
	public $dateShipped;
	public $trackingNumber;
	public $orderFileKey;
	public $orderException;
	public $created;
	public $updated;

	protected $dbFields = array(
		'id' => array(
			'scheme' => array(
				'type'    => 'int(11) unsigned',
				'key'     => 'PRI',
				'null'    => 'NO',
				'default' => null,
				'extra'   => 'auto_increment'
			)
		),
		'giftId' => array(
			'scheme' => array(
				'type'    => 'int(11) unsigned',
				'null'    => 'YES',
				'default' => null,
				'key'     => 'MUL'
			)
		),
		'shoppingCartId' => array(
			'scheme' => array(
				'type'    => 'int(11) unsigned',
				'null'    => 'YES',
				'default' => null,
				'key'     => 'MUL'
			)
		),
		'shippingOptionId' => array(
			'scheme' => array(
				'type'    => 'int(11) unsigned',
				'null'    => 'YES',
				'default' => null
			)
		),
		'companyName' => array(
			'encryptScheme' => array(
				'type'    => 'longtext',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'encrypt' => true,
			)
		),
		'address' => array(
			'encryptScheme' => array(
				'type'    => 'longtext',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'encrypt' => true,
			)
		),
		'address2' => array(
			'encryptScheme' => array(
				'type'    => 'longtext',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'encrypt' => true,
			)
		),
		'city' => array(
			'encryptScheme' => array(
				'type'    => 'longtext',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'encrypt' => true,
			)
		),
		'state' => array(
			'encryptScheme' => array(
				'type'    => 'longtext',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'encrypt' => true,
			)
		),
		'zip' => array(
			'encryptScheme' => array(
				'type'    => 'longtext',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'encrypt' => true,
			)
		),
		'country' => array(
			'encryptScheme' => array(
				'type'    => 'longtext',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'encrypt' => true,
			)
		),
		'cardNumber' => array(
			'scheme' => array(
				'type'    => 'varchar(32)',
				'null'    => 'YES',
				'default' => null
			)
		),
		'dateShipped' => array(
			'scheme' => array(
				'type'    => 'timestamp',
				'null'    => 'YES',
				'default' => null
			)
		),
		'trackingNumber' => array(
			'scheme' => array(
				'type'    => 'varchar(30)',
				'null'    => 'YES',
				'default' => null
			)
		),
		'orderFileKey' => array(
			'scheme' => array(
				'type'    => 'varchar(32)',
				'null'    => 'YES',
				'default' => null
			)
		),
		'orderException' => array(
			'scheme' => array(
				'type'    => 'varchar(16)',
				'null'    => 'YES',
				'default' => null
			)
		),
		'created' => array(
			'scheme' => array(
				'type'    => 'timestamp',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'createdTimestamp' => true
			)
		),
		'updated' => array(
			'scheme' => array(
				'type'    => 'timestamp',
				'null'    => 'YES',
				'default' => null
			),
			'properties' => array(
				'updatedTimestamp' => true
			)
		)
	);

}  //end shippingDetailDefinition
