<?php

function rcl_init_beat($beatName){
	global $rcl_beats;
	$rcl_beats[$beatName] = [];
}

if ( ! function_exists( 'get_called_class' ) ) :
	function get_called_class() {
		$arr		 = array();
		$arrTraces	 = debug_backtrace();
		foreach ( $arrTraces as $arrTrace ) {
			if ( ! array_key_exists( "class", $arrTrace ) )
				continue;
			if ( count( $arr ) == 0 )
				$arr[]	 = $arrTrace['class'];
			else if ( get_parent_class( $arrTrace['class'] ) == end( $arr ) )
				$arr[]	 = $arrTrace['class'];
		}
		return end( $arr );
	}

endif;
//getting array of pages IDs and titles
//for using in settings: ID => post_title
function rcl_get_pages_ids() {

	$pages = RQ::tbl( new Rcl_Posts_Query() )->select( ['ID', 'post_title' ] )
			->where( ['post_type' => 'page', 'post_status' => 'publish' ] )
			->limit( -1 )
			->orderby( 'post_title', 'ASC' )
			->get_walker()->get_index_values( 'ID', 'post_title' );

	$pages = array( __( 'Not selected', 'wp-recall' ) ) + $pages;

	return $pages;
}

function rcl_get_roles_ids() {
	$editable_roles	 = array_reverse( get_editable_roles() );
	$roles			 = [ ];
	foreach ( $editable_roles as $role => $details ) {
		if ( $role == 'administrator' )
			continue;
		$roles[$role] = translate_user_role( $details['name'] );
	}
	return $roles;
}

function rcl_sanitize_string( $title, $sanitize = true ) {

	$title = mb_strtolower( $title );

	switch ( get_site_option( 'rtl_standard' ) ) {
		case 'off':
			return $title;
		case 'gost':
			$title	 = strtr( $title, apply_filters( 'rcl_sanitize_gost', array(
				"Є"	 => "EH", "І"	 => "I", "і"	 => "i", "№"	 => "#", "є"	 => "eh",
				"А"	 => "A", "Б"	 => "B", "В"	 => "V", "Г"	 => "G", "Д"	 => "D",
				"Е"	 => "E", "Ё"	 => "JO", "Ж"	 => "ZH",
				"З"	 => "Z", "И"	 => "I", "Й"	 => "JJ", "К"	 => "K", "Л"	 => "L",
				"М"	 => "M", "Н"	 => "N", "О"	 => "O", "П"	 => "P", "Р"	 => "R",
				"С"	 => "S", "Т"	 => "T", "У"	 => "U", "Ф"	 => "F", "Х"	 => "KH",
				"Ц"	 => "C", "Ч"	 => "CH", "Ш"	 => "SH", "Щ"	 => "SHH", "Ъ"	 => "'",
				"Ы"	 => "Y", "Ь"	 => "", "Э"	 => "EH", "Ю"	 => "YU", "Я"	 => "YA",
				"а"	 => "a", "б"	 => "b", "в"	 => "v", "г"	 => "g", "д"	 => "d",
				"е"	 => "e", "ё"	 => "jo", "ж"	 => "zh",
				"з"	 => "z", "и"	 => "i", "й"	 => "jj", "к"	 => "k", "л"	 => "l",
				"м"	 => "m", "н"	 => "n", "о"	 => "o", "п"	 => "p", "р"	 => "r",
				"с"	 => "s", "т"	 => "t", "у"	 => "u", "ф"	 => "f", "х"	 => "kh",
				"ц"	 => "c", "ч"	 => "ch", "ш"	 => "sh", "щ"	 => "shh", "ъ"	 => "",
				"ы"	 => "y", "ь"	 => "", "э"	 => "eh", "ю"	 => "yu", "я"	 => "ya",
				"—"	 => "-", "«"	 => "", "»"	 => "", "…"	 => ""
				) ) );
			break;
		default:
			$title	 = strtr( $title, apply_filters( 'rcl_sanitize_iso', array(
				"Є"	 => "YE", "І"	 => "I", "Ѓ"	 => "G", "і"	 => "i", "№"	 => "#", "є"	 => "ye", "ѓ"	 => "g",
				"А"	 => "A", "Б"	 => "B", "В"	 => "V", "Г"	 => "G", "Д"	 => "D",
				"Е"	 => "E", "Ё"	 => "YO", "Ж"	 => "ZH",
				"З"	 => "Z", "И"	 => "I", "Й"	 => "J", "К"	 => "K", "Л"	 => "L",
				"М"	 => "M", "Н"	 => "N", "О"	 => "O", "П"	 => "P", "Р"	 => "R",
				"С"	 => "S", "Т"	 => "T", "У"	 => "U", "Ф"	 => "F", "Х"	 => "X",
				"Ц"	 => "C", "Ч"	 => "CH", "Ш"	 => "SH", "Щ"	 => "SHH", "Ъ"	 => "'",
				"Ы"	 => "Y", "Ь"	 => "", "Э"	 => "E", "Ю"	 => "YU", "Я"	 => "YA",
				"а"	 => "a", "б"	 => "b", "в"	 => "v", "г"	 => "g", "д"	 => "d",
				"е"	 => "e", "ё"	 => "yo", "ж"	 => "zh",
				"з"	 => "z", "и"	 => "i", "й"	 => "j", "к"	 => "k", "л"	 => "l",
				"м"	 => "m", "н"	 => "n", "о"	 => "o", "п"	 => "p", "р"	 => "r",
				"с"	 => "s", "т"	 => "t", "у"	 => "u", "ф"	 => "f", "х"	 => "x",
				"ц"	 => "c", "ч"	 => "ch", "ш"	 => "sh", "щ"	 => "shh", "ъ"	 => "",
				"ы"	 => "y", "ь"	 => "", "э"	 => "e", "ю"	 => "yu", "я"	 => "ya",
				"—"	 => "-", "«"	 => "", "»"	 => "", "…"	 => ""
				) ) );
	}

	return $sanitize ? sanitize_title_with_dashes( $title, '', 'save' ) : $title;
}

