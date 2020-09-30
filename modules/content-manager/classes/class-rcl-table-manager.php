<?php

class Rcl_Table_Manager extends Rcl_Content_Manager {

	public $cols_manager	 = '';
	public $default_cols	 = array();
	public $active_cols		 = array();
	public $disabled_cols	 = array();
	public $dropdown_filter	 = true;
	public $table			 = false;

	function __construct( $args ) {

		parent::__construct( $args );

		if ( $this->cols_manager ) {

			if ( isset( $_COOKIE[$this->cols_manager] ) && $_COOKIE[$this->cols_manager] ) {
				$this->active_cols = json_decode( wp_unslash( $_COOKIE[$this->cols_manager] ) );
			}

			if ( ! $this->active_cols ) {
				$this->active_cols = $this->default_cols ? $this->default_cols : array_keys( $this->get_table_cols() );
			}
		}
	}

	function get_sort_col( $dataKey ) {
		return array(
			'onclick'	 => 'rcl_order_table_manager_page(this);return false;',
			'order'		 => ($this->orderby == $dataKey) ? $this->order : null
		);
	}

	function get_search_col( $dataKey ) {
		return array(
			'submit' => 'rcl_table_manager_search_by_col'
		);
	}

	function get_table_cols() {
		return array();
	}

	function get_table_row( $item ) {

		$rowData = array();
		foreach ( $this->get_table_cols() as $colId => $colData ) {

			if ( ! isset( $colData['get_result'] ) )
				continue;

			$args = array();
			if ( isset( $colData['result_args'] ) ) {
				$args = $colData['result_args'];
			}

			$rowData[] = $colData['get_result']( $item, $args );
		}

		return $rowData;
	}

	function get_buttons_args() {

		if ( ! $this->cols_manager )
			return [ ];

		rcl_dialog_scripts();
		rcl_sortable_scripts();

		$cols	 = $this->get_table_cols();
		$allCols = array();
		foreach ( $cols as $colId => $col ) {
			$allCols[$colId] = $col['title'];
		}

		return [
			[
				'label'		 => __( 'Менеджер колонок' ),
				'icon'		 => 'fa-bars',
				'onclick'	 => 'return rcl_get_table_manager_cols("' . $this->cols_manager . '",' . json_encode( $allCols ) . ',' . json_encode( $this->active_cols ) . ',' . json_encode( $this->disabled_cols ) . ',this);return false;',
			]
		];
	}

	function get_data_content() {

		$content = '<div class="manager-content">';

		if ( ! $this->data ) {
			$content .= $this->get_no_result_notice();
		} else {

			$table = new Rcl_Table( array(
				'cols'	 => $this->filter_table_cols( $this->get_table_cols() ),
				'border' => array( 'rows', 'cols', 'table' ),
				'zebra'	 => true
				) );

			foreach ( $this->data as $item ) {

				$rowData = $this->get_table_row( $item );

				if ( $this->cols_manager ) {

					$newRowData = [ ];
					foreach ( $this->active_cols as $colID ) {
						$newRowData[$colID] = $rowData[$colID];
					}

					$rowData = $newRowData;
				}

				$table->add_row( $rowData );
			}

			$content .= $table->get_table();
		}
		$content .= '</div>';

		return $content;
	}

	function filter_table_cols( $cols ) {

		if ( $this->cols_manager ) {

			$newCols = array();
			foreach ( $this->active_cols as $colId ) {

				if ( ! isset( $cols[$colId] ) )
					continue;
				$newCols[$colId] = $cols[$colId];
			}

			$cols = $newCols;
		}

		foreach ( $cols as $colId => $colData ) {

			if ( isset( $colData['sort'] ) && $colData['sort'] ) {
				$cols[$colId]['sort'] = $this->get_sort_col( $colId );
			}

			if ( isset( $colData['search'] ) && $colData['search'] ) {
				$cols[$colId]['search'] = $this->get_search_col( $colId );
			}
		}

		return $cols;
	}

}
