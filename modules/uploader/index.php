<?php

require_once 'class-rcl-uploader.php';
function rcl_uploader_scripts() {
	rcl_enqueue_style( 'rcl-uploader', RCL_URL . 'modules/uploader/style.css' );
	rcl_enqueue_script( 'rcl-uploader', RCL_URL . 'modules/uploader/scripts.js' );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_uploader_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_uploader_scripts', 10 );
}
