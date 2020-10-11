<?php

function rcl_get_tab_button( $tab_id, $master_id ) {

	$tab = rcl_get_tab( $tab_id );

	if ( ! class_exists( 'Rcl_Tab' ) )
		require_once RCL_PATH . 'classes/class-rcl-tab.php';

	$Rcl_Tab = new Rcl_Tab( $tab );

	return $Rcl_Tab->get_tab_button( $master_id );
}

function rcl_get_tab_content( $tab_id, $master_id, $subtab_id = '' ) {
	global $user_ID;
	if ( ! class_exists( 'Rcl_Tab' ) )
		require_once RCL_PATH . 'classes/class-rcl-tab.php';

	$tab = rcl_get_tab( $tab_id );

	if ( ! $tab )
		return false;

	$tab['first'] = 1;

	$Rcl_Tab = new Rcl_Tab( $tab );

	$content = $Rcl_Tab->get_tab( $master_id, $subtab_id );

	return $content;
}

//регистрируем вкладки для вывода в личном кабинете
//add_action( 'wp', 'rcl_register_tabs', 10 );
function rcl_register_tabs() {

	if ( is_admin() || ! rcl_is_office() )
		return false;

	$rcl_tabs = rcl_get_tabs();

	if ( ! $rcl_tabs )
		return false;

	if ( ! class_exists( 'Rcl_Tab' ) )
		require_once RCL_PATH . 'classes/class-rcl-tab.php';

	foreach ( $rcl_tabs as $tab ) {
		$Rcl_Tab = new Rcl_Tab( $tab );
		$Rcl_Tab->register_tab();
	}
}

//сортируем вкладки и изменяем их данные согласно настроек
//add_filter( 'rcl_tabs', 'rcl_add_custom_tabs', 5 );
function rcl_add_custom_tabs( $tabs ) {

	$areas = rcl_get_area_options();

	if ( ! $areas )
		return $tabs;

	if ( $tabs ) {

		foreach ( $tabs as $tab_id => $tab ) {

			$tabArea = (isset( $tab['output'] )) ? $tab['output'] : 'menu';

			if ( ! isset( $areas[$tabArea] ) || ! $areas[$tabArea] )
				continue;

			foreach ( $areas[$tabArea] as $k => $field ) {

				if ( $field['slug'] != $tab_id )
					continue;

				$tabs[$tab_id]['icon']	 = $field['icon'];
				$tabs[$tab_id]['hidden'] = isset( $field['hidden'] ) ? $field['hidden'] : 0;
				$tabs[$tab_id]['name']	 = $field['title'];
				$tabs[$tab_id]['order']	 = ++ $k;
			}
		}
	}

	return $tabs;
}

//выясняем какую вкладку ЛК показывать пользователю,
//если ни одна не указана для вывода
//add_filter( 'rcl_tabs', 'rcl_get_order_tabs', 100 );
function rcl_get_order_tabs( $rcl_tabs ) {
	global $user_ID, $user_LK;

	if ( isset( $_GET['tab'] ) || ! $rcl_tabs )
		return $rcl_tabs;

	$counter = array();
	$a		 = 10;
	foreach ( $rcl_tabs as $id => $data ) {

		if ( ! isset( $data['output'] ) )
			$data['output'] = 'menu';

		if ( $data['output'] != 'menu' )
			continue;

		if ( isset( $data['hidden'] ) && $data['hidden'] )
			continue;

		if ( ! isset( $data['public'] ) || $data['public'] != 1 ) {

			if ( ! $user_ID )
				continue;

			if ( $data['public'] < 0 && $user_ID == $user_LK )
				continue;

			if ( $data['public'] == 0 && $user_ID != $user_LK )
				continue;
		}

		$order					 = (isset( $data['order'] )) ? $data['order'] : ++ $a;
		$rcl_tabs[$id]['order']	 = $order;
		$counter[$order]		 = $id;
	}

	if ( count( $counter ) == 1 ) {

		foreach ( $counter as $order => $id_tab ) {
			$rcl_tabs[$id_tab]['first'] = 1;
			break;
		}

		return $rcl_tabs;
	}

	if ( count( $rcl_tabs ) == 1 ) {

		foreach ( $rcl_tabs as $id_tab => $data ) {
			$rcl_tabs[$id_tab]['first'] = 1;
			break;
		}

		return $rcl_tabs;
	}

	ksort( $counter );

	$id_first						 = array_shift( $counter );
	$rcl_tabs[$id_first]['first']	 = 1;

	return $rcl_tabs;
}

