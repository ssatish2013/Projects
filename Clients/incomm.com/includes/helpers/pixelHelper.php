<?php

class pixelHelper {

	static public function arithmetic ( $current, $change ) {
		return (((int) $current) + ((int) $change)) . "px";
	}

}
