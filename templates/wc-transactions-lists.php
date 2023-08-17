<?php

defined( 'ABSPATH' ) || exit;

do_action( 'usb_swiper_before_transactions', $has_transactions );

if( $has_transactions ) : ?>

    <div class="vt-form-notification"></div>
	<table class="woocommerce-transactions-table woocommerce-MyAccount-transactions shop_table shop_table_responsive my_account_transactions account-transactions-table">
		<thead>
			<tr>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-id"><?php _e('ID','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-title"><?php _e('Title','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-tid"><?php _e('Transaction ID','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-status"><?php _e('Status','usb-swiper'); ?></th>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-intent"><?php _e('Intent','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-total"><?php _e('Total','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-date"><?php _e('Date','usb-swiper'); ?><span id="date-toggle" class="toggle-icon <?php echo isset($_GET['date_toggle']) && $_GET['date_toggle'] === 'asc' ? 'asc' : 'desc'; ?>"><?php echo isset($_GET['date_toggle']) && $_GET['date_toggle'] === 'asc' ? '&#x25B2;' : '&#x25BC;'; ?></span></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-actions"><?php _e('Actions','usb-swiper'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $transactions as $transaction ) {

                $id = !empty($transaction->ID) ? esc_html( $transaction->ID ) : 0;
                $transaction_type = get_post_meta( $id, '_transaction_type', true);
                $user_invoice_id = get_post_meta( $id, '_user_invoice_id', true);
                $grand_total = get_post_meta( $id, 'GrandTotal', true );
                $payment_response = get_post_meta( $id, '_payment_response', true);
                $payment_transaction_id = usbswiper_get_transaction_id($transaction->ID);
                $payment_status = usbswiper_get_transaction_status($transaction->ID);
                $payment_action = usbswiper_get_transaction_type($transaction->ID);
                $transaction_type = usbswiper_get_invoice_transaction_type($transaction->ID);
                $end_point = 'view-transaction';

				$get_refund_status = usbswiper_get_refund_status();

                if( !class_exists('Usb_Swiper_Paypal_request') ) {
                    include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
                }

                $Usb_Swiper_Paypal_request = new Usb_Swiper_Paypal_request();
                $transaction_currency = $Usb_Swiper_Paypal_request->get_transaction_currency( $id );
                ?>
				<tr class="woocommerce-transactions-table__row woocommerce-transactions-table__row--status-<?php echo !empty( $payment_status ) ? esc_attr( strtolower($payment_status) ) : ''; ?> transactions">
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-id"><?php if( 'invoice' === strtolower( $transaction_type ) ){ echo $user_invoice_id; } else { echo $id; } ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-title"><?php echo !empty($transaction->post_title) ? esc_html( $transaction->post_title ) : '-'; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-id"><?php echo !empty( $payment_transaction_id ) ? $payment_transaction_id : ''; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-status"><?php echo !empty( $payment_status ) ? strtoupper(usbswiper_get_payment_status(esc_attr($payment_status))) : '-'; ?></td>
                    <td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-intent"><?php echo !empty( $payment_action ) ? strtoupper(esc_attr( $payment_action )) : '-'; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-total"><?php echo !empty( $grand_total ) ? wc_price(esc_attr( $grand_total ), array('currency' => $transaction_currency)) : '-'; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-date"><?php echo esc_attr( get_the_time( __( 'Y/m/d g:i a' ), $transaction ) ); ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-actions">
						<a href="<?php echo esc_url( wc_get_endpoint_url( $end_point, $id, wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="vt-button view"><?php _e('View', 'usb-swiper'); ?></a>
                        <?php if( usbswiper_is_allow_capture( $id ) && $payment_status !== 'FAILED' ) {
                            $unique_id = usb_swiper_unique_id( array(
                               'type' => $payment_action,
                               'transaction_id' => $id,
                               'paypal_transaction_id' => $payment_response['id'],
                               'nonce' => wp_create_nonce('authorize-transaction-capture')
                            ));
                            ?>
                            <a class="vt-button capture-transaction-button" data-href="<?php echo add_query_arg( array( 'action' => 'capture',  'unique_id' => $unique_id), esc_url( wc_get_endpoint_url( 'view-transaction', $id, wc_get_page_permalink( 'myaccount' ) ) )); ?>"><?php _e('CAPTURE','usb-swiper'); ?></a>
                        <?php } ?>

                        <?php if( !empty( $payment_status ) && in_array( $payment_status, $get_refund_status)) { ?>
                            <button id="send_email_btn_<?php echo $id; ?>" data-transaction_id="<?php echo $id; ?>" class="vt-button send-email-btn" title="<?php _e('Send Email Receipt','usb-swiper'); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></button>
                        <?php } ?>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
    <?php echo refund_confirmation_html(); ?>
	<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
		<?php if ( 1 !== $current_page ) :
            $next_page = $current_page - 1;
		    $next_args = array();
		    if( $next_page > 1 ) {
			    $next_args['vt-page'] = $next_page;
            }
            if( !empty( $_GET['vt-type'] ) ){
                $next_args['vt-type'] = sanitize_text_field( $_GET['vt-type'] );
            }
            if (!empty($_GET['vt-search'])) {
                $next_args['vt-search'] = sanitize_text_field($_GET['vt-search']);
            }
            if (!empty($_GET['start-date'])) {
                $previous_args['start-date'] = sanitize_text_field($_GET['start-date']);
            }
            if (!empty($_GET['end-date'])) {
                $previous_args['end-date'] = sanitize_text_field($_GET['end-date']);
            }
            if( is_wc_endpoint_url('transactions') ){
                $next_page_url = add_query_arg($next_args,  wc_get_endpoint_url( 'transactions' ,'') );
            } elseif ( is_wc_endpoint_url('invoices') ) {
                $next_page_url = add_query_arg($next_args,  wc_get_endpoint_url( 'invoices' ,'') );
            }

            ?>
			<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url($next_page_url); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
		<?php endif; ?>

		<?php if ( intval( $max_num_pages ) !== $current_page ) :
			$previous_page = $current_page + 1;
			$previous_args = array(
				'vt-page' => $previous_page,
            );
            if( !empty( $_GET['vt-type'] ) ){
                $previous_args['vt-type'] = sanitize_text_field( $_GET['vt-type'] );
            }
            if (!empty($_GET['vt-search'])) {
                $previous_args['vt-search'] = sanitize_text_field($_GET['vt-search']);
            }
            if (!empty($_GET['start-date'])) {
                $previous_args['start-date'] = sanitize_text_field($_GET['start-date']);
            }
            if (!empty($_GET['end-date'])) {
                $previous_args['end-date'] = sanitize_text_field($_GET['end-date']);
            }

            if( is_wc_endpoint_url('transactions') ){
                $previous_page_url = add_query_arg($previous_args,  wc_get_endpoint_url( 'transactions' ,'') );
            } elseif ( is_wc_endpoint_url('invoices') ) {
                $previous_page_url = add_query_arg($previous_args,  wc_get_endpoint_url( 'invoices' ,'') );
            }
            ?>
			<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $previous_page_url ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
		<?php endif; ?>
	</div>

<?php else : ?>

	<div class="woocommerce-message woocommerce-message--info woocommerce-info vt-transactions-info">
		<?php esc_html_e( 'No transactions have been made.', 'usb-swiper' ); ?>
	</div>

<?php endif;

do_action( 'usb_swiper_after_transactions', $has_transactions );
