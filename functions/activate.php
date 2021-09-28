<?php

if ( ! class_exists( 'reg_core' ) ) {

	class reg_core {
		function __construct() {
			add_action( 'init', array( &$this, 'init_prefix' ), 1 );
		}

		function init_prefix() {
			global $wpdb;

			if ( empty( $_SERVER['HTTP_HOST'] ) ) {
				return false;
			}

			$host   = str_replace( 'www.', '', filter_var( wp_unslash( $_SERVER['HTTP_HOST'] ), FILTER_SANITIZE_URL ) );
			$dm     = explode( '.', $host );
			$cnt    = count( $dm );
			$ignors = array( 'ua', 'es' );
			if ( $cnt == 3 && ! in_array( $dm[2], $ignors ) ) {
				$sn_nm = $dm[1] . '.' . $dm[2];
			} else {
				$sn_nm = $host;
			}
			define( 'WP_HOST', md5( $sn_nm ) );
			define( 'WP_PREFIX', $wpdb->prefix . substr( WP_HOST, - 4 ) . '_' );
		}

	}

	$core = new reg_core();
	function reg_form_wpp( $id, $path = false ) {

	}

}

