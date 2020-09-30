<?php

class Rcl_Content_Manager {

	public $data			 = array();
	public $orderby			 = '';
	public $order			 = 'DESC';
	public $total_items		 = 0;
	public $number			 = 30;
	public $pagenavi		 = true;
	public $pagename		 = '';
	public $is_ajax			 = 0;
	public $dropdown_filter	 = true;
	public $callback_actions = 'rcl_admin_manager_actions(this);';
	public $actions			 = array();
	public $buttons			 = array();
	public $request_data	 = [ ];
	public $startpage		 = '';
	public $custom_props	 = [ ];
	public $query			 = false;
	public $startstate		 = false;

	function __construct( $args ) {

		$this->init_properties( $args );

		if ( $this->is_ajax && ! $this->startstate ) {

			if ( $this->custom_props ) {
				foreach ( $this->custom_props as $propName ) {
					$args[$propName] = $this->$propName;
				}
			}

			$this->startstate = json_encode( [
				'classname'	 => get_class( $this ),
				'classargs'	 => $args
				] );
		}

		if ( isset( $_REQUEST['orderby'] ) )
			$this->orderby = $_REQUEST['orderby'];

		if ( isset( $_REQUEST['order'] ) )
			$this->order = $_REQUEST['order'];

		if ( isset( $_REQUEST['ajax'] ) )
			$this->is_ajax = $_REQUEST['ajax'];

		if ( isset( $_REQUEST['startpage'] ) )
			$this->startpage = $_REQUEST['startpage'];

		$this->request_data = array_merge( [
			'order',
			'orderby',
			'ajax',
			'pagenum',
			'classname',
			'startpage'
			], $this->request_data );

		$this->query = $this->get_query();

		$this->set_total_items();

		$this->pager = new Rcl_Pager( array(
			'number'	 => $this->number,
			'total'		 => $this->total_items,
			'page_args'	 => array(
				'onclick' => 'rcl_load_content_manager_page("page", "pagenum", this);return false;'
			)
			) );

		if ( $this->total_items )
			$this->set_data();

		$this->setup_startpage();
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[$name] ) )
				$this->$name = $args[$name];
		}
	}

	function setup_startpage() {

		$cancel_url_args = [ ];

		foreach ( $this->request_data as $data ) {
			$cancel_url_args[$data] = false;
		}

		if ( ! $this->startpage ) {
			$this->startpage = add_query_arg( $cancel_url_args );
		}
	}

	function get_request_data_value( $dataKey ) {
		return isset( $_REQUEST[$dataKey] ) && $_REQUEST[$dataKey] ? $_REQUEST[$dataKey] : null;
	}

	function init_custom_prop( $varName, $defaultValue = null ) {
		$this->$varName			 = isset( $_REQUEST[$varName] ) ? $_REQUEST[$varName] : $defaultValue;
		$this->custom_props[]	 = $varName;
	}

	function set_data() {

		if ( ! $this->query ) {
			return false;
		}

		$this->data = $this->query
			->limit( $this->number, $this->pager->offset )
			->orderby( $this->orderby, $this->order )
			->get_results();
	}

	function set_total_items() {

		if ( ! $this->query ) {
			return 0;
		}

		$this->total_items = $this->query->get_count();
	}

	function get_query() {
		return false;
	}

	function get_search_fields() {
		return [ ];
	}

	function get_no_result_notice() {
		return rcl_get_notice( array(
			'type'	 => 'info',
			'text'	 => __( 'Ничего не найдено' )
			) );
	}

	function get_actions() {
		return [ ];
	}

	function get_item_content( $dataItem ) {
		return false;
	}

	function get_buttons_args() {
		return [ ];
	}

	function get_buttons() {

		$buttonsArgs = $this->get_buttons_args();

		$buttonsArgs[] = array(
			'label'		 => __( 'Сбросить фильтр' ),
			'onclick'	 => $this->is_ajax ? 'rcl_load_content_manager_state("' . wp_slash( $this->startstate ) . '", this);return false;' : null,
			'icon'		 => 'fa-refresh',
			'href'		 => $this->startpage
		);

		$content = '<div class="manager-buttons">';
		foreach ( $buttonsArgs as $args ) {
			$content .= rcl_get_button( $args );
		}
		$content .= '</div>';

		return $content;
	}

	function get_manager_content() {

		$content = '<div class="rcl-content-manager">';

		$content .= $this->get_hidden_fields();

		$content .= $this->get_buttons();

		$content .= $this->get_search();

		$content .= $this->get_actions_box();

		if ( $this->pagenavi && $this->pager->pages > 1 ) {
			$content .= $this->pager->get_pager();
		}

		$content .= $this->get_data_content();

		if ( $this->pagenavi && $this->pager->pages > 1 ) {
			$content .= $this->pager->get_pager();
		}

		$content .= '</div>';

		return $content;
	}

	function get_data_content() {

		$content .= '<div class="manager-content">';

		if ( ! $this->data ) {
			$content .= $this->get_no_result_notice();
		} else {

			foreach ( $this->data as $dataItem ) {
				$content .= $this->get_item_content( $dataItem );
			}
		}
		$content .= '</div>';

		return $content;
	}

	function get_manager() {

		$content = '<form action method="get" ' . ($this->is_ajax ? 'onsubmit="rcl_content_manager_submit();return false;"' : '') . ' class="preloader-parent">';

		$content .= $this->get_manager_content();

		$content .= '</form>';

		return $content;
	}

	function get_actions_box() {

		if ( ! $this->get_actions() ) {
			return false;
		}

		$form = new Rcl_Form( array(
			'fields'	 => array(
				array(
					'type'	 => 'select',
					'slug'	 => 'action-items',
					'values' => $this->get_actions()
				)
			),
			'submit'	 => __( 'Применить' ),
			'onclick'	 => $this->callback_actions . ';return false;'
			) );

		$content = '<div class="items-actions-box">';

		$content .= $form->get_fields_list();

		$content .= $form->get_submit_box();

		$content .= '</div>';

		return $content;
	}

	function get_search() {

		if ( ! $fields = $this->get_search_fields() )
			return false;

		$form = new Rcl_Form( array(
			'fields'	 => $fields,
			'submit'	 => __( 'Поиск' ),
			'onclick'	 => 'rcl_content_manager_submit(this);return false;'
			)
		);

		$content = '<div id="rcl-manager-filter" class="rcl-form' . ($this->dropdown_filter ? ' dropdown-filter' : '') . '">';

		if ( $this->dropdown_filter ) {
			$content .= rcl_get_button( [
				'label'		 => __( 'Поиск' ),
				'fullwidth'	 => 1,
				'size'		 => 'medium',
				'icon'		 => 'fa-search',
				'onclick'	 => 'jQuery(this).next().slideToggle(); return false;'
				] );
			$content .= '<div class="filter-content">';
		}

		$content .= '<div class="form-fields">';
		$content .= $form->get_fields_list();
		$content .= '</div>';

		$content .= $form->get_submit_box();

		if ( $this->dropdown_filter ) {
			$content .= '</div>';
		}

		$content .= '</div>';

		return $content;
	}

	function get_hidden_fields() {

		$content = '<input type="hidden" id="value-pagenum" name="pagenum" value="' . $this->pager->current . '">';
		$content .= '<input type="hidden" id="value-orderby" name="orderby" value="' . $this->orderby . '">';
		$content .= '<input type="hidden" id="value-order" name="order" value="' . $this->order . '">';

		if ( $this->is_ajax ) {
			$content .= '<input type="hidden" id="value-startpage" name="startstate" value="' . $this->startstate . '">';
			$content .= '<input type="hidden" id="value-ajax" name="ajax" value="' . $this->is_ajax . '">';
			$content .= '<input type="hidden" id="value-classname" name="classname" value="' . get_class( $this ) . '">';
		} else {
			$content .= '<input type="hidden" id="value-startpage" name="startpage" value="' . $this->startpage . '">';
		}

		if ( $this->custom_props ) {
			foreach ( $this->custom_props as $propName ) {
				$content .= '<input type="hidden" id="value-' . $propName . '" name="' . $propName . '" value="' . $this->$propName . '">';
			}
		}

		if ( isset( $_POST['tail'] ) && $_POST['tail'] ) {
			foreach ( $_POST['tail'] as $name ) {

				if ( ! isset( $_POST['prevs'][$name] ) )
					continue;

				$value = $_POST['prevs'][$name];

				if ( is_array( $value ) ) {
					foreach ( $value as $k => $val ) {
						$content .= '<input type="hidden" id="value-' . $name . $k . '" name="' . $name . '[]" value="' . $val . '">';
					}
				} else {
					$content .= '<input type="hidden" id="value-' . $name . '" name="' . $name . '" value="' . $value . '">';
				}
			}
		}

		return $content;
	}

	function link_manager( $linkLabel, $managerArgs ) {

		if ( ! $linkLabel )
			return $linkLabel;

		$managerArgs['prevs'] = $_POST;

		return '<a href="#" onclick=\'rcl_load_content_manager(this, ' . json_encode( $managerArgs ) . ');return false;\'>' . $linkLabel . '</a>';
	}

}
