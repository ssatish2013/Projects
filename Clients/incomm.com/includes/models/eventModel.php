<?php

class eventModel extends eventDefinition {

	private $eventType;
	
	public function _getEventType(){
		if(!$this->eventType){
			$this->eventType = new eventTypeModel($this->typeId);
		}
		
		return $this->eventType;
	}
}
