<?php

require_once 'classes/class-rcl-option.php';
require_once 'classes/class-rcl-options-box.php';
require_once 'classes/class-rcl-options-group.php';
require_once 'classes/class-rcl-options-manager.php';
require_once 'functions.php';
function rcl_options_manager_scripts() {
	rcl_enqueue_style( 'rcl-options-manager', RCL_URL . 'modules/options-manager/style.css' );
	rcl_enqueue_script( 'rcl-options-manager', RCL_URL . 'modules/options-manager/scripts.js' );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_options_manager_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_options_manager_scripts', 10 );
}
