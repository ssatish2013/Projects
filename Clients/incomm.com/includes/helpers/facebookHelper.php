<?php

//@TODO maybe move these to a more "abstract" method in the access token model
class facebookHelper {
	public static function populateFacebookOnMessage($message){
		env::includeLibrary("facebook/facebook");

		if($accessToken=request::unsignedPost('fbAccessToken')){
			$fbCreds = settingModel::getPartnerSettings(globals::partner(), 'facebook');

			$facebook = new Facebook( array(
				'appId'  => $fbCreds['appId'],
				'secret' => $fbCreds['secret']
			));

			$facebook->setAccessToken($accessToken);
			$message->facebookAccessToken=$accessToken;
			$message->facebookUserId=$facebook->getUser();

			//exchange a long-live token
			try{
				$data = array(
						'grant_type' => 'fb_exchange_token',
						'client_id'=> $fbCreds['appId'],
						'client_secret' => $fbCreds['secret'],
						'fb_exchange_token' => $accessToken
				);

				$req = '';

				foreach ($data as $key => $value){
					$value = urlencode(stripslashes($value));
					$req .= "&" . $key . "=" . $value;
				}


				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/oauth/access_token');
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

				$response = curl_exec($ch);
				parse_str($response,$output);
				if ($output['access_token']){
					$message->facebookAccessToken = $output['access_token'];
					log::info("$message->facebookUserId's access_token has been extended.");
				}

			}
			catch (Exception $e) {
				log::error("Can't exchange $message->facebookUserId's access_token, data: " . json_encode($data), $e);
			}


		}
	}

	public static function resampleImage($url){
		$keyname = str_replace('https://', '', $url);
		$keyname = str_replace('http://', '', $keyname);
		//always fetch using http curl
		$curlurl = 'http://'.$keyname;
		$keyname = str_replace('.s3.amazonaws.com','',$keyname);
		$keyname = str_replace('/', '.', $keyname);

		$keyId = "13XC7NDN0C35M66AR582";
		$secretKey = "mxk+DsUWIzg0n8OK+wp+Tw2ejNdAU8v0Tz9AQz89";
		$S3 = new S3( $keyId, $secretKey );
		$bucketName = 'gc-fgs';
		$prefixName = 'facebook/';

		//check if the image already resampled, if so return s3 path directly
		$existed = $S3->getObjectInfo($bucketName, $prefixName.$keyname);
		if ($existed){
			return 'https://' . $bucketName . '.s3.amazonaws.com/' . $prefixName . $keyname;
		}

		$finfo = pathinfo($url);
		list($filename) = explode('?',$finfo['basename']);
		$ext = strtolower($finfo['extension']);
		$allowedext = array('jpg','jpeg','gif','png');

		if (!in_array($ext,$allowedext)){
			return;
		}
		//download image first
		$ch = curl_init();
		$user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.7) Gecko/20060909 Firefox/1.5.0.7";
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_USERAGENT,$user_agent);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

		// grab URL
		$out = curl_exec($ch);

		// close cURL resource, and free up system resources
		curl_close($ch);

		$ran = rand () ;
		$local_filepath = '../cache/'.$ran.$filename;
		file_put_contents($local_filepath,$out);

		//resampling
		switch ($ext) {
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($local_filepath);
				break;
			case 'gif':
				$image = imagecreatefromgif($local_filepath);
				break;
			case 'png':
				$image = imagecreatefrompng($local_filepath);
				break;
		}

		list($width, $height) = getimagesize($local_filepath);
		//fixed 1:1 size image for fb timeline display
		$dst = imagecreatetruecolor($width,$width);
		$whitebg = imagecolorallocate($dst, 255, 255, 255 );
		imagefill($dst, 0, 0, $whitebg);
		$offset = ($width-$height)/2;
		self::fastimagecopyresampled($dst,$image,0,$offset,0,0,$width,$height,$width,$height);

		ob_start();
		imagepng($dst, NULL, 2);
		$output = ob_get_clean();

		//upload back to s3
		$S3->putObjectString( $output, $bucketName, ($prefixName . $keyname), S3::ACL_PUBLIC_READ);
		$s3path = 'https://' . $bucketName . '.s3.amazonaws.com/' . $prefixName . $keyname;
		return $s3path;
	}


	public static function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
		// Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
		// Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
		// Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
		// Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
		//
		// Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
		// Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
		// 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
		// 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
		// 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
		// 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
		// 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

		if (empty($src_image) || empty($dst_image) || $quality <= 0) {
		return false;
	}
	if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		$temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
		imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
		imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
		imagedestroy ($temp);
	} else imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	return true;
	}
}


