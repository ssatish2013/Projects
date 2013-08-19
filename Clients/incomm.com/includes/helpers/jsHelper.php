<?php
class jsHelper {
	public static function package($packageFile, $base) {
		$pf = file_get_contents($packageFile);
		$h = '';
		foreach (explode("\n", $pf) as $file) {
			$file = trim($file);
			if ($file && $file[0] != '#') {
				$h.= "<script src=\"$base/$file\"></script>\n";
			}
		}
		return $h;
	}
	
	public static function getTimestamp($js) {
		return file_exists($js)? filemtime($js):'';
	}
}