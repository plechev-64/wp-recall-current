
jQuery( function( $ ) {

	/* показ дочерних полей */
	$( ".rcl-parent-field:not(.rcl-children-field)" ).find( "input, select" ).each( function() {
		RclOptionsControl.showChildrens( RclOptionsControl.getId( this ), $( this ).val() );
	} );

	$( '.rcl-parent-field select, .rcl-parent-field input' ).change( function() {
		RclOptionsControl.hideChildrens( RclOptionsControl.getId( this ) );
		RclOptionsControl.showChildrens( RclOptionsControl.getId( this ), $( this ).val() );
	} );
	/***/

} );

var RclOptionsControl = {
	getId: function( e ) {
		return jQuery( e ).attr( 'type' ) == 'radio' && jQuery( e ).is( ":checked" ) ? jQuery( e ).data( 'slug' ) : jQuery( e ).attr( 'id' );
	},
	showChildrens: function( parentId, parentValue ) {

		var childrenBox = jQuery( '[data-parent="' + parentId + '"][data-parent-value="' + parentValue + '"]' );

		if ( !childrenBox.length )
			return false;

		childrenBox.show();

		if ( childrenBox.hasClass( 'rcl-parent-field' ) ) {

			childrenBox.find( "input, select" ).each( function() {

				RclOptionsControl.showChildrens( RclOptionsControl.getId( this ), jQuery( this ).val() );

			} );
		}

	},
	hideChildrens: function( parentId ) {

		var childrenBox = jQuery( '[data-parent="' + parentId + '"]' );

		childrenBox.hide();

		if ( childrenBox.hasClass( 'rcl-parent-field' ) ) {

			childrenBox.find( "input, select" ).each( function() {

				RclOptionsControl.hideChildrens( RclOptionsControl.getId( this ) );

			} );
		}
	}

};

function rcl_enable_extend_options( e ) {
	var extend = e.checked ? 1 : 0;
	jQuery.cookie( 'rcl_extends', extend );
	var options = jQuery( '.rcl-options-form .extend-options' );
	if ( extend )
		options.show();
	else
		options.hide();
}

function rcl_update_options() {

	rcl_preloader_show( jQuery( '.rcl-options-form' ) );

	if ( typeof tinyMCE != 'undefined' )
		tinyMCE.triggerSave();

	rcl_ajax( {
		/*rest: {action: 'usp_update_options'},*/
		data: 'action=rcl_update_options&' + jQuery( '.rcl-options-form' ).serialize()
	} );

	return false;
}

function rcl_get_option_help( elem ) {

	var help = jQuery( elem ).children( '.help-content' );
	var title_dialog = jQuery( elem ).parents( '.rcl-option' ).children( 'rcl-field-title' ).text();

	var content = help.html();
	help.dialog( {
		modal: true,
		dialogClass: 'rcl-help-dialog',
		resizable: false,
		minWidth: 400,
		title: title_dialog,
		open: function( e, data ) {
			jQuery( '.rcl-help-dialog .help-content' ).css( {
				'display': 'block',
				'min-height': 'initial'
			} );
		},
		close: function( e, data ) {
			jQuery( elem ).append( '<span class="help-content">' + content + '</span>' );
		}
	} );
}

function rcl_onclick_options_label( e ) {

	var label = jQuery( e );

	var viewBox = label.data( 'options' );

	if ( jQuery( '#' + viewBox + '-options-box' ).hasClass( 'active' ) )
		return false;

	jQuery( '.rcl-options .options-box' ).removeClass( 'active' );
	jQuery( '.rcl-options .rcl-menu > a' ).removeClass( 'rcl-bttn__active' );

	jQuery( '#' + viewBox + '-options-box' ).addClass( 'active' );
	jQuery( e ).addClass( 'rcl-bttn__active' );

	rcl_update_history_url( label.attr( 'href' ) );

	jQuery( '.rcl-options .active-menu-item .rcl-bttn__text' ).text( label.children( 'span.rcl-bttn__text' ).text() );
	jQuery( '.rcl-options .rcl-menu' ).removeClass( 'active-menu' );

}

function rcl_show_options_menu( e ) {
	jQuery( '.rcl-options .rcl-menu' ).addClass( 'active-menu' );
}