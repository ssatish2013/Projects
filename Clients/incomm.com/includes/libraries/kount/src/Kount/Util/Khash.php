<?php
/**
 * @package Kount
 * @subpackage Util
 */

/**
 * Kount payment token hashing class.
 *
 * Example usage:
 *   $hashed = Kount_Util_Khash::hashPaymentToken("4111111111111111");
 *     Expect: 411111WMS5YA6FUZA1KC
 *   $hashed = Kount_Util_Khash::hashPaymentToken("5199185454061655");
 *     Expect: 5199182NOQRXNKTTFL11
 *
 * @package Kount
 * @subpackage Util
 * @author Kount <custserv@kount.com>
 * @copyright 2011 Kount Inc. All Rights Reverved.
 */
class Kount_Util_Khash {

  /**
   * Create a Kount hash of a provided payment token. Payment tokens that can be
   * hashed via this method include: credit card numbers, Paypal payment IDs,
   * Check numbers, Google Checkout IDs, Bill Me Later IDs, and Green Dot
   * MoneyPak IDs.
   *
   * @param string $token String to be hashed
   * @return string Hashed token
   */
  static public function hashPaymentToken ($token) {
    $firstSix = mb_substr($token, 0, 6, 'latin1');
    $hash = self::hash($token, 14);
    return "{$firstSix}{$hash}";
  }

  /**
   * Create a Kount hash of a gift card number.
   *
   * @param int $merchantId Merchant ID
   * @param string $cardNumber Gift card number
   * @return string Hashed card number
   */
  static public function hashGiftCard ($merchantId, $cardNumber) {
    $hash = self::hash($cardNumber, 14);
    return "{$merchantId}{$hash}";
  }

  /**
   * Compute a Kount hash of the provided input string.
   *
   * @param string $data Data to hash
   * @param int $len Length of hash to retain
   * @return string Hashed data
   */
  static public function hash ($data, $len) {
    static $key = '4077th hawkeye trapper radar section-8';
    static $a = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $r = sha1("{$data}.{$key}");
    $c = '';
    if ($len > 17) {
      $len = 17;
    }

    $limit = 2 * $len;
    for ($i = 0; $i < $limit; $i += 2) {
      $c .= $a[hexdec(mb_substr($r, $i, 7, 'latin1')) % 36];
    }

    return $c;
  }

} // end Kount_Util_Khash