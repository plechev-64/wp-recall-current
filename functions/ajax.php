<?php

function rcl_is_ajax() {
	return (defined( 'DOING_AJAX' ) && DOING_AJAX || isset( $GLOBALS['wp']->query_vars['rest_route'] ));
}

function Rcl_Ajax() {
	return Rcl_Ajax::getInstance();
}

function rcl_verify_ajax_nonce() {
	Rcl_Ajax()->verify();
}

function rcl_rest_action( $function_name ) {
	Rcl_Ajax()->init_rest( $function_name );
}

function rcl_ajax_action( $callback, $guest_access = false, $modules = true ) {
	Rcl_Ajax()->init_ajax_callback( $callback, $guest_access, $modules );
}

rcl_rest_action( 'rcl_ajax_call' );
function rcl_ajax_call() {
	global $user_ID;

	Rcl_Ajax()->verify();

	$callback	 = $_POST['call_action'];
	$modules	 = $_POST['used_modules'];

	if ( $modules ) {
		foreach ( $modules as $module_id ) {
			RCL()->use_module( $module_id );
		}
	}

	$callbackProps = Rcl_Ajax()->get_ajax_callback( $callback );

	if ( ! $callbackProps ) {

		wp_send_json( [
			'error' => __( 'Unregistered callback', 'wp-recall' )
		] );
	}

	if ( ! $user_ID && ! $callbackProps['guest'] ) {
		wp_send_json( [
			'error' => __( 'Access to callback is forbidden', 'wp-recall' )
		] );
	}

	if ( ! function_exists( $callback ) ) {
		wp_send_json( [
			'error' => __( 'Function is not found', 'wp-recall' )
		] );
	}

	wp_enqueue_script( 'rcl-core-scripts', RCL_URL . 'assets/js/core.js', array( 'jquery' ), VER_RCL );

	$respond = $callback();

	$respond['used_modules'] = $modules ? array_unique( $modules + RCL()->used_modules ) : RCL()->used_modules;

	wp_send_json( $respond );
}

rcl_ajax_action( 'rcl_load_tab', true, true );
function rcl_load_tab() {
	global $user_LK, $office_id;

	$tab_id		 = $_POST['tab_id'];
	$subtab_id	 = $_POST['subtab_id'];
	$office_id	 = intval( $_POST['office_id'] );
	$page_id	 = intval( $_POST['page_id'] );

	$tab = RCL()->tabs()->tab( $tab_id );

	if ( ! $tab ) {
		return array( 'error' => __( 'Data of the requested tab was not found.', 'wp-recall' ) );
	}

	$ajax = (in_array( 'ajax', $tab->supports ) || in_array( 'dialog', $tab->supports )) ? 1 : 0;

	if ( ! $ajax ) {
		return array( 'error' => __( 'Perhaps this add-on does not support ajax loading', 'wp-recall' ) );
	}

	$user_LK = $office_id;

	RCL()->tabs()->current_id	 = $tab_id;
	$tab->current_id			 = $subtab_id ? $subtab_id : $tab->content[0]->id;

	$content = $tab->get_menu();

	$content .= apply_filters( 'rcl_ajax_tab_content', $tab->subtab( $subtab_id )->get_content() );

	return array(
		'content'	 => $content,
		'tab'		 => $tab,
		'tab_id'	 => $tab->id,
		'subtab_id'	 => $subtab_id ? $subtab_id : '',
		'tab_url'	 => $tab->subtab( $subtab_id )->get_permalink(),
		'supports'	 => $tab->supports
	);
}

//регистрируем биение плагина
rcl_ajax_action( 'rcl_beat', true );
function rcl_beat() {

	$databeat	 = json_decode( wp_unslash( $_POST['databeat'] ) );
	$result		 = ['beat_result' => [ ] ];

	if ( $databeat ) {
		foreach ( $databeat as $data ) {

			$callback = $data->action;

			$result['beat_result'][] = [
				'result'	 => $callback( $data->data ),
				'success'	 => $data->success,
				'beat_name'	 => $data->beat_name
			];
		}
	}

	return $result;
}

