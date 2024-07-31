<?php
$customers = !empty( $args['customers'] ) ? $args['customers'] : [];
$total_pages = !empty( $args['total_pages'] ) ? $args['total_pages'] : 0;
$current_page = !empty( $args['current_page'] ) ? $args['current_page'] : 1;

?>
<div class="vt-form-notification"></div>
<div class="vt-customers" style="width: 100%;text-align: right;">
    <a id="vt_add_customer" title="<?php _e('Create new Customer', 'usb-swiper'); ?>" href="<?php echo esc_url(add_query_arg( array('action'=>'create'), wc_get_endpoint_url( 'vt-customers', '', wc_get_page_permalink( 'myaccount' )))); ?>"  class="vt-button vt-add-customer"><?php _e('Add Customer','usb-swiper'); ?></a>
</div>
<?php
if( !empty( $customers ) ) :

?>
<table class="woocommerce-transactions-table woocommerce-MyAccount-transactions shop_table shop_table_responsive my_account_transactions account-transactions-table">
	<thead>
	<tr>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-id"><?php _e('ID','usb-swiper'); ?></th>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-first_name"><?php _e('First Name','usb-swiper'); ?></th>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-last_name"><?php _e('Last Name','usb-swiper'); ?></th>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-email"><?php _e('Email','usb-swiper'); ?></th>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-company"><?php _e('Company','usb-swiper'); ?></th>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-date"><?php _e('Date','usb-swiper'); ?></th>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-modified_date"><?php _e('Modified Date','usb-swiper'); ?></th>
		<th class="woocommerce-orders-table__header woocommerce-orders-table__header-actions"><?php _e('Actions','usb-swiper'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php if( !empty( $customers ) && is_array( $customers ) ) {

		foreach ( $customers as $key => $customer ) {

            $customer_id = !empty( $customer['customer_id'] ) ? $customer['customer_id'] : 0;
			?>
			<tr class="woocommerce-transactions-table__row customers">
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-id"><?php echo $customer_id; ?></td>
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-first_name"><?php echo !empty( $customer['BillingFirstName'] ) ? $customer['BillingFirstName'] : ''; ?></td>
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-last_name"><?php echo !empty( $customer['BillingLastName'] ) ? $customer['BillingLastName'] : ''; ?></td>
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-email"><?php echo !empty( $customer['BillingEmail'] ) ? $customer['BillingEmail'] : ''; ?></td>
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-company"><?php echo !empty( $customer['company'] ) ? $customer['company'] : ''; ?></td>
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-date"><?php echo !empty( $customer['date'] ) ? $customer['date'] : ''; ?></td>
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-modified_date"><?php echo !empty( $customer['modified_date'] ) ? $customer['modified_date'] : ''; ?></td>
				<td class="woocommerce-transactions-table__cell woocommerce-orders-table__cell-actions">
                    <a title="<?php _e('View Customer', 'usb-swiper'); ?>" href="<?php echo esc_url(add_query_arg( array('action'=>'view', 'customer_id' => $customer_id), wc_get_endpoint_url( 'vt-customers', '', wc_get_page_permalink( 'myaccount' )))); ?>" class="vt_view_customer" data-id="<?php echo $customer_id; ?>">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="#4361ee" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </a>
                    <a title="<?php _e('Edit Customer', 'usb-swiper'); ?>" href="<?php echo esc_url(add_query_arg( array('action'=>'edit', 'customer_id' => $customer_id), wc_get_endpoint_url( 'vt-customers', '', wc_get_page_permalink( 'myaccount' )))); ?>" class="vt_update_customer" data-id="<?php echo $customer_id;?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4361ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                    </a>
                    <a title="<?php _e('Delete Customer', 'usb-swiper'); ?>" href="javascript:void(0);" class="vt_delete_customer" data-id="<?php echo $customer_id; ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f44336" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </a>
                </td>
			</tr>
			<?php
		}
	} ?>
	</tbody>
</table>
<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
    <?php
    if ( 1 !== $current_page ) :
	    $previous_args = [];
	    $previous_page = $current_page - 1;
	    if( $previous_page > 1 ) {
		    $next_args['vt-page'] = $previous_page;
	    }
	    $previous_page_url = add_query_arg( $previous_args,  wc_get_endpoint_url( 'vt-customers' ,'') );
        ?>
        <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url($previous_page_url); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
        <?php
    endif;

    if ( intval( $total_pages ) !== $current_page ) :
	    $next_page = $current_page + 1;
	    $next_args = [
		    'vt-page' => $next_page,
        ];

	    $next_page_url = add_query_arg($next_args,  wc_get_endpoint_url( 'vt-customers' ,'') );
        ?>
        <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( $next_page_url ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
        <?php
    endif;
    ?>
</div>

<?php else : ?>

    <div class="woocommerce-message woocommerce-message--info woocommerce-info vt-transactions-info">
		<?php esc_html_e( 'No customer lists available.', 'usb-swiper' ); ?>
    </div>

<?php endif;