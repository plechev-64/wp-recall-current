Rcl.modules.push( 'forms' );

function RclForm( form ) {

	this.form = form;
	this.errors = { };

	this.validate = function() {

		var valid = true;

		for ( var objKey in this.checkForm ) {

			var chekObject = this.checkForm[objKey];

			if ( !chekObject.isValid.call( this ) ) {

				valid = false;

				break;

			}

		}
		;

		if ( this.errors ) {
			for ( var k in this.errors ) {
				this.showError( this.errors[k] );
			}
			;
		}

		return valid;

	};

	this.addChekForm = function( id, data ) {
		this.checkForm[id] = data;
	};

	this.addChekFields = function( id, data ) {
		this.checkFields[id] = data;
	};

	this.addError = function( id, error ) {
		this.errors[id] = error;
	};

	this.shake = function( shakeBox ) {
		shakeBox.css( 'box-shadow', 'red 0px 0px 5px 1px inset' ).animateCss( 'shake' );
	};

	this.noShake = function( shakeBox ) {
		shakeBox.css( 'box-shadow', 'none' );
	};

	this.showError = function( error ) {
		rcl_notice( error, 'error', 10000 );
	};

	this.checkForm = {
		checkFields: {
			isValid: function() {

				var valid = true;
				var parent = this;

				this.form.find( 'input,select,textarea' ).each( function() {

					var field = jQuery( this );
					var typeField = field.attr( 'type' );

					if ( field.is( 'textarea' ) ) {
						typeField = 'textarea';
					}

					var checkFields = rcl_apply_filters( 'rcl_form_check_rules', parent.checkFields, parent );

					for ( var objKey in checkFields ) {

						var chekObject = checkFields[objKey];

						if ( chekObject.types.length && jQuery.inArray( typeField, chekObject.types ) < 0 ) {
							continue;
						}

						var shakeBox = jQuery.inArray( typeField, [ 'radio',
							'checkbox' ] ) < 0 ? field : field.next( 'label' );

						if ( !chekObject.isValid( field ) ) {

							parent.shake( shakeBox );
							parent.addError( objKey, chekObject.errorText() );
							valid = false;
							return;

						} else {
							parent.noShake( shakeBox );
						}

					}
					;

				} );

				return valid;

			}

		}

	};

	this.checkFields = {
		required: {
			types: [ ],
			isValid: function( field ) {

				var required = true;

				if ( !field.is( ":required" ) )
					return required;

				if ( field.is( ":disabled" ) )
					return required;

				var value = false;

				if ( field.attr( 'type' ) == 'checkbox' ) {
					if ( field.is( ":checked" ) )
						value = true;
				} else if ( field.attr( 'type' ) == 'radio' ) {
					if ( jQuery( 'input[name="' + field.attr( 'name' ) + '"]:checked' ).val() )
						value = true;
				} else {
					if ( field.val() )
						value = true;
				}

				if ( !value ) {
					required = false;
				}

				return required;

			},
			errorText: function() {
				return Rcl.errors.required;
			}


		},
		numberRange: {
			types: [ 'number' ],
			isValid: function( field ) {
				var range = true;

				var val = field.val();

				if ( val === '' )
					return true;

				val = parseInt( val );
				var min = parseInt( field.attr( 'min' ) );
				var max = parseInt( field.attr( 'max' ) );

				if ( min != 'undefined' && min > val || max != 'undefined' && max < val ) {
					range = false;
				}

				return range;
			},
			errorText: function() {
				return Rcl.errors.number_range;
			}

		},
		pattern: {
			types: [ 'text', 'tel' ],
			isValid: function( field ) {

				var val = field.val();

				if ( !val )
					return true;

				var pattern = field.attr( 'pattern' );

				if ( !pattern )
					return true;

				var re = new RegExp( pattern );

				return re.test( val );
			},
			errorText: function() {
				return Rcl.errors.pattern;
			}

		},
		fileMaxSize: {
			types: [ 'file' ],
			isValid: function( field ) {

				var valid = true;

				field.each( function() {

					var maxsize = jQuery( this ).data( "size" );
					var fileInput = jQuery( this )[0];
					var file = fileInput.files[0];

					if ( !file )
						return;

					var filesize = file.size / 1024 / 1024;

					if ( filesize > maxsize ) {
						valid = false;
						return;
					}

				} );

				return valid;
			},
			errorText: function() {
				return Rcl.errors.file_max_size;
			}

		},
		fileAccept: {
			types: [ 'file' ],
			isValid: function( field ) {

				var valid = true;

				field.each( function() {

					var fileInput = jQuery( this )[0];
					var file = fileInput.files[0];
					var accept = fileInput.accept.split( ',' );

					if ( !file )
						return;

					if ( accept ) {

						var fileType = false;

						if ( file.type ) {

							for ( var i in accept ) {
								if ( accept[i] == file.type ) {
									fileType = true;
									return;
								}
							}

						}

						var exts = jQuery( this ).data( "ext" );

						if ( !exts )
							return;

						if ( !fileType ) {

							var exts = exts.split( ',' );
							var filename = file.name;

							for ( var i in exts ) {
								if ( filename.indexOf( '.' + exts[i] ) + 1 ) {
									fileType = true;
									return;
								}
							}

						}

						if ( !fileType ) {
							valid = false;
							return;
						}

					}

				} );

				return valid;
			},
			errorText: function() {
				return Rcl.errors.file_accept;
			}

		}
	};

	this.send = function( action, success, rest ) {

		if ( !this.validate() )
			return false;

		var rest = rest ? {
			action: action
		} : false;

		rcl_preloader_show( form );

		var sendData = {
			rest: rest,
			data: form.serialize() + '&action=' + action
		};

		if ( success ) {
			sendData.success = success;
		}

		rcl_ajax( sendData );

	};

}

function rcl_chek_form_field( e ) {

	var field = jQuery( e );

	var rclFormFactory = new RclForm( field.parents( 'form' ) );

	var result = rclFormFactory.validate( {
		check_fields: [ field.data( 'slug' ) ]
	} );

	return result;

}

function rcl_submit_form( e ) {

	var form = jQuery( e ).parents( 'form' );

	if ( rcl_check_form( form ) )
		form.submit();

}

function rcl_send_form_data( action, e ) {

	var form = jQuery( e ).parents( 'form' );

	if ( !rcl_check_form( form ) )
		return false;

	if ( e && jQuery( e ).parents( '.preloader-parent' ) ) {
		rcl_preloader_show( jQuery( e ).parents( '.preloader-parent' ) );
	}

	if ( typeof tinyMCE !== 'undefined' )
		tinyMCE.triggerSave();

	rcl_ajax( {
		data: form.serialize() + '&action=' + action
	} );

}

function rcl_check_form( form ) {

	var rclFormFactory = new RclForm( form );

	return rclFormFactory.validate();

}


