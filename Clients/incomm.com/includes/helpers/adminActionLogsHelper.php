<?php

class adminActionLogsHelper {

	public static function anyType() { 
		self::doSearch();
	}

	public static function authorization() { 
		self::doSearch(array('input.PAYMENTACTION' => 'Authorization'));
	}

	public static function authorizationCapture() { 
		self::doSearch(array('input.METHOD' => 'DoCapture'));
	}

	public static function authorizationID() { 
		$authorizationID = request::unsignedPost('searchTerm');

		//find the transaction ID if there is one
		$transactionID = self::getTransFromAuth($authorizationID);
		self::doSearch(array('$or' => array(
			array('input.TRANSACTIONID' => $transactionID),
			array('response.TRANSACTIONID' => $transactionID),
			array('input.TRANSACTIONID' => $authorizationID),
			array('response.TRANSACTIONID' => $authorizationID)
		)));
	}

	public static function transactionID() { 
		$transactionID = request::unsignedPost('searchTerm');
		$authorizationID = self::getAuthFromTrans($transactionID);

		self::doSearch(array('$or' => array(
			array('input.TRANSACTIONID' => $transactionID),
			array('response.TRANSACTIONID' => $transactionID),
			array('input.AUTHORIZATIONID' => $authorizationID),
			array('response.TRANSACTIONID' => $authorizationID)
		)));
	}

	public static function failedPayPal() { 
		self::doSearch(array(
			'response.ACK' => array('$ne' => 'Success', '$exists' => true)
		));
	}

	public static function email() { 
		$email= request::unsignedPost('searchTerm');
		self::doSearch(array('$or' => array(
			array('response.EMAIL' => $email),
			array('input.EMAL' => $email),
			array('input.S2EM' => $email)
		)));
	}

	public static function custom() { 
		$searchTerm = request::unsignedPost('searchTerm');
		$searchField = request::unsignedPost('searchField');

		$criteria = array();
		if($searchTerm != '' && $searchField != '') {
			$criteria[$searchField] = array('$in' => array($searchTerm), '$exists' => true); 
		}
		self::doSearch($criteria);
	}




	//standard search function, takes into account all the filters and what not
	public static function doSearch($criteria = array()) {

		$limit = request::unsignedPost('searchLimit');
		$sort = intval(request::unsignedPost('searchSort'));
		$partner = request::unsignedPost('partner');
		$area = request::unsignedPost('area');

		if($partner != "") { 
			$criteria["partner"] = $partner;
		}
		if($area != "") { 
			$criteria["area"] = $area;
		}
		if($limit == "") { $limit = null; }

		$return = dbMongo::find('actions', $criteria, array("timestamp" => $sort), $limit);
		echo json_encode($return);
	}


	public static function loadActionLog() { 
		$id = request::unsignedPost('actionLogId');
		$return = dbMongo::findOne('actions', array("id" => $id)); 
		echo json_encode($return);
	}


	/*** ACTUAL UTILITY FUNCTIONS ***/
	public static function getTransFromAuth($authorizationID) { 
		$entries = dbMongo::find('apiLogs', array('input.AUTHORIZATIONID' => $authorizationID));

		//finding authorization ID used
		$transactionID= "";
		foreach($entries as $entry) { 
			$id = $entry['response']['TRANSACTIONID'];
			if(isset($id) && $id != $authorizationID) { 
				return $id;
			}
		}
		return "";
	}

	public static function getAuthFromTrans($transactionID) { 
		$entries = dbMongo::find('apiLogs', array('response.TRANSACTIONID' => $transactionID));

		//finding authorization ID used
		$authorizationID = "";
		foreach($entries as $entry) { 
			if(isset($entry['input']) && isset($entry['input']['AUTHORIZATIONID'])) { 
				$id = $entry['input']['AUTHORIZATIONID'];
				if($id != $transactionID) { 
					return $id;
				}
			}
		}
		return "";
	}
}
