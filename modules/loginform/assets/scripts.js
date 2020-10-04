jQuery( window ).load( function() {
	jQuery( "body" ).on( 'click', '.rcl-register', function() {
		Rcl.loginform.call( 'register' );
	} );

	jQuery( "body" ).on( 'click', '.rcl-login', function() {
		Rcl.loginform.call( 'login' );
	} );

	if ( rcl_url_params['action-rcl'] === 'login' ) {
		Rcl.loginform.call( 'login' );
	} else if ( rcl_url_params['action-rcl'] === 'register' ) {
		Rcl.loginform.call( 'register' );
	} else if ( rcl_url_params['action-rcl'] === 'lostpassword' ) {
		Rcl.loginform.call( 'lostpassword' );
	} else if ( rcl_url_params['show-form'] === 'login' ) {
		Rcl.loginform.tabShow( 'login' );
	} else if ( rcl_url_params['show-form'] === 'register' ) {
		Rcl.loginform.tabShow( 'register' );
	} else if ( rcl_url_params['show-form'] === 'lostpassword' ) {
		Rcl.loginform.tabShow( 'lostpassword' );
	}

} );

Rcl.loginform = {
	animating: false,
	tabShow: function( tabId, e ) {
		var form = jQuery( '.usp-loginform' );
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

		rcl_preloader_show( jQuery( '.usp-loginform' ) );

		rcl_ajax( {
			data: form.serialize( ) + '&tab_id=' + tabId + '&action=rcl_send_loginform',
			afterSuccess: function( result ) {
				jQuery( '.tab-content.tab-' + tabId ).html( result.content );
			}
		} );

	},
	call: function( form ) {

		var form = form ? form : 'login';

		rcl_ajax( {
			data: {
				form: form,
				action: 'rcl_call_loginform'
			}
		} );

	}
};

