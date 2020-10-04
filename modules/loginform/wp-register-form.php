<?php

//добавляем коды ошибок для тряски формы ВП
add_filter( 'shake_error_codes', 'rcl_add_shake_error_codes' );
function rcl_add_shake_error_codes( $codes ) {
	return array_merge( $codes, array(
		'rcl_register_login',
		'rcl_register_empty',
		'rcl_register_email',
		'rcl_register_login_us',
		'rcl_register_email_us'
		) );
}

add_filter( 'wp_login_errors', 'rcl_checkemail_success' );
function rcl_checkemail_success( $errors ) {

	if ( isset( $_GET['success'] ) && $_GET['success'] == 'checkemail' ) {

		$errors = new WP_Error();
		$errors->add( 'checkemail', __( 'Your email has been successfully confirmed! Log in using your username and password', 'wp-recall' ), 'message' );
	}

	if ( isset( $_GET['register'] ) ) {

		$errors = new WP_Error();

		if ( $_GET['register'] == 'success' ) {

			$errors->add( 'register', __( 'Registration completed!', 'wp-recall' ), 'message' );
		}

		if ( $_GET['register'] == 'checkemail' ) {

			$errors->add( 'register', __( 'Registration is completed! Check your email for the confirmation link.', 'wp-recall' ), 'message' );
		}
	}

	if ( isset( $_GET['login'] ) ) {

		$errors = new WP_Error();

		if ( $_GET['login'] == 'checkemail' ) {

			$errors->add( 'register', __( 'Your email is not confirmed!', 'wp-recall' ), 'error' );
		}
	}

	if ( isset( $_GET['remember'] ) ) {

		$errors = new WP_Error();

		if ( $_GET['remember'] == 'success' ) {

			$errors->add( 'register', __( 'Your password has been sent!<br>Check your email.', 'wp-recall' ), 'message' );
		}
	}

	return $errors;
}

add_action( 'register_form', 'rcl_add_register_fields_to_register_form', 10 );
function rcl_add_register_fields_to_register_form() {

	$fields = rcl_get_register_form_fields();

	foreach ( $fields as $k => $field ) {
		if ( $field['slug'] == 'user_email' ) {
			unset( $fields[$k] );
		}
	}

	RCL()->use_module( 'forms' );

	$form = new Rcl_Form( [
		'fields' => $fields
		] );

	echo $form->get_fields_list();
}
