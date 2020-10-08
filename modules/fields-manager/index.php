<?php

require_once 'classes/class-rcl-fields-manager.php';
require_once 'functions.php';
function rcl_fields_manager_scripts() {
	rcl_enqueue_style( 'rcl-fields-manager', RCL_URL . 'modules/fields-manager/style.css', false, false, true );
	rcl_enqueue_script( 'rcl-fields-manager', RCL_URL . 'modules/fields-manager/scripts.js', false, false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_fields_manager_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_fields_manager_scripts', 10 );
}
