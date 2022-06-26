<?php

//Подключение стилей
if ( ! is_admin() ) {
	add_action( 'rcl_enqueue_scripts', 'rcl_webx_theme_style', 10 );
}
function rcl_webx_theme_style() {
	rcl_enqueue_style( 'rcl_webx_theme_style', rcl_addon_url( 'assets/css/style.css', __FILE__ ) );
}

// инициализируем наши скрипты
add_action( 'rcl_enqueue_scripts', 'rcl_webx_script_load' );
function rcl_webx_script_load() {
	if ( rcl_is_office() ) {
		rcl_enqueue_script( 'theme-scripts', rcl_addon_url( 'assets/js/main.js', __FILE__ ), false, true );
	}
}


// выводим обложку
add_filter( 'rcl_inline_styles', 'rcl_webx_add_cover_inline_styles', 10 );
function rcl_webx_add_cover_inline_styles( $styles ) {
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
	$styles     .= '#lk-conteyner{background-image: url(' . $cover_url . '?vers=' . @filemtime( $cover_path ) . ');}';

	return $styles;
}

// объявляем поддержку загрузки аватарки, загрузку обложки, модальное окно "Подробная информация"
add_action( 'rcl_addons_included', 'rcl_webx_setup_template_options', 10 );
function rcl_webx_setup_template_options() {
	rcl_template_support( 'avatar-uploader' );
	rcl_template_support( 'cover-uploader' );
	rcl_template_support( 'modal-user-details' );
}

add_filter( 'rcl_options', 'rcl_webx_construct_theme' );
function rcl_webx_construct_theme( $options ) {
	//Настройки цвета
	$options->box( 'primary' )->add_group( 'design', [
		'title' => __( 'Design', 'wp-recall' ),
	] )->add_options( [
		[
			'type'    => 'color',
			'slug'    => 'primary-color',
			'title'   => __( 'Primary color', 'wp-recall' ),
			'default' => '#4C8CBD',
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-color',
			'title'   => __( 'Primary button color', 'wp-recall' ),
			'default' => '#000000',
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-color',
			'title'   => __( 'The main color of the buttons in the menu', 'wp-recall' ),
			'default' => '#ffffff',
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-background',
			'title'   => __( 'The main background color of the buttons in the menu', 'wp-recall' ),
			'default' => '#000000',
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-color-hover',
			'title'   => __( 'The main color of the buttons in the menu on hover', 'wp-recall' ),
			'default' => '#000000',
		],
		[
			'type'    => 'color',
			'slug'    => 'webx-theme-href-background-hover',
			'title'   => __( 'The main background color of the buttons in the menu on hover', 'wp-recall' ),
			'default' => '#ffffff',
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-padding',
			'title'      => __( 'Padding from the edge in the personal account', 'wp-recall' ),
			'value_step' => '1',
			'value_max'  => '100',
			'value_min'  => '0',
			'default'    => '12',
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-avatar',
			'title'      => __( 'Rounding up the avatar', 'wp-recall' ),
			'value_step' => '1',
			'default'    => '0',
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-cover',
			'title'      => __( 'Rounding Cover', 'wp-recall' ),
			'value_step' => '1',
			'default'    => '0',
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-userinfo',
			'title'      => __( 'Rounding under the avatar line', 'wp-recall' ),
			'value_step' => '1',
			'default'    => '0',
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-boxcontent',
			'title'      => __( 'Rounding up the main block', 'wp-recall' ),
			'value_step' => '1',
			'default'    => '0',
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-href',
			'title'      => __( 'Rounding buttons', 'wp-recall' ),
			'value_step' => '1',
			'default'    => '0',
		],
		[
			'type'       => 'runner',
			'slug'       => 'webx-theme-radius-chat',
			'title'      => __( 'Rounding up the chat style', 'wp-recall' ),
			'value_step' => '1',
			'default'    => '0',
		],
		[
			'type'      => 'radio',
			'slug'      => 'rcl_hide_avatar',
			'title'     => __( 'Disable avatar uploader in personal account?', 'wp-recall' ),
			'values'    => [ __( 'No', 'wp-recall' ), __( 'Yes', 'wp-recall' ) ],
			'default'   => 0,
			'childrens' => [
				0 => [
					[
						'type'       => 'uploader',
						'temp_media' => 1,
						'multiple'   => 0,
						'crop'       => 1,
						'filetitle'  => 'rcl-default-avatar',
						'filename'   => 'rcl-default-avatar',
						'slug'       => 'default_avatar',
						'title'      => __( 'Default avatar', 'wp-recall' ),
					],
				],
			],
		],
	] );


	return $options;

}

