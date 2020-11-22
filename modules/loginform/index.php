<?php

require_once 'registration.php';
require_once 'authorization.php';

if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
	require_once 'wp-register-form.php';
}

if ( class_exists( 'ReallySimpleCaptcha' ) ) {
	require_once 'captcha.php';
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_loginform_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_loginform_scripts', 10 );
}
function rcl_loginform_scripts() {
	if ( ! rcl_get_option( 'login_form_recall' ) )
		rcl_dialog_scripts();
	rcl_enqueue_style( 'rcl-loginform', RCL_URL . 'modules/loginform/assets/style.css', false, false, true );
	rcl_enqueue_script( 'rcl-loginform', RCL_URL . 'modules/loginform/assets/scripts.js', false, false, true );
}

function rcl_get_loginform( $atts = [ ] ) {
	global $user_ID;

	extract( shortcode_atts( array(
		'active' => 'login',
		'forms'	 => 'login,register,lostpassword'
			), $atts ) );

	$forms = array_map( 'trim', explode( ',', $forms ) );

	$content = '<div class="rcl-loginform preloader-parent">';

	$content .= '<div class="tab-group">';
	if ( in_array( 'login', $forms ) )
		$content .= '<a href="#login" class="tab tab-login' . ($active == 'login' ? ' active' : '') . '" onclick="Rcl.loginform.tabShow(\'login\',this);return false;">' . __( 'Авторизация', 'wp-recall' ) . '</a>';
	if ( in_array( 'register', $forms ) )
		$content .= '<a href="#register" class="tab tab-register' . ($active == 'register' ? ' active' : '') . '" onclick="Rcl.loginform.tabShow(\'register\',this);return false;">' . __( 'Регистрация', 'wp-recall' ) . '</a>';
	$content .= '</div>';

	$content .= apply_filters( 'rcl_loginform_notice', '' );

	if ( in_array( 'login', $forms ) ) {

		$content .= '<div class="tab-content tab-login' . ($active == 'login' ? ' active' : '') . '">';

		$content .= rcl_get_form( array(
			'submit'	 => __( 'Вход', 'wp-recall' ),
			'onclick'	 => 'Rcl.loginform.send("login",this);return false;',
			'fields'	 => apply_filters( 'rcl_login_form_fields', array(
				array(
					'slug'			 => 'user_login',
					'type'			 => 'text',
					'title'			 => __( 'Логин или E-mail', 'wp-recall' ),
					'placeholder'	 => __( 'Логин или E-mail', 'wp-recall' ),
					'icon'			 => 'fa-user',
					'maxlenght'		 => 50,
					'required'		 => 1
				),
				array(
					'slug'			 => 'user_pass',
					'type'			 => 'password',
					'title'			 => __( 'Password', 'wp-recall' ),
					'placeholder'	 => __( 'Password', 'wp-recall' ),
					'icon'			 => 'fa-key',
					'maxlenght'		 => 50,
					'required'		 => 1
				)
			) )
			) );

		if ( in_array( 'lostpassword', $forms ) )
			$content .= '<a href="#" class="forget-link" onclick="Rcl.loginform.tabShow(\'lostpassword\',this);return false;">' . __( 'Восстановить пароль', 'wp-recall' ) . '</a>';

		$content .= '</div>';
	}

	if ( in_array( 'register', $forms ) ) {

		$content .= '<div class="tab-content tab-register' . ($active == 'register' ? ' active' : '') . '">';
		$content .= rcl_get_form( array(
			'submit'	 => __( 'Регистрация', 'wp-recall' ),
			'onclick'	 => 'Rcl.loginform.send("register",this);return false;',
			'fields'	 => rcl_get_register_form_fields(),
			'structure'	 => get_site_option( 'rcl_fields_register_form_structure' )
			)
		);
		$content .= '</div>';
	}

	if ( in_array( 'lostpassword', $forms ) ) {
		$content .= '<div class="tab-content tab-lostpassword' . ($active == 'lostpassword' ? ' active' : '') . '">';
		$content .= rcl_get_form( array(
			'submit'	 => __( 'Получить новый пароль', 'wp-recall' ),
			'onclick'	 => 'Rcl.loginform.send("lostpassword",this);return false;',
			'fields'	 => apply_filters( 'rcl_lostpassword_form_fields', array(
				array(
					'type'			 => 'text',
					'slug'			 => 'user_login',
					'title'			 => __( 'Логин', 'wp-recall' ),
					'placeholder'	 => __( 'Логин или Email', 'wp-recall' ),
					'icon'			 => 'fa-user',
					'maxlenght'		 => 50,
					'required'		 => 1
				)
				)
			) )
		);
		$content .= '</div>';
	}

	$content .= '</div>';

	return $content;
}

