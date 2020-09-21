<?php

rcl_ajax_action( 'rcl_get_ajax_chat_window' );
function rcl_get_ajax_chat_window() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$user_id = intval( $_POST['user_id'] );

	$chatdata = rcl_get_chat_private( $user_id );

	wp_send_json( array(
		'dialog' => array(
			'content'		 => $chatdata['content'],
			'title'			 => __( 'Chat with', 'wp-recall' ) . ' ' . get_the_author_meta( 'display_name', $user_id ),
			'class'			 => 'rcl-chat-window',
			'size'			 => 'small',
			'buttonClose'	 => false,
			'onClose'		 => array( 'rcl_chat_clear_beat', array( $chatdata['token'] ) )
		)
	) );
}

rcl_ajax_action( 'rcl_chat_remove_contact', false );
function rcl_chat_remove_contact() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$chat_id = intval( $_POST['chat_id'] );

	rcl_chat_update_user_status( $chat_id, $user_ID, 0 );

	$res['remove'] = true;

	wp_send_json( $res );
}

rcl_ajax_action( 'rcl_get_chat_page', true );
function rcl_get_chat_page() {

	rcl_verify_ajax_nonce();

	$chat_page	 = intval( $_POST['page'] );
	$in_page	 = intval( $_POST['in_page'] );
	$important	 = intval( $_POST['important'] );
	$chat_token	 = $_POST['token'];
	$chat_room	 = rcl_chat_token_decode( $chat_token );

	if ( ! rcl_get_chat_by_room( $chat_room ) )
		return false;

	require_once 'class-rcl-chat.php';

	$chat = new Rcl_Chat(
		array(
		'chat_room'	 => $chat_room,
		'paged'		 => $chat_page,
		'important'	 => $important,
		'in_page'	 => $in_page
		)
	);

	$res['content'] = $chat->get_messages_box();

	wp_send_json( $res );
}

rcl_ajax_action( 'rcl_chat_add_message', false );
function rcl_chat_add_message() {
	global $user_ID, $rcl_options;

	rcl_verify_ajax_nonce();

	$POST = wp_unslash( $_POST['chat'] );

	$chat_room = rcl_chat_token_decode( $POST['token'] );

	if ( ! rcl_get_chat_by_room( $chat_room ) )
		return false;

	$antispam = isset( $rcl_options['chat']['antispam'] ) ? $rcl_options['chat']['antispam'] : 5;

	if ( $antispam = apply_filters( 'rcl_chat_antispam_option', $antispam ) ) {

		$query = new Rcl_Chat_Messages_Query();

		$cntLastMess = $query->count( [
			'user_id'				 => $user_ID,
			'private_key__not_in'	 => [0 ],
			'message_status__not_in' => [1 ],
			'date_query'			 => [
				[
					'column'	 => 'message_time',
					'compare'	 => '=',
					'last'		 => '24 HOUR'
				]
			],
			'groupby'				 => 'private_key'
			] );

		if ( $cntLastMess > $antispam )
			wp_send_json( [
				'error' => __( 'Your activity has sings of spam!', 'wp-recall' )
			] );
	}

	$attach = (isset( $POST['attachment'] )) ? $POST['attachment'] : false;

	$content = '';

	$newMessages = rcl_chat_get_new_messages( ( object ) array(
			'last_activity'		 => $_POST['last_activity'],
			'token'				 => $POST['token'],
			'user_write'		 => 0,
			'update_activity'	 => 0
		) );

	if ( isset( $newMessages['content'] ) && $newMessages['content'] ) {
		$res['new_messages'] = 1;
		$content .= $newMessages['content'];
	}

	require_once 'class-rcl-chat.php';
	$chat = new Rcl_Chat( array( 'chat_room' => $chat_room ) );

	$result = $chat->add_message( $POST['message'], $attach );

	if ( isset( $result->errors ) && $result->errors ) {
		$res['errors'] = $result->errors;
		wp_send_json( $res );
	}

	if ( $attach )
		rcl_delete_temp_media( $attach );

	if ( isset( $result['errors'] ) ) {
		wp_send_json( $result );
	}

	$res['content']			 = $content . $chat->get_message_box( $result );
	$res['last_activity']	 = current_time( 'mysql' );

	wp_send_json( $res );
}

rcl_ajax_action( 'rcl_get_chat_private_ajax', false );
function rcl_get_chat_private_ajax() {

	rcl_verify_ajax_nonce();

	$user_id = intval( $_POST['user_id'] );

	$chatdata = rcl_get_chat_private( $user_id, array( 'avatar_size' => 30, 'userslist' => 0 ) );

	$chat = '<div class="rcl-chat-panel">'
		. '<a href="' . rcl_get_tab_permalink( $user_id, 'chat' ) . '"><i class="rcli fa-search-plus" aria-hidden="true"></i></a>'
		. '<a href="#" onclick="rcl_chat_close(this);return false;"><i class="rcli fa-times" aria-hidden="true"></i></a>'
		. '</div>';
	$chat .= $chatdata['content'];

	$result['content']		 = $chat;
	$result['chat_token']	 = $chatdata['token'];

	wp_send_json( $result );
}

rcl_ajax_action( 'rcl_chat_message_important', false );
function rcl_chat_message_important() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$message_id = intval( $_POST['message_id'] );

	$important = rcl_chat_get_message_meta( $message_id, 'important:' . $user_ID );

	if ( $important ) {
		rcl_chat_delete_message_meta( $message_id, 'important:' . $user_ID );
	} else {
		rcl_chat_add_message_meta( $message_id, 'important:' . $user_ID, 1 );
	}

	$result['important'] = ($important) ? 0 : 1;

	wp_send_json( $result );
}

rcl_ajax_action( 'rcl_chat_important_manager_shift', false );
function rcl_chat_important_manager_shift() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$chat_token			 = wp_slash( $_POST['token'] );
	$status_important	 = intval( $_POST['status_important'] );
	$chat_room			 = rcl_chat_token_decode( $chat_token );

	if ( ! rcl_get_chat_by_room( $chat_room ) )
		return false;

	require_once 'class-rcl-chat.php';
	$chat = new Rcl_Chat( array( 'chat_room' => $chat_room, 'important' => $status_important ) );

	$res['content'] = $chat->get_messages_box();

	wp_send_json( $res );
}

rcl_ajax_action( 'rcl_chat_delete_attachment', false );
function rcl_chat_delete_attachment() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$attachment_id = intval( $_POST['attachment_id'] );

	if ( ! $attachment_id )
		return false;

	if ( ! $post = get_post( $attachment_id ) )
		return false;

	if ( $post->post_author != $user_ID )
		return false;

	wp_delete_attachment( $attachment_id );

	$result['remove'] = true;

	wp_send_json( $result );
}

rcl_ajax_action( 'rcl_chat_ajax_delete_message', false );
function rcl_chat_ajax_delete_message() {
	global $current_user;

	rcl_verify_ajax_nonce();

	if ( ! $message_id = intval( $_POST['message_id'] ) )
		return false;

	if ( $current_user->user_level >= rcl_get_option( 'consol_access_rcl', 7 ) ) {
		rcl_chat_delete_message( $message_id );
	}

	$result['remove'] = true;

	wp_send_json( $result );
}
