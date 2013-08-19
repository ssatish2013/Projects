<?php
class pixelController{
	public function t(){
		$guid = request::url('guid');
		
		$emails = emailModel::loadAll(array(
			"guid"=>$guid
		));
		
		if(sizeof($emails)){
			$email = $emails[0];
			if(!$email->openedAt){
				$email->openedAt = date("Y/m/d H:i:s");
				$email->save();
			}
		}
		
		ob_end_clean();
		
		// Output transparent gif
		header('Cache-Control: no-cache');
		header('Content-type: image/gif');
		header('Content-length: 37');

		echo base64_decode('R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
	}
}