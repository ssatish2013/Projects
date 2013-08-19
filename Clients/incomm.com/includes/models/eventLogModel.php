<?php

class eventLogModel extends eventLogDefinition {

	public function __construct( $type=null, $name=null, $value = null, $partner = null ) {

		$eventType = new eventTypeModel(
			array( "name" => $type )
		);

		//load up the specific event model that's a
		//child of our event type
		$event = new eventModel (
			array( 
				"typeId" => $eventType->id,
				"name" => $name 
			)
		);

		if($partner === null) { 
			$partner = globals::partner();
		}
		// Only log an event if it has an name / id
		if ( $event->id ) {
			$this->eventId = $event->id;
			$this->partner = $partner;
			if ( $value ) {
				$this->value = $value;
			}
			$this->save();
		}

	}
	
}
