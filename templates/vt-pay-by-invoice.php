<?php
$invoice_id = !empty($args['invoice_id']) ? (int)$args['invoice_id'] : "";
$invoice_status = !empty($args['invoice_status']) ? $args['invoice_status'] : "";
$transaction_type = get_post_meta($invoice_id,'_transaction_type', true);
$payment_status = get_post_meta($invoice_id,'_payment_status', true);
$billing_first_name = get_post_meta($invoice_id, 'BillingFirstName', true);
$billing_last_name = get_post_meta($invoice_id, 'BillingLastName', true);

$settings = usb_swiper_get_settings('general');
$vt_invoice_page = !empty( $settings['vt_paybyinvoice_page'] ) ? $settings['vt_paybyinvoice_page'] : '';

if( !class_exists('Usb_Swiper_Paypal_request') ) {
    include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
}

$Paypal_request = Usb_Swiper_Paypal_request::instance();
$response = $Paypal_request->create_transaction_request( $invoice_id,true );

?>

<div id="content" class="invoice-content-main-wrap main-content woocommerce">
    <div class="vt-form-notification"></div>
    <form method="post" class="HostedFields" name="ae-paypal-pos-form" id="ae-paypal-pos-form" enctype="multipart/form-data">
        <div class="vt-form-contents">
            <div class="vt-row">
                <?php if( empty( $invoice_id ) ){ ?>
                    <div class="vt-form-message"><?php _e('Sorry, No invoice data found.','usb-swiper'); ?></div>
                <?php } elseif( !empty( $payment_status ) && strtolower($payment_status) === 'paid' && !empty( $invoice_status ) && strtolower($invoice_status) == 'completed' ) {
                    usb_swiper_get_template( 'wc-transaction-history.php', array( 'transaction_id' => $invoice_id ) );
                } else { ?>
                    <div class="d-flex">

                        <?php
                        usb_swiper_get_template( 'vt-invoice-html.php', array( 'invoice_id' => $invoice_id ) );

                        if( !empty( $vt_invoice_page ) && (int)$vt_invoice_page === get_the_ID()) { ?>
                            <div class="paypal-payment">
                                <div class="vt-col vt-col-100 pay-with-paypal vt-col-payments">
                                    <div class="vt-col-pay-using-paypal">
                                        <div id="smart-button-container">
                                            <div style="text-align: center;">
                                                <div id="paypal-button-container"></div>
                                                <script type="text/javascript">
                                                    jQuery( document ).ready(function( $ ) {
                                                        function initPayPalButton() {
                                                            paypal.Buttons({
                                                                style: {
                                                                    shape: 'pill',
                                                                    color: 'gold',
                                                                    layout: 'vertical',
                                                                    label: 'pay',
                                                                },

                                                                createOrder: function (data, actions) {
                                                                    return actions.order.create(<?php echo !empty($response) ? $response : ''; ?>);
                                                                },
                                                                onApprove: function (data, actions) {
                                                                    return actions.order.capture().then(function (orderData) {
                                                                        jQuery.ajax({
                                                                            url: usb_swiper_settings.ajax_url,
                                                                            type: 'POST',
                                                                            dataType: 'json',
                                                                            data: "orderData="+JSON.stringify( orderData )+"&action=manage_pay_with_paypal_transaction&transaction_id=<?php echo $invoice_id; ?>",
                                                                        }).done(function ( response ) {
                                                                            if( response) {
                                                                                actions.redirect(response.redirect_url);
                                                                            }
                                                                        });
                                                                    });
                                                                },
                                                                onError: function (err) {
                                                                    jQuery.ajax({
                                                                        url: usb_swiper_settings.ajax_url,
                                                                        type: 'POST',
                                                                        dataType: 'json',
                                                                        data: "orderData="+err+"&action=manage_pay_with_paypal_transaction&is_error=true&transaction_id=<?php echo $invoice_id; ?>",
                                                                    }).done(function ( response ) {
                                                                        if( response.message ) {
                                                                            const notification = jQuery('.vt-form-notification');
                                                                            notification.html('');
                                                                            notification.append('<p class="notification error">'+response.message+'</p>');
                                                                        }
                                                                    });
                                                                }
                                                            }).render('#paypal-button-container');
                                                        }
                                                        initPayPalButton();
                                                    });
                                                </script>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="vt-col vt-col-100 vt-col-payments">
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
                                        <input type="hidden" id="transaction_id" name="transaction_id" value="<?php echo ! empty( $invoice_id ) ? $invoice_id : ''; ?>">
                                        <input type="hidden" id="BillingFirstName" name="BillingFirstName" value="<?php echo ! empty( $BillingFirstName ) ? $BillingFirstName : ''; ?>">
                                        <input type="hidden" id="BillingLastName" name="BillingLastName" value="<?php echo ! empty( $BillingLastName ) ? $BillingLastName : ''; ?>">
                                        <button type="submit" class="vt-button" id="pos-submit-btn"><?php _e('Process Payment','usb-swiper'); ?></button>
                                    </div>
                                    <div class="usb-swiper-ppcp-cc-form"><div id="angelleye_ppcp_checkout"></div></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </form>
</div>
