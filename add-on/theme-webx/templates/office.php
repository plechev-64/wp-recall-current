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

    <div id="webx-userinfo">
        <div class="webx-userinfo">
            <div class="webx-row">
                <div class="webx-col-md-3 userName"><?php rcl_username(); ?><?php do_action( 'webx_area_name' ); ?></div>
                <div class="webx-col-md-9">
                    <div class="webx-row">
                        <div class="webx-col-md-6">
							<?php do_action( 'webx_area_center' ); ?>
                        </div>
                        <div class="webx-col-md-6 webx-area-counters">
							<?php do_action( 'rcl_area_counters' ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="webx-content">
        <div class="webx-row">
            <div class="webx-col-md-3">
                <div class="webx-area-menu">
                    <a class="webx_phone_menu" href="#"><i class="rcli fa-bars"></i> <?php esc_html_e( 'Menu', 'wp-recall' ) ?></a>
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