add_filter( 'rcl_inline_styles', 'rcl_webx_add_colors_inline_styles', 10 );
function rcl_webx_add_colors_inline_styles( $styles ) {
	if ( ! rcl_is_office() ) {
		return $styles;
	}

	if ( rcl_get_option( 'primary-color' ) ) {
		global $rcl_options;

		$lca_hex = rcl_get_option( 'primary-color', '#4C8CBD' ); // достаем оттуда наш цвет
		[ $r, $g, $b ] = sscanf( $lca_hex, "#%02x%02x%02x" );

		$rp = round( $r * 0.90 );
		$gp = round( $g * 0.90 );
		$bp = round( $b * 0.90 );

		$styles .= '
			.rcl-noread-users, .rcl-chat-panel {
				background: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', .85);
			}
			body .rcl_preloader i {
				color: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', 1);
			}
			.rcl-noread-users a.active-chat::before {
			    border-right-color: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', .85);
			}
			.rcl-chat .nth .message-box {
				background: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', .35);
			}
			.rcl-chat .nth .message-box::before {
				border-right-color: rgba(' . $rp . ', ' . $gp . ', ' . $bp . ', .35);
			}
		';


		$webx_theme_color = rcl_get_option( 'webx-theme-color' );
		if ( $webx_theme_color ) {
			$styles .= '
				#webx-content .webx-area-menu a,
				#rcl-office .webx-userinfo .webx-area-counters a,
				#rcl-office .balance-amount a {
					color: ' . $webx_theme_color . ';
				}
			';
		}


		$webx_theme_href_background_hover = rcl_get_option( 'webx-theme-href-background-hover' );
		$webx_theme_href_color_hover      = rcl_get_option( 'webx-theme-href-color-hover', '#fff' );
		if ( $webx_theme_href_background_hover ) {
			$styles .= '
				#rcl-office #lk-menu a.recall-button:hover,
				body #webx-content .rcl-bttn.rcl-bttn__type-primary:hover,
				body #rcl-office .webx_phone_menu:hover,
				#rcl-office .webx-userinfo .webx-area-counters a:hover,
				#rcl-office .balance-amount a:hover {
					background: ' . $webx_theme_href_background_hover . ' !important;
					border-color: ' . $webx_theme_href_background_hover . ' !important;
					color: ' . $webx_theme_href_color_hover . ' !important;
				}
			';
		}


		$webx_theme_href_background = rcl_get_option( 'webx-theme-href-background' );
		$webx_theme_href_color      = rcl_get_option( 'webx-theme-href-color', '#000' );
		if ( $webx_theme_href_background ) {
			$styles .= '
				#rcl-office #lk-menu a.recall-button.active,
				#rcl-office .rcl-subtab-menu .rcl-bttn.rcl-bttn__type-primary.rcl-bttn__active,
				body #webx-content .rcl-bttn.rcl-bttn__type-primary, 
				body .rcl-bttn.rcl-bttn__type-primary,
				body #rcl-office .webx_phone_menu {
					background: ' . $webx_theme_href_background . ' !important;
					border-color: ' . $webx_theme_href_background . ' !important;
					color: ' . $webx_theme_href_color . ' !important;
				}
			';
		}


		$webx_padding = rcl_get_option( 'webx-theme-padding', 18 );
		$styles       .= '
			.webx--padding {
				margin: ' . $webx_padding . 'px;
			}
		';

		/*Блок округления блоков*/

		$webx_theme_radius_href = rcl_get_option( 'webx-theme-radius-href', 0 );
		if ( $webx_theme_radius_href > 0 ) {
			$styles .= '
				#webx-content .webx-area-menu a,
				body #webx-content .rcl-bttn.rcl-bttn__type-primary,
				#rcl-office .rcl-subtab-menu .rcl-bttn.rcl-bttn__type-primary.rcl-bttn__active, 
				#rcl-office .rcl-data-filters a.rcl-bttn__disabled,
				body #webx-content .rcl-bttn.rcl-bttn__type-primary, 
				body #webx-content .rcl-bttn.rcl-bttn__type-primary:hover,
				#rcl-office .webx-userinfo .webx-area-counters a,
				#rcl-office .balance-amount a,
				#rcl-office #webx-header .rcl-cover-icon,
				#webx-header #rcl-avatar .avatar-icons .rcl-avatar-icon a {
					border-radius: ' . $webx_theme_radius_href . 'px;
				}
			';
		}


		$webx_theme_radius_avatar = rcl_get_option( 'webx-theme-radius-avatar', 0 );
		if ( $webx_theme_radius_avatar > 0 ) {
			$styles .= '
				#rcl-office #rcl-avatar img {
					border-radius: ' . $webx_theme_radius_avatar . 'px;
				}
			';
		}


		$webx_theme_radius_cover = rcl_get_option( 'webx-theme-radius-cover', 0 );
		if ( $webx_theme_radius_cover > 0 ) {
			$styles .= '
				#lk-conteyner {
					border-radius: ' . $webx_theme_radius_cover . 'px;
				}
			';
		}


		$webx_theme_radius_userinfo = rcl_get_option( 'webx-theme-radius-userinfo', 0 );
		if ( $webx_theme_radius_cover > 0 ) {
			$styles .= '
				.webx-userinfo {
					border-radius: ' . $webx_theme_radius_userinfo . 'px;
				}
			';
		}


		$webx_theme_radius_boxcontent = rcl_get_option( 'webx-theme-radius-boxcontent', 0 );
		if ( $webx_theme_radius_cover > 0 ) {
			$styles .= '
				#webx-content .webx-area-tabs,
				#webx-content .rcl-notice {
					border-radius: ' . $webx_theme_radius_boxcontent . 'px;
				}
			';
		}

		$webx_theme_radius_chat = rcl_get_option( 'webx-theme-radius-chat', 0 );
		if ( $webx_theme_radius_cover > 0 ) {
			$styles .= '
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
				#prime-forum .prime-forum-item {
					border-radius: ' . $webx_theme_radius_chat . 'px;
				}
			';
		}

	}

	return $styles;
}

