<?php

require_once 'registration.php';
require_once 'authorization.php';
require_once 'wp-register-form.php';
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
			$content .= '<a href="#" class="forget-link" onclick="Rcl.loginform.tabShow(\'lostpassword\',this);return false;">' . __( 'Forget password', 'wp-recall' ) . '</a>';

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

		return array(
			'content'	 => rcl_get_notice( [
				'type'	 => 'success',
				'text'	 => __( 'Регистрация завершена, проверьте вашу почту, затем '
					. 'зайдите на <a href="#" onclick="Rcl.loginform.tabShow(\'login\',this);'
					. 'return false;">страницу входа</a>.', 'wp-recall' )
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
