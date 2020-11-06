<?php

add_action( 'wp_head', 'rcl_register_avatar_sizes', 10 );
function rcl_register_avatar_sizes() {

	$sizes = apply_filters( 'rcl_avatar_sizes', [70, 150, 300 ] );

	asort( $sizes );

	foreach ( $sizes as $k => $size ) {
		add_image_size( 'rcl-avatar-' . $size, $size, $size, 1 );
	}
}

//регистрируем размеры миниатюра загружаемого аватара пользователя
add_action( 'init', 'rcl_init_avatar_sizes' );
function rcl_init_avatar_sizes() {
	global $rcl_avatar_sizes;

	$sizes = array( 70, 150, 300 );

	$rcl_avatar_sizes = apply_filters( 'rcl_avatar_sizes', $sizes );
	asort( $rcl_avatar_sizes );
}

add_action( 'rcl_avatar', 'rcl_setup_avatar_icons', 10 );
function rcl_setup_avatar_icons() {

	$icons = rcl_avatar_icons();

	if ( ! $icons )
		return false;

	$html = array();
	foreach ( $icons as $icon_id => $icon ) {

		$atts = array();

		if ( isset( $icon['atts'] ) ) {
			foreach ( $icon['atts'] as $attr => $val ) {
				$val	 = (is_array( $val )) ? implode( ' ', $val ) : $val;
				$atts[]	 = $attr . '="' . $val . '"';
			}
		}

		$string = '<a ' . implode( ' ', $atts ) . '>';

		if ( isset( $icon['icon'] ) )
			$string .= '<i class="rcli ' . $icon['icon'] . '"></i>';

		if ( isset( $icon['content'] ) )
			$string .= $icon['content'];

		$string .= '</a>';

		$html[] = '<span class="rcl-avatar-icon icon-' . $icon_id . '">' . $string . '</span>';
	}

	echo '<span class="avatar-icons">' . implode( '', $html ) . '</span>';
}

function rcl_avatar_icons() {
	return apply_filters( 'rcl_avatar_icons', array() );
}

//указание url до загруженного изображения аватарки
add_filter( 'pre_get_avatar_data', 'rcl_avatar_data_replacement', 20, 2 );
function rcl_avatar_data_replacement( $args, $id_or_email ) {
	global $rcl_user;

	$size = $args['size'];

	$user_id	 = 0;
	$avatar_data = false;

	if ( $rcl_user && $rcl_user->ID == $id_or_email ) {

		$user_id = $rcl_user->ID;

		if ( isset( $rcl_user->avatar_data ) && $rcl_user->avatar_data ) {
			$avatar_data = $rcl_user->avatar_data;
		}
	} else {

		if ( is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} elseif ( is_object( $id_or_email ) ) {
			$user_id = $id_or_email->user_id;
		} elseif ( is_email( $id_or_email ) ) {
			if ( $user	 = get_user_by( 'email', $id_or_email ) )
				$user_id = $user->ID;
		}
	}

	if ( $user_id ) {

		if ( ! $avatar_data )
			$avatar_data = get_user_meta( $user_id, 'rcl_avatar', 1 );

		if ( ! $avatar_data ) {
			$avatar_data = rcl_get_option( 'default_avatar', false );
		}

		if ( $avatar_data ) {

			$url = false;

			if ( is_numeric( $avatar_data ) ) {
				$image_attributes	 = wp_get_attachment_image_src( $avatar_data, array( $size, $size ) );
				if ( $image_attributes )
					$url				 = $image_attributes[0];
			}else if ( is_string( $avatar_data ) ) {
				$url = rcl_get_url_avatar( $avatar_data, $user_id, $size );
			}

			if ( $url && file_exists( rcl_path_by_url( $url ) ) ) {
				$args['url'] = $url;
			}
		}
	}

	return $args;
}

function rcl_get_url_avatar( $url_image, $user_id, $size ) {
	global $rcl_avatar_sizes;

	if ( ! $rcl_avatar_sizes )
		return $url_image;

	$optimal_size	 = 150;
	$optimal_path	 = false;
	$name			 = explode( '.', basename( $url_image ) );
	foreach ( $rcl_avatar_sizes as $rcl_size ) {
		if ( $size > $rcl_size )
			continue;

		$optimal_size	 = $rcl_size;
		$optimal_url	 = RCL_UPLOAD_URL . 'avatars/' . $user_id . '-' . $optimal_size . '.' . $name[1];
		$optimal_path	 = RCL_UPLOAD_PATH . 'avatars/' . $user_id . '-' . $optimal_size . '.' . $name[1];
		break;
	}

	if ( $optimal_path && file_exists( $optimal_path ) )
		$url_image = $optimal_url;

	return $url_image;
}
