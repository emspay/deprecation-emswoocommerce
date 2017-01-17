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

	protected function define_variables() {
		$this->id                 = 'ems_creditcard';
		$this->has_fields         = true;
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


	public function validate_fields() {
		if ( empty( $_POST  ) || !isset( $_POST['ccbrand'] ) || empty( $_POST['ccbrand'] ) ) {
			wc_add_notice( __( 'Choose Credit Card', 'emspay' ), 'error' );
			return false;
		}

		$ccbrand = stripslashes( $_POST['ccbrand'] );
		if ( !array_key_exists( $ccbrand, $this->get_supported_cc_brands() ) ) {
			wc_add_notice( __( 'Invalid Credit Card brand', 'emspay' ), 'error' );
			return false;
		}

		$this->payment_method = $ccbrand;

		return parent::validate_fields();
	}

	public function payment_fields() {
		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		?>
		<select name="ccbrand" id="ccbrand">
			<option value=""><?php _e('Choose Credit Card', 'emspay') ?></option>
			<?php foreach ( $this->get_supported_cc_brands() as $option_key => $option_value ): ?>
				<option<?php selected( $this->payment_method, $option_key ); ?> value="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $option_value ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php
	}


	public function get_supported_cc_brands() {
		return array(
			'M' => __( 'MasterCard', 'emspay' ),
			'V' => __( 'Visa', 'emspay' ),
			'C' => __( 'Diners Club', 'emspay' ),
		);
	}


}