function rcl_get_loginform_url( $type ) {

	if ( $type == 'login' ) {
		switch ( rcl_get_option( 'login_form_recall' ) ) {
			case 1: return add_query_arg( ['rcl-form' => 'login' ], get_permalink( rcl_get_option( 'page_login_form_recall' ) ) );
				break;
			case 2: return wp_login_url( get_permalink( rcl_get_option( 'page_login_form_recall' ) ) );
				break;
			default: return '#';
				break;
		}
	}

	if ( $type == 'register' ) {
		switch ( rcl_get_option( 'login_form_recall' ) ) {
			case 1: return add_query_arg( ['rcl-form' => 'register' ], get_permalink( rcl_get_option( 'page_login_form_recall' ) ) );
				break;
			case 2: return wp_registration_url();
				break;
			default: return '#';
				break;
		}
	}
}

//вызываем форму входа и регистрации
rcl_ajax_action( 'rcl_call_loginform', true );
function rcl_call_loginform() {
	global $user_ID;

	$form = $_POST['form'];

	if ( $user_ID )
		return [
			'error' => __( 'Вы уже авторизованы!', 'wp-recall' )
		];

	return [
		'dialog' => [
			'size'		 => 'smallToMedium',
			'content'	 => rcl_get_loginform( ['active' => $form ] )
		]
	];
}

//принимаем данные отправленные с формы входа и регистрации
rcl_ajax_action( 'rcl_send_loginform', true );
function rcl_send_loginform() {

	$tab_id		 = $_POST['tab_id'];
	$user_login	 = isset( $_POST['user_login'] ) ? sanitize_user( $_POST['user_login'] ) : false;

	if ( $tab_id == 'login' ) {

		$password = sanitize_text_field( $_POST['user_pass'] );

		$user = wp_signon( array(
			'user_login'	 => $user_login,
			'user_password'	 => $password,
			'remember'		 => isset( $_POST['remember'] ) ? true : false,
			) );

		if ( is_wp_error( $user ) ) {
			return array(
				'error' => $user->get_error_message()
			);
		}

		rcl_update_timeaction_user();

		return array(
			'redirect'	 => rcl_get_authorize_url( $user->ID ),
			'success'	 => __( 'Успешная авторизация', 'usp' )
		);
	} else if ( $tab_id == 'register' ) {

		$user_email = sanitize_email( $_POST['user_email'] );

		if ( ! $user_login )
			$user_login = $user_email;

		$user_id = register_new_user( $user_login, $user_email );

		if ( is_wp_error( $user_id ) ) {
			return array(
				'error' => $user_id->get_error_message()
			);
		}

		$notice = __( 'Регистрация завершена, теперь вы можете авторизоваться на '
		              . '<a href="#" onclick="Rcl.loginform.tabShow(\'login\',this);'
		              . 'return false;">странице входа</a>.', 'wp-recall' );

		if ( rcl_get_option( 'confirm_register_recall' ) ){
			$notice = __( 'Регистрация завершена, проверьте вашу почту, затем '
			              . 'зайдите на <a href="#" onclick="Rcl.loginform.tabShow(\'login\',this);'
			              . 'return false;">страницу входа</a>.', 'wp-recall' );
		}

		return array(
			'content'	 => rcl_get_notice( [
				'type'	 => 'success',
				'text'	 => $notice
			] ),
			'success'	 => __( 'Успешная регистрация', 'usp' )
		);
	} else if ( $tab_id == 'lostpassword' ) {

		$result = retrieve_password();

		if ( is_wp_error( $result ) ) {
			return array(
				'error' => $result->get_error_message()
			);
		}

		return array(
			'content'	 => rcl_get_notice( [
				'type'	 => 'success',
				'text'	 => __( 'Ссылка для восстановления пароля выслана на почту', 'wp-recall' )
			] ),
			'success'	 => __( 'Письмо успешно отправлено', 'usp' )
		);
	}
}