function rcl_get_smiles( $id_area ) {

	$smiles = '<div class="rcl-smiles" data-area="' . $id_area . '">';
	$smiles .= '<i class="rcli fa-smile-o" aria-hidden="true"></i>';
	$smiles .= '<div class="rcl-smiles-list">
						<div class="smiles"></div>
					</div>';
	$smiles .= '</div>';

	return $smiles;
}

function rcl_mail( $email, $title, $text, $from = false, $attach = false ) {

	$from_name	 = (isset( $from['name'] )) ? $from['name'] : get_bloginfo( 'name' );
	$from_mail	 = (isset( $from['email'] )) ? $from['email'] : 'noreply@' . $_SERVER['HTTP_HOST'];

	add_filter( 'wp_mail_content_type', function() {
		return "text/html";
	} );

	$headers = 'From: ' . $from_name . ' <' . $from_mail . '>' . "\r\n";

	$content = rcl_get_include_template( 'mail.php', false, [
		'mail_title'	 => $title,
		'mail_content'	 => $text
		] );

	$content .= '<p><small>-----------------------------------------------------<br/>
	' . __( 'This letter was created automatically, no need to answer it.', 'wp-recall' ) . '<br/>
	"' . get_bloginfo( 'name' ) . '"</small></p>';

	return wp_mail( $email, $title, $content, $headers, $attach );
}

function rcl_get_form( $args ) {
	RCL()->use_module( 'forms' );
	$Form = new Rcl_Form( $args );
	return $Form->get_form();
}

function rcl_get_notice( $args ) {
	require_once RCL_PATH . '/classes/class-rcl-notice.php';
	$Notice = new Rcl_Notice( $args );
	return $Notice->get_notice();
}

function rcl_get_button( $args, $depr_url = false, $depr_args = false ) {

	if ( is_array( $args ) ) {
		$bttn = new Rcl_Button( $args );
		return $bttn->get_button();
	}

	_deprecated_argument( __FUNCTION__, '16.21.0' );

	$button = '<a href="' . $depr_url . '" ';
	if ( isset( $depr_args['attr'] ) && $depr_args['attr'] )
		$button .= $depr_args['attr'] . ' ';
	if ( isset( $depr_args['id'] ) && $depr_args['id'] )
		$button .= 'id="' . $depr_args['id'] . '" ';
	$button .= 'class="recall-button ';
	if ( isset( $depr_args['class'] ) && $depr_args['class'] )
		$button .= $depr_args['class'];
	$button .= '">';
	if ( isset( $depr_args['icon'] ) && $depr_args['icon'] )
		$button .= '<i class="rcli ' . $depr_args['icon'] . '"></i>';
	$button .= '<span>' . $args . '</span>';
	$button .= '</a>';
	return $button;
}

function rcl_get_balloon( $args ) {

	$content = '<span class="rcl-balloon-hover ' . (isset( $args['class'] ) ? $args['class'] : '') . '">';
	$content .= '<i class="rcli ' . $args['icon'] . '" aria-hidden="true"></i>';
	if ( isset( $args['label'] ) ) {
		$content .= ' ' . $args['label'];
	}
	$content .= '<span class="rcl-balloon ' . (isset( $args['position'] ) ? 'position-' . $args['position'] : 'position-bottom') . '">';
	$content .= $args['content'];
	$content .= '</span>';
	$content .= '</span>';

	return $content;
}

function rcl_get_area_options() {

	$areas = array(
		'menu'		 => get_site_option( 'rcl_fields_area-menu' ),
		'counters'	 => get_site_option( 'rcl_fields_area-counters' ),
		'actions'	 => get_site_option( 'rcl_fields_area-actions' ),
	);

	return $areas;
}

function rcl_add_log( $title, $data = false, $force = false ) {

	if ( ! $force && ! rcl_get_option( 'rcl-log' ) )
		return false;

	$RclLog = new Rcl_Log();

	$RclLog->insert_title( $title );

	if ( $data )
		$RclLog->insert_log( $data );
}

function rcl_is_gutenberg() {
	global $post;

	if ( ! is_admin() ) {
		return false;
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	if ( get_current_screen()->base !== 'post' ) {
		return false;
	}

	if ( isset( $_GET['classic-editor'] ) ) {
		return false;
	}

	// Gutenberg plugin is installed and activated.
	$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

	// Block editor since 5.0.
	$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

	if ( ! $gutenberg && ! $block_editor ) {
		return false;
	}

	return true;
}

function rcl_get_author_block() {
	global $post;

	$content = "<div id=block_author-rcl>";
	$content .= "<h3>" . __( 'Publication author', 'wp-recall' ) . "</h3>";

	if ( function_exists( 'rcl_add_userlist_follow_button' ) )
		add_filter( 'rcl_user_description', 'rcl_add_userlist_follow_button', 90 );

	$content .= rcl_get_userlist( array(
		'template'	 => 'rows',
		'orderby'	 => 'display_name',
		'include'	 => $post->post_author,
		'filter'	 => 0,
		'data'		 => 'rating_total,description,posts_count,user_registered,comments_count'
		) );

	if ( function_exists( 'rcl_add_userlist_follow_button' ) )
		remove_filter( 'rcl_user_description', 'rcl_add_userlist_follow_button', 90 );

	$content .= "</div>";

	return $content;
}
