<?php
if( empty($transaction_id)) {
    return;
}

$is_email = !empty( $args['is_email'] );
$is_admin = !empty( $args['is_admin'] );

$card_last_digits = get_post_meta( $transaction_id, '_payment_card_last_digits', true);
$card_brand = get_post_meta( $transaction_id, '_payment_card_brand', true);
$credit_card_number = '';
$global_payment_status = get_post_meta( $transaction_id, '_payment_status', true);
$payment_status = usbswiper_get_transaction_status($transaction_id);
if( ! empty( $card_last_digits )) {
    $credit_card_number = $card_last_digits.' ('.$card_brand.')';
} else {
    if( strtolower($global_payment_status) !== 'pending' ) {
        $credit_card_number = __('PayPal', 'usb-swiper');
    }
}
$company_name = get_post_meta($transaction_id,'company',true);
$user_invoice_id = get_post_meta( $transaction_id, '_user_invoice_id', true);
$NetAmount = get_post_meta( $transaction_id, 'NetAmount', true);
$NetAmount = usb_swiper_price_formatter($NetAmount);
$ShippingAmount = get_post_meta( $transaction_id, 'ShippingAmount', true);
$ShippingAmount = usb_swiper_price_formatter($ShippingAmount);
$HandlingAmount = get_post_meta( $transaction_id, 'HandlingAmount', true);
$HandlingAmount = usb_swiper_price_formatter($HandlingAmount);
$TaxAmount = get_post_meta( $transaction_id, 'TaxAmount', true);
$TaxAmount = usb_swiper_price_formatter($TaxAmount);
$GrandTotal = get_post_meta( $transaction_id, 'GrandTotal', true);
$GrandTotal = usb_swiper_price_formatter($GrandTotal);
$ItemName = get_post_meta( $transaction_id, 'ItemName', true);
$Notes = get_post_meta( $transaction_id, 'Notes', true);
$InvoiceID = get_post_meta( $transaction_id, 'InvoiceID', true);
$transaction_debug_id = get_post_meta( $transaction_id, '_paypal_transaction_debug_id', true);
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
if( strtolower($global_payment_status) === 'failed' || ( !empty( $transaction_type ) && strtolower($transaction_type) === 'invoice' && $payment_status !== 'PARTIALLY_REFUNDED' && $payment_status !== 'CREATED' && $payment_status !== 'REFUNDED') || empty($payment_status) ) {
    $payment_status = $global_payment_status;
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
    if( usbswiper_is_allow_capture( $transaction_id ) && $global_payment_status !== 'FAILED' && is_wc_endpoint_url('view-transaction') && $get_current_user_id === (int)$author_id ) {
        $unique_id = usb_swiper_unique_id( array(
           'type' => $payment_action,
           'transaction_id' => $transaction_id,
           'paypal_transaction_id' => $payment_response['id'],
           'nonce' => wp_create_nonce('authorize-transaction-capture')
        )); ?>
        <div class="transaction-refund-wrap transaction-history-field">
            <a class="vt-button capture-transaction" href="<?php echo add_query_arg( array( 'action' => 'capture',  'unique_id' => $unique_id), esc_url( wc_get_endpoint_url( 'view-transaction', $id, wc_get_page_permalink( 'myaccount' ) ) )); ?>"><?php _e('CAPTURE','usb-swiper'); ?></a>
        </div>
    <?php }
    if( !empty( $myaccount_page_id ) && $myaccount_page_id === get_the_ID() ) {
        $get_refund_status = usbswiper_get_refund_status();
        if( !empty( $payment_status ) && in_array( $payment_status, $get_refund_status)) {

            $refund_amount = get_total_refund_amount($transaction_id);
            ?>
            <div class="transaction-refund-wrap transaction-history-field">
                <button data-id="<?php echo $transaction_id; ?>" class="vt-button transaction-refund"><?php _e('Refund','usb-swiper'); ?></button>
                <div class="refund-form-wrap">
                    <form method="post" action="" name="vt_refund_form" id="vt_refund_form">
                        <div class="refund-field">
                            <label for="transaction_amount"><?php _e('Total Amount', 'usb-swiper'); ?></label>
                            <input type="text" readonly name="transaction_amount" id="transaction_amount" value="<?php echo $GrandTotal; ?>" />
                        </div>
                        <div class="refund-field">
                            <label for="remaining_amount"><?php _e('Remaining Amount', 'usb-swiper'); ?></label>
                            <input type="text" readonly name="remaining_amount" id="remaining_amount" value="<?php echo $refund_amount; ?>" />
                        </div>
                        <div class="refund-field">
                            <label for="refund_amount"><?php _e('Refund Amount', 'usb-swiper'); ?></label>
                            <input type="number" min="0" step="any" max="<?php echo $refund_amount; ?>" maxlength="<?php echo $refund_amount; ?>" name="refund_amount" id="refund_amount" value="<?php echo $refund_amount; ?>" />
                        </div>
                        <div class="refund-field refund-actions">
                            <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('refund-request'); ?>">
                            <input type="hidden" name="transaction_id" id="transaction_id" value="<?php echo $transaction_id; ?>">
                            <button type="submit" class="vt-button confirm-transaction-refund" id="transaction_refund_btn" name="transaction_refund_btn"><?php _e('Refund','usb-swiper'); ?></button>
                            <button type="button" class="vt-button-normal cancel-refund"><?php _e('Cancel','usb-swiper'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <?php
        }
    }

    $vt_invoice_id = 'invoice' === strtolower( $transaction_type ) ? $user_invoice_id : $transaction_id;
    $invoice_id_text = $vt_invoice_id;
    if( $is_email ){
        $invoice_id_text = "<a style='text-decoration: none;' href='".esc_url( wc_get_endpoint_url( 'view-transaction', $vt_invoice_id, wc_get_page_permalink( 'myaccount' ) ) )."'>".$vt_invoice_id."</a>";
    }

    ?>
    <table style="width: 100%;border-radius: 0;margin-bottom: 20px;border: 0;" cellspacing="0" cellpadding="0" width="100%" class="hide-me-in-print woocommerce-table woocommerce-table--order-details shop_table order_details">
        <tbody>
        <tr>
            <td class="transaction-table-product-td" style="padding: 12px 12px 12px 0;color:#000;font-weight: 400;vertical-align: top;"><?php _e('Receipt ID: ','usb-swiper'); ?></td>
            <td class="transaction-table-product-td" style="padding: 12px;color:#000;font-weight: 400;vertical-align: top;"><?php _e('Date: ','usb-swiper'); ?></td>
            <td class="transaction-table-product-td" style="padding: 12px;color:#000;font-weight: 400;vertical-align: top;"><?php _e('Status: ','usb-swiper'); ?></td>
            <td class="transaction-table-product-td" style="padding: 12px;color:#000;font-weight: 400;vertical-align: top;"><?php _e('Payment Method: ','usb-swiper'); ?></td>
        </tr>
        <tr>
            <td class="transaction-table-product-td" style="padding: 0 12px 12px 0;color:#000;font-weight: 700;border: 0;vertical-align: top;"><?php echo $invoice_id_text; ?></td>
            <td class="transaction-table-product-td" style="padding: 0 12px 12px;color:#000;font-weight: 700;border: 0;vertical-align: top;"><?php echo get_the_date('Y-m-d',$transaction_id); ?></td>
            <td class="transaction-table-product-td payment-status-text" style="padding: 0 12px 12px;color:#000;font-weight: 700;border: 0;vertical-align: top;"><?php echo usbswiper_get_payment_status($payment_status); ?></td>
            <td class="transaction-table-product-td" style="padding: 0 12px 12px;color:#000;font-weight: 700;border: 0;vertical-align: top;"><?php echo $credit_card_number; ?></td>
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
        $billingInfo = get_post_meta( $transaction_id, 'billingInfo', true);
        $shippingDisabled = get_post_meta( $transaction_id, 'shippingDisabled', true);
        $shippingSameAsBilling = get_post_meta( $transaction_id, 'shippingSameAsBilling', true);

        $billing_address = array();

        $BillingFirstName = get_post_meta( $transaction_id, 'BillingFirstName', true);
        if( !empty($BillingFirstName)) { $billing_address['first_name'] = $BillingFirstName; }

        $BillingLastName = get_post_meta( $transaction_id, 'BillingLastName', true);
        if( !empty($BillingLastName)) { $billing_address['last_name'] = $BillingLastName; }

        $BillingStreet = get_post_meta( $transaction_id, 'BillingStreet', true);
        if( !empty($BillingStreet)) { $billing_address['street1'] = $BillingStreet; }

        $BillingStreet2 = get_post_meta( $transaction_id, 'BillingStreet2', true);
        if( !empty($BillingStreet2)) { $billing_address['street2'] = $BillingStreet2; }

        $BillingCity = get_post_meta( $transaction_id, 'BillingCity', true);
        if( !empty($BillingCity)) { $billing_address['city'] = $BillingCity; }

        $BillingState = get_post_meta( $transaction_id, 'BillingState', true);
        if( !empty($BillingState)) { $billing_address['state'] = $BillingState; }

        $BillingPostalCode = get_post_meta( $transaction_id, 'BillingPostalCode', true);
        if( !empty($BillingPostalCode)) { $billing_address['pincode'] = $BillingPostalCode; }

        $BillingCountryCode = get_post_meta( $transaction_id, 'BillingCountryCode', true);
        if( !empty($BillingCountryCode)) { $billing_address['country'] = $BillingCountryCode; }

        $BillingPhoneNumber = get_post_meta( $transaction_id, 'BillingPhoneNumber', true);

        $shipping_address = array();

        $ShippingFirstName = get_post_meta( $transaction_id, 'ShippingFirstName', true);
        if( !empty( $ShippingFirstName ) ) { $shipping_address['first_name'] = $ShippingFirstName; }

        $ShippingLastName = get_post_meta( $transaction_id, 'ShippingLastName', true);
        if( !empty( $ShippingLastName ) ) { $shipping_address['last_name'] = $ShippingLastName; }

        $ShippingStreet = get_post_meta( $transaction_id, 'ShippingStreet', true);
        if( !empty( $ShippingStreet ) ) { $shipping_address['street1'] = $ShippingStreet; }

        $ShippingStreet2 = get_post_meta( $transaction_id, 'ShippingStreet2', true);
        if( !empty( $ShippingStreet2 ) ) { $shipping_address['street2'] = $ShippingStreet2; }

        $ShippingCity = get_post_meta( $transaction_id, 'ShippingCity', true);
        if( !empty( $ShippingCity ) ) { $shipping_address['city'] = $ShippingCity; }

        $ShippingState = get_post_meta( $transaction_id, 'ShippingState', true);
        if( !empty( $ShippingState ) ) { $shipping_address['state'] = $ShippingState; }

        $ShippingPostalCode = get_post_meta( $transaction_id, 'ShippingPostalCode', true);
        if( !empty( $ShippingPostalCode ) ) { $shipping_address['pincode'] = $ShippingPostalCode; }

        $ShippingCountryCode = get_post_meta( $transaction_id, 'ShippingCountryCode', true);
        if( !empty( $ShippingCountryCode ) ) { $shipping_address['country'] = $ShippingCountryCode; }

        $ShippingPhoneNumber = get_post_meta( $transaction_id, 'ShippingPhoneNumber', true);
        $ShippingEmail = get_post_meta( $transaction_id, 'ShippingEmail', true);
        ?>

        <?php if( !$is_email ) { ?>
            <!-- Receipt Information-->
            <div style="width: calc(33% - 15px);vertical-align: top;margin-left: 10px;float: left;" class=" form-detail show-me-only-in-print">

                <ul style="margin: 10px 0;padding: 0;width: 100%;display: block;">
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
        <!-- Billing Address-->
        <div style="float: left;display: inline-block;vertical-align: top;margin-right: 10px;width: calc( 50% - 10px ); <?php echo ('true' !== $billingInfo ) ? 'display:none': ''; ?>" class="transaction-column billing-details form-detail <?php echo ( 'true' === $shippingDisabled ) ? 'no-shipping-address' :''; ?>">
            <h2 class="transaction-details__title transaction-history-title"><?php _e('Billing Address','usb-swiper'); ?></h2>
            <address class="address-wrap" >
                <p style="margin-bottom: 0"><strong><?php echo $company_name ;?></strong></p>
                <?php
                // Splitting the Address Values
                if (!empty($billing_address)){

                    $billing_address_first_name =  !empty( $billing_address['first_name'] ) ? $billing_address['first_name'] : '' ;
                    $billing_address_last_name = !empty( $billing_address['last_name'] ) ? $billing_address['last_name'] : '' ;
                    $billing_address_street1 = !empty( $billing_address['street1'] ) ? $billing_address['street1'] : '' ;
                    $billing_address_street2 = !empty( $billing_address['street2'] ) ? $billing_address['street2'] : '' ;
                    $billing_address_city  = !empty( $billing_address['city'] ) ? $billing_address['city'] : '' ;
                    $billing_address_state = !empty( $billing_address['state'] ) ? $billing_address['state'] : '' ;
                    $billing_address_pincode  = !empty( $billing_address['pincode'] ) ? $billing_address['pincode'] : '' ;
                    $billing_address_country  = !empty( $billing_address['country']) ? $billing_address['country'] : '' ;
                    ?>
                    <p>
                        <?php echo esc_attr($billing_address_first_name)  . ' ' .   esc_attr($billing_address_last_name)  . ',<br/>';
                        echo sprintf( '%s %s', ! empty( $billing_address_street1 ) ? esc_attr($billing_address_street1).',' : '', ! empty( $billing_address_street2 ) ? esc_attr($billing_address_street2) : '' ). ' <br/>';
                        echo sprintf( '%s %s %s', ! empty( $billing_address_city ) ? esc_attr($billing_address_city).',' : '', ! empty( $billing_address_state ) ? esc_attr($billing_address_state) : '', ! empty( $billing_address_pincode ) ? ' '.esc_attr($billing_address_pincode) : '' ). ' <br/>';
                        echo esc_attr($billing_address_country)  . ' <br/>';
                        ?>
                    </p>
                    <?php
                }
                if(!empty($BillingPhoneNumber)){
                    ?>
                    <p class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>
                    <?php
                }
                if(!empty($BillingEmail)){
                    ?>
                    <p class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
                    <?php
                }
                ?>
            </address>
        </div>
        <!--Shipping Address-->
        <div style="float: left;display: inline-block;vertical-align: top;margin-left: 10px;width: calc( 50% - 10px );<?php echo ('true' === $shippingDisabled ) ? 'display:none': ''; ?>" class="shipping-details form-detail  transaction-column">
            <h2 class="transaction-details__title transaction-history-title" ><?php _e('Shipping Address','usb-swiper'); ?></h2>
            <address class="address-wrap" >
                <?php if( 'true' !== $shippingDisabled ) {
                    if( 'true' !== $shippingSameAsBilling ) { ?>
                        <!-- Splitting the Address Values-->
                        <?php  if (!empty($shipping_address)){

                            $shipping_address_first_name = !empty( $shipping_address['first_name'] ) ? $shipping_address['first_name'] : '' ;
                            $shipping_address_last_name = !empty( $shipping_address['last_name'] ) ? $shipping_address['last_name'] : '' ;
                            $shipping_address_street1 = !empty( $shipping_address['street1'] ) ? $shipping_address['street1'] : '' ;
                            $shipping_address_street2 = !empty( $shipping_address['street2'] ) ? $shipping_address['street2'] : '' ;
                            $shipping_address_city  = !empty( $shipping_address['city'] ) ? $shipping_address['city'] : '' ;
                            $shipping_address_state = !empty( $shipping_address['state'] ) ? $shipping_address['state'] : '' ;
                            $shipping_address_pincode  = !empty( $shipping_address['pincode'] ) ? $shipping_address['pincode'] : '' ;
                            $shipping_address_country  = !empty( $shipping_address['country'] ) ? $shipping_address['country'] : '' ;
                            ?>
                            <p>
                                <?php  echo esc_attr($shipping_address_first_name) . ' ' .   esc_attr($shipping_address_last_name) . ',<br/>';
                                echo sprintf( '%s %s', ! empty( $shipping_address_street1 ) ? esc_attr($shipping_address_street1).',' : '', ! empty( $shipping_address_street2 ) ? esc_attr($shipping_address_street2) : '' ). ' <br/>';
                                echo sprintf( '%s %s %s', ! empty( $shipping_address_city ) ? esc_attr($shipping_address_city).',' : '', ! empty( $shipping_address_state ) ? esc_attr($shipping_address_state) : '', ! empty( $shipping_address_pincode ) ? ' '.esc_attr($shipping_address_pincode) : '' ). ' <br/>';
                                echo esc_attr($shipping_address_country) . ' <br/>';
                                ?>
                            </p>
                            <?php
                        }
                        ?>
                        <p class="woocommerce-customer-details--phone"><?php echo esc_attr($ShippingPhoneNumber); ?></p>
                        <p class="woocommerce-customer-details--email"><?php echo esc_attr($ShippingEmail); ?></p>
                    <?php } else { ?>
                        <p>
                            <?php  echo esc_attr($billing_address_first_name) . ' ' .   esc_attr($billing_address_last_name) . ',<br/>';
                            echo esc_attr($billing_address_street1) . ', ' . esc_attr($billing_address_street2) . '<br/>';
                            echo esc_attr($billing_address_city) . ', ' . esc_attr($billing_address_state) . '-' . esc_attr($billing_address_pincode) . ' <br/>';
                            echo esc_attr($billing_address_country) . ' <br/>';
                            ?>
                        </p>
                        <?php
                        if(!empty($BillingPhoneNumber)){
                            ?>
                            <p class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>
                            <?php
                        }
                        if(!empty($BillingEmail)){
                            ?>
                            <p class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
                            <?php
                        }
                    }
                } ?>
            </address>
        </div>
    </div>
    <div class="transaction-details transaction-history-field" style="float: left;width: 100%;display: block;margin: 0 0 20px 0;padding: 0;">
        <h2 class="transaction-details__title transaction-history-title"><?php _e('Product Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;margin-bottom: 20px;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
            <tr>
                <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Product Name','usb-swiper'); ?></td>
                <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Quantity','usb-swiper'); ?></td>
                <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Price','usb-swiper'); ?></td>
            </tr>
            <?php
            if( ! empty( $vt_products ) && is_array( $vt_products ) ) {
                foreach ( $vt_products as $vt_product ) {
                    ?>
                    <tr>
                        <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $vt_product['product_name'] ) ? $vt_product['product_name'] : '-'; ?></td>
                        <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $vt_product['product_quantity'] ) ? $vt_product['product_quantity'] : '-'; ?></td>
                        <td class="transaction-table-product-td" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo !empty( $vt_product['product_price'] ) ? wc_price($vt_product['product_price'], array('currency' => $transaction_currency)) : 0; ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <h2 class="transaction-details__title transaction-history-title"><?php _e('Order Totals','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
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
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Tax Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($TaxAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('Grand Total','usb-swiper'); ?></th>
                <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo wc_price($GrandTotal, array('currency' => $transaction_currency)); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php if( empty( $transaction_type ) || empty( $payment_status ) || ( strtolower($transaction_type) === 'transaction' ) || ( strtolower($transaction_type) === 'invoice' && strtolower($payment_status) !== 'pending' ) ){ ?>
        <div class="payment-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 20px 0;padding: 0;float: left;">
        <h2 class="transaction-details__title transaction-history-title" ><?php _e('Transaction Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
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
                if( ( is_wc_endpoint_url('view-transaction') && $get_current_user_id === (int)$author_id ) || $_GET['action'] === 'edit' || ( $is_email && $is_admin ) ){ ?>
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
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo $credit_card_number; ?></td>
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
            <?php if( !empty( $transaction_debug_id ) ) { ?>
                <tr>
                    <th class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php _e('PayPal Debug ID','usb-swiper'); ?></th>
                    <td class="transaction-table-header" style="padding: 12px;border: 1px solid #ebebeb;"><?php echo $transaction_debug_id; ?></td>
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
    if( !empty( $status_note ) ) { ?>
        <div class="custom-payment-notes transaction-history-field" style="float: left;display: block;padding: 10px;border: 1px solid rgba(0,0,0,.1);margin: 10px 0;width: calc( 100% - 20px);">
            <p style="margin: 0;"> <?php echo sprintf(__('<strong>Payment Notes</strong>: %s','usb-swiper'), $status_note);?></p>
        </div>
        <style type="text/css">.custom-payment-notes p span{ margin-left:5px; }</style>
    <?php } ?>

    <div class="refund-details transaction-history-field" style="width: 100%;float: left;display: block;margin: 0 0 10px 0;padding: 0;">
        <?php if( !empty( $payment_refunds ) && is_array($payment_refunds)) {
            if( !class_exists('Usb_Swiper_Paypal_request') ) {
                include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
            }
            $Paypal_request = new Usb_Swiper_Paypal_request();
            echo $Paypal_request->get_refund_html($transaction_id);
        } ?>
    </div>
    <?php if( !$is_email ) { ?>
        <div class="custom-payment-notes signature" style="clear:both;">
            <br/>
            <br/>
            <p class="signature-text"><?php _e('Signature........................................','ubs-swiper') ; ?>
            </p>
        </div>
    <?php } ?>
</div>