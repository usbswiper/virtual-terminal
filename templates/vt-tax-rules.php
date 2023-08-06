<?php
    $action = !empty($_POST['action']) ? esc_html($_POST['action']) : 'add';
    $add_product_form_fields = array(
        array(
            'type' => 'text',
            'id' => 'tax_label',
            'name' => 'tax_label',
            'placeholder' => __( 'Tax Label', 'usb-swiper'),
            'attributes' => '',
            'description' => '',
            'readonly' => false,
            'value' => '',
            'class' => 'vt-input-field',
            'required' => true,
        ),
        array(
            'type' => 'number',
            'id' => 'tax_rate',
            'name' => 'tax_rate',
            'placeholder' => __( 'Tax Rate', 'usb-swiper'),
            'attributes' => '',
            'description' => '',
            'readonly' => false,
            'value' => '',
            'class' => 'vt-input-field'
        ),
        array(
            'type' => 'checkbox',
            'id' => 'shipping',
            'name' => 'shipping',
            'label' => __( 'Include Shipping:', 'usb-swiper' ),
            'attributes' => '',
            'description' => '',
            'readonly' => false,
            'value' => '',
            'class' => 'vt-input-field'
        ),
    );
?>
<div class="vt-form-notification"></div>
<div class="vt-taxrule" style="width: 100%;">
    <button id="vt_add_taxrule" class="vt-button"><?php _e('Add Tax Rule','usb-swiper'); ?></button>
</div>
<div class="vt-taxrule-wrapper">
    <div class="vt-taxrule-inner">
        <div class="close">
            <a href="<?php echo esc_url( wc_get_endpoint_url( 'vt-tax-rules', '', wc_get_page_permalink( 'myaccount' )) );?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a>
        </div>
        <div class="vt-taxrule-content">
            <div class="vt-form-notification"></div>
            <form id="vt_add_taxrule_form" method="post" action="" name="vt-add-taxrule-form" enctype="multipart/form-data">
                <?php
                foreach ( $add_product_form_fields as $form_field ){
                    echo usb_swiper_get_html_field( $form_field );
                }
                ?>
                <div class="input-field-wrap button-wrap">
                    <input type="hidden" name="vt-taxrule-action" id="vt_taxrule_action" value="<?php echo !empty( $action ) ? $action : 'add'; ?>">
                    <input type="hidden" name="vt-add-taxrule-form-nonce" id="vt_add_taxrule_form_nonce" value="<?php echo wp_create_nonce('vt-add-taxrule-form'); ?>">
                    <input type="hidden" name="vt_taxrule_id" value="">
                    <button id="vt_add_taxrule_cancel" type="reset" class="vt-button"><?php _e( 'Cancel', 'usb-swiper'); ?></button>
                    <?php if( ! empty( $action ) && 'edit' === $action ) { ?>
                        <button id="vt_add_taxrule_submit" type="submit" class="vt-button"><?php _e( 'Update', 'usb-swiper'); ?></button>
                    <?php } else { ?>
                        <button id="vt_add_taxrule_submit" type="submit" class="vt-button"><?php _e( 'Add Tax Rule', 'usb-swiper'); ?></button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="vt-taxrule-lists">
    <?php
        $user_id = get_current_user_id();

        // Get tax data from usermeta
        
        // $tax_label = get_user_meta($user_id, 'tax_label', true);
        // $tax_rate = get_user_meta($user_id, 'tax_rate', true);
        // $include_shipping = get_user_meta($user_id, 'shipping', true);
    ?>
    <table cellpadding="0" cellspacing="0" id="taxdataTable" class="my-account-taxrule-listings-table">
        <thead>
            <tr>
                <th class="tax-label">Tax Label</th>
                <th class="tax-rate">Tax Rate</th>
                <th class="tax-shipping">Include Shipping</th>
                <th class="tax-actions">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tax_data = get_user_meta($user_id, 'user_tax_data', true);
            
                if ($tax_data && is_array($tax_data)) {
                foreach ($tax_data as $tax_item) {
                        $tax_label = isset($tax_item['tax_label']) ? esc_html($tax_item['tax_label']) : '';
                        $tax_rate = isset($tax_item['tax_rate']) ? esc_html($tax_item['tax_rate']) : '';
                        $include_shipping = isset($tax_item['shipping']) ? ($tax_item['shipping'] ? 'Yes' : 'No') : '';
                    ?>
            <tr>
                <td class="tax-label"><?php echo $tax_label; ?></td>
                <td class="tax-rate"><?php echo $tax_rate; ?></td>
                <td class="tax-shipping"><?php echo ($include_shipping ? 'Yes' : 'No'); ?></td>
                <td class="tax-actions">
                    <a title="<?php _e('Edit Tax Rule', 'usb-swiper'); ?>" href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'tax_label' => $tax_label), wc_get_endpoint_url('vt-tax-rules', '', wc_get_page_permalink('myaccount')))); ?>"  class="vt_update_taxrule">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                    </a>
                    <a title="<?php _e('Delete Tax Rule', 'usb-swiper'); ?>" class="vt_delete_taxrule" data-id="">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </a>
                </td>
            </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>




