<?php

//регистрируем вкладку личного кабинета
function rcl_tab( $tab_data ) {

	$tab_data = apply_filters( 'rcl_tab', $tab_data );

	if ( ! $tab_data )
		return false;

	RCL()->tabs()->add( $tab_data );
}

//регистрация дочерней вкладки
function rcl_add_sub_tab( $tab_id, $subtabData ) {

	if ( ! $tab = RCL()->tabs()->tab( $tab_id ) )
		return false;

	$tab->add_subtab( $subtabData );
}

function rcl_get_tabs() {
	return RCL()->tabs;
}

function rcl_get_tab( $tab_id ) {
	return RCL()->tabs()->tab( $tab_id );
}

function rcl_get_subtab( $tab_id, $subtab_id ) {

	$tab = rcl_get_tab( $tab_id );

	if ( ! $tab )
		return false;

	return $subtab = $tab->subtab( $subtab_id ) ? $subtab : false;
}

function rcl_get_tab_permalink( $user_id, $tab_id, $subtab_id = false ) {
	return add_query_arg( ['tab' => $tab_id, 'subtab' => $subtab_id ], rcl_get_user_url( $user_id ) );
}

//вывод контента произвольной вкладки
add_filter( 'rcl_custom_tab_content', 'do_shortcode', 11 );
add_filter( 'rcl_custom_tab_content', 'wpautop', 10 );
function rcl_custom_tab_content( $content ) {
	return apply_filters( 'rcl_custom_tab_content', stripslashes_deep( $content ) );
}

add_filter( 'rcl_custom_tab_content', 'rcl_filter_custom_tab_vars', 6 );
function rcl_filter_custom_tab_vars( $content ) {
	global $user_ID, $user_LK;

	$matchs = array(
		'{USERID}'	 => $user_ID,
		'{MASTERID}' => $user_LK
	);

	$matchs = apply_filters( 'rcl_custom_tab_vars', $matchs );

	if ( ! $matchs )
		return $content;

	return strtr( $content, $matchs );
}

add_filter( 'rcl_custom_tab_content', 'rcl_filter_custom_tab_usermetas', 5 );
function rcl_filter_custom_tab_usermetas( $content ) {
	global $rcl_office;

	preg_match_all( '/{RCL-UM:([^}]+)}/', $content, $metas );

	if ( ! $metas[1] )
		return $content;

	$matchs = array();

	foreach ( $metas[1] as $meta ) {
		$value								 = get_user_meta( $rcl_office, $meta, 1 ) ? : __( 'not specified', 'wp-recall' );
		$matchs['{RCL-UM:' . $meta . '}']	 = (is_array( $value )) ? implode( ', ', $value ) : $value;
	}

	return strtr( $content, $matchs );
}

add_filter( 'rcl_tab_content', 'rcl_check_user_blocked', 10 );
function rcl_check_user_blocked( $content ) {
	global $user_ID, $user_LK;
	if ( $user_LK && $user_LK != $user_ID ) {
		if ( get_user_meta( $user_LK, 'rcl_black_list:' . $user_ID ) ) {
			$content = rcl_get_notice( array(
				'type'	 => 'info',
				'text'	 => __( 'The user has restricted access to their page', 'wp-recall' )
				) );
		}
	}
	return $content;
}

add_action( 'rcl_init_tabs', 'rcl_add_block_black_list_button', 10 );
function rcl_add_block_black_list_button() {
	global $user_LK, $user_ID;

	$user_block = get_user_meta( $user_ID, 'rcl_black_list:' . $user_LK );

	$title = ($user_block) ? __( 'Unblock', 'wp-recall' ) : __( 'Заблокировать', 'wp-recall' );

	rcl_tab(
		array(
			'id'		 => 'blacklist',
			'name'		 => $title,
			'public'	 => -2,
			'output'	 => 'actions',
			'icon'		 => 'fa-user',
			'onclick'	 => 'rcl_manage_user_black_list(this, ' . $user_LK . ', "' . __( 'Are you sure?', 'wp-recall' ) . '");return false;'
		)
	);
}
