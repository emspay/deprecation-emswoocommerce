<?php


class Emspay_Masterpass { 

	public function __construct() {
		add_action('woocommerce_proceed_to_checkout', array( $this, 'ems_masterpass_button' ) );
	}

	public function ems_masterpass_button(){
		echo 'test';
	}

}