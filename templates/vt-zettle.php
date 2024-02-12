<?php
?>
<div class="zettle-wrap">
	<div class="zettle-settings">
		<form method="post" action="" id="vt_zettle_form" name="vt_zettle_form" enctype="multipart/form-data">
			<div class="vt-product-content">
				<?php
				if( !empty( $get_zettle_fields ) && is_array( $get_zettle_fields ) ) {
				
					foreach ( $get_zettle_fields as $key => $get_zettle_field ) {
						
						echo usb_swiper_get_html_field( $get_zettle_field );
					}
				}
				?>
			</div>
			<div class="input-field-wrap button-wrap">
				<input type="hidden" name="action" id="vt_zettle_form_actio" value="vt-zettle-form">
				<input type="hidden" name="_nonce" id="vt_zettle_form_nonce" value="<?php echo wp_create_nonce('vt-zettle-form-nonce'); ?>">
				<button id="vt_zettle_settings" type="submit" class="vt-button"><?php _e( 'Save changes', 'usb-swiper'); ?></button>
			</div>
		</form>
	</div>
    <div class="zettle-contents">
        <?php
            if( !empty( $zettle_settings['zettle_client_id']  ) ) {
	            
	            $access_token = !empty( $zettle_token['access_token'] ) ? $zettle_token['access_token'] : '';
                if( empty( $access_token ) ) {
	                
	                ?>
                    <a href="<?php echo esc_url( UsbSwiperZettle::get_generate_token_link() ); ?>" class="vt-button"><?php _e( 'Connect to Zettle', 'usb-swiper'); ?></a>
	                <?php
                } else {
	                $disconnect_app_link = add_query_arg(
		                [
			                'disconnect_app' => true,
		                ],
		                UsbSwiperZettle::get_redirection_uri(),
	                );
	                
	                ?>
                    <a href="<?php echo esc_url( $disconnect_app_link ); ?>" class="vt-button"><?php _e( 'Disconnect from Zettle', 'usb-swiper'); ?></a>
	                <?php
                }
            }
        ?>
    </div>
</div>
