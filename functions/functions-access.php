<?php

//запрещаем доступ в админку
add_action( 'init', 'rcl_admin_access', 1 );
function rcl_admin_access() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		return;
	if ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST )
		return;

	if ( is_admin() ) {

		global $user_ID;

		$access = rcl_check_access_console();

		if ( $access )
			return true;

		if ( isset( $_POST['short'] ) && intval( $_POST['short'] ) == 1 || isset( $_POST['fetch'] ) && intval( $_POST['fetch'] ) == 1 ) {

			return true;
		} else {

			if ( ! $user_ID )
				return true;

			wp_redirect( '/' );
			exit;
		}
	}
}

add_action( 'wp_head', 'rcl_hidden_admin_panel' );
function rcl_hidden_admin_panel() {
	global $user_ID;

	if ( ! $user_ID ) {
		return show_admin_bar( false );
	}

	$access = rcl_check_access_console();

	if ( $access )
		return true;

	show_admin_bar( false );
}

add_action( 'init', 'rcl_banned_user_redirect' );
function rcl_banned_user_redirect() {
	global $user_ID;
	if ( ! $user_ID )
		return false;
	if ( rcl_is_user_role( $user_ID, 'banned' ) )
		wp_die( __( 'Congratulations! You have been banned.', 'wp-recall' ) );
}

function rcl_check_access_console() {
	global $user_ID;

	$roles = rcl_get_option( 'consol_access_rcl' );

	//support old option
	if ( ! is_array( $roles ) || ! $roles ) {
		$roles = ['administrator' ];
	} else {
		$roles[] = 'administrator';
	}

	return rcl_is_user_role( $user_ID, $roles );
}
