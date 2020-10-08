<?php

require_once 'classes/class-rcl-table.php';
function rcl_table_scripts() {
	rcl_enqueue_style( 'rcl-table', RCL_URL . 'modules/table/style.css', false, false, true );
	rcl_enqueue_script( 'rcl-table', RCL_URL . 'modules/table/scripts.js', false, false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_table_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_table_scripts', 10 );
}
