<?php

add_filter( 'rcl_field_options', 'rcl_edit_field_options', 10, 3 );
function rcl_edit_field_options( $options, $field, $manager_id ) {

	$types = array( 'range', 'runner' );

	if ( in_array( $field->type, $types ) ) {

		foreach ( $options as $k => $option ) {

			if ( $option['slug'] == 'required' ) {
				unset( $options[$k] );
			}
		}
	}

	return $options;
}

rcl_ajax_action( 'rcl_manager_get_new_field', false );
function rcl_manager_get_new_field() {

	$managerProps = $_POST['props'];

	$Manager = new Rcl_Fields_Manager( $managerProps['manager_id'], $managerProps );

	$field_id = 'newField-' . rand( 1, 10000 );

	$Manager->add_field( array(
		'slug'	 => $field_id,
		'type'	 => $Manager->types[0],
		'_new'	 => true
	) );

	wp_send_json( array(
		'content' => $Manager->get_field_manager( $field_id )
	) );
}

rcl_ajax_action( 'rcl_manager_get_custom_field_options', false );
function rcl_manager_get_custom_field_options() {

	$new_type	 = $_POST['newType'];
	$old_type	 = $_POST['oldType'];
	$field_id	 = $_POST['fieldId'];

	$managerProps = $_POST['manager'];

	$Manager = new Rcl_Fields_Manager( $managerProps['manager_id'], $managerProps );

	if ( stristr( $field_id, 'newField' ) !== FALSE ) {

		$Manager->add_field( array(
			'slug'	 => $field_id,
			'type'	 => $new_type,
			'_new'	 => true
		) );
	} else {

		$Manager->set_field_prop( $field_id, 'type', $new_type );

		$Manager->fields[$field_id] = $Manager::setup( ( array ) $Manager->fields[$field_id] );
	}

	$content = $Manager->get_field_options_content( $field_id );

	$multiVars = array(
		'select',
		'radio',
		'checkbox',
		'multiselect'
	);

	if ( in_array( $new_type, $multiVars ) ) {

		$content .= $Manager->sortable_dynamic_values_script( $field_id );
	}

	wp_send_json( array(
		'content' => $content
	) );
}

rcl_ajax_action( 'rcl_manager_get_new_area', false );
function rcl_manager_get_new_area() {

	$managerProps = $_POST['props'];

	$Manager = new Rcl_Fields_Manager( 'any', $managerProps );

	wp_send_json( array(
		'content' => $Manager->get_active_area()
	) );
}

rcl_ajax_action( 'rcl_manager_get_new_group', false );
function rcl_manager_get_new_group() {

	$managerProps = $_POST['props'];

	$Manager = new Rcl_Fields_Manager( 'any', $managerProps );

	wp_send_json( array(
		'content' => $Manager->get_group_areas()
	) );
}

rcl_ajax_action( 'rcl_manager_update_fields_by_ajax', false, true );
function rcl_manager_update_fields_by_ajax() {

	return rcl_manager_update_data_fields();
}

add_action( 'admin_init', 'rcl_manager_update_fields_by_post', 10 );
function rcl_manager_update_fields_by_post() {
	global $wpdb;

	if ( ! isset( $_POST['rcl_manager_update_fields_by_post'] ) )
		return false;

	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'rcl-update-custom-fields' ) )
		return false;

	rcl_manager_update_data_fields();

	wp_redirect( $_POST['_wp_http_referer'] );
	exit;
}

