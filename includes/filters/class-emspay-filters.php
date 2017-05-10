<?php
/**
 * EMS Filters.
 *
 * @package  Ems_Payments_For_WooCommerce
 * @category Class
 * @author   DLWT
 * @version  1.0.0
 */
class Emspay_Filters {

	public function __construct() {
		add_filter('wc_get_price_decimals', array($this, 'ems_wc_change_price_decimals') , 200, 2 );
	} 

	/**
	 * Forces 2 decimal precision for compatibility with IPG.
	 */
	public function ems_wc_change_price_decimals() {
		$decimals	=	get_option( 'woocommerce_price_num_decimals', 2 );
		if($decimals < 2) {
			return absint( 2 );
		} 
		else {
			return absint( $decimals );
		}
	}
}