<?php
class actionModel extends actionDefinition {

  public static function logChanges($area, $new, $old, $lookup = null) {

		//only want to log stuff going on in the admin controller
		if(globals::controller() != 'admin') { return; }

		//get any models we want to explicity not log and exit if it's one of them
    $excludes = explode('|', settingModel::getSetting('actionLogging', 'excludeModels'));
    if(in_array($area, $excludes)) { return; }

		//grab a diff of what's new and old
		//and remove any updated timestamps, we only care about values
    $diff = utilityHelper::arrayRecursiveDiff($new, $old);
    unset($diff['updated']);

		//if there's nothing different, nothing to log
    if(empty($diff)) { return; }

		//get user info
    $user = loginHelper::forceLogin();

		//create the action/mongo entry
    $action = new actionModel();
    $action->area = $area;
    $action->newValue = $new;
    $action->oldValue = $old;
    $action->changed = $diff;

		//log structured user array
    $action->user = array(
      'id' => $user->id,
      'name' => $user->firstName . ' ' . $user->lastName,
      'email' => $user->email
    );
    $action->partner = globals::partner();

		//if we specified any lookup information, add it
    if($lookup !== null) {
      $action->lookup = $lookup;
    }
		try{
			$action->save();
		} catch( Exception $e){}
  }

	public static function getActions($area, $lookup) {
		
		//setup our query
		$criteria = array('area' => $area);
		$orLookups = array();
		foreach($lookup as $field => $value) { 
			$orLookups[] = array('lookup.'.$field => $value);
		}
		$criteria['$or'] = $orLookups;

		//grab data from mongo
		$result = dbMongo::find('actions', $criteria, array("timestamp" => 1));

		//cycle through and populate the actions that were taken
		$actions = array();
		foreach($result as $row) { 

			//make a new row for each thing that was changed
			foreach($row['changed'] as $key => $value) { 
				$actions[] = array(
					'user' => $row['user']['name'].' ('.$row['user']['email'].')',
					'area' => $row['area'],
					'changed' => $key . ': ' . $value,
					'timestamp' => $row['timestamp']
				);
			}
		}
		return $actions;
	}
}
