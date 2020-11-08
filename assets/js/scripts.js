
jQuery( function() {
	rcl_do_action( 'rcl_init' );
} );

rcl_add_action( 'rcl_init', 'rcl_init_cookie' );

jQuery( window ).load( function() {
	jQuery( 'body' ).on( 'drop', function() {
		return false;
	} );
	jQuery( document.body ).bind( "drop", function( e ) {
		e.preventDefault();
	} );
} );

function rcl_load_tab( tab_id, subtab_id, page, e ) {

	var button = jQuery( e );

	rcl_do_action( 'rcl_before_upload_tab', button );

	rcl_preloader_show( jQuery( '#rcl-tab-content' ) );

	let data = {
		action: 'rcl_load_tab',
		tab_id: tab_id,
		subtab_id: subtab_id,
		office_id: Rcl.office_ID
	};

	/* support old pager */
	if(pagerKey = button.data('pager-key')){
		data[pagerKey] = button.data('page');
		data['pager-id'] = button.data('pager-id')
	}

	rcl_ajax( {
		rest: true,
		data: data,
		success: function( data ) {

			data = rcl_apply_filters( 'rcl_upload_tab', data );

			if ( data.error ) {
				rcl_notice( data.error, 'error', 10000 );
				return false;
			}

			var supports = data.supports;
			var subtab_id = data.subtab_id;
			var box_id = '';

			if ( supports && supports.indexOf( 'dialog' ) >= 0 ) { //если вкладка поддерживает диалог

				if ( !subtab_id ) { //если загружается основная вкладка

					ssi_modal.show( {
						className: 'rcl-dialog-tab ' + data.tab_id,
						sizeClass: 'small',
						buttons: [ {
								label: Rcl.local.close,
								closeAfter: true
							} ],
						content: data.content
					} );

				} else {

					box_id = '#ssi-modalContent';

				}

			} else {

				rcl_update_history_url( data.tab_url );

				if ( !subtab_id )
					jQuery( '.rcl-tabs-menu a' ).removeClass( 'rcl-bttn__active' );

				button.addClass( 'rcl-bttn__active' );

				box_id = '#rcl-tab-content';

			}

			if ( box_id ) {

				jQuery( box_id ).html( data.content );

				var options = rcl_get_options_url_params();

				if ( options.scroll === 1 ) {
					var offsetTop = jQuery( box_id ).offset().top;
					jQuery( 'body,html' ).animate( {
						scrollTop: offsetTop - options.offset
					},
						1000 );
				}

				if ( data.includes ) {

					var includes = data.includes;

					includes.forEach( function( src ) {

						jQuery.getScript( src );

					} );

				}

			}

			jQuery( box_id ).animateCss( 'fadeIn' );
			
			rcl_do_action( 'rcl_upload_tab', {
				element: button,
				result: data
			} );

		}
	} );

}

function rcl_get_options_url_params() {

	var options = {
		scroll: 1,
		offset: 100
	};

	options = rcl_apply_filters( 'rcl_options_url_params', options );

	return options;
}

function rcl_add_dropzone( idzone ) {

	jQuery( document.body ).bind( "drop", function( e ) {
		var dropZone = jQuery( idzone ),
			node = e.target,
			found = false;

		if ( dropZone[0] ) {
			dropZone.removeClass( 'in hover' );
			do {
				if ( node === dropZone[0] ) {
					found = true;
					break;
				}
				node = node.parentNode;
			} while ( node != null );

			if ( found ) {
				e.preventDefault();
			} else {
				return false;
			}
		}
	} );

	jQuery( idzone ).bind( 'dragover', function( e ) {
		var dropZone = jQuery( idzone ),
			timeout = window.dropZoneTimeout;

		if ( !timeout ) {
			dropZone.addClass( 'in' );
		} else {
			clearTimeout( timeout );
		}

		var found = false,
			node = e.target;

		do {
			if ( node === dropZone[0] ) {
				found = true;
				break;
			}
			node = node.parentNode;
		} while ( node != null );

		if ( found ) {
			dropZone.addClass( 'hover' );
		} else {
			dropZone.removeClass( 'hover' );
		}

		window.dropZoneTimeout = setTimeout( function() {
			window.dropZoneTimeout = null;
			dropZone.removeClass( 'in hover' );
		}, 100 );
	} );
}

