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


	protected function get_item_subtotal_tax( $item, $round = true ) {
		$price = $item['line_subtotal_tax'] / max( 1, $item['qty'] );
		$price = $round ? wc_round_tax_total( $price ) : $price;

		return $price;
	}


	static public function get_items_count( $items, $order ) {
		$count = count( $items );
		if ( $order->get_shipping_tax() > 0 ) {
			$count++;
		}

		return $count;
	}


	static public function get_line_item_tax( $tax, &$actual_tax, &$items_count ) {
		if ( $items_count == 1 ) {
			$tax = $actual_tax;
		} else {
			$items_count--;
			$actual_tax -= $tax;
		}

		return $tax;
	}


	// id;description;quantity;item_total_price;sub_total;vat_tax;shipping
	public function get_line_item_args( $order ) {
		$args = array();

		$i = 1;
		$actual_tax = $order->get_total_tax();
		$items = $order->get_items( array( 'line_item', 'fee' ) );
		$items_count = self::get_items_count( $items, $order );

		foreach ( $items as $item ) {
			$line_item = array(
				$item[ 'product_id' ], // id
				$item[ 'name' ], // description
				$item[ 'qty' ], // quantity
				$order->get_item_subtotal( $item, true ), // item_total_price (inc tax)
				$order->get_item_subtotal( $item ), // sub_total (exc tax)
				self::get_line_item_tax( $this->get_item_subtotal_tax( $item ), $actual_tax, $items_count ), // vat_tax
				0 // shipping (added as total shipping)
			);

			self::add_line_item( $args, $i++, $line_item );
		}

		if ( $order->get_total_shipping() > 0 ) {
			$shipping_tax = self::get_line_item_tax( $order->get_shipping_tax(), $actual_tax, $items_count );

			$line_item = array(
				'IPG_SHIPPING', // id
				__( 'Shipping fee', 'emspay' ), // description
				1, // quantity
				self::round_price( $order->get_total_shipping() + $shipping_tax ), // item_total_price
				self::round_price( $order->get_total_shipping() ), // sub_total
				self::round_price( $shipping_tax ), // vat_tax
				0 // shipping (added as total shipping)
			);

			self::add_line_item( $args, $i++, $line_item );
		}

		if ( $order->get_total_discount() > 0 ) {
			$line_item = array(
				0, // id
				__( 'Discount', 'emspay' ), // description
				1, // quantity
				- $order->get_total_discount(), // item_total_price
				- $order->get_total_discount(), // sub_total
				0, // vat_tax
				0 // shipping (added as total shipping)
			);

			self::add_line_item( $args, $i, $line_item );
		}

		return $args;
	}

  static public function add_line_item( &$args, $idx, $line_item ) {
    $args[ 'item' . $idx ] = implode( ';', $line_item );
  }

	static public function round_price( $price ) {
		return round( $price, wc_get_price_decimals() );
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
		// we add the shipping price as line item
		$args[ 'shipping' ] = 0;
		$args[ 'subtotal' ] = self::round_price( $args[ 'subtotal' ] + $order->get_total_shipping() );

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
