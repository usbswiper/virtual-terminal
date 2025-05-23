<?php
if( empty($transaction_id)) {
    return;
}

$is_email = !empty( $args['is_email'] );
$is_admin = !empty( $args['is_admin'] );

$card_last_digits = get_post_meta( $transaction_id, '_payment_card_last_digits', true);
$card_brand = get_post_meta( $transaction_id, '_payment_card_brand', true);
$credit_card_number = '';
$payment_status = usbswiper_get_transaction_status($transaction_id);
$payment_source_type = get_post_meta( $transaction_id, 'payment_source', true );

if( ! empty( $card_last_digits )) {
    $credit_card_number = $card_last_digits.' ('.$card_brand.')';
} else {
    if( strtolower($payment_status) !== 'pending' ) {
        $credit_card_number = __('PayPal', 'usb-swiper');
    }
}
$company_name = get_post_meta($transaction_id,'company',true);
$user_invoice_id = get_post_meta( $transaction_id, '_user_invoice_id', true);
$OrderAmount = get_post_meta($transaction_id, 'OrderAmount', true);
$OrderAmount = usb_swiper_price_formatter($OrderAmount);
$DiscountAmount = get_post_meta($transaction_id, 'DiscountAmount', true);
$DiscountAmount = usb_swiper_price_formatter($DiscountAmount);
$NetAmount = get_post_meta( $transaction_id, 'NetAmount', true);
$NetAmount = usb_swiper_price_formatter($NetAmount);
$ShippingAmount = get_post_meta( $transaction_id, 'ShippingAmount', true);
$ShippingAmount = usb_swiper_price_formatter($ShippingAmount);
$HandlingAmount = get_post_meta( $transaction_id, 'HandlingAmount', true);
$HandlingAmount = usb_swiper_price_formatter($HandlingAmount);
$tax_rate = get_post_meta( $transaction_id, 'TaxRate', true);
$tax_rate_label = '';
if( !empty( $tax_rate ) ){
	$tax_rate_label = __("&nbsp;<span style='font-size: 11px;font-weight: bold;'>($tax_rate%)</span>", 'usb-swiper');
}
$TaxAmount = get_post_meta( $transaction_id, 'TaxAmount', true);
$TaxAmount = usb_swiper_price_formatter($TaxAmount);
$GrandTotal = get_post_meta( $transaction_id, 'GrandTotal', true);
$GrandTotal = usb_swiper_price_formatter($GrandTotal);
$ItemName = get_post_meta( $transaction_id, 'ItemName', true);
$Notes = get_post_meta( $transaction_id, 'Notes', true);
$InvoiceID = get_post_meta( $transaction_id, 'InvoiceID', true);
$transaction_debug_id = get_post_meta( $transaction_id, '_paypal_transaction_debug_id', true);
$transaction_issue = get_post_meta( $transaction_id, '_payment_failed_response', true);
$status_note = get_post_meta( $transaction_id, '_payment_status_notes', true);
$payment_response = get_post_meta( $transaction_id, '_payment_response', true);
$payment_source = !empty( $payment_response['payment_source'] ) ? $payment_response['payment_source'] : '';
$transaction_type = get_post_meta( $transaction_id, '_transaction_type', true);
$payment_card_number = !empty( $payment_source['card']['last_digits'] ) ? $payment_source['card']['last_digits'] : '';
$payment_card_brand = !empty( $payment_source['card']['brand'] ) ? $payment_source['card']['brand'] : '';
$payment_card_type = !empty( $payment_source['card']['type'] ) ? $payment_source['card']['type'] : '';
$BillingEmail = get_post_meta( $transaction_id, 'BillingEmail', true);

$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
$payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
$payment_refunds = !empty( $payment_details['refunds'] ) ? $payment_details['refunds'] : '';

$payment_intent_id = usbswiper_get_intent_id($transaction_id);
$payment_transaction_id = usbswiper_get_transaction_id($transaction_id);
$payment_action = usbswiper_get_transaction_type($transaction_id);
$payment_create_time = usbswiper_get_transaction_datetime($transaction_id);
$payment_update_time = usbswiper_get_transaction_datetime($transaction_id, 'update_time');
$payment_processor_response = get_post_meta($transaction_id, '_processor_response', true);
$payment_processor_response = !empty( $payment_processor_response ) ? $payment_processor_response : [];

$processor_response = !empty( $payment_details['captures']['0']['processor_response'] ) ? $payment_details['captures']['0']['processor_response'] : [];

if( !empty($processor_response['cvv_code']) && empty($payment_processor_response['cvv_code']) ){
    $payment_processor_response['cvv_code'] = $processor_response['cvv_code'];
}

