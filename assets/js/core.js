var rcl_actions =  typeof rcl_actions === 'undefined'? [ ]: rcl_actions;
var rcl_filters = typeof rcl_filters === 'undefined'? [ ]: rcl_filters;
var rcl_beats = [ ];
var rcl_beats_delay = 0;
var rcl_url_params = rcl_get_value_url_params();

jQuery( document ).ready( function( $ ) {

	$.fn.extend( {
		insertAtCaret: function( myValue ) {
			return this.each( function( i ) {
				if ( document.selection ) {
					// Для браузеров типа Internet Explorer
					this.focus();
					var sel = document.selection.createRange();
					sel.text = myValue;
					this.focus();
				} else if ( this.selectionStart || this.selectionStart == '0' ) {
					// Для браузеров типа Firefox и других Webkit-ов
					var startPos = this.selectionStart;
					var endPos = this.selectionEnd;
					var scrollTop = this.scrollTop;
					this.value = this.value.substring( 0, startPos ) + myValue + this.value.substring( endPos, this.value.length );
					this.focus();
					this.selectionStart = startPos + myValue.length;
					this.selectionEnd = startPos + myValue.length;
					this.scrollTop = scrollTop;
				} else {
					this.value += myValue;
					this.focus();
				}
			} )
		},
		animateCss: function( animationNameStart, functionEnd ) {
			var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
			this.addClass( 'animated ' + animationNameStart ).one( animationEnd, function() {
				jQuery( this ).removeClass( 'animated ' + animationNameStart );

				if ( functionEnd ) {
					if ( typeof functionEnd == 'function' ) {
						functionEnd( this );
					} else {
						jQuery( this ).animateCss( functionEnd );
					}
				}
			} );
			return this;
		}
	} );

} );

function rcl_do_action( action_name ) {

	var callbacks_action = rcl_actions[action_name];
	
	if ( !callbacks_action )
		return false;

	var args = [ ].slice.call( arguments, 1 );

	callbacks_action.forEach( function( callback, i, callbacks_action ) {
		if ( window[callback] )
			window[callback].apply( this, args );
		if ( typeof callback === 'function' )
			callback.apply( this, args );
	} );
}

function rcl_add_action( action_name, callback ) {
	if ( !rcl_actions[action_name] ) {
		rcl_actions[action_name] = [ callback ];
	} else {
		var i = rcl_actions[action_name].length;
		rcl_actions[action_name][i] = callback;
	}
}

function rcl_apply_filters( filter_name ) {

	var args = [ ].slice.call( arguments, 1 );

	var callbacks_filter = rcl_filters[filter_name];

	if ( !callbacks_filter )
		return args[0];

	callbacks_filter.forEach( function( callback, i, callbacks_filter ) {
		args[0] = window[callback].apply( this, args );
	} );

	return args[0];
}

function rcl_add_filter( filter_name, callback ) {
	if ( !rcl_filters[filter_name] ) {
		rcl_filters[filter_name] = [ callback ];
	} else {
		var i = rcl_filters[filter_name].length;
		rcl_filters[filter_name][i] = callback;
	}
}

function rcl_get_value_url_params() {
	var tmp_1 = new Array();
	var tmp_2 = new Array();
	var rcl_url_params = new Array();
	var get = location.search;
	if ( get !== '' ) {
		tmp_1 = ( get.substr( 1 ) ).split( '&' );
		for ( var i = 0; i < tmp_1.length; i++ ) {
			tmp_2 = tmp_1[i].split( '=' );
			rcl_url_params[tmp_2[0]] = tmp_2[1];
		}
	}

	return rcl_url_params;
}

function rcl_is_valid_url( url ) {
	var objRE = /http(s?):\/\/[-\w\.]{3,}\.[A-Za-z]{2,3}/;
	return objRE.test( url );
}

function setAttr_rcl( prmName, val ) {
	var res = '';
	var d = location.href.split( "#" )[0].split( "?" );
	var base = d[0];
	var query = d[1];
	if ( query ) {
		var params = query.split( "&" );
		for ( var i = 0; i < params.length; i++ ) {
			var keyval = params[i].split( "=" );
			if ( keyval[0] !== prmName ) {
				res += params[i] + '&';
			}
		}
	}
	res += prmName + '=' + val;
	return base + '?' + res;
}

function rcl_update_history_url( url ) {

	if ( url != window.location ) {
		if ( history.pushState ) {
			window.history.pushState( null, null, url );
		}
	}

}

