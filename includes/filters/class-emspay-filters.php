<?php

	class Emspay_Filters{

		public function __construct(){
			add_filter('wc_get_price_decimals', array($this, 'ems_wc_change_price_decimals') , 200, 2 );
		}

		public function ems_wc_change_price_decimals(){
			$decimals	=	get_option( 'woocommerce_price_num_decimals', 2 );
			if($decimals < 2){
					return absint( 2 );
				}else{
					return absint( $decimals );
				}
		}

	}