<?php

require_once 'register.php';
require_once 'authorize.php';
require_once 'shortcodes.php';
if ( class_exists( 'ReallySimpleCaptcha' ) ) {
	require_once 'captcha.php';
}
function rcl_loginform_scripts() {
	rcl_enqueue_style( 'rcl-loginform', RCL_URL . 'modules/loginform/assets/style.css' );
	rcl_enqueue_script( 'rcl-loginform', RCL_URL . 'modules/loginform/assets/scripts.js' );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_loginform_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_loginform_scripts', 10 );
}
function rcl_call_loginform() {
	global $user_ID;

	if ( $user_ID )
		return [
			'error' => __( 'Вы уже авторизованы!', 'wp-recall' )
		];

	return [
		'dialog' => [
			'size'		 => 'smallToMedium',
			'content'	 => rcl_get_loginform()
		]
	];
}

rcl_ajax_action( 'rcl_send_loginform', true );
function rcl_send_loginform() {

	rcl_verify_ajax_nonce();

	$tab_id		 = $_POST['tab_id'];
	$user_login	 = sanitize_user( $_POST['user_login'] );

	if ( $tab_id == 'login' ) {

		$password = sanitize_text_field( $_POST['user_pass'] );

		$user = wp_signon( array(
			'user_login'	 => $user_login,
			'user_password'	 => $password,
			'remember'		 => isset( $_POST['remember'] ) ? true : false,
			) );

		if ( is_wp_error( $user ) ) {
			wp_send_json( array(
				'error' => $user->get_error_message()
			) );
		}

		wp_send_json( array(
			'success' => __( 'Успешная авторизация', 'usp' )
		) );
	} else if ( $tab_id == 'register' ) {

		$user_email = sanitize_email( $_POST['user_email'] );

		$user_id = register_new_user( $user_login, $user_email );

		if ( is_wp_error( $user_id ) ) {
			wp_send_json( array(
				'error' => $user_id->get_error_message()
			) );
		}

		wp_send_json( array(
			'success' => __( 'Успешная регистрация', 'usp' )
		) );
	} else if ( $tab_id == 'lostpassword' ) {

		$result = retrieve_password();

		if ( is_wp_error( $result ) ) {
			wp_send_json( array(
				'error' => $result->get_error_message()
			) );
		}

		wp_send_json( array(
			'success' => __( 'Ссылка на восстановление выслана на почту', 'usp' )
		) );
	}
}

add_filter( 'rcl_login_form_fields', 'rcl_add_login_form_custom_data', 10 );
function rcl_add_login_form_custom_data( $fields ) {

	ob_start();

	do_action( 'login_form' );

	$content = ob_get_contents();
	ob_end_clean();

	if ( ! $content )
		return $fields;

	$fields[] = array(
		'slug'		 => 'custom_data',
		'type'		 => 'custom',
		'content'	 => $content
	);

	return $fields;
}

add_filter( 'rcl_login_form_fields', 'rcl_add_rememberme_button', 20 );
function rcl_add_rememberme_button( $fields ) {

	$fields[] = array(
		'slug'	 => 'rememberme',
		'type'	 => 'checkbox',
		'icon'	 => 'fa-key',
		'values' => array(
			1 => __( 'Запомнить меня', 'usp' )
		)
	);

	return $fields;
}

add_filter( 'rcl_register_form_fields', 'rcl_add_register_form_custom_data', 10 );
function rcl_add_register_form_custom_data( $fields ) {

	ob_start();

	do_action( 'register_form' );

	$content = ob_get_contents();
	ob_end_clean();

	if ( ! $content )
		return $fields;

	$fields[] = array(
		'slug'		 => 'custom_data',
		'type'		 => 'custom',
		'content'	 => $content
	);

	return $fields;
}

add_filter( 'rcl_lostpassword_form_fields', 'rcl_add_lostpassword_form_custom_data', 10 );
function rcl_add_lostpassword_form_custom_data( $fields ) {

	ob_start();

	do_action( 'lostpassword_form' );

	$content = ob_get_contents();
	ob_end_clean();

	if ( ! $content )
		return $fields;

	$fields[] = array(
		'slug'		 => 'custom_data',
		'type'		 => 'custom',
		'content'	 => $content
	);

	return $fields;
}