rcl_ajax_action( 'rcl_manage_user_black_list', false );
function rcl_manage_user_black_list() {
	global $user_ID;

	$user_id = intval( $_POST['user_id'] );

	if ( ! $user_id ) {
		return array(
			'error' => __( 'Error', 'wp-recall' )
		);
	}

	$user_block = get_user_meta( $user_ID, 'rcl_black_list:' . $user_id );

	if ( $user_block ) {
		delete_user_meta( $user_ID, 'rcl_black_list:' . $user_id );
		do_action( 'remove_user_blacklist', $user_id );
	} else {
		add_user_meta( $user_ID, 'rcl_black_list:' . $user_id, 1 );
		do_action( 'add_user_blacklist', $user_id );
	}

	$new_status = $user_block ? 0 : 1;

	return array(
		'label' => ($new_status) ? __( 'Unblock', 'wp-recall' ) : __( 'Заблокировать', 'wp-recall' )
	);
}

rcl_ajax_action( 'rcl_get_smiles_ajax', false );
function rcl_get_smiles_ajax() {
	global $wpsmiliestrans;

	$content = array();

	$smilies = array();
	foreach ( $wpsmiliestrans as $emo => $smilie ) {
		$smilies[$smilie] = $emo;
	}

	foreach ( $smilies as $smilie => $emo ) {
		if ( ! $emo )
			continue;
		$content[] = str_replace( 'style="height: 1em; max-height: 1em;"', '', convert_smilies( $emo ) );
	}

	if ( ! $content ) {
		return array(
			'error' => __( 'Failed to load emoticons', 'wp-recall' )
		);
	}

	return array(
		'content' => implode( '', $content )
	);
}

/* new uploader */
rcl_ajax_action( 'rcl_upload', true );
function rcl_upload() {

	$options = ( array ) json_decode( wp_unslash( $_POST['options'] ) );

	if ( ! isset( $options['class_name'] ) || ! $options['class_name'] )
		return [
			'error' => __( 'Error', 'wp-recall' )
		];

	$className = $options['class_name'];

	if ( $className == 'Rcl_Uploader' )
		$uploader	 = new $className( $options['uploader_id'], $options );
	else
		$uploader	 = new $className( $options );

	if ( md5( json_encode( $uploader ) . rcl_get_option( 'security-key' ) ) != $_POST['sk'] )
		return [
			'error' => __( 'Error of security', 'wp-recall' )
		];

	$files = $uploader->upload();

	if ( $files ) {
		return $files;
	} else {
		return array(
			'error' => __( 'Something has been wrong', 'wp-recall' )
		);
	}
}

//удаление фото приложенных к публикации через загрузчик плагина
rcl_ajax_action( 'rcl_ajax_delete_attachment', true );
function rcl_ajax_delete_attachment() {
	global $user_ID;

	$attachment_id	 = intval( $_POST['attach_id'] );
	$post_id		 = intval( $_POST['post_id'] );

	if ( ! $attachment_id ) {
		return array(
			'error' => __( 'The data has been wrong!', 'wp-recall' )
		);
	}

	if ( $post_id ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return array(
				'error' => __( 'You can`t delete this file!', 'wp-recall' )
			);
		}
	} else {

		$media = RQ::tbl( new Rcl_Temp_Media() )->where( ['media_id' => $attachment_id ] )->get_row();

		if ( ! $user_ID ) {
			if ( $media->session_id != $_COOKIE['PHPSESSID'] ) {
				return array(
					'error' => __( 'You can`t delete this file!', 'wp-recall' )
				);
			}
		} else {
			if ( ! current_user_can( 'edit_post', $attachment_id ) ) {
				return array(
					'error' => __( 'You can`t delete this file!', 'wp-recall' )
				);
			}
		}

		rcl_delete_temp_media( $attachment_id );
	}

	wp_delete_attachment( $attachment_id, true );

	return array(
		'success' => __( 'The file has been successfully deleted!', 'wp-recall' )
	);
}

/* new uploader end */
