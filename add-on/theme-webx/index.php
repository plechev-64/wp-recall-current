<?php
//Подключение стилей
if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'webx_theme_style', 10 );
endif;
function webx_theme_style() {
	rcl_enqueue_style( 'webx_theme_style', rcl_addon_url( 'assets/css/style.css', __FILE__ ) );
}

// инициализируем наши скрипты
add_action( 'rcl_enqueue_scripts', 'cab_15_script_load' );
function cab_15_script_load() {
	global $user_LK;
	if ( $user_LK ) {
		rcl_enqueue_script( 'theme-scripts', rcl_addon_url( 'assets/js/main.js', __FILE__ ), false, true );
	}
}


// выводим обложку
add_filter( 'rcl_inline_styles', 'webx_add_cover_inline_styles', 10 );
function webx_add_cover_inline_styles( $styles ) {
	if ( ! rcl_is_office() ) {
		return $styles;
	}
	global $user_LK;
	$cover = get_user_meta( $user_LK, 'rcl_cover', 1 );
	if ( ! $cover ) {
		$cover = rcl_get_option( 'default_cover', 0 );
	}
	$cover_url = $cover && is_numeric( $cover ) ? wp_get_attachment_image_url( $cover, 'large' ) : $cover;
	if ( ! $cover_url ) {
		$cover_url = rcl_addon_url( 'assets/image/default-cover.jpg', __FILE__ );
	}
	$dataUrl    = wp_parse_url( $cover_url );
	$cover_path = untrailingslashit( ABSPATH ) . $dataUrl['path'];
	$styles     .= '#webx-cover{background-image: url(' . $cover_url . '?vers=' . @filemtime( $cover_path ) . ');}';

	return $styles;
}

// объявляем поддержку загрузки аватарки, загрузку обложки, модальное окно "Подробная информация"
add_action( 'rcl_addons_included', 'lt_setup_template_options', 10 );
function lt_setup_template_options() {
	rcl_template_support( 'avatar-uploader' );
	rcl_template_support( 'cover-uploader' );
	rcl_template_support( 'modal-user-details' );
}

add_filter( 'rcl_options', 'webx_construct_theme' );
function webx_construct_theme( $options ) {
	//Настройки цвета
	$options->box( 'primary' )->add_group( 'design', [
		'title' => __( 'Оформление' )
	] )->add_options( [
		[
			'type'    => 'color',
			'slug'    => 'webx-color',
			'title'   => __( 'Основной цвет' ),
			'default' => '#000000'
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-color',
			'title'   => __( 'Основной цвет кнопок' ),
			'default' => '#000000'
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-color',
			'title'   => __( 'Основной цвет кнопок в меню' ),
			'default' => '#ffffff'
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-background',
			'title'   => __( 'Основной цвет фона кнопок в меню' ),
			'default' => '#000000'
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-color-hover',
			'title'   => __( 'Основной цвет кнопок в меню при hover' ),
			'default' => '#000000'
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-background-hover',
			'title'   => __( 'Основной цвет фона кнопок в меню при hover' ),
			'default' => '#ffffff'
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-avatar',
			'title'      => __( 'Округление автара' ),
			'value_step' => '1',
			'default'    => '0'
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-cover',
			'title'      => __( 'Округление Cover' ),
			'value_step' => '1',
			'default'    => '0'
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-userinfo',
			'title'      => __( 'Округление под аватаром линии' ),
			'value_step' => '1',
			'default'    => '0'
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-boxcontent',
			'title'      => __( 'Округление основного блока' ),
			'value_step' => '1',
			'default'    => '0'
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-href',
			'title'      => __( 'Округление кнопок' ),
			'value_step' => '1',
			'default'    => '0'
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-chat',
			'title'      => __( 'Округление стиля чата' ),
			'value_step' => '1',
			'default'    => '0'
		],
	] );


	return $options;

}