add_filter( 'rcl_loginform_notice', 'rcl_add_login_form_notice', 10 );
function rcl_add_login_form_notice( $notice ) {

	if ( ! isset( $_REQUEST['formaction'] ) || ! $_REQUEST['formaction'] )
		return $notice;

	switch ( $_REQUEST['formaction'] ) {
		case 'success-checkemail':
			$notice = rcl_get_notice( [
				'success' => __( 'Your email has been successfully confirmed! Log in using your username and password', 'wp-recall' )
				] );
			break;
	}

	return $notice;
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

if ( $GLOBALS['pagenow'] !== 'wp-login.php' )
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

function retrieve_password() {
	$errors    = new WP_Error();
	$user_data = false;

	if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {
		$errors->add( 'empty_username', __( '<strong>Error</strong>: Please enter a username or email address.' ) );
	} elseif ( strpos( $_POST['user_login'], '@' ) ) {
		$user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
		if ( empty( $user_data ) ) {
			$errors->add( 'invalid_email', __( '<strong>Error</strong>: There is no account with that username or email address.' ) );
		}
	} else {
		$login     = trim( wp_unslash( $_POST['user_login'] ) );
		$user_data = get_user_by( 'login', $login );
	}

	/**
	 * Fires before errors are returned from a password reset request.
	 *
	 * @since 2.1.0
	 * @since 4.4.0 Added the `$errors` parameter.
	 * @since 5.4.0 Added the `$user_data` parameter.
	 *
	 * @param WP_Error      $errors    A WP_Error object containing any errors generated
	 *                                 by using invalid credentials.
	 * @param WP_User|false $user_data WP_User object if found, false if the user does not exist.
	 */
	do_action( 'lostpassword_post', $errors, $user_data );

	/**
	 * Filters the errors encountered on a password reset request.
	 *
	 * The filtered WP_Error object may, for example, contain errors for an invalid
	 * username or email address. A WP_Error object should always be returned,
	 * but may or may not contain errors.
	 *
	 * If any errors are present in $errors, this will abort the password reset request.
	 *
	 * @since 5.5.0
	 *
	 * @param WP_Error      $errors    A WP_Error object containing any errors generated
	 *                                 by using invalid credentials.
	 * @param WP_User|false $user_data WP_User object if found, false if the user does not exist.
	 */
	$errors = apply_filters( 'lostpassword_errors', $errors, $user_data );

	if ( $errors->has_errors() ) {
		return $errors;
	}

	if ( ! $user_data ) {
		$errors->add( 'invalidcombo', __( '<strong>Error</strong>: There is no account with that username or email address.' ) );
		return $errors;
	}

	// Redefining user_login ensures we return the right case in the email.
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;
	$key        = get_password_reset_key( $user_data );

	if ( is_wp_error( $key ) ) {
		return $key;
	}

	if ( is_multisite() ) {
		$site_name = get_network()->site_name;
	} else {
		/*
		 * The blogname option is escaped with esc_html on the way into the database
		 * in sanitize_option we want to reverse this for the plain text arena of emails.
		 */
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
	/* translators: %s: Site name. */
	$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
	/* translators: %s: User login. */
	$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
	$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
	$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
	$message .= network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n";

	/* translators: Password reset notification email subject. %s: Site title. */
	$title = sprintf( __( '[%s] Password Reset' ), $site_name );

	/**
	 * Filters the subject of the password reset email.
	 *
	 * @since 2.8.0
	 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
	 *
	 * @param string  $title      Default email title.
	 * @param string  $user_login The username for the user.
	 * @param WP_User $user_data  WP_User object.
	 */
	$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

	/**
	 * Filters the message body of the password reset mail.
	 *
	 * If the filtered message is empty, the password reset email will not be sent.
	 *
	 * @since 2.8.0
	 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
	 *
	 * @param string  $message    Default mail message.
	 * @param string  $key        The activation key.
	 * @param string  $user_login The username for the user.
	 * @param WP_User $user_data  WP_User object.
	 */
	$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

	if ( $message && ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {
		$errors->add(
			'retrieve_password_email_failure',
			sprintf(
			/* translators: %s: Documentation URL. */
				__( '<strong>Error</strong>: The email could not be sent. Your site may not be correctly configured to send emails. <a href="%s">Get support for resetting your password</a>.' ),
				esc_url( __( 'https://wordpress.org/support/article/resetting-your-password/' ) )
			)
		);
		return $errors;
	}

	return true;
}
