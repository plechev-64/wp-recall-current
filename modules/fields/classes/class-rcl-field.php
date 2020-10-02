<?php

class Rcl_Field {
	static function setup( $args ) {

		if ( is_admin() ) {
			rcl_font_awesome_style();
		}

		if ( isset( RCL()->fields[$args['type']] ) ) {

			$className = RCL()->fields[$args['type']]['class'];

			return new $className( $args );
		}
	}

}
