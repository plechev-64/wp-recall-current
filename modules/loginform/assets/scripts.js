Rcl.loginform = {
	animating: false,
	tabShow: function( tabId, e ) {
		var form = jQuery( '.usp-loginform' );
		form.find( '.tab, .tab-content' ).removeClass( 'active' );
		form.find( '.tab-' + tabId ).addClass( 'active' );
		jQuery( e ).addClass( 'active' );

	},
	send: function( tabId, e ) {
		var form = jQuery( e ).parents( "form" );
		if ( !rcl_check_form( form ) )
			return false;

		rcl_ajax( {
			data: form.serialize( ) + '&tab_id=' + tabId + '&action=rcl_send_loginform'
		} );

	},
	call: function( e ) {

		rcl_ajax( {
			data: {
				action: 'rcl_call_loginform'
			}
		} );

	}
};

