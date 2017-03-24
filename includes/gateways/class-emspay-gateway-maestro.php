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


	protected function define_variables() {
		$this->id                 = 'ems_maestro';
		$this->has_fields         = true;
		$this->method_title       = __( 'EMS Maestro', 'emspay' );
		$this->method_description = __( 'Maestro description.', 'emspay' );
		$this->icon               = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/maestro.png';
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

  public function validate_fields() {
		if ( empty( $_POST  ) || !isset( $_POST['debit_card'] ) || empty( $_POST['debit_card'] ) ) {
			wc_add_notice( __( 'Choose Debit Card', 'emspay' ), 'error' );
			return false;
		}

		$debit_card = stripslashes( $_POST['debit_card'] );
		if ( !array_key_exists( $debit_card, $this->get_supported_debit_cards() ) ) {
			wc_add_notice( __( 'Invalid Debit Card', 'emspay' ), 'error' );
			return false;
		}

		$this->payment_method = $debit_card;

		return parent::validate_fields();
	}

	public function payment_fields() {
		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		?>
		<select name="debit_card" id="debit_card">
			<option value=""><?php _e('Choose Debit Card', 'emspay') ?></option>
			<?php foreach ( $this->get_supported_debit_cards() as $option_key => $option_value ): ?>
				<option<?php selected( $this->payment_method, $option_key ); ?> value="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $option_value ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php
	}


	public function get_supported_debit_cards() {
		return array(
			'MA'         => __( 'Maestro', 'emspay' ),
			'maestroUK'  => __( 'Maestro UK', 'emspay' ),
		);
	}

}
