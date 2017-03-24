<?php
namespace EmsCore;

class Request {
  const URL_INTEGRATION = 'https://test.ipg-online.com/connect/gateway/processing';
  const COMMON_NAME_INTEGRATION = 'test.ipg-online.com';
  const URL_PRODUCTION = 'https://www.ipg-online.com/connect/gateway/processing';
  const COMMON_NAME_PRODUCTION = 'www.ipg-online.com';

  private $order;
  private $options;

  private $formMapping = array(
    'txntype' => array('order', 'transactionType'),
    'timezone' => array('order', 'timezone'),
    'txndatetime' => array('order', 'transactionTime'),
    'hash_algorithm' => array('order', 'hashAlgorythm'),
    'hash' => array('getHash'),
    'storename' => array('options', 'storename'),
    'mode' => array('options', 'paymode'),
    'chargetotal' => array('order', 'chargetotal'),
    'currency' => array('order', 'currency'),
    'checkoutoption' => array('options', 'checkoutOption'),
    'language' => array('order', 'language'),
    'mobileMode' => array('order', 'mobile'),
    'oid' => array('order', 'orderId'),
    'paymentMethod' => array('order', 'paymentMethod'),
    'responseFailURL' => array('options', 'failUrl'),
    'responseSuccessURL' => array('options', 'successUrl'),
    'transactionNotificationURL' => array('options', 'ipnUrl'),
    'idealIssuerID' => array('order', 'idealIssuerID'),
    'shipping' => array('order', 'shipping'),
    'vattax' => array('order', 'vattax'),
    'subtotal' => array('order', 'subtotal'),
    'authenticateTransaction' => array('order', 'authenticateTransaction'),
    'bcompany' => array('order', 'bcompany'),
    'bname' => array('order', 'bname'),
    'baddr1' => array('order', 'baddr1'),
    'baddr2' => array('order', 'baddr2'),
    'bcity' => array('order', 'bcity'),
    'bstate' => array('order', 'bstate'),
    'bcountry' => array('order', 'bcountry'),
    'bzip' => array('order', 'bzip'),
    'phone' => array('order', 'phone'),
    'fax' => array('order', 'fax'),
    'email' => array('order', 'email'),
    'sname' => array('order', 'sname'),
    'saddr1' => array('order', 'saddr1'),
    'saddr2' => array('order', 'saddr2'),
    'scity' => array('order', 'scity'),
    'sstate' => array('order', 'sstate'),
    'scountry' => array('order', 'scountry'),
    'szip' => array('order', 'szip'),
    'klarnaFirstname' => array('order', 'klarnaFirstname'),
    'klarnaLastname' => array('order', 'klarnaLastname'),
    'klarnaStreetName' => array('order', 'klarnaStreetName'),
    'klarnaHouseNumber' => array('order', 'klarnaHouseNumber'),
    'klarnaHouseNumberExtension' => array('order', 'klarnaHouseNumberExtension'),
    'klarnaPhone' => array('order', 'klarnaPhone')
  );
  function __construct(Order $order, Options $options) {
    $this->order = $order;
    $this->options = $options;
  }

  function getFormAction() {
    return $this->options->getEnvironment() === Options::ENV_INTEGRATION
      ? Request::URL_INTEGRATION
      : Request::URL_PRODUCTION;
  }

  function getCommonName() {
    return $this->options->getEnvironment() === Options::ENV_INTEGRATION
      ? Request::COMMON_NAME_INTEGRATION
      : Request::COMMON_NAME_PRODUCTION;
  }

  function getFormFields() {
    $fields = array();
    foreach($this->formMapping as $field => $getter) {
      $value = null;
      if(count($getter) == 1) {
        $value = $this->{$getter[0]}();
      }
      else {
        $value = $this->{$getter[0]}->{$getter[1]};
      }

      if($value !== null) {
        $fields[$field] = $value;

        if($field === 'checkoutoption' and $value === Options::CHECKOUT_COMBINEDPAGE) {
          unset($fields['mode']);
        }
      }
    }

    foreach($this->order->items as $itemName => $itemValue) {
      $fields[$itemName] = $itemValue;
    }

    return $fields;
  }

  static function getExampleRequest(Options $options) {
    $order = new Order();
    $order->transactionType = Order::TXNTYPE_SALE;
    $order->timezone = 'Europe/London';
    $order->transactionTime = date("Y:m:d-H:i:s");
    $order->chargetotal = '2';
    $order->currency = '978';
    $order->language = 'en_GB';
    $order->mobile = false;
    $order->orderId = '1';
    return new Request($order, $options);
  }

  static function checkCredentials(Options $options) {
    if(function_exists('stream_context_create') and function_exists('file_get_contents')) {
      $request = Request::getExampleRequest($options);

      $http = array(
        'http' => array(
          'method' => 'POST',
          'header'  => 'Content-type: application/x-www-form-urlencoded',
          'content' => http_build_query($request->getFormFields())
        ),
        'ssl' => array(
          'verify_peer' => false,
          'cafile' => "./cacert.pem",
          'ciphers' => 'HIGH:TLSv1.2:TLSv1.1:TLSv1.0:!SSLv3:!SSLv2',
          'peer_name' => $request->getCommonName(),
          'disable_compression' => true,
        )
      );
      $context = stream_context_create($http);
      $content = file_get_contents($request->getFormAction(), false, $context);
      return Request::checkContent($content);
    }
    elseif(extension_loaded('curl') && function_exists('curl_init')) {
      $request = Request::getExampleRequest($options);

      $ch = curl_init();
      curl_setopt_array($ch, array(
        CURLOPT_URL => $request->getFormAction(),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_POST => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POSTFIELDS => http_build_query($request->getFormFields())
      ));
      $content = curl_exec($ch);
      return Request::checkContent($content);
    }

    return true;
  }

  static private function checkContent($content) {
    return (strpos($content, 'invalid') === false && strpos($content, 'not permitted') == false);
  }

  private function getHash() {
    return hash(
      $this->order->hashAlgorythm,
      bin2hex(
        $this->options->getStoreName() .
        $this->order->transactionTime .
        $this->order->chargetotal .
        $this->order->currency .
        $this->options->getSharedSecret()
      )
    );
  }
}
