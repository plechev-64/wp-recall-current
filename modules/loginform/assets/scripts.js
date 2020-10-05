jQuery( window ).load( function() {
	jQuery( "body" ).on( 'click', '.rcl-register', function() {
		Rcl.loginform.call( 'register' );
	} );

	jQuery( "body" ).on( 'click', '.rcl-login', function() {
		Rcl.loginform.call( 'login' );
	} );

	if ( rcl_url_params['rcl-form'] ) {
		if ( rcl_url_params['type-form'] == 'float' ) {
			Rcl.loginform.call( rcl_url_params['rcl-form'], rcl_url_params['formaction'] );
		} else {
			Rcl.loginform.tabShow( rcl_url_params['rcl-form'] );
		}
	}

} );

Rcl.loginform = {
	animating: false,
	tabShow: function( tabId, e ) {
		var form = jQuery( '.rcl-loginform' );
		form.find( '.tab, .tab-content' ).removeClass( 'active' );
		form.find( '.tab-' + tabId ).addClass( 'active' );
		if ( e )
			jQuery( e ).addClass( 'active' );
		else
			form.find( '.tab-' + tabId ).addClass( 'active' );

	},
	send: function( tabId, e ) {
		var form = jQuery( e ).parents( "form" );
		if ( !rcl_check_form( form ) )
			return false;

		rcl_preloader_show( jQuery( '.rcl-loginform' ) );

		rcl_ajax( {
			data: form.serialize( ) + '&tab_id=' + tabId + '&action=rcl_send_loginform',
			afterSuccess: function( result ) {
				jQuery( '.tab-content.tab-' + tabId ).html( result.content );
			}
		} );

	},
	call: function( form, action ) {

		var form = form ? form : 'login';
		var formaction = action ? action : '';

		rcl_ajax( {
			data: {
				form: form,
				formaction: formaction,
				action: 'rcl_call_loginform'
			}
		} );

	}
};