function rcl_init_cookie() {

	jQuery.cookie = function( name, value, options ) {
		if ( typeof value !== 'undefined' ) {
			options = options || { };
			if ( value === null ) {
				value = '';
				options.expires = -1;
			}
			var expires = '';
			if ( options.expires && ( typeof options.expires === 'number' || options.expires.toUTCString ) ) {
				var date;
				if ( typeof options.expires === 'number' ) {
					date = new Date();
					date.setTime( date.getTime() + ( options.expires * 24 * 60 * 60 * 1000 ) );
				} else {
					date = options.expires;
				}
				expires = '; expires=' + date.toUTCString();
			}
			var path = options.path ? '; path=' + ( options.path ) : '';
			var domain = options.domain ? '; domain=' + ( options.domain ) : '';
			var secure = options.secure ? '; secure' : '';
			document.cookie = [ name, '=', encodeURIComponent( value ),
				expires, path,
				domain, secure ].join( '' );
		} else {
			var cookieValue = null;
			if ( document.cookie && document.cookie !== '' ) {
				var cookies = document.cookie.split( ';' );
				for ( var i = 0; i < cookies.length; i++ ) {
					var cookie = jQuery.trim( cookies[i] );
					if ( cookie.substring( 0, name.length + 1 ) === ( name + '=' ) ) {
						cookieValue = decodeURIComponent( cookie.substring( name.length + 1 ) );
						break;
					}
				}
			}
			return cookieValue;
		}
	};

}

function rcl_rand( min, max ) {
	if ( max ) {
		return Math.floor( Math.random() * ( max - min + 1 ) ) + min;
	} else {
		return Math.floor( Math.random() * ( min + 1 ) );
	}
}

function rcl_notice( text, type, time_close ) {

	time_close = time_close || false;

	var options = {
		text: text,
		type: type,
		time_close: time_close
	};

	options = rcl_apply_filters( 'rcl_notice_options', options );

	var notice_id = rcl_rand( 1, 1000 );

	var html = '<div id="notice-' + notice_id + '" class="notice-window type-' + options.type + '"><a href="#" class="close-notice"><i class="rcli fa-times"></i></a>' + options.text + '</div>';
	if ( !jQuery( '#rcl-notice' ).length ) {
		jQuery( 'body > div' ).last().after( '<div id="rcl-notice">' + html + '</div>' );
	} else {
		if ( jQuery( '#rcl-notice > div' ).length )
			jQuery( '#rcl-notice > div:last-child' ).after( html );
		else
			jQuery( '#rcl-notice' ).html( html );
	}

	jQuery( '#rcl-notice > div' ).last().animateCss( 'slideInLeft' );

	if ( time_close ) {
		setTimeout( function() {
			rcl_close_notice( '#rcl-notice #notice-' + notice_id )
		}, options.time_close );
	}
}

function rcl_close_notice( e ) {

	var timeCook = jQuery( e ).data( 'notice_time' );

	if ( timeCook ) {

		var idCook = jQuery( e ).data( 'notice_id' );
		var block = jQuery( e ).parents( '.rcl-notice' );

		jQuery( block ).animateCss( 'flipOutX', function() {
			jQuery( block ).remove();
		} );

		jQuery.cookie( idCook, '1', {
			expires: timeCook,
			path: '/'
		} );

	} else {

		jQuery( e ).animateCss( 'flipOutX', function( e ) {
			jQuery( e ).hide();
		} );

	}

	return false;
}

function rcl_preloader_show( e, size ) {

	var font_size = ( size ) ? size : 80;
	var margin = font_size / 2;

	var options = {
		size: font_size,
		margin: margin,
		icon: 'fa-circle-o-notch',
		class: 'rcl_preloader'
	};

	options = rcl_apply_filters( 'rcl_preloader_options', options );

	var style = 'style="font-size:' + options.size + 'px;margin: -' + options.margin + 'px 0 0 -' + options.margin + 'px;"';

	var html = '<div class="' + options.class + '"><i class="rcli ' + options.icon + ' fa-spin" ' + style + '></i></div>';

	if ( typeof ( e ) === 'string' )
		jQuery( e ).after( html );
	else
		e.append( html );
}

function rcl_preloader_hide() {
	jQuery( '.rcl_preloader' ).remove();
}

function rcl_proccess_ajax_return( result ) {

	var methods = {
		redirect: function( url ) {

			var urlData = url.split( '#' );

			if ( window.location.origin + window.location.pathname === urlData[0] ) {
				location.reload();
			} else {
				location.replace( url );
			}

		},
		reload: function() {
			location.reload();
		},
		current_url: function( url ) {
			rcl_update_history_url( url );
		},
		dialog: function( dialog ) {

			if ( dialog.content ) {

				if ( jQuery( '#ssi-modalContent' ).length )
					ssi_modal.close();

				var ssiOptions = {
					className: 'rcl-dialog-tab ' + ( dialog.class ? ' ' + dialog.class : '' ),
					sizeClass: dialog.size ? dialog.size : 'auto',
					content: dialog.content,
					buttons: [ ]
				};

				if ( dialog.buttons ) {
					ssiOptions.buttons = dialog.buttons;
				}

				var buttonClose = true;

				if ( 'buttonClose' in dialog ) {
					buttonClose = dialog.buttonClose;
				}

				if ( buttonClose ) {

					ssiOptions.buttons.push( {
						label: Rcl.local.close,
						closeAfter: true
					} );

				}

				if ( 'onClose' in dialog ) {
					ssiOptions.onClose = function( m ) {
						window[dialog.onClose[0]].apply( this, dialog.onClose[1] );
					};
				}

				if ( dialog.title )
					ssiOptions.title = dialog.title;

				ssi_modal.show( ssiOptions );

			}

			if ( dialog.close ) {
				ssi_modal.close();
			}

		}
	};

	for ( var method in result ) {
		if ( methods[method] ) {
			methods[method]( result[method] );
		}
	}

}