function rcl_manager_update_data_fields() {
	global $wpdb;

	$copy		 = $_POST['copy'];
	$manager_id	 = $_POST['manager_id'];
	$option_name = $_POST['option_name'];

	$fieldsData	 = wp_unslash( $_POST['fields'] );
	$structure	 = isset( $_POST['structure'] ) ? $_POST['structure'] : false;

	$fields		 = array();
	$keyFields	 = array();
	$changeIds	 = array();
	$isset_new	 = false;
	foreach ( $fieldsData as $field_id => $field ) {

		if ( ! $field['title'] )
			continue;

		if ( isset( $field['values'] ) ) {
			//удаляем из массива values пустые значения
			$values = array();
			foreach ( $field['values'] as $k => $v ) {
				if ( $v == '' )
					continue;
				$values[$k] = $v;
			}
			$field['values'] = $values;
		}

		if ( stristr( $field_id, 'newField' ) !== FALSE ) {

			$isset_new = true;

			$old_id = $field_id;

			if ( ! $field['id'] ) {

				$field_id = str_replace( array( '-', ' ' ), '_', rcl_sanitize_string( $field['title'] ) . '-' . rand( 1, 100 ) );
			} else {
				$field_id = $field['id'];
			}

			$changeIds[$old_id] = $field_id;
		}

		$field['slug'] = $field_id;

		$keyFields[$field_id] = 1;

		unset( $field['id'] );

		$fields[] = $field;
	}

	if ( $structure ) {

		$strArray	 = array();
		$area_id	 = -1;

		foreach ( $structure as $value ) {

			if ( is_array( $value ) ) {

				if ( isset( $value['group_id'] ) ) {
					$group_id = $value['group_id'];

					$strArray[$group_id] = isset( $_POST['structure-groups'][$group_id] ) ? $_POST['structure-groups'][$group_id] : array();
				} else if ( isset( $value['field_id'] ) ) {
					$strArray[$group_id]['areas'][$area_id]['fields'][] = $value['field_id'];
				}
			} else {
				$area_id ++;
				$strArray[$group_id]['areas'][$area_id]['width'] = isset( $_POST['structure-areas'][$area_id]['width'] ) ? $_POST['structure-areas'][$area_id]['width'] : 0;
			}
		}

		$endStructure = array();

		foreach ( $strArray as $group_id => $group ) {

			if ( isset( $group['id'] ) && $group_id != $group['id'] ) {
				$group_id = $group['id'];
			}

			$endStructure[$group_id]			 = $group;
			$endStructure[$group_id]['areas']	 = array();

			foreach ( $group['areas'] as $area ) {

				$fieldsArea = array();

				foreach ( $area['fields'] as $k => $field_id ) {

					if ( isset( $changeIds[$field_id] ) ) {
						$field_id = $changeIds[$field_id];
					}

					if ( ! isset( $keyFields[$field_id] ) ) {
						unset( $area['fields'][$k] );
						continue;
					}

					$fieldsArea[] = $field_id;
				}

				$endStructure[$group_id]['areas'][] = array(
					'width'	 => round( $area['width'], 0 ),
					'fields' => $fieldsArea
				);
			}
		}

		$structure = $endStructure;
	}

	$fields = apply_filters( 'rcl_pre_update_manager_fields', $fields, $manager_id );

	update_site_option( $option_name, $fields );

	$args = array(
		'success' => __( 'Settings saved!', 'wp-recall' )
	);

	if ( $structure )
		update_site_option( 'rcl_fields_' . $manager_id . '_structure', $structure );
	else
		delete_site_option( 'rcl_fields_' . $manager_id . '_structure' );

	if ( isset( $_POST['deleted_fields'] ) && $_POST['deleted_fields'] ) {
		if ( isset( $_POST['delete_table_data'] ) ) {
			foreach ( $_POST['delete_table_data'] as $table_name => $colname ) {
				$wpdb->query( "DELETE FROM $table_name WHERE $colname IN ('" . implode( "','", $_POST['deleted_fields'] ) . "')" );
			}

			$args['reload'] = true;
		}
	}

	if ( $copy ) {

		update_site_option( 'rcl_fields_' . $copy, $fields );

		if ( $structure )
			update_site_option( 'rcl_fields_' . $copy . '_structure', $structure );

		do_action( 'rcl_fields_copy', $fields, $manager_id, $copy );

		$args['reload'] = true;
	}

	if ( $isset_new ) {
		$args['reload'] = true;
	}

	do_action( 'rcl_fields_update', $fields, $manager_id );

	return $args;
}
