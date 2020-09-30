<?php
function rcl_action() {
	global $rcl_userlk_action;
	$last_action = rcl_get_useraction( $rcl_userlk_action );
	$class		 = ( ! $last_action) ? 'online' : 'offline';

	if ( $last_action )
		$status	 = __( 'offline', 'wp-recall' ) . ' ' . $last_action;
	else
		$status	 = __( 'online', 'wp-recall' );

	echo sprintf( '<span class="user-status %s">%s</span>', $class, $status );
}

function rcl_avatar( $avatar_size = 120, $attr = false ) {
	global $user_LK;
	?>
	<div id="rcl-avatar">
		<span class="avatar-image">
			<?php echo get_avatar( $user_LK, $avatar_size, false, false, $attr ); ?>
			<span id="avatar-upload-progress"><span></span></span>
		</span>
		<?php do_action( 'rcl_avatar' ); ?>
	</div>
	<?php
}

function rcl_status_desc() {
	global $user_LK;
	$desc = get_the_author_meta( 'description', $user_LK );
	if ( $desc )
		echo '<div class="ballun-status">'
		. '<div class="status-user-rcl">' . nl2br( wp_strip_all_tags( $desc ) ) . '</div>'
		. '</div>';
}

function rcl_username() {
	global $user_LK;
	echo get_the_author_meta( 'display_name', $user_LK );
}

function rcl_user_name() {
	global $rcl_user;
	echo $rcl_user->display_name;
}

function rcl_user_url() {
	global $rcl_user;
	echo rcl_get_user_url( $rcl_user->ID );
}

function rcl_user_avatar( $size = 50 ) {
	global $rcl_user;
	echo get_avatar( $rcl_user->ID, $size );
}

function rcl_user_rayting() {
	global $rcl_user, $rcl_users_set;
	if ( ! rcl_exist_addon( 'rating-system' ) )
		return false;
	if ( false !== array_search( 'rating_total', $rcl_users_set->data ) || isset( $rcl_user->rating_total ) ) {
		if ( ! isset( $rcl_user->rating_total ) )
			$rcl_user->rating_total = 0;
		echo rcl_rating_block( array( 'value' => $rcl_user->rating_total ) );
	}
}

add_action( 'rcl_user_description', 'rcl_user_meta', 30 );
function rcl_user_meta() {
	global $rcl_user, $rcl_users_set;
	if ( false !== array_search( 'profile_fields', $rcl_users_set->data ) || isset( $rcl_user->profile_fields ) ) {
		if ( ! isset( $rcl_user->profile_fields ) )
			$rcl_user->profile_fields = array();

		if ( $rcl_user->profile_fields ) {

			echo '<div class="user-profile-fields">';
			foreach ( $rcl_user->profile_fields as $field_id => $field ) {
				echo $field->get_field_value( true );
			}
			echo '</div>';
		}
	}
}

add_action( 'rcl_user_description', 'rcl_user_comments', 20 );
function rcl_user_comments() {
	global $rcl_user, $rcl_users_set;
	if ( false !== array_search( 'comments_count', $rcl_users_set->data ) || isset( $rcl_user->comments_count ) ) {
		if ( ! isset( $rcl_user->comments_count ) )
			$rcl_user->comments_count = 0;
		echo '<span class="filter-data"><i class="rcli fa-comment"></i>' . __( 'Comments', 'wp-recall' ) . ': ' . $rcl_user->comments_count . '</span>';
	}
}

add_action( 'rcl_user_description', 'rcl_user_posts', 20 );
function rcl_user_posts() {
	global $rcl_user, $rcl_users_set;
	if ( false !== array_search( 'posts_count', $rcl_users_set->data ) || isset( $rcl_user->posts_count ) ) {
		if ( ! isset( $rcl_user->posts_count ) )
			$rcl_user->posts_count = 0;
		echo '<span class="filter-data"><i class="rcli fa-file-text-o"></i>' . __( 'Publics', 'wp-recall' ) . ': ' . $rcl_user->posts_count . '</span>';
	}
}

