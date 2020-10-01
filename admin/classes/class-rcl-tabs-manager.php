<?php

RCL()->use_module( 'fields-manager' );

class Rcl_Tabs_Manager extends Rcl_Fields_Manager {
	function __construct( $areaType ) {

		parent::__construct( $areaType, array(
			'switch_type'	 => false,
			'switch_id'		 => false,
			'types'			 => array(
				'custom'
			),
			'field_options'	 => array(
				array(
					'slug'			 => 'icon',
					'default'		 => 'fa-check-square',
					'placeholder'	 => 'fa-check-square',
					'class'			 => 'rcl-iconpicker',
					'type'			 => 'text',
					'title'			 => __( 'Icon class of  font-awesome', 'wp-recall' )
				),
				array(
					'type'	 => 'radio',
					'slug'	 => 'hidden',
					'title'	 => __( 'Hidden tab', 'wp-recall' ),
					'notice' => __( 'The tab will be available only by link', 'wp-recall' ),
					'values' => array(
						__( 'No', 'wp-recall' ),
						__( 'Yes', 'wp-recall' )
					)
				),
				array(
					'type'			 => 'text',
					'slug'			 => 'icon',
					'class'			 => 'rcl-iconpicker',
					'title'			 => __( 'Icon class of  font-awesome', 'wp-recall' ),
					'placeholder'	 => __( 'Example, fa-user', 'wp-recall' ),
					'notice'		 => __( 'Source', 'wp-recall' ) . ' <a href="https://fontawesome.com/v4.7.0/icons/" target="_blank">http://fontawesome.com/</a>'
				),
				array(
					'type'	 => 'select',
					'slug'	 => 'public-tab',
					'title'	 => __( 'Tab privacy', 'wp-recall' ),
					'values' => array(
						__( 'Private', 'wp-recall' ),
						__( 'Public', 'wp-recall' )
					)
				),
				array(
					'type'	 => 'checkbox',
					'slug'	 => 'supports-tab',
					'title'	 => __( 'Support of the functions', 'wp-recall' ),
					'values' => array(
						'ajax'	 => __( 'ajax-loading', 'wp-recall' ),
						'cache'	 => __( 'caching', 'wp-recall' ),
						'dialog' => __( 'dialog box', 'wp-recall' )
					)
				),
				array(
					'type'		 => 'editor',
					'tinymce'	 => true,
					'slug'		 => 'content',
					'title'		 => __( 'Content tab', 'wp-recall' ),
					'notice'	 => __( 'supported shortcodes and HTML-code', 'wp-recall' )
				)
			)
		) );

		$this->setup_tabs();

		add_filter( 'rcl_field_options', array( $this, 'edit_tab_options' ), 10, 3 );
	}

	function form_navi() {

		$areas = array(
			'area-menu'		 => __( '"Menu" area', 'wp-recall' ),
			'area-actions'	 => __( '"Actions" area', 'wp-recall' ),
			'area-counters'	 => __( '"Counters" area', 'wp-recall' )
		);

		$content = '<div class="rcl-custom-fields-navi">';

		$content .= '<ul class="rcl-types-list">';

		foreach ( $areas as $type => $name ) {

			$class = ($this->manager_id == $type) ? 'class="current-item"' : '';

			$content .= '<li ' . $class . '><a href="' . admin_url( 'admin.php?page=rcl-tabs-manager&area-type=' . $type ) . '">' . $name . '</a></li>';
		}

		$content .= '</ul>';

		$content .= '</div>';

		return $content;
	}

	function is_default_tab( $slug ) {

		if ( ! $tab = RCL()->tabs()->tab( $slug ) )
			return false;

		return $tab->custom_tab ? false : true;
	}

	function setup_tabs() {

		$defaultTabs = $this->get_default_tabs();

		if ( $this->fields ) {

			foreach ( $this->fields as $k => $tab ) {

				if ( $this->is_default_tab( $tab->id ) ) {
					$tab->set_prop( 'must_delete', false );
				} else {
					if ( isset( $tab->{'default-tab'} ) ) {
						unset( $this->fields[$k] );
					}
				}
			}

			if ( $defaultTabs ) {
				foreach ( $defaultTabs as $tab ) {
					if ( $this->is_active_field( $tab['slug'] ) )
						continue;
					$this->add_field( $tab );
				}
			}
		}else if ( $defaultTabs ) {

			foreach ( $defaultTabs as $tab ) {
				$this->add_field( $tab );
			}
		}
	}

	function get_default_tabs() {

		if ( ! RCL()->tabs )
			return false;

		$fields = array();

		foreach ( RCL()->tabs as $tab_id => $tab ) {

			if ( $tab->custom_tab )
				continue;

			if ( 'area-' . $tab->output != $this->manager_id )
				continue;

			$fields[] = array(
				'type-edit'		 => false,
				'slug'			 => $tab_id,
				'delete'		 => false,
				'default-tab'	 => true,
				'type'			 => 'custom',
				'must_delete'	 => false,
				'title'			 => $tab->name,
				'icon'			 => $tab->icon
			);
		}

		return $fields;
	}

	function edit_tab_options( $options, $field, $type ) {

		if ( ! $field->slug )
			return $options;

		if ( $this->is_default_tab( $field->slug ) ) {

			unset( $options['public-tab'] );
			unset( $options['supports-tab'] );
			unset( $options['content'] );
			unset( $options['slug'] );

			$options['icon']['placeholder'] = RCL()->tabs()->tab( $field->slug )->icon;

			$options['default-tab'] = array(
				'type'	 => 'hidden',
				'slug'	 => 'default-tab',
				'value'	 => 1
			);
		} else {
			$options['custom-tab'] = array(
				'type'	 => 'hidden',
				'slug'	 => 'custom-tab',
				'value'	 => 1
			);
		}

		return $options;
	}

}
