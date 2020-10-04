<?php

RCL()->use_module( 'fields-manager' );

$Manager = new Rcl_Fields_Manager( 'products-variations', array(
	'switch_type'	 => 1,
	'types'			 => array(
		'select',
		'checkbox',
		'radio'
	),
	'field_options'	 => array(
		array(
			'type'	 => 'textarea',
			'slug'	 => 'notice',
			'title'	 => __( 'field description', 'wp-recall' )
		)
	)
	) );

$content = '<h2>' . __( 'Products variations management', 'wp-recall' ) . '</h2>';

$content .= $Manager->get_manager();

echo $content;

