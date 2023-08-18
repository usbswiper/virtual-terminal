<?php
$action = !empty( $_GET['action'] ) ? esc_html( $_GET['action'] ) : 'add';
$product_id = !empty( $_GET['product-id'] ) ? esc_html( $_GET['product-id'] ) : 0;

$product_name = '';
$product_description = '';
$product_price = '';
$product_sku = '';
$product_image_id = '';

if( ! empty( $product_id ) ){
    $product             = wc_get_product( $product_id );
    $product_name        = $product->get_name();
    $product_description = $product->get_description();
    $product_price       = $product->get_price();
    $product_sku         = $product->get_sku();
    $product_image_id    = $product->get_image_id();
}

$add_product_form_fields = array(
    array(
        'type' => 'text',
        'id' => 'product_name',
        'name' => 'product-name',
        'placeholder' => __( 'Product Name:', 'usb-swiper'),
        'attributes' => '',
        'description' => '',
        'readonly' => false,
        'value' => ! empty( $product_name ) ? $product_name : '',
        'class' => 'vt-input-field',
        'required' => true,
    ),
    array(
        'type' => 'textarea',
        'id' => 'description',
        'name' => 'description',
        'placeholder' => __( 'Description:', 'usb-swiper'),
        'attributes' => array(
            'rows'=>'5',
        ),
        'description' => '',
        'readonly' => false,
        'value' => ! empty( $product_description ) ? $product_description : '',
        'class' => 'vt-input-field vt-textarea-field'
    ),
    array(
        'type' => 'number',
        'id' => 'price',
        'name' => 'price',
        'placeholder' => __( 'Price:', 'usb-swiper'),
        'attributes' => array(
            "step" => usbswiper_get_price_step(),
        ),
        'description' => '',
        'readonly' => false,
        'value' => ! empty( $product_price ) ? $product_price : '',
        'class' => 'vt-input-field',
        'is_symbol' => true,
        'symbol' => usbswiper_get_currency_symbol(),
        'symbol_wrap_class' => 'currency-sign',
        'required' => true,
    ),
    array(
        'type' => 'text',
        'id' => 'sku',
        'name' => 'sku',
        'placeholder' => __( 'SKU:', 'usb-swiper'),
        'attributes' => '',
        'description' => '',
        'readonly' => false,
        'value' => ! empty( $product_sku ) ? usbswiper_get_product_sku($product_sku, true) : '',
        'class' => 'vt-input-field'
    ),
    array(
        'type' => 'file',
        'id' => 'vt_product_image',
        'name' => 'vt-product-image',
        'label' => __( 'Upload Image', 'usb-swiper'),
        'required' => false,
        'class' => 'p-2 vt-image-upload',
        'wrapper_class' => 'vt-image-upload-wrap'
    )
);

?>
<div class="vt-form-notification"></div>
<div class="vt-products" style="width: 100%;">
    <button id="vt_add_product" class="vt-button vt-add-product"><?php _e('Add Product','usb-swiper'); ?></button>
