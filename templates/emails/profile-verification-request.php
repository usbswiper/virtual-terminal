<?php
/**
 * Product available notify email for admin.
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$user_id = !empty( $profile_args['user_id'] ) ? $profile_args['user_id'] : '';
$user_name = !empty( $profile_args['user_name'] ) ? $profile_args['user_name'] : '';
$profile_link = add_query_arg( array( 'user_id' => $user_id ), get_edit_user_link( get_current_user_id() ));
$settings     = usb_swiper_get_settings( 'general' );
$vt_page_id   = ! empty( $settings['virtual_terminal_page'] ) ? (int) $settings['virtual_terminal_page'] : '';
$vt_page_link = get_the_permalink( $vt_page_id );
$myaccount_page_id = (int)get_option( 'woocommerce_myaccount_page_id' );

$merchant_name = get_user_meta($user_id ,'billing_first_name', true);
$merchant_email = get_user_meta($user_id ,'billing_email', true);
$merchant_phone = get_user_meta($user_id ,'billing_phone', true);
$merchant_business_name = get_user_meta($user_id ,'billing_company', true);
$merchant_business_address = get_user_meta($user_id ,'billing_address_1', true);

do_action( 'woocommerce_email_header', $email_heading, $email );
?>
    <div class="verification-email-wrapper">
        <p><?php echo sprintf(__('Hello %s,','usb-swiper'), 'Admin'); ?></p>
        <p><?php _e("A new merchant has applied for approval. Please review the following information and verify the merchant's account:", "usb-swiper"); ?></p>
        <p><?php echo sprintf( __("Merchant Name: %s", "usb-swiper"), $merchant_name); ?></p>
        <p><?php echo sprintf( __("Email Address: %s", "usb-swiper"), $merchant_email); ?></p>
        <p><?php echo sprintf( __("Phone Number: %s", "usb-swiper"), $merchant_phone); ?></p>
        <p><?php echo sprintf( __("Business Name: %s", "usb-swiper"), $merchant_business_name); ?></p>
        <p><?php echo sprintf( __("Business Address: %s", "usb-swiper"), $merchant_business_address); ?></p>
        <p><?php _e("Please underwrite this user and click below to verify them when ready.", "usb-swiper"); ?></p>
        <p style="text-align: center;display: block;"><a style="display: inline-block;color: #ffffff;border-width: 0;border-radius: 26px;letter-spacing: 1px;font-size: 13px;font-weight: 800;text-transform: uppercase;background-image: linear-gradient(243deg,#3D72E7 0%,#53a0fe 100%);padding:15px 30px;text-decoration: none;display: inline-block;margin-bottom: 10px;cursor: pointer;" target='_blank' href="<?php echo $profile_link.'#verify_data'; ?>"><?php _e('VERIFY MERCHANT','usb-swiper'); ?></a></p>
    </div>
    <?php
    if ( $additional_content ) {
        echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
    }

do_action( 'woocommerce_email_footer', $email );
