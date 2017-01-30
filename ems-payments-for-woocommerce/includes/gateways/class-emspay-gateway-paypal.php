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


}
