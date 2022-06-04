<?php
/**
 * Template office
 */

?>
<div class="webx-main webx--padding">
    <div id="webx-header">
        <div class="webx-row">
            <div class="webx-col-md-3">
				<?php rcl_avatar( 450 ); ?>
            </div>
            <div class="webx-col-md-9">
                <div id="lk-conteyner"><?php do_action( 'rcl_area_top' ); ?></div>
            </div>
        </div>
    </div>

    <div class="webx-userinfo">
        <div class="webx-user-left">
            <div class="webx-user-name"><?php rcl_username(); ?></div>
            <div class="webx-in-user-name"><?php do_action( 'webx_area_name' ); ?></div>
        </div>
        <div class="webx-user-right">
            <div class="webx-user-center"><?php do_action( 'webx_area_center' ); ?></div>
            <div class="webx-user-counters">
				<?php do_action( 'rcl_area_counters' ); ?>
            </div>
        </div>
    </div>

    <div id="webx-content">
        <div class="webx-row">
            <div class="webx-col-md-3">
                <div class="webx-area-menu">
                    <a class="webx_phone_menu" href="#"><i class="rcli fa-bars"></i><span><?php esc_html_e( 'Menu', 'wp-recall' ) ?></span></a>
                    <div class="webx_phone_block">
						<?php do_action( 'rcl_area_menu' ); ?>
                    </div>
                </div>
            </div>
            <div class="webx-col-md-9">
                <div class="webx-area-tabs">
					<?php do_action( 'rcl_area_tabs' ); ?>
                </div>
            </div>
        </div>
    </div>
    <div id="webx-footer"></div>
</div>
