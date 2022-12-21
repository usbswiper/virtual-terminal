<?php

if( ! empty( $_GET['product-id'] ) ){
    $product             = wc_get_product( $_GET['product-id'] );
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
        'class' => 'vt-input-field'
    ),
    array(
        'type' => 'textarea',
        'id' => 'description',
        'name' => 'description',
        'placeholder' => __( 'Description:', 'usb-swiper'),
        'attributes' => '',
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
        'attributes' => '',
        'description' => '',
        'readonly' => false,
        'value' => ! empty( $product_price ) ? $product_price : '',
        'class' => 'vt-input-field'
    ),
    array(
        'type' => 'text',
        'id' => 'sku',
        'name' => 'sku',
        'placeholder' => __( 'SKU:', 'usb-swiper'),
        'attributes' => '',
        'description' => '',
        'readonly' => false,
        'value' => ! empty( $product_sku ) ? $product_sku : '',
        'class' => 'vt-input-field'
    ),
    array(
        'type' => 'file',
        'id' => 'vt_product_image',
        'name' => 'vt-product-image',
        'label' => __( 'Image:', 'usb-swiper'),
        'required' => false,
        'class' => 'p-2'
    ),
    array(
        'type' => 'hidden',
        'id' => 'vt_product_action',
        'name' => 'vt-product-action',
        'label' => '',
        'value' => 'add',
        'required' => false,
    ),
    array(
        'type' => 'hidden',
        'id' => 'vt_add_product_form_nonce',
        'name' => 'vt-add-product-form-nonce',
        'label' => '',
        'value' => wp_create_nonce('vt-add-product-form'),
        'required' => false,
    )
);

?>
    <div class="vt-form-notification"></div>
    <div class="vt-products" style="width: 100%;">
        <button id="vt_add_product" class="vt-button-primary vt-add-product"><?php _e('Add Product','usb-swiper'); ?></button>
    </div>
    <div class="vt-product-wrapper">
        <div class="vt-product-inner">
            <div class="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <form id="vt_add_product_form" method="post" action="" name="vt-add-product-form" enctype="multipart/form-data">
                <?php
                foreach ( $add_product_form_fields as $form_field ){
                    echo usb_swiper_get_html_field( $form_field );
                }
                ?>
                <?php if( ! empty( $product_image_id ) ) { ?>
                    <div class="input-field-wrap">
                        <label><?php _e( 'Product Image:', 'usb-swiper'); ?></label>
                        <img height="50px" width="50px" src="<?php echo wp_get_attachment_image_url( $product_image_id );?>" alt="Product-image"/>
                    </div>
                <?php } ?>
                <div class="button-wrap">
                    <button id="vt_add_product_cancel" type="reset" class="vt-button-primary"><?php _e( 'Cancel', 'usb-swiper'); ?></button>
                    <?php if( ! empty( $_GET['action'] ) ) { ?>
                    <button id="vt_add_product_submit" type="submit" class="vt-button-primary"><?php _e( 'Save', 'usb-swiper'); ?></button>
                    <?php } else { ?>
                    <button id="vt_add_product_submit" type="submit" class="vt-button-primary"><?php _e( 'Add Product', 'usb-swiper'); ?></button>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>


<?php if( ! empty( $args['product'] ) ) : ?>
    <table cellpadding="0" cellspacing="0" class="my-account-products-listings-table">
        <thead>
        <tr>
            <th class="text-center"><?php _e('ID','usb-swiper'); ?></th>
            <th><?php _e('Title','usb-swiper'); ?></th>
            <th><?php _e('Price','usb-swiper'); ?></th>
            <th><?php _e('Product Image','usb-swiper'); ?></th>
            <th><?php _e('Description','usb-swiper'); ?></th>
            <th><?php _e('SKU','usb-swiper'); ?></th>
            <th><?php _e('Action','usb-swiper'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $args['product'] as $product ) {
            $product_data = wc_get_product( $product->ID );
            ?>
            <tr>
                <td class="text-center"><?php echo $product->ID;?></td>
                <td><?php echo $product->post_title;?></td>
                <td class="text-center"><?php echo $product_data->get_price();?></td>
                <td class="text-center">
                    <?php if( ! empty( $product_data->get_image_id() ) ) { ?>
                    <img height="50px" width="50px" src="<?php echo wp_get_attachment_image_url( $product_data->get_image_id() );?>" alt="Product-image"/>
                    <?php } ?>
                </td>
                <td><?php echo $product_data->get_description();?></td>
                <td class="text-center"><?php echo $product_data->get_sku();?></td>
                <td class="text-center">
                    <a href="<?php echo '?action=edit&product-id='.$product->ID;?>" class="vt_update_product" data-id="<?php echo $product->ID;?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                    </a>
                    <a class="vt_delete_product" data-id="<?php echo $product->ID;?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </a>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
<?php endif; ?>