//регистрируем контентые блоки
function rcl_block( $place, $callback, $args = false ) {
	global $rcl_blocks, $user_LK;

	$rcl_blocks[$place][] = apply_filters( 'block_data_rcl', array(
		'place'		 => $place,
		'callback'	 => $callback,
		'args'		 => $args
		) );

	$rcl_blocks = apply_filters( 'rcl_blocks', $rcl_blocks );
}

//формируем вывод зарегистрированных контентных блоков в личном кабинете
add_action( 'wp', 'rcl_setup_blocks' );
function rcl_setup_blocks() {
	global $rcl_blocks, $user_LK;

	if ( is_admin() || ! $user_LK )
		return false;

	if ( ! $rcl_blocks )
		return false;

	if ( ! class_exists( 'Rcl_Blocks' ) )
		require_once RCL_PATH . 'deprecated/class-rcl-blocks.php';

	foreach ( $rcl_blocks as $place_id => $blocks ) {
		if ( ! $blocks )
			continue;
		foreach ( $blocks as $data ) {
			$Rcl_Blocks = new Rcl_Blocks( $data );
			$Rcl_Blocks->add_block();
		}
	}

	do_action( 'rcl_setup_blocks' );
}

//регистрируем список публикаций указанного типа записи
function rcl_postlist( $id, $post_type, $name = '', $args = false ) {
	global $rcl_postlist;

	if ( ! rcl_get_option( 'publics_block_rcl' ) )
		return false;

	$rcl_postlist[$post_type] = array( 'id' => $id, 'post_type' => $post_type, 'name' => $name, 'args' => $args );
}

function rcl_key_addon( $path_parts ) {
	if ( ! isset( $path_parts['dirname'] ) )
		return false;
	return rcl_get_addon_dir( $path_parts['dirname'] );
}

function rcl_encode_post( $array ) {
	return base64_encode( json_encode( $array ) );
}

function rcl_decode_post( $string ) {
	return json_decode( base64_decode( $string ) );
}

function rcl_format_in( $array ) {
	$separats = array_fill( 0, count( $array ), '%d' );
	return implode( ', ', $separats );
}

function rcl_get_postmeta_array( $post_id ) {
	global $wpdb;

	$cachekey	 = json_encode( array( 'rcl_get_postmeta_array', $post_id ) );
	$cache		 = wp_cache_get( $cachekey );
	if ( $cache )
		return $cache;

	$mts	 = array();
	$metas	 = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE post_id='%d'", $post_id ) );
	if ( ! $metas )
		return false;
	foreach ( $metas as $meta ) {
		$mts[$meta->meta_key] = $meta->meta_value;
	}

	wp_cache_add( $cachekey, $mts );

	return $mts;
}

function rcl_format_url( $url, $tab_id = false, $subtab_id = false ) {
	$ar_perm = explode( '?', $url );
	$cnt	 = count( $ar_perm );
	if ( $cnt > 1 )
		$a		 = '&';
	else
		$a		 = '?';
	$url	 = $url . $a;
	if ( $tab_id )
		$url .= 'tab=' . $tab_id;
	if ( $subtab_id )
		$url .= '&subtab=' . $subtab_id;
	return $url;
}

function rcl_setup_chartdata( $mysqltime, $data ) {
	global $chartArgs;

	$day = date( "Y.m.j", strtotime( $mysqltime ) );

	$price = $data / 1000;

	$chartArgs[$day]['summ'] += $price;
	$chartArgs[$day]['cnt'] += 1;
	$chartArgs[$day]['days'] = date( "t", strtotime( $mysqltime ) );

	return $chartArgs;
}

function rcl_get_chart( $arr = false ) {
	global $chartData;

	if ( ! $arr )
		return false;

	foreach ( $arr as $month => $data ) {
		$cnt				 = (isset( $data['cnt'] )) ? $data['cnt'] : 0;
		$summ				 = (isset( $data['summ'] )) ? $data['summ'] : 0;
		$chartData['data'][] = array( '"' . $month . '"', $cnt, $summ );
	}

	if ( ! $chartData )
		return false;

	krsort( $chartData['data'] );
	array_unshift( $chartData['data'], array_pop( $chartData['data'] ) );

	return rcl_get_include_template( 'chart.php' );
}

