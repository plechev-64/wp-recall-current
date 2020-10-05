<?php

require_once 'classes/class-rcl-content-manager.php';
require_once 'classes/class-rcl-table-manager.php';
require_once 'classes/class-rcl-table-cols-manager.php';
require_once 'functions-ajax.php';
function rcl_table_manager_scripts() {
	rcl_enqueue_style( 'rcl-content-manager', RCL_URL . 'modules/content-manager/assets/style.css', false, false, true );
	rcl_enqueue_script( 'rcl-content-manager', RCL_URL . 'modules/content-manager/assets/scripts.js', ['rcl-core-scripts' ], false, false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_table_manager_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_table_manager_scripts', 10 );
}
