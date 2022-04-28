<div id="webx-main">
    <div id="webx-header">
        <div class="row">
            <div class="col-md-3">
                <?php rcl_avatar( 200 ); ?>
            </div>
            <div class="col-md-9">
                <div id="webx-cover"><?php do_action( 'rcl_area_top' ); ?></div>
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
                            <?php 
                            global $active_addons;
                            if(isset($active_addons['user-balance'])){ 
                            global $user_ID;
                            global $user_LK;
                            if($user_ID==$user_LK){
                            ?>
                            <?php echo do_shortcode('[rcl-usercount]'); ?>
                            <?php } 
                            }?>
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
                    <a class="webx_phone_menu" href="#"><i class="rcli fa-bars"></i> Меню</a>
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





