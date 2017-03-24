<?php
namespace EmsCore\Test;
use PHPUnit\Framework\TestCase;
require 'config.php';

class ResponseTest extends TestCase {
  protected $options;
  protected $order;
  protected $post;
  protected $postBad1;
  protected $postBad2;

  public function __construct() {
    $this->options = new \EmsCore\Options();
    $this->options
      ->setStoreName('teststore')
      ->setSharedSecret('testsecret')
      ->setEnvironment(\EmsCore\Options::ENV_INTEGRATION)
      ->setCheckoutOption(\EmsCore\Options::CHECKOUT_COMBINEDPAGE)
      ->setPayMode(\EmsCore\Options::PAYMODE_PAYPLUS)
      ->setFailUrl('http://test.com/fail')
      ->setSuccessUrl('http://test.com/success')
      ->setIpnUrl('http://test.com/ipn');

    $this->order = new \EmsCore\Order();
    $this->order->transactionType = \EmsCore\Order::TXNTYPE_SALE;
    $this->order->timezone = 'Europe/London';
    $this->order->transactionTime = '2017:02:02-13:43:57';
    $this->order->chargetotal = '9.32';
    $this->order->currency = '978';
    $this->order->language = 'en_GB';
    $this->order->mobile = false;
    $this->order->orderId = '175';
    $this->order->paymentMethod = 'V';

    $this->post = array(
      'approval_code' => 'Y:200448:3845992018:PPXP:000044',
      'timezone' => 'Europe/London',
      'response_hash' => '40406ef641e3553737d30627253c1fa990dc5a9c23035d3441be30c6e2380e76',
      'oid' => "175",
      'hash_algorithm' => "SHA256",
      'paymentMethod' => 'V',
      'chargetotal' => '9.32',
      'status' => 'APPROVED',
      'currency' => '978'
    );

    $this->postBad1 = array(
      'approval_code' => 'Y:200448:3845992018:PPXP:000044',
      'timezone' => 'Europe/London',
      'oid' => "175",
      'hash_algorithm' => "SHA256",
      'paymentMethod' => 'V',
      'chargetotal' => '9.32',
      'status' => 'APPROVED',
      'currency' => '978'
    );

    $this->postBad2 = array(
      'approval_code' => 'Y:200448:3845992018:PPXP:000044',
      'timezone' => 'Europe/London',
      'response_hash' => '40406ef641e3553737d30627253c1fa990dc5a9c23035d3441be30c6e2380e77',
      'oid' => "175",
      'hash_algorithm' => "SHA256",
      'paymentMethod' => 'V',
      'chargetotal' => '9.32',
      'status' => 'APPROVED',
      'currency' => '978'
    );
  }

  public function testValidateResponseIncorrectArray() {
    $response = new \EmsCore\Response($this->options);
    $answer = $response->validate('');
    $this->assertEquals('Invalid POST data received in \EmsCore\Response->validate() call', $response->getError());
    $this->assertEquals(false, $answer);
    $answer = $response->validate(array());
    $this->assertEquals('Invalid POST data received in \EmsCore\Response->validate() call', $response->getError());
    $this->assertEquals(false, $answer);
    $answer = $response->validate(array('oid' => 1));
    $this->assertEquals('"approval_code" is missing from POST data received in \EmsCore\Response->validate() call', $response->getError());
    $answer = $response->validate($this->postBad1);
    $this->assertEquals('"response_hash" and "notification_hash" fields are missing from POST data received in \EmsCore\Response->validate() call', $response->getError());
    $this->assertEquals(false, $answer);
  }

  public function testValidateResponseCorrectArray() {
    $response = new \EmsCore\Response($this->options);
    $answer = $response->validate($this->post);
    $this->assertEquals('', $response->getError());
    $this->assertEquals(true, $answer);
    $orderId = $response->getOrderId();
    $this->assertEquals($this->post['oid'], $orderId);
  }

  public function testIsNotification() {
    $response = new \EmsCore\Response($this->options);
    $answer = $response->isNotification();
    $this->assertEquals(null, $answer);
    $response->validate($this->post);
    $answer = $response->isNotification();
    $this->assertEquals(false, $answer);
  }

  public function testValidateTransaction() {
    $response = new \EmsCore\Response($this->options);
    $response->validate($this->post);
    $answer = $response->validateTransaction($this->order);
    $this->assertEquals(true, $answer);
    $this->assertEquals('', $response->getError());
  }

  public function testValidateTransactionIncorrect() {
    $response = new \EmsCore\Response($this->options);
    $response->validate($this->postBad2);
    $answer = $response->validateTransaction($this->order);
    $this->assertEquals(false, $answer);
    $this->assertEquals('"response_hash" validation failed in \EmsCore\Response->validateTransaction() call', $response->getError());
  }
}
