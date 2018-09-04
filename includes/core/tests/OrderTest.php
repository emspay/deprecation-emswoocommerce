<?php
namespace EmsCore\Test;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase {
  public function testEmptyOrder() {
    $order = new \EmsCore\Order();
    $this->assertEquals('', $order->orderId);
    $this->assertEquals(\EmsCore\Order::TXNTYPE_SALE, $order->transactionType);
    $this->assertEquals(\EmsCore\Order::HASH_ALGORITHM_SHA256, $order->hashAlgorithm);
    $this->assertEquals(array(), $order->items);
  }

  public function testInvalidProperty() {
    $order = new \EmsCore\Order();
    $this->assertEquals(null, $order->invalid);
  }

  public function testTransactionTime() {
    $order = new \EmsCore\Order();
    $value = '2016:12:01-01:01:01';
    $order->transactionTime = $value;
    $this->assertEquals($value, $order->transactionTime);
  }

  public function testValidCurrency() {
    $order = new \EmsCore\Order();
    $value = '987';
    $order->currency = $value;
    $this->assertEquals($value, $order->currency);
  }

  public function testInvalidCurrency() {
    $order = new \EmsCore\Order();
    $value = 'EUR';
    $order->currency = $value;
    $this->assertEquals(null, $order->currency);
  }

  public function testValidPrice() {
    $order = new \EmsCore\Order();
    $value = '12.34';
    $order->chargetotal = $value;
    $this->assertEquals($value, $order->chargetotal);
  }

  public function testInvalidPrice() {
    $order = new \EmsCore\Order();
    $value = '12;34';
    $order->chargetotal = $value;
    $this->assertEquals(null, $order->chargetotal);
  }

  public function testValidBool() {
    $order = new \EmsCore\Order();
    $value = true;
    $order->authenticateTransaction = $value;
    $this->assertEquals('true', $order->authenticateTransaction);
  }

  public function testInvalidBool() {
    $order = new \EmsCore\Order();
    $value = 'abc';
    $order->authenticateTransaction = $value;
    $this->assertEquals(null, $order->authenticateTransaction);
  }

  public function testValidNotEmpty() {
    $order = new \EmsCore\Order();
    $value = '123';
    $order->orderId = $value;
    $this->assertEquals($value, $order->orderId);
  }

  public function testInvalidNotEmpty() {
    $order = new \EmsCore\Order();
    $value = '';
    $order->orderId = $value;
    $this->assertEquals(null, $order->orderId);
  }
}
