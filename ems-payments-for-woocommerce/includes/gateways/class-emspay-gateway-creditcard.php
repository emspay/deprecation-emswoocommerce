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

	protected $authenticate_transaction = false;

	protected function define_variables() {
		$this->id                 = 'ems_creditcard';
		$this->has_fields         = true;
		$this->method_title       = __( 'EMS Creditcard', 'emspay' );
		$this->method_description = __( 'Creditcard description.', 'emspay' );
	}


	public function load_options() {
		parent::load_options();

		$this->authenticate_transaction = 'yes' === $this->get_option( '3d_secure', 'no' );
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


	public function get_extra_form_fields() {
		return array(
			'3d_secure' => array(
				'title'       => __( '3D Secure transactions', 'emspay' ),
				'label'       => __( 'Enable Secure transactions <br>(If your credit card agreement includes 3D Secure and your Merchant ID has been activated to use this service.)', 'emspay' ),
				'type'        => 'checkbox',
				'description' => __( 'The ability to authenticate transactions using Verified by Visa, MasterCard SecureCode.', 'emspay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
		);
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


	public function get_cc_brands_icon() {
		return array(
			'M' => plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/mastercard.png',
			'V' => plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/visa.png',
			'C' => plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/dinersclub.png',
		);
	}


	public function hosted_payment_args( $args, $order ) {
		if ( !$this->authenticate_transaction ) {
			$args['authenticateTransaction'] = $this->authenticate_transaction;
		}

		return $args;
	}


	/**
	 * Get gateway icon.
	 * @return string
	 */
	public function get_icon() {
		if ( ! $this->integration->show_icon ) {
			return apply_filters( 'woocommerce_gateway_icon', '', $this->id );
		}

		$cc_brands = $this->get_supported_cc_brands();
		$icons = $this->get_cc_brands_icon();
		$icon_html = '';

		foreach ( $icons as $brand => $icon_url ) {
			$icon_html .= '<img src="' .  WC_HTTPS::force_https_url( $icon_url ) . '" alt="' . esc_attr__( $cc_brands[ $brand ] ) . '" />';
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
	}


}
