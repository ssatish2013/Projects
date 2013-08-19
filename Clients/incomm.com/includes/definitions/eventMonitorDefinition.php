<?php

abstract class eventMonitorDefinition extends baseModel {

	public $id;
	public $eventTypeId;						// This is the monitor for event types
	public $eventId;								// This is the monitor for an exact event
	public $enabled;								// Should this even be run?
	public $minimumPercent;					// The lowest percent it can be without triggering a notification
	public $maximumPercent;					// The highest percent it can be without triggering a notification
	public $minimumHardLimit;				// The hard limit on when we want to be notified
	public $maximumHardLimit;
	public $compareStartTime;				// (String) Start time of the period to compate
	public $compareEndTime;					// (String) End time of the period to compate
	public $currentStartTime;				// (String) Start time of the current period
	public $currentEndTime;					// (String) End time of the current period
	public $lastTriggered;					// When was this last triggered?
	
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
		'eventTypeId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'eventId'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'enabled'=>array(
			'scheme'=>array(
				'type'=>'int(1) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'minimumPercent'=>array(
			'scheme'=>array(
				'type'=>'float',
				'null'=>'YES',
				'default'=>null
			)
		),
		'maximumPercent'=>array(
			'scheme'=>array(
				'type'=>'float',
				'null'=>'YES',
				'default'=>null
			)
		),
		'minimumHardLimit'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'maximumHardLimit'=>array(
			'scheme'=>array(
				'type'=>'int(11) unsigned',
				'null'=>'YES',
				'default'=>null
			)
		),
		'compareStartTime'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'compareEndTime'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'currentStartTime'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'currentEndTime'=>array(
			'scheme'=>array(
				'type'=>'varchar(64)',
				'null'=>'YES',
				'default'=>null
			)
		),
		'lastTriggered'=>array(
			'scheme'=>array(
				'type'=>'timestamp',
				'null'=>'YES',
				'default'=>null
			)
		)
	);
}
