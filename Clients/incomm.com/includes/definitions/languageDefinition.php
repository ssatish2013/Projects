<?php

abstract class languageDefinition extends baseModel {

    public $id;
    public $partner;
    public $name;
    public $value;
    public $language;
    public $status;
    public $created;
    public $isEditable;
    public $updated;

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
        'name' => array(
            'scheme' => array(
                'type' => 'varchar(128)',
                'null' => 'YES',
                'default' => null
            )
        ),
        'value' => array(
            'scheme' => array(
                'type' => 'longtext',
                'null' => 'YES',
                'default' => null
            )
        ),
        'language' => array(
            'scheme' => array(
                'type' => 'char(2)',
                'null' => 'YES',
                'default' => null
            )
        ),
        'status' => array(
            'scheme' => array(
                'type' => 'int(11) unsigned',
                'null' => 'YES',
                'default' => null
            )
        ),
        'isEditable' => array(
            'scheme' => array(
                'type' => 'int(11)',
                'null' => 'YES',
                'default' => null
            )
        ),
        'created' => array(
            'scheme' => array(
                'type' => 'timestamp',
                'null' => 'YES',
                'default' => null
            )
        ),
        'updated' => array(
            'scheme' => array(
                'type' => 'timestamp',
                'null' => 'YES',
                'default' => null
            )
        )
	);	
}
