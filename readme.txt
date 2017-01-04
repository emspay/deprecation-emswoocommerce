=== EMS payments for WooCommerce ===
Contributors: dave.ligthart
Tags: ems, emspay, payments, woocommerce, e-commerce, webshop, psp, ideal, sofort, credit card, creditcard, visa, mastercard, mistercash, bancontact, bitcoin, paysafecard, direct debit, incasso, sepa, banktransfer, overboeking, betalingen
Requires at least: 3.8
Tested up to: 4.6.1
Stable tag: 2.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept payments in WooCommerce with the official EMS e-Commerce Gateway plugin

== Description ==

This plugin will add support for the following EMS payments methods to your WooCommerce webshop:

* iDEAL
* PayPal
* Maestro
* Creditcard
* MasterPass

= Features =

* Support for all available EMS payment methods
* Multiple translations: English, Dutch
* WordPress Multisite support
* WPML support

== Frequently Asked Questions ==

= I can't install the plugin, the plugin is displayed incorrectly =

Please temporarily enable the [WordPress Debug Mode](https://codex.wordpress.org/Debugging_in_WordPress). Edit your `wp-config.php` and set the constants `WP_DEBUG` and `WP_DEBUG_LOG` to `true` and try
it again. When the plugin triggers an error, WordPress will log the error to the log file `/wp-content/debug.log`. Please check this file for errors. When done, don't forget to turn off
the WordPress debug mode by setting the two constants `WP_DEBUG` and `WP_DEBUG_LOG` back to `false`.

= I get a white screen when opening ... =

Most of the time a white screen means a PHP error. Because PHP won't show error messages on default for security reasons, the page is white. Please turn on the WordPress Debug Mode to turn on PHP error messages (see previous answer).

= I have a different question about this plugin =

Please contact plugins@emspay.nl with your EMS partner ID, please describe your problem as detailed as possible. Include screenshots where appropriate.
Where possible, also include the log file. You can find the log files in `/wp-content/uploads/wc-logs/` or `/wp-content/plugin/woocommerce/logs`.

== Installation ==

= Minimum Requirements =

* PHP version 5.2 or greater
* PHP extensions enabled: cURL, JSON
* WordPress 3.8 or greater
* WooCommerce 2.1.0 or greater

= Automatic installation =

1. Install the plugin via Plugins -> New plugin. Search for 'EMS Payments for WooCommerce'.
2. Activate the 'EMS Payments for WooCommerce' plugin through the 'Plugins' menu in WordPress
3. Set your EMS API key at WooCommerce -> Settings -> Checkout (or use the *EMS Settings* link in the Plugins overview)
4. You're done, the active payment methods should be visible in the checkout of your webshop.

= Manual installation =

1. Unpack the download package
2. Upload the directory 'ems-payments-for-woocommerce' to the `/wp-content/plugins/` directory
3. Activate the 'EMS Payments for WooCommerce' plugin through the 'Plugins' menu in WordPress
4. Set your EMS API key at WooCommerce -> Settings -> Checkout (or use the *EMS Settings* link in the Plugins overview)
5. You're done, the active payment methods should be visible in the checkout of your webshop.

Please contact plugins@emspay.nl if you need help installing the EMS WooCommerce plugin. Please provide your EMS partner ID and website URL.

= Updating =

Automatic updates should work flawlessly; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.0 =
* Initial release
