<?php

class cssController {

	public static $defaultMethod = "build";

	private function compress( $css ) {
		env::includeLibrary('cssmin');
		return CssMin::minify($css);
	}

	public function build() {
		header('Content-Type: text/css; charset=utf-8');

		$file = basename( $_SERVER['REDIRECT_URL'], '.css' );
		
		$css = memcacheHelper::get("css".$file);
		if($css){
			echo $css;
		} else {
			$parts = explode( '-', $file );
			$type = array_pop( $parts );

			// Grab and minify CSS
			$css = view::ReturnRender('css/' . $type  . '.css');
			$css = $this->compress( $css );

			memcacheHelper::set("css".$file, "/* Generated on " . date("r") . "*/ " . $css, 3600);
			memcacheHelper::set("md5css".$file, md5($css), 3600);
			echo "/* Cached for the first time */ " . $css;
		}
	}
	
	
}
