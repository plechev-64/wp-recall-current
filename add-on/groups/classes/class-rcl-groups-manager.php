<?php

class Rcl_Groups_Manager extends Rcl_Content_Manager {

	public $user_id;
	public $admin_id;
	public $template;

	function __construct( $args = array() ) {

		$this->init_custom_prop( 'user_id', isset( $args['user_id'] ) ? $args['user_id'] : 0  );
		$this->init_custom_prop( 'admin_id', isset( $args['admin_id'] ) ? $args['admin_id'] : 0  );
		$this->init_custom_prop( 'template', isset( $args['template'] ) ? $args['template'] : 'list'  );

		parent::
		__construct( array(
			'number'	 => 30,
			'is_ajax'	 => 1,
		) );
	}

	function get_query() {

		$query = RQ::tbl( new Rcl_Groups_Query() )
			->select( [
				'ID',
				'admin_id',
				'group_users',
				'group_status',
				'group_date'
			] )
			->where( [
				'user_id'		 => $this->user_id ? $this->user_id : null,
				'admin_id'		 => $this->admin_id ? $this->admin_id : null,
				'group_status'	 => $this->get_request_data_value( 'group_status' ),
				'group_users'	 => $this->get_request_data_value( 'group_users' ),
				'group_date'	 => $this->get_request_data_value( 'group_date' )
			] )
			->orderby( $this->get_request_data_value( 'orderby', 'group_date' ), $this->get_request_data_value( 'order', 'DESC' ) )
			->setup_termdata();

		if ( $name = $this->get_request_data_value( 'name' ) ) {
			$query->where_string( 'wp_terms.name LIKE "%' . $name . '%"' );
		}

		return $query;
	}

	function get_item_content( $itemData ) {
		global $rcl_group;
		$rcl_group = $itemData;
		return rcl_get_include_template( 'group-' . $this->template . '.php', __FILE__ );
	}

	function get_search_fields() {

		$maxUsers = RQ::tbl( new Rcl_Groups_Query() )->where( [
				'user_id'		 => $this->user_id ? $this->user_id : null,
				'admin_id'		 => $this->admin_id ? $this->admin_id : null,
				'group_status'	 => $this->get_request_data_value( 'group_status' ),
			] )->get_max( 'group_users' );

		$fields = array(
			array(
				'type'	 => 'text',
				'slug'	 => 'name',
				'title'	 => __( 'Наименование', 'wp-recall' ),
				'value'	 => $this->get_request_data_value( 'name' ),
			),
			array(
				'type'			 => 'select',
				'slug'			 => 'group_status',
				'title'			 => __( 'Статус группы', 'wp-recall' ),
				'empty_first'	 => __( 'Все группы', 'wp-recall' ),
				'values'		 => [
					'open'	 => __(
						'Открытые группы', 'wp-recall' ),
					'closed' => __( 'Закрытые группы', 'wp-recall' )
				],
				'value'			 => $this->get_request_data_value( 'group_status' ),
			),
			array(
				'type'	 => 'select',
				'slug'	 => 'orderby',
				'title'	 => __( 'Сортировка по', 'wp-recall' ),
				'values' => [
					'ID'			 => __( 'Дате создания', 'wp-recall' ),
					'group_users'	 => __( 'Количеству пользователей', 'wp-recall' )
				],
				'value'	 => $this->get_request_data_value( 'orderby', 'group_date' ),
			), array(
				'type'	 => 'radio',
				'slug'	 => 'order',
				'title'	 => __( 'Направление сортировки', 'wp-recall' ),
				'values' => [
					'DESC'	 => __( 'По убыванию', 'wp-recall' ),
					'ASC'	 => __( 'По возрастанию', 'wp-recall' )
				],
				'value'	 => $this->get_request_data_value( 'order', 'DESC' ),
			)
		);

		if ( $maxUsers ) {
			$fields[] = [
				'type'		 => 'range',
				'slug'		 => 'group_users',
				'title'		 => __( 'Пользователи', 'wp-recall' ),
				'value_max'	 => $maxUsers,
				'value'		 => $this->get_request_data_value( 'group_users' ),
			];
		}

		return $fields;
	}

}
