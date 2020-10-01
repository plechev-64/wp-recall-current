<?php

class Rcl_Module {

	public $path	 = '';
	public $parents	 = [ ];

	function __construct( $path, $parents = [ ] ) {
		$this->path		 = $path;
		$this->parents	 = $parents;
	}

	function inc() {
		require_once $this->path;
	}

}
