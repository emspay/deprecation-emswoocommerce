<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Bancontact.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Bancontact extends Emspay_Gateway {

	protected $payment_method = 'BCMC';

	protected function define_variables() {
		$this->id                 = 'ems_bancontact';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Bancontact', 'emspay' );
		$this->method_description = __( 'Bancontact description.', 'emspay' );
		$this->icon               = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/bancontact.png';
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Bancontact', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Bancontact', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Bancontact.', 'emspay' );
	}


}
