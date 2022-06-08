<?php
if( empty($transaction_id)) {
    return;
}

$card_last_digits = get_post_meta( $transaction_id, '_payment_card_last_digits', true);
$card_brand = get_post_meta( $transaction_id, '_payment_card_brand', true);
$credit_card_number = $card_last_digits.' ('.$card_brand.')';
if( empty( $card_last_digits )) {
	$credit_card_number = __('Credit Card','usb-swiper');
}

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
$global_payment_status = get_post_meta( $transaction_id, '_payment_status', true);
$status_note = get_post_meta( $transaction_id, '_payment_status_notes', true);
$payment_response = get_post_meta( $transaction_id, '_payment_response', true);
$payment_source = !empty( $payment_response['payment_source'] ) ? $payment_response['payment_source'] : '';
$payment_card_number = !empty( $payment_source['card']['last_digits'] ) ? $payment_source['card']['last_digits'] : '';
$payment_card_brand = !empty( $payment_source['card']['brand'] ) ? $payment_source['card']['brand'] : '';
$payment_card_type = !empty( $payment_source['card']['type'] ) ? $payment_source['card']['type'] : '';

$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
$payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
$payment_refunds = !empty( $payment_details['refunds'] ) ? $payment_details['refunds'] : '';

$payment_intent_id = usbswiper_get_intent_id($transaction_id);
$payment_transaction_id = usbswiper_get_transaction_id($transaction_id);
$payment_status = usbswiper_get_transaction_status($transaction_id);
$payment_action = usbswiper_get_transaction_type($transaction_id);
$payment_create_time = usbswiper_get_transaction_datetime($transaction_id);
$payment_update_time = usbswiper_get_transaction_datetime($transaction_id, 'update_time');

if( $global_payment_status === 'FAILED' ) {
    $payment_status = $global_payment_status;
}

if( !class_exists('Usb_Swiper_Paypal_request') ) {
	include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
}

