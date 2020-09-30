<?php

function rcl_get_option( $option, $default = false ) {
	global $rcl_options;

	$pre = apply_filters( "rcl_pre_option_{$option}", false, $option, $default );

	if ( false !== $pre )
		return $pre;

	if ( ! $rcl_options )
		$rcl_options = get_site_option( 'rcl_global_options' );

	if ( isset( $rcl_options[$option] ) ) {
		if ( $rcl_options[$option] || is_numeric( $rcl_options[$option] ) ) {
			return $rcl_options[$option];
		}
	}

	return $default;
}

function rcl_update_option( $name, $value ) {
	global $rcl_options;

	if ( ! $rcl_options )
		$rcl_options = get_site_option( 'rcl_global_options' );

	$rcl_options[$name] = $value;

	return update_site_option( 'rcl_global_options', $rcl_options );
}

function rcl_delete_option( $name ) {
	global $rcl_options;

	if ( ! $rcl_options )
		$rcl_options = get_site_option( 'rcl_global_options' );

	unset( $rcl_options[$name] );

	return update_site_option( 'rcl_global_options', $rcl_options );
}

function rcl_get_commerce_option( $option, $default = false ) {
	global $rmag_options;

	if ( ! $rmag_options )
		$rmag_options = get_site_option( 'primary-rmag-options' );

	if ( isset( $rmag_options[$option] ) ) {
		if ( $rmag_options[$option] || is_numeric( $rmag_options[$option] ) ) {
			return $rmag_options[$option];
		}
	}

	return $default;
}

function rcl_update_commerce_option( $name, $value ) {
	global $rmag_options;

	if ( ! $rmag_options )
		$rmag_options = get_site_option( 'primary-rmag-options' );

	$rmag_options[$name] = $value;

	return update_site_option( 'primary-rmag-options', $rmag_options );
}

function rcl_delete_commerce_option( $name ) {
	global $rmag_options;

	if ( ! $rmag_options )
		$rmag_options = get_site_option( 'primary-rmag-options' );

	unset( $rmag_options[$name] );

	return update_site_option( 'primary-rmag-options', $rmag_options );
}
