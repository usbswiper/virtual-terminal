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
$company_name = get_post_meta($transaction_id,'company',true);

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
            <div>
                <button id="send_email_btn" class="vt-button"><?php _e('Send Email','usb-swiper'); ?></button>
            </div>
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
    <div class="hide-me-in-print transaction-overview transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">

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

        <!--        Receipt Information-->
        <div style="width: calc(33% - 15px);vertical-align: top;margin-left: 10px;" class=" form-detail show-me-only-in-print">

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
        <!--        Billing Address-->
        <div style=";display: inline-block;vertical-align: top;margin-right: 10px;<?php echo ('true' !== $billingInfo ) ? 'display:none': ''; ?>" class="transaction-column billing-details form-detail <?php echo ( 'true' === $shippingDisabled ) ? 'no-shipping-address' :''; ?>">
            <h2 class="transaction-details__title transaction-history-title"><?php _e('Billing Address','usb-swiper'); ?></h2>
            <address class="address-wrap" >
                <p style="margin-bottom: 0"> <strong>
						<?php echo $company_name ;?>
                    </strong>
                </p>

				<?php
//				Splitting the Address Values

                if (!empty($billing_address)){

					$billing_address_first_name =  !empty( $billing_address[0] ) ? $billing_address[0] : '' ;
					$billing_address_last_name = !empty( $billing_address[1] ) ? $billing_address[1] : '' ;
					$billing_address_street1 = !empty( $billing_address[2] ) ? $billing_address[2] : '' ;
					$billing_address_street2 = !empty( $billing_address[3] ) ? $billing_address[3] : '' ;
					$billing_address_city  = !empty( $billing_address[4] ) ? $billing_address[4] : '' ;
					$billing_address_state = !empty( $billing_address[5] ) ? $billing_address[5] : '' ;
					$billing_address_pincode  = !empty( $billing_address[6] ) ? $billing_address[6] : '' ;
					$billing_address_country  = !empty( $billing_address[7]) ? $billing_address[7] : '' ;
				}
				?>
                <p>
					<?php  echo esc_attr($billing_address_first_name)  . ' ' .   esc_attr($billing_address_last_name)  . ',<br/>';
					echo esc_attr($billing_address_street1) . ', ' . esc_attr($billing_address_street2) . '</br>';
					echo esc_attr($billing_address_city)  . ', ' .  esc_attr($billing_address_state)  . '- ' . esc_attr($billing_address_pincode)  . '<br/>';
					echo esc_attr($billing_address_country)  . ' <br/>';
					?>
                </p>

	            <?php
	            if(!empty($BillingPhoneNumber)){
		            ?>
                    <p class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>

		            <?php
	            }
	            ?>

	            <?php
	            if(!empty($BillingEmail)){
		            ?>
                    <p class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>
		            <?php
	            }
	            ?>
            </address>
        </div>
        <!--Shipping Address-->
        <div  style="display: inline-block;vertical-align: top;margin-left: 10px;<?php echo ('true' === $shippingDisabled ) ? 'display:none': ''; ?>" class="shipping-details form-detail  transaction-column">
            <h2 class="transaction-details__title transaction-history-title" ><?php _e('Shipping Address','usb-swiper'); ?></h2>
            <address class="address-wrap" >
				<?php if( 'true' !== $shippingDisabled ) { ?>
					<?php if( 'true' !== $shippingSameAsBilling ) { ?>
                        <!--                Splitting the Address Values-->
						<?php  if (!empty($shipping_address)){

							$shipping_address_first_name = !empty( $shipping_address[0] ) ? $shipping_address[0] : '' ;
							$shipping_address_last_name = !empty( $shipping_address[1] ) ? $shipping_address[1] : '' ;
							$shipping_address_street1 = !empty( $shipping_address[2] ) ? $shipping_address[2] : '' ;
							$shipping_address_street2 = !empty( $shipping_address[3] ) ? $shipping_address[3] : '' ;
							$shipping_address_city  = !empty( $shipping_address[4] ) ? $shipping_address[4] : '' ;
							$shipping_address_state = !empty( $shipping_address[5] ) ? $shipping_address[5] : '' ;
							$shipping_address_pincode  = !empty( $shipping_address[6] ) ? $shipping_address[6] : '' ;
							$shipping_address_country  = !empty( $shipping_address[7] ) ? $shipping_address[7] : '' ;
						}
						?>
                        <p>
							<?php  echo esc_attr($shipping_address_first_name) . ' ' .   esc_attr($shipping_address_last_name) . '</br>';
							echo esc_attr($shipping_address_street1) . ', ' . esc_attr($shipping_address_street2) . '</br>';
							echo esc_attr($shipping_address_city) . ', ' . esc_attr($shipping_address_state) . '-' . esc_attr($shipping_address_pincode) . '</br>';
							echo esc_attr($shipping_address_country) . '</br>';
							?>
                        </p>

                        <p class="woocommerce-customer-details--phone"><?php echo esc_attr($ShippingPhoneNumber); ?></p>
                        <p class="woocommerce-customer-details--email"><?php echo esc_attr($ShippingEmail); ?></p>
					<?php } else { ?>
                        <p>
							<?php  echo esc_attr($billing_address_first_name) . ' ' .   esc_attr($billing_address_last_name) . '</br>';
							echo esc_attr($billing_address_street1) . ', ' . esc_attr($billing_address_street2) . '</br>';
							echo esc_attr($billing_address_city) . ', ' . esc_attr($billing_address_state) . '-' . esc_attr($billing_address_pincode) . '</br>';
							echo esc_attr($billing_address_country) . '</br>';
							?>
                        </p>
						<?php
						if(!empty($BillingPhoneNumber)){
							?>
                            <p class="woocommerce-customer-details--phone"><?php echo $BillingPhoneNumber; ?></p>

							<?php
						}
						?>

						<?php
						if(!empty($BillingEmail)){
							?>
                            <p class="woocommerce-customer-details--email"><?php echo $BillingEmail; ?></p>

							<?php
						}
						?>

					<?php } ?>
				<?php } ?>
            </address>
        </div>
    </div>
    <div class="transaction-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
        <h2 class="transaction-details__title transaction-history-title"><?php _e('Item Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
            <tr>
                <th class="transaction-table-header"><?php echo !empty( $ItemName) ? $ItemName : ''; ?></th>
                <td class="transaction-table-header"><?php echo wc_price($NetAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Shipping Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header""><?php echo wc_price($ShippingAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Handling Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo wc_price($HandlingAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Tax Amount','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo wc_price($TaxAmount, array('currency' => $transaction_currency)); ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Grand Total','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo wc_price($GrandTotal, array('currency' => $transaction_currency)); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="payment-details transaction-history-field" style="width: 100%;display: block;margin: 0 0 10px 0;padding: 0;">
        <h2 class="transaction-details__title transaction-history-title" ><?php _e('Transaction Details','usb-swiper'); ?></h2>
        <table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
            <tbody>
            <tr>
                <th class="transaction-table-header"><?php _e('Payment Intent ID','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo !empty( $payment_intent_id ) ? $payment_intent_id : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Payment Intent','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo !empty( $payment_action ) ? $payment_action : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('PayPal Transaction ID','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo !empty( $payment_transaction_id ) ? $payment_transaction_id : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Payment Status','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo !empty( $payment_status ) ? usbswiper_get_payment_status($payment_status) : ''; ?></td>
            </tr>
			<?php if( !empty( $InvoiceID ) ) { ?>
                <tr>
                    <th class="transaction-table-header"><?php _e('Invoice ID','usb-swiper'); ?></th>
                    <td class="transaction-table-header"><?php echo $InvoiceID; ?></td>
                </tr>
			<?php } ?>
            <tr>
                <th class="transaction-table-header"><?php _e('Payment Source','usb-swiper'); ?></th>
				<?php if( !empty( $payment_card_number ) ) {  ?>
                    <td class="transaction-table-header"><?php echo sprintf( '%s (%s) - %s', $payment_card_number, $payment_card_brand, $payment_card_type); ?></td>
				<?php } else { ?>
                    <td class="transaction-table-header"><?php echo $credit_card_number; ?></td>
				<?php } ?>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Payment Created At','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo !empty( $payment_create_time ) ? date('Y/m/d g:i a', strtotime($payment_create_time)) : ''; ?></td>
            </tr>
            <tr>
                <th class="transaction-table-header"><?php _e('Payment Updated At','usb-swiper'); ?></th>
                <td class="transaction-table-header"><?php echo !empty( $payment_update_time ) ? date('Y/m/d g:i a', strtotime($payment_update_time)) : ''; ?></td>
            </tr>
			<?php if( !empty( $transaction_debug_id ) ) { ?>
                <tr>
                    <th class="transaction-table-header"><?php _e('PayPal Debug ID','usb-swiper'); ?></th>
                    <td class="transaction-table-header"><?php echo $transaction_debug_id; ?></td>
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
    <div class="custom-payment-notes signature">
        <br/>
        <br/>
        <p class="signature-text"><?php _e('Signature........................................','ubs-swiper') ; ?>
        </p>
    </div>
</div>
<?php

$send_email_form_fields = array(
	array(
		'type' => 'text',
		'id' => 'billing_email',
		'name' => 'billing_email',
		'label' => __( 'Billing Email:', 'usb-swiper'),
		'attributes' => '',
		'description' => '',
		'readonly' => false,
        'value' => ! empty( $BillingEmail ) ? esc_attr( $BillingEmail ) : ''
	),
    array(
		'type' => 'hidden',
		'id' => 'transaction_id',
		'name' => 'transaction_id',
		'attributes' => '',
		'description' => '',
		'readonly' => false,
		'value' => ! empty( $transaction_id ) ? esc_attr( $transaction_id ) : ''
	),
	array(
		'type' => 'hidden',
		'id' => 'vt_send_email_nonce',
		'name' => 'vt-send-email-nonce',
		'label' => '',
		'value' => wp_create_nonce('vt-send-email-form'),
		'required' => false,
	)
);

if( !empty( $args['is_email_html'] ) ) {
?>
<div class="vt-resend-email-form">
    <div class="vt-resend-email-form-wrapper">
        <div class="close">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="512px" id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" width="512px" xml:space="preserve"><path d="M443.6,387.1L312.4,255.4l131.5-130c5.4-5.4,5.4-14.2,0-19.6l-37.4-37.6c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4  L256,197.8L124.9,68.3c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4L68,105.9c-5.4,5.4-5.4,14.2,0,19.6l131.5,130L68.4,387.1  c-2.6,2.6-4.1,6.1-4.1,9.8c0,3.7,1.4,7.2,4.1,9.8l37.4,37.6c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1L256,313.1l130.7,131.1  c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1l37.4-37.6c2.6-2.6,4.1-6.1,4.1-9.8C447.7,393.2,446.2,389.7,443.6,387.1z"/></svg>
        </div>
        <form id="vt_resend_email_form" method="post" action="" name="vt-resend-email-form">
            <?php
                foreach ($send_email_form_fields as $form_field){
                    echo usb_swiper_get_html_field($form_field);
                }
            ?>
            <span><?php _e( 'Add multiple emails with "," separated', 'usb-swiper'); ?></span>
            <div class="button-wrap">
                <button id="vt_send_email_cancel" type="reset" class="vt-button"><?php _e( 'Cancel', 'usb-swiper'); ?></button>
                <button id="vt_send_email_submit" type="submit" class="vt-button"><?php _e( 'Send Email', 'usb-swiper'); ?></button>
            </div>
        </form>
    </div>
</div>
<?php } ?>