<?php
?>
<div class="zettle-wrap">
    <div class="vt-form-notification"></div>
	<div class="zettle-settings">
		<form method="post" action="" id="vt_zettle_form" name="vt_zettle_form" enctype="multipart/form-data">
			<div class="vt-zettle-content vt-product-content">
				<?php
				if( !empty( $get_zettle_fields ) && is_array( $get_zettle_fields ) ) {
				
					foreach ( $get_zettle_fields as $key => $get_zettle_field ) {
						
						echo usb_swiper_get_html_field( $get_zettle_field );
					}
				}
				?>
			</div>
			<div class="input-field-wrap button-wrap">
				<input type="hidden" name="action" id="vt_zettle_form_action" value="vt-zettle-form">
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
	                
	                $reader_data = UsbSwiperZettle::get_zettle_reader_data();
	                ?>
                    <div class="zettle-pair-reader">
                        <form method="post" id="zettle_pair_reader_form">
                            <div class="vt-zettle-reader-content vt-product-content">
                            <?php
	                            echo usb_swiper_get_html_field( [
		                            'type' => 'text',
		                            'id' => 'zettle_pair_reader_code',
		                            'name' => 'zettle_pair_reader_code',
		                            'label' => __('Reader code', 'usb-swiper'),
		                            'required' => true,
		                            'attributes' => '',
		                            'class' => 'regular-text vt-input-field',
		                            'value' =>  !empty( $reader_data['zettle_pair_reader_code'] ) ? $reader_data['zettle_pair_reader_code'] : '',
		                            'description' => '',
	                            ] );
                             
	                            echo usb_swiper_get_html_field( [
		                            'type' => 'text',
		                            'id' => 'zettle_pair_reader_device_name',
		                            'name' => 'zettle_pair_reader_device_name',
		                            'label' => __('Device name', 'usb-swiper'),
		                            'required' => false,
		                            'attributes' => '',
		                            'class' => 'regular-text vt-input-field',
		                            'value' =>  !empty( $reader_data['zettle_pair_reader_device_name'] ) ? $reader_data['zettle_pair_reader_device_name'] : '',
		                            'description' => '',
                                    'default' => __( 'USBSwiper Terminal', 'usb-swiper' ),
	                            ] );
                            ?>
                            </div>
                            <div class="input-field-wrap button-wrap">
                                <input type="hidden" name="form_action" id="vt_zettle_pair_reader_action" value="vt-zettle-pair-reader-form">
                                <input type="hidden" name="_nonce" id="vt_zettle_pair_reader_nonce" value="<?php echo wp_create_nonce('vt-zettle-pair-reader'); ?>">
                                <?php if( !empty( $reader_data ) && is_array( $reader_data ) ) {
	                                
	                                $unpairing_zettle_device_link = add_query_arg(
		                                [
			                                'unpairing' => true,
		                                ],
		                                UsbSwiperZettle::get_redirection_uri(),
	                                );
                                    ?>
                                    <a href="<?php echo esc_url( $unpairing_zettle_device_link ); ?>" id="vt_zettle_unpairing_reader_settings" class="vt-button"><?php _e( 'Unpairing Zettle Device', 'usb-swiper'); ?></a>
                                <?php } else { ?>
                                    <button id="vt_zettle_pair_reader_settings" type="submit" class="vt-button"><?php _e( 'Pair Zettle Device', 'usb-swiper'); ?></button>
                                <?php } ?>
                                <a href="<?php echo esc_url( $disconnect_app_link ); ?>" class="vt-button"><?php _e( 'Disconnect from Zettle', 'usb-swiper'); ?></a>
                            </div>
                        </form>
                        <?php if( !empty( $reader_data ) && is_array( $reader_data ) ) { ?>
                            <h2 class="wc-account-title zettle-device-info"><?php esc_html_e('Zettle Device Information', 'usb-swiper' ); ?></h2>
                            <table class="form-table zettle-device-information" cellspacing="0" cellpadding="0">
                                <tbody>
                                    <tr>
                                        <td><strong><?php esc_html_e('ID', 'usb-swiper' ); ?></strong></td>
                                        <td><?php echo !empty( $reader_data['id'] ) ? $reader_data['id'] : ''; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Organization Uuid', 'usb-swiper' ); ?></strong></td>
                                        <td><?php echo !empty( $reader_data['organizationUuid'] ) ? $reader_data['organizationUuid'] : ''; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Model', 'usb-swiper' ); ?></strong></td>
                                        <td><?php echo !empty( $reader_data['readerTags']['model'] ) ? $reader_data['readerTags']['model'] : ''; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Serial Number', 'usb-swiper' ); ?></strong></td>
                                        <td><?php echo !empty( $reader_data['readerTags']['serial_number'] ) ? $reader_data['readerTags']['serial_number'] : ''; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Device Code', 'usb-swiper' ); ?></strong></td>
                                        <td><?php echo !empty( $reader_data['zettle_pair_reader_code'] ) ? $reader_data['zettle_pair_reader_code'] : ''; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php esc_html_e('Device Name', 'usb-swiper' ); ?></strong></td>
                                        <td><?php echo !empty( $reader_data['zettle_pair_reader_device_name'] ) ? $reader_data['zettle_pair_reader_device_name'] : ''; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
	                <?php
                }
            }
        ?>
    </div>
</div>
