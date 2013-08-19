<?php

abstract class productFeeDefinition extends baseModel{
	public $id;
	public $productId;
	public $productGroupId;
	public $feeType;
	public $title;
	public $amount;
	public $status;

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
							'null'=>'NO'
					)
			),
			'productGroupId'=>array(
					'scheme'=>array(
							'type'=>'int(11) unsigned',
							'null'=>'NO'
					)
			),
			'feeType'=>array(
					'scheme'=>array(
							'type'=>'varchar(45)',
							'null'=>'NO'
					)
			),
			'title'=>array(
					'scheme'=>array(
							'type'=>'varchar(255)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'amount'=>array(
					'scheme'=>array(
							'type'=>'decimal(8,2)',
							'null'=>'YES',
							'default'=>null
					)
			),
			'status'=>array(
					'scheme'=>array(
							'type'=>'int(11) unsigned',
							'null'=>'YES'
					)
			),
	);
}