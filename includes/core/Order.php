<?php
namespace EmsCore;

class Order {
  const TXNTYPE_SALE = 'sale';
  const TXNTYPES = array(
    Order::TXNTYPE_SALE
  );

  const HASH_ALGORTYTHM_SHA256 = 'SHA256';
  const HASH_ALGORYTHMS = array(
    Order::HASH_ALGORTYTHM_SHA256
  );

  const PAYMENT_MASTERCARD = 'M';
  const PAYMENT_VISA = 'V';
  const PAYMENT_DINERS = 'C';
  const PAYMENT_IDEAL = 'ideal';
  const PAYMENT_KLARNA = 'klarna';
  const PAYMENT_MAESTRO = 'MA';
  const PAYMENT_MAESTRO_UK = 'maestroUK';
  const PAYMENT_MASTERPASS = 'masterpass';
  const PAYMENT_PAYPAL = 'paypal';
  const PAYMENT_SOFORT = 'sofort';
  const PAYMENT_BANCONTACT = 'BCMC';
  const PAYMENTS = array(
    Order::PAYMENT_MASTERCARD,
    Order::PAYMENT_VISA,
    Order::PAYMENT_DINERS,
    Order::PAYMENT_IDEAL,
    Order::PAYMENT_KLARNA,
    Order::PAYMENT_MAESTRO,
    Order::PAYMENT_MAESTRO_UK,
    Order::PAYMENT_MASTERPASS,
    Order::PAYMENT_PAYPAL,
    Order::PAYMENT_SOFORT,
    Order::PAYMENT_BANCONTACT
  );

  const IDEAL_ABN_AMRO = 'ABNANL2A';
  const IDEAL_ING = 'INGBNL2A';
  const IDEAL_SNS_BANK = 'SNSBNL2A';
  const IDEAL_VAN_LANSCHOT = 'FVLBNL22';
  const IDEAL_TRIDOS_BANK = 'TRIONL2U';
  const IDEAL_KNAB = 'KNABNL2H';
  const IDEAL_RABOBANK = 'RABONL2U';
  const IDEAL_REGIOBANK = 'RBRBNL21';
  const IDEAL_ASN_BANK = 'ASNBNL21';
  const IDEAL_BUNQ = 'BUNQNL2A';
  const IDEAL_BANKS = array(
    Order::IDEAL_ABN_AMRO,
    Order::IDEAL_ING,
    Order::IDEAL_SNS_BANK,
    Order::IDEAL_VAN_LANSCHOT,
    Order::IDEAL_TRIDOS_BANK,
    Order::IDEAL_KNAB,
    Order::IDEAL_RABOBANK,
    Order::IDEAL_REGIOBANK,
    Order::IDEAL_ASN_BANK,
    Order::IDEAL_BUNQ
  );

  private static $fieldValidation = array(
    'transactionType' => Order::TXNTYPES,
    'timezone' => array(),
    'transactionTime' => 'time',
    'hashAlgorythm' => Order::HASH_ALGORYTHMS,
    'chargetotal' => 'price',
    'shipping' => 'price',
    'vattax' => 'price',
    'subtotal' => 'price',
    'currency' => 'currency',
    'mobile' => 'bool',
    'orderId' => 'notempty',
    'paymentMethod' => Order::PAYMENTS,
    'idealIssuerID' => Order::IDEAL_BANKS,
    'authenticateTransaction' => 'bool',
    'bcompany' => 'notempty',
    'bname' => 'notempty',
    'baddr1' => 'notempty',
    'baddr2' => 'notempty',
    'bcity' => 'notempty',
    'bstate' => 'notempty',
    'bcountry' => 'notempty',
    'bzip' => 'notempty',
    'phone' => 'notempty',
    'fax' => 'notempty',
    'email' => 'notempty',
    'sname' => 'notempty',
    'saddr1' => 'notempty',
    'saddr2' => 'notempty',
    'scity' => 'notempty',
    'sstate' => 'notempty',
    'scountry' => 'notempty',
    'szip' => 'notempty',
    'klarnaFirstname' => 'notempty',
    'klarnaLastname' => 'notempty',
    'klarnaStreetName' => 'notempty',
    'klarnaHouseNumber' => 'notempty',
    'klarnaHouseNumberExtension' => 'notempty',
    'klarnaPhone' => 'notempty',

    // in locale format (en_US, en_GB)
    'language' => 'notempty'
  );

  private static $initialized = false;

  private $fields = array(
    'transactionType' => Order::TXNTYPE_SALE,
    'hashAlgorythm' => Order::HASH_ALGORTYTHM_SHA256,
    'items' => array()
  );

  static function getDateTime() {
    return date_i18n('Y:m:d-H:i:s');
  }

  static function init() {
    if(!self::$initialized) {
      self::$fieldValidation['timezone'] = \DateTimeZone::listIdentifiers();
      self::$initialized = true;
    }
  }

  function __set($name, $value) {
    self::init();

    // Either in $fieldValidation or starts with "item"
    if(!array_key_exists($name, self::$fieldValidation) and substr($name, 0, 4) !== 'item') {
      return;
    }

    $validation = isset(self::$fieldValidation[$name]) ? self::$fieldValidation[$name] : 'notempty';

    if(is_array($validation) and !in_array($value, $validation)) {
      return;
    } elseif($validation === 'time' and !preg_match('/[0-9]{4}:[0-9]{2}:[0-9]{2}-[0-9]{2}:[0-9]{2}:[0-9]{2}/', $value)) {
      return;
    } elseif($validation === 'notempty' and !strlen($value)) {
      return;
    } elseif($validation === 'currency' and !preg_match('/[0-9]{3}/', $value)) {
      return;
    } elseif($validation === 'price' and !is_numeric(str_replace(['.', ','], '', $value))) {
      return;
    } elseif($validation === 'bool' and !is_bool($value)) {
      return;
    }

    if(substr($name, 0, 4) === 'item') {
      $this->fields['items'][$name] = $value;
    } else {
      $this->fields[$name] = is_bool($value) ? var_export($value, true) : $value;
    }
  }

  function __get($name) {
    return array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
  }

}
