/**
 * EMS Pay Integration JS
 */
jQuery( function ( $ ) {

	function togleEnvFields() {
		// Get value.
	var env = $( this ).val();
	var map = {
		production: 'integration',
		integration: 'production'
	};
	var show = '#woocommerce_emspay_' + env + '_storename, #woocommerce_emspay_' + env + '_sharedsecret';
	var hide = '#woocommerce_emspay_' + map[env] + '_storename, #woocommerce_emspay_' + map[env] + '_sharedsecret';;

		$( show ).parents( 'tr' ).show();
		$( hide ).parents( 'tr' ).hide();
	}

	$( '#woocommerce_emspay_environment' ).each( togleEnvFields ).change( togleEnvFields );
});
