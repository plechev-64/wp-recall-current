<?php

RCL()->use_module( 'fields-manager' );

class Rcl_Register_Form_Manager extends Rcl_Fields_Manager {
	function __construct() {

		parent::__construct( 'register_form', array(
			'empty_field'	 => 0,
			'create_field'	 => 0,
			'option_name'	 => 'rcl_register_form_fields',
			'structure_edit' => 1,
			'default_fields' => apply_filters( 'rcl_register_form_default_fields', $this->get_default_fields() ),
			'field_options'	 => apply_filters( 'rcl_register_form_field_options', array(
				array(
					'type'			 => 'text',
					'slug'			 => 'icon',
					'class'			 => 'rcl-iconpicker',
					'title'			 => __( 'Icon class', 'wp-recall' ),
					'placeholder'	 => __( 'Example, fa-user', 'wp-recall' )
				),
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

	function get_default_fields() {

		$fields = get_site_option( 'rcl_profile_fields' );

		if($fields){
			foreach($fields as $k => $field){
				if(in_array($field['slug'], ['primary_pass', 'repeat_pass', 'user_email'])){
					unset($fields[$k]);
				}
			}
		}

		if ( ! $fields )
			$fields = [ ];

		$fields[] = [
			'type'			 => 'text',
			'slug'			 => 'user_login',
			'title'			 => __( 'Логин', 'wp-recall' ),
			'placeholder'	 => __( 'Логин', 'wp-recall' ),
			'icon'			 => 'fa-user',
			'maxlenght'		 => 50,
			'required'		 => 1
		];

		$fields[] = [
			'type'		 => 'password',
			'slug'		 => 'user_pass',
			'icon'		 => 'fa-lock',
			'title'		 => __( 'Пароль', 'wp-recall' ),
			'required'	 => 1
		];

		$fields[] = [
			'type'		 => 'password',
			'slug'		 => 'user_pass_repeat',
			'icon'		 => 'fa-lock',
			'title'		 => __( 'Повтор пароля', 'wp-recall' ),
			'required'	 => 1
		];

		return $fields;
	}

}
