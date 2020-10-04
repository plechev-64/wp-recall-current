<?php

RCL()->use_module( 'fields-manager' );

class Rcl_Register_Form_Manager extends Rcl_Fields_Manager {
	function __construct() {

		parent::__construct( 'register_form', array(
			'empty_field'	 => false,
			'create_field'	 => false,
			'option_name'	 => 'rcl_register_form_fields',
			'structure_edit' => true,
			'default_fields' => apply_filters( 'rcl_register_form_default_fields', $this->get_profile_fields() ),
			'field_options'	 => apply_filters( 'rcl_register_form_field_options', array(
				array(
					'type'	 => 'radio',
					'slug'	 => 'required',
					'title'	 => __( 'required field', 'usp' ),
					'values' => array( __( 'No', 'usp' ), __( 'Yes', 'usp' ) )
				)
			) )
		) );

		$this->setup_default_fields();
	}

	function get_profile_fields() {
		return get_site_option( 'rcl_profile_fields' );
	}

}