if( !empty($processor_response['avs_code']) && empty($payment_processor_response['avs_code']) ){
    $payment_processor_response['avs_code'] = $processor_response['avs_code'];
}

if( empty($processor_response['response_description']) && !empty($payment_processor_response['response_description']) ){
    $processor_response['response_description'] = $payment_processor_response['response_description'];
}

if( empty($processor_response['response_code']) && !empty($payment_processor_response['response_code']) ){
    $processor_response['response_code'] = $payment_processor_response['response_code'];
}

if( !class_exists('Usb_Swiper_Paypal_request') ) {
    include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
}

$Usb_Swiper_Paypal_request = new Usb_Swiper_Paypal_request();
$transaction_currency = $Usb_Swiper_Paypal_request->get_transaction_currency( $transaction_id);
$vt_products = get_post_meta( $transaction_id, 'vt_products', true );

?>
<div class="vt-form-notification"></div>

<div class="vt-transaction-history woocommerce-page" style="width: 100%;">
    <?php
    $myaccount_page_id = (int)get_option('woocommerce_myaccount_page_id');
    $payment_action = usbswiper_get_transaction_type($transaction_id);
    $payment_response = get_post_meta( $transaction_id, '_payment_response', true);
    $author_id = get_post_field( 'post_author', $transaction_id );
    $author_id = ! empty( $author_id ) ? $author_id : 1;
    $get_current_user_id = get_current_user_id();

    if( !empty( $myaccount_page_id ) && $myaccount_page_id === get_the_ID() ) {
        $get_refund_status = usbswiper_get_refund_status();

        $refund_amount = get_total_refund_amount($transaction_id);
        $transaction_total = usbswiper_get_zettle_transaction_total( $transaction_id );
        ?>
        <div class="send-email-btn-wrapper hide-me-in-print" style="text-align: right;">
            <?php if( !empty( $payment_status ) && in_array( $payment_status, $get_refund_status) && !$is_email ) { ?>
                <button id="send_email_btn_<?php echo $transaction_id; ?>" data-transaction_id="<?php echo $transaction_id; ?>" class="vt-button send-email-btn hide-me-in-print"><?php _e('Send Email Receipt','usb-swiper'); ?></button>
            <?php } ?>

            <?php if( !$is_email && !is_admin()) { ?>
                <label><?php _e('Page Size: ', 'usb-swiper'); ?></label>
                <select id="page-size" name="page-size" class="woocommerce-Select">
                    <option value="a4"><?php _e('A4','usb-swiper'); ?></option>
                    <option value="envelope-3.5"><?php _e('Envelope 3.5','usb-swiper'); ?></option>
                </select>
            <?php } ?>

            <?php if( !$is_email && !is_admin()) { ?>
                <button class="vt-button print hide-me-in-print"  id="print_transaction_receipt"><?php _e('Print Receipt', 'usb-swiper') ?></button>
            <?php } ?>

            <?php if( !empty( $payment_status ) && in_array( $payment_status, $get_refund_status) && !$is_email ) { ?>
                <button data-id="<?php echo $transaction_id; ?>" class="vt-button transaction-refund"><?php _e('Refund','usb-swiper'); ?></button>
            <?php } ?>

            <?php
            if( strtolower( $transaction_type) !== 'zettle' && usbswiper_is_allow_capture( $transaction_id ) && $payment_status !== 'FAILED' && is_wc_endpoint_url('view-transaction') && $get_current_user_id === (int)$author_id ) {
	            $unique_id = usb_swiper_unique_id( array(
		            'type' => $payment_action,
		            'transaction_id' => $transaction_id,
		            'paypal_transaction_id' => !empty( $payment_response['id'] ) ? $payment_response['id'] : '',
		            'nonce' => wp_create_nonce('authorize-transaction-capture')
	            ));
	            $id = !empty( $id ) ? $id : $transaction_id;
	            ?>
                <a class="vt-button void-transaction-button" data-href="<?php echo add_query_arg( array( 'action' => 'void',  'unique_id' => $unique_id), esc_url( wc_get_endpoint_url( 'view-transaction', $id, wc_get_page_permalink( 'myaccount' ) ) )); ?>"><?php _e('VOID','usb-swiper'); ?></a>
                <a class="vt-button capture-transaction-button" data-href="<?php echo add_query_arg( array( 'action' => 'capture',  'unique_id' => $unique_id), esc_url( wc_get_endpoint_url( 'view-transaction', $id, wc_get_page_permalink( 'myaccount' ) ) )); ?>"><?php _e('CAPTURE','usb-swiper'); ?></a>
                <?php
            }
            ?>
        </div>

        <?php if( !empty( $payment_status ) && in_array( $payment_status, $get_refund_status) && !$is_email ) { ?>
            <div class="transaction-refund-wrap transaction-history-field hide-me-in-print">
                <div class="refund-form-wrap">
                    <form method="post" action="" name="vt_refund_form_data" id="vt_refund_form_data">
                        <div class="refund-field">
                            <label for="transaction_amount_display"><?php _e('Total Amount', 'usb-swiper'); ?></label>
                            <input type="text" readonly name="transaction_amount_display" id="transaction_amount_display" value="<?php echo usbswiper_get_price_format( $transaction_total ); ?>" />
                        </div>
                        <div class="refund-field">
                            <label for="remaining_amount_display"><?php _e('Remaining Amount', 'usb-swiper'); ?></label>
                            <input type="text" readonly class="remain-amount-input" name="remaining_amount_display" id="remaining_amount" value="<?php echo $refund_amount; ?>" />
                        </div>
                        <div class="refund-field refund-amount-field">
                            <label for="refund_amount_display"><?php _e('Refund Amount', 'usb-swiper'); ?></label>
                            <input type="number" min="0" class="remain-amount-input refund-amount-input" step="any" max="<?php echo $refund_amount; ?>" maxlength="<?php echo $refund_amount; ?>" name="refund_amount_display" id="refund_amount_display" value="<?php echo $refund_amount; ?>" />
                        </div>
                        <div class="refund-field refund-actions">
                            <button type="button" class="vt-button confirm-transaction-refund-notification" id="transaction_refund_btn_display" name="transaction_refund_btn_display"><?php _e('Refund','usb-swiper'); ?></button>
                            <button type="button" class="vt-button-normal cancel-refund"><?php _e('Cancel','usb-swiper'); ?></button>
                        </div>
                    </form>
                </div>
                <div class="vt-refund-popup-wrapper">
                    <div class="popup-loader"></div>
                    <div class="vt-refund-popup-inner">
                        <div class="close">
                            <a href="javascript:void(0);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></a>
                        </div>
                        <div class="vt-notification-content">
                            <div class="input-field-wrap ">
                                <p><?php _e('Are you sure you want to process this refund?','usb-swiper'); ?></p>
                            </div>
                            <div class="input-field-wrap button-wrap">
                                <div class="vt-form-notification"></div>
                                <form method="post" action="" name="vt_refund_form" id="vt_refund_form">
                                    <input type="hidden" readonly name="transaction_amount" id="transaction_amount" value="<?php echo $GrandTotal; ?>" />
                                    <input type="hidden" readonly class="remain-amount-input" name="remaining_amount" id="remaining_amount" value="<?php echo $refund_amount; ?>" />
                                    <input type="hidden" name="refund_amount" id="refund_amount" value="" />
                                    <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('refund-request'); ?>">
                                    <input type="hidden" name="transaction_id" id="transaction_id" value="<?php echo $transaction_id; ?>">
                                    <input type="hidden" name="transaction_type" id="transaction_type" value="<?php echo esc_attr( strtolower( $transaction_type) ); ?>">
                                    <button type="submit" class="vt-button confirm-transaction-refund" id="transaction_refund_btn" name="transaction_refund_btn"><?php _e('Refund','usb-swiper'); ?></button>
                                    <button type="button" class="vt-button-normal cancel-refund"><?php _e('Cancel','usb-swiper'); ?></button>
                                </form>
                                <div class="zettle-refund-response"><ul></ul></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }

	    if( strtolower( $transaction_type) !== 'zettle' && usbswiper_is_allow_capture( $transaction_id ) && $payment_status !== 'FAILED' && is_wc_endpoint_url('view-transaction') && $get_current_user_id === (int)$author_id ) {
		    echo refund_confirmation_html();
		    echo void_confirmation_html();
	    }
    }

    $vt_invoice_id = 'invoice' === strtolower( $transaction_type ) ? $user_invoice_id : $transaction_id;
    $heading_label_style =  'padding: 0 12px 12px 0;color:#000;font-weight: 700;border: 0;vertical-align: top;';
    $heading_th_style =  'padding: 12px 12px 12px 0;color:#000;font-weight: 400;vertical-align: top;';
    $heading_table_border_style = '0';
    if( is_admin() ) {
	    $heading_label_style =  'padding: 12px;color:#000;font-weight: 700;border: 0;vertical-align: top;border:1px solid #ebebeb;';
	    $heading_th_style =  'padding: 12px;color:#000;font-weight: 400;vertical-align: top;border:1px solid #ebebeb;';
	    $heading_table_border_style  =  '1px solid #ebebeb;';
    }
    ?>
    <table style="width: 100%;border-radius: 0;margin-bottom: 10px !important;border: <?php echo $heading_table_border_style; ?>;" cellspacing="0" cellpadding="0" width="100%" class="hide-me-in-print woocommerce-table woocommerce-table--order-details shop_table order_details">
        <tbody>
        <tr>
            <td class="transaction-table-product-td" style="<?php echo $heading_th_style; ?>"><?php _e('Receipt ID: ','usb-swiper'); ?></td>
            <td class="transaction-table-product-td" style="<?php echo $heading_th_style; ?>"><?php _e('Date: ','usb-swiper'); ?></td>
            <td class="transaction-table-product-td" style="<?php echo $heading_th_style; ?>"><?php _e('Status: ','usb-swiper'); ?></td>
            <td class="transaction-table-product-td" style="<?php echo $heading_th_style; ?>"><?php _e('Payment Method: ','usb-swiper'); ?></td>
        </tr>
        <tr>
            <?php if( $is_email && $is_admin ) { ?>
                <td class="transaction-table-product-td" style="<?php echo $heading_label_style; ?>"><a style='text-decoration: none;' href="<?php echo esc_url( wc_get_endpoint_url( 'view-transaction', $vt_invoice_id, wc_get_page_permalink( 'myaccount' ) ) ); ?>"><?php echo $vt_invoice_id; ?></a></td>
            <?php } else { ?>
                <td class="transaction-table-product-td" style="<?php echo $heading_label_style; ?>"><?php echo $vt_invoice_id; ?></td>
            <?php } ?>
            <td class="transaction-table-product-td" style="<?php echo $heading_label_style; ?>"><?php echo get_the_date('Y-m-d',$transaction_id); ?></td>
            <td class="transaction-table-product-td payment-status-text" style="<?php echo $heading_label_style; ?>"><?php echo usbswiper_get_payment_status($payment_status); ?></td>
            <td class="transaction-table-product-td" style="<?php echo $heading_label_style; ?>"><?php echo !empty( $payment_source_type ) ? strtoupper($payment_source_type) : $credit_card_number; ?></td>
        </tr>
        </tbody>
    </table>
    <?php
    if( !empty( $transaction_type ) && strtolower($transaction_type) === 'invoice' && !empty( $payment_status ) && strtolower($payment_status) === 'pending' && ( empty( $_GET['action'] ) || $_GET['action'] !== 'edit' ) && $is_email ){
        $payment_link = !empty($args['payment_link']) ? esc_url($args['payment_link']) : '';
        $display_name = !empty( $args['display_name'] ) ? esc_html($args['display_name']) : '';
        ?>
        <div style="margin: 10px 0;padding: 0;width: 100%;display: block;float: left;color:#000;">
            <?php if( !empty( $payment_link ) ){
                $button_background = get_button_background_color($BillingEmail,$is_email); ?>
                <p style="text-align: center;color:#000;"><a style="display: inline-block;color: #ffffff;border-width: 0;border-radius: 26px;letter-spacing: 1px;font-size: 13px;font-weight: 800;text-transform: uppercase;background:<?php echo $button_background; ?>;padding:15px 30px;text-decoration: none;margin-bottom: 10px;cursor: pointer;" href="<?php echo $payment_link; ?>"><?php echo __('Click to Pay', 'usb-swiper'); ?></a></p>
            <?php } else { ?>
                <p style="color:#000;"><?php echo sprintf(__('Hello %s','usb-swiper'), $display_name); ?></p>
                <p style="color:#000;"><?php echo sprintf(__('Thanks for create invoice in %s.','usb-swiper'), get_option('blogname')); ?></p>
            <?php } ?>
        </div>
        <?php
    }
    ?>
    <div class="customer-details transaction-history-field" style="float: left;width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
        <?php
        $addresses = get_transaction_address_format($transaction_id);
        $address_style = isset($is_email) ? 'margin: 0;font-size: 14px;padding:0;font-style: normal;' : 'padding:0;margin:0';

        $billingInfo = get_post_meta( $transaction_id, 'billingInfo', true);
        $shippingDisabled = get_post_meta( $transaction_id, 'shippingDisabled', true);
        $shippingSameAsBilling = get_post_meta( $transaction_id, 'shippingSameAsBilling', true);

        $BillingPhoneNumber = get_post_meta( $transaction_id, 'BillingPhoneNumber', true);
        $ShippingPhoneNumber = get_post_meta( $transaction_id, 'ShippingPhoneNumber', true);
        $ShippingEmail = get_post_meta( $transaction_id, 'ShippingEmail', true);
        ?>

        <?php if( !$is_email ) { ?>
            <div class="Print-Receipt-phone-number printArea" style="display:none;text-align: center;">
                <?php
                $serialized_data = get_option('et_divi');
                $phone_number = maybe_unserialize($serialized_data);

                if (isset($phone_number['phone_number'])) {
                    echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" margin-right="2"><path d="M21.384,17.752a2.108,2.108,0,0,1-.522,3.359,7.543,7.543,0,0,1-5.476.642C10.5,20.523,3.477,13.5,2.247,8.614a7.543,7.543,0,0,1,.642-5.476,2.108,2.108,0,0,1,3.359-.522L8.333,4.7a2.094,2.094,0,0,1,.445,2.328A3.877,3.877,0,0,1,8,8.2c-2.384,2.384,5.417,10.185,7.8,7.8a3.877,3.877,0,0,1,1.173-.781,2.092,2.092,0,0,1,2.328.445Z"/></svg>' . $phone_number['phone_number'];
                } ?>
            </div>
        <?php } ?>

	    <?php if( !$is_email ) { ?>
            <div class="Print-Receipt-logo printArea" style="display:none;text-align: center;">
                <?php $brand_logo = usbswiper_get_brand_logo(get_current_user_id(), false,[250,50]);

                $attachment_id = $brand_logo['attachment_id'];
                if ($brand_logo['image_html']) {
                    echo $brand_logo['image_html'];
                }?>
            </div>
        <?php } ?>

        <?php if( !$is_email ) { ?>
            <!-- Receipt Information-->
            <div style="width: 100%;vertical-align: top;margin-left: 10px;" class="form-heading-detail form-detail show-me-only-in-print">
                <ul style="margin: 10px 0;padding: 0;width: 100%;display: block;" class="receipt-header-ul">
                    <li class="transaction_history_receipt_info">
                        <strong>
                            <?php _e('Receipt ID : ','usb-swiper'); ?>
                        </strong>
                        <?php echo $transaction_id; ?>
                    </li>
                    <li class="transaction_history_receipt_info">
                        <strong>
                            <?php _e('Date : ','usb-swiper'); ?>
                        </strong>
                        <?php echo get_the_date('Y-m-d',$transaction_id); ?>
                    </li>
                    <li class="transaction_history_receipt_info">
                        <strong>
                            <?php _e('Status : ','usb-swiper'); ?>
                        </strong>
                        <?php echo usbswiper_get_payment_status($payment_status); ?>
                    </li>
                    <li class="transaction_history_receipt_info">
                        <strong>
                            <?php _e('Card Detail : ','usb-swiper'); ?>
                        </strong><?php echo $credit_card_number; ?>
                    </li>
                </ul>
            </div>
        <?php }  ?>
        <div class="print printArea" style="clear:both;">
            <!-- Billing Address-->
            <div style="float: left;display: inline-block;vertical-align: top;margin-right: 10px;width: calc( 50% - 10px ); <?php echo ('true' !== $billingInfo ) ? 'display:none': ''; ?>" class="transaction-column billing-details form-detail <?php echo ( 'true' === $shippingDisabled ) ? 'no-shipping-address' :''; ?>">
                <h2 class="transaction-details__title transaction-history-title"><?php _e('Billing Address','usb-swiper'); ?></h2>
                <address class="address-wrap" style="padding: 10px;border: 2px solid #ebebeb;min-height: <?php echo ( is_admin() ) ? '150px;' : '200px;'; ?>">
                    <p style="margin: 0;font-size: 14px;padding:0;font-style: normal;"><strong><?php echo $company_name ;?></strong></p>
			        <?php
			        // Splitting the Address Values
			        echo !empty( $addresses['billing_address'] ) ? $addresses['billing_address'] : '';
			        if( !empty( $BillingPhoneNumber ) ) { ?>
                        <p style="<?php echo $address_style; ?>" class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>
				        <?php
			        }
			        if( !empty( $BillingEmail ) ) { ?>
                        <p style="<?php echo $address_style; ?>" class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
			        <?php } ?>
                </address>
            </div>
            <!-- Personal Info -->
            <?php
            $billingInfoEnabled = 'true' === $billingInfo;
            ?>
            <div style="float: left; display: inline-block; vertical-align: top; margin-right: 10px; width: calc(50% - 10px); <?php echo $billingInfoEnabled ? 'display:none;' : ''; ?>" class="transaction-column billing-details form-detail <?php echo ('true' === $shippingDisabled) ? 'no-shipping-address' : ''; ?>">
                <h2 class="transaction-details__title transaction-history-title"><?php _e('Personal Info', 'usb-swiper'); ?></h2>
                <address class="address-wrap" style="padding: 10px; border: 2px solid #ebebeb; min-height: <?php echo (is_admin()) ? '150px;' : '200px;'; ?>">
                    <p style="margin: 0;font-size: 14px;padding:0;font-style: normal;"><strong><?php echo $company_name ;?></strong></p>
                    <?php
                    echo !empty( $addresses['billing_address'] ) ? $addresses['billing_address'] : '';
                    if( !empty( $BillingEmail ) ) { ?>
                        <p style="<?php echo $address_style; ?>" class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
                    <?php } ?>
                </address>
            </div>
            <!--Shipping Address-->
            <div style="float: left;display: inline-block;vertical-align: top;margin-left: 10px;width: calc( 50% - 10px );<?php echo ('true' !== $shippingDisabled ) ? 'display:none': ''; ?>" class="shipping-details form-detail  transaction-column">
                <h2 class="transaction-details__title transaction-history-title" ><?php _e('Shipping Address','usb-swiper'); ?></h2>
                <address class="address-wrap" style="padding: 10px;border: 2px solid #ebebeb;min-height: <?php echo ( is_admin() ) ? '150px;' : '200px;'; ?>">
			        <?php if( 'true' === $shippingDisabled ) {
				        if( 'true' !== $shippingSameAsBilling ) { ?>
                            <!-- Splitting the Address Values-->
					        <?php  echo !empty( $addresses['shipping_address'] ) ? $addresses['shipping_address'] : '';?>
                            <p style="<?php echo $address_style; ?>" class="woocommerce-customer-details--phone"><?php echo esc_attr($ShippingPhoneNumber); ?></p>
                            <p style="<?php echo $address_style; ?>" class="woocommerce-customer-details--email"><?php echo esc_attr($ShippingEmail); ?></p>
				        <?php } else {
					        echo !empty( $addresses['shipping_address'] ) ? $addresses['shipping_address'] : '';
					        if(!empty($BillingPhoneNumber)){ ?>
                                <p style="<?php echo $address_style; ?>" class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>
					        <?php }
					        if(!empty($BillingEmail)){ ?>
                                <p style="<?php echo $address_style; ?>" class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
						        <?php
					        }
				        }
			        } ?>
                </address>
            </div>
        </div>
    </div>
    <div class="transaction-details transaction-history-field" style="float: left;width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
        <h2 class="transaction-details__title transaction-history-title"><?php _e('Product Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;margin-bottom: 10px !important;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
            <tr>
                <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Product Name','usb-swiper'); ?></td>
                <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Quantity','usb-swiper'); ?></td>
                <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Price','usb-swiper'); ?></td>
            </tr>
            <?php
            if( ! empty( $vt_products ) && is_array( $vt_products ) ) {
                foreach ( $vt_products as $vt_product ) {
	                $product_id = !empty( $vt_product['product_id'] ) ? $vt_product['product_id'] : 0;
	                $is_taxable = get_post_meta( $product_id, 'is_product_taxable', true );
	                $is_taxable_label = '';
                    if( !empty( $is_taxable ) ) {
	                    $is_taxable_label = __('&nbsp;<span style="font-size: 11px;font-weight: bold;">(Taxable)</span>', 'usb-swiper');
                    }
                    ?>
                    <tr>
                        <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $vt_product['product_name'] ) ? $vt_product['product_name'].$is_taxable_label : '-'; ?></td>
                        <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $vt_product['product_quantity'] ) ? $vt_product['product_quantity'] : '-'; ?></td>
                        <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $vt_product['product_price'] ) ? wc_price(usb_swiper_clean_price_string($vt_product['product_price']), array('currency' => $transaction_currency)) : 0; ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <h2 class="transaction-details__title transaction-history-title"><?php _e('Order Totals','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;margin-bottom: 10px !important;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Order Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($OrderAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Discount Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($DiscountAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Net Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($NetAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Shipping Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header"  style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($ShippingAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Handling Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($HandlingAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo sprintf( __('Tax Amount %s','usb-swiper' ), $tax_rate_label ); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($TaxAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <?php

            if( !empty( $transaction_type ) && strtolower( $transaction_type) === 'zettle') {

	            $tip_amount = usbswiper_get_zettle_transaction_tip_amount( $transaction_id );

                if( !empty( $tip_amount ) && $tip_amount > 0 ) {

                    ?>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Sub Total','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($GrandTotal, array('currency' => $transaction_currency)); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Tip Amount','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($tip_amount, array('currency' => $transaction_currency)); ?></td>
                    </tr>
                    <?php

	                $GrandTotal = usbswiper_get_zettle_transaction_total( $transaction_id );
                }

            } ?>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Grand Total','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($GrandTotal, array('currency' => $transaction_currency)); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php
    if( !empty( $transaction_type ) && strtolower($transaction_type) === 'zettle' ) {

        $result_payload = !empty( $payment_response['result_payload'] )  ? $payment_response['result_payload'] : [];
	    $result_payload = !empty( $payment_response['resultPayload'] ) ? $payment_response['resultPayload'] : $result_payload;

        $reference_number = !empty( $result_payload->REFERENCE_NUMBER ) ? $result_payload->REFERENCE_NUMBER : '';
        $application_identifier = !empty( $result_payload->APPLICATION_IDENTIFIER ) ? $result_payload->APPLICATION_IDENTIFIER : '';
	    $card_payment_uuid = !empty( $result_payload->CARD_PAYMENT_UUID ) ? $result_payload->CARD_PAYMENT_UUID : '';
        $card_payment_entry_mode = !empty( $result_payload->CARD_PAYMENT_ENTRY_MODE ) ? $result_payload->CARD_PAYMENT_ENTRY_MODE : '';
        $tracking_id = !empty( $result_payload->REFERENCES->trackingId ) ? $result_payload->REFERENCES->trackingId : '';
        $checkout_uuid = !empty( $result_payload->REFERENCES->checkoutUUID ) ? $result_payload->REFERENCES->checkoutUUID : '';
        $card_holder_verification_method = !empty( $result_payload->CARDHOLDER_VERIFICATION_METHOD ) ? $result_payload->CARDHOLDER_VERIFICATION_METHOD : '';
        $application_name = !empty( $result_payload->APPLICATION_NAME ) ? $result_payload->APPLICATION_NAME : '';
        $authorization_code = !empty( $result_payload->AUTHORIZATION_CODE ) ? $result_payload->AUTHORIZATION_CODE : '';
        $card_type = !empty( $result_payload->CARD_TYPE ) ? $result_payload->CARD_TYPE : '';
        $card_hase = !empty( $result_payload->CARD_HASH ) ? $result_payload->CARD_HASH : '';
        ?>
        <div class="payment-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;float: left;">
            <h2 class="transaction-details__title transaction-history-title" ><?php _e('Transaction Details','usb-swiper'); ?></h2>
            <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;margin-bottom: 10px !important;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
                <tbody>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Reference Number','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $reference_number ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Application Identifier','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $application_identifier ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Card Payment UUID','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $card_payment_uuid ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Card Payment Entry Mode','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $card_payment_entry_mode ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Tracking ID','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $tracking_id ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Checkout UUID','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $checkout_uuid ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Card Holder Verification Method','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $card_holder_verification_method ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Application Name','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $application_name ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Authorization Code','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $authorization_code ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Card Type','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $card_type ); ?></td>
                    </tr>
                    <tr>
                        <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Card Hase','usb-swiper'); ?></th>
                        <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo esc_html( $card_hase ); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    } elseif( empty( $transaction_type ) || empty( $payment_status ) || ( strtolower($transaction_type) === 'transaction' ) || ( strtolower($transaction_type) === 'invoice' && strtolower($payment_status) !== 'pending' ) ){ ?>
        <div class="payment-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;float: left;">
        <h2 class="transaction-details__title transaction-history-title" ><?php _e('Transaction Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;margin-bottom: 10px !important" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Payment Intent ID','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_intent_id ) ? $payment_intent_id : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Payment Intent','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_action ) ? $payment_action : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('PayPal Transaction ID','usb-swiper'); ?></th>
                <?php
                if( ( is_wc_endpoint_url('view-transaction') && $get_current_user_id === (int)$author_id && !$is_email ) || ( !empty($_GET['action']) && sanitize_text_field($_GET['action']) === 'edit' ) || ( $is_email && $is_admin ) ){ ?>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><a style='text-decoration: none;' href="<?php echo get_paypal_transaction_url($payment_transaction_id); ?>" target="_blank"><?php echo !empty( $payment_transaction_id ) ? $payment_transaction_id : ''; ?></a></td>
                <?php } else { ?>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_transaction_id ) ? $payment_transaction_id : ''; ?></td>
                <?php } ?>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Payment Status','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_status ) ? usbswiper_get_payment_status($payment_status) : ''; ?></td>
            </tr>
            <?php if( !empty( $InvoiceID ) ) { ?>
                <tr>
                    <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Invoice ID','usb-swiper'); ?></th>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo $InvoiceID; ?></td>
                </tr>
            <?php } ?>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Payment Source','usb-swiper'); ?></th>
                <?php if( !empty( $payment_card_number ) ) {  ?>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo sprintf( '%s (%s) - %s', $payment_card_number, $payment_card_brand, $payment_card_type); ?></td>
                <?php } else { ?>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_source_type ) ? strtoupper($payment_source_type) : $credit_card_number; ?></td>
                <?php } ?>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Payment Created At','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_create_time ) ? date('Y/m/d g:i a', strtotime($payment_create_time)) : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Payment Updated At','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_update_time ) ? date('Y/m/d g:i a', strtotime($payment_update_time)) : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('AVS Code','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_processor_response['avs_code'] ) ? strtoupper( $payment_processor_response['avs_code'] ) : 'N/A'; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('CVV2 Code','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $payment_processor_response['cvv_code'] ) ? strtoupper( $payment_processor_response['cvv_code'] ) : 'N/A'; ?></td>
            </tr>

            <?php if( !empty( $payment_status ) && in_array( strtolower( $payment_status ), [ 'failed', 'declined' ] ) ) { ?>
                <tr>
                    <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Processor Response Code','usb-swiper'); ?></th>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $processor_response['response_code'] ) ? $processor_response['response_code'] : ''; ?></td>
                </tr>
            <?php } ?>
            <?php if( !empty( $payment_status ) && in_array( strtolower( $payment_status ), [ 'failed', 'declined' ] ) ) { ?>
                <tr>
                    <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Processor Response Message','usb-swiper'); ?></th>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $processor_response['response_description'] ) ? $processor_response['response_description'] : ''; ?></td>
                </tr>
            <?php } ?>

            <?php if( !empty( $transaction_debug_id ) ) { ?>
                <tr>
                    <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('PayPal Debug ID','usb-swiper'); ?></th>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo $transaction_debug_id; ?></td>
                </tr>
            <?php } ?>

            <?php if( !empty( $transaction_issue ) ) {
                
                $issue_message = !empty( $transaction_issue['message'] ) ? $transaction_issue['message'] : '';
                $issue_details = !empty( $transaction_issue['details'][0] ) ? (array) $transaction_issue['details'][0] : [];
                $issue_type = !empty( $issue_details['issue'] ) ? $issue_details['issue'] : '';
                $issue_description = !empty( $issue_details['description'] ) ? $issue_details['description'] : '';

                $transaction_issue_message = sprintf('<p><strong>%s</strong> %s<p><p>%s</p>',$issue_type, $issue_message, $issue_description);

                ?>
                <tr>
                    <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Error Message','usb-swiper'); ?></th>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo $transaction_issue_message; ?></td>
                </tr>
            <?php } ?>

            </tbody>
        </table>
    </div>
    <?php }
    if( !empty( $Notes ) ) { ?>
        <div class="custom-notes transaction-history-field" style="float: left;display: block;padding: 10px;border: 1px solid rgba(0,0,0,.1);margin: 10px 0;width: calc( 100% - 20px);">
            <p style="margin: 0;"> <?php echo sprintf(__('<strong>Notes</strong>: %s','usb-swiper'), esc_html($Notes));?></p>
        </div>
    <?php }
    if( strtolower($transaction_type) === 'invoice' && !$is_email ){
        require_once USBSWIPER_PLUGIN_DIR.'/library/usb-swiper-invoice-pdf.php';

        $Usb_Swiper_Invoice_PDF = new Usb_Swiper_Invoice_PDF();
        $get_attachment_url = $Usb_Swiper_Invoice_PDF->generate_invoice($transaction_id);
        if( !empty($get_attachment_url['invoice_url']) ){ ?>
            <a href="<?php echo esc_url( $get_attachment_url['invoice_url'] ); ?>" class="vt-button" download><?php _e('Download invoice','usb-swiper'); ?></a>
        <?php }
    }
    if( !empty( $status_note ) && ( $payment_status === 'FAILED' || $payment_status === 'PENDING' ) ) { ?>
        <div class="custom-payment-notes transaction-history-field" style="float: left;display: block;padding: 10px;border: 1px solid rgba(0,0,0,.1);margin: 10px 0;width: calc( 100% - 20px);">
            <p style="margin: 0;"> <?php echo sprintf(__('<strong>Payment Notes</strong>: %s','usb-swiper'), $status_note);?></p>
        </div>
        <style type="text/css">.custom-payment-notes p span{ margin-left:5px; }</style>
    <?php } ?>

    <div class="refund-details transaction-history-field" style="width: 100%;float: left;display: block;margin: 0 0 10px 0;padding: 0;">
        <?php if( !empty( $payment_refunds ) && is_array($payment_refunds) && !empty( $transaction_type ) && strtolower($transaction_type) !== 'zettle') {
            if( !class_exists('Usb_Swiper_Paypal_request') ) {
                include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
            }
            $Paypal_request = new Usb_Swiper_Paypal_request();
            echo $Paypal_request->get_refund_html($transaction_id);
        } elseif ( !empty( $transaction_type ) && strtolower($transaction_type) === 'zettle' ) {

            echo UsbSwiperZettle::get_refund_html( $transaction_id );
        } ?>
    </div>

    <?php if( !$is_email ) { ?>
        <div class="custom-payment-notes signature" style="clear:both;">
            <?php if( !is_admin()) {?>
            <br/>
            <br/>
            <p class="signature-text"><?php _e('Signature........................................','ubs-swiper') ; ?></p>
            <?php } ?>
        </div>
    <?php } ?>
</div>
