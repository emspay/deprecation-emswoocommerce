<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Pay Currency.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
final class Emspay_Currency {

	// List of supported currencies (numeric ISO code map)
	const NUMERIC_CURRENCY_CODES = array(
		'AUD' => '036', // Australian dollar
		'BRL' => '986', // Brazilian real
		'EUR' => '978', // Euro
		'INR' => '356', // Indian rupee
		'GBP' => '826', // Pound sterling
		'USD' => '840', // United States dollar
		'ZAR' => '710', // South African rand
		'CHF' => '756', // Swiss franc
		'AWG' => '533', // Aruban florin
		'KYD' => '136', // Cayman Islands dollar
		'DOP' => '214', // Dominican peso
		'BSD' => '044', // Bahamian dollar
		'BHD' => '048', // Bahraini dinar
		'BBD' => '052', // Barbadian dollar
		'BZD' => '084', // Belize dollar
		'CAD' => '124', // Canadian dollar
		'CNY' => '156', // Chinese yuan
		'HRK' => '191', // Croatian kuna
		'CZK' => '203', // Czech koruna
		'DKK' => '208', // Danish krone
		'XCD' => '951', // East Caribbean dollar
		'GYD' => '328', // Guyanese dollar
		'HKD' => '344', // Hong Kong dollar
		'HUF' => '348', // Hungarian forint
		'ISL' => '376', // Israeli new shekel
		'JMD' => '388', // Jamaican dollar
		'JPY' => '392', // Japanese yen
		'KWD' => '414', // Kuwaiti dinar
		'LTL' => '440', // Lithuanian litas
		'MXN' => '484', // Mexican peso
		'NZD' => '554', // New Zealand dollar
		'ANG' => '532', // Netherlands Antillean guilder
		'NOK' => '578', // Norwegian krone
		'OMR' => '512', // Omani rial
		'PLN' => '985', // Polish zloty
		'RON' => '946', // Romanian leu
		'SAR' => '682', // Saudi riyal
		'SGD' => '702', // Singapore dollar
		'KRW' => '410', // South Korean won
		'SRD' => '968', // Surinamese dollar
		'SEK' => '752', // Swedish krona
		'TTD' => '780', // Trinidad and Tobago dollar
		'TRY' => '949', // Turkish lira
		'AED' => '784', // United Arab Emirates dirham
	);


	/**
	 * Check if the currency is supported by EMS Pay.
	 *
	 * @since  1.0.0
	 * @param string
	 * @return bool
	 */
	public static function is_valid_currency( $currency_code ) {
		return array_key_exists( $currency_code, self::NUMERIC_CURRENCY_CODES );
	}


	/**
	 * Get back the numeric ISO code for the currency.
	 *
	 * @since  1.0.0
	 * @param string
	 * @return string
	 */
	public static function get_numeric_currency_code( $currency_code ) {
		if ( self::is_valid_currency( $currency_code ) ) {
			return self::NUMERIC_CURRENCY_CODES[ $currency_code ];
		}

		return '';
	}


	/**
	 * Filters the supported currencies.
	 *
	 * @since  1.0.0
	 * @param array
	 * @return array
	 */
	public static function emspay_supported_currencies( $currencies ) {
		return array_intersect_key( $currencies, self::NUMERIC_CURRENCY_CODES );
	}


}
