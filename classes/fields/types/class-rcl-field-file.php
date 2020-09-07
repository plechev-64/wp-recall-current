<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-rcl-custom-field-text
 *
 * @author Андрей
 */
class Rcl_Field_File extends Rcl_Field_Uploader {
	function __construct( $args ) {

		if ( isset( $args['ext-files'] ) )
			$args['file_types'] = $args['ext-files'];

		if ( isset( $args['sizefile'] ) )
			$args['max_size'] = $args['sizefile'];

		parent::__construct( $args );
	}

	function get_options() {

		$options = array(
			array(
				'slug'			 => 'icon',
				'default'		 => 'fa-file',
				'placeholder'	 => 'fa-file',
				'class'			 => 'rcl-iconpicker',
				'type'			 => 'text',
				'title'			 => __( 'Icon class of  font-awesome', 'wp-recall' )
			),
			array(
				'slug'		 => 'max_size',
				'default'	 => $this->max_size,
				'type'		 => 'runner',
				'unit'		 => 'Kb',
				'value_min'	 => 256,
				'value_max'	 => 5120,
				'value_step' => 256,
				'title'		 => __( 'File size', 'wp-recall' ),
				'notice'	 => __( 'maximum size of uploaded file, Kb (Default - 512)', 'wp-recall' )
			),
			array(
				'slug'		 => 'file_types',
				'default'	 => $this->file_types,
				'type'		 => 'text',
				'title'		 => __( 'Allowed file types', 'wp-recall' ),
				'notice'	 => __( 'allowed types of files are divided by comma, for example: pdf, zip, jpg', 'wp-recall' )
			)
		);

		return $options;
	}

	function get_uploader_props() {
		global $user_ID;

		return wp_parse_args( $this->uploader_props, array(
			'user_id'		 => $user_ID,
			'multiple'		 => 0,
			'max_size'		 => $this->max_size,
			'auto_upload'	 => 1,
			'file_types'	 => array_map( 'trim', explode( ',', $this->file_types ) ),
			'max_files'		 => 1,
			'crop'			 => 0,
			'input_attach'	 => $this->input_name,
			'mode_output'	 => 'list'
			) );
	}

}
