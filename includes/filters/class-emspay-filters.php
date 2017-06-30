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

	    // Force price decimals to 2. EMS can only work with 2 decimals.
		add_filter('wc_get_price_decimals', array($this, 'ems_wc_change_price_decimals') , 200, 2 );

		// Change the way woocommerce rounds its prices. Force to 2 decimals.
        add_filter('pre_option_woocommerce_price_num_decimals', array($this, 'pre_option_woocommerce_price_num_decimals'), 10, 1);
	} 

	/**
	 * Forces 2 decimal precision for compatibility with IPG.
     * @return int
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

    /**
     * Overrides the number of decimals used to format prices.
     *
     * @param int decimals The number of decimals passed by WooCommerce.
     * @return int
     */
    public function pre_option_woocommerce_price_num_decimals($decimals) {
        $price_decimals = 2;
        return $price_decimals;
    }
}