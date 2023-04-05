<?php
$invoice_id = !empty($args['invoice_id']) ? (int)$args['invoice_id'] : "";
$user_invoice_id = get_post_meta( $invoice_id, '_user_invoice_id', true);
$is_email = !empty( $args['is_email'] );
$Usb_Swiper_Paypal_request = new Usb_Swiper_Paypal_request();
$transaction_currency = $Usb_Swiper_Paypal_request->get_transaction_currency( $invoice_id);

$merchant_id = get_post_meta( $invoice_id, '_transaction_user_id', true);

$merchantInfo = !empty( $merchant_id ) ? get_userdata($merchant_id) : '';
$merchant_name = !empty( $merchantInfo->display_name ) ? $merchantInfo->display_name : '';
$merchant_email = !empty( $merchantInfo->user_email ) ? $merchantInfo->user_email : '';
$merchant_brand = get_user_meta( $merchant_id,'brand_name', true);
$merchant_brand = !empty( $merchant_brand ) ? $merchant_brand : get_bloginfo('name');

$transaction_type = get_post_meta($invoice_id,'_transaction_type', true);
$payment_status = usbswiper_get_transaction_status($invoice_id);
$billing_first_name = get_post_meta($invoice_id, 'BillingFirstName', true);
$billing_last_name = get_post_meta($invoice_id, 'BillingLastName', true);
$billing_email = get_post_meta($invoice_id, 'BillingEmail', true);
$billing_phone_number = get_post_meta($invoice_id, 'BillingPhoneNumber', true);
$net_amount = get_post_meta($invoice_id, 'NetAmount', true);
$shipping_amount = get_post_meta($invoice_id, 'ShippingAmount', true);
$handling_amount = get_post_meta($invoice_id, 'HandlingAmount', true);
$tax_rate = get_post_meta($invoice_id, 'TaxRate', true);
$tax_amount = get_post_meta($invoice_id, 'TaxAmount', true);
$grand_total_amount = get_post_meta($invoice_id, 'GrandTotal', true);
$vt_products = get_post_meta($invoice_id, 'vt_products', true);
$item_names = get_post_meta($invoice_id, 'ItemName', true);
$invoice_notes = get_post_meta($invoice_id, 'Notes', true);
$billing_phone_number = !empty( $billing_phone_number ) ? mobile_number_format($billing_phone_number) : '-';

$billing_address = array();

$BillingStreet = get_post_meta( $invoice_id, 'BillingStreet', true);
if( !empty($BillingStreet)) { $billing_address[] = $BillingStreet; }

$BillingStreet2 = get_post_meta( $invoice_id, 'BillingStreet2', true);
if( !empty($BillingStreet2)) { $billing_address[] = $BillingStreet2; }

$BillingCity = get_post_meta( $invoice_id, 'BillingCity', true);
if( !empty($BillingCity)) { $billing_address[] = $BillingCity; }

$BillingState = get_post_meta( $invoice_id, 'BillingState', true);
if( !empty($BillingState)) { $billing_address[] = $BillingState; }

$BillingPostalCode = get_post_meta( $invoice_id, 'BillingPostalCode', true);
if( !empty($BillingPostalCode)) { $billing_address[] = $BillingPostalCode; }

$BillingCountryCode = get_post_meta( $invoice_id, 'BillingCountryCode', true);
if( !empty($BillingCountryCode)) { $billing_address[] = $BillingCountryCode; }

$shippingDisabled = get_post_meta( $invoice_id,'shippingDisabled', true);

$shipping_address = array();
if( $shippingDisabled !== 'true') {
    $shippingSameAsBilling = get_post_meta($invoice_id, 'shippingSameAsBilling', true);
    if( $shippingSameAsBilling !== true ) {
        $ShippingFirstName = get_post_meta( $invoice_id, 'ShippingFirstName', true);
        if( !empty( $ShippingFirstName ) ) { $shipping_address[] = $ShippingFirstName; }
        $ShippingLastName = get_post_meta( $invoice_id, 'ShippingLastName', true);
        if( !empty( $ShippingLastName ) ) { $shipping_address[] = $ShippingLastName; }
        $shipping_address_1 = get_post_meta( $invoice_id,'ShippingStreet', true);
        if( !empty($shipping_address_1)) { $shipping_address[] = $shipping_address_1; }
        $shipping_address_2 = get_post_meta( $invoice_id,'ShippingStreet2', true);
        if( !empty($shipping_address_2)) { $shipping_address[] = $shipping_address_2; }
        $shipping_city = get_post_meta( $invoice_id,'ShippingCity', true);
        if( !empty($shipping_city)) { $shipping_address[] = $shipping_city; }
        $shipping_state = get_post_meta( $invoice_id,'ShippingState', true);
        if( !empty($shipping_state)) { $shipping_address[] = $shipping_state; }
        $shipping_postcode = get_post_meta( $invoice_id,'ShippingPostalCode', true);
        if( !empty($shipping_postcode)) { $shipping_address[] = $shipping_postcode; }
        $shipping_country = get_post_meta( $invoice_id,'ShippingCountryCode', true);
        if( !empty($shipping_country)) { $shipping_address[] = $shipping_country; }

        $ShippingPhoneNumber = get_post_meta( $invoice_id, 'ShippingPhoneNumber', true);
        $ShippingEmail = get_post_meta( $invoice_id, 'ShippingEmail', true);
    }
}

