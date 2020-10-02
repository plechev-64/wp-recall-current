<?php

require_once 'classes/class-rcl-form.php';
function rcl_forms_scripts() {
	rcl_enqueue_style( 'rcl-forms', RCL_URL . 'modules/forms/style.css' );
	rcl_enqueue_script( 'rcl-forms', RCL_URL . 'modules/forms/scripts.js' );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_forms_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_forms_scripts', 10 );
}
