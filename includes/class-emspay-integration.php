<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Pay Integration.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Integration extends WC_Integration {

	/**
	 * The EMS store ID.
	 * @var String
	 */
	public $storename;

	/**
	 * EMS Shared Secret.
	 * @var string
	 */
	public $sharedsecret;

	/**
	 * Checkout option.
	 * @var string classic, checkoutoption
	 */
	public $checkoutoption;

	/**
	 * Pay mode.
	 * @var string payonly, payplus, fullpay
	 */
	public $mode;

	/**
	 * Transaction environment.
	 * @var string integration, production
	 */
	public $environment;

	/**
	 * Show gateways icons.
	 * @var boolean
	 */
	public $show_icon;

	/**
	 * Init and hook in the integration.
	 *
	 * @since  1.0.0
	 * @return Emspay_Integration
	 */
	public function __construct() {

		$this->id                 = 'emspay';
		$this->method_title       = __( 'EMS e-Commerce Gateway', 'emspay' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->checkoutoption = $this->get_option( 'checkoutoption', 'classic' );
		$this->mode           = $this->get_option( 'mode', 'payonly' );
		$this->show_icon      = 'yes' === $this->get_option( 'show_icon', 'yes' );

		$this->init_environment();

		// Actions.
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
	}


	public function enqueue_script() {
		if ( key_exists( 'page', $_GET ) && $_GET['page'] === 'wc-settings' && key_exists( 'tab', $_GET ) && $_GET['tab'] === 'integration' ) {
			wp_enqueue_script( 'emspay', plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/js/emspay-integration.js', array('jquery') );
		}
	}


	protected function init_environment() {
		$this->environment    = $this->get_option( 'environment', 'integration' );
		$this->storename      = $this->get_option( $this->environment . '_storename' );
		$this->sharedsecret   = $this->get_option( $this->environment . '_sharedsecret' );
	}


	/**
	 * Initialize integration settings form fields.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'environment' => array(
				'title'             => __( 'Environment', 'emspay' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'description'       => __( 'This setting specifies whether you will process live transactions, or whether you will process simulated transactions.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => 'integration',
				'options'           => array(
					'integration'  => __( 'Integration', 'emspay' ),
					'production'   => __( 'Production', 'emspay' ),
				)
			),
			'integration_storename' => array(
				'title'             => __( 'Store Name (integration)', 'emspay' ),
				'type'              => 'text',
				'description'       => __( 'This is the ID of the store provided by EMS.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => '',
			),
			'integration_sharedsecret' => array(
				'title'             => __( 'Shared Secret (integration)', 'emspay' ),
				'type'              => 'password',
				'description'       => __( 'This is the shared secret provided to you by EMS.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => '',
			),
			'production_storename' => array(
				'title'             => __( 'Store Name (production)', 'emspay' ),
				'type'              => 'text',
				'description'       => __( 'This is the ID of the store provided by EMS.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => '',
			),
			'production_sharedsecret' => array(
				'title'             => __( 'Shared Secret (production)', 'emspay' ),
				'type'              => 'password',
				'description'       => __( 'This is the shared secret provided to you by EMS.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => '',
			),
			'checkoutoption' => array(
				'title'             => __( 'Checkout option', 'emspay' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'description'       => __( 'This field allows you to set the checkout option.', 'emspay' ),
				'desc_tip'          => true,
				'default'           => 'classic',
				'options'           => array(
					// splits the payment process into multiple pages
					'classic'       => __( 'classic', 'emspay' ),
					// consolidates the payment method choice and the typical next step in a single page
					// limitations, supported payment methods are currently limited to:
					// credit cards, Maestro, PayPal, iDEAL, SOFORT and MasterPass
					'combinedpage'  => __( 'combinedpage', 'emspay' ),
				)
			),
			'mode' => array(
				'title'             => __( 'Pay mode', 'emspay' ),
				'type'              => 'select',
				'class'             => 'wc-enhanced-select',
				'description'       => __( 'Chosen mode for the transaction when using the "classic" checkout option', 'emspay' ),
				'desc_tip'          => true,
				'default'           => 'payonly',
				'options'           => array(
					// shows a hosted page to collect the minimum set of information for the transaction
					// (e. g. cardholder name, card number, expiry date and card code for a credit card transaction)
					'payonly'  => __( 'payonly', 'emspay' ),
					// in addition to the above, the payment gateway collects a full set of billing information on an additional page
					'payplus'  => __( 'payplus', 'emspay' ),
					// in addition to the above, the payment gateway displays a third page to also collect shipping information
					'fullpay'  => __( 'fullpay', 'emspay' ),
				)
			),
			'show_icon' => array(
				'title'   => __( 'Show gateway\'s icon', 'emspay' ),
				'type'    => 'checkbox',
				'label'   => __( 'Show icons', 'emspay' ),
				'default' => 'yes'
			),
		);
	}


	/**
	 * Return the title for admin screens.
	 * @return string
	 */
	public function get_method_title() {
		return apply_filters( 'woocommerce_integration_title', $this->method_title, $this );
	}


	/**
	 * Output the gateway settings screen.
	 */
	public function admin_options() {
		?>
		<h2><?php esc_html_e( $this->get_method_title() ) ?></h2>
		<div class="card">
			<h2><?php esc_html_e( 'Are you already a customer ?', 'emspay' ) ?></h2>
			<p>
			<?php esc_html_e( 'If you are already registered as an EMS merchant then please enter the credentials and settings below.', 'emspay' ) ?>
			<br/><br/>
			<?php esc_html_e( 'For new customers please follow the link below to acquire an EMS merchant account.', 'emspay' ) ?>
			</p>

			<h2><?php esc_html_e( 'Becoming an EMS customer', 'emspay' ) ?></h2>
			<p>
			<?php esc_html_e( 'Get a merchant account via this link:', 'emspay' ) ?>
			<a target="_blank" rel="external" href="https://www.emspay.eu/en/request-an-offer">https://www.emspay.eu/en/request-an-offer</a>
			</p>

			<h2><?php esc_html_e( 'Contact EMS Support', 'emspay' ) ?></h2>
			<p>
			<?php esc_html_e( 'Visit the FAQ:', 'emspay' ) ?>
			<br/>
			<a target="_blank" rel="external" href="http://www.emspay.eu/en/customer-service/faq">http://www.emspay.eu/en/customer-service/faq</a>
			<br/><br/>
			<?php esc_html_e( 'Contact information:', 'emspay' ) ?>
			<br/>
			<a target="_blank" rel="external" href="https://www.emspay.eu/en/about-ems/contact">https://www.emspay.eu/en/about-ems/contact</a>
			<br/>
			</p>
		</div>

		<div><input type="hidden" name="section" value="<?php esc_attr_e( $this->id ) ?>" /></div>
		<table class="form-table">
		<?php echo $this->generate_settings_html( $this->get_form_fields(), false ) ?>
		</table>
		<?php
	}


	/**
	 * Validate the Store Name
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 * @return string
	 */
	public function validate_integration_storename_field( $key, $value = null ) {
		return $this->validate_required_field( $key, $value, 'integration', __( 'Store Name (integration)', 'emspay' ) );
	}


	public function validate_production_storename_field( $key, $value = null ) {
		return $this->validate_required_field( $key, $value, 'production', __( 'Store Name (production)', 'emspay' ) );
	}


	/**
	 * Validate the Shared Secret
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 * @return string
	 */
	public function validate_integration_sharedsecret_field( $key, $value = null ) {
		return $this->validate_required_field( $key, $value, 'integration', __( 'Shared Secret (integration)', 'emspay' ) );
	}


	public function validate_production_sharedsecret_field( $key, $value = null ) {
		return $this->validate_required_field( $key, $value, 'production', __( 'Shared Secret (production)', 'emspay' ) );
	}


	/**
	 * Validate a required field.
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  string $key
	 * @param  string $value
	 * @param  string $title
	 * @return string
	 */
	public function validate_required_field( $key, $value, $environment, $title ) {
		if ( is_null( $value ) ) {
			$field_key = $this->get_field_key( $key );
			$value     = isset( $_POST[ $field_key ] ) ? $_POST[ $field_key ] : null;
		}

		$value = $this->validate_text_field( $key, $value );

		if ( $this->get_option( 'environment' ) != $environment ) {
			return $value;
		}

		if ( empty( $value ) ) {
			WC_Admin_Settings::add_error( sprintf( __( 'Error: You must enter %s.', 'emspay' ), $title ) );
		}

		return $value;
	}

	public function get_core_options() {
		$this->init_environment();

		$core_options = new EmsCore\Options();
		$url = WC()->api_request_url( 'Emspay_Gateway' );

		$core_options
			->setStoreName($this->storename)
			->setSharedSecret($this->sharedsecret)
			->setEnvironment($this->environment)
			->setCheckoutOption($this->checkoutoption)
			->setPayMode($this->mode)
			->setFailUrl($url)
			->setSuccessUrl($url)
			->setIpnUrl($url);

		return $core_options;
	}

	public function is_connected() {
		$this->init_environment();

		return ( ! empty( $this->storename ) && ! empty( $this->sharedsecret ) );
	}


	public function process_admin_options() {
		parent::process_admin_options();

		// Validate credentials
		$this->validate_credentials();
	}

	protected function validate_credentials() {
		if ( ! $this->is_connected() ) {
			return false;
		}


		if( ! EmsCore\Request::checkCredentials( $this->get_core_options() ) ) {
			WC_Admin_Settings::add_error( sprintf( __( 'Error: The %s credentials you provided are not valid.  Please double-check that you entered them correctly and try again.', 'emspay' ), $this->environment ) );
			return false;
		}

		return true;
	}


}
