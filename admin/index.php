<?php
require_once "admin-menu.php";
require_once "metaboxes.php";
require_once "deprecated.php";

add_action( 'admin_init', 'rcl_admin_scripts', 10 );

add_filter( 'display_post_states', 'rcl_mark_own_page', 10, 2 );
function rcl_mark_own_page( $post_states, $post ) {

	if ( $post->post_type === 'page' ) {

		$plugin_pages = get_site_option( 'rcl_plugin_pages' );

		if ( ! $plugin_pages )
			return $post_states;

		if ( in_array( $post->ID, $plugin_pages ) ) {
			$post_states[] = __( 'The page of plugin WP-Recall' );
		}
	}

	return $post_states;
}

function rmag_global_options() {

	RCL()->use_module( 'options-manager' );

	$Manager = new Rcl_Options_Manager( array(
		'option_name'	 => 'primary-rmag-options',
		'page_options'	 => 'manage-wpm-options',
		) );

	$Manager = apply_filters( 'rcl_commerce_options', $Manager );

	$content = '<h2>' . __( 'Settings of commerce', 'wp-recall' ) . '</h2>';

	$content .= $Manager->get_content();

	echo $content;
}

function rmag_update_options() {
	if ( isset( $_POST['primary-rmag-options'] ) ) {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-options-rmag' ) )
			return false;
		$_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		foreach ( $_POST['global'] as $key => $value ) {
			if ( $key == 'primary-rmag-options' )
				continue;
			$options[$key] = $value;
		}

		update_site_option( 'primary-rmag-options', $options );

		if ( isset( $_POST['local'] ) ) {
			foreach ( ( array ) $_POST['local'] as $key => $value ) {
				update_site_option( $key, $value );
			}
		}

		wp_redirect( admin_url( 'admin.php?page=manage-wpm-options' ) );
		exit;
	}
}

add_action( 'init', 'rmag_update_options' );
function rcl_wp_list_current_action() {
	if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
		return false;

	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
		return $_REQUEST['action'];

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
		return $_REQUEST['action2'];

	return false;
}

if ( is_admin() )
	add_action( 'admin_init', 'rcl_postmeta_post' );
function rcl_postmeta_post() {
	add_meta_box( 'recall_meta', __( 'WP-Recall settings', 'wp-recall' ), 'rcl_options_box', 'post', 'normal', 'high' );
	add_meta_box( 'recall_meta', __( 'WP-Recall settings', 'wp-recall' ), 'rcl_options_box', 'page', 'normal', 'high' );
}

function rcl_options_box( $post ) {
	$content = '';
	echo apply_filters( 'rcl_post_options', $content, $post );
	?>
	<input type="hidden" name="rcl_fields_nonce" value="<?php echo wp_create_nonce( __FILE__ ); ?>" />
	<?php
}

add_action( 'save_post', 'rcl_postmeta_update', 0 );
function rcl_postmeta_update( $post_id ) {
	if ( ! isset( $_POST['rcl_fields_nonce'] ) )
		return false;
	if ( ! wp_verify_nonce( $_POST['rcl_fields_nonce'], __FILE__ ) )
		return false;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return false;
	if ( ! current_user_can( 'edit_post', $post_id ) )
		return false;

	if ( ! isset( $_POST['wprecall'] ) )
		return false;

	$POST = $_POST['wprecall'];

	foreach ( $POST as $key => $value ) {
		if ( ! is_array( $value ) )
			$value = trim( $value );
		if ( $value == '' )
			delete_post_meta( $post_id, $key );
		else
			update_post_meta( $post_id, $key, $value );
	}
	return $post_id;
}

function wp_enqueue_theme_rcl( $url ) {
	wp_enqueue_style( 'theme_rcl', $url );
}

add_filter( 'admin_footer_text', 'rcl_admin_footer_text', 10 );
function rcl_admin_footer_text( $footer_text ) {
	$current_screen = get_current_screen();

	$dlm_page_ids = array(
		'toplevel_page_manage-wprecall',
		'wp-recall_page_rcl-options',
		'wp-recall_page_rcl-repository',
		'wp-recall_page_manage-addon-recall',
		'wp-recall_page_manage-templates-recall',
		'wp-recall_page_rcl-tabs-manager',
		'wp-recall_page_manage-userfield',
		'wp-recall_page_manage-public-form'
	);

	if ( isset( $current_screen->id ) && in_array( $current_screen->id, $dlm_page_ids ) ) {
		$footer_text = sprintf( __( 'If you liked plugin %sWP-Recall%s, please vote for it in repository %s★★★★★%s. Thank you so much!', 'wp-recall' ), '<strong>', '</strong>', '<a href="https://wordpress.org/support/view/plugin-reviews/wp-recall?filter=5#new-post" target="_blank">', '</a>' );
	}

	return $footer_text;
}

function rcl_send_addon_activation_notice( $addon_id, $addon_headers ) {
	wp_remote_post( RCL_SERVICE_HOST . '/products-files/api/add-ons.php?rcl-addon-info=add-notice', array( 'body' => array(
			'rcl-key'	 => get_site_option( 'rcl-key' ),
			'addon-id'	 => $addon_id,
			'headers'	 => array(
				'version'	 => $addon_headers['version'],
				'item-id'	 => $addon_headers['item-id'],
				'key-id'	 => $addon_headers['key-id'],
			),
			'host'		 => $_SERVER['SERVER_NAME']
		)
		)
	);
}
