<?php
namespace EmsCore;

class Response {
  private $order;
  private $options;
  private $post = array();

  private static $requiredFields = array(
    'approval_code',
    'oid',
    'status',
    'hash_algorithm',
    'paymentMethod',
    'currency',
    'chargetotal'
  );

  private $error = '';

  function __construct(Options $options) {
    $this->options = $options;
  }

  function validate($post) {
    $this->error = '';
    if(!is_array($post) or !count($post)) {
      $this->error = 'Invalid POST data received in \EmsCore\Response->validate() call';
      return;
    }
    $this->post = $post;

    foreach (Response::$requiredFields as $name) {
      if(!isset($this->post[$name])) {
        $this->error = '"' . $name . '" is missing from POST data received in \EmsCore\Response->validate() call';
        return false;
      }
    }

    if($this->get('response_hash') === null and $this->get('notification_hash') === null) {
      $this->error = '"response_hash" and "notification_hash" fields are missing from POST data received in \EmsCore\Response->validate() call';
      return false;
    }

    return true;
  }

  function getOrderId() {
    if($this->get('oid') !== null) {
      return $this->get('oid');
    }
    return null;
  }

  function validateTransaction(Order $order) {
    $this->order = $order;

    // Check hash
    if($this->isNotification() and !$this->validateNotificationHash()) {
      $this->error = '"notification_hash" validation failed in \EmsCore\Response->validateTransaction() call';
      return false;
    } elseif(!$this->isNotification() and !$this->validateResponseHash()) {
      $this->error = '"response_hash" validation failed in \EmsCore\Response->validateTransaction() call';
      return false;
    }

    // Parse approval code
    $code = strtoupper(substr($this->get('approval_code'), 0, 1));
    switch($code) {
      case 'Y':
        $this->post['status'] = 'APPROVED';
        break;
      case 'N':
        $this->post['status'] = 'DECLINED';
        break;
      case '?':
      $this->post['status'] = 'WAITING';
      break;
    }

    return true;
  }

  function isNotification() {
    return $this->get('notification_hash') !== null ? true : false;
  }

  function getError() {
    return $this->error;
  }

  function __get($name) {
    return $this->get($name);
  }

  private function get($name) {
    return array_key_exists($name, $this->post) ? $this->post[$name] : null;
  }

  private function validateResponseHash() {
    return $this->get('response_hash') === $this->getResponseHash();
  }

  private function validateNotificationHash() {
    return $this->get('notification_hash') === $this->getNotificationHash();
  }

  private function getResponseHash() {
    return hash(
      $this->order->hashAlgorythm,
      bin2hex(
        $this->options->getSharedSecret() .
        $this->get('approval_code') .
        $this->get('chargetotal') .
        $this->get('currency') .
        $this->order->transactionTime .
        $this->options->getStoreName()
      )
    );
  }

  private function getNotificationHash() {
    return hash(
      $this->order->hashAlgorythm,
      bin2hex(
        $this->get('chargetotal') .
        $this->options->getSharedSecret() .
        $this->get('currency') .
        $this->order->transactionTime .
        $this->options->getStoreName() .
        $this->get('approval_code')
      )
    );
  }
}
