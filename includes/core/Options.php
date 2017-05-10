<?php
namespace EmsCore;

class Options {
  const ENV_INTEGRATION = 'integration';
  const ENV_PRODUCTION = 'production';
  const ENVIRONMENTS = array(
    Options::ENV_INTEGRATION,
    Options::ENV_PRODUCTION
  );

  const CHECKOUT_CLASSIC = 'classic';
  const CHECKOUT_COMBINEDPAGE = 'combinedpage';
  const CHECKOUT_OPTIONS = array(
    Options::CHECKOUT_CLASSIC,
    Options::CHECKOUT_COMBINEDPAGE
  );

  const PAYMODE_PAYONLY = 'payonly';
  const PAYMODE_PAYPLUS = 'payplus';
  const PAYMODE_FULLPAY = 'fullpay';
  const PAY_MODES = array(
    Options::PAYMODE_PAYONLY,
    Options::PAYMODE_PAYPLUS,
    Options::PAYMODE_FULLPAY
  );

  private $fields = array(
    'storename' => '',
    'sharedsecret' => '',
    'environment' => Options::ENV_INTEGRATION,
    'checkoutOption' => Options::CHECKOUT_CLASSIC,
    'paymode' => Options::PAYMODE_PAYONLY,
    'failUrl' => '',
    'successUrl' => '',
    'ipnUrl' => ''
  );

  function setStoreName($name) {
    $this->fields['storename'] = $name;
    return $this;
  }

  function setSharedSecret($secret) {
    $this->fields['sharedsecret'] = $secret;
    return $this;
  }

  function setEnvironment($env) {
    if(in_array($env, Options::ENVIRONMENTS)) {
      $this->fields['environment'] = $env;
    }

    return $this;
  }

  function setCheckoutOption($checkout) {
    if(in_array($checkout, Options::CHECKOUT_OPTIONS)) {
      $this->fields['checkoutOption'] = $checkout;
    }

    return $this;
  }

  function setPayMode($mode) {
    if(in_array($mode, Options::PAY_MODES)) {
      $this->fields['paymode'] = $mode;
    }

    return $this;
  }

  function setFailUrl($url) {
    $this->fields['failUrl'] = $url;
    return $this;
  }

  function setSuccessUrl($url) {
    $this->fields['successUrl'] = $url;
    return $this;
  }

  function setIpnUrl($url) {
    $this->fields['ipnUrl'] = $url;
    return $this;
  }

  function getStoreName() {
    return $this->fields['storename'];
  }

  function getSharedSecret() {
    return html_entity_decode($this->fields['sharedsecret']);
  }

  function getEnvironment() {
    return $this->fields['environment'];
  }

  function getCheckoutOption() {
    return $this->fields['checkoutOption'];
  }

  function getPayMode() {
    return $this->fields['paymode'];
  }

  function getFailUrl() {
    return $this->fields['failUrl'];
  }

  function getSuccessUrl() {
    return $this->fields['successUrl'];
  }

  function getIpnUrl() {
    return $this->fields['ipnUrl'];
  }

  function __get($name) {
    return array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
  }

  function __set($name, $value) {
    if(array_key_exists($name, $this->fields)) {
      $this->fields[$name] = $value;
    }
    return $this;
  }
}
