<?php

class Rcl_Orders_Table_Manager extends Rcl_Table_Manager {

	public $user_id;

	function __construct( $args = array() ) {
		global $user_ID;

		$this->init_custom_prop( 'user_id', isset( $args['user_id'] ) ? $args['user_id'] : $user_ID  );

		parent::__construct( array(
			'number'	 => 30,
			'orderby'	 => 'order_id',
			'order'		 => 'DESC',
			'is_ajax'	 => 1,
		) );
	}

	function get_table_cols() {

		return array(
			'order_id'			 => array(
				'title'	 => __( 'Order number', 'wp-recall' ),
				'sort'	 => true,
				'search' => true,
				'align'	 => 'center',
				'width'	 => 20
			),
			'order_date'		 => array(
				'title'	 => __( 'Order date', 'wp-recall' ),
				'sort'	 => true,
				'align'	 => 'center',
				'width'	 => 20,
			),
			'products_amount'	 => array(
				'title'	 => __( 'Number of goods', 'wp-recall' ),
				'sort'	 => true,
				'align'	 => 'center',
				'width'	 => 20,
			),
			'order_price'		 => array(
				'title'	 => __( 'Sum', 'wp-recall' ),
				'sort'	 => true,
				'align'	 => 'center',
				'width'	 => 20,
			),
			'order_status'		 => array(
				'title'	 => __( 'Order status', 'wp-recall' ),
				'sort'	 => true,
				'align'	 => 'center',
				'width'	 => 20,
			)
		);
	}

	function get_query() {

		return RQ::tbl( new Rcl_Orders_Query() )
				->select( [
					'order_id',
					'order_date',
					'products_amount',
					'order_price',
					'order_status'
				] )
				->where( [
					'user_id'				 => $this->user_id,
					'order_status'			 => $this->get_request_data_value( 'order_status' ),
					'order_id'				 => $this->get_request_data_value( 'order_id' ),
					'order_date'			 => $this->get_request_data_value( 'order_date' ),
					'order_price__between'	 => $this->get_request_data_value( 'order_price' )
				] );
	}

	function get_table_row( $rowData ) {

		return array(
			'order_id'			 => rcl_get_button( [
				'label'	 => $rowData->order_id,
				'type'	 => 'simple',
				'href'	 => rcl_get_tab_permalink( $this->user_id, 'orders', 'single-order' ) . '&order-id=' . $rowData->order_id
			] ),
			'order_date'		 => mysql2date( 'Y-m-d H:i', $rowData->order_date ),
			'products_amount'	 => $rowData->products_amount,
			'order_price'		 => $rowData->order_price . ' ' . rcl_get_primary_currency( 1 ),
			'order_status'		 => rcl_get_status_name_order( $rowData->order_status )
		);
	}

	function get_search_fields() {

		return array(
			array(
				'type'			 => 'select',
				'slug'			 => 'order_status',
				'title'			 => __( 'Статус заказа', 'wp-recall' ),
				'empty_first'	 => __( 'Все заказы', 'wp-recall' ),
				'values'		 => rcl_order_statuses(),
				'value'			 => $this->get_request_data_value( 'order_status' ),
			),
			array(
				'type'		 => 'range',
				'slug'		 => 'order_price',
				'title'		 => __( 'Сумма заказа', 'wp-recall' ),
				'value_max'	 => RQ::tbl( new Rcl_Orders_Query() )->where( [
					'user_id'		 => $this->user_id,
					'order_status'	 => $this->get_request_data_value( 'order_status' )
				] )->get_max( 'order_price' ),
				'value'		 => $this->get_request_data_value( 'order_price' ),
			)
		);
	}

}
