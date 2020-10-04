<?php

function rcl_get_register_form_fields() {

	$registerFields = array(
		array(
			'type'			 => 'text',
			'slug'			 => 'user_email',
			'title'			 => __( 'Email', 'wp-recall' ),
			'placeholder'	 => __( 'Email', 'wp-recall' ),
			'icon'			 => 'fa-at',
			'maxlenght'		 => 50,
			'required'		 => 1
		)
	);

	if ( $customFields = get_site_option( 'rcl_register_form_fields' ) ) {
		$registerFields = array_merge( $registerFields, $customFields );
	}

	return apply_filters( 'rcl_register_form_fields', $registerFields );
}

/* * * регистрация через функцию register_new_user() ** */
//отключаем регистрационное стандартное письмо с данными для входа
remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
//отправляем письмо о регистрации от плагина
add_action( 'register_new_user', 'rcl_process_user_register_data', 10 );
function rcl_process_user_register_data( $user_id ) {

	$user_pass = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : false;

	if ( ! $user_pass ) {

		$user_pass = wp_generate_password( 12, false );

		//отключаем отправку письма о смене пароля
		add_filter( 'send_password_change_email', function() {
			return false;
		} );

		wp_update_user( [
			'ID'		 => $user_id,
			'user_pass'	 => wp_hash_password( $user_pass )
		] );
	}

	rcl_register_mail( array(
		'user_id'	 => $user_id,
		'user_pass'	 => $user_pass,
		'user_login' => isset( $_POST['user_login'] ) ? $_POST['user_login'] : $_POST['user_email'],
		'user_email' => $_POST['user_email']
	) );

	wp_send_new_user_notifications( $user_id, 'admin' );

	if ( ! isset( $_REQUEST['rest_route'] ) ) {
		//если данные пришли с формы WP, то возвращаем на wp-login.php с нужными GET-параметрами
		if ( rcl_get_option( 'confirm_register_recall' ) == 1 ) {
			wp_safe_redirect( wp_login_url() . '?checkemail=confirm' );
		} else {
			wp_safe_redirect( wp_login_url() . '?checkemail=registered' );
		}

		exit();
	}
}

//сохраняем данные пользователя при создании/регистрации
add_action( 'user_register', 'rcl_register_new_user_data', 10 );
function rcl_register_new_user_data( $user_id ) {

	$timeAction = '0000-00-00 00:00:00';

	if ( rcl_get_option( 'confirm_register_recall' ) ) {
		wp_update_user( array(
			'ID'	 => $user_id,
			'role'	 => 'need-confirm'
		) );
	} else {
		$timeAction = current_time( 'mysql' );
	}

	global $wpdb;

	$wpdb->insert( RCL_PREF . 'user_action', array(
		'user'			 => $user_id,
		'time_action'	 => $timeAction
	) );

	update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

	rcl_update_profile_fields( $user_id );
}

/* * * регистрация через функцию register_new_user() конец ** */
function rcl_insert_user( $data ) {

	if ( get_user_by( 'email', $data['user_email'] ) )
		return false;

	if ( get_user_by( 'login', $data['user_login'] ) )
		return false;

	$userdata = array_merge( $data, array(
		'user_nicename'	 => ''
		, 'nickname'		 => $data['user_email']
		, 'first_name'	 => $data['display_name']
		, 'rich_editing'	 => 'true'  // false - выключить визуальный редактор для пользователя.
		) );

	$user_id = wp_insert_user( $userdata );

	if ( ! $user_id || is_wp_error( $user_id ) ) {
		return false;
	}

	rcl_register_new_user_data( $user_id );

	rcl_register_mail( array(
		'user_id'	 => $user_id,
		'user_login' => isset( $_POST['user_login'] ) ? $_POST['user_login'] : $_POST['user_email'],
		'user_email' => $_POST['user_email']
	) );

	wp_send_new_user_notifications( $user_id, 'admin' );

	do_action( 'rcl_insert_user', $user_id, $userdata );

	return $user_id;
}

//подтверждаем регистрацию пользователя по ссылке
function rcl_confirm_user_registration() {
	global $wpdb;

	if ( $confirmdata = urldecode( $_GET['rcl-confirmdata'] ) ) {

		$confirmdata = json_decode( base64_decode( $confirmdata ) );

		if ( $user = get_user_by( 'login', $confirmdata[0] ) ) {

			if ( md5( $user->ID ) != $confirmdata[1] )
				return false;

			if ( ! rcl_is_user_role( $user->ID, 'need-confirm' ) )
				return false;

			$defaultRole = get_site_option( 'default_role' );
			if ( $defaultRole == 'need-confirm' ) {
				update_site_option( 'default_role', 'author' );
				$defaultRole = 'author';
			}

			wp_update_user( array( 'ID' => $user->ID, 'role' => $defaultRole ) );

			if ( ! rcl_get_time_user_action( $user->ID ) )
				$wpdb->insert( RCL_PREF . 'user_action', array( 'user' => $user->ID, 'time_action' => current_time( 'mysql' ) ) );

			do_action( 'rcl_confirm_registration', $user->ID );

			if ( rcl_get_option( 'login_form_recall' ) == 2 ) {
				wp_safe_redirect( wp_login_url() . '?success=checkemail' );
			} else {

				$place = home_url();
				if ( rcl_get_option( 'login_form_recall', 0 ) == 1 ) {
					$place = rcl_format_url( get_permalink( rcl_get_option( 'page_login_form_recall' ) ) );
				}
				wp_redirect( add_query_arg( array(
					'action-rcl' => 'login', 'success'	 => 'checkemail'
						), $place ) );
			}
			exit;
		}
	}

	if ( rcl_get_option( 'login_form_recall' ) == 2 ) {
		wp_safe_redirect( wp_login_url() . '?checkemail=confirm' );
	} else {
		wp_redirect( add_query_arg( array(
			'action-rcl' => 'login', 'login'		 => 'checkemail'
				), home_url() ) );
	}
	exit;
}

//принимаем данные для подтверждения регистрации
add_action( 'init', 'rcl_confirm_user_resistration_activate', 10 );
function rcl_confirm_user_resistration_activate() {

	if ( ! isset( $_GET['rcl-confirmdata'] ) )
		return false;

	if ( rcl_get_option( 'confirm_register_recall' ) )
		add_action( 'wp', 'rcl_confirm_user_registration', 10 );
}

//ошибки плагина при регистрации
add_filter( 'registration_errors', 'rcl_add_user_register_errors', 10 );
function rcl_add_user_register_errors( $errors ) {

	$fields = rcl_get_register_form_fields();

	if ( $fields ) {
		$required = true;
		foreach ( $fields as $field ) {
			if ( ! isset( $field['required'] ) || ! $field['required'] )
				continue;

			$slug = $field['slug'];

			if ( ! isset( $_POST[$slug] ) || ! $_POST[$slug] ) {
				$required = false;
				break;
			}
		}
	}

	if ( ! $required ) {
		$errors->add( 'rcl_register_empty', __( 'Fill in the required fields!', 'wp-recall' ) );
	}

	if ( isset( $_POST['user_pass'] ) && isset( $_POST['user_pass_repeat'] ) && $_POST['user_pass'] != $_POST['user_pass_repeat'] ) {
		$errors->add( 'rcl_register_repeat_pass', __( 'Repeated password not correct!', 'wp-recall' ) );
	}

	return $errors;
}

//письмо высылаемое при регистрации
function rcl_register_mail( $userdata ) {

	$user_login	 = $userdata['user_login'];
	$user_id	 = $userdata['user_id'];

	$userdata = apply_filters( 'rcl_register_mail_data', $userdata );

	$textmail = '
    <p>' . __( 'You or someone else signed up on our website', 'wp-recall' ) . ' "' . get_bloginfo( 'name' ) . '" ' . __( 'with the following data:', 'wp-recall' ) . '</p>
    <p>' . __( 'Login', 'wp-recall' ) . ': ' . $userdata['user_login'] . '</p>
    <p>' . __( 'Password', 'wp-recall' ) . ': ' . $userdata['user_pass'] . '</p>';

	if ( rcl_get_option( 'confirm_register_recall' ) ) {

		$subject = __( 'Confirm your registration!', 'wp-recall' );

		$confirmstr = base64_encode(
			json_encode(
				array(
					$user_login,
					md5( $user_id )
				)
			)
		);

		$url = add_query_arg( array(
			'rcl-confirmdata' => urlencode( $confirmstr )
			), home_url() );

		$textmail .= '<p>' . __( 'If it was you, then confirm your registration by clicking on the link below', 'wp-recall' ) . ':</p>
        <p><a href="' . $url . '">' . $url . '</a></p>
        <p>' . __( 'Unable to activate the account?', 'wp-recall' ) . '</p>
        <p>' . __( 'Copy the link below, paste it into the address bar of your browser and hit Enter', 'wp-recall' ) . '</p>';
	} else {

		$subject = __( 'Registration completed', 'wp-recall' );
	}

	$textmail .= '<p>' . __( 'If it wasn’t you, then just ignore this email', 'wp-recall' ) . '</p>';

	$textmail = apply_filters( 'rcl_register_mail_text', $textmail, $userdata );

	rcl_mail( $userdata['user_email'], $subject, $textmail );
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
