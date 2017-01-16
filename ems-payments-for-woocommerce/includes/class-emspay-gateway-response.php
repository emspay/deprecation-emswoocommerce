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
						update_post_meta( $order->id, '_ems_approval_code', $response->approval_code );
						update_post_meta( $order->id, '_ems_status', $response->status );

						$status = strtolower( $response->status );
						call_user_func( array( 'Emspay_Gateway_Response', 'payment_status_' . $status ), $order, $response );
						exit;
					}
				}
			}
		}

		wp_die( 'EMS Response Failure', 'EMS response', 500 );
	}

	protected static function get_core_option() {
		$integration = emspay_gateway()->get_integration();

		$core_option = new EmsCore\Options();
		$core_option->setStoreName($integration->storename);
		$core_option->setSharedSecret($integration->sharedsecret);
		$core_option->setEnvironment($integration->environment);
		$core_option->setCheckoutOption($integration->checkoutoption);
		$core_option->setPayMode($integration->mode);

		return $core_option;
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
		// Add order note
		$order->add_order_note( sprintf( __( 'EMS payment approved (Reference number: %s)', 'emspay' ), $response->refnumber ) );
		// Payment complete
		$order->payment_complete( $response->refnumber );
	}


	protected static function payment_failed( $order, $response ) {
		// Store meta data to order.
		update_post_meta( $order->id, '_ems_fail_reason', $response->fail_reason );
		// Set order status to failed
		$order->update_status( 'failed', sprintf( __( 'EMS payment error: %s', 'emspay' ), $response->fail_reason ) );
	}


	protected static function payment_on_hold( $order, $response ) {
		// Set order status to failed
		$order->update_status( 'on-hold', sprintf( __( 'EMS payment pending: %s', 'emspay' ), $response->status )  );
		$order->reduce_order_stock();

		//WC()->cart->empty_cart();
	}
}
