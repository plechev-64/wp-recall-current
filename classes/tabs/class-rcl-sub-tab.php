<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-rcl-sub-tabs
 *
 * @author Андрей
 */
class Rcl_Sub_Tab {

	public $id;
	public $parent_id;
	public $name	 = false;
	public $title	 = false;
	public $icon	 = 'fa-cog';
	public $supports = array();
	public $counter	 = null;
	public $callback = array();
	public $url		 = false;

	function __construct( $subtabData ) {
		$this->init_properties( $subtabData );
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( ! isset( $args[$name] ) )
				continue;
			$this->$name = $args[$name];
		}
	}

	function setup_prop( $propName, $value ) {
		$this->$propName = $value;
	}

	function is_prop( $propName ) {
		return isset( $this->$propName );
	}

	function get_prop( $propName ) {
		return $this->is_prop( $propName ) ? $this->$propName : false;
	}

	function get_permalink( $user_id = false ) {
		return add_query_arg( [ 'tab' => $this->parent_id, 'subtab' => $this->id ], rcl_get_user_url( $user_id ) );
	}

	function get_button( $args = array() ) {

		$tab = RCL()->tabs()->tab( $this->parent_id );

		$ajaxLoad = false;
		if ( isset( $tab->supports ) ) {
			if ( in_array( 'ajax', $tab->supports ) ) {
				$ajaxLoad = true;
			}
		}

		$args = wp_parse_args( $args, array(
			'label'		 => $this->name,
			'icon'		 => $this->icon,
			'counter'	 => $this->counter,
			'href'		 => $this->get_permalink(),
			'onclick'	 => $ajaxLoad ? 'rcl_load_tab("' . $tab->id . '", "' . $this->id . '", this);return false;' : null
			) );

		return rcl_get_button( $args );
	}

	function get_content() {
		global $user_LK;

		$title = $this->title ? $this->title : $this->name;

		$content = '<div id="rcl-subtab-' . $this->id . '">';

		$content .= '<div class="tab-title">';
		if ( $this->icon )
			$content .= '<i class="fa ' . $this->icon . '" aria-hidden="true"></i> ';
		$content .= $title;
		$content .= '</div>';

		if ( $this->callback ) {

			if ( isset( $this->callback['args'] ) ) {
				$args = $this->callback['args'];
			} else {
				$args = array( $user_LK );
			}

			if ( function_exists( $this->callback['name'] ) ) {
				$content .= apply_filters( 'rcl_tab_content', call_user_func_array( $this->callback['name'], $args ), $this->parent_id, $this->id );
			} else {
				$content .= rcl_get_notice( ['text' => __( 'При загрузке вкладки произошла ошибка. Функция не найдена.', 'wp-recall' ) ] );
			}
		}

		$content .= '</div>';

		return $content;
	}

}
