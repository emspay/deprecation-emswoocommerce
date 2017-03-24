<?php
namespace EmsCore\Test;
use PHPUnit\Framework\TestCase;
require 'config.php';

class RequestTest extends TestCase {
  protected $options;
  protected $order;

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
  }

  public function testRequest() {
    $request = new \EmsCore\Request($this->order, $this->options);
    $this->assertEquals(\EmsCore\Request::URL_INTEGRATION, $request->getFormAction());
    $this->assertEquals(\EmsCore\Request::COMMON_NAME_INTEGRATION, $request->getCommonName());
    $this->assertGreaterThan(0, count($request->getFormFields()));
    $this->assertEquals(64, strlen($request->getFormFields()['hash']));
  }

  public function testGetExampleRequest() {
    $example = \EmsCore\Request::getExampleRequest($this->options);
    $this->assertInstanceOf(\EmsCore\Request::class, $example);
  }

  public function testCheckCredentials() {
    $response = \EmsCore\Request::checkCredentials($this->options);
    $this->assertEquals(false, $response);
  }
}
