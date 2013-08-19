<?php
abstract class apiLogDefinition extends baseMongoModel{
	public $id;
	public $url;
	public $call;
	public $input;
	public $response;
	public $responseTime;
	public $partner;
	public $apiPartner;
	public $success;
	public $created;
	public $updated;

	protected $dbFields = array(
		'id'=>array(
			'scheme'=>array(
				'key'=>true,
			)
		),
		'url'=>array(
		),
		'call'=>array(
		),
		'input'=>array(
		),
		'response'=>array(
		),
		'responseTime'=>array(
		),
		'partner'=>array(
			'scheme'=>array(
				'key'=>true,
			)
		),
		'apiPartner'=>array(
			'scheme'=>array(
				'key'=>true,
			)
		),
		'success'=>array(
			'scheme'=>array(
				'default'=>0
			)
		),
		'created'=>array(
      'scheme'=>array(
        'type'=>'date',
      ),
			'properties'=>array(
				'createdTimestamp'=>true
			)
		),
		'updated'=>array(
    	'scheme'=>array(
        'type'=>'date',
      ),
			'properties'=>array(
				'updatedTimestamp'=>true
			)
		)
	);
}
