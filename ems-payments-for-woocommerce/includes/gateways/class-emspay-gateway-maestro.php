<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Maestro.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Maestro extends Emspay_Gateway {

	protected $payment_method = 'MA'; // maestroUK

	protected function define_variables() {
		$this->id                 = 'ems_maestro';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Maestro', 'emspay' );
		$this->method_description = __( 'Maestro description.', 'emspay' );
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Maestro', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Maestro', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Maestro.', 'emspay' );
	}


}
