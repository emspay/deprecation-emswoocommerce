<?php
include './index.php';

// Create EMS options
$options = new EmsCore\Options();
$options
  ->setStoreName('230995037')
  ->setSharedSecret('CwvrpQ94[+')
  ->setEnvironment('integration')
  ->setCheckoutOption('classic')
  ->setPayMode('payonly')
  ->setFailUrl('http://test.com/fail')
  ->setSuccessUrl('http://test.com/success')
  ->setIpnUrl('http://test.com/ipn');

// Create EMS order
$order = new EmsCore\Order();
$order->transactionType = EmsCore\Order::TXNTYPE_SALE;
$order->timezone = 'Europe/London';
$order->transactionTime = '2017:01:17-20:26:40';
$order->chargetotal = '120.00';
$order->currency = '978';
$order->language = 'en_GB';
$order->mobile = false;
$order->orderId = '66';
$order->paymentMethod = EmsCore\Order::PAYMENT_BANCONTACT;
$order->shipping = '1.0';
$order->vattax = '1.0';
$order->subtotal = '1.0';
$order->idealIssuerID = EmsCore\Order::IDEAL_ABN_AMRO;

// Request
$request = new EmsCore\Request($order, $options);
echo "Form action: " . $request->getFormAction() . "\n";
foreach($request->getFormFields() as $name => $value) {
  echo "$name = $value\n";
}

$post = array(
  'approval_code' => 'Y:000000:3845893913:PPX :6D796182EE023160D',
  'timezone' => 'Europe/London',
  'notification_hash' => 'c9ff24cd91304ad375bf7f519c0e435c825709c6f393a1d20e04eee941891eaa',
  'oid' => "66",
  'hash_algorithm' => "SHA256",
  'paymentMethod' => 'paypal',
  'chargetotal' => '122.00',
  'status' => 'APPROVED',
  'currency' => '978'
);

// <input value="N:-30031:No terminal setup" name="approval_code" type="hidden">
// 		<input value="12/01/17 16:14:21" name="txndate_processed" type="hidden">
// 		<input value="378282" name="ccbin" type="hidden">
// 		<input value="Europe/London" name="timezone" type="hidden">
// 		<input value="2020" name="expyear" type="hidden">
// 		<input value="9bd889a57213b14640c8ce128ece8891e981652c8396d3a0d34733bb54403407" name="response_hash" type="hidden">
// 		<input value="30031" name="fail_rc" type="hidden">
// 		<input value="19" name="oid" type="hidden">
// 		<input value="http://mocsok.dyndns.org/?wc-api=Emspay_Gateway" name="transactionNotificationURL" type="hidden">
// 		<input value="1484234061" name="tdate" type="hidden">
// 		<input value="USA" name="cccountry" type="hidden">
// 		<input value="Gabor Test" name="bname" type="hidden">
// 		<input value="AMEX" name="ccbrand" type="hidden">
// 		<input value="05" name="expmonth" type="hidden">
// 		<input value="SHA256" name="hash_algorithm" type="hidden">
// 		<input value="sale" name="txntype" type="hidden">
// 		<input value="A" name="paymentMethod" type="hidden">
// 		<input value="978" name="currency" type="hidden">
// 		<input value="2017:01:12-15:13:49" name="txndatetime" type="hidden">
// 		<input value="(AMEX) ... 0005" name="cardnumber" type="hidden">
// 		<input value="83845851475" name="ipgTransactionId" type="hidden">
// 		<input value="No terminal available" name="fail_reason" type="hidden">
// 		<input value="381.00" name="chargetotal" type="hidden">
// 		<input value="FAILED" name="status" type="hidden">

// Response
$response = new EmsCore\Response($options);
echo 'Pre validate: ' . var_export($response->validate($post), true) . "\n";
echo 'Order ID: ' . $response->getOrderId() . "\n";
echo 'Validate: ' . var_export($response->validateTransaction($order), true) . "\n";
echo 'Notification: ' . var_export($response->isNotification(), true) . "\n";
echo 'Error ' . $response->getError() . "\n";;
