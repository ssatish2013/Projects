<?php
abstract class actionDefinition extends baseMongoModel{
	public $id;

	//generic logging stuff
	public $area;
	public $oldValue;
	public $newValue;
	public $changed;
	public $user;
	public $partner;

	//in case we need to sear
	public $lookup;
	public $timestamp;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'key'=>true,
			)
		),
		'area'=>array(
		),
		'oldValue'=>array(
		),
		'newValue'=>array(
		),
		'changed'=>array(
		),
		'user'=>array(
		),
		'partner'=>array(
		),
		'lookup'=>array(
		),
		'timestamp'=>array(
      'scheme'=>array(
        'type'=>'date',
      ),
      'properties'=>array(
        'createdTimestamp'=>true
      )
		)
	);
}