$Usb_Swiper_Paypal_request = new Usb_Swiper_Paypal_request();
$transaction_currency = $Usb_Swiper_Paypal_request->get_transaction_currency( $transaction_id);
?>
<div class="vt-form-notification"></div>
<div class="vt-transaction-history woocommerce-page" style="width: 100%;">
    <?php
    $myaccount_page_id = (int)get_option('woocommerce_myaccount_page_id');
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
     ?>
    <div class="transaction-overview transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
        <ul style="margin: 10px 0;padding: 0;width: 100%;display: block;">
            <li style="width: calc(25% - 5px);display: inline-block;font-size: 14px;margin-bottom: 15px;" class="transaction-id"><?php _e('Receipt ID','usb-swiper'); ?><strong style="display: block;"><?php echo $transaction_id; ?></strong></li>
            <li style="width: calc(25% - 5px);display: inline-block;font-size: 14px;margin-bottom: 15px;" class="transaction-date"><?php _e('Date','usb-swiper'); ?><strong style="display: block;"><?php echo get_the_date('Y-m-d',$transaction_id); ?></strong></li>
            <li style="width: calc(25% - 5px);display: inline-block;font-size: 14px;margin-bottom: 15px;" class="payment-status"><?php _e('Status','usb-swiper'); ?><strong style="display: block;"><?php echo usbswiper_get_payment_status($payment_status); ?></strong></li>
            <li style="width: calc(25% - 5px);display: inline-block;font-size: 14px;margin-bottom: 15px;" class="card-details"><?php _e('Card Detail','usb-swiper'); ?><strong style="display: block;"><?php echo $credit_card_number; ?></strong></li>
        </ul>
    </div>
    <div class="customer-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
		<?php
		$billingInfo = get_post_meta( $transaction_id, 'billingInfo', true);
		$shippingDisabled = get_post_meta( $transaction_id, 'shippingDisabled', true);
		$shippingSameAsBilling = get_post_meta( $transaction_id, 'shippingSameAsBilling', true);

		$billing_address = array();

		$BillingFirstName = get_post_meta( $transaction_id, 'BillingFirstName', true);
		if( !empty($BillingFirstName)) { $billing_address[] = $BillingFirstName; }

		$BillingLastName = get_post_meta( $transaction_id, 'BillingLastName', true);
		if( !empty($BillingLastName)) { $billing_address[] = $BillingLastName; }

		$BillingStreet = get_post_meta( $transaction_id, 'BillingStreet', true);
		if( !empty($BillingStreet)) { $billing_address[] = $BillingStreet; }

		$BillingStreet2 = get_post_meta( $transaction_id, 'BillingStreet2', true);
		if( !empty($BillingStreet2)) { $billing_address[] = $BillingStreet2; }

		$BillingCity = get_post_meta( $transaction_id, 'BillingCity', true);
		if( !empty($BillingCity)) { $billing_address[] = $BillingCity; }

		$BillingState = get_post_meta( $transaction_id, 'BillingState', true);
		if( !empty($BillingState)) { $billing_address[] = $BillingState; }

		$BillingPostalCode = get_post_meta( $transaction_id, 'BillingPostalCode', true);
		if( !empty($BillingPostalCode)) { $billing_address[] = $BillingPostalCode; }

		$BillingCountryCode = get_post_meta( $transaction_id, 'BillingCountryCode', true);
		if( !empty($BillingCountryCode)) { $billing_address[] = $BillingCountryCode; }

		$BillingPhoneNumber = get_post_meta( $transaction_id, 'BillingPhoneNumber', true);
		$BillingEmail = get_post_meta( $transaction_id, 'BillingEmail', true);

		$shipping_address = array();
		$ShippingFirstName = get_post_meta( $transaction_id, 'ShippingFirstName', true);
		if( !empty( $ShippingFirstName ) ) { $shipping_address[] = $ShippingFirstName; }

		$ShippingLastName = get_post_meta( $transaction_id, 'ShippingLastName', true);
		if( !empty( $ShippingLastName ) ) { $shipping_address[] = $ShippingLastName; }

		$ShippingStreet = get_post_meta( $transaction_id, 'ShippingStreet', true);
		if( !empty( $ShippingStreet ) ) { $shipping_address[] = $ShippingStreet; }

		$ShippingStreet2 = get_post_meta( $transaction_id, 'ShippingStreet2', true);
		if( !empty( $ShippingStreet2 ) ) { $shipping_address[] = $ShippingStreet2; }

		$ShippingCity = get_post_meta( $transaction_id, 'ShippingCity', true);
		if( !empty( $ShippingCity ) ) { $shipping_address[] = $ShippingCity; }

		$ShippingState = get_post_meta( $transaction_id, 'ShippingState', true);
		if( !empty( $ShippingState ) ) { $shipping_address[] = $ShippingState; }

		$ShippingPostalCode = get_post_meta( $transaction_id, 'ShippingPostalCode', true);
		if( !empty( $ShippingPostalCode ) ) { $shipping_address[] = $ShippingPostalCode; }

		$ShippingCountryCode = get_post_meta( $transaction_id, 'ShippingCountryCode', true);
		if( !empty( $ShippingCountryCode ) ) { $shipping_address[] = $ShippingCountryCode; }

		$ShippingPhoneNumber = get_post_meta( $transaction_id, 'ShippingPhoneNumber', true);
		$ShippingEmail = get_post_meta( $transaction_id, 'ShippingEmail', true);

		?>
        <div style="width: calc(50% - 15px);display: inline-block;vertical-align: top;margin-right: 10px;<?php echo ('true' !== $billingInfo ) ? 'display:none': ''; ?>" class="billing-details form-detail <?php echo ( 'true' === $shippingDisabled ) ? 'no-shipping-address' :''; ?>">
            <h2 class="transaction-details__title" style="font-size: 1.625rem;padding: 10px 0;"><?php _e('Billing Address','usb-swiper'); ?></h2>
            <address style="padding: 10px;border: 1px solid #ebebeb;">
				<?php echo !empty( $billing_address ) ? implode('<br/>', $billing_address) :'';?>
                <p class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>
                <p class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
            </address>
        </div>
        <div style="width: calc(50% - 15px);display: inline-block;vertical-align: top;margin-left: 10px;<?php echo ('true' === $shippingDisabled ) ? 'display:none': ''; ?>" class="shipping-details form-detail">
            <h2 class="transaction-details__title" style="font-size: 1.625rem;padding: 10px 0;"><?php _e('Shipping Address','usb-swiper'); ?></h2>
            <address style="padding: 10px;border: 1px solid #ebebeb;">
				<?php if( 'true' !== $shippingDisabled ) { ?>
					<?php if( 'true' !== $shippingSameAsBilling ) { ?>
						<?php echo !empty( $shipping_address ) ? implode('<br/>', $shipping_address) :'';?>
                        <p class="woocommerce-customer-details--phone"><?php echo $ShippingPhoneNumber; ?></p>
                        <p class="woocommerce-customer-details--email"><?php echo $ShippingEmail; ?></p>
					<?php } else { ?>
						<?php echo !empty( $billing_address ) ? implode('<br/>', $billing_address) :'';?>
                        <p class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>
                        <p class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
					<?php } ?>
				<?php } ?>
            </address>
        </div>
    </div>
    <div class="transaction-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
        <h2 class="transaction-details__title" style="font-size: 1.625rem;padding: 10px 0;"><?php _e('Item Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php echo !empty( $ItemName) ? $ItemName : ''; ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo wc_price($NetAmount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Shipping Amount','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo wc_price($ShippingAmount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Handling Amount','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo wc_price($HandlingAmount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Tax Amount','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo wc_price($TaxAmount, array('currency' => $transaction_currency)); ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Grand Total','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo wc_price($GrandTotal, array('currency' => $transaction_currency)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="payment-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
        <h2 class="transaction-details__title" style="font-size: 1.625rem;padding: 10px 0;"><?php _e('Transaction Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Payment Intent ID','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo !empty( $payment_intent_id ) ? $payment_intent_id : ''; ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Payment Intent','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo !empty( $payment_action ) ? $payment_action : ''; ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('PayPal Transaction ID','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo !empty( $payment_transaction_id ) ? $payment_transaction_id : ''; ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Payment Status','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo !empty( $payment_status ) ? usbswiper_get_payment_status($payment_status) : ''; ?></td>
                </tr>
                <?php if( !empty( $InvoiceID ) ) { ?>
                    <tr>
                        <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Invoice ID','usb-swiper'); ?></th>
                        <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo $InvoiceID; ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Payment Source','usb-swiper'); ?></th>
                    <?php if( !empty( $payment_card_number ) ) {  ?>
                        <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo sprintf( '%s (%s) - %s', $payment_card_number, $payment_card_brand, $payment_card_type); ?></td>
                    <?php } else { ?>
                        <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo $credit_card_number; ?></td>
                    <?php } ?>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Payment Created At','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo !empty( $payment_create_time ) ? date('Y/m/d g:i a', strtotime($payment_create_time)) : ''; ?></td>
                </tr>
                <tr>
                    <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('Payment Updated At','usb-swiper'); ?></th>
                    <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo !empty( $payment_update_time ) ? date('Y/m/d g:i a', strtotime($payment_update_time)) : ''; ?></td>
                </tr>
                <?php if( !empty( $transaction_debug_id ) ) { ?>
                    <tr>
                        <th style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php _e('PayPal Debug ID','usb-swiper'); ?></th>
                        <td style="text-align:left;width: 50%;padding: 10px;border-bottom: 1px solid #ebebeb;"><?php echo $transaction_debug_id; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

	<?php if( !empty( $Notes ) ) { ?>
        <div class="custom-notes transaction-history-field" style="display: block;padding: 10px;border: 1px solid rgba(0,0,0,.1);margin: 10px 0;width: calc( 100% - 20px);">
            <p style="margin: 0;"> <?php echo sprintf(__('<strong>Notes</strong>: %s','usb-swiper'), esc_html($Notes));?></p>
        </div>
	<?php } ?>

    <?php if( !empty( $status_note ) ) { ?>
        <div class="custom-payment-notes transaction-history-field" style="display: block;padding: 10px;border: 1px solid rgba(0,0,0,.1);margin: 10px 0;width: calc( 100% - 20px);">
            <p style="margin: 0;"> <?php echo sprintf(__('<strong>Payment Notes</strong>: %s','usb-swiper'), $status_note);?></p>
        </div>
        <style type="text/css">.custom-payment-notes p span{ margin-left:5px; }</style>
    <?php } ?>

    <div class="refund-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
	    <?php if( !empty( $payment_refunds ) && is_array($payment_refunds)) {
            if( !class_exists('Usb_Swiper_Paypal_request') ) {
	            include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
            }
            $Paypal_request = new Usb_Swiper_Paypal_request();
            echo $Paypal_request->get_refund_html($transaction_id);
	    } ?>
    </div>
</div>