Rcl.modules.push( 'fields' );

rcl_add_action( 'rcl_init', 'rcl_init_update_requared_checkbox' );
function rcl_init_update_requared_checkbox() {

	jQuery( 'body form' ).find( '.required-checkbox' ).each( function() {
		rcl_update_require_checkbox( this );
	} );

	jQuery( 'body form' ).on( 'click', '.required-checkbox', function() {
		rcl_update_require_checkbox( this );
	} );

}

function rcl_add_dynamic_field( e ) {
	var parent = jQuery( e ).parents( '.dynamic-value' );
	var box = parent.parent( '.dynamic-values' );
	var html = parent.html();
	box.append( '<span class="dynamic-value">' + html + '</span>' );
	jQuery( e ).attr( 'onclick', 'rcl_remove_dynamic_field(this);return false;' ).children( 'i' ).toggleClass( "fa-plus fa-minus" );
	box.children( 'span' ).last().children( 'input' ).val( '' ).focus();
}

function rcl_remove_dynamic_field( e ) {
	jQuery( e ).parents( '.dynamic-value' ).remove();
}

function rcl_update_require_checkbox( e ) {
	var name = jQuery( e ).attr( 'name' );
	var chekval = jQuery( 'form input[name="' + name + '"]:checked' ).val();
	if ( chekval )
		jQuery( 'form input[name="' + name + '"]' ).attr( 'required', false );
	else
		jQuery( 'form input[name="' + name + '"]' ).attr( 'required', true );
}

function rcl_setup_datepicker_options() {

	jQuery.datepicker.setDefaults( jQuery.extend( jQuery.datepicker.regional["ru"] ) );

	var options = {
		monthNames: [ "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
			"Июль",
			"Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь" ],
		dayNamesMin: [ "Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб" ],
		firstDay: 1,
		dateFormat: 'yy-mm-dd',
		yearRange: "1950:c+3",
		changeYear: true
	};

	options = rcl_apply_filters( 'rcl_datepicker_options', options );

	return options;

}

function rcl_show_datepicker( e ) {
	jQuery( e ).datepicker( rcl_setup_datepicker_options() );
	jQuery( e ).datepicker( "show" );
	rcl_add_action( 'rcl_upload_tab', 'rcl_remove_datepicker_box' );
}

function rcl_remove_datepicker_box() {
	jQuery( '#ui-datepicker-div' ).remove();
}

function rcl_init_field_file( field_id ) {
	jQuery( "#" + field_id ).parents( 'form' ).attr( "enctype", "multipart/form-data" );
}

function rcl_init_runner( props ) {

	var box = jQuery( '#rcl-runner-' + props.id );

	box.children( '.rcl-runner-box' ).slider( {
		value: parseInt( props.value ),
		min: parseInt( props.min ),
		max: parseInt( props.max ),
		step: parseInt( props.step ),
		create: function( event, ui ) {
			var value = box.children( '.rcl-runner-box' ).slider( 'value' );
			box.children( '.rcl-runner-value' ).text( value );
			box.children( '.rcl-runner-field' ).val( value );
		},
		slide: function( event, ui ) {
			box.find( '.rcl-runner-value' ).text( ui.value );
			box.find( '.rcl-runner-field' ).val( ui.value );
		}
	} );
}

function rcl_init_range( props ) {

	var box = jQuery( '#rcl-range-' + props.id );

	box.children( '.rcl-range-box' ).slider( {
		range: true,
		values: [ parseInt( props.values[0] ), parseInt( props.values[1] ) ],
		min: parseInt( props.min ),
		max: parseInt( props.max ),
		step: parseInt( props.step ),
		create: function( event, ui ) {
			var values = box.children( '.rcl-range-box' ).slider( 'values' );
			box.children( '.rcl-range-value' ).text( values[0] + ' - ' + values[1] );
			box.children( '.rcl-range-min' ).val( values[0] );
			box.children( '.rcl-range-max' ).val( values[1] );
		},
		slide: function( event, ui ) {
			box.children( '.rcl-range-value' ).text( ui.values[0] + ' - ' + ui.values[1] );
			box.find( '.rcl-range-min' ).val( ui.values[0] );
			box.find( '.rcl-range-max' ).val( ui.values[1] );
		}
	} );
}

function rcl_init_color( id, props ) {
	jQuery( "#" + id ).wpColorPicker( props );
}

function rcl_init_field_maxlength( fieldID ) {

	var field = jQuery( '#' + fieldID );
	var maxlength = field.attr( 'maxlength' );

	if ( !field.parent().find( '.maxlength' ).length ) {

		if ( field.val() ) {
			maxlength = maxlength - field.val().length;
		}

		field.after( '<span class="maxlength">' + maxlength + '</span>' );
	}

	field.on( 'keyup', function() {
		var maxlength = jQuery( this ).attr( 'maxlength' );
		if ( !maxlength )
			return false;
		var word = jQuery( this );
		var count = maxlength - word.val().length;
		jQuery( this ).next().text( count );
		if ( word.val().length > maxlength )
			word.val( word.val().substr( 0, maxlength ) );
	} );
}

function rcl_init_ajax_editor( id, options ) {

	if ( typeof QTags === 'undefined' )
		return false;

	rcl_do_action( 'rcl_pre_init_ajax_editor', {
		id: id,
		options: options
	} );

	var qt_options = {
		id: id,
		buttons: ( options.qt_buttons ) ? options.qt_buttons : "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
	};

	QTags( qt_options );

	QTags._buttonsInit();

	if ( options.tinymce && typeof tinyMCEPreInit != 'undefined' ) {

		tinyMCEPreInit.qtInit[id] = qt_options;

		tinyMCEPreInit.mceInit[id] = {
			body_class: id,
			selector: '#' + id,
			menubar: false,
			skin: "lightgray",
			theme: 'modern',
			toolbar1: "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv",
			toolbar2: "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
			wpautop: true
		};

		tinymce.init( tinyMCEPreInit.mceInit[id] );
		tinyMCE.execCommand( 'mceAddEditor', true, id );

		switchEditors.go( id, 'html' );
	}

}

function rcl_setup_quicktags( newTags ) {

	if ( typeof QTags === 'undefined' )
		return false;

	newTags.forEach( function( tagArray, i, newTags ) {

		QTags.addButton(
			tagArray[0],
			tagArray[1],
			tagArray[2],
			tagArray[3],
			tagArray[4],
			tagArray[5],
			tagArray[6]
			);

	} );

}

rcl_add_action( 'rcl_pre_init_ajax_editor', 'rcl_add_ajax_quicktags' );
function rcl_add_ajax_quicktags( editor ) {

	if ( typeof Rcl === 'undefined' || !Rcl.QTags )
		return false;

	rcl_setup_quicktags( Rcl.QTags );

}

rcl_add_action( 'rcl_footer', 'rcl_add_quicktags' );
function rcl_add_quicktags() {

	if ( typeof Rcl === 'undefined' || !Rcl.QTags )
		return false;

	rcl_setup_quicktags( Rcl.QTags );

}

function rcl_init_iconpicker() {
	jQuery( '.rcl-iconpicker' ).iconpicker();
}