<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wordpress.org/plugins/ems-payments-for-woocommerce/
 * @since             1.0.0
 * @package           Ems_Payments_For_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       EMS payments for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/ems-payments-for-woocommerce/
 * Description:       Accept payments in WooCommerce with the official EMS e-Commerce Gateway plugin.
 * Version:           1.1.1
 * Author:            emspay
 * Author URI:        http://emspay.eu/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       emspay
 * Domain Path:       /languages
 */


/**
 * EMS Pay Gateway Plugin.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
final class Emspay_Gateway_Plugin {

	/**
	 * @var Emspay_Gateway_Plugin The single instance of the class
	 * @access protected
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main Emspay_Gateway_Plugin Instance.
	 *
	 * Ensures only one instance of Emspay_Gateway_Plugin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Emspay_Gateway_Plugin Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Emspay_Gateway_Plugin Constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

	}


	/**
	 * Define Pugin Constants.
	 */
	private function define_constants() {
		$this->define( 'EMSPAY_PLUGIN_FILE', __FILE__ );
	}


	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}


	/**
	 * Hook into actions and filters.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		add_filter( 'woocommerce_currencies', array( 'Emspay_Currency', 'emspay_supported_currencies' ) );
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function includes() {
		include_once 'includes/class-emspay-currency.php';
	}


	/**
	 * Initialize the plugin.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function init_plugin() {
		// Checks if WooCommerce is installed.
		if ( class_exists( 'WC_Integration' ) ) {
			$this->register_integration();

			$this->register_gateways();

			$this->register_filters();

			// If needed show admin missing settings warning
			add_action( 'admin_notices', array( $this, 'show_settings_warning' ) );

			$this->load_plugin_textdomain();
		} else {
			// maybe throw an admin error
		}
	}


	/**
	 * Register the integration.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function register_integration() {
		// Include our integration class.
		include_once 'includes/class-emspay-integration.php';

		// Register the integration.
		add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
	}


	/**
	 * Init gateways.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function register_gateways() {
		// Include required classes.
		include_once 'includes/core/index.php';
		include_once 'includes/abstracts/abstract-emspay-gateway.php';
		include_once 'includes/gateways/class-emspay-gateway-bancontact.php';
		include_once 'includes/gateways/class-emspay-gateway-creditcard.php';
		include_once 'includes/gateways/class-emspay-gateway-ideal.php';
		include_once 'includes/gateways/class-emspay-gateway-klarna.php';
		include_once 'includes/gateways/class-emspay-gateway-maestro.php';
		include_once 'includes/gateways/class-emspay-gateway-masterpass.php';
		include_once 'includes/gateways/class-emspay-gateway-paypal.php';
		include_once 'includes/gateways/class-emspay-gateway-sofort.php';

		include_once 'includes/class-emspay-gateway-response.php';

		// Register the gateways.
		add_filter( 'woocommerce_payment_gateways', array( $this, 'payment_gateways' ) );
	}


	/**
	 * Init filters.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function register_filters() {
		// Include required classes.
		include_once 'includes/filters/class-emspay-filters.php';
		$Emsfilters	=	new Emspay_Filters();
	}



	/**
	 * Load Localisation file.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	protected static function load_plugin_textdomain() {
		load_plugin_textdomain( 'emspay', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}


	public static function activate_plugin() {
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			// Deactivate the plugin
			deactivate_plugins( __FILE__ );

			self::load_plugin_textdomain();

			die( __( 'EMS payments for WooCommerce requires WooCommerce plugin to be active!', 'emspay' ) );
		}
	}


	/**
	 * Add Emspay Gateway integration to WooCommerce.
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array
	 * @return array
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'Emspay_Integration';

		return $integrations;
	}


	/**
	 * Init gateways
	 *
	 * @access public
	 * @since  1.0.0
	 * @param  array
	 * @return array
	 */
	public function payment_gateways( $load_gateways ) {
		$gateways = array(
			'Emspay_Gateway_Bancontact',
			'Emspay_Gateway_Creditcard',
			'Emspay_Gateway_Ideal',
			'Emspay_Gateway_Klarna',
			'Emspay_Gateway_Maestro',
			'Emspay_Gateway_Masterpass',
			'Emspay_Gateway_Paypal',
			'Emspay_Gateway_Sofort',
		);

		return array_merge( $load_gateways, $gateways );
	}


	/**
	 * Display an admin warning, if required settings are missing.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function show_settings_warning() {
		if ( ( empty( $_GET['page'] ) || empty( $_GET['tab'] ) || 'wc-settings' !== $_GET['page'] || 'integration' !== $_GET['tab'] ) ) {
			$integration = $this->get_integration();

			if ( ! $integration->is_connected() ) {
				$url = $this->get_settings_url();
				?>
				<div class="notice notice-warning">
					<p>
						<?php echo sprintf( __( '%sEMS e-Commerce Gateway is almost ready. To get started, %sconnect your EMS account%s.%s', 'emspay' ), '<strong>', '<a href="' . esc_url( $url ) . '">', '</a>', '</strong>' ); ?>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Generate a URL to our specific settings screen.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string Generated URL.
	 */
	public function get_settings_url() {
		$url = admin_url( 'admin.php' );
		$url = add_query_arg( 'page', 'wc-settings', $url );
		$url = add_query_arg( 'tab', 'integration', $url );
		$url = add_query_arg( 'section', 'emspay', $url );

		return $url;
	}


	public function get_integration() {
		// Find a different method of retrieving this value.
		return WC()->integrations->integrations['emspay'];
	}

}

register_activation_hook( __FILE__, array( 'Emspay_Gateway_Plugin', 'activate_plugin' ) );

/**
 * Return instance of Emspay_Gateway_Plugin.
 *
 * @return Emspay_Gateway_Plugin
 */
function emspay_gateway() {
	return Emspay_Gateway_Plugin::instance();
}

emspay_gateway();
