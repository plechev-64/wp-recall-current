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
	global $current_user;

	$need_access = rcl_get_option( 'consol_access_rcl', 7 );

	if ( $current_user->user_level ) {

		$access = ( $current_user->user_level < $need_access ) ? false : true;
	} else if ( isset( $current_user->allcaps['level_' . $need_access] ) ) {

		$access = ( $current_user->allcaps['level_' . $need_access] == 1 ) ? true : false;
	} else {

		$roles = array(
			10	 => 'administrator',
			7	 => 'editor',
			2	 => 'author',
			1	 => 'contributor'
		);

		$access = (isset( $roles[$need_access] ) && current_user_can( $roles[$need_access] )) ? true : false;
	}

	return $access;
}
