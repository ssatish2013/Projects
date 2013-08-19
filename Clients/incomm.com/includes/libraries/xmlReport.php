<?php 
class xmlReport {

	const REPORT_DIR = '/var/reports/';

  public $data = array();
  public $doc = null;
  public $root = null;
  public $startDate = null;
  public $endDate = null;
	public $defaultTimeZone;
	public $timeZone;
	public $hoursInOneDay = 24;

	private $xmlDir;
	private $partnerDir;

	public function __destruct() { 
		unset($this->doc);
	}

  /* 
   * Construct, just prepares the xml doc
   */
  public function __construct($args = array( 'startDate' => 'Now')) {

		$this->xmlDir = self::REPORT_DIR . Env::getEnvName().'/xml/';
		$this->partnerDir = self::REPORT_DIR . Env::getEnvName().'/partners/';

		$this->defaultTimeZone = date_default_timezone_get(); 
		$this->timeZone = settingModel::getSetting('reportTimeZone','reportTimeZone');; 

    //create the xml doc
    $this->doc = new DomDocument('1.0');
		/*
		$this->doc->preserveWhiteSpace = false;
		$this->doc->formatOutput   = true;
		*/

    //document inside the xml file
		$comment = new DomComment("\n" . 
		"Environment: " . Env::getEnvName() . "\n" . 
		"Created: " . date('Y-m-d H:i:s', strtotime("now")) . " " . $this->defaultTimeZone . "\n" .
		"Report Timezone: " . $this->timeZone . "\n"
		);
		$this->doc->appendChild($comment);
    $this->root = $this->doc->createElement('root');
    $this->doc->appendChild($this->root);


		//string to time our start date in the correct timezone
		date_default_timezone_set($this->timeZone);
		$this->startDate = strtotime($args['startDate'] . ' 00:00:00');

		if(isset($args['endDate'])) { 
			$this->endDate = strtotime($args['endDate'] . ' 23:59:59');
		}
		else {
			$this->endDate = strtotime($args['startDate'] . ' 23:59:59');
		}

		$hours = ($this->endDate - $this->startDate + 1) / 3600;
		if (23 <= $hours && $hours <= 25) {
			$this->hoursInOneDay = $hours;
		}
		date_default_timezone_set($this->defaultTimeZone);
  }

	/*
	 * Transfroms the report using a specific report name
	 * also automagically grabs the XML file to use!
	 */
	public function doTransform($reportName, $type, $outFile, $params = array()) { 

		//create our processor
    $xslt = new XSLTProcessor();


		//set any params we were passed
		foreach($params as $name => $value) { 
			$xslt->setParameter('', $name, $value);
		}

		//register those PHP functions!
    $xslt->registerPHPFunctions();    

		//process the file
		print "Loading " . xmlReport::getXslFilename($reportName, $type) . "\n";
		$xsl = new DOMDocument();
    $xsl->load( xmlReport::getXslFilename($reportName, $type), LIBXML_NOCDATA);
    $xslt->importStylesheet( $xsl );

		//output the file
		$output = $xslt->transformToXML($this->doc);
		if(!file_exists($this->partnerDir)) { 
			mkdir($this->partnerDir, 0777, true);
		}
		print "Creating $outFile\n";
    file_put_contents($outFile, $output);

		return $output;
	}


	/*
	 * Add's a transaction type to the report.  Right now this
	 * assumes that everything is originating from the ledger
	 */
	public function addTransactionType($type) { 
    //grab our doc info
    $doc = $this->doc;
    $root = $this->root;

    //add the transaction type
    $trans = $doc->createElement($type.'s');
		$table = 'ledgers';
		$timeField = 'timestamp';
		$condition = 'WHERE `type`="'.mysql_escape_string($type).'" ' . 
		'AND `'.$timeField.'` >= "'.date('Y-m-d H:i:s', $this->startDate).'" ';


		//any specific/weird types
		if(strtolower($type) == 'redemption') { 
			$timeField = 'redemptionTime';
			$table = 'externalRedemptions';
			$condition = 'WHERE `'.$timeField.'` >= "'.date('Y-m-d H:i:s', $this->startDate).'" ';
		}


		//if there's no endDate, let's make it 1 day
		if($this->endDate == $this->startDate) { 
			$condition .= ' AND `'.$timeField.'` <= "'.date('Y-m-d H:i:s', ($this->startDate*60*60*$this->hoursInOneDay)).'" ';
		}

		//otherwise use the given endDate
		else {
			$condition .= ' AND `'.$timeField.'` <= "'.date('Y-m-d H:i:s', $this->endDate).'" ';
		}
    $this->_addObject($table, $trans, $condition);
    $root->appendChild($trans);

	}

