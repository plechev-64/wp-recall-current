<?php

/**
 * авторизация пользователя
 */
function rcl_login_user() {
	global $wp_errors;

	$pass	 = sanitize_text_field( $_POST['user_pass'] );
	$login	 = sanitize_user( $_POST['user_login'] );
	$member	 = (isset( $_POST['rememberme'] )) ? intval( $_POST['rememberme'] ) : 0;
	$url	 = esc_url( $_POST['redirect_to'] );

	$wp_errors = new WP_Error();

	if ( ! $pass || ! $login ) {
		$wp_errors->add( 'rcl_login_empty', __( 'Fill in the required fields!', 'wp-recall' ) );
		return $wp_errors;
	}

	$creds					 = array();
	$creds['user_login']	 = $login;
	$creds['user_password']	 = $pass;
	$creds['remember']		 = $member;
	$userdata				 = wp_signon( $creds );

	if ( is_wp_error( $userdata ) ) {
		$wp_errors = $userdata;
		return $wp_errors;
	}

	wp_redirect( apply_filters( 'login_redirect', $url, '', $userdata ) );
	exit;
}

//принимаем данные для авторизации пользователя с формы wp-recall
add_action( 'init', 'rcl_get_login_user_activate' );
function rcl_get_login_user_activate() {
	if ( isset( $_POST['login_wpnonce'] ) ) {
		if ( ! wp_verify_nonce( $_POST['login_wpnonce'], 'login-key-rcl' ) )
			return false;
		add_action( 'wp', 'rcl_login_user', 10 );
	}
}

function rcl_get_current_url( $typeform = false, $unset = false ) {

	$args = array(
		'register'			 => false,
		'login'				 => false,
		'remember'			 => false,
		'success'			 => false,
		'rcl-confirmdata'	 => false
	);

	$args['action-rcl'] = $typeform;

	if ( $typeform == 'remember' ) {
		$args['remember'] = 'success';
	}

	return add_query_arg( $args );
}

function rcl_referer_url( $typeform = false ) {
	echo rcl_get_current_url( $typeform );
}

function rcl_form_action( $typeform ) {
	echo rcl_get_current_url( $typeform, true );
}
