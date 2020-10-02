<?php

function rcl_update_options() {

	rcl_verify_ajax_nonce();

	$POST = $_POST; //filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

	array_walk_recursive(
		$POST, function(&$v, $k) {
		$v = trim( $v );
	} );

	foreach ( $POST as $option_name => $values ) {

		if ( ! is_array( $values ) )
			continue;

		$values = apply_filters( $option_name . '_pre_update', $values );

		if ( $option_name == 'local' ) {

			foreach ( $values as $local_name => $value ) {
				update_site_option( $local_name, $value );
			}
		} else {
			update_site_option( $option_name, $values );
		}
	}

	do_action( 'rcl_update_options' );

	return array(
		'success' => __( 'Settings saved!', 'wp-recall' )
	);
}

add_action( 'rcl_update_options', 'rcl_delete_temp_default_avatar_cover', 10 );
function rcl_delete_temp_default_avatar_cover() {

	if ( isset( $_POST['rcl_global_options']['default_avatar'] ) )
		rcl_delete_temp_media( $_POST['rcl_global_options']['default_avatar'] );

	if ( isset( $_POST['rcl_global_options']['default_cover'] ) )
		rcl_delete_temp_media( $_POST['rcl_global_options']['default_cover'] );
}

function rcl_add_cover_options( $options ) {

	$options->box( 'primary' )->group( 'design' )->add_options( [
		array(
			'type'		 => 'uploader',
			'temp_media' => 1,
			'max_size'	 => 5120,
			'multiple'	 => 0,
			'crop'		 => ['ratio' => 0 ],
			'filetitle'	 => 'rcl-default-cover',
			'filename'	 => 'rcl-default-cover',
			'slug'		 => 'default_cover',
			'title'		 => __( 'Default cover', 'wp-recall' ),
		),
		array(
			'type'		 => 'runner',
			'value_min'	 => 0,
			'value_max'	 => 5120,
			'value_step' => 256,
			'default'	 => 1024,
			'slug'		 => 'cover_weight',
			'title'		 => __( 'Max weight of cover', 'wp-recall' ) . ', Kb',
			'notice'	 => __( 'Set the image upload limit in kb, by default', 'wp-recall' ) . ' 1024Kb' .
			'. ' . __( 'If 0 is specified, download is disallowed.', 'wp-recall' )
		)
	] );

	return $options;
}
