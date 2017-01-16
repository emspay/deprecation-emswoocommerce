<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Klarna.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Klarna extends Emspay_Gateway {

	protected $payment_method = 'klarna';

	protected function define_variables() {
		$this->id                 = 'ems_klarna';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Klarna', 'emspay' );
		$this->method_description = __( 'Klarna description.', 'emspay' );
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Klarna', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Klarna', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Klarna.', 'emspay' );
	}


}