function rcl_user_action( $type = 1 ) {
	global $rcl_user;

	$action = (isset( $rcl_user->time_action )) ? $rcl_user->time_action : $rcl_user->user_registered;

	switch ( $type ) {
		case 1: $last_action = rcl_get_useraction( $action );
			if ( ! $last_action )
				echo '<span class="status_user online"><i class="rcli fa-circle"></i></span>';
			else
				echo '<span class="status_user offline" title="' . __( 'offline', 'wp-recall' ) . ' ' . $last_action . '"><i class="rcli fa-circle"></i></span>';
			break;
		case 2: echo rcl_get_miniaction( $action );
			break;
	}
}

function rcl_user_description() {
	global $rcl_user;

	if ( isset( $rcl_user->description ) && $rcl_user->description ) {
		echo '<div class="ballun-status">';
		echo '<div class="status-user-rcl">' . nl2br( esc_html( $rcl_user->description ) ) . '</div>
		</div>';
	}

	do_action( 'rcl_user_description' );
}

add_action( 'rcl_user_description', 'rcl_user_register', 20 );
function rcl_user_register() {
	global $rcl_user, $rcl_users_set;
	if ( false !== array_search( 'user_registered', $rcl_users_set->data ) || isset( $rcl_user->user_registered ) ) {
		if ( ! isset( $rcl_user->user_registered ) )
			return false;
		echo '<span class="filter-data"><i class="rcli fa-calendar-check-o"></i>' . __( 'Registration', 'wp-recall' ) . ': ' . mysql2date( 'd-m-Y', $rcl_user->user_registered ) . '</span>';
	}
}

add_action( 'rcl_user_description', 'rcl_filter_user_description', 10 );
function rcl_filter_user_description() {
	global $rcl_user;
	$cont	 = '';
	echo $cont	 = apply_filters( 'rcl_description_user', $cont, $rcl_user->ID );
}

function rcl_is_user_role( $user_id, $role ) {
	$user_data = get_userdata( $user_id );

	if ( ! isset( $user_data->roles ) || ! $user_data->roles )
		return false;

	$roles = $user_data->roles;

	$current_role = array_shift( $roles );

	if ( is_array( $role ) ) {
		if ( in_array( $current_role, $role ) )
			return true;
	}else {
		if ( $current_role == $role )
			return true;
	}

	return false;
}

function rcl_get_useraction( $user_action = false ) {
	global $rcl_userlk_action;

	if ( ! $user_action )
		$user_action = $rcl_userlk_action;

	$unix_time_user = strtotime( $user_action );

	if ( ! $unix_time_user || $user_action == '0000-00-00 00:00:00' )
		return __( 'long ago', 'wp-recall' );

	$timeout			 = ($time				 = rcl_get_option( 'timeout' )) ? $time * 60 : 600;
	$unix_time_action	 = strtotime( current_time( 'mysql' ) );

	if ( $unix_time_action > $unix_time_user + $timeout ) {
		return human_time_diff( $unix_time_user, $unix_time_action );
	} else {
		return false;
	}
}

function rcl_get_useraction_html( $user_id, $type = 1 ) {

	$action = rcl_get_time_user_action( $user_id );

	switch ( $type ) {
		case 1:

			$last_action = rcl_get_useraction( $action );

			if ( ! $last_action )
				return '<span class="status_user online"><i class="rcli fa-circle"></i></span>';
			else
				return '<span class="status_user offline" title="' . __( 'offline', 'wp-recall' ) . ' ' . $last_action . '"><i class="rcli fa-circle"></i></span>';

			break;
		case 2:

			return rcl_get_miniaction( $action );

			break;
	}
}

function rcl_human_time_diff( $time_action ) {
	$unix_current_time	 = strtotime( current_time( 'mysql' ) );
	$unix_time_action	 = strtotime( $time_action );
	return human_time_diff( $unix_time_action, $unix_current_time );
}