function passwordStrength( password ) {
	var desc = [
		Rcl.local.pass0,
		Rcl.local.pass1,
		Rcl.local.pass2,
		Rcl.local.pass3,
		Rcl.local.pass4,
		Rcl.local.pass5
	];

	var score = 0;
	if ( password.length > 6 )
		score++;
	if ( ( password.match( /[a-z]/ ) ) && ( password.match( /[A-Z]/ ) ) )
		score++;
	if ( password.match( /\d+/ ) )
		score++;
	if ( password.match( /.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/ ) )
		score++;
	if ( password.length > 12 )
		score++;
	document.getElementById( "passwordDescription" ).innerHTML = desc[score];
	document.getElementById( "passwordStrength" ).className = "strength" + score;
}

function rcl_manage_user_black_list( e, user_id, confirmText ) {

	var class_i = jQuery( e ).children( 'i' ).attr( 'class' );

	if ( class_i === 'rcli fa-refresh fa-spin' )
		return false;

	if ( !confirm( confirmText ) )
		return false;

	jQuery( e ).children( 'i' ).attr( 'class', 'rcli fa-refresh fa-spin' );

	rcl_ajax( {
		data: {
			action: 'rcl_manage_user_black_list',
			user_id: user_id
		},
		success: function( data ) {

			jQuery( e ).children( 'i' ).attr( 'class', class_i );

			if ( data['label'] ) {
				jQuery( e ).find( 'span' ).text( data['label'] );
			}

		}
	} );

	return false;
}

function rcl_show_tab( id_block ) {
	jQuery( ".rcl-tab-button .recall-button" ).removeClass( "active" );
	jQuery( "#lk-content .recall_content_block" ).removeClass( "active" );
	jQuery( '#tab-button-' + id_block ).children( '.recall-button' ).addClass( "active" );
	jQuery( '#lk-content .' + id_block + '_block' ).addClass( "active" );
	return false;
}

rcl_add_action( 'rcl_init', 'rcl_init_recallbar_hover' );
function rcl_init_recallbar_hover() {
	jQuery( "#recallbar .menu-item-has-children" ).hover( function() {
		jQuery( this ).children( ".sub-menu" ).css( {
			'visibility': 'visible'
		} );
	}, function() {
		jQuery( this ).children( ".sub-menu" ).css( {
			'visibility': ''
		} );
	} );
}

/*rcl_add_action( 'rcl_before_upload_tab', 'rcl_add_class_upload_tab' );
 function rcl_add_class_upload_tab( e ) {
 e.addClass( 'tab-upload' );
 }*/

rcl_add_action( 'rcl_before_upload_tab', 'rcl_add_preloader_tab' );
function rcl_add_preloader_tab() {
	rcl_preloader_show( '#lk-content > div' );
	rcl_preloader_show( '#ssi-modalContent > div' );
}

rcl_add_action( 'rcl_init', 'rcl_init_get_smilies' );
function rcl_init_get_smilies() {
	jQuery( document ).on( {
		mouseenter: function() {
			var sm_box = jQuery( this ).next();
			var block = sm_box.children();
			sm_box.show();
			if ( block.html() )
				return false;
			block.html( Rcl.local.loading + '...' );
			var dir = jQuery( this ).data( 'dir' );

			rcl_ajax( {
				data: {
					action: 'rcl_get_smiles_ajax',
					area: jQuery( this ).parent().data( 'area' ),
					dir: dir ? dir : 0
				},
				success: function( data ) {
					if ( data['content'] ) {
						block.html( data['content'] );
					}
				}
			} );

		},
		mouseleave: function() {
			jQuery( this ).next().hide();
		}
	},
		"body .rcl-smiles .fa-smile-o" );
}

rcl_add_action( 'rcl_init', 'rcl_init_hover_smilies' );
function rcl_init_hover_smilies() {

	jQuery( document ).on( {
		mouseenter: function() {
			jQuery( this ).show();
		},
		mouseleave: function() {
			jQuery( this ).hide();
		}
	},
		"body .rcl-smiles > .rcl-smiles-list" );

	jQuery( 'body' ).on( 'hover click', '.rcl-smiles > img', function() {
		var block = jQuery( this ).next().children();
		if ( block.html() )
			return false;
		block.html( Rcl.local.loading + '...' );
		var dir = jQuery( this ).data( 'dir' );

		rcl_ajax( {
			data: {
				action: 'rcl_get_smiles_ajax',
				area: jQuery( this ).parent().data( 'area' ),
				dir: dir ? dir : 0
			},
			success: function( data ) {
				if ( data['content'] ) {
					block.html( data['content'] );
				}
			}
		} );

		return false;
	} );
}

