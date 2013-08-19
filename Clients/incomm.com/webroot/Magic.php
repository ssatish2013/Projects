<?php
require_once(dirname(__FILE__)."/../includes/init.php");
Env::main(); // Make sure config's contructor gets run

try {
	log::$defaultEntry->context->partner = globals::partner();

	// Initialize the session
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	sessionStore::init();

	// Set the language for i18n multilingual
	if (globals::lang() != language::getDefault()) {
		language::init(globals::partner(), globals::lang());
	}

	// Take the URL and explode it into pieces
	list( $uri ) = explode( "?", $_SERVER['REQUEST_URI'] );

	$uriParts = array_values( array_filter( explode( '/', $uri )));

	// Scrub uri parts
	if ( count( $uriParts ) > 0 ) {
		$uriParts = array_map( function( $part ) {
			return preg_replace( '/[^a-zA-Z0-9_-]/', '', $part );
		}, $uriParts );
	}


	if(isset( $uriParts[0] ) && substr($uriParts[0],0,1)=="_"){
		$controller = "redirect";
		$method = "handler";

		// Set the redirectLoader
		$redirectLoader = substr($uriParts[0],1);
		globals::redirectLoader($redirectLoader);

		array_shift( $uriParts ); // shift off the _
		if ( isset( $uriParts[0] )) {
			$targetController = array_shift( $uriParts );
		}

		if ( isset( $uriParts[0] )) {
			$targetMethod = array_shift( $uriParts );
		}
	} else {
		// Grab the controller and method
		if ( isset( $uriParts[0] )) {
			$controller = array_shift( $uriParts );
		}

		if ( isset( $uriParts[0] )) {
			$method = array_shift( $uriParts );
		}
	}

	// Get the remaining pieces
	$remainingParts = array_slice( $uriParts, 0 );
	$parameters = array();

	// Every two pieces become a key/value pair
	if ( count( $remainingParts ) > 0 ) {
		foreach( array_chunk( $remainingParts, 2 ) as $pair ) {
			if ( isset( $pair[1] ) ) {
				$parameters[ $pair[0] ] = $pair[1];
			}
		}
	}

	// Initialize Parameters
	request::init( $parameters );

	// If no controller specified, use "gift"
	if ( ! isset( $controller )) {
		$controller = "gift";
	}
	$controllerName = $controller."Controller";

	if (!class_exists($controllerName)) {
		throw new NotFoundException("class $controllerName doesn't exist.");
	}

	// If no method specified, get default for the controller
	if ( ! isset( $method ) && isset( $controllerName::$defaultMethod )) {
		$method = $controllerName::$defaultMethod;
	}

	$methodCalled = false;
	// Handle redirect controller specially
	if( isset($controller) && $controller=="redirect" ){
		if( !isset($targetController) ){
			$targetController='';
		}

		if( !isset($targetMethod) ){
			$targetMethod='';
		}

		$obj = new $controllerName();
		$obj->$method($targetController,$targetMethod,$parameters);
		$methodCalled = true;
	}


	// Custom methods by request  type
	$method = preg_replace("/(?:get|post|put|delete)$/i", "", $method);
	$methods = array(
		$method . ucwords( strtolower( utilityHelper::getRequestType() )),
		$method
	);


	foreach ( $methods as $methodType ) {

		if (!$methodCalled && method_exists( $controllerName, $methodType )) {

			// Set controller and methods on smarty
			view::set("controller", $controller);
			view::set("method", $method);
			view::set("methodType", $methodType);

			if (Env::isDev()) {
				view::set("scriptTags", jsHelper::package("js/package-all.txt", "/js"));
			} else {
				$alljsname = "js/all-".Env::getVersion().".js";
				view::set("scriptTags", "<script src=\"/$alljsname?".jsHelper::getTimestamp($alljsname)."\"></script>");
			}

			// FIXME Some settings for look and feel - temporary
			view::set("siteWidth", "1000px");
			view::set("siteWidth2", "978px");

			//set globals for code
			globals::controller($controller);
			globals::method($method);
			globals::methodType($methodType);



			if(geoipModel::isExempted($controllerName, $methodType) || geoipModel::canView()){
				log::debug("Request " . $_SERVER['REQUEST_URI'] . " -> $controllerName->$methodType()");
				$obj = new $controllerName();
				$obj->$methodType($remainingParts);
			}
			$methodCalled = true;
		}
	}

	if ( ! $methodCalled ) {
		throw new NotFoundException("Method $methodCalled not found.");
	}

	new eventLogModel("flow", $controller, $method);
} catch (NotFoundException $e) {
	log::warn("Not found exception. Request: " . $_SERVER['REQUEST_URI'], $e);
	header('HTTP/1.0 404 Not Found');
	include '404.php';
} catch( Exception $e ) {
	log::error("Uncaught exception.", $e);
	include '500.php';
}
// remove the flashMessage
unset($_SESSION['flashMessage']);
