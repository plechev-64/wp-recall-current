<?php

$Manager = new Rcl_Fields_Manager( 'products-variations', array(
	'switch_type'   => true,
	'types'         => array(
		'select',
		'checkbox',
		'radio'
	),
	'field_options' => array(
		array(
			'type'  => 'textarea',
			'slug'  => 'notice',
			'title' => __( 'field description', 'wp-recall' )
		)
	)
) );

$content = '<h2>' . esc_html__( 'Products variations management', 'wp-recall' ) . '</h2>';

$content .= $Manager->get_manager();
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $content;

