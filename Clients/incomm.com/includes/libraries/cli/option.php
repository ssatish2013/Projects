<?php

/**
 * CLI tool option plugin
 * 
 * @category giftingapp
 * @package libtool.cli
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

// Package libtool.cli
namespace libtool\cli;

use libtool\cli\exception\noValueSpecifiedException as NoValueSpecifiedException;
use \UnexpectedValueException;

class option {
	/**
	 * Class constants for exception code
	 * Exception code class 11001* (11001[0-9])
	 */
	const EXCP_INVALID_ARG = 110010;
	const EXCP_UNEXPECTED_VAL = 110011;
	const EXCP_NO_VAL = 110012;

	/**
	 * Class constants for option types
	 * - Short option
	 * - long option
	 */
	const OPT_SHORT = 0;
	const OPT_LONG = 1;
	/**
	 * Class constants for options sorted, organised and stored in different types of class properties
	 * - External options: options passed into class from constructor or setOptions() method
	 *     { optionName1: [ shortOption1, longOption1 ], ..., optionNameN: [ shortOptionN, longOptionN ] }
	 * - Extracted options: options extracted from external options, reorganized and grouped into short
	 *     options string and short options array which are ready to use for getopt() function
	 *     [ shortOptions, longOptions ]
	 *     getopt(shortOptions, longOptions)
	 * - Mapped options: options that are mapped from CLI options and external options, it basically takes
	 *     both short and long options and assign them to mappedOptions, for the same optionNameN, if both
	 *     shortOptionN and longOptionN exist, longOptionN will override shortOptionN
	 *     {optionName1: optionValue1, ..., optionNameN: optionValueN }
	 */
	const OPT_TYPE_EXTERNAL = 0;
	const OPT_TYPE_EXTRACTED = 1;
	const OPT_TYPE_MAPPED = 2;

	/**
	 * External option settings imported from class constructor or setOptions() method
	 * @var array
	 * @access protected
	 */
	protected $_externalOpts = array();
	/**
	 * Options extracted from php getopts() function
	 * @var array
	 * @access protected
	 * @uses self::OPT_SHORT
	 * @uses self::OPT_LONG
	 */
	protected $_extractedOpts = array(
		self::OPT_SHORT => '',
		self::OPT_LONG => array()
	);
	/**
	 * Options mapped from CLI opts to externalOpts settings
	 * @var array
	 * @access protected
	 */
	protected $_mappedOptions = array();

	/**
	 * Constructor
	 * @param array $arguments 
	 * @return volid
	 * @access public
	 * @uses self::setOptions()
	 */
	public function __construct(array $arguments = array()) {
		if (count($arguments) > 0) {
			$this->setOptions($arguments[0]);
		}
	}   //end __construct

	/**
	 * Set custom CLI options
	 * 
	 * array (
	 *   'optName1' => array( 'shortOpt1' , 'longOpt1' ),
	 *   'optName2' => array( 'shortOpt2' , 'longOpt2' ),
	 *   'optName3' => array( 'shortOpt3' , 'longOpt3' ),
	 *   ...,
	 *   ...
	 * )
	 * 
	 * @param array $options 
	 * @return void
	 * @access public
	 * @uses self::_externalOpts
	 * @uses self::_extractOpts()
	 */
	public function setOptions($options) {
		$this->_externalOpts = $options;
		$this->_extractOpts();
	}  //end setOptions

	/**
	 * Get different types of options
	 *
	 * @param int $optionType See class constants OPT_TYPE_*
	 * @return array
	 * @throws UnexpectedValueException
	 * @access public
	 * @uses self::OPT_TYPE_EXTERNAL
	 * @uses self::OPT_TYPE_EXTRACTED
	 * @uses self::OPT_TYPE_MAPPED
	 * @uses self::EXCP_INCORRECT_OPT_TYPE
	 */
	public function getOptions($optionType = self::OPT_TYPE_MAPPED) {
		$options = array();
		switch ($optionType) {
			case self::OPT_TYPE_EXTERNAL:
				$options = $this->_externalOpts;
				break;
			case self::OPT_TYPE_EXTRACTED:
				$options = $this->_extractedOpts;
				break;
			case self::OPT_TYPE_MAPPED:
				$options = $this->_mappedOptions;
				break;
			default:
				throw new UnexpectedValueException('Incorrect option type', self::EXCP_INCORRECT_OPT_TYPE);
		}

		return $options;
	}  //end getOptions

	/**
	 * Get mapped options (extracted from CLI and mapped to pre-defined settings)
	 *
	 * @return array
	 * @access public
	 * @uses self::getOptions()
	 * @uses self::OPT_TYPE_MAPPED
	 */
	public function getMappedOptions() {
		return $this->getOptions(self::OPT_TYPE_MAPPED);
	}  //end getMappedOptions

	/**
	 * Parse CLI options
	 *
	 * @return array
	 * @access public
	 * @uses $this->_extractedOpts
	 * @uses self::OPT_SHORT
	 * @uses self::OPT_LONG
	 * @uses self::_mapOpts()
	 * @uses self::_examineOptionValues()
	 * @uses self::_mappedOptions
	 */
	public function parse() {
		$cliOpts = getopt($this->_extractedOpts[self::OPT_SHORT], $this->_extractedOpts[self::OPT_LONG]);
		foreach ($this->_externalOpts as $optName => $opts) {
			$this->_mapOpts($cliOpts, $optName, $opts);
		}
		$this->_examineOptionValues();

		return $this->_mappedOptions;
	}  //end parse

	/**
	 * Check if the given option name exists in CLI options
	 *
	 * @param string $option
	 * @return bool
	 * @access public
	 * @uses self::_mappedOptions
	 */
	public function exists($option) {
		return isset($this->_mappedOptions[$option]);
	}  //end exist

	/**
	 * Extract CLI options and put them under different categories (long or short opts)
	 * in self::_extractedOpts, which will be used in getopts() function as the 1st and
	 * 2nd arguments
	 *
	 * @return void
	 * @access protected
	 * @uses self::_externalOpts
	 * @uses self::OPT_SHORT
	 * @uses self::OPT_LONG
	 */
	protected function _extractOpts() {
		foreach ($this->_externalOpts as $optName => $opts) {
			$this->_extractedOpts[self::OPT_SHORT] .= $opts[0];
			$this->_extractedOpts[self::OPT_LONG][] = $opts[1];
		}
	}  //end _extractOpts

	/**
	 * Map CLI options to an organised collection with pre-defined option names
	 *
	 * @param array $cliOptions
	 * @param string $currentOptionName
	 * @param array $currentOptions
	 * @return void
	 * @access protected
	 * @uses self::_isValueRequired()
	 * @uses self::_mappedOptions
	 * @uses self::_isValueOptional()
	 * @uses self::_doesNotAcceptValue()
	 */
	protected function _mapOpts(array & $cliOptions, $currentOptionName, array $currentOptions) {
		foreach ($currentOptions as $optType => $opt) {
			$optEscaped = trim($opt, ':');
			if (!isset($cliOptions[$optEscaped])) {
				if ($this->_isValueRequired($opt) && !isset($this->_mappedOptions[$currentOptionName])) {
					$this->_mappedOptions[$currentOptionName] = null;
				}
			} else {
				if ($this->_isValueOptional($opt) || $this->_doesNotAcceptValue($opt)) {
					$this->_mappedOptions[$currentOptionName] = ($cliOptions[$optEscaped] !== false)
						? $cliOptions[$optEscaped]
						: '';
				} else {
					$this->_mappedOptions[$currentOptionName] = $cliOptions[$optEscaped];
				}
			}
		}
	}  //end _mapOpts

	/**
	 * Check to see if the given option is required
	 *
	 * @param string $option
	 * @return bool
	 * @access protected
	 */
	protected function _isValueRequired($option) {
		return preg_match('/[a-zA-Z0-9]:$/', $option);
	}  //end _isValueRequired

	/**
	 * Check to see if the given option is optional
	 *
	 * @param string $option
	 * @return bool
	 * @access protected
	 */
	protected function _isValueOptional($option) {
		return preg_match('/[a-zA-Z0-9]::$/', $option);
	}  //end _isValueOptional

	/**
	 * Check to see if the given option does not accept any values
	 *
	 * @param string $option
	 * @return bool
	 * @access protected
	 */
	protected function _doesNotAcceptValue($option) {
		return preg_match('/[a-zA-Z0-9]$/', $option);
	}  //end _doesNotAcceptValue

	/**
	 * Examine CLI options and check if there are any absent required opts
	 *
	 * @return void
	 * @throws NoValueSpecifiedException
	 * @access protected
	 * @uses self::_mappedOptions
	 */
	protected function _examineOptionValues() {
		foreach ($this->_mappedOptions as $opt => $val) {
			if (is_null($val)) {
				throw new NoValueSpecifiedException(
					'No value specified for required option ['.$opt.']',
					self::EXCP_NO_VAL
					);
			}
		}
	}  //end _examineOptionValues

}  //end option
