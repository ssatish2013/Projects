<?php

/**
 * CLI tool
 * 
 * @category giftingapp
 * @package libtool
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 * @example libtool\cli::option()
 *          libtool\cli::option(array(...))->parse()
 *          libtool\cli::option()->exists('...')
 *          - add a backward slash at the very beginning of namespace\classname to avoid
 *            any possible namespace miss linking or referring
 *          - see the line below
 *          \libtool\cli::is()
 */

// Package libtool
namespace libtool;

use libtool\cli\exception\incorrectCliEnvironmentException as IncorrectCliEnvironmentException;

/**
 * Magic method
 * @method object option(array $options)
 */
class cli {
	/**
	 * Class constant for exception code
	 * Exception code class 11000* (11000[0-9])
	 */
	const EXCP_INCORRECT_ENV = 110001;

	/**
	 * Collection of cli tool class instances (e.g. option, ...)
	 * @var array 
	 * @access private
	 * @static
	 */
	private static $_instances = array();

	/**
	 * Run from CLI?? (or web)
	 * @var bool (default null)
	 * @access protected
	 * @static
	 */
	protected static $_runFromCli = null;

	/**
	 * Magic static method for static methods call
	 *
	 * @param string $name Static Method name
	 * @param array $arguments Method arguments
	 * @return object
	 * @throws IncorrectCliEnvironmentException
	 * @access public
	 * @uses self::_isCli()
	 * @uses self::$_instances
	 * @uses self::EXCP_INCORRECT_ENV
	 * @static
	 */
	public static function __callStatic($name, array $arguments) {
		if (!self::_isCli()) {
			throw new IncorrectCliEnvironmentException(
				'Incorrect CLI environment',
				self::EXCP_INCORRECT_ENV
				);
		}
		$class = '\\' . __CLASS__ . '\\' . $name;
		if (class_exists($class)) {
			if (!isset(self::$_instances[$class])) {
				self::$_instances[$class] = new $class($arguments);
			}
			return self::$_instances[$class];
		}
	}  //end __callStatic

	/**
	 * Detect if the script is running from CLI
	 * Public facing
	 *
	 * @return bool
	 * @access public
	 * @uses self::_isCli()
	 * @static
	 */
	public static function is() {
		return self::_isCli();
	}  //end is

	/**
	 * Detect if the script is running from CLI
	 * Non-public facing
	 *
	 * @return bool
	 * @access protected
	 * @uses self::$_runFromCli
	 * @static
	 */
	protected static function _isCli() {
		if (!isset(self::$_runFromCli)) {
			self::$_runFromCli = isset($_SERVER['_']);
		}

		return self::$_runFromCli;
	}  //end _isCli

}  //end cli