//добавляем уведомление в личном кабинете
function rcl_notice_text( $text, $type = 'warning' ) {
	if ( is_admin() )
		return false;
	if ( ! class_exists( 'Rcl_Notify' ) )
		include_once RCL_PATH . 'functions/notify.php';
	$block = new Rcl_Notify( $text, $type );
}

function rcl_multisort_array( $array, $key, $type = SORT_ASC, $cmp_func = 'strcmp' ) {
	$GLOBALS['ARRAY_MULTISORT_KEY_SORT_KEY'] = $key;
	usort( $array, create_function( '$a, $b', '$k = &$GLOBALS["ARRAY_MULTISORT_KEY_SORT_KEY"];
		return ' . $cmp_func . '($a[$k], $b[$k]) * ' . ($type == SORT_ASC ? 1 : -1) . ';' ) );
	return $array;
}

function rcl_a_active( $param1, $param2 ) {
	if ( $param1 == $param2 )
		return 'filter-active';
}

//add_action( 'rcl_area_tabs', 'rcl_apply_filters_area_tabs', 10 );
function rcl_apply_filters_area_tabs() {

	$content = '<div id="lk-content" class="rcl-content">';
	$content .= apply_filters( 'rcl_content_area_tabs', '' );
	$content .= '</div>';

	echo $content;
}

//add_action( 'rcl_area_menu', 'rcl_apply_filters_area_menu', 10 );
function rcl_apply_filters_area_menu() {

	$content = '<div id="lk-menu" class="rcl-menu">';
	$content .= apply_filters( 'rcl_content_area_menu', '' );
	$content .= '</div>';

	echo $content;
}

//add_action( 'rcl_area_top', 'rcl_apply_filters_area_top', 10 );
function rcl_apply_filters_area_top() {
	echo apply_filters( 'rcl_content_area_top', '' );
}

//add_action( 'rcl_area_details', 'rcl_apply_filters_area_details', 10 );
function rcl_apply_filters_area_details() {
	echo apply_filters( 'rcl_content_area_details', '' );
}

//add_action( 'rcl_area_actions', 'rcl_apply_filters_area_actions', 10 );
function rcl_apply_filters_area_actions() {
	echo apply_filters( 'rcl_content_area_actions', '' );
}

//add_action( 'rcl_area_counters', 'rcl_apply_filters_area_counters', 10 );
function rcl_apply_filters_area_counters() {
	echo apply_filters( 'rcl_content_area_counters', '' );
}

function rcl_notice() {
	$notify	 = '';
	$notify	 = apply_filters( 'notify_lk', $notify );
	if ( $notify )
		echo '<div class="notify-lk">' . $notify . '</div>';
}

function rcl_sort_gallery( $attaches, $key, $user_id = false ) {
	global $user_ID;

	if ( ! $attaches )
		return false;
	if ( ! $user_id )
		$user_id = $user_ID;
	$cnt	 = count( $attaches );
	$v		 = $cnt + 10;
	foreach ( $attaches as $attach ) {
		$id	 = str_replace( $key . '-' . $user_id . '-', '', $attach->post_name );
		if ( ! is_numeric( $id ) || $id > 100 )
			$id	 = $v ++;
		if ( ! $id )
			$id	 = 0;
		foreach ( $attach as $k => $att ) {
			$gallerylist[( int ) $id][$k] = $attach->$k;
		}
	}

	$b	 = 0;
	$cnt = count( $gallerylist );
	for ( $a = 0; $b < $cnt; $a ++ ) {
		if ( ! isset( $gallerylist[$a] ) )
			continue;
		$new[$b] = $gallerylist[$a];
		$b ++;
	}
	for ( $a = $cnt - 1; $a >= 0; $a -- ) {
		$news[] = ( object ) $new[$a];
	}

	return $news;
}

/* Удаление поста вместе с его вложениями */
//add_action( 'before_delete_post', 'rcl_delete_attachments_with_post' );
function rcl_delete_attachments_with_post( $postid ) {
	$attachments = get_posts( array( 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' => null, 'post_parent' => $postid ) );
	if ( $attachments ) {
		foreach ( ( array ) $attachments as $attachment ) {
			wp_delete_attachment( $attachment->ID, true );
		}
	}
}
