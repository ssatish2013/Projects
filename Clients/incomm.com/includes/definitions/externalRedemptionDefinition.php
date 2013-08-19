<?php
abstract class externalRedemptionDefinition extends baseModel{
	public $id;
	public $inventoryId;
	public $redemptionTime;

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
		'inventoryId'=>array(
			'scheme'=>array(
				'type'=>'int(10) unsigned',
				'null'=>'NO',
				'default'=>null,
				'key'=>'UNI'
			)
		),
		'redemptionTime'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		)
	);	

  protected $foreignTables = array(
    array(
      'table' => 'inventorys',
      'foreignKey' => 'id',
      'localKey' => 'inventoryId',
      'multiple' => false
    ),
  );


}