add_filter( 'rcl_inline_styles', 'webx_add_colors_inline_styles', 10 );
function webx_add_colors_inline_styles( $styles ) {
	global $rcl_options;
	if ( ! rcl_is_office() ) {
		return $styles;
	}

	$lca_hex = rcl_get_option( 'webx-color' ); // достаем оттуда наш цвет
	list( $r, $g, $b ) = sscanf( $lca_hex, "#%02x%02x%02x" );

	$rp = round( $r * 0.90 );
	$gp = round( $g * 0.90 );
	$bp = round( $b * 0.90 );

	$webx_theme_color                 = $rcl_options['webx-theme-color'];
	$webx_theme_href_background       = $rcl_options['webx-theme-href-background'];
	$webx_theme_href_color            = $rcl_options['webx-theme-href-color'];
	$webx_theme_href_background_hover = $rcl_options['webx-theme-href-background-hover'];
	$webx_theme_href_color_hover      = $rcl_options['webx-theme-href-color-hover'];

	$styles .= '
	.rcl-noread-users, .rcl-chat-panel{
		background: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 0.85);
	}
	body .rcl_preloader i {
		color: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 1);
	}
	.rcl-noread-users a.active-chat::before {
	    border-right-color: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 0.85);
	}
	.rcl-chat .nth .message-box{
		background: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 0.35);
	}
	.rcl-chat .nth .message-box::before{
		border-right-color: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 0.35);
	}
	';

	$styles .= '
	#rcl-office #lk-menu a.recall-button.active,
	#rcl-office .rcl-subtab-menu .rcl-bttn.rcl-bttn__type-primary.rcl-bttn__active,
	body #webx-content .rcl-bttn.rcl-bttn__type-primary, 
	body .rcl-bttn.rcl-bttn__type-primary,
	body #rcl-office .webx_phone_menu
	{
		background: ' . $webx_theme_href_background . ' !important;
		border-color: ' . $webx_theme_href_background . ' !important;
		color: ' . $webx_theme_href_color . ' !important;
	}
	#webx-content .webx-area-menu a,
	#webx-main .webx-userinfo .webx-area-counters a
	{
		color: ' . $webx_theme_color . ';
	}
	#rcl-office #lk-menu a.recall-button:hover,
	body #webx-content .rcl-bttn.rcl-bttn__type-primary:hover,
	body #rcl-office .webx_phone_menu:hover
	{

		background: ' . $webx_theme_href_background_hover . ' !important;
		border-color: ' . $webx_theme_href_background_hover . ' !important;
		color: ' . $webx_theme_href_color_hover . ' !important;
	}
	';


	/*Блок округления блоков*/
	$webx_theme_radius_avatar     = $rcl_options['webx-theme-radius-avatar'];
	$webx_theme_radius_cover      = $rcl_options['webx-theme-radius-cover'];
	$webx_theme_radius_userinfo   = $rcl_options['webx-theme-radius-userinfo'];
	$webx_theme_radius_boxcontent = $rcl_options['webx-theme-radius-boxcontent'];
	$webx_theme_radius_href       = $rcl_options['webx-theme-radius-href'];
	$webx_theme_radius_chat       = $rcl_options['webx-theme-radius-chat'];
	$styles                       .= '
	#webx-main #rcl-avatar img{
		border-radius: ' . $webx_theme_radius_avatar . 'px;
	}
	#webx-cover{
		border-radius: ' . $webx_theme_radius_cover . 'px;
	}
	.webx-userinfo{
		border-radius: ' . $webx_theme_radius_userinfo . 'px;
	}
	#webx-content .webx-area-tabs,
	#webx-content .rcl-notice{
		border-radius: ' . $webx_theme_radius_boxcontent . 'px;
	}
	#webx-content .webx-area-menu a,
	body #webx-content .rcl-bttn.rcl-bttn__type-primary,
	#rcl-office .rcl-subtab-menu .rcl-bttn.rcl-bttn__type-primary.rcl-bttn__active, 
	#rcl-office .rcl-data-filters a.rcl-bttn__disabled,
	body #webx-content .rcl-bttn.rcl-bttn__type-primary, body #webx-content .rcl-bttn.rcl-bttn__type-primary:hover{
		border-radius: ' . $webx_theme_radius_href . 'px;
	}
	#rcl-office .rcl-chat-contacts .noread-message, 
	#rcl-office .rcl-chat-contacts .avatar-contact img, 
	#rcl-office .rcl-chat-contacts .master-avatar img, 
	#rcl-office .rcl-chat .chat-users-box, 
	#rcl-office .rcl-chat .user-avatar img, 
	#rcl-office .rcl-chat .message-box, 
	#rcl-office .rcl-chat .chat-messages, 
	.rcl-chat .chat-form textarea, 
	#rcl-office .rcl-chat .chat-form textarea, 
	.rcl-noread-users, 
	.rcl-chat-panel, 
	#prime-forum .prime-forum-item{
		border-radius: ' . $webx_theme_radius_chat . 'px;
	}
	';

	return $styles;
}