  /*
	 * adding the object, table, node with a given condition and whether or not it's an array
	 */
  private function _addObject($table, $node, $cond, $isArray = false) {
    $doc = $this->doc;

		//get the name of our model
		$objName = preg_replace('/s$/','', $table);

		//TODO add in date timezone stuff
    $query = "SELECT `id` FROM `".mysql_escape_string($table)."` ".$cond;

    $result = db::query($query, false);

    //create an array to store object
    if($isArray) {
      $objects = $doc->createElement($table);
      $node->appendChild($objects);
    }

    //grab all items from object
    while($row = mysql_fetch_array($result)) {

      //create a new object and load it
			$className = $objName.'Model';
      $obj = new $className($row[0]);

      //create the object
      $object = $doc->createElement($objName);
      if($isArray) {
        $objects->appendChild($object);
      }
      //otherwise we want to add on the object
      else {
        $node->appendChild($object);
      }

      //go through fields on the object
      foreach($obj->dbFields as $field => $info) {

				if(!property_exists($obj, $field)) { continue; }

        //create the field
        $dbField = $doc->createElement($field);

        //go through attributes
				if(isset($info['scheme'])) { 
					foreach($info['scheme'] as $attr => $value) {
						$dbField->setAttribute($attr, $value);
					}
				}

        //if it's an array value, we need to parse it
        if(is_array($obj->$field)) {
          $this->_parseArray($dbField, $obj->$field);
        }
        //otherwise just add the value
        else {

					//if it's null, add a blank field
					if(is_object($obj->$field) ||
					  ($obj->$field === null)) {
          	$valueField = $doc->createTextNode('');
					}

					//if it's not do something with it
					else {

						//if it's a date, format the date in the right timezone
						if(isset($info['scheme']) && $info['scheme']['type'] == 'timestamp') { 

								//create in original timezone
								$timestamp = strtotime($obj->$field);
								
								//set in new timezone
								date_default_timezone_set($this->timeZone);
          			$valueField = $doc->createTextNode(date('Y-m-d H:i:s', $timestamp));

								//go back to original
								date_default_timezone_set($this->defaultTimeZone);
						}

						//otherwise just get the field
						else {
          		$valueField = $doc->createTextNode(htmlentities($obj->$field));
						}
					}
          $dbField->appendChild($valueField);
        }

        //attach the entire field on the object
        $object->appendChild($dbField);
      }

      //add the objects for any foreign tables
      if(isset($obj->foreignTables)) {
        foreach($obj->foreignTables as $foreignInfo) {
					//lets try and avoid some unwanted recursion
					//if there's a parent element that the current element is trying to load, 
					//we don't want that (i.e. messages -> shopping cart -> messages -> shopping cart, etc
					$addForeign = true;

					//cycle through parents and see if there's already the data there
					$parent = $object->parentNode;
					while($parent = $parent->parentNode) {
						if($parent->tagName == preg_replace('/s$/', '', $foreignInfo['table'])) { $addForeign = false; }
					}
					
					//only if we actually want to add it
					if($addForeign) { 
						$this->_addObject($foreignInfo['table'], $object, 'WHERE ' . $foreignInfo['foreignKey'] . ' = "'  . $obj->$foreignInfo['localKey'] . '"', $foreignInfo['multiple']);
					}
        }
      }
    }
  }


	/*
	 *	Helper Functions
	 */

	/* Grabs the filename of the xml data file associated with the repot */
	public function getXmlFilename() { 
		$filename = false;
		date_default_timezone_set($this->timeZone);
		if(($this->endDate - $this->startDate) <= (60*60*$this->hoursInOneDay)) { 
			$filename = $this->xmlDir.date('Ymd', $this->startDate).'.xml';
		}
		else {
			$filename = $this->xmlDir.date('Ymd', $this->startDate).'_'.date('Ymd', $this->endDate).'.xml';
		}
		date_default_timezone_set($this->defaultTimeZone);
		return $filename;
	}

	/* Grabs the transform filename */
	public static function getXslFilename($report, $type) { 
		$filename = false;
		$filename = Env::main()->includePath().'/batch/report/xsl/'.$report.'_'.$type.'.xsl';
		return $filename;
	}

