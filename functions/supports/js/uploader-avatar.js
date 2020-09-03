jQuery( function( $ ) {

	RclUploaders.get( 'rcl_avatar' ).afterDone = function( e, data ) {

		var image = jQuery( '#rcl-avatar .avatar-image img' ).attr( 'srcset', '' ).attr( 'src', data.result.uploads[0].src );
		image.load( function() {
			image.animateCss( 'zoomIn' );
		} );

		image = jQuery( '#recallbar img.avatar' ).attr( 'srcset', '' ).attr( 'src', data.result.uploads[0].src );
		image.load( function() {
			image.animateCss( 'zoomIn' );
		} );

		rcl_do_action( 'rcl_success_upload_avatar', data );

	};

	RclUploaders.get( 'rcl_avatar' ).animateLoading = function( status ) {

		if ( status )
			rcl_preloader_show( jQuery( '#rcl-avatar' ) );
		else
			rcl_preloader_hide();

	};

} );