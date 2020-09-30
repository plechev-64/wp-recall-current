<?php

add_filter( 'users_search_form_rcl', 'rcl_default_search_form' );
function rcl_default_search_form( $form ) {
	global $user_LK, $rcl_tab;

	$search_text	 = ((isset( $_GET['search_text'] ))) ? $_GET['search_text'] : '';
	$search_field	 = (isset( $_GET['search_field'] )) ? $_GET['search_field'] : '';

	$form .='<div class="rcl-search-form">
            <form method="get">
                <div class="rcl-search-form-title">' . __( 'Search users', 'wp-recall' ) . '</div>
                <input type="text" name="search_text" value="' . $search_text . '">
                <select name="search_field">
                    <option ' . selected( $search_field, 'display_name', false ) . ' value="display_name">' . __( 'by name', 'wp-recall' ) . '</option>
                    <option ' . selected( $search_field, 'user_login', false ) . ' value="user_login">' . __( 'by login', 'wp-recall' ) . '</option>
                </select>'
		. rcl_get_button( array(
			'label'	 => __( 'Search', 'wp-recall' ),
			'submit' => true
		) )
		. '<input type="hidden" name="default-search" value="1">';

	if ( $user_LK && $rcl_tab ) {

		$get = rcl_get_option( 'link_user_lk_rcl', 'user' );

		$form .='<input type="hidden" name="' . $get . '" value="' . $user_LK . '">';
		$form .='<input type="hidden" name="tab" value="' . $rcl_tab->id . '">';
	}

	$form .='</form>
        </div>';
	return $form;
}

//добавляем стили колорпикера и другие в хеадер
add_action( 'wp_head', 'rcl_inline_styles', 100 );
function rcl_inline_styles() {

	list($r, $g, $b) = ($color = rcl_get_option( 'primary-color' )) ? sscanf( $color, "#%02x%02x%02x" ) : array( 76, 140, 189 );

	$styles = apply_filters( 'rcl_inline_styles', '', array( $r, $g, $b ) );

	if ( ! $styles )
		return false;

	// удаляем пробелы, переносы, табуляцию
	$styles = preg_replace( '/ {2,}/', '', str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $styles ) );

	echo "<style>" . $styles . "</style>\r\n";
}

