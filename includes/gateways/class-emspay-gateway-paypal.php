<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Paypal.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Paypal extends Emspay_Gateway {

	protected $payment_method = 'paypal';

	protected $supported_currencies = array(
		'AUD', // Australian Dollar (036)
		'BRL', // Brazilian Real (986)
		'CAD', // Canadian dollar (124)
		'CZK', // Czech koruna (203)
		'DKK', // Danish krone (208)
		'EUR', // Euro (978)
		'HKD', // Hong Kong dollar (344)
		'HUF', // Hungarian forint (348)
		'ISL', // Israeli new shekel (376)
		'JPY', // Japanese yen (392)
		'MXN', // Mexican peso (484)
		'NOK', // Norwegian krone (578)
		'NZD', // New Zealand dollar (554)
		'PLN', // Polish zloty (985)
		'GBP', // Pound sterling (826)
		'SGD', // Singapore dollar (702)
		'SEK', // Swedish krona (752)
		'CHF', // Swiss franc (756)
		'USD', // United States dollar (840)
	);

	protected function define_variables() {
		$this->id                 = 'ems_paypal';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Paypal', 'emspay' );
		$this->method_description = __( 'Paypal description.', 'emspay' );
		$this->icon               = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/paypal.png';
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Paypal', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Paypal', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Paypal.', 'emspay' );
	}


	protected function is_currency_supported( $currency ) {
		return in_array( $currency, $this->supported_currencies );
	}


}
