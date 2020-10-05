<?php

final class Rcl_Ajax {

	public $name;
	public $nopriv			 = false;
	public $rest			 = false;
	public $rest_space		 = 'rcl';
	public $rest_route		 = '';
	public $rest_callback	 = '';
	public $ajax_callbacks	 = array();

	public static function getInstance() {
		static $instance;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	private function __construct() {
		static $hasInstance = false;

		if ( $hasInstance ) {
			return;
		}

		$hasInstance = true;
	}

	/* function __construct( $args = false ) {
	  $this->init_properties( $args );
	  } */

	/* function init_properties( $args ) {

	  $properties = get_class_vars( get_class( $this ) );

	  foreach ( $properties as $name => $val ) {
	  if ( isset( $args[$name] ) )
	  $this->$name = $args[$name];
	  }
	  } */
	public function is_rest_request() {
		return isset( $_REQUEST['rest_route'] ) && $_REQUEST['rest_route'] == '/' . $this->rest_space . '/' . $this->rest_route . '/' ? true : false;
	}

	public function init_ajax_callback( $callback, $guest_access = false, $modules = false ) {

		if ( ! $this->is_rest_request() )
			return false;

		$this->ajax_callbacks[$callback] = ['guest' => $guest_access, 'modules' => $modules ];
	}

	public function get_ajax_callback( $callback ) {
		return isset( $this->ajax_callbacks[$callback] ) ? $this->ajax_callbacks[$callback] : false;
	}

	function init_rest( $rest_callback ) {

		$this->rest_callback = $rest_callback;
		$this->rest_route	 = $rest_callback;

		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	/* function init() {

	  if ( $this->rest ) {
	  add_action( 'rest_api_init', array( $this, 'register_route' ) );
	  } else {

	  if ( is_array( $this->name ) ) {
	  $name = $this->name[1];
	  } else {
	  $name = $this->name;
	  }

	  add_action( 'wp_ajax_' . $name, $this->name );

	  if ( $this->nopriv )
	  add_action( 'wp_ajax_nopriv_' . $name, $this->name );
	  }
	  } */
	function register_route() {

		register_rest_route( $this->rest_space, '/' . $this->rest_route . '/', array(
			'methods'	 => 'POST',
			'callback'	 => $this->rest_callback
		) );
	}

	function verify() {

		if ( isset( $_POST['ajax_nonce'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
				return false;
			if ( ! wp_verify_nonce( $_POST['ajax_nonce'], 'wp_rest' ) ) {
				wp_send_json( array( 'error' => __( 'Signature verification failed', 'wp-recall' ) . '!' ) );
			}
		} else {
			check_ajax_referer( 'wp_rest' );
		}
	}

}