function rcl_ajax( prop ) {

	if ( prop.data.ask ) {
		if ( !confirm( prop.data.ask ) ) {
			rcl_preloader_hide();
			return false;
		}
	}

	if ( typeof Rcl != 'undefined' ) {
		if ( typeof prop.data === 'string' ) {
			prop.data += '&_wpnonce=' + Rcl.nonce;
		} else if ( typeof prop.data === 'object' ) {
			prop.data._wpnonce = Rcl.nonce;
		}
	}

	var action = 'rcl_ajax_call';
	var callback = false;
	if ( typeof prop.data === 'string' ) {

		var propData = prop.data.split( '&' );
		var newRequestArray = [ ];

		for ( var key in propData ) {
			if ( propData[key].split( "=" )[0] == 'action' ) {
				callback = propData[key].split( "=" )[1];
				newRequestArray.push( 'call_action=' + propData[key].split( "=" )[1] );
			} else {
				newRequestArray.push( propData[key] );
			}
		}

		prop.data = newRequestArray.join( '&' );

		Rcl.used_modules.forEach( function( module_id ) {
			prop.data += '&used_modules[]=' + module_id;
		} );

		prop.data += '&action=rcl_ajax_call';

	} else if ( typeof prop.data === 'object' ) {
		callback = prop.data.action;
		prop.data.used_modules = Rcl.used_modules;
		prop.data.action = action;
		prop.data.call_action = callback;
	}

	prop.rest = {
		action: action
	};

	var url;

	if ( prop.rest ) {

		var restAction = action;
		var restRoute = restAction;
		var restSpace = 'rcl';

		if ( typeof prop.rest === 'object' ) {

			if ( prop.rest.action )
				restAction = prop.rest.action;
			if ( prop.rest.space )
				restSpace = prop.rest.space;
			if ( prop.rest.route )
				restRoute = prop.rest.route;
			else
				restRoute = restAction;

		}

		if ( Rcl.permalink )
			url = Rcl.wpurl + '/wp-json/' + restSpace + '/' + restRoute + '/';
		else
			url = Rcl.wpurl + '/?rest_route=/' + restSpace + '/' + restRoute + '/';

	} else {

		url = ( typeof ajax_url !== 'undefined' ) ? ajax_url : Rcl.ajaxurl;

	}

	if ( typeof tinyMCE != 'undefined' )
		tinyMCE.triggerSave();

	jQuery.ajax( {
		type: 'POST',
		data: prop.data,
		dataType: 'json',
		url: url,
		success: function( result, post ) {

			var noticeTime = result.notice_time ? result.notice_time : 5000;

			if ( !result ) {
				rcl_notice( Rcl.local.error, 'error', noticeTime );
				return false;
			}

			if ( result.error || result.errors ) {

				rcl_preloader_hide();

				if ( result.errors ) {
					jQuery.each( result.errors, function( index, error ) {
						rcl_notice( error, 'error', noticeTime );
					} );
				} else {
					rcl_notice( result.error, 'error', noticeTime );
				}

				if ( prop.error )
					prop.error( result );

				return false;

			}

			if ( !result.preloader_live ) {
				rcl_preloader_hide();
			}

			if ( result.success ) {
				rcl_notice( result.success, 'success', noticeTime );
			}

			if ( result.warning ) {
				rcl_notice( result.warning, 'warning', noticeTime );
			}

			rcl_do_action( 'rcl_ajax_success', result );

			if ( prop.success ) {

				prop.success( result );

			} else {

				rcl_proccess_ajax_return( result );

			}

			if ( prop.afterSuccess ) {

				prop.afterSuccess( result );

			}

			rcl_do_action( callback, result );

			if ( result.used_modules ) {
				Rcl.used_modules = result.used_modules;
			}

		}
	} );

}

function rcl_add_beat( beat_name, delay, data ) {

	delay = ( delay < 10 ) ? 10 : delay;

	var data = ( data ) ? data : false;

	var i = rcl_beats.length;

	rcl_beats[i] = {
		beat_name: beat_name,
		delay: delay,
		data: data
	};

}

function rcl_remove_beat( beat_name ) {

	if ( !rcl_beats )
		return false;

	var remove = false;
	var all_beats = rcl_beats;

	all_beats.forEach( function( beat, index, all_beats ) {
		if ( beat.beat_name != beat_name )
			return;
		delete rcl_beats[index];
		remove = true;
	} );

	return remove;

}

function rcl_exist_beat( beat_name ) {

	if ( !rcl_beats )
		return false;

	var exist = false;

	rcl_beats.forEach( function( beat, index, rcl_beats ) {
		if ( beat.beat_name != beat_name )
			return;
		exist = true;
	} );

	return exist;

}

/** new uploader scripts **/

/** new uploader scripts end **/