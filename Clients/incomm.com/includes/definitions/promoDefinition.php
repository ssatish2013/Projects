<?php
abstract class promoDefinition extends baseModel{
	public $id;
	public $partner;
	public $startDate;
	public $stopDate;
	public $maxBudget;
	public $minSpend;
	public $maxUsesPerUser;
	public $maxUsesPerIP;
	public $maxUsesPerCC;
	public $productLimited;
	public $discountPercent;
	public $discountAmount;		//dollar amount discount
	public $bonusAmount;		//increase activation amount instead of paid amount.
	public $status;
	public $terminalId;
	public $locationId;
	public $retailerName;
	public $title;
	public $description;

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
				'null'=>'YES',
				'default'=>null
			)
		),
		'startDate'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		),
		'stopDate'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null,
			)
		),
		'maxBudget'=>array(
			'scheme'=>array(
				'type'=>'decimal(14,2) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'minSpend'=>array(
			'scheme'=>array(
				'type'=>'decimal(6,2) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'maxUsesPerUser'=>array(
			'scheme'=>array(
				'type'=>'int(10) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'maxUsesPerIP'=>array(
			'scheme'=>array(
				'type'=>'int(10) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'maxUsesPerCC'=>array(
			'scheme'=>array(
				'type'=>'int(10) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'productLimited'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'NO',
				'default'=>1
			)
		),
		'discountPercent'=>array(
			'scheme'=>array(
				'type'=>'decimal(5,2) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'discountAmount'=>array(
			'scheme'=>array(
				'type'=>'decimal(5,2) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'bonusAmount'=>array(
			'scheme'=>array(
				'type'=>'decimal(5,2) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'status'=>array(
			'scheme'=>array(
				'type'=>'tinyint(1) unsigned',
				'null'=>'NO',
				'default'=>0
			)
		),
		'terminalId'=>array(
			'scheme'=>array(
				'type'=>'char(8)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'locationId'=>array(
			'scheme'=>array(
				'type'=>'varchar(25)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'retailerName'=>array(
			'scheme'=>array(
				'type'=>'varchar(25)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'title'=>array(
			'scheme'=>array(
				'type'=>'varchar(200)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'description'=>array(
			'scheme'=>array(
				'type'=>'longtext',
				'null'=>'YES',
				'default'=>null
			)
		),
	);
}