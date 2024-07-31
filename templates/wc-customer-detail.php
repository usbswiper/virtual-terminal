<?php
$tab_fields = [
	'currency_info' => __( 'Currency Information' ,'usb-swiper' ),
	'personal_info' => __( 'Buyer Information' ,'usb-swiper' ),
	'billing_address' => __( 'Billing Address' ,'usb-swiper' ),
	'shipping_address' => __( 'Shipping Address' ,'usb-swiper' ),
	'save_customer_info' => __( 'Save Customer Details' ,'usb-swiper' ),
];

$action = !empty( $args['action'] ) ? esc_attr( $args['action'] ) : 'create';
$field_type = $action === 'view';
$customer_data = !empty( $args['customer_data'] ) ? $args['customer_data'] : [];
$heading_title = __( 'Create new Customer', 'usb-swiper');
if( 'view' === $action ) {
	$heading_title = __( 'View Customer Details', 'usb-swiper');
} elseif ( 'edit' === $action ) {
	$heading_title = __( 'Update Customer Details', 'usb-swiper');
}
?>
<div class="vt-form-notification"></div>
<h2 class="wc-account-title general-info"><?php echo esc_attr( $heading_title ); ?></h2>
<form method="post" action="" name="vt-customer-form" id="vt-customer-form" enctype="multipart/form-data">
    <div class="vt-form-contents">
        <div class="vt-row">
            <div class="vt-col vt-col-100 vt-col-form-fields">
	            <?php
	            if( !empty( $tab_fields ) && is_array( $tab_fields ) ) {

		            foreach ( $tab_fields as $tab_key => $tab_field ) {
			            $form_fields = usb_swiper_get_vt_form_fields( $tab_key );
			            ?>
                        <fieldset>
                            <label><?php echo !empty( $tab_field ) ? $tab_field : ''; ?></label>
                            <div class="vt-fields-wrap">
					            <?php
					            if( !empty( $form_fields ) && is_array( $form_fields ) ) {
						            foreach ( $form_fields as $form_field ) {
                                        $field_id = !empty( $form_field['id'] ) ? $form_field['id'] : '';
                                        $type = !empty( $form_field['type'] ) ? $form_field['type'] : '';
                                        if(!empty( $field_id ) && $field_id === 'TransactionCurrency' ) {
	                                        $form_field['class'] = '';
                                        }
                                        $form_field['readonly'] = $field_type;
							            $form_field['disabled'] = $field_type;
                                        if('BillingEmail' === $field_id) {
	                                        $form_field['required'] = true;
                                        }
                                        if( $type === 'checkbox' ) {
	                                        $form_field['checked'] = !empty( $customer_data[$field_id] );
                                        } else {
	                                        $form_field['value'] = !empty( $customer_data[$field_id] ) ? $customer_data[$field_id] : '';
                                        }

							            echo usb_swiper_get_html_field( $form_field );
						            }
					            }
					            ?>
                            </div>
                        </fieldset>
			            <?php
		            }
	            }
	            ?>
                <div class="actions">
                    <input type="hidden" name="action" value="vt-customer-form">
                    <input type="hidden" name="action_type" value="<?php echo esc_attr( $action ); ?>">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('vt-customer-form'); ?>">
                    <input type="hidden" name="customer_id" value="<?php echo !empty( $args['customer_id'] ) ? esc_attr( $args['customer_id'] ) : ''; ?>">
                    <?php if( 'view' !== $action ) { ?>
                        <button class="vt-button" id="vt_submit_button" type="submit" name=""><?php echo sprintf(__('%s Customer', 'usb-swiper'), ($action == 'edit') ? 'Update': 'Create'); ?></button>
                    <?php } else { ?>
                        <a class="vt-button" href="<?php echo esc_url( wc_get_endpoint_url( 'vt-customers', '', wc_get_page_permalink( 'myaccount' )) ); ?>"><?php _e('View All Customers', 'usb-swiper'); ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</form>