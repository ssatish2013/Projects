<?php
/**
 * @package Kount_Ris
 */

/**
 * RIS response data class.
 *
 * @package Kount_Ris
 * @author Kount <custserv@kount.com>
 * @version $Id: Response.php 19013 2012-03-29 17:59:09Z mmn $
 * @copyright 2012 Kount, Inc. All Rights Reserved.
 */
class Kount_Ris_Response {

  /**
   * Response data
   *
   * @var Hash map
   */
  private $response = array();

  /**
   * Raw response string
   *
   * @var string
   */
  private $raw;

  /**
   * A logger binding.
   * @var Kount_Log_Binding_Logger
   */
  protected $logger;

  /**
   * Construct a response object
   *
   * @param string $output Response string comes in as key=value\n pairs
   */
  public function __construct ($output) {
    $loggerFactory = Kount_Log_Factory_LogFactory::getLoggerFactory();
    $this->logger = $loggerFactory->getLogger(__CLASS__);

    $this->raw = $output;
    $lines = preg_split('/[\r\n]+/', $output, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($lines as $line) {
      list($key, $value) = explode('=', $line, 2);
      $this->response[$key] = $value;
    }
  }

  /**
   * Get the version number
   *
   * @return string
   */
  public function getVersion () {
    return $this->safeGet('VERS');
  }

  /**
   * Get the RIS mode
   *
   * @return string
   */
  public function getMode () {
    return $this->safeGet('MODE');
  }

  /**
   * Get the transaction id
   *
   * @return string
   */
  public function getTransactionId () {
    return $this->safeGet('TRAN');
  }

  /**
   * Get the merchant id
   *
   * @return int
   */
  public function getMerchantId () {
    return $this->safeGet('MERC');
  }

  /**
   * Get the session id
   *
   * @return string
   */
  public function getSessionId () {
    return $this->safeGet('SESS');
  }

  /**
   * Get the site
   *
   * @return string
   */
  public function getSite () {
    return $this->safeGet('SITE');
  }

  /**
   * Get the merchant order number
   *
   * @return string
   */
  public function getOrderNumber () {
    return $this->safeGet('ORDR');
  }

  /**
   * Get the RIS auto response (A/R/D)
   *
   * @return string
   */
  public function getAuto () {
    return $this->safeGet('AUTO');
  }

  /**
   * Get the RIS reason for the response/score
   *
   * @return string
   * @deprecated version 5.0.0 - 2012.
   *     Use Kount_Ris_Response->getReasonCode() instead.
   */
  public function getReason () {
    $this->logger->info('The method Kount_Ris_Response->getReason() is " +
        "deprecated. Use Kount_Ris_Response->getReasonCode() instead.');
    return $this->safeGet('REAS');
  }

  /**
   * Get the merchant defined decision reason code.
   *
   * @return string
   */
  public function getReasonCode () {
    return $this->safeGet('REASON_CODE');
  }

  /**
   * Get the Kount score
   *
   * @return int
   */
  public function getScore () {
    return $this->safeGet('SCOR');
  }

  /**
   * Get the geox
   *
   * @return string
   */
  public function getGeox () {
    return $this->safeGet('GEOX');
  }

  /**
   * Get the card brand
   *
   * @return string
   */
  public function getBrand () {
    return $this->safeGet('BRND');
  }

  /**
   * Get the 6 week velocity
   *
   * @return int
   */
  public function getVelo () {
    return $this->safeGet('VELO');
  }

  /**
   * Get the 6 hour velocity
   *
   * @return int
   */
  public function getVmax () {
    return $this->safeGet('VMAX');
  }

  /**
   * Get the network type
   *
   * @return string
   */
  public function getNetwork () {
    return $this->safeGet('NETW');
  }

  /**
   * Get the 'know your customer' flash
   *
   * @return string
   */
  public function getKnowYourCustomer () {
    return $this->safeGet('KYCF');
  }

  /**
   * Get the region
   *
   * @return string
   */
  public function getRegion () {
    return $this->safeGet('REGN');
  }

  /**
   * Get the kaptcha flag, enabled upon request and for when a RIS record has
   *
   * @return string
   */
  public function getKaptcha () {
    return $this->safeGet('KAPT');
  }


  /**
   * Get a string representing whether the remote device is using a proxy
   * @return string "Y" or "N"
   */
  public function getProxy () {
    return $this->safeGet('PROXY');
  }

  /**
   * Get the number of transactions associated with the email
   * @return string
   */
  public function getEmails () {
    return $this->safeGet('EMAILS');
  }

  /**
   * Get the two character country code setting in the remote device's
   * browser
   * @return string
   */
  public function getHttpCountry () {
    return $this->safeGet('HTTP_COUNTRY');
  }

  /**
   * Get a string representing the time zone of the customer as a 3 digit
   * number
   * @return string
   */
  public function getTimeZone () {
    return $this->safeGet('TIMEZONE');
  }

  /**
   * Get the number of transactions associated with the credit card
   * @return string
   */
  public function getCards () {
    return $this->safeGet('CARDS');
  }

  /**
   * Get a string representing whether the end device is a remotely
   * controlled computer
   * @return string "Y" or "N"
   */
  public function getPcRemote () {
    return $this->safeGet('PC_REMOTE');
  }

  /**
   * Get the number of transactions associated with the particular device
   * @return string
   */
  public function getDevices () {
    return $this->safeGet('DEVICES');
  }

  /**
   * Get a string representing the five layers (Operating System, SSL, HTTP,
   * Flash, JavaScript) of the remote device
   * @return string
   */
  public function getDeviceLayers () {
    return $this->safeGet('DEVICE_LAYERS');
  }

  /**
   * Get the mobile device's wireless application protocol
   * @return string
   */
  public function getMobileForwarder () {
    return $this->safeGet('MOBILE_FORWARDER');
  }

  /**
   * Get a string representing whether or not the remote device is voice
   * controlled
   * @return string "Y" or "N"
   */
  public function getVoiceDevice () {
    return $this->safeGet('VOICE_DEVICE');
  }

  /**
   * Get local time of the remote device in the YYYY-MM-DD format
   * @return string
   */
  public function getLocalTime () {
    return $this->safeGet('LOCALTIME');
  }

  /**
   * Get the mobile device type
   * @return string
   */
  public function getMobileType () {
    return $this->safeGet('MOBILE_TYPE');
  }

  /**
   * Get the device finger print
   * @return string
   */
  public function getFingerPrint () {
    return $this->safeGet('FINGERPRINT');
  }

  /**
   * Get a string representing whether or not the remote device allows flash
   * @return string "Y" or "N"
   */
  public function getFlash () {
    return $this->safeGet('FLASH');
  }

  /**
   * Get the language setting on the remote device
   * @return string
   */
  public function getLanguage () {
    return $this->safeGet('LANGUAGE');
  }

  /**
   * Get the remote device's country of origin as a two character code
   * @return string
   */
  public function getCountry () {
    return $this->safeGet('COUNTRY');
  }

  /**
   * Get a string representing whether the remote device allows JavaScript
   * @return string "Y" or "N"
   */
  public function getJavaScript () {
    return $this->safeGet('JAVASCRIPT');
  }

  /**
   * Get a string representing whether the remote device allows cookies
   * @return string "Y" or "N"
   */
  public function getCookies () {
    return $this->safeGet('COOKIES');
  }

  /**
   * Get a string representing whether the remote device is a mobile device
   * @return string "Y" or "N"
   */
  public function getMobileDevice () {
    return $this->safeGet('MOBILE_DEVICE');
  }

  /**
   * Print all values in the object
   * @return string The string representation of the response
   */
  public function __toString () {
    return $this->raw;
  }

  /**
   * Get a possible error code
   *
   * @return string
   */
  public function getErrorCode () {
    return $this->safeGet('ERRO');
  }

  /**
   * Get a value from $this->response with safe handling of missing keys.
   * @param string $key Value to get
   * @return string Value found in response or null if key not present
   */
  protected function safeGet ($key) {
    if (array_key_exists($key, $this->response)) {
      return $this->response[$key];
    } else {
      return null;
    }
  }

  /**
   * Get an array of the rules triggered by this Response.
   * @return Rules
   */
  public function getRulesTriggered () {
    $rules = array();
    $ruleCount = $this->getNumberRulesTriggered();
    for ($i = 0; $i < $ruleCount; $i++) {
      $ruleId = $this->safeGet("RULE_ID_{$i}");
      $rules[$ruleId] = $this->safeGet("RULE_DESCRIPTION_{$i}");
    }
    return $rules;
  }

  /**
   * Get the number of rules triggered with the response.
   * @return int Number of rules triggered
   */
  public function getNumberRulesTriggered () {
    // A RIS response will always contain the field RULES_TRIGGERED which
    // will be set to zero if there are no rules triggered.
    return (int) $this->safeGet("RULES_TRIGGERED");
  }

  /**
   * Get an array of the warnings returned by this Response.
   * @return Warnings
   */
  public function getWarnings () {
    $warnings = array();
    $warningCount = $this->getWarningCount();
    for ($i = 0; $i < $warningCount; $i++) {
      $warnings["WARNING_{$i}"] = $this->safeGet("WARNING_{$i}");
    }
    return $warnings;
  }

  /**
   * Get the number of warnings associated with the response.
   * @return int Number of warnings
   */
  public function getWarningCount () {
    // A RIS response will always contain the field WARNING_COUNT which
    // will be set to zero if there are no warnings.
    return (int) $this->safeGet("WARNING_COUNT");
  }

  /**
   * Check if the response has warnings.
   * @return boolean True if response has warnings, false otherwise.
   */
  public function hasWarnings () {
    return (bool) $this->getWarningCount();
  }

  /**
   * Get the number of errors associated with the response.
   * @return Errors
   */
  public function getErrors () {
    $errors = array();
    $errorCount = $this->getErrorCount();
    for ($i = 0; $i < $errorCount; $i++) {
      $errors["ERROR_{$i}"] = $this->safeGet("ERROR_{$i}");
    }
    return $errors;
  }

  /**
   * Get the number of errors associated with the response.
   * @return int Number of errors
   */
  public function getErrorCount () {
    // A normal response will not contain any errors in which case the
    // RIS response field ERROR_COUNT will not be sent.
    return (int) $this->safeGet("ERROR_COUNT");
  }

  /**
   * Check if the response has errors.
   * @return boolean True if response has errors, false otherwise.
   */
  public function hasErrors () {
    return (bool) $this->getErrorCount();
  }

  /**
   * Get LexisNexis Chargeback Defender attribute data associated with the RIS
   * transaction. Please contact your Kount representative to enable support
   * for this feature in your merchant account.
   *
   * @return array The array keys are the attribute names and the values are the
   *     attribute values.
   */
  public function getLexisNexisCbdAttributes () {
    return $this->getPrefixedResponseDataMap("CBD_");
  }

  /**
   * Get LexisNexis Instant ID attribute data associated with the RIS
   * transaction. Please contact your Kount representative to enable support
   * for this feature in your merchant account.
   *
   * @return array The array keys are the attribute names and the values are the
   *     attribute values.
   */
  public function getLexisNexisInstantIdAttributes () {
    return $this->getPrefixedResponseDataMap("INSTANTID_");
  }

  /**
   * Get a map of the response data where the keys are the RIS response keys
   * that begin with a specified prefix.
   * @param string $prefix Key prefix.
   * @return map Map of key-value pairs for a specified RIS key prefix.
   */
  protected function getPrefixedResponseDataMap ($prefix) {
    $data = array();
    foreach ($this->response as $key => $value) {
      $prefixLength = mb_strlen($prefix);
      if (mb_substr($key, 0, $prefixLength) == $prefix) {
        $data[mb_substr($key, $prefixLength)] = $value;
      }
    }
    return $data;
  }

  /**
   * Get a map of the rules counters triggered in the response.
   * @return Map Key: counter name, Value: counter value.
   */
  public function getCountersTriggered () {
    $counters = array();
    $numCounters = $this->getNumberCountersTriggered();
    for ($i = 0; $i < $numCounters; $i++) {
      $counterName = $this->safeGet("COUNTER_NAME_{$i}");
      $counters[$counterName] = $this->safeGet("COUNTER_VALUE_{$i}");
    }
    return $counters;
  }

  /**
   * Get the number of rules counters triggered in the response.
   * @return int Number of counters triggered
   */
  public function getNumberCountersTriggered () {
    return (int) $this->safeGet("COUNTERS_TRIGGERED");
  }

} // Kount_Ris_Response