rcl_add_action( 'rcl_init', 'rcl_init_click_smilies' );
function rcl_init_click_smilies() {
	jQuery( "body" ).on( "click", '.rcl-smiles-list img', function() {
		var alt = jQuery( this ).attr( "alt" );
		var area = jQuery( this ).parents( ".rcl-smiles" ).data( "area" );
		var box = jQuery( "#" + area );
		box.val( box.val() + " " + alt + " " );
	} );
}

rcl_add_action( 'rcl_init', 'rcl_init_close_popup' );
function rcl_init_close_popup() {
	jQuery( '#rcl-popup,.floatform' ).on( 'click', '.close-popup', function() {
		rcl_hide_float_login_form();
		jQuery( '#rcl-overlay' ).fadeOut();
		jQuery( '#rcl-popup' ).empty();
		return false;
	} );
}

rcl_add_action( 'rcl_init', 'rcl_init_click_overlay' );
function rcl_init_click_overlay() {
	jQuery( '#rcl-overlay' ).click( function() {
		rcl_hide_float_login_form();
		jQuery( '#rcl-overlay' ).fadeOut();
		jQuery( '#rcl-popup' ).empty();
		return false;
	} );
}

rcl_add_action( 'rcl_init', 'rcl_init_click_float_window' );
function rcl_init_click_float_window() {
	jQuery( ".float-window-recall" ).on( 'click', '.close', function() {
		jQuery( ".float-window-recall" ).remove();
		return false;
	} );
}

rcl_add_action( 'rcl_init', 'rcl_init_loginform_shift_tabs' );
function rcl_init_loginform_shift_tabs() {
	jQuery( 'body' ).on( 'click', '.form-tab-rcl .link-tab-rcl', function() {
		jQuery( '.form-tab-rcl' ).hide();

		if ( jQuery( this ).hasClass( 'link-login-rcl' ) )
			rcl_show_login_form_tab( 'login' );

		if ( jQuery( this ).hasClass( 'link-register-rcl' ) )
			rcl_show_login_form_tab( 'register' );

		if ( jQuery( this ).hasClass( 'link-remember-rcl' ) )
			rcl_show_login_form_tab( 'remember' );

		return false;
	} );
}

rcl_add_action( 'rcl_init', 'rcl_init_check_url_params' );
function rcl_init_check_url_params() {

	var options = rcl_get_options_url_params();

	if ( rcl_url_params['tab'] ) {
		var lkContent = jQuery( "#lk-content" );
		if ( !lkContent.length )
			return false;

		if ( options.scroll == 1 ) {
			var offsetTop = lkContent.offset().top;
			jQuery( 'body,html' ).animate( {
				scrollTop: offsetTop - options.offset
			},
				1000 );
		}

		var id_block = rcl_url_params['tab'];
		rcl_show_tab( id_block );
	}

}

rcl_add_action( 'rcl_init', 'rcl_init_close_notice' );
function rcl_init_close_notice() {
	jQuery( '#rcl-notice,body' ).on( 'click', 'a.close-notice', function() {
		rcl_close_notice( jQuery( this ).parent() );
		return false;
	} );
}

rcl_add_action( 'rcl_footer', 'rcl_beat' );
function rcl_beat() {

	var beats = rcl_apply_filters( 'rcl_beats', rcl_beats );

	var DataBeat = rcl_get_actual_beats_data( beats );

	if ( rcl_beats_delay && DataBeat.length ) {

		rcl_do_action( 'rcl_beat' );

		rcl_ajax( {
			data: {
				action: 'rcl_beat',
				databeat: JSON.stringify( DataBeat )
			},
			success: function( data ) {

				data.beat_result.forEach( function( result ) {

					rcl_do_action( 'rcl_beat_success_' + result['beat_name'] );

					new ( window[result['success']] )( result['result'] );

				} );

			}
		} );

	}

	rcl_beats_delay++;

	setTimeout( 'rcl_beat()', 1000 );
}

function rcl_get_actual_beats_data( beats ) {

	var beats_actual = [];

	if ( beats ) {

		beats.forEach( function( beat ) {
			var rest = rcl_beats_delay % beat.delay;
			if ( rest === 0 ) {

				var object = new ( window[beat.beat_name] )( beat.data );

				if ( object.data ) {

					object = rcl_apply_filters( 'rcl_beat_' + beat.beat_name, object );

					object.beat_name = beat.beat_name;

					var k = beats_actual.length;
					beats_actual[k] = object;
				}
			}
		} );

	}

	return beats_actual;

}
