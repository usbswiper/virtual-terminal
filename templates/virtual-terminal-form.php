<?php
$profile_status = get_user_meta( get_current_user_id(),'vt_user_verification_status', true );
$is_notice = get_user_meta( get_current_user_id(),'usb_swiper_vt_notice', true );
$profile_status = filter_var($profile_status, FILTER_VALIDATE_BOOLEAN);
$get_merchant_data = usbswiper_get_onboarding_merchant_response(get_current_user_id());
$merchant_id = !empty( $get_merchant_data['merchant_id'] ) ? $get_merchant_data['merchant_id'] : '';
$notifications = [];
if( true === $profile_status && !empty($merchant_id)) {

    $disable_payment = '';

    if( ! usbswiper_get_brand_name() ){
        $disable_payment = 'disabled';
        $edit_page =  wc_get_account_endpoint_url( 'edit-account' );
        $notifications[] = [
                'type' => 'error',
            'message' => sprintf(__('Please add a Brand Name under your %s.  <br />If you have questions about this or would like some help, please call 224-677-0283 x4.', 'usb-swiper'), '<a href="'.esc_url($edit_page).'">'.__('Account Details Settings', 'usb-swiper').'</a>')
        ];
    }

    if( ! usbswiper_get_invoice_prefix() ){
        $disable_payment = 'disabled';
        $edit_page =  wc_get_account_endpoint_url( 'edit-account' );
        $notifications[] = [
            'type' => 'error',
            'message' => sprintf(__('Please add an Invoice Prefix under your %s.  <br />If you have questions about this or would like some help, please call 224-677-0283 x4.', 'usb-swiper'), '<a href="'.esc_url($edit_page).'">'.__('Account Details Settings', 'usb-swiper').'</a>')
        ];
    }

    if( ! usbswiper_get_invoice_prefix() ){
        $disable_payment = 'disabled';
        $edit_page =  wc_get_account_endpoint_url( 'edit-account' );
        $notifications[] = [
            'type' => 'error',
            'message' => sprintf(__('Kindly add the Invoice Prefix on %s to initiate the transaction.', 'usb-swiper'), '<a href="'.esc_url($edit_page).'">'.__('My account', 'usb-swiper').'</a>')
        ];
    }

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
                <div class="vt-col vt-col-60 vt-col-form-fields">
                    <fieldset>
                        <div class="vt-fields-wrap">
			                <?php echo usb_swiper_get_html_field( array(
				                'type' => 'text',
				                'id' => 'customerInformation',
				                'name' => 'customerInformation',
				                'label' => __('Search Customer Information','usb-swiper'),
				                'required' => false,
				                'attributes' => '',
				                'class' => '',
                                'placeholder' => __( 'Search Customer', 'usb-swiper'),
				                'tooltip' => true,
                                'tooltip_text' => __( 'Search for a customer using First Name, Last  Name, Email or Company Name.', 'usb-swiper'),
			                )); ?>
                        </div>
                    </fieldset>
                    <fieldset>
                        <label><?php _e('Currency Information','usb-swiper'); ?></label>
                        <div class="vt-fields-wrap">
                            <?php echo usb_swiper_get_html_field( array(
                                'type' => 'select',
                                'id' => 'TransactionCurrency',
                                'name' => 'TransactionCurrency',
                                'placeholder' => __( 'Currency', 'usb-swiper'),
                                'required' => true,
                                'options' => usbswiper_get_currency_code_options(),
                                'default' => usbswiper_get_default_currency(),
                                'attributes' => '',
                                'description' => '',
                                'readonly' => false,
                                'disabled' => false,
                                'class' => 'usbswiper-change-currency vt-select-field',
                            ) ); ?>
                        </div>
	                    <?php if( empty( $is_notice ) ) { ?>
                            <div class="warning-description">
                                <p><?php _e('<strong>Note:</strong> Zettle transactions can only be processed in US dollars.','usb-swiper'); ?></p>
                                <a href="javascript:void(0);" class="notice-cancel-btn"><svg xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM8.96963 8.96965C9.26252 8.67676 9.73739 8.67676 10.0303 8.96965L12 10.9393L13.9696 8.96967C14.2625 8.67678 14.7374 8.67678 15.0303 8.96967C15.3232 9.26256 15.3232 9.73744 15.0303 10.0303L13.0606 12L15.0303 13.9696C15.3232 14.2625 15.3232 14.7374 15.0303 15.0303C14.7374 15.3232 14.2625 15.3232 13.9696 15.0303L12 13.0607L10.0303 15.0303C9.73742 15.3232 9.26254 15.3232 8.96965 15.0303C8.67676 14.7374 8.67676 14.2625 8.96965 13.9697L10.9393 12L8.96963 10.0303C8.67673 9.73742 8.67673 9.26254 8.96963 8.96965Z" fill="#3e474f"/></svg></a>
                            </div>
	                    <?php } ?>
                    </fieldset>
                    <fieldset>
                        <label><?php _e('Product Information','usb-swiper'); ?></label>
                        <div id="vt_repeater_field" class="vt-repeater-field">
                            <?php
                            echo get_product_html();
                            echo usb_swiper_get_html_field(array(
                                'type' => 'hidden',
                                'id' => 'vt_add_product_nonce',
                                'name' => 'vt_add_product_nonce',
                                'required' => false,
                                'attributes' => '',
                                'description' => '',
                                'readonly' => false,
                                'disabled' => false,
                                'value' => wp_create_nonce('vt_add_product_nonce')
                            ));
                            ?>
                        </div>
                        <button type="button" id="vt_add_item" class="vt-add-item vt-button"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><?php _e( 'Add Item', 'usb-swiper'); ?></button>
                    </fieldset>
					<?php
					$tab_fields = usb_swiper_get_vt_tab_fields();
					if( !empty( $tab_fields ) && is_array( $tab_fields ) ) {
						foreach ( $tab_fields as $tab_key => $tab_field ) {
							$form_fields = usb_swiper_get_vt_form_fields( $tab_key );
							?>
                            <fieldset>
                                <label><?php echo !empty( $tab_field ) ? $tab_field : ''; ?></label>
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
                            echo usb_swiper_get_html_field(array(
                                'type' => 'hidden',
                                'id' => 'vt_add_tax_nonce',
                                'name' => 'vt_add_tax_nonce',
                                'required' => false,
                                'attributes' => '',
                                'description' => '',
                                'readonly' => false,
                                'disabled' => false,
                                'value' => wp_create_nonce('vt_add_tax_nonce')
                            ));
						}
					}
					?>
                </div>
                <div class="vt-col-40">
                    <div class="vt-payment-wrapper">
                        <div class="vt-col vt-col-pay-by-invoice">
                            <fieldset>
                                <label><?php _e('Customer Information', 'usb-swiper'); ?><span class="tool customer-review-tooltip" id="tooltip"  data-tip="<?php _e('Select if you want to save the new customer’s record.', 'usb-swiper'); ?>" tabindex="1">?</span></label>

                                <div class="vt-fields-wrap review_changes">
                                    <?php
                                    echo usb_swiper_get_html_field(array(
                                        'type' => 'checkbox',
                                        'id' => 'save_customer_details',
                                        'name' => 'save_customer_details',
                                        'label' => __( 'Do you want to Save this customer’s record?', 'usb-swiper'),
                                        'required' => false,
                                        'value' => true,
                                        'checked' => false,
                                        'attributes' => array(
                                            'data-default-checked' => 'FALSE'
                                        ),
                                        'description' => '',
                                        'class' => '',
                                    ));
                                    ?>
                                </div>
                            </fieldset>
                        </div>
                        <div class="vt-col vt-col-pay-by-invoice">
                            <fieldset>
                                <label><?php _e('Invoicing','usb-swiper'); ?><span class="tool" data-tip="<?php _e('Enable invoicing to send an email invoice to your buyer. They can pay the invoice using PayPal or a credit card.','usb-swiper'); ?>" tabindex="1">?</span></label>

                                <div class="vt-fields-wrap">
                                    <?php
                                    echo usb_swiper_get_html_field(array(
                                        'type' => 'checkbox',
                                        'id' => 'PayByInvoiceDisabled',
                                        'name' => 'PayByInvoiceDisabled',
                                        'label' => __( 'Enable Invoicing', 'usb-swiper'),
                                        'required' => false,
                                        'value' => "true",
                                        'checked' => true,
                                        'attributes' => array(
                                            'data-default-checked' => 'FALSE'
                                        ),
                                        'description' => '',
                                        'class' => '',
                                    ));
                                    echo usb_swiper_get_html_field(array(
                                        'type' => 'button',
                                        'id' => 'PayByInvoice',
                                        'name' => 'PayByInvoice',
                                        'btn_type' => 'button',
                                        'required' => false,
                                        'value' => __( 'Send Invoice', 'usb-swiper'),
                                        'description' => '',
                                        'class' => 'vt-button',
                                        'attributes' => [
                                            $disable_payment => !empty( $disable_payment ) ? true : false
                                        ]
                                    ));
                                    ?>
                                </div>
                            </fieldset>
                        </div>
                        <div class="vt-col vt-col-pay-with-zettle">
                            <fieldset>
                                <label><?php _e('Zettle','usb-swiper'); ?><span class="tool" data-tip="<?php _e('Enable to make payment with zettle.','usb-swiper'); ?>" tabindex="1">?</span></label>
                                <div class="vt-fields-wrap">
				                    <?php
					                    echo usb_swiper_get_html_field(array(
						                    'type' => 'checkbox',
						                    'id' => 'PayWithZettleDisabled',
						                    'name' => 'PayWithZettleDisabled',
						                    'label' => __( 'Enable Zettle', 'usb-swiper'),
						                    'required' => false,
						                    'value' => "true",
						                    'checked' => true,
						                    'attributes' => array(
							                    'data-default-checked' => 'FALSE'
						                    ),
						                    'description' => '',
						                    'class' => '',
					                    ));
					                    echo usb_swiper_get_html_field(array(
						                    'type' => 'button',
						                    'id' => 'PayWithZettle',
						                    'name' => 'PayWithZettle',
						                    'btn_type' => 'button',
						                    'required' => false,
						                    'value' => __( 'Pay With Zettle', 'usb-swiper'),
						                    'description' => '',
						                    'class' => 'vt-button',
                                            'attributes' => [
                                                $disable_payment => !empty( $disable_payment ) ? true : false
                                            ]
					                    ));
				                    ?>
                                </div>
                            </fieldset>
                            <div class="zettle-response"><ul></ul></div>
                        </div>
                        <div class="vt-col vt-col-payments">
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
                                <input type="hidden" name="card_type" id="card_type" value="">
                                <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('vt-form-transaction'); ?>">
                                <button type="submit" <?php echo esc_attr($disable_payment); ?> class="vt-button" id="pos-submit-btn"><?php _e('Process Payment','usb-swiper'); ?></button>
                            </div>
                            <div class="usb-swiper-ppcp-cc-form"><div id="angelleye_ppcp_checkout"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php usb_swiper_get_template('vt-payment-timeout-popup.php');
} else {
    if( empty($merchant_id) ){ ?>
        <div class="vt-form-wrap woocommerce">
            <div class="vt-form-notification">
                <p class="notification error"><?php _e("Your merchant ID is unavailable. Please log out and back in to refresh your session/merchant ID.","usb-swiper"); ?></p>
            </div>
        </div>
<?php
    }
}
?>