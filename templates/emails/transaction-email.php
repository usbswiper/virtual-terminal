<?php
$transaction_id = !empty( $profile_args['transaction_id'] ) ? $profile_args['transaction_id'] : '';

if( empty( $transaction_id)) {
    return;
}
$transaction = get_post($transaction_id);
$transaction_author = !empty( $transaction->post_author ) ? $transaction->post_author : '';

$Usb_Swiper_Public = new Usb_Swiper_Public();
$brand_logo = $Usb_Swiper_Public->add_brand_logo_for_email();



do_action( 'woocommerce_email_header', $email_heading, $email );


$author_name = !empty( $profile_args['email_args']['display_name'] ) ? $profile_args['email_args']['display_name'] : '';



if( empty( $author_name ) ) {
    $user_info = get_user_by( 'id', $transaction_author );
    $author_name = !empty( $user_info->display_name ) ? $user_info->display_name : '';
}
$args = array(
    'transaction_id' => $transaction_id,
    'display_name' => $author_name,
    'is_email' => true,
);



usb_swiper_get_template( 'wc-transaction-history.php', $args );

if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );