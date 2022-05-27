<?php
/**
 * Template office
 */

global $active_addons;
?>
<div class="webx-main">
    <div id="webx-header">
        <div class="row">
            <div class="col-md-3">
				<?php rcl_avatar( 200 ); ?>
            </div>
            <div class="col-md-9">
                <div id="lk-conteyner"><?php do_action( 'rcl_area_top' ); ?></div>
            </div>
        </div>
    </div>

    <div id="webx-userinfo">
        <div class="webx-userinfo">
            <div class="row">
                <div class="col-md-3 userName"><?php rcl_username(); ?></div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">
							<?php if ( isset( $active_addons['user-balance'] ) ) {
								global $user_ID;
								if ( rcl_is_office( $user_ID ) ) {
									echo do_shortcode( '[rcl-usercount]' );
								}
							} ?>
                        </div>
                        <div class="col-md-6 webx-area-counters">
							<?php do_action( 'rcl_area_counters' ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="webx-content">
        <div class="row">
            <div class="col-md-3">
                <div class="webx-area-menu">
                    <a class="webx_phone_menu" href="#"><i class="rcli fa-bars"></i> <?php esc_html_e( 'Menu', 'wp-recall' ) ?></a>
                    <div class="webx_phone_block">
						<?php do_action( 'rcl_area_menu' ); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="webx-area-tabs">
					<?php do_action( 'rcl_area_tabs' ); ?>
                </div>
            </div>
        </div>
    </div>
    <div id="webx-footer"></div>
</div>
