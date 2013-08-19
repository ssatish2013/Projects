<?php
class inCommXMLMessage {
	private $fields = array();

	public function setMessage($msg){
		// The first 2 characters are the length of the message, which we do not need.
		$msg = substr($msg,2);
		
		$xmlObj = simplexml_load_string($msg);
		
		foreach($xmlObj->field as $field){
			$attr=array();
			foreach($field->attributes() as $k=>$v){
				$attr[$k]=(string)$v;
			}

			$this->fields[$attr['id']]=$attr['value'];
		}

	}

	public function getMessage(){
		$xmlString = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<isomsg></isomsg>";
		$xmlObj = simplexml_load_string($xmlString);
		foreach($this->fields as $id=>$value){
			$field = $xmlObj->addChild('field');
			$field->addAttribute("id",$id);
			$field->addAttribute("value",$value);
		}
		$xmlString = $xmlObj->asXML();
		$mLen = strlen($xmlString);
		return chr(intval($mLen/256)).chr($mLen%256).$xmlString;
	}

	public function setField($id,$value){
		$this->fields[$id]=$value;
	}

	public function getField($id){
		if(isset($this->fields[$id])){
			return $this->fields[$id];
		} else {
			return false;
		}
	}
	
	public function getFieldsArray(){
		return $this->fields;
	}
}