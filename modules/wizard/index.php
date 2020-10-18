<?php

require_once 'classes/class-rcl-wizard-step.php';
require_once 'classes/class-rcl-wizard.php';

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_wizard_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_wizard_scripts', 10 );
}
function rcl_wizard_scripts() {
	rcl_enqueue_style( 'rcl-wizard', RCL_URL . 'modules/wizard/style.css', false, false, true );
}
