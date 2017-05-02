<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMS Gateway Response.
 *
 * @package	Ems_Payments_For_WooCommerce
 * @category Class
 * @author	 DLWT
 * @version	1.0.0
 */
class Emspay_Gateway_Response {

	/**
	 * Handle the EMS Transaction Response.
	 */
	public static function response_handler() {
		if ( ! empty( $_POST ) ) {
			Emspay_Gateway::log( 'EMS Response ' . print_r( $_POST, true ) );

			$post = wp_unslash( $_POST );
			$response = new EmsCore\Response( self::get_core_option() );

			if ( $response->validate( $post ) ) {
				$order_id = absint( $response->oid );

				if ( $order = wc_get_order( $order_id ) ) {
					$core_order = new EmsCore\Order();
					$core_order->chargetotal      = $order->get_total();
					$core_order->paymentMethod    = $order->ems_payment_method;
					$core_order->transactionTime  = $order->ems_txndatetime;
					$core_order->currency         = $order->ems_currency_code;

					if ( $response->validateTransaction( $core_order ) ) {
						// Store meta data to order.
						self::save_emspay_meta( $order, $response );

						$status = strtolower( $response->status );
						call_user_func( array( 'Emspay_Gateway_Response', 'payment_status_' . $status ), $order, $response );
						exit;
					} else {
						Emspay_Gateway::log( 'EMS Response invalid transaction: ' . $response->getError() );
					}
				} else {
					Emspay_Gateway::log( 'EMS Response Order #' . $order_id . ' not found' );
				}
			} else {
				Emspay_Gateway::log( 'EMS Response invalid POST data: ' . $response->getError() );
			}
		} else {
			Emspay_Gateway::log( 'EMS Response empty POST data');
		}

		wp_die( 'EMS Response Failure', 'EMS response', 500 );
	}


	protected static function save_emspay_meta( $order, $response ) {
		// Store meta data to order.
		foreach( self::get_emspay_meta( $response ) as $key => $value ) {
			update_post_meta( $order->id, $key, $value );
		}
	}


	protected static function get_emspay_meta( $response ) {
		return array(
			'_ems_approval_code' => $response->approval_code,
			'_ems_status'        => $response->status,
		);
	}


	protected static function is_klarna_payment( $order ) {
		return $order->ems_payment_method == 'klarna';
	}


	protected static function get_core_option() {
		return emspay_gateway()->get_integration()->get_core_options();
	}


	protected static function payment_status_approved( $order, $response ) {
		self::payment_complete( $order, $response );
	}


	protected static function payment_status_declined( $order, $response ) {
		self::payment_failed( $order, $response );
	}


	protected static function payment_status_failed( $order, $response ) {
		self::payment_failed( $order, $response );
	}


	protected static function payment_status_waiting( $order, $response ) {
		self::payment_on_hold( $order, $response );
	}


	protected static function payment_complete( $order, $response ) {
		if ( !$order->is_paid() ) {
			Emspay_Gateway::log( 'Order #' . $order->id . ' payment complete, reference number: ' . $response->ipgTransactionId );

			// Add order note
			$order->add_order_note( sprintf( __( 'EMS payment approved (Reference number: %s)', 'emspay' ), $response->ipgTransactionId ) );
			if ( self::is_klarna_payment( $order ) ) {
				$order->add_order_note( __( 'Please visit the EMS virtual terminal to approve the payment for Klarna. The current order status "proceeding" or "completed" does not mean that the payment has been approved.', 'emspay' ) );
			}

			// Payment complete
			$order->payment_complete( $response->refnumber );
		} else {
			Emspay_Gateway::log( 'Order #' . $order->id . ' already paid (based on the order status).' );
		}

		self::maybe_redirect( $order, $response );
	}


	protected static function payment_failed( $order, $response ) {
		Emspay_Gateway::log( 'Order #' . $order->id . ' payment failed, fail eason: ' . $response->fail_reason );

		// Store meta data to order.
		update_post_meta( $order->id, '_ems_fail_reason', $response->fail_reason );
		// Set order status to failed
		$order->update_status( 'failed', sprintf( __( 'EMS payment error: %s', 'emspay' ), $response->fail_reason ) );

		if ( !$response->isNotification() ) {
			wc_add_notice( sprintf( __('Payment error: %s', 'emspay'), $response->fail_reason ), 'error' );
		}

		self::maybe_redirect( $order, $response );
	}


	protected static function payment_on_hold( $order, $response ) {
		if ( !self::order_has_status( $order, 'on-hold' ) ) {
			Emspay_Gateway::log( 'Order #' . $order->id . ' payment on hold.');

			// Set order status to on-hold
			$order->update_status( 'on-hold', sprintf( __( 'EMS payment pending: %s', 'emspay' ), $response->status )  );
			if ( self::is_klarna_payment( $order ) ) {
				$order->add_order_note( __( 'Please visit the EMS virtual terminal to approve the payment for Klarna. The current order status "proceeding" or "completed" does not mean that the payment has been approved.', 'emspay' ) );
			}

			$order->reduce_order_stock();

  		//WC()->cart->empty_cart();
		}

		self::maybe_redirect( $order, $response );
	}


	protected static function order_has_status( $order, $status ) {
		$result = $order->has_status( $status );
		if ( $result ) {
			Emspay_Gateway::log( 'Order #' . $order->id . ' is already ' . $status );
		}

		return $result;
	}


	protected static function maybe_redirect( $order, $response ) {
		if ( $response->isNotification() ) {
			wp_die( 'EMS Response Processed', 'EMS response', 200 );
		}

		wp_redirect( self::get_return_url( $order ) );
		exit();
	}


	protected static function get_return_url( $order = null ) {
		if ( $order ) {
			$return_url = $order->get_checkout_order_received_url();
		} else {
			$return_url = wc_get_endpoint_url( 'order-received', '', wc_get_page_permalink( 'checkout' ) );
		}

		if ( is_ssl() || get_option('woocommerce_force_ssl_checkout') == 'yes' ) {
			$return_url = str_replace( 'http:', 'https:', $return_url );
		}

		return apply_filters( 'woocommerce_get_return_url', $return_url, $order );
	}




}