function rcl_update_timeaction_user() {
	global $user_ID, $wpdb;

	if ( ! $user_ID )
		return false;

	$rcl_current_action = rcl_get_time_user_action( $user_ID );

	$last_action = rcl_get_useraction( $rcl_current_action );

	if ( $last_action ) {

		$time = current_time( 'mysql' );

		$res = $wpdb->update(
			RCL_PREF . 'user_action', array( 'time_action' => $time ), array( 'user' => $user_ID )
		);

		if ( ! isset( $res ) || $res == 0 ) {
			$act_user = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(time_action) FROM " . RCL_PREF . "user_action WHERE user ='%d'", $user_ID ) );
			if ( $act_user == 0 ) {
				$wpdb->insert(
					RCL_PREF . 'user_action', array( 'user'			 => $user_ID,
					'time_action'	 => $time )
				);
			}
			if ( $act_user > 1 ) {
				rcl_delete_user_action( $user_ID );
			}
		}
	}

	do_action( 'rcl_update_timeaction_user' );
}

function rcl_get_time_user_action( $user_id ) {

	$cachekey	 = json_encode( array( 'rcl_get_time_user_action', ( int ) $user_id ) );
	$cache		 = wp_cache_get( $cachekey );
	if ( $cache )
		return $cache;

	$action = RQ::tbl( new Rcl_User_Action() )->select( ['time_action' ] )->where( ['user' => $user_id ] )->get_var();

	if ( ! $action ) {
		$action = '0000-00-00 00:00:00';
	}

	wp_cache_add( $cachekey, $action, 'default', rcl_get_option( 'timeout', 10 ) * 60 );

	return $action;
}

function rcl_get_miniaction( $action ) {
	global $rcl_user;

	if ( ! $action )
		$action = rcl_get_time_user_action( $rcl_user->ID );

	$last_action = rcl_get_useraction( $action );

	$class = ( ! $last_action && $action) ? 'online' : 'offline';

	$content = apply_filters( 'rcl_before_miniaction', '' );

	$content .= ( ! $last_action && $action) ? '<i class="rcli fa-circle"></i>' : __( 'offline', 'wp-recall' ) . ' ' . $last_action;

	$content = sprintf( '<div class="status_author_mess %s">%s</div>', $class, $content );

	return $content;
}

//заменяем ссылку автора комментария на ссылку его ЛК
add_filter( 'get_comment_author_url', 'rcl_get_link_author_comment', 10 );
function rcl_get_link_author_comment( $url ) {
	global $comment;
	if ( ! isset( $comment ) || $comment->user_id == 0 )
		return $url;
	return rcl_get_user_url( $comment->user_id );
}

function rcl_is_register_open() {
	return apply_filters( 'rcl_users_can_register', get_site_option( 'users_can_register' ) );
}

