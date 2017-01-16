<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway.
 *
 * @package	Ems_Payments_For_WooCommerce
 * @extends	WC_Payment_Gateway
 * @category Class
 * @author	 DLWT
 * @version	1.0.0
 */
abstract class Emspay_Gateway extends WC_Payment_Gateway {

	protected $supported_languages = array(
		'zh_CN', // Chinese (simplified)
		'zh_TW', // Chinese (traditional)
		'cs_CZ', // Czech
		'nl_NL', // Dutch
		'en_US', // English (USA)
		'en_GB', // English (UK)
		'fi_FI', // Finnish
		'fr_FR', // French
		'de_DE', // German
		'el_GR', // Greek
		'it_IT', // Italian
		'pl_PL', // Polish
		'pt_BR', // Portuguese (Brazil)
		'sk_SK', // Slovak
		'es_ES', // Spanish
	);

	protected $default_language = 'en_US';

	protected $payment_method;

	protected $core_option;

	protected $core_order;

	/**
	 * Init and hook in the integration.
	 *
	 * @since	1.0.0
	 * @return Emspay_Gateway
	 */
	public function __construct() {

		$this->define_variables();

		$this->init_form_fields();
		$this->init_settings();

		$this->load_options();

		$this->init_hook();

		$this->init_gateway();
	}

	abstract protected function define_variables();

	abstract protected function get_enabled_field_label();

	abstract protected function get_title_field_default();

	abstract protected function get_description_field_default();

	public function load_options() {
		// Define user set variables.
		$this->enabled     = $this->get_option( 'enabled', 'yes' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
	}


	protected function init_hook() {
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		//add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_api_emspay_gateway', array( 'Emspay_Gateway_Response', 'response_handler' ) );
	}


	protected function init_gateway() {
		$this->core_options = new EmsCore\Options();
		$this->core_order = new EmsCore\Order();

		$this->set_core_options();
	}


	protected function set_core_options() {
		$integration = emspay_gateway()->get_integration();
		$url = WC()->api_request_url( 'Emspay_Gateway' );
		$url = 'http://mocsok.dyndns.org/?wc-api=Emspay_Gateway';

		$this->core_options
			->setStoreName($integration->storename)
			->setSharedSecret($integration->sharedsecret)
			->setEnvironment($integration->environment)
			->setCheckoutOption($integration->checkoutoption)
			->setPayMode($integration->mode)
			->setFailUrl($url)
			->setSuccessUrl($url)
			->setIpnUrl($url);
	}


	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'   => array(
				'title'   => __( 'Enable/Disable', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => $this->get_enabled_field_label(),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'emspay' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'emspay' ),
				'default'     => $this->get_title_field_default(),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'emspay' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => __( 'This controls the description which the user sees during checkout.', 'emspay' ),
				'default'     => $this->get_description_field_default()
			),
		);
	}


	public function get_emspay_language() {
		$locale = get_locale();

		if ( ! in_array( $locale, $this->supported_languages ) ) {
			return $this->default_language;
		}

		return $locale;
	}

	// TODO do we need to show something special here ?
	public function thankyou_page() {

	}


	/**
	 * Process the payment.
	 *
	 * @param int $order_id
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		// Payment form is hosted on EMS
		return $this->process_hosted_payment( $order );
	}


	/**
	 * Process standard payments.
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	protected function process_hosted_payment( $order ) {
		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);
	}


	/**
	 * Receipt page.
	 *
	 * @param  int $order_id
	 */
	public function receipt_page( $order_id ) {
		$order = wc_get_order( $order_id );

		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with your selected method.', 'emspay' ) . '</p>';

		$args = $this->get_hosted_payment_args( $order );
		foreach ( $args as $field => $value ) {
			$this->core_order->{$field} = $value;
		}

		// Initialize payment
		$hosted_payment = new EmsCore\Request( $this->core_order, $this->core_options );

?>
		<form method="post" action="<?php echo $hosted_payment->getFormAction(); ?>">
		<?php foreach( $hosted_payment->getFormFields() as $name => $value ) { ?>
			<input type="hidden" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php } ?>
			<input type="submit" class="button" value="<?php esc_attr_e( 'Payment', 'emspay' ); ?>" />
		</form>

<?php
	}

	protected function get_hosted_payment_args( $order ) {
		$currency_code = $order->get_order_currency();
		$numeric_currency_code = Emspay_Currency::get_numeric_currency_code( $currency_code );
		$transaction_time = EmsCore\Order::getDateTime();

		// Store meta data to order.
		update_post_meta( $order->id, '_ems_txndatetime', $transaction_time );
		update_post_meta( $order->id, '_ems_currency_code', $numeric_currency_code );
		update_post_meta( $order->id, '_ems_payment_method', $this->payment_method );

		$args = apply_filters( 'woocommerce_emspay_hosted_args', array(
			'mobile'          => wp_is_mobile(),
			'chargetotal'     => $order->get_total(),
			'orderId'         => $order->id,
			'language'        => $this->get_emspay_language(),
			'paymentMethod'   => $this->payment_method,
			'currency'        => $numeric_currency_code,
			'timezone'        => wc_timezone_string(),
			'transactionTime' => $transaction_time,
		), $order->id );

		return $args;
	}

}
