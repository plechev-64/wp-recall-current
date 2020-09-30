<?php

require_once 'classes/class-rcl-content-manager.php';
require_once 'classes/class-rcl-table-manager.php';
require_once 'classes/class-rcl-table-cols-manager.php';

if ( is_admin() )
	require_once 'functions-ajax.php';

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_table_manager_scripts', 10 );
endif;
function rcl_table_manager_scripts() {
	rcl_enqueue_style( 'rcl-table-manager', RCL_URL . 'modules/content-manager/assets/style.css' );
	rcl_enqueue_script( 'rcl-table-manager', RCL_URL . 'modules/content-manager/assets/scripts.js' );
}
