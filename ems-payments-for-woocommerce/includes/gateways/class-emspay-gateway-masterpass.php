<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Masterpass.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Masterpass extends Emspay_Gateway {

	protected $payment_method = 'masterpass';

	protected function define_variables() {
		$this->id                 = 'ems_masterpass';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Masterpass', 'emspay' );
		$this->method_description = __( 'Masterpass description.', 'emspay' );
		$this->icon               = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/masterpass.png';
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Masterpass', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Masterpass', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Masterpass.', 'emspay' );
	}


}
