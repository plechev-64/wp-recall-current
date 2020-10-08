<?php

require_once 'classes/class-rcl-field-abstract.php';
require_once 'classes/class-rcl-field.php';
require_once 'classes/class-rcl-fields.php';
require_once 'classes/types/class-rcl-field-agree.php';
require_once 'classes/types/class-rcl-field-checkbox.php';
require_once 'classes/types/class-rcl-field-color.php';
require_once 'classes/types/class-rcl-field-custom.php';
require_once 'classes/types/class-rcl-field-date.php';
require_once 'classes/types/class-rcl-field-dynamic.php';
require_once 'classes/types/class-rcl-field-editor.php';
require_once 'classes/types/class-rcl-field-select.php';
require_once 'classes/types/class-rcl-field-multiselect.php';
require_once 'classes/types/class-rcl-field-radio.php';
require_once 'classes/types/class-rcl-field-range.php';
require_once 'classes/types/class-rcl-field-runner.php';
require_once 'classes/types/class-rcl-field-text.php';
require_once 'classes/types/class-rcl-field-tel.php';
require_once 'classes/types/class-rcl-field-number.php';
require_once 'classes/types/class-rcl-field-textarea.php';
require_once 'classes/types/class-rcl-field-uploader.php';
require_once 'classes/types/class-rcl-field-file.php';
require_once 'classes/types/class-rcl-field-hidden.php';
function rcl_fields_scripts() {
	rcl_enqueue_style( 'rcl-fields', RCL_URL . 'modules/fields/assets/style.css', false, false, true );
	rcl_enqueue_script( 'rcl-fields', RCL_URL . 'modules/fields/assets/scripts.js', ['rcl-core-scripts' ], false, true );
}

if ( is_admin() || isset( $_REQUEST['rest_route'] ) ) {
	rcl_fields_scripts();
} else {
	add_action( 'rcl_enqueue_scripts', 'rcl_fields_scripts', 10 );
}
