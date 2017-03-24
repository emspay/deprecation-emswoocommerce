<?php
namespace EmsCore\Test;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase {
  public function testEmptyOption() {
    $options = new \EmsCore\Options();
    $this->assertEquals('', $options->getStoreName());
    $this->assertEquals('', $options->getSharedSecret());
    $this->assertEquals(\EmsCore\Options::ENV_INTEGRATION, $options->getEnvironment());
    $this->assertEquals(\EmsCore\Options::CHECKOUT_CLASSIC, $options->getCheckoutOption());
    $this->assertEquals(\EmsCore\Options::PAYMODE_PAYONLY, $options->getPayMode());
    $this->assertEquals('', $options->getFailUrl());
    $this->assertEquals('', $options->getSuccessUrl());
    $this->assertEquals('', $options->getIpnUrl());
  }

  public function testInvalidProperty() {
    $options = new \EmsCore\Options();
    $this->assertEquals(null, $options->invalid);
  }

  public function testSetStoreName() {
    $options = new \EmsCore\Options();
    $value = 'abc';
    $options->setStoreName($value);
    $this->assertEquals($value, $options->getStoreName());
  }

  public function testSetSharedSecret() {
    $options = new \EmsCore\Options();
    $value = 'abc';
    $options->setSharedSecret($value);
    $this->assertEquals($value, $options->getSharedSecret());
  }

  public function testSetEnvironment() {
    $options = new \EmsCore\Options();
    $value = \EmsCore\Options::ENV_PRODUCTION;
    $options->setEnvironment($value);
    $this->assertEquals($value, $options->getEnvironment());
  }

  public function testSetCheckout() {
    $options = new \EmsCore\Options();
    $value = \EmsCore\Options::CHECKOUT_COMBINEDPAGE;
    $options->setCheckoutOption($value);
    $this->assertEquals($value, $options->getCheckoutOption());
  }

  public function testSetPaymode() {
    $options = new \EmsCore\Options();
    $value = \EmsCore\Options::PAYMODE_FULLPAY;
    $options->setPayMode($value);
    $this->assertEquals($value, $options->getPayMode());
  }

  public function testSetFailUrl() {
    $options = new \EmsCore\Options();
    $value = 'abc';
    $options->setFailUrl($value);
    $this->assertEquals($value, $options->getFailUrl());
  }

  public function testSetSuccessUrl() {
    $options = new \EmsCore\Options();
    $value = 'abc';
    $options->setSuccessUrl($value);
    $this->assertEquals($value, $options->getSuccessUrl());
  }

  public function testSetIpnUrl() {
    $options = new \EmsCore\Options();
    $value = 'abc';
    $options->setIpnUrl($value);
    $this->assertEquals($value, $options->getIpnUrl());
  }
}