/* 16.0.0 */
function rcl_update_profile_fields( $user_id, $profileFields = false ) {

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');
	require_once(ABSPATH . "wp-admin" . '/includes/file.php');
	require_once(ABSPATH . "wp-admin" . '/includes/media.php');

	if ( ! $profileFields )
		$profileFields = rcl_get_profile_fields();

	if ( $profileFields ) {

		$defaultFields = array(
			'user_email',
			'description',
			'user_url',
			'first_name',
			'last_name',
			'display_name',
			'primary_pass',
			'repeat_pass'
		);

		foreach ( $profileFields as $field ) {

			$field = apply_filters( 'rcl_pre_update_profile_field', $field, $user_id );

			if ( ! $field || ! $field['slug'] )
				continue;

			$slug = $field['slug'];

			$value = (isset( $_POST[$slug] )) ? $_POST[$slug] : false;

			if ( isset( $field['admin'] ) && $field['admin'] == 1 && ! is_admin() ) {

				if ( in_array( $slug, array( 'display_name', 'user_url' ) ) ) {

					if ( get_the_author_meta( $slug, $user_id ) )
						continue;
				}else {

					if ( get_user_meta( $user_id, $slug, $value ) )
						continue;
				}
			}

			if ( $field['type'] == 'file' ) {

				$attach_id = get_user_meta( $user_id, $slug, 1 );

				if ( $attach_id && $value != $attach_id ) {
					wp_delete_attachment( $attach_id );
					delete_user_meta( $user_id, $slug );
				}
			}

			if ( $field['type'] != 'editor' ) {

				if ( is_array( $value ) ) {
					$value = array_map( 'esc_html', $value );
				} else {
					$value = esc_html( $value );
				}
			}

			if ( in_array( $slug, $defaultFields ) ) {

				if ( $slug == 'repeat_pass' )
					continue;

				if ( $slug == 'primary_pass' && $value ) {

					if ( $value != $_POST['repeat_pass'] )
						continue;

					$slug = 'user_pass';
				}

				if ( $slug == 'user_email' ) {

					if ( ! $value )
						continue;

					$currentEmail = get_the_author_meta( 'user_email', $user_id );

					if ( $currentEmail == $value )
						continue;
				}

				wp_update_user( array( 'ID' => $user_id, $slug => $value ) );

				continue;
			}

			if ( $field['type'] == 'checkbox' ) {

				$vals = array();

				if ( is_array( $value ) ) {

					$vals = array();

					foreach ( $value as $val ) {
						if ( in_array( $val, $field['values'] ) )
							$vals[] = $val;
					}
				}

				if ( $vals ) {
					update_user_meta( $user_id, $slug, $vals );
				} else {
					delete_user_meta( $user_id, $slug );
				}
			} else {

				if ( $value ) {

					update_user_meta( $user_id, $slug, $value );
				} else {

					if ( get_user_meta( $user_id, $slug, $value ) )
						delete_user_meta( $user_id, $slug, $value );
				}
			}

			if ( $value ) {

				if ( $field['type'] == 'uploader' ) {
					foreach ( $value as $val ) {
						rcl_delete_temp_media( $val );
					}
				} else if ( $field['type'] == 'file' ) {
					rcl_delete_temp_media( $value );
				}
			}
		}
	}

	do_action( 'rcl_update_profile_fields', $user_id );
}

/* 16.0.0 */
function rcl_get_profile_fields( $args = false ) {

	$fields = get_site_option( 'rcl_profile_fields' );

	$fields = apply_filters( 'rcl_profile_fields', $fields, $args );

	$profileFields = array();

	if ( $fields ) {

		foreach ( $fields as $k => $field ) {

			if ( isset( $args['include'] ) && ! in_array( $field['slug'], $args['include'] ) ) {

				continue;
			}

			if ( isset( $args['exclude'] ) && in_array( $field['slug'], $args['exclude'] ) ) {

				continue;
			}

			$profileFields[] = $field;
		}
	}

	return $profileFields;
}

function rcl_get_profile_field( $field_id ) {

	$fields = rcl_get_profile_fields( array( 'include' => array( $field_id ) ) );

	return $fields[0];
}

add_filter( 'author_link', 'rcl_author_link', 999, 2 );
function rcl_author_link( $link, $author_id ) {

	if ( rcl_get_option( 'view_user_lk_rcl' ) != 1 )
		return $link;

	return rcl_get_user_url( $author_id );
}

function rcl_get_user_url( $user_id ) {

	if ( rcl_get_option( 'view_user_lk_rcl' ) != 1 )
		return get_author_posts_url( $user_id );

	return add_query_arg(
		array(
		rcl_get_option( 'link_user_lk_rcl', 'user' ) => $user_id
		), get_permalink( rcl_get_option( 'lk_page_rcl' ) )
	);
}

add_action( 'delete_user', 'rcl_delete_user_action', 10 );
function rcl_delete_user_action( $user_id ) {
	global $wpdb;
	return $wpdb->query( $wpdb->prepare( "DELETE FROM " . RCL_PREF . "user_action WHERE user ='%d'", $user_id ) );
}

add_action( 'delete_user', 'rcl_delete_user_avatar', 10 );
function rcl_delete_user_avatar( $user_id ) {
	array_map( "unlink", glob( RCL_UPLOAD_URL . 'avatars/' . $user_id . '-*.jpg' ) );
}
