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
                    </fieldset>
                    <fieldset>
                        <label><?php _e('Product Information','usb-swiper'); ?></label>
                        <div id="vt_repeater_field" class="vt-repeater-field">
                            <?php
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
                            <div id="vt_fields_wrap_0" class="vt-fields-wrap">
                                <?php echo usb_swiper_get_html_field(array(
                                    'type' => 'text',
                                    'id' => 'VTProduct',
                                    'name' => 'VTProduct[]',
                                    'required' => false,
                                    'placeholder' => __( 'Search Product', 'usb-swiper'),
                                    'attributes' => '',
                                    'description' => '',
                                    'readonly' => false,
                                    'disabled' => false,
                                    'class' => 'vt-input-field vt-product-input',
                                    'wrapper_class' => 'product'
                                ));
                                echo usb_swiper_get_html_field(array(
                                    'type' => 'number',
                                    'id' => 'VTProductQuantity',
                                    'name' => 'VTProductQuantity[]',
                                    'placeholder' => __( 'Quantity', 'usb-swiper'),
                                    'required' => false,
                                    'attributes' => '',
                                    'description' => '',
                                    'readonly' => false,
                                    'disabled' => false,
                                    'class' => 'vt-input-field vt-product-quantity',
                                    'wrapper_class' => 'product_quantity'
                                ));
                                echo usb_swiper_get_html_field(array(
                                    'type' => 'number',
                                    'id' => 'VTProductPrice',
                                    'name' => 'VTProductPrice[]',
                                    'placeholder' => __( 'Price', 'usb-swiper'),
                                    'required' => false,
                                    'attributes' => '',
                                    'description' => '',
                                    'readonly' => false,
                                    'disabled' => false,
                                    'class' => 'vt-input-field vt-product-price',
                                    'wrapper_class' => 'price'
                                )); ?>
                            </div>
                        </div>
                        <a data-id="0" id="vt_add_item" class="vt-add-item"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><?php _e( 'Add Item', 'usb-swiper'); ?></a>
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
                        <button type="submit" class="vt-button-primary" id="pos-submit-btn"><?php _e('Process Payment','usb-swiper'); ?></button>
                    </div>
                    <div class="usb-swiper-ppcp-cc-form"><div id="angelleye_ppcp_checkout"></div></div>
                </div>
            </div>
        </div>
    </form>
</div>
