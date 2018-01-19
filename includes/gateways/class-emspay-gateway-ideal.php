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

	protected $issuer_bank;

	public $select_bank = false;

	public $select_text = '';

  protected $supported_currencies = array(
		'EUR', // Euro (978)
	);

	protected function define_variables() {
		$this->id                 = 'ems_ideal';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS iDEAL', 'emspay' );
		$this->method_description = __( 'iDEAL description.', 'emspay' );
		$this->icon               = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/ideal.png';
	}


	public function load_options() {
		parent::load_options();

		$this->select_bank = 'yes' === $this->get_option( 'select_bank', 'no' );
		$this->select_text = $this->get_option( 'select_text', 'Choose your bank' );
	}


	protected function get_enabled_field_label() {
		return __( 'Enable iDEAL', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'iDEAL', 'emspay' );
	}


	public function get_extra_form_fields() {
		return array(
			'select_bank'   => array(
				'title'   => __( 'Show issuer bank select', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Let your customers select the issuer bank', 'emspay' ),
				'default' => 'no'
			),
			'select_text'   => array(
				'title'   => __( 'Text for first option in issuer select', 'emspay' ),
				'type'    => 'text',
				'default' => 'Choose your bank'
			),
		);
	}


	protected function get_description_field_default() {
		return __( 'Paying online with iDEAL.', 'emspay' );
	}


	public function validate_fields() {
		if ( $this->select_bank ) {
			if ( empty( $_POST  ) || !isset( $_POST['issuer_bank'] ) || empty( $_POST['issuer_bank'] ) ) {
				wc_add_notice( __( 'Choose your bank', 'emspay' ), 'error' );
				return false;
			}

			$bank = stripslashes( $_POST['issuer_bank'] );
			if ( !array_key_exists( $bank, $this->get_issuer_banks() ) ) {
				wc_add_notice( __( 'Invalid bank', 'emspay' ), 'error' );
				return false;
			}

			$this->issuer_bank = $bank;
		}

		return parent::validate_fields();
	}


	public function payment_fields() {
		if ( !$this->select_bank ) {
			parent::payment_fields();
			return;
		}

		if ( !$this->select_text ) {
			parent::payment_fields();
			return;
		}

		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}

		?>
		<select name="issuer_bank" id="issuer_bank">
			<option value=""><?php _e( $this->select_text, 'emspay' ) ?></option>
			<?php foreach ( $this->get_issuer_banks() as $option_key => $option_value ): ?>
				<option<?php selected( $this->issuer_bank, $option_key ); ?> value="<?php echo esc_attr( $option_key ); ?>"><?php echo esc_html( $option_value ); ?></option>
			<?php endforeach; ?>
		</select>

		<?php
	}


	public function get_issuer_banks() {
		return array(
			'ABNANL2A' => __( 'ABN AMRO', 'emspay' ),
			'ASNBNL21' => __( 'ASN Bank', 'emspay' ),
			'BUNQNL2A' => __( 'Bunq', 'emspay' ),
			'INGBNL2A' => __( 'ING', 'emspay' ),
			'KNABNL2H' => __( 'Knab', 'emspay' ),
			'RABONL2U' => __( 'Rabobank', 'emspay' ),
			'RBRBNL21' => __( 'RegioBank', 'emspay' ),
			'SNSBNL2A' => __( 'SNS Bank', 'emspay' ),
			'TRIONL2U' => __( 'Triodos Bank', 'emspay' ),
			'FVLBNL22' => __( 'van Lanschot', 'emspay' ),
		);
	}


	public function hosted_payment_args( $args, $order ) {
		if ( $this->select_bank ) {
			$args['idealIssuerID'] = $order->ems_idealIssuerID;
		}

		return $args;
	}


	protected function get_emspay_meta( $order ) {
		$meta = parent::get_emspay_meta( $order );
		if ($this->select_bank) {
			$meta['_ems_idealIssuerID'] = $this->issuer_bank;
		}

		return $meta;
	}


	protected function is_currency_supported( $currency ) {
		return in_array( $currency, $this->supported_currencies );
	}


	/**
	 * Process standard payments.
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	protected function process_hosted_payment($order)
	{
		$query_args = build_query( array( 'order_id' => $order->get_id() ) );

		return array(
			'order_id' => $order->get_id(),
			'result' => 'success',
			'redirect' => wc_get_checkout_url() . '?' . $query_args
		);
	}


	/**
	 * Init hook.
	 */
	protected function init_hook()
	{
		parent::init_hook();
		add_filter( 'woocommerce_after_checkout_form', array( $this, 'redirect_to_ems' ));
	}


	/**
	 * Redirect to EMS.
	 */
	public function redirect_to_ems() {

		if ( ! empty( $_GET['order_id'] ) ) {
			$order = wc_get_order( $_GET['order_id'] );

			$args = $this->get_hosted_payment_args( $order );
			foreach ( $args as $field => $value ) {
				$this->core_order->{$field} = $value;
			}

			// Initialize payment
			$hosted_payment = new EmsCore\Request( $this->core_order, $this->core_options );
			$form_fields    = $hosted_payment->getFormFields();

			self::log( 'Payment form fields for Order #' . $_GET['order_id'] . ' ' . print_r( $form_fields, TRUE ) );
			?>
			<form id="payForm" method="post"
				action="<?php echo $hosted_payment->getFormAction(); ?>">
				<?php foreach ( $form_fields as $name => $value ) { ?>
					<input type="hidden" name="<?php echo $name; ?>"
						value="<?php echo esc_attr( $value ); ?>">
				<?php } ?>
			</form>
			<script>
              jQuery(document).ready(function () {
                jQuery('#payForm').submit();
              });
			</script>
			<?php
		}

	}

}
