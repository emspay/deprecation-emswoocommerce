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
	protected $mastercard_payment_card = false;
	protected $visa_payment_card = false;
	protected $diners_club_payment_card = false;


	protected function define_variables() {
		$this->id                 = 'ems_creditcard';
		$this->has_fields         = true;
		$this->method_title       = __( 'EMS Creditcard', 'emspay' );
		$this->method_description = __( 'Creditcard description.', 'emspay' );
	}


	public function load_options() {
		parent::load_options();

		$this->authenticate_transaction = 'yes' === $this->get_option( '3d_secure', 'no' );
		$this->txntype_preauth          = 'yes' === $this->get_option( 'txntype_preauth', 'no' );
		$this->mastercard_payment_card  = 'yes' === $this->get_option( 'mastercard_payment_card', 'no' );
		$this->visa_payment_card        = 'yes' === $this->get_option( 'visa_payment_card', 'no' );
		$this->diners_club_payment_card = 'yes' === $this->get_option( 'diners_club_payment_card', 'no' );

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


	public function process_admin_options() {
		$this->init_settings();
		$post_data = $this->get_post_data();

		foreach ( $this->get_form_fields() as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				try {
					$this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
				} catch ( Exception $e ) {
					$this->add_error( $e->getMessage() );
				}
			}
		}

		return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
	}


	public function get_extra_form_fields() {
		return array(
			'3d_secure'                => array(
				'title'       => __( '3D Secure transactions', 'emspay' ),
				'label'       => __( 'Enable Secure transactions <br>(If your credit card agreement includes 3D Secure and your Merchant ID has been activated to use this service.)', 'emspay' ),
				'type'        => 'checkbox',
				'description' => __( 'The ability to authenticate transactions using Verified by Visa, MasterCard SecureCode.', 'emspay' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'skip_order_pay_page'      => array(
				'title'   => __( 'Skip order pay page', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => __( 'The payment confirmation page will be skipped, customers will be automatically redirected to the EMS payment gateway', 'emspay' ),
				'default' => 'no',
			),
			'txntype_preauth'          => array(
				'title'   => __( 'txntype Preauth', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable "preauth" txntype. (Default txntype is "Sale")', 'emspay' ),
				'default' => 'no',
			),
			'mastercard_payment_card'  => array(
				'title'   => __( 'MasterCard', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Use MasterCard payment card', 'emspay' ),
				'default' => 'yes',
			),
			'visa_payment_card'        => array(
				'title'   => __( 'Visa', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Use Visa payment card', 'emspay' ),
				'default' => 'yes',
			),
			'diners_club_payment_card' => array(
				'title'   => __( 'Diners Club', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Use Diners Club payment card', 'emspay' ),
				'default' => 'no',
			),
		);
	}


	public function validate_fields() {
		if ( empty( $_POST ) || ! isset( $_POST['ccbrand'] ) || empty( $_POST['ccbrand'] ) ) {
			wc_add_notice( __( 'Choose Credit Card', 'emspay' ), 'error' );

			return false;
		}

		$ccbrand = stripslashes( $_POST['ccbrand'] );

		if ( ! array_key_exists( $ccbrand, $this->get_supported_cc_brands() ) ) {
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
		$supported_cc_brand      = $this->get_supported_cc_brands();
		$supported_cc_brand_val  = key( $this->get_supported_cc_brands() );
		$supported_cc_brand_name = reset( $supported_cc_brand );

		if ( count( $supported_cc_brand ) > 1 ) :?>
			<select name="ccbrand" id="ccbrand">
				<option value=""><?php _e( 'Choose Credit Card', 'emspay' ) ?></option>
				<?php foreach ( $this->get_supported_cc_brands() as $option_key => $option_value ):
					if ( $option_key == "N/A" ) {
						continue;
					} ?>
					<option<?php selected( $this->payment_method, $option_key ); ?>
						value="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $option_value ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $supported_cc_brand_val == "N/A" ): ?>
			<input type="hidden" name="ccbrand" id="ccbrand"
			       value="<?php echo esc_attr( $supported_cc_brand_val ); ?>">
		<?php else: ?>
			<input type="hidden" name="ccbrand" id="ccbrand"
			       value="<?php echo esc_attr( $supported_cc_brand_val ); ?>"><?php echo esc_html( $supported_cc_brand_name ); ?>
		<?php endif;
	}


	public function get_supported_cc_brands() {

		$supported_brands = [];

		if ( $this->mastercard_payment_card ) {
			$supported_brands['M'] = __( 'MasterCard', 'emspay' );
		}

		if ( $this->visa_payment_card ) {
			$supported_brands['V'] = __( 'Visa', 'emspay' );
		}

		if ( $this->diners_club_payment_card ) {
			$supported_brands['C'] = __( 'Diners Club', 'emspay' );
		}

		if ( ! $this->mastercard_payment_card && ! $this->visa_payment_card && ! $this->diners_club_payment_card ) {
			$supported_brands['N/A'] = __( 'Any payment method', 'emspay' );
		}

		return $supported_brands;
	}


	public function get_cc_brands_icon() {

		$brand_icon = [];

		if ( $this->mastercard_payment_card ) {
			$brand_icon['M'] = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/mastercard.png';
		}

		if ( $this->visa_payment_card ) {
			$brand_icon['V'] = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/visa.png';
		}

		if ( $this->diners_club_payment_card ) {
			$brand_icon['C'] = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/dinersclub.png';
		}

		return $brand_icon;
	}


	public function hosted_payment_args( $args, $order ) {
		if ( ! $this->authenticate_transaction ) {
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
		$icons     = $this->get_cc_brands_icon();
		$icon_html = '';

		foreach ( $icons as $brand => $icon_url ) {
			$icon_html .= '<img src="' . WC_HTTPS::force_https_url( $icon_url ) . '" alt="' . esc_attr__( $cc_brands[ $brand ] ) . '" />';
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
	}

}