add_filter( 'rcl_inline_styles', 'rcl_default_inline_styles', 5, 2 );
function rcl_default_inline_styles( $styles, $rgb ) {

	list($r, $g, $b) = $rgb;

	$styles .= 'a.recall-button,
    span.recall-button,
    .recall-button.rcl-upload-button,
    input[type="submit"].recall-button,
    input[type="submit"] .recall-button,
    input[type="button"].recall-button,
    input[type="button"] .recall-button,
    a.recall-button:hover,
    .recall-button.rcl-upload-button:hover,
    input[type="submit"].recall-button:hover,
    input[type="submit"] .recall-button:hover,
    input[type="button"].recall-button:hover,
    input[type="button"] .recall-button:hover{
        background: rgb(' . $r . ', ' . $g . ', ' . $b . ');
    }
    a.recall-button.active,
    a.recall-button.active:hover,
    a.recall-button.filter-active,
    a.recall-button.filter-active:hover,
    a.data-filter.filter-active,
    a.data-filter.filter-active:hover{
        background: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.4);
    }
    .rcl_preloader i{
        color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .rcl-user-getails .status-user-rcl::before{
        border-left-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .rows-list .status-user-rcl::before{
        border-top-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .status-user-rcl{
        border-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .rcl-field-input input[type="checkbox"]:checked + label.block-label::before,
    .rcl-field-input input[type="radio"]:checked + label.block-label::before{
        background:rgb(' . $r . ',' . $g . ',' . $b . ');
        border-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }';

	return $styles;
}

// background color button api
add_filter( 'rcl_inline_styles', 'rcl_api_button_inline_background', 10, 2 );
function rcl_api_button_inline_background( $styles, $rgb ) {
	list($r, $g, $b) = $rgb;
	$background_color = $r . ',' . $g . ',' . $b;

	$styles .= '
		body .rcl-bttn.rcl-bttn__type-primary {
			background-color: rgb(' . $background_color . ');
		}
		.rcl-bttn.rcl-bttn__type-primary.rcl-bttn__active {
			background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.4);
		}
		.rcl-bttn.rcl-bttn__type-simple.rcl-bttn__active {
			box-shadow: 0 -5px 0 -3px rgb(' . $r . ', ' . $g . ', ' . $b . ') inset;
		}
	';

	return $styles;
}

// color button api
add_filter( 'rcl_inline_styles', 'rcl_api_button_inline_color', 10 );
function rcl_api_button_inline_color( $styles ) {
	$color_button = rcl_get_option( 'rcl-button-text-color', '#fff' );

	$styles .= '
		body .rcl-bttn.rcl-bttn__type-primary {
			color: ' . $color_button . ';
		}
	';

	return $styles;
}

// size button api
add_filter( 'rcl_inline_styles', 'rcl_api_button_inline_size', 10 );
function rcl_api_button_inline_size( $styles ) {
	$size = rcl_get_option( 'rcl-button-font-size', '14' );

	$styles .= '
		body .rcl-bttn,
		.rcl-bttn.rcl-bttn__size-small {
			font-size: ' . 0.86 * $size . 'px;
		}
		.rcl-bttn.rcl-bttn__size-standart {
			font-size: ' . $size . 'px;
		}
		.rcl-bttn.rcl-bttn__size-medium {
			font-size: ' . 1.16 * $size . 'px;
		}
		.rcl-bttn__type-clear.rcl-bttn__mod-only-icon.rcl-bttn__size-medium,
		.rcl-bttn.rcl-bttn__size-large {
			font-size: ' . 1.33 * $size . 'px;
		}
		.rcl-bttn.rcl-bttn__size-big {
			font-size: ' . 1.5 * $size . 'px;
		}
		.rcl-bttn__type-clear.rcl-bttn__mod-only-icon.rcl-bttn__size-large {
			font-size: ' . 1.66 * $size . 'px;
		}
		.rcl-bttn__type-clear.rcl-bttn__mod-only-icon.rcl-bttn__size-big {
			font-size: ' . 2 * $size . 'px;
		}
	';

	return $styles;
}

// css variable
// Основные цвета WP-Recall переведем в css переменные
// для удобства: hex и rgb значения - чтобы потом самим css генерировать как прозрачность текста (rgba)
add_filter( 'rcl_inline_styles', 'rcl_css_variable', 10, 2 );
function rcl_css_variable( $styles, $rgb ) {
	$rcl_color = rcl_get_option( 'primary-color', '#4c8cbd' );

	list($r, $g, $b) = $rgb;

	// темнее rgb
	$rd	 = round( $r * 0.45 );
	$gd	 = round( $g * 0.45 );
	$bd	 = round( $b * 0.45 );

	// ярче rgb
	$rl	 = round( $r * 1.4 );
	$gl	 = round( $g * 1.4 );
	$bl	 = round( $b * 1.4 );

	// инверт rgb
	$rf	 = round( 0.75 * (255 - $r) );
	$gf	 = round( 0.75 * (255 - $g) );
	$bf	 = round( 0.75 * (255 - $b) );

	// https://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color
	$text_color	 = '#fff';
	$threshold	 = apply_filters( 'rcl_text_color_threshold', 150 );
	if ( ($r * 0.299 + $g * 0.587 + $b * 0.114) > $threshold ) {
		$text_color = '#000';
	}

	$styles .= '
:root{
--rclText: ' . $text_color . ';
--rclHex:' . $rcl_color . ';
--rclRgb:' . $r . ',' . $g . ',' . $b . ';
--rclRgbDark:' . $rd . ',' . $gd . ',' . $bd . ';
--rclRgbLight:' . $rl . ',' . $gl . ',' . $bl . ';
--rclRgbFlip:' . $rf . ',' . $gf . ',' . $bf . ';
}
';

	return $styles;
}

add_action( 'wp_footer', 'rcl_init_footer_action', 100 );
function rcl_init_footer_action() {
	echo '<script>rcl_do_action("rcl_footer")</script>';
}

add_action( 'wp_footer', 'rcl_popup_contayner', 4 );
function rcl_popup_contayner() {
	echo '<div id="rcl-overlay"></div>
        <div id="rcl-popup"></div>';
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

add_filter( 'the_content', 'rcl_message_post_moderation' );
function rcl_message_post_moderation( $content ) {
	global $post;

	if ( ! isset( $post ) || ! $post )
		return $content;

	if ( $post->post_status == 'pending' ) {
		$content = rcl_get_notice( ['text' => __( 'Publication pending approval!', 'wp-recall' ), 'type' => 'error' ] ) . $content;
	}

	if ( $post->post_status == 'draft' ) {
		$content = rcl_get_notice( ['text' => __( 'Draft of a post!', 'wp-recall' ), 'type' => 'error' ] ) . $content;
	}

	return $content;
}

function rcl_bar_add_icon( $id_icon, $args ) {
	global $rcl_bar;
	if ( ! rcl_get_option( 'view_recallbar' ) )
		return false;
	$rcl_bar['icons'][$id_icon] = $args;
	return true;
}

function rcl_bar_add_menu_item( $id_item, $args ) {
	global $rcl_bar;
	if ( ! rcl_get_option( 'view_recallbar' ) )
		return false;
	$rcl_bar['menu'][$id_item] = $args;
	return true;
}

add_action( 'wp', 'rcl_post_bar_setup', 10 );
function rcl_post_bar_setup() {
	do_action( 'rcl_post_bar_setup' );
}

function rcl_post_bar_add_item( $id_item, $args ) {
	global $rcl_post_bar;

	if ( isset( $args['url'] ) )
		$args['href'] = $args['url'];

	$rcl_post_bar['items'][$id_item] = $args;

	return true;
}

add_filter( 'the_content', 'rcl_post_bar', 999 );
function rcl_post_bar( $content ) {
	global $rcl_post_bar;

	if ( doing_filter( 'get_the_excerpt' ) || ! is_single() || is_front_page() )
		return $content;

	$rcl_bar_items = apply_filters( 'rcl_post_bar_items', $rcl_post_bar['items'] );

	if ( ! isset( $rcl_bar_items ) || ! $rcl_bar_items )
		return $content;


	$bar = '<div id="rcl-post-bar">';

	foreach ( $rcl_bar_items as $id_item => $item ) {

		$bar .= '<div id="bar-item-' . $id_item . '" class="post-bar-item">';

		$bar .= rcl_get_button( $item );

		$bar .= '</div>';
	}

	$bar .= '</div>';

	$content = $bar . $content;


	return $content;
}
