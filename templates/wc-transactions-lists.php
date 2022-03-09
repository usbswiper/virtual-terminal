<?php

defined( 'ABSPATH' ) || exit;

do_action( 'usb_swiper_before_transactions', $has_transactions );

if( $has_transactions ) : ?>

	<table class="woocommerce-transactions-table woocommerce-MyAccount-transactions shop_table shop_table_responsive my_account_transactions account-transactions-table">
		<thead>
			<tr>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-id"><?php _e('ID','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-title"><?php _e('Title','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-tid"><?php _e('Transaction ID','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-status"><?php _e('Status','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-type"><?php _e('Type','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-total"><?php _e('Total','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-date"><?php _e('Date','usb-swiper'); ?></th>
				<th class="woocommerce-orders-table__header woocommerce-orders-table__header-actions"><?php _e('Actions','usb-swiper'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $transactions as $transaction ) {

                    $id = !empty($transaction->ID) ? esc_html( $transaction->ID ) : 0;
                    $grand_total = get_post_meta( $id, 'GrandTotal', true );
                    $payment_response = get_post_meta( $id, '_payment_response', true);
                    $purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
                    $payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
                    $payment_captures = !empty( $payment_details['captures'][0] ) ? $payment_details['captures'][0] : '';
                    $payment_authorizations = !empty( $payment_details['authorizations'][0] ) ? $payment_details['authorizations'][0] : '';
                    $payment_status = !empty( $payment_authorizations['status'] ) ? $payment_authorizations['status'] : '';
				    $payment_action = 'AUTHORIZE';
                    if( !empty( $payment_captures ) && !empty( $payment_captures['id'] ) ) {
	                    $payment_action = 'CAPTURE';
                        $payment_status = !empty( $payment_captures['status'] ) ? $payment_captures['status'] : '';
                    }

                    if( !class_exists('Usb_Swiper_Paypal_request') ) {
                        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
                    }
                    $Usb_Swiper_Paypal_request = new Usb_Swiper_Paypal_request();
                    $transaction_currency = $Usb_Swiper_Paypal_request->get_transaction_currency( $id );
                    ?>
				<tr class="woocommerce-transactions-table__row woocommerce-transactions-table__row--status-<?php echo esc_attr( $payment_status ); ?> transactions">
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-id"><?php echo $id; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-title"><?php echo !empty($transaction->post_title) ? esc_html( $transaction->post_title ) : '-'; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-id"><?php echo !empty( $payment_response['id'] ) ? $payment_response['id'] : ''; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-status"><?php echo !empty( $payment_status ) ? usbswiper_get_payment_status(esc_attr($payment_status)) : '-'; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-type"><?php echo !empty( $payment_action ) ? strtoupper(esc_attr( $payment_action )) : '-'; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-total"><?php echo !empty( $grand_total ) ? wc_price(esc_attr( $grand_total ), array('currency' => $transaction_currency)) : '-'; ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-date"><?php echo esc_attr( get_the_time( __( 'Y/m/d g:i a' ), $transaction ) ); ?></td>
					<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-transaction-actions">
						<a href="<?php echo esc_url( wc_get_endpoint_url( 'view-transaction', $id, wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="vt-button view"><?php _e('View', 'usb-swiper'); ?></a>
                        <?php if( !empty( $payment_status ) && !empty( $payment_action ) && 'completed' !== strtolower( $payment_status ) && 'authorize' === strtolower( $payment_action ) ) {
                            $unique_id = usb_swiper_unique_id( array(
                               'type' => $payment_action,
                               'transaction_id' => $id,
                               'paypal_transaction_id' => $payment_response['id'],
                               'nonce' => wp_create_nonce('authorize-transaction-capture')
                            ));
                            ?>
                            <a class="vt-button capture-transaction" href="<?php echo add_query_arg( array( 'action' => 'capture',  'unique_id' => $unique_id), esc_url( wc_get_endpoint_url( 'view-transaction', $id, wc_get_page_permalink( 'myaccount' ) ) )); ?>"><?php _e('CAPTURE','usb-swiper'); ?></a>
                        <?php } ?>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
		<?php if ( 1 !== $current_page ) :
            $next_page = $current_page - 1;
		    $next_args = array();
		    if( $next_page > 1 ) {
			    $next_args['vt-page'] = $next_page;
            }
		    $next_page_url = add_query_arg($next_args,  wc_get_endpoint_url( 'transactions' ,'') );
            ?>
			<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url($next_page_url); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
		<?php endif; ?>

		<?php if ( intval( $max_num_pages ) !== $current_page ) :
			$previous_page = $current_page + 1;
			$previous_args = array(
				'vt-page' => $previous_page,
            );

			$previous_page_url = add_query_arg($previous_args,  wc_get_endpoint_url( 'transactions' ,'') );
            ?>
			<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $previous_page_url ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
		<?php endif; ?>
	</div>

<?php else : ?>

	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<?php esc_html_e( 'No transactions have been made.', 'usb-swiper' ); ?>
	</div>

<?php endif;

do_action( 'usb_swiper_after_transactions', $has_transactions );
