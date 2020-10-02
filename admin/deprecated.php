<?php

/* 16.0.0 */
add_action( 'admin_init', 'rcl_update_custom_fields', 10 );
function rcl_update_custom_fields() {
	global $wpdb;

	if ( ! isset( $_POST['rcl_save_custom_fields'] ) )
		return false;

	if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'rcl-update-custom-fields' ) )
		return false;

	$fields = array();

	$table = 'postmeta';

	if ( $_POST['rcl-fields-options']['name-option'] == 'rcl_profile_fields' )
		$table = 'usermeta';

	$POSTDATA = apply_filters( 'rcl_pre_update_custom_fields_options', $_POST );

	if ( ! $POSTDATA )
		return false;

	if ( isset( $POSTDATA['rcl_deleted_custom_fields'] ) ) {

		$deleted = explode( ',', $POSTDATA['rcl_deleted_custom_fields'] );

		if ( $deleted ) {

			foreach ( $deleted as $slug ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->$table . " WHERE meta_key = '%s'", $slug ) );
			}
		}
	}

	$newFields = array();

	if ( isset( $POSTDATA['new-field'] ) ) {

		$nKey = 0;

		foreach ( $POSTDATA['new-field'] as $optionSlug => $vals ) {
			$newFields[$nKey] = $vals;
			$nKey ++;
		}
	}

	$fields	 = array();
	$nKey	 = 0;

	foreach ( $POSTDATA['fields'] as $k => $slug ) {

		if ( ! $slug ) {

			if ( ! isset( $newFields[$nKey] ) || ! $newFields[$nKey]['title'] )
				continue;

			if ( isset( $newFields[$nKey]['slug'] ) && $newFields[$nKey]['slug'] )
				$slug	 = $newFields[$nKey]['slug'];
			else
				$slug	 = str_replace( array( '-', ' ' ), '_', rcl_sanitize_string( $newFields[$nKey]['title'] ) . '-' . rand( 10, 100 ) );

			$field = $newFields[$nKey];

			$nKey ++;
		}else {

			if ( ! isset( $POSTDATA['field'][$slug] ) )
				continue;

			$field = $POSTDATA['field'][$slug];
		}

		$field['slug'] = $slug;

		$fields[] = $field;
	}

	foreach ( $fields as $k => $field ) {

		if ( isset( $field['values'] ) && $field['values'] && is_array( $field['values'] ) ) {

			$values = array();
			foreach ( $field['values'] as $val ) {
				if ( $val == '' )
					continue;
				$values[] = $val;
			}

			$fields[$k]['values'] = $values;
		}
	}

	if ( isset( $POSTDATA['options'] ) ) {
		$fields['options'] = $POSTDATA['options'];
	}

	update_site_option( $_POST['rcl-fields-options']['name-option'], $fields );

	do_action( 'rcl_update_custom_fields', $fields, $POSTDATA );

	wp_redirect( $_POST['_wp_http_referer'] );
	exit;
}

rcl_ajax_action( 'rcl_get_new_custom_field', false );
function rcl_get_new_custom_field() {

	$post_type	 = $_POST['post_type'];
	$primary	 = ( array ) json_decode( wp_unslash( $_POST['primary_options'] ) );
	$default	 = ( array ) json_decode( wp_unslash( $_POST['default_options'] ) );

	$manageFields = new Rcl_Custom_Fields_Manager( $post_type, $primary );

	if ( $default ) {

		$manageFields->defaultOptions = array();

		foreach ( $default as $option ) {
			$manageFields->defaultOptions[] = ( array ) $option;
		}
	}

	$content = $manageFields->empty_field();

	wp_send_json( array(
		'content' => $content
	) );
}

rcl_ajax_action( 'rcl_get_custom_field_options', false );
function rcl_get_custom_field_options() {

	$type_field	 = $_POST['type_field'];
	$old_type	 = $_POST['old_type'];
	$post_type	 = $_POST['post_type'];
	$slug_field	 = $_POST['slug'];

	$primary = ( array ) json_decode( wp_unslash( $_POST['primary_options'] ) );
	$default = ( array ) json_decode( wp_unslash( $_POST['default_options'] ) );

	$manageFields = new Rcl_Custom_Fields_Manager( $post_type, $primary );

	if ( $default ) {

		$manageFields->defaultOptions = array();

		foreach ( $default as $option ) {
			$manageFields->defaultOptions[] = ( array ) $option;
		}
	}

	$manageFields->field = array( 'type' => $type_field );

	if ( strpos( $slug_field, 'CreateNewField' ) === false ) {

		$manageFields->field['slug'] = $slug_field;
	} else {

		$manageFields->field['slug'] = '';
		$manageFields->new_slug		 = $slug_field;
	}

	$content = $manageFields->get_options();

	$multiVars = array(
		'select',
		'radio',
		'checkbox',
		'multiselect'
	);

	if ( in_array( $type_field, $multiVars ) ) {

		$content .= '<script>'
			. "jQuery('#field-" . $slug_field . " .rcl-field-input .dynamic-values').sortable({
             containment: 'parent',
             placeholder: 'ui-sortable-placeholder',
             distance: 15,
             stop: function( event, ui ) {
                 var items = ui.item.parents('.dynamic-values').find('.dynamic-value');
                 items.each(function(f){
                     if(items.length == (f+1)){
                         jQuery(this).children('a').attr('onclick','rcl_add_dynamic_field(this);return false;').children('i').attr('class','fa-plus');
                     }else{
                         jQuery(this).children('a').attr('onclick','rcl_remove_dynamic_field(this);return false;').children('i').attr('class','fa-minus');
                     }
                 });

             }
         });"
			. '</script>';
	}

	wp_send_json( array(
		'content' => $content
	) );
}