</div>
<div class="vt-product-wrapper">
    <div class="vt-product-inner">
        <div class="close">
            <a href="<?php echo esc_url( wc_get_endpoint_url( 'vt-products', '', wc_get_page_permalink( 'myaccount' )) );?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></a></div>
        <div class="vt-product-content">
            <?php if( !empty( $action ) && 'view' === $action ) {
                foreach ( $add_product_form_fields as $form_field ){
                    if( !empty( $form_field['type'] ) && 'hidden' === $form_field['type'] ) {
                        return true;
                    }
                    $label = !empty( $form_field['label'] ) ? $form_field['label'] : '';
                    $placeholder = !empty( $form_field['placeholder'] ) ? $form_field['placeholder'] : '';
                    $value = !empty( $form_field['value'] ) ? $form_field['value'] : '';
                    if( !empty( $form_field['type'] ) && 'file' === $form_field['type'] ) {
                        $image_url = wp_get_attachment_image_url( $product_image_id );
                        $label = __( 'Image:', 'usb-swiper' );
                        $value = "<img src='{$image_url}' alt=''>";
                    }

                    if( !empty( $form_field['id'] ) && 'price' === $form_field['id'] ) {
                        $value = wc_price( $value, ['currency' => usbswiper_get_default_currency()] );
                    }
                    ?>
                    <div class="view-product" id="view_<?php echo !empty( $form_field['id'] ) ? $form_field['id'] : ''; ?>">
                        <div class="label"><?php echo !empty( $label ) ? $label : $placeholder; ?></div>
                        <div class="value"><?php echo !empty( $value ) ? $value : ''; ?></div>
                    </div>
                    <?php
                }
            } else { ?>
                <div class="vt-form-notification"></div>
                <form id="vt_add_product_form" method="post" action="" name="vt-add-product-form" enctype="multipart/form-data">
                    <?php
                    foreach ( $add_product_form_fields as $form_field ){
                        echo usb_swiper_get_html_field( $form_field );
                    }
                    ?>
                    <?php if( ! empty( $product_image_id ) ) { ?>
                        <div class="input-field-wrap">
                            <div class="upload-image-preview preview">
                                <img src="<?php echo wp_get_attachment_image_url( $product_image_id );?>" alt="Product-image"/>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="input-field-wrap button-wrap">
                        <input type="hidden" name="vt-product-action" id="vt_product_action" value="<?php echo !empty( $action ) ? $action : 'add'; ?>">
                        <input type="hidden" name="vt-add-product-form-nonce" id="vt_add_product_form_nonce" value="<?php echo wp_create_nonce('vt-add-product-form'); ?>">
                        <input type="hidden" name="vt_product_id" value="<?php echo $product_id; ?>">
                        <button id="vt_add_product_cancel" type="reset" class="vt-button"><?php _e( 'Cancel', 'usb-swiper'); ?></button>
                        <?php if( ! empty( $action ) && 'edit' === $action ) { ?>
                            <button id="vt_add_product_submit" type="submit" class="vt-button"><?php _e( 'Update', 'usb-swiper'); ?></button>
                        <?php } else { ?>
                            <button id="vt_add_product_submit" type="submit" class="vt-button"><?php _e( 'Add Product', 'usb-swiper'); ?></button>
                        <?php } ?>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</div>

<div class="vt-product-lists">
    <table cellpadding="0" cellspacing="0" class="my-account-products-listings-table">
        <thead>
            <tr>
                <th class="product-id text-center"><?php _e('ID','usb-swiper'); ?></th>
                <th class="product-title"><?php _e('Title','usb-swiper'); ?></th>
                <th class="product-price text-center"><?php _e('Price','usb-swiper'); ?></th>
                <th class="product-image"><?php _e('Product Image','usb-swiper'); ?></th>
                <th class="product-content"><?php _e('Description','usb-swiper'); ?></th>
                <th class="product-sku text-center"><?php _e('SKU','usb-swiper'); ?></th>
                <th class="product-actions text-center"><?php _e('Action','usb-swiper'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if( ! empty( $args['product'] ) ) {
            foreach ( $args['product'] as $product ) {
                $product_data = wc_get_product( $product->ID );
                ?>
                <tr class="product-item-<?php echo esc_attr($product->ID); ?>">
                    <td class="product-id text-center" data-title="<?php _e('ID','usb-swiper'); ?>"><?php echo $product->ID; ?></td>
                    <td class="product-title" data-title="<?php _e('Title','usb-swiper'); ?>"><?php echo $product->post_title;?></td>
                    <td class="product-price text-center" data-title="<?php _e('Price','usb-swiper'); ?>"><?php echo wc_price($product_data->get_price(), ['currency' => usbswiper_get_default_currency()]);?></td>
                    <td class="product-image text-center" data-title="<?php _e('Product Image','usb-swiper'); ?>">
                        <?php if( ! empty( $product_data->get_image_id() ) ) { ?>
                            <img height="50px" width="50px" src="<?php echo wp_get_attachment_image_url( $product_data->get_image_id() );?>" alt="Product-image"/>
                        <?php } ?>
                    </td>
                    <td class="product-content" data-title="<?php _e('Description','usb-swiper'); ?>"><?php echo usbswiper_set_content_limit($product_data->get_description());?></td>
                    <td class="product-sku text-center" data-title="<?php _e('SKU','usb-swiper'); ?>"><?php echo usbswiper_get_product_sku($product_data->get_sku(), true);?></td>
                    <td class="product-actions text-center" data-title="<?php _e('Action','usb-swiper'); ?>">
                        <a title="<?php _e('View product', 'usb-swiper'); ?>" href="<?php echo esc_url(add_query_arg( array('action'=>'view', 'product-id' => $product->ID), wc_get_endpoint_url( 'vt-products', '', wc_get_page_permalink( 'myaccount' )))); ?>" class="vt_view_product" data-id="<?php echo $product->ID;?>">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </a>
                        <a title="<?php _e('Edit product', 'usb-swiper'); ?>" href="<?php echo esc_url(add_query_arg( array('action'=>'edit', 'product-id' => $product->ID), wc_get_endpoint_url( 'vt-products', '', wc_get_page_permalink( 'myaccount' )))); ?>" class="vt_update_product" data-id="<?php echo $product->ID;?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        </a>
                        <a title="<?php _e('Delete product', 'usb-swiper'); ?>" class="vt_delete_product" data-id="<?php echo $product->ID; ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </a>
                    </td>
                </tr>
            <?php }
            } else {
                ?>
                <tr>
                   <td class="text-center" colspan="7"><?php _e('Products not found.','usb-swiper'); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <div class="vt-pagination">
        <?php
        echo usbswiper_get_pagination([
            'format' => '?vt-page=%#%',
            'max_num_pages' => !empty( $args['max_num_pages'] ) ? $args['max_num_pages'] : 0,
            'current_page' => !empty( $args['current_page'] ) ? $args['current_page'] : 0,
        ]);
        ?>
    </div>
</div>