<?php

add_shortcode( 'rcl-userpanel', 'rcl_get_userpanel' );
function rcl_get_userpanel( $atts = [ ] ) {
	global $user_ID;

	if ( $user_ID ) {
		$content = rcl_get_button( [
			'label'	 => __( 'Перейти в личный кабинет', 'wp-recall' ),
			'icon'	 => 'fa-home',
			'size'	 => 'large',
			'href'	 => rcl_get_user_url( $user_ID )
			] );

		$content .= rcl_get_button( [
			'label'	 => __( 'Exit', 'wp-recall' ),
			'href'	 => wp_logout_url( home_url() ),
			'icon'	 => 'fa-external-link',
			'size'	 => 'large',
			] );

		return $content;
	}

	if ( ! rcl_get_option( 'login_form_recall' ) )
		rcl_dialog_scripts();

	$content = rcl_get_button( [
		'label'		 => __( 'Авторизация', 'wp-recall' ),
		'icon'		 => 'fa-sign-in',
		'size'		 => 'large',
		'onclick'	 => rcl_get_option( 'login_form_recall' ) ? null : 'Rcl.loginform.call(this);return false;',
		'href'		 => rcl_get_loginform_url( 'login' )
		] );

	$content .= rcl_get_button( [
		'label'		 => __( 'Регистрация', 'wp-recall' ),
		'icon'		 => 'fa-book',
		'size'		 => 'large',
		'onclick'	 => rcl_get_option( 'login_form_recall' ) ? null : 'Rcl.loginform.call(this);return false;',
		'href'		 => rcl_get_loginform_url( 'register' )
		] );

	return $content;
}

add_shortcode( 'rcl-loginform', 'rcl_get_loginform' );
function rcl_get_loginform( $atts = [ ] ) {
	global $user_ID;

	if ( $user_ID ) {
		return rcl_get_notice( [
			'type'	 => 'success',
			'text'	 => __( 'Вы уже авторизованы на сайте. Перейдите в <a href="' . rcl_get_user_url( $user_ID ) . '">личный кабинет</a>, чтобы начать работу.' )
			] );
	}

	extract( shortcode_atts( array(
		'forms' => 'login,register,lostpassword'
			), $atts ) );

	$forms = array_map( 'trim', explode( ',', $forms ) );

	$content = '<div class="usp-loginform">';

	$content .= '<div class="tab-group">';
	if ( in_array( 'login', $forms ) )
		$content .= '<a href="#signup" class="tab active" onclick="Rcl.loginform.tabShow(\'login\',this);return false;">' . __( 'Авторизация', 'wp-recall' ) . '</a>';
	if ( in_array( 'register', $forms ) )
		$content .= '<a href="#login" class="tab" onclick="Rcl.loginform.tabShow(\'register\',this);return false;">' . __( 'Регистрация', 'wp-recall' ) . '</a>';
	$content .= '</div>';

	if ( in_array( 'login', $forms ) ) {

		$content .= '<div class="tab-content tab-login active">';

		$content .= rcl_get_form( array(
			'submit'	 => __( 'Вход', 'wp-recall' ),
			'onclick'	 => 'Rcl.loginform.send("login",this);return false;',
			'fields'	 => apply_filters( 'rcl_login_form_fields', array(
				array(
					'slug'			 => 'user_login',
					'type'			 => 'text',
					'title'			 => __( 'Логин', 'wp-recall' ),
					'placeholder'	 => __( 'Логин', 'wp-recall' ),
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
		$registerFields = array(
			array(
				'type'			 => 'text',
				'slug'			 => 'user_login',
				'title'			 => __( 'Логин', 'wp-recall' ),
				'placeholder'	 => __( 'Логин', 'wp-recall' ),
				'icon'			 => 'fa-user',
				'maxlenght'		 => 50,
				'required'		 => 1
			),
			array(
				'type'			 => 'text',
				'slug'			 => 'user_email',
				'title'			 => __( 'Email', 'wp-recall' ),
				'placeholder'	 => __( 'Email', 'wp-recall' ),
				'icon'			 => 'fa-at',
				'maxlenght'		 => 50,
				'required'		 => 1
			),
			array(
				'type'			 => 'password',
				'slug'			 => 'user_pass',
				'title'			 => __( 'Password', 'wp-recall' ),
				'placeholder'	 => __( 'Password', 'wp-recall' ),
				'icon'			 => 'fa-key',
				'maxlenght'		 => 50,
				'required'		 => 1
			)
		);

		if ( $customFields = get_site_option( 'rcl_register_form_fields' ) ) {
			$registerFields = array_merge( $registerFields, $customFields );
		}

		$content .= '<div class="tab-content tab-register">';
		$content .= rcl_get_form( array(
			'submit'	 => __( 'Регистрация', 'wp-recall' ),
			'onclick'	 => 'Rcl.loginform.send("register",this);return false;',
			'fields'	 => $registerFields, //apply_filters( 'rcl_register_form_fields', $registerFields ),
			'structure'	 => get_site_option( 'rcl_fields_register_form_structure' )
			)
		);
		$content .= '</div>';
	}

	if ( in_array( 'lostpassword', $forms ) ) {
		$content .= '<div class="tab-content tab-lostpassword">';
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
