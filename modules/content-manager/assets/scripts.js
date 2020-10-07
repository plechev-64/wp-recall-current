
function rcl_table_manager_state( classname, state ) {

}

function rcl_content_manager_submit( e ) {

	var isAjax = parseInt( jQuery( e ).parents( 'form' ).find( '#value-ajax' ).val() );

	if ( isAjax ) {

		rcl_load_content_manager( e );

		return false;

	} else {

		if ( e && jQuery( e ).parents( '.preloader-parent' ) ) {
			rcl_preloader_show( jQuery( e ).parents( '.preloader-parent' ) );
		}

		rcl_submit_form( e );

	}

}

function rcl_init_on_change_select_templates_filter() {

	jQuery( 'body' ).on( 'change', '#rcl-templates-manager select', function() {
		rcl_content_manager_submit( this );
	} );

}

function rcl_table_manager_search_by_col( e, key, submit ) {

	if ( key != 'Enter' )
		return;

	jQuery( e ).parents( 'form' ).find( '#value-pagenum' ).val( 1 );

	rcl_content_manager_submit( e );

}

function rcl_load_content_manager_page( dataval, postname, e ) {

	jQuery( e ).parents( 'form' ).find( '#value-' + postname ).val( jQuery( e ).data( dataval ) );

	rcl_content_manager_submit( e );

}

function rcl_order_table_manager_page( e ) {

	var order = jQuery( e ).data( 'order' );

	var nextorder = ( order == 'desc' ) ? 'asc' : 'desc';

	jQuery( e ).attr( 'data-order', nextorder );

	var form = jQuery( e ).parents( 'form' );

	form.find( '#value-order' ).val( nextorder );
	form.find( '#value-orderby' ).val( jQuery( e ).data( 'col' ) );

	form.find( '#value-pagenum' ).val( 1 );

	rcl_content_manager_submit( e );

}

function rcl_table_manager_prev( prevData, e ) {

	var FormFactory = new RclForm( jQuery( e ).parents( 'form' ) );

	rcl_ajax( {
		data: prevData,
		success: function( result ) {
			rcl_proccess_ajax_return( result );

			FormFactory.form.find( '.rcl-content-manager' ).replaceWith( result.content );
		}
	} );

}

function rcl_load_content_manager( e, props ) {

	//получаем данные формы
	var FormFactory = new RclForm( jQuery( e ).parents( 'form' ) );

	if ( props != 'undefined' && props ) {

		rcl_ajax( {
			data: {
				action: 'rcl_load_content_manager',
				classname: props.classname,
				classargs: props.classargs,
				tail: props.tail,
				prevs: props.prevs
			},
			success: function( result ) {
				rcl_proccess_ajax_return( result );

				FormFactory.form.find( '.rcl-content-manager' ).replaceWith( result.content );
			}
		} );

	} else {

		//проверяем на правильность заполнения
		if ( !FormFactory.validate() )
			return false;

		FormFactory.send( 'rcl_load_content_manager', function( result ) {

			rcl_proccess_ajax_return( result );

			FormFactory.form.find( '.rcl-content-manager' ).replaceWith( result.content );

		}, true );

	}

}

function rcl_load_content_manager_state( state, e ) {

	//получаем данные формы
	var FormFactory = new RclForm( jQuery( e ).parents( 'form' ) );

	if ( e && jQuery( e ).parents( '.preloader-parent' ) ) {
		rcl_preloader_show( jQuery( e ).parents( '.preloader-parent' ) );
	}

	rcl_ajax( {
		rest: true,
		data: {
			action: 'rcl_load_content_manager_state',
			state: state,
		},
		success: function( result ) {
			rcl_proccess_ajax_return( result );
			FormFactory.form.find( '.rcl-content-manager' ).replaceWith( result.content );
		}
	} );


}

function rcl_save_table_manager_cols( e ) {

	var form = jQuery( '#rcl-cols-manager .active-cols input' );

	rcl_ajax( {
		rest: {
			action: 'rcl_save_table_manager_cols'
		},
		data: form.serialize() + '&action=rcl_save_table_manager_cols'
	} );
}

function rcl_get_table_manager_cols( managerId, cols, active_cols, disabled_cols, e ) {

	rcl_ajax( {
		rest: true,
		data: {
			action: 'rcl_get_table_manager_cols',
			manager_id: managerId,
			cols: cols,
			active_cols: active_cols,
			disabled_cols: disabled_cols
		}
	} );

}

