<?php

/**
 * Libtool bootstrap
 * 
 * @category giftingapp
 * @package libtool
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

// Package libtool
namespace libtool;

class bootstrap {
	/**
	 * Class constants for directory separator 
	 */
	const DS = DIRECTORY_SEPARATOR;

	/**
	 * Collection for holding and caching lib class pathes
	 * @var array
	 * @access private
	 */
	private static $_pathes = array();

	/**
	 * Bootstrapping initializer
	 *
	 * @return void
	 * @access public 
	 */
	public static function init() {
		;
	}  //end init

	/**
	 * Custom autoloader for libtool classes
	 *
	 * @param string $class Class name
	 * @return bool
	 * @access public
	 */
	public static function autoloader($class) {
		if (!file_exists(self::_path($class))) {
			return false;
		}
		include_once self::_path($class);

		return class_exists($class);
	}  //end autoloader

	/**
	 * Class file path builder
	 *
	 * @param string $class Class name
	 * @return string
	 * @access private
	 */
	private static function _path($class) {
		if (!isset(self::$_pathes[$class])) {
			$pathizedClassName = str_replace(array(__NAMESPACE__, '\\'), array('', self::DS), $class);
			self::$_pathes[$class] = dirname(__FILE__) . self::DS . ltrim($pathizedClassName, self::DS) . '.php';
		}

		return self::$_pathes[$class];
	}  //end _path

}  //end bootstrap

// Register the custom autoloader for libtool classes
spl_autoload_register('libtool\bootstrap::autoloader');
// Bootstrapping initializer
bootstrap::init();
