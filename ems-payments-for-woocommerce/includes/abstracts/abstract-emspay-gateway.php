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

	/** @var bool Whether or not logging is enabled */
	public static $log_enabled = false;

	/** @var WC_Logger Logger instance */
	public static $log = false;

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

	protected $supported_payment_methods = array(
		'M',          // MasterCard
		'V',          // Visa (Credit/Debit/Electron/Delta)
		'C',          // Diners Club
		'ideal',      // iDEAL
		'klarna',     // Klarna
		'MA',         // Maestro
		'maestroUK',  // Maestro UK
		'masterpass', // MasterPass
		'paypal',     // PayPal
		'sofort',     // SOFORT Banking (Überweisung)
		'BCMC',       // Bancontact
	);

	protected $supported_checkout_options = array(
		'classic',
		'combinedpage',
	);

	protected $core_option;

	protected $core_order;

	protected $integration;

	/**
	 * Init and hook in the integration.
	 *
	 * @since	1.0.0
	 * @return Emspay_Gateway
	 */
	public function __construct() {
		// Get the integration settings
		$this->integration = emspay_gateway()->get_integration();

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
		$this->debug       = 'yes' === $this->get_option( 'debug', 'no' );

		self::$log_enabled = $this->debug;

		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = 'no';
		}
	}


	public function is_valid_for_use() {
		return in_array( $this->integration->checkoutoption, $this->supported_checkout_options );
	}


	public function admin_options() {
		if ( $this->is_valid_for_use() ) {
			parent::admin_options();
		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'emspay' ); ?></strong>: <?php echo sprintf( __( 'Gateway does not support selected checkout option: %s.', 'emspay' ), $this->integration->checkoutoption ); ?></p></div>
			<?php
		}
	}


	protected function init_hook() {
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		//add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_api_emspay_gateway', array( 'Emspay_Gateway_Response', 'response_handler' ) );
		add_filter( 'woocommerce_emspay_' . $this->id . '_hosted_args', array( $this, 'hosted_payment_args' ), 10, 2 );
	}


	protected function init_gateway() {
		$this->core_options = new EmsCore\Options();
		$this->core_order = new EmsCore\Order();

		$this->set_core_options();
	}


	protected function set_core_options() {
		$url = WC()->api_request_url( 'Emspay_Gateway' );

		$this->core_options
			->setStoreName($this->integration->storename)
			->setSharedSecret($this->integration->sharedsecret)
			->setEnvironment($this->integration->environment)
			->setCheckoutOption($this->integration->checkoutoption)
			->setPayMode($this->integration->mode)
			->setFailUrl($url)
			->setSuccessUrl($url)
			->setIpnUrl($url);
	}


	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array_merge(
			array(
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
			'debug' => array(
				'title'       => __( 'Debug Log', 'emspay' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'emspay' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log EMS events, such as pay request, response, inside: <br><code>%s</code>', 'emspay' ), wc_get_log_file_path( 'emspay' ) )
			),
		), $this->get_extra_form_fields() );
	}


	public function get_extra_form_fields() {
		return array();
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

		$this->save_emspay_meta( $order );

		// Payment form is hosted on EMS
		return $this->process_hosted_payment( $order );
	}


	protected function save_emspay_meta( $order ) {
		// Store meta data to order.
		foreach( $this->get_emspay_meta( $order ) as $key => $value ) {
			update_post_meta( $order->id, $key, $value );
		}
	}


	protected function get_emspay_meta( $order ) {
		$currency_code = $order->get_order_currency();
		$numeric_currency_code = Emspay_Currency::get_numeric_currency_code( $currency_code );
		$transaction_time = EmsCore\Order::getDateTime();

		return array(
			'_ems_txndatetime'     => $transaction_time,
			'_ems_currency_code'   => $numeric_currency_code,
			'_ems_payment_method'  => $this->payment_method,
		);
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
		$form_fields = $hosted_payment->getFormFields();

		self::log( 'Payment form fields for Order #' . $order_id . ' ' . print_r( $form_fields, true ) );
?>
		<form method="post" action="<?php echo $hosted_payment->getFormAction(); ?>">
		<?php foreach( $form_fields as $name => $value ) { ?>
			<input type="hidden" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>">
		<?php } ?>
			<input type="submit" class="button" value="<?php esc_attr_e( 'Payment', 'emspay' ); ?>" />
		</form>

<?php
	}


	protected function get_hosted_payment_args( $order ) {
		$args = apply_filters( 'woocommerce_emspay_' . $this->id . '_hosted_args', array_merge(
			array(
				'mobile'          => wp_is_mobile(),
				'chargetotal'     => $order->get_total(),
				'shipping'        => $order->get_total_shipping(),
				'vattax'          => $order->get_total_tax(),
				'subtotal'        => $order->get_subtotal(),
				'orderId'         => $order->id,
				'language'        => $this->get_emspay_language(),
				'paymentMethod'   => $order->ems_payment_method,
				'currency'        => $order->ems_currency_code,
				'timezone'        => wc_timezone_string(),
				'transactionTime' => $order->ems_txndatetime,
			),
			$this->get_billing_args( $order ),
			$this->get_shipping_args( $order )
		), $order );

		return $args;
	}


	protected function get_billing_args( $order ) {
		$billing_args = array();
		if ( in_array( $this->integration->mode, array( 'payplus', 'fullpay' ) ) ) {
			$billing_args['bname']    = $order->get_formatted_billing_full_name();
			$billing_args['bcompany'] = $order->billing_company;
			$billing_args['baddr1']   = $order->billing_address_1;
			$billing_args['baddr2']   = $order->billing_address_2;
			$billing_args['bcity']    = $order->billing_city;
			$billing_args['bstate']   = $this->get_state_name( $order->billing_country, $order->billing_state );
			$billing_args['bcountry'] = $order->billing_country;
			$billing_args['bzip']     = $order->billing_postcode;
			$billing_args['phone']    = $order->billing_phone;
			$billing_args['email']    = $order->billing_email;
		}

		return $billing_args;
	}

	protected function get_shipping_args( $order ) {
		$shipping_args = array();
		if ( $this->integration->mode == 'fullpay' ) {
			$shipping_args['sname']    = $order->get_formatted_shipping_full_name();
			$shipping_args['saddr1']   = $order->shipping_address_1;
			$shipping_args['saddr2']   = $order->shipping_address_2;
			$shipping_args['scity']    = $order->shipping_city;
			$shipping_args['sstate']   = $this->get_state_name( $order->shipping_country, $order->shipping_state );
			$shipping_args['scountry'] = $order->shipping_country;
			$shipping_args['szip']     = $order->shipping_postcode;
		}

		return $shipping_args;
	}


	protected function get_state_name( $country_code, $state ) {
		$states = WC()->countries->get_states( $country_code );

		if ( isset( $states[ $state ] ) ) {
			return $states[ $state ];
		}

		return $state;
	}


	/**
	 * Logging method.
	 * @param string $message
	 */
	public static function log( $message ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}

			self::$log->add( 'emspay', $message );
		}
	}


	/**
	 * Validate frontend fields.
	 *
	 * Validate payment fields on the frontend.
	 *
	 * @return bool
	 */
	public function validate_fields() {
		if ( !in_array( $this->payment_method, $this->supported_payment_methods ) ) {
			wc_add_notice( __( 'Invalid payment method.', 'emspay' ), 'error' );
			return false;
		}

		return true;
	}


	public function hosted_payment_args( $args, $order ) {
		return $args;
	}

}
