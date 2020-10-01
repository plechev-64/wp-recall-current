<?php

require_once 'classes/class-rcl-table.php';

add_action( 'rcl_enqueue_scripts', 'rcl_table_scripts', 10 );
function rcl_table_scripts() {
	rcl_enqueue_style( 'rcl-table', RCL_URL . 'modules/table/style.css' );
}
