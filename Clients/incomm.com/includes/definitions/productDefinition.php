<?php
abstract class productDefinition extends baseModel{
	public $id;
	public $guid;
	public $isActive;
	public $currency; //deprecated
	public $fixedAmount;
	public $isOpen;
	public $minAmount;
	public $maxAmount;
	public $description; //deprecated
	public $upc;
	public $dcmsId;
	public $inventoryPlugin;
	public $defaultMargin;
	public $displayName; //deprecated
	public $displayTerms; 
	public $pinDisplay; //deprecated
	public $thirdparty; //deprecated

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
		'guid'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
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
		'fixedAmount'=>array(

			'scheme'=>array(

					'type'=>'decimal(8,2)',

					'null'=>'YES',

					'default'=>null

			)

		),
		'isOpen'=>array(

				'scheme'=>array(

						'type'=>'int(1) unsigned',

						'null'=>'NO',

						'default'=>0

				)

		),
		'minAmount'=>array(
			'scheme'=>array(
				'type'=>'decimal(6,2)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'maxAmount'=>array(
			'scheme'=>array(
				'type'=>'decimal(8,2)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'description'=>array(
			'scheme'=>array(
				'type'=>'varchar(255)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'upc'=>array(
			'scheme'=>array(
				'type'=>'varchar(32)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'dcmsId'=>array(
			'scheme'=>array(
				'type'=>'varchar(8)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'inventoryPlugin'=>array(
			'scheme'=>array(
				'type'=>'varchar(45)',
				'null'=>'NO',
				'default'=>null
			)
		),
		'defaultMargin'=>array(
			'scheme'=>array(
				'type'=>'decimal(6,4)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'displayName'=>array(

			'scheme'=>array(

				'type'=>'varchar(255)',

				'null'=>'YES',

				'default'=>null

			)

		),
		'displayTerms'=>array(

			'scheme'=>array(

				'type'=>'varchar(255)',

				'null'=>'YES',

				'default'=>null

			)

		),
		'pinDisplay'=>array(

			'scheme'=>array(

				'type'=>'varchar(255)',

				'null'=>'YES',

				'default'=>null

			)

		),

		'thirdparty' => array(

			'scheme' => array(

				'type' => 'int(11)',

				'null' => 'NO',
				'default' => 0

			)

		),
	);
}
