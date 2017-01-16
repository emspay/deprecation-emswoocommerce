<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Ideal.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Ideal extends Emspay_Gateway {

	protected $payment_method = 'ideal';

	protected function define_variables() {
		$this->id                 = 'ems_ideal';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS iDEAL', 'emspay' );
		$this->method_description = __( 'iDEAL description.', 'emspay' );
	}


	protected function get_enabled_field_label() {
		return __( 'Enable iDEAL', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'iDEAL', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with iDEAL.', 'emspay' );
	}

}
