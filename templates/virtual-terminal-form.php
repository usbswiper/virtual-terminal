<?php
$profile_status = get_user_meta( get_current_user_id(),'vt_user_verification_status', true );
$profile_status = filter_var($profile_status, FILTER_VALIDATE_BOOLEAN);
if( true === $profile_status) {
?>
<div class="vt-form-wrap woocommerce">
    <div class="vt-form-notification">
        <?php
        if( !empty( $notifications ) && is_array( $notifications ) ) {
            foreach ( $notifications as $key => $notification ) {
                $type = !empty( $notification['type'] ) ? $notification['type'] : '';
                $message = !empty( $notification['message'] ) ? $notification['message'] : '';
                echo "<p class='notification {$type}'>{$message}</p>";
            }
        }
        ?>
    </div>
    <form method="post" action="" class="HostedFields" name="ae-paypal-pos-form" id="ae-paypal-pos-form" enctype="multipart/form-data">
        <div class="vt-form-contents">
            <div class="vt-row">
                <div class="vt-col vt-col-60">
					<?php
					$tab_fields = usb_swiper_get_vt_tab_fields();
					if( !empty( $tab_fields ) && is_array( $tab_fields ) ) {
						foreach ( $tab_fields as $tab_key => $tab_field ) {
							$form_fields = usb_swiper_get_vt_form_fields( $tab_key );
							?>
                            <fieldset>
                                <legend><?php echo !empty( $tab_field ) ? $tab_field : ''; ?></legend>
                                <div class="vt-fields-wrap">
									<?php
									if( !empty( $form_fields ) && is_array( $form_fields ) ) {
										foreach ( $form_fields as $form_field ) {
											echo usb_swiper_get_html_field( $form_field );
										}
									}
									?>
                                </div>
                            </fieldset>
							<?php
						}
					}
					?>
                </div>
                <div class="vt-col vt-col-40 vt-col-payments">
                    <div class="usb-swiper-advanced-cc-form">
                        <div class="card-form">
                            <div class="vt-card-field vt-card-number">
                                <label for="card-number"><?php _e('Card Number','usb-swiper'); ?></label>
                                <div id="card-number" class="card_field"></div>
                            </div>
                            <div class="vt-card-details">
                                <div class="vt-card-field vt-card-field-50 vt-card-expiration-date">
                                    <label for="expiration-date"><?php _e('Expiration Date','usb-swiper'); ?></label>
                                    <div id="expiration-date" class="card_field"></div>
                                </div>
                                <div class="vt-card-field vt-card-field-50 vt-card-cvv">
                                    <label for="cvv"><?php _e('CVV','usb-swiper'); ?></label>
                                    <div id="cvv" class="card_field"></div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('vt-form-transaction'); ?>">
                        <button type="submit" class="btn btn-primary button button-primary" id="pos-submit-btn"><?php _e('Process Payment','usb-swiper'); ?></button>
                    </div>
                    <div class="usb-swiper-ppcp-cc-form"><div id="angelleye_ppcp_checkout"></div></div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}
?>