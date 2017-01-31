<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Klarna.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @extends  Emspay_Gateway
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Gateway_Klarna extends Emspay_Gateway {

	protected $payment_method = 'klarna';

	protected $supported_checkout_options = array(
		'classic',
	);

	protected $supported_country_currency = array(
		'AT-EUR', // Austria - Euro (978)
		'DE-EUR', // Germany - Euro (978)
		'NL-EUR', // Netherlands - Euro (978)
		'NO-EUR', // Norway - Euro (978)
		'DK-DKK', // Denmark - Danish krone (208)
		'NO-NOK', // Norway - Norwegian krone (578)
		'SE-SEK', // Sweden - Swedish krona (752)
	);

	protected $supported_countries = array(
		'AT' => 'AUT', // Austria
		'DE' => 'DEU', // Germany
		'NL' => 'NLD', // Netherlands
		'NO' => 'NOR', // Norway
		'DK' => 'DNK', // Denmark
		'SE' => 'SWE', // Sweden
	);

	protected $supported_currencies = array(
		'EUR', // Euro (978)
		'DKK', // Danish krone (208)
		'NOK', // Norwegian krone (578)
		'SEK', // Swedish krona (752)
	);

	protected function define_variables() {
		$this->id                 = 'ems_klarna';
		$this->has_fields         = false;
		$this->method_title       = __( 'EMS Klarna', 'emspay' );
		$this->method_description = __( 'Klarna description.', 'emspay' );
		$this->icon               = plugin_dir_url( EMSPAY_PLUGIN_FILE ) . 'assets/images/icons/klarna.png';
	}


	protected function get_enabled_field_label() {
		return __( 'Enable Klarna', 'emspay' );
	}


	protected function get_title_field_default() {
		return __( 'Klarna', 'emspay' );
	}


	protected function get_description_field_default() {
		return __( 'Paying online with Klarna.', 'emspay' );
	}


	/**
	 * Process standard payments.
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	protected function process_hosted_payment( $order ) {
		if ( ! $this->is_valid_for_order( $order ) ) {
			wc_add_notice( $this->disabled_error, 'error' );

			return array(
				'result'   => 'fail',
				'redirect' => ''
			);
		}

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true )
		);
	}


	protected function is_currency_supported( $currency ) {
		return in_array( $currency, $this->supported_currencies );
	}


	protected function is_country_supported( $order ) {
		return array_key_exists( $order->billing_country, $this->supported_countries );
	}


	protected function is_country_currency_supported( $order ) {
		return in_array( $order->billing_country . '-' . $order->get_order_currency(), $this->supported_country_currency );
	}


	public function is_valid_for_use() {
		if ( ! parent::is_valid_for_use() ) {
			return false;
		}

		if ( ! $this->is_currency_supported( get_woocommerce_currency() ) ) {
			$this->disabled_error = sprintf( __( 'Gateway does not supports selected currency: %s.', 'emspay' ), get_woocommerce_currency() );
			return false;
		}

		return true;
	}


	public function is_valid_for_order( $order ) {
		if ( ! $this->is_country_supported( $order ) ) {
			$this->disabled_error = __( 'Klarna is not available in your country.', 'emspay' );
			return false;
		}

		if ( ! $this->is_country_currency_supported( $order ) ) {
			$this->disabled_error = __( 'The currency does not correspond to the country\'s currency.', 'emspay' );
			return false;
		}

		return true;
	}


	protected function include_billing_args() {
		return true;
	}


	// id;description;quantity;item_total_price;sub_total;vat_tax;shipping
	public function get_line_item_args( $order ) {
		$args = array();

		$i = 1;
		foreach ( $order->get_items( array( 'line_item', 'fee' ) ) as $item ) {
			$line_item = array(
				$item[ 'product_id' ], // id
				$item[ 'name' ], // description
				$item[ 'qty' ], // quantity
				$order->get_line_total( $item, true ), // item_total_price
				$order->get_line_subtotal( $item ), // sub_total
				$order->get_line_tax( $item ), // vat_tax
				0 // shipping (added as total shipping)
			);

			$args[ 'item' . $i++ ] = implode( ';', $line_item );
		}

		return $args;
	}


	public function get_klarna_phone( $order ) {
		return array(
			'klarnaPhone' => trim(
				preg_replace( '/[^\s0-9\-]/', '', str_replace( '+', '00', $order->billing_phone ) )
			)
		);
	}


	public function get_klarna_address( $order ) {
		$address = $order->billing_address_1;
		$street = $address;
		$house_number = '';
		$extension = $order->billing_address_2;

		if ( preg_match( '/^[^0-9]*/', $address, $match ) ) {
			$address = str_replace( $match[0], '', $address );
			$street  = trim( $match[0] );

			if ( strlen( $address ) != 0 ) {
				$addrArray = explode( ' ', $address );
				$house_number = array_shift( $addrArray );

				if ( count( $addrArray ) != 0 ) {
					// If there is an extension already include it
					if ( ! empty( $extension ) ) {
						array_push( $addrArray, $extension );
					}

					$extension = implode( ' ', $addrArray );
				}
			}
		}

		return array(
			'klarnaStreetName'           => $street,
			'klarnaHouseNumber'          => $house_number,
			'klarnaHouseNumberExtension' => $extension,
		);
	}


	public function hosted_payment_args( $args, $order ) {
		// correct the shipping price, include shipping tax, and exclude it from vattax
		$args[ 'shipping' ] = round( $args[ 'shipping' ] + $order->get_shipping_tax(), wc_get_price_decimals() );
		$args[ 'vattax' ]   = round( $args[ 'vattax' ] - $order->get_shipping_tax(), wc_get_price_decimals() );

		// remove phone number, because it override klarnaPhone field
		unset( $args[ 'phone' ] );

		return array_merge(
			$args,
			array(
				'klarnaFirstname' => $order->billing_first_name,
				'klarnaLastname'  => $order->billing_last_name,
			),
			$this->get_klarna_phone( $order ),
			$this->get_klarna_address( $order ),
			$this->get_line_item_args( $order )
		);
	}


}
