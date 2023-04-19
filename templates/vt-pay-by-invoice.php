<?php
$invoice_id = !empty($args['invoice_id']) ? (int)$args['invoice_id'] : "";
$invoice_status = !empty($args['invoice_status']) ? $args['invoice_status'] : "";
$transaction_type = get_post_meta($invoice_id,'_transaction_type', true);
$payment_status = usbswiper_get_transaction_status($invoice_id);
$billing_first_name = get_post_meta($invoice_id, 'BillingFirstName', true);
$billing_last_name = get_post_meta($invoice_id, 'BillingLastName', true);

$settings = usb_swiper_get_settings('general');
$vt_invoice_page = !empty( $settings['vt_paybyinvoice_page'] ) ? $settings['vt_paybyinvoice_page'] : '';

if( !class_exists('Usb_Swiper_Paypal_request') ) {
    include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
}

$Paypal_request = Usb_Swiper_Paypal_request::instance();
$response = $Paypal_request->create_transaction_request( $invoice_id,true );
$payment_intent = usbswiper_get_transaction_type($invoice_id);
$payment_intent = !empty( $payment_intent ) ? strtolower( $payment_intent ) : '';
?>
<div id="content" class="invoice-content-main-wrap main-content woocommerce">
    <div class="vt-form-notification"></div>
    <form method="post" class="HostedFields" name="ae-paypal-pos-form" id="ae-paypal-pos-form" enctype="multipart/form-data">
        <div class="vt-form-contents">
            <div class="vt-row">
                <?php if( empty( $invoice_id ) ){ ?>
                    <div class="vt-form-message"><?php _e('Sorry, No invoice data found.','usb-swiper'); ?></div>
                <?php } elseif( !empty( $payment_status ) && ( strtolower($payment_status) === 'paid' || strtolower($payment_status) === 'authorized' ) ) {
                    usb_swiper_get_template( 'wc-transaction-history.php', array( 'transaction_id' => $invoice_id ) );
                } else { ?>
                    <div class="d-flex">
                        <?php
                        usb_swiper_get_template( 'vt-invoice-html.php', array( 'invoice_id' => $invoice_id ) );

                        if( !empty( $vt_invoice_page ) && (int)$vt_invoice_page === get_the_ID()) { ?>
                            <div class="paypal-payment">
                                <div class="vt-payment-wrapper">
                                    <div class="vt-col vt-col-100 pay-with-paypal vt-col-payments">
                                        <div class="vt-col-pay-using-paypal">
                                            <div id="smart-button-container">
                                                <div style="text-align: center;">
                                                    <div id="paypal-button-container"></div>
                                                    <script type="text/javascript">
                                                        jQuery( document ).ready(function( $ ) {
                                                            var VtForm = jQuery('form#ae-paypal-pos-form');
                                                            function initPayPalButton() {
                                                                paypal.Buttons({
                                                                    style: {
                                                                        shape: 'pill',
                                                                        color: 'gold',
                                                                        layout: 'vertical',
                                                                        label: 'pay',
                                                                    },

                                                                    createOrder: function (data, actions) {
                                                                        VtForm.addClass('processing').block({
                                                                            message: null,
                                                                            overlayCSS: {
                                                                                background: '#fff',
                                                                                opacity: 0.6
                                                                            }
                                                                        });

                                                                        return fetch(usb_swiper_settings.create_transaction_url+"&transaction_id=<?php echo $invoice_id; ?>", {
                                                                            method: 'post',
                                                                            headers: {
                                                                                'Content-Type': 'application/x-www-form-urlencoded'
                                                                            },
                                                                            body: "transaction_id=<?php echo $invoice_id; ?>",
                                                                        }).then(function (res) {
                                                                            return res.json();
                                                                        }).then(function (data) {
                                                                            if( data.orderID ) {
                                                                                localStorage.setItem("vt_order_id", data.orderID);
                                                                                return data.orderID;
                                                                            }
                                                                        });
                                                                    },
                                                                    onApprove: function (data, actions) {
                                                                        if (data.orderID) {
                                                                            $.post(usb_swiper_settings.cc_capture + "&paypal_transaction_id=" + data.orderID + "&transaction_id=<?php echo $invoice_id; ?>&wc-process-transaction-nonce=" + usb_swiper_settings.usb_swiper_transaction_nonce, function (data) {
                                                                                if( data.result === 'success' ) {
                                                                                    window.location.href = data.redirect;
                                                                                } else{
                                                                                    const notification = jQuery('.vt-form-notification');
                                                                                    notification.html('');
                                                                                    notification.append('<p class="notification error">'+data.message+'</p>');
                                                                                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                                                                }
                                                                            });
                                                                        }
                                                                    },
                                                                    onCancel: function (data) {
                                                                        VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                                                    },
                                                                    onError: function (err) {
                                                                        if( err ) {
                                                                            const notification = jQuery('.vt-form-notification');
                                                                            notification.html('');
                                                                            notification.append('<p class="notification error">'+err+'</p>');
                                                                            VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                                                        }
                                                                    }
                                                                }).render('#paypal-button-container');
                                                            }
                                                            initPayPalButton('<?php echo $payment_intent; ?>');
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
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </form>
</div>