add_action( 'widgets_init', 'rcl_webx_sidebar_before' );
function rcl_webx_sidebar_before() {
	register_sidebar( [
		'name'          => 'RCL: ' . __( 'Sidebar above the personal account', 'wp-recall' ),
		'id'            => 'rcl_webx_sidebar_before',
		'description'   => __( 'It is displayed only in the personal account.', 'wp-recall' ),
		'before_title'  => '<h3 class="cab_title_before">',
		'after_title'   => '</h3>',
		'before_widget' => '<div class="rcl_webx_sidebar rcl_webx_sidebar-before webx--padding">',
		'after_widget'  => '</div>',
	] );
}

add_action( 'widgets_init', 'rcl_webx_sidebar_after' );
function rcl_webx_sidebar_after() {
	register_sidebar( [
		'name'          => 'RCL: ' . __( 'Sidebar under the personal account', 'wp-recall' ),
		'id'            => 'rcl_webx_sidebar_after',
		'description'   => __( 'It is displayed only in the personal account.', 'wp-recall' ),
		'before_title'  => '<h3 class="cab_title_after">',
		'after_title'   => '</h3>',
		'before_widget' => '<div class="rcl_webx_sidebar rcl_webx_sidebar-after webx--padding">',
		'after_widget'  => '</div>',
	] );
}

add_action( 'rcl_area_before', 'rcl_webx_add_sidebar_area_before' );
function rcl_webx_add_sidebar_area_before() {
	if ( function_exists( 'dynamic_sidebar' ) ) {
		dynamic_sidebar( 'rcl_webx_sidebar_before' );
	}
}

add_action( 'rcl_area_after', 'rcl_webx_add_sidebar_area_after' );
function rcl_webx_add_sidebar_area_after() {
	if ( function_exists( 'dynamic_sidebar' ) ) {
		dynamic_sidebar( 'rcl_webx_sidebar_after' );
	}
}

add_action( 'webx_area_center', 'rcL_webx_balance_out', 50 );
function rcL_webx_balance_out() {
	if ( ! rcl_exist_addon( 'user-balance' ) ) {
		return;
	}

	global $user_ID;

	if ( rcl_is_office( $user_ID ) ) {
		echo do_shortcode( '[rcl-usercount]' );
	}
}
