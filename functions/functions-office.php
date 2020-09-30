<?php

add_action( 'rcl_area_top', 'rcl_add_office_menu_options', 10 );
function rcl_add_office_menu_options() {
	echo RCL()->tabs()->get_menu( 'options' );
}

add_action( 'rcl_area_actions', 'rcl_add_office_menu_actions', 10 );
function rcl_add_office_menu_actions() {
	echo RCL()->tabs()->get_menu( 'actions' );
}

add_action( 'rcl_area_counters', 'rcl_add_office_menu_counters', 10 );
function rcl_add_office_menu_counters() {
	echo RCL()->tabs()->get_menu( 'counters' );
}

add_action( 'rcl_area_menu', 'rcl_add_office_menu_menu', 10 );
function rcl_add_office_menu_menu() {
	echo RCL()->tabs()->get_menu( 'menu' );
}

add_action( 'rcl_area_tabs', 'rcl_add_office_tab_content', 10 );
function rcl_add_office_tab_content() {
	if ( $current = RCL()->tabs()->current() )
		echo $current->get_content();
	return false;
}

function rcl_is_office( $user_id = null ) {
	global $rcl_office;

	if ( isset( $_POST['action'] ) && $_POST['action'] == 'rcl_ajax_tab' ) {

		$post = rcl_decode_post( $_POST['post'] );

		if ( $post->master_id )
			$rcl_office = $post->master_id;
	}else if ( isset( $_POST['action'] ) && $_POST['action'] == 'rcl_load_tab' ) {

		$rcl_office = intval( $_POST['office_id'] );
	}

	if ( $rcl_office ) {

		if ( isset( $user_id ) ) {
			if ( $user_id == $rcl_office )
				return true;
			return false;
		}

		return true;
	}

	return false;
}

function rcl_office_class() {
	global $active_addons, $user_LK, $user_ID;

	$class = array( 'wprecallblock', 'rcl-office' );

	$active_template = get_site_option( 'rcl_active_template' );

	if ( $active_template ) {
		if ( isset( $active_addons[$active_template] ) )
			$class[] = 'office-' . strtolower( str_replace( ' ', '-', $active_addons[$active_template]['template'] ) );
	}

	if ( $user_ID ) {
		$class[] = ($user_LK == $user_ID) ? 'visitor-master' : 'visitor-guest';
	} else {
		$class[] = 'visitor-guest';
	}

	$class[] = (rcl_get_option( 'buttons_place' ) == 1) ? "vertical-menu" : "horizontal-menu";

	echo 'class="' . implode( ' ', $class ) . '"';
}

function rcl_template_support( $support ) {

	switch ( $support ) {
		case 'avatar-uploader':

			if ( rcl_get_option( 'avatar_weight', 1024 ) > 0 )
				include_once RCL_PATH . 'functions/supports/uploader-avatar.php';

			break;
		case 'cover-uploader':

			add_filter( 'rcl_options', 'rcl_add_cover_options', 10 );

			if ( rcl_get_option( 'cover_weight', 1024 ) > 0 )
				include_once RCL_PATH . 'functions/supports/uploader-cover.php';

			break;
		case 'modal-user-details':
			include_once RCL_PATH . 'functions/supports/modal-user-details.php';
			break;
	}
}

function rcl_add_balloon_menu( $data, $args ) {
	if ( $data['id'] != $args['tab_id'] )
		return $data;
	$data['name'] = sprintf( '%s <span class="rcl-menu-notice">%s</span>', $data['name'], $args['ballon_value'] );
	return $data;
}
