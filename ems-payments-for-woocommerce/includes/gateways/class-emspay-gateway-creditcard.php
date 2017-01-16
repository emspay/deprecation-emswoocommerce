<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Creditcard.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Creditcard extends Emspay_Gateway {
  // TODO fix this hardcoded
	protected $payment_method = 'M';

	protected function define_variables() {
		$this->id                 = 'ems_creditcard';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Creditcard', 'emspay' );
		$this->method_description = __( 'Creditcard description.', 'emspay' );
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Creditcard', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Creditcard', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Creditcard.', 'emspay' );
	}


}
