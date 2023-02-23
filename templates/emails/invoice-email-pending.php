<?php
$transaction_id = !empty( $profile_args['transaction_id'] ) ? $profile_args['transaction_id'] : '';
$args = !empty( $profile_args['email_args'] ) ? $profile_args['email_args'] : '';

if( empty( $transaction_id)) {
    return;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

$payment_link = !empty( $args['payment_link'] ) ? $args['payment_link'] : '';
if( empty($args['payment_link']) ){
    $settings = usb_swiper_get_settings('general');
    $paybyinvoice_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';
    $payment_link = add_query_arg(array('invoice-session'=>base64_encode(json_encode(array('id' => "invoice_$transaction_id", 'status' => false))) ),get_the_permalink( $paybyinvoice_id ));
}
$transaction = get_post($transaction_id);
$transaction_author = !empty( $transaction->post_author ) ? $transaction->post_author : '';
$author_name = '';
if( !empty( $transaction_author ) && $transaction_author > 0 ) {
    $user_info = get_user_by( 'id', $transaction_author );
    $author_name = !empty( $user_info->display_name ) ? $user_info->display_name : '';
}
$args = array(
    'transaction_id' => $transaction_id,
    'payment_link' =>  $payment_link,
    'display_name' => $author_name,
    'is_email' => true,
);

usb_swiper_get_template( 'wc-transaction-history.php', $args );

if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );