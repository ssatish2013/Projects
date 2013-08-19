<?php
/**
 * @package Kount
 * @author Kount <custserv@kount.com>
 * @version SVN: $Id: autoload.php 19026 2012-03-29 22:37:30Z mmn $
 * @copyright 2012 Kount, Inc. All Rights Reserved.
 */

/**
 * Simple autoloader that converts underscore and backslash to directory
 * separators and attempts to resolve files relative to this one.
 *
 * @param string $class Class to load
 * @return bool True if load succeeded, false otherwise
 */
// @SUPPRESS global function used outside static class.
function kount_sdk_autoload ($class) {
  $path = dirname(__FILE__) . DIRECTORY_SEPARATOR .
      strtr($class, '_\\', DIRECTORY_SEPARATOR) . '.php';

  if (file_exists($path)) {
    require_once $path;
    return true;
  }
  return false;
}

if (function_exists('spl_autoload_register')) {
  // supported by PHP >= 5.1.2
  spl_autoload_register('kount_sdk_autoload');

} else if (!function_exists('__autoload')) {
  // supported by PHP >= 5.0.0
  function __autoload ($class) {
    kount_sdk_autoload($class);
  }

} else {
  error_log('Unable to register kount_autoload function.');

}
