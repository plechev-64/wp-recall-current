<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-usp-tabs
 *
 * @author Андрей
 */
class Rcl_Tabs {

	public $current_id;
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function isset_tab( $tab_id ) {
		return isset( RCL()->tabs[$tab_id] );
	}

	function add( $tabData ) {

		RCL()->tabs[$tabData['id']] = new Rcl_Tab( $tabData );

		return RCL()->tabs[$tabData['id']];
	}

	function tab( $tab_id ) {

		if ( ! $this->isset_tab( $tab_id ) )
			return false;

		return RCL()->tabs[$tab_id];
	}

	function remove_tab( $tab_id ) {

		if ( ! $this->isset_tab( $tab_id ) )
			return false;

		unset( RCL()->tabs[$tab_id] );
	}

	function current() {
		return ($this->current_id = $this->get_current_id()) ? $this->tab( $this->current_id ) : false;
	}

	function get_current_id() {

		if ( isset( $_GET['tab'] ) ) {
			return $_GET['tab'];
		} else if ( RCL()->tabs ) {

			if ( $tabs = $this->get_access_menu_items( 'menu' ) ) {
				foreach ( $tabs as $tab_id ) {
					return $tab_id;
				}
			} else {
				foreach ( RCL()->tabs as $tab_id => $tab ) {

					if ( $tab->output == 'menu' )
						continue;

					if ( ! $tab->content )
						continue;

					if ( ! $tab->is_access() ) {
						continue;
					}

					return $tab_id;
				}
			}
		}

		return false;
	}

	function get_menu_items( $menu_id ) {

		if ( ! RCL()->tabs )
			return false;

		$tab_ids = array();
		foreach ( RCL()->tabs as $tab_id => $tab ) {

			if ( $tab->output != $menu_id )
				continue;

			$tab_ids[] = $tab_id;
		}

		return $tab_ids;
	}

	function get_access_menu_items( $menu_id ) {

		if ( ! $tab_ids = $this->get_menu_items( $menu_id ) )
			return false;

		$ids = array();
		foreach ( $tab_ids as $tab_id ) {

			if ( ! $tab = $this->tab( $tab_id ) ) {
				continue;
			}

			if ( ! $tab->is_access() ) {
				continue;
			}

			$ids[] = $tab_id;
		}

		return $ids;
	}

	function get_menu( $menu_id, $args = array() ) {

		$tab_ids = isset( $args['tab_ids'] ) && $args['tab_ids'] ? $args['tab_ids'] : $this->get_access_menu_items( $menu_id );

		if ( ! $tab_ids )
			return false;

		if ( ! $this->current_id )
			$this->current_id = $this->get_current_id();

		$classes = ['rcl-tabs-menu', 'rcl-wrap' ];

		if ( isset( $args['class'] ) && $args['class'] ) {
			$classes[] = $args['class'];
		}

		$content = '<div id="rcl-tabs-menu-' . $menu_id . '" class="' . implode( ' ', $classes ) . '">';

		foreach ( $tab_ids as $tab_id ) {

			if ( ! $tab = $this->tab( $tab_id ) ) {
				continue;
			}

			$content .= $tab->get_button( $this->current_id == $tab_id ? ['status' => 'active' ] : [ ]  );
		}

		$content .= '</div>';

		return $content;
	}

	function init_custom_tabs() {

		$areas = rcl_get_area_options();

		foreach ( $areas as $area_id => $tabs ) {

			if ( ! $tabs )
				continue;

			foreach ( $tabs as $tab ) {

				if ( isset( $tab['default-tab'] ) )
					continue;

				$tab_data = array(
					'id'		 => $tab['slug'],
					'name'		 => $tab['title'],
					'public'	 => isset( $tab['public-tab'] ) && $tab['public-tab'] ? 1 : 0,
					'icon'		 => $tab['icon'] ? $tab['icon'] : 'fa-cog',
					'output'	 => $area_id,
					'supports'	 => isset( $tab['supports-tab'] ) ? $tab['supports-tab'] : array(),
					'custom-tab' => true,
					'content'	 => array(
						array(
							'id'		 => 'subtab-1',
							'name'		 => $tab['title'],
							'icon'		 => ($tab['icon']) ? $tab['icon'] : 'fa-cog',
							'callback'	 => array(
								'name'	 => 'rcl_custom_tab_content',
								'args'	 => array( $tab['content'] )
							)
						)
					)
				);

				$this->add( $tab_data );
			}
		}
	}

	function order_tabs() {

		$areas = rcl_get_area_options();

		if ( $areas ) {

			$initTabs = RCL()->tabs;

			$newArray = [ ];

			foreach ( $areas as $area_id => $tabs ) {

				if ( ! $tabs )
					continue;

				foreach ( $tabs as $tabData ) {

					if ( ! $tab = RCL()->tabs()->tab( $tabData['slug'] ) )
						continue;

					$tab->setup_prop( 'name', $tabData['title'] );
					$tab->setup_prop( 'hidden', $tabData['hidden'] );
					$tab->setup_prop( 'icon', $tabData['icon'] ? $tabData['icon'] : 'fa-cog'  );

					if ( isset( $tabData['custom-tab'] ) && $tabData['custom-tab'] ) {
						$tab->setup_prop( 'custom_tab', 1 );
						$tab->setup_prop( 'supports', isset( $tabData['supports-tab'] ) ? $tabData['supports-tab'] : [ ]  );
						$tab->setup_prop( 'public', isset( $tabData['public-tab'] ) && $tabData['public-tab'] ? 1 : 0  );

						$tab->content[0] = new Rcl_Sub_Tab( [
							'id'		 => $tab->id,
							'name'		 => $tab->name,
							'icon'		 => $tab->icon,
							'parent_id'	 => $tab->id,
							'callback'	 => [
								'name'	 => $tab->content[0]->callback['name'],
								'args'	 => [$tabData['content'] ]
							]
							] );
					}

					$newArray[$tab->id] = $tab;
				}

				foreach ( RCL()->tabs as $tab_id => $tab ) {
					if ( ! isset( $newArray[$tab_id] ) )
						$newArray[$tab_id] = $tab;
				}
			}

			RCL()->tabs = $newArray;
		}
	}

}
