<?php

rcl_ajax_action( 'rcl_get_table_manager_cols', true, true );
function rcl_get_table_manager_cols() {

	$manager_id		 = $_POST['manager_id'];
	$cols			 = $_POST['cols'];
	$active_cols	 = $_POST['active_cols'];
	$disabled_cols	 = $_POST['disabled_cols'];

	$manager = new Rcl_Table_Cols_Manager( $manager_id, array(
		'cols'			 => $cols,
		'active_cols'	 => $active_cols,
		'disabled_cols'	 => $disabled_cols,
		) );

	return array(
		'dialog' => array(
			'size'		 => 'medium',
			'title'		 => __( 'Менеджер колонок' ),
			'content'	 => $manager->get_manager()
		)
	);
}

rcl_ajax_action( 'rcl_save_table_manager_cols', true, true );
function rcl_save_table_manager_cols() {

	$manager_id	 = $_POST['manager_id'];
	$col_ids	 = $_POST['col_ids'];

	setcookie( $manager_id, json_encode( $col_ids ), time() + 3600 * 24 * 30 * 12, '/', $_SERVER['HOST'] );

	return array(
		'success'	 => __( 'Структура таблицы сохранена!' ),
		'reload'	 => true
	);
}

rcl_ajax_action( 'rcl_load_content_manager', true, true );
function rcl_load_content_manager() {

	$class		 = $_REQUEST['classname'];
	$classargs	 = isset( $_POST['classargs'] ) ? $_POST['classargs'] : null;
	$tail		 = isset( $_POST['tail'] ) ? $_POST['tail'] : null;

	$Manager = new $class( $classargs );

	return array(
		'content' => $Manager->get_manager_content()
	);
}

rcl_ajax_action( 'rcl_load_content_manager_state', true, true );
function rcl_load_content_manager_state() {

	$state		 = json_decode( wp_unslash( $_REQUEST['state'] ), true );
	$class		 = $state['classname'];
	$classargs	 = isset( $state['classargs'] ) ? $state['classargs'] : null;

	$Manager = new $class( $classargs );

	return array(
		'content' => $Manager->get_manager_content()
	);
}
