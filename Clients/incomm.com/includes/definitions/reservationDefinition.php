<?php
abstract class reservationDefinition extends baseModel{
	public $id;
	public $productId;
	public $giftId;
	public $inventoryId;
	public $reservationTime;
	public $clearTime;
	public $isolationTime;
	


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
				'null'=>'NO',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'inventoryId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null,
				'key'=>'MUL'
			)
		),
		'reservationTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			),
			'properties'=>array(
				'createdTimestamp'=>true
			)
		),
		'clearTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'isolationTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
	);

	protected $foreignTables = array(
		array(
			'table' => 'inventorys',
			'foreignKey' => 'id',
			'localKey' => 'inventoryId',
			'multiple' => false
		)
	);
}