$net_amount = !empty( $net_amount ) ? usb_swiper_price_formatter($net_amount) : usb_swiper_price_formatter(0);
$shipping_amount = !empty( $shipping_amount ) ? usb_swiper_price_formatter($shipping_amount) : usb_swiper_price_formatter(0);
$handling_amount = !empty( $handling_amount ) ? usb_swiper_price_formatter($handling_amount) : usb_swiper_price_formatter(0);
$tax_rate = !empty( $tax_rate ) ? $tax_rate : '0%';
$tax_amount = !empty( $tax_amount ) ? usb_swiper_price_formatter($tax_amount) : usb_swiper_price_formatter(0);
$grand_total_amount = !empty( $grand_total_amount ) ? usb_swiper_price_formatter($grand_total_amount) : usb_swiper_price_formatter(0);
$discount_amount = !empty( $discount_amount ) ? usb_swiper_price_formatter($discount_amount) : usb_swiper_price_formatter(0);
$discount_percentage = !empty( $discount_percentage ) ? $discount_percentage : '0%';

$site_logo = esc_url( wp_get_attachment_url( get_theme_mod( 'custom_logo' ) ) );

?>
<div class="invoice-wrap" style="border: 1px solid #ccc;box-shadow: 0 0 10px #ccc;width: <?php echo $is_email ? '100%':'70%'; ?>">
    <section class="invoice-branding invoice-general" style="display: block;padding: 20px;float: left;width: 100%;border-bottom: 1px solid #CCC;">
        <div class="branding" style="width: 50%;display: inline-block;vertical-align: top;float: left;">
            <div class="logo" style="width: 100%;float: left;">
                <?php if( !empty( $site_logo ) ) { ?>
                    <img style="width: 100%;float: left;max-width: 25%" src="<?php echo $site_logo; ?>" alt="logo">
                <?php } ?>
                <h3 style="width: auto;float: left;clear: unset;margin-left: 10px;margin-top: 5px;"><?php echo !empty( $merchant_brand ) ? $merchant_brand : ""; ?></h3>
            </div>
            <div class="address" style="width: 100%;float: left;">
                <p style="margin: 0" class="invoice-display-name"><?php echo !empty( $merchant_name ) ? $merchant_name : ""; ?></p>
                <p style="margin: 0" class="invoice-email-address"><?php echo !empty( $merchant_email ) ? $merchant_email : ""; ?></p>
            </div>
        </div>
        <div class="invoice-date" style="width: 50%;display: inline-block;vertical-align: top;float: left;text-align: right;">
            <p class="invoice-number">
                <span class="invoice-title" style="font-size: 20px;font-weight: bold;"><?php echo sprintf( __('Invoice: #%s', 'usb-swiper'), '<span style="color: #4361ee;">'.$user_invoice_id.'</span>'); ?></span>
            </p>
            <p style="margin: 0" class="invoice-created-date">
                <span style="font-weight: bold;" class="invoice-title"><?php echo  sprintf( __('Invoice Date: %s', 'usb-swiper'), '<span style="font-weight: normal;">'.get_the_date('d M Y', $invoice_id).'</span>'); ?></span>
            </p>
            <p style="margin: 0" class="invoice-due-date">
                <span style="font-weight: bold;" class="invoice-title"><?php echo  sprintf( __('Due Date: %s', 'usb-swiper'), '<span style="font-weight: normal;">'.get_the_date('d M Y', $invoice_id).'</span>'); ?></span>
            </p>
        </div>
    </section>
    <section class="invoice-payment-info invoice-general" style="display: block;padding: 20px;float: left;width: 100%;border-bottom: 1px solid #CCC;">
        <div class="address" style="width: 75%;display: inline-block;vertical-align: top;float: left;">
            <div class="address" style="width: 50%;display: inline-block;vertical-align: top;float: left;">
                <h2 style="font-size: 20px;"><?php _e('Invoice To', 'usb-swiper'); ?></h2>
                <p style="margin: 0;font-size: 14px;font-weight: 600;color:#4361ee;"><?php echo $billing_first_name . ' ' . $billing_last_name ; ?></p>
                <?php
                if( empty( $shipping_address ) && !empty( $billing_address ) ){
                    echo '<p style="margin: 0;font-size: 12px;font-weight: 600;">'.sprintf( '%s %s', ! empty( $BillingStreet ) ? esc_attr($BillingStreet).',' : '', ! empty( $BillingStreet2 ) ? esc_attr($BillingStreet2) : '' ). '<br />';
                    echo sprintf( '%s %s %s', ! empty( $BillingCity ) ? esc_attr($BillingCity).',' : '', ! empty( $BillingState ) ? esc_attr($BillingState) : '', ! empty( $BillingPostalCode ) ? ' '.esc_attr($BillingPostalCode) : '' ). '<br />';
                    echo !empty($BillingCountryCode) ? esc_attr($BillingCountryCode) : '' . '</p>';
                } else {
                    echo '<p style="margin: 0;font-size: 12px;font-weight: 600;">'.sprintf( '%s %s', ! empty( $shipping_address_1 ) ? esc_attr($shipping_address_1).',' : '', ! empty( $shipping_address_2 ) ? esc_attr($shipping_address_2) : '' ). '<br />';
                    echo sprintf( '%s %s %s', ! empty( $shipping_city ) ? esc_attr($shipping_city).',' : '', ! empty( $shipping_state ) ? esc_attr($shipping_state) : '', ! empty( $shipping_postcode ) ? ' '.esc_attr($shipping_postcode) : '' ). '<br />';
                    echo !empty($shipping_country) ? esc_attr($shipping_country) : '' . '</p>';
                } ?>
                <?php echo ! empty( $billing_email ) ? '<p style="margin: 0;font-size: 12px;font-weight: 600;">'.$billing_email.'</p>' : '' ?>
                <?php echo ! empty( $billing_phone_number ) ? '<p style="margin: 0;font-size: 12px;font-weight: 600;">'.$billing_phone_number.'</p>' : '' ?>
            </div>
            <?php if( !empty( $shipping_address ) && !empty( $billing_address ) ){ ?>
                <div class="address" style="width: 50%;display: inline-block;vertical-align: top;float: left;">
                    <h2 style="font-size: 20px;"><?php _e('Shipping Address', 'usb-swiper'); ?></h2>
                    <p style="margin: 0;font-size: 14px;font-weight: 600;color:#4361ee;"><?php echo $ShippingFirstName . ' ' . $ShippingLastName ; ?></p>
                    <?php
                    echo '<p style="margin: 0;font-size: 12px;font-weight: 600;">'.sprintf( '%s %s', ! empty( $shipping_address_1 ) ? esc_attr($shipping_address_1).',' : '', ! empty( $shipping_address_2 ) ? esc_attr($shipping_address_2) : '' ). '<br />';
                    echo sprintf( '%s %s %s', ! empty( $shipping_city ) ? esc_attr($shipping_city).',' : '', ! empty( $shipping_state ) ? esc_attr($shipping_state) : '', ! empty( $shipping_postcode ) ? ' '.esc_attr($shipping_postcode) : '' ). '<br />';
                    echo !empty($shipping_country) ? esc_attr($shipping_country) : '' . '</p>';
                    echo ! empty( $ShippingEmail ) ? '<p style="margin: 0;font-size: 12px;font-weight: 600;">'.$ShippingEmail.'</p>' : '';
                    echo ! empty( $ShippingPhoneNumber ) ? '<p style="margin: 0;font-size: 12px;font-weight: 600;">'.$ShippingPhoneNumber.'</p>' : '' ?>
                </div>
            <?php } ?>
        </div>
        <div class="invoice-payment" style="width: 25%;display: inline-block;vertical-align: top;float: left;text-align: left;">
            <h2 style="font-size: 20px;"><?php _e( 'Payment Status','usb-swiper'); ?></h2>
            <p style="margin: 0;font-size: 12px;font-weight: 600;">
                <img style="width: 80px;float: left;top: 0;left: 30%;" src="<?php echo usb_swiper_get_invoice_status_icon($invoice_id); ?>" alt="logo">
            </p>
        </div>
    </section>
    <section class="invoice-items invoice-general" style="display: block;padding: 0;float: left;width: 100%;border-bottom: 1px solid #CCC;">
        <table class="invoice-table" cellpadding="0" cellspacing="0" style="border: 0; width: 100%">
            <thead>
                <tr>
                    <th style="width:12%;padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;text-align: center;" class="number"><?php _e( 'S.NO','usb-swiper'); ?></th>
                    <th style="width:40%;padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;text-align: left;" class="item"><?php _e( 'ITEMS','usb-swiper'); ?></th>
                    <th style="width:10%;padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;text-align: center;" class="qty"><?php _e( 'QTY','usb-swiper'); ?></th>
                    <th style="width:20%;padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;text-align: right;" class="price"><?php _e( 'PRICE','usb-swiper'); ?></th>
                    <th style="width:18%;padding:10px 20px 10px 10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;text-align: right;" class="amount"><?php _e( 'AMOUNT','usb-swiper'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if( !empty( $vt_products ) && is_array($vt_products) ){

                    foreach ($vt_products as $key => $product ){
                        $quantity = !empty($product['product_quantity']) ? (int)$product['product_quantity'] : 0;
                        $price = !empty($product['product_price']) ? (float)$product['product_price'] : 0;
                        $total_price = $quantity * $price;
                        $price = !empty( $price ) ? usb_swiper_price_formatter($price) : usb_swiper_price_formatter(0);
                        $total_price = !empty( $total_price ) ? usb_swiper_price_formatter($total_price) : usb_swiper_price_formatter(0);
                        ?>
                        <tr>
                            <td style="padding:10px;text-align: center;border-left: 0;border-right: 0;border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;" class="number" data-title="<?php _e( 'S.NO','usb-swiper'); ?>"><?php echo $key+1; ?></td>
                            <td style="padding:10px;text-align: left;border-left: 0;border-right: 0;border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;" class="item" data-title="<?php _e( 'ITEMS','usb-swiper'); ?>"><?php echo !empty($product['product_name']) ? $product['product_name'] : ""; ?></td>
                            <td style="padding:10px;text-align: center;border-left: 0;border-right: 0;border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;" class="qty" data-title="<?php _e( 'QTY','usb-swiper'); ?>"><?php echo !empty($quantity) ? $quantity : 0; ?></td>
                            <td style="padding:10px;text-align: right;border-left: 0;border-right: 0;border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;" class="price" data-title="<?php _e( 'PRICE','usb-swiper'); ?>"><?php echo wc_price($price, array('currency' => $transaction_currency)); ?></td>
                            <td style="padding:10px 20px 10px 10px;text-align: right;border-left: 0;border-right: 0;border-top: 1px solid #ccc; border-bottom: 1px solid #ccc;" class="amount" data-title="<?php _e( 'AMOUNT','usb-swiper'); ?>"><?php echo wc_price($total_price, array('currency' => $transaction_currency));?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td style="padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" colspan="3"></td>
                    <td style="padding:10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="title"><?php _e('Sub Total:','usb-swiper'); ?></td>
                    <td style="padding:10px 20px 10px 10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="amount" data-title="<?php _e('Sub Total:','usb-swiper'); ?>"><?php echo wc_price($net_amount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr>
                    <td style="padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" colspan="3"></td>
                    <td style="padding:10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="title"><?php _e('Tax Amount:','usb-swiper'); ?></td>
                    <td style="padding:10px 20px 10px 10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="amount" data-title="<?php _e('Tax Amount:','usb-swiper'); ?>"><?php echo wc_price($tax_amount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr>
                    <td style="padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" colspan="3"></td>
                    <td style="padding:10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="title"><?php _e('Shipping Amount:','usb-swiper'); ?></td>
                    <td style="padding:10px 20px 10px 10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="amount" data-title="<?php _e('Shipping Amount: ','usb-swiper'); ?>"><?php echo wc_price($shipping_amount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr>
                    <td style="padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" colspan="3"></td>
                    <td style="padding:10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="title"><?php _e('Handling Amount:','usb-swiper'); ?></td>
                    <td style="padding:10px 20px 10px 10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" class="amount" data-title="<?php _e('Handling Amount: ','usb-swiper'); ?>"><?php echo wc_price($shipping_amount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr class="grand-total">
                    <td style="padding:10px;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;" colspan="3"></td>
                    <td style="padding:10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;font-weight: bold;" class="title"><?php _e('Grand Total:','usb-swiper'); ?></td>
                    <td style="padding:10px 20px 10px 10px;text-align: right;border-left: 0;border-right: 0;border-top: 0; border-bottom: 0;font-weight: bold;" class="amount" data-title="<?php _e('Grand Total:','usb-swiper'); ?>"><?php echo wc_price($grand_total_amount, array('currency' => $discount_amount)); ?></td>
                </tr>
            </tfoot>
        </table>
    </section>
    <section class="invoice-footer invoice-general" style="display: block;padding: 20px;float: left;width: 100%;border-bottom: 1px solid #CCC;">
        <div class="invoice--note">
            <?php if( !empty( $invoice_notes ) ){ ?>
                <p style="margin: 0;font-weight: 600;"><?php echo sprintf( __('Note: %s', 'usb-swiper'), $invoice_notes); ?></p>
            <?php } ?>
        </div>
    </section>
</div>
