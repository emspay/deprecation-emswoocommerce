<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Sofort.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Sofort extends Emspay_Gateway {

	protected $payment_method = 'sofort';

	protected function define_variables() {
		$this->id                 = 'ems_sofort';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Sofort', 'emspay' );
		$this->method_description = __( 'Sofort description.', 'emspay' );
		$this->icon               = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/sofort.png';
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Sofort', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Sofort', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Sofort.', 'emspay' );
	}


}