	/* Grabs the partner-specific filename for the file */
	public function getPartnerFilename($partner, $report, $type, $iteration = '') { 

		$dateFormat = 'Ymd';

		//see if there's a setting for the filename
		$filenameDateFormat = settingModel::getSetting('reportGenerateDateFormat', $report);
		if(isset($filenameDateFormat)) { 
			$dateFormat = $filenameDateFormat;
		}

		$filenameFormat = settingModel::getSetting('reportGenerateFilename', $report);
		if(isset($filenameFormat)) { 
			$report = $filenameFormat;
		}

		//filename
		$filename = $this->partnerDir.$partner.'/'.$type.'/'.$report;

		if(!strstr($filename, '|iteration|')) { $filename .= '|iteration|'; }
		$filename = str_replace('|iteration|', ucfirst($iteration), $filename);

		//our times should be in our report time, not GMT
		date_default_timezone_set($this->timeZone);

		//if it's a daily report, just show the day
		if(!strstr($filename, '|startdate|')) { $filename .= '|startdate|'; }
		$filename = str_replace('|startdate|', date($dateFormat, $this->startDate), $filename);
		$filename = str_replace('|enddate|', date($dateFormat, $this->endDate), $filename);
		$filename .= ".$type";

		//set back to our default timezone
		date_default_timezone_set($this->defaultTimeZone);
		return $filename;
	}

	/* Generates an xml file from a report */
	public function generateXml($debug = false) { 
		if(($this->endDate - $this->startDate) > (60*60*$this->hoursInOneDay)) {
			$this->generateXmlFromFiles();
			return;
		}

		$filename = $this->getXmlFilename();
		
		if($debug) { 
			print "Starting rawXml ledger report $filename " . date('Y-m-d H:i:s', strtotime('Now')) . "\n";
		}

		//all reports start from ledgers, we can change this later if needed
		$sql = 'SELECT DISTINCT `type` FROM `ledgers`';
		$result = db::query($sql, false);

		//go through all types
		while($row = mysql_fetch_assoc($result)) {
			$transaction = $row['type'];
			if($debug) { 
				print "...$transaction\n";
			}

			//add the `type` as a transaction type
			$this->addTransactionType($transaction);
		}
		$this->addTransactionType('redemption');
		print "...redemption\n";
		if($debug) { 
			print "SAVING\n";
		}

		//save the file so we can rock out.  Also this provides a "cached" version of the transaction
		//log as well
		if($debug) { 
			print "...".$filename."\n";
		}

		if(!file_exists($this->xmlDir)) { 
			mkdir($this->xmlDir, 0777, true);
		}
		$this->doc->save($filename);
		print "Finished rawXml ledger report " . date('Y-m-d H:i:s', strtotime('Now')) . "\n\n";
	}

	public function generateXmlFromFiles() { 
		print "Compiling files\n";
		$start = $this->startDate;
		$end = $this->endDate;


		while($start < $end) { 
	
			print "Grabbing " . date('Y-m-d', $start) . "\n";
			$report = new xmlReport(array(
				'startDate' => date('Y-m-d', $start)
			));
			$filename = $report->getXmlFilename();
			print "Looking for $filename..." ;
			if(!file_exists($filename)) { 
				print "NOT FOUND!\n";
				$report->generateXml(true);
			}
			else {
				print "ok\n";
			}


			//lets grab the relevant bits
			$doc = new DomDocument('1.0');
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput   = true;
			$doc->load($filename);

			//cycle through all children of the root nodes
			$root = $doc->getElementsByTagName('root');
print "Processing...\n";
			foreach($root->item(0)->childNodes  as $node) { 

				//see if this already exists in our document
				$type = $this->doc->getElementsByTagName($node->nodeName);
				if($type->length == 0) {
					$type = $this->doc->createElement($node->nodeName);
					$this->root->appendChild($type);
				}
				else {
					$type = $type->item(0);
				}
print $type->nodeName . ' ' . count($node->childNodes) . "\n"; 

				foreach($node->childNodes as $childNode) { 
					$entryNode = $this->doc->importNode($childNode, true);
					$type->appendChild($entryNode);
				}
			}
			//move on to the next day
			$start = strtotime('+1 day', $start);
		}

		$filename = $this->getXmlFilename();
		print "...$filename\n";
		$this->doc->save($filename);
		print "Finished rawXml ledger report " . date('Y-m-d H:i:s', strtotime('Now')) . "\n\n";
		
	}
}


