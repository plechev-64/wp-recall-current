<?php

require_once 'loginform.php';
require_once 'register.php';
require_once 'authorize.php';
require_once 'shortcodes.php';
require_once 'wp-register-form.php';
if ( class_exists( 'ReallySimpleCaptcha' ) ) {
	require_once 'captcha.php';
}
function rcl_loginform_scripts() {
	if ( ! rcl_get_option( 'login_form_recall' ) )
		rcl_dialog_scripts();
	rcl_enqueue_style( 'rcl-loginform', RCL_URL . 'modules/loginform/assets/style.css' );
	rcl_enqueue_script( 'rcl-loginform', RCL_URL . 'modules/loginform/assets/scripts.js' );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_loginform_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_loginform_scripts', 10 );
}
//вызываем форму входа и регистрации
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

		return array(
			'success' => __( 'Успешная авторизация', 'usp' )
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

//add_filter( 'rcl_register_form_fields', 'rcl_add_register_form_custom_data', 10 );
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
