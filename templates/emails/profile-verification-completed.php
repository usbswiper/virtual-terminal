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

$myaccount_page_url = get_the_permalink($myaccount_page_id);
$myaccount_page_url = !empty( $myaccount_page_url ) ? sanitize_url( $myaccount_page_url ) : '#';

do_action( 'woocommerce_email_header', $email_heading, $email );
$button_background = get_button_background_color($email->recipient,true);
?>
    <div class="verification-email-wrapper">
        <p><?php echo sprintf(__('Hi %s,','usb-swiper'), $user_name); ?></p>
        <p><?php _e("We are pleased to inform you that your account has been verified and is ready be connected to your PayPal account.", 'usb-swiper'); ?></p>
        <p><?php echo sprintf( __("%sPlease login%s to your account using the credentials you provided during registration.", 'usb-swiper') ,'<a target="_blank" href="'.$myaccount_page_url.'">','</a>'); ?></p>
        <p><?php echo sprintf( __("Then click the “%sConnect to PayPal%s” button to continue setting up your account.", 'usb-swiper') ,'<a target="_blank" href="'.$myaccount_page_url.'">','</a>'); ?></p>
        <p><?php _e("You’ll be using your Virtual Terminal within minutes!", 'usb-swiper'); ?></p>
        <p><?php echo sprintf(__("If you have any questions or encounter any issues, please do not hesitate to contact our support team at %s.", 'usb-swiper'),'<a href="mailto:support@usbswiper.atlassian.net">support@usbswiper.atlassian.net</a>'); ?></p>
        <p style="text-align:center;"><a style="display: inline-block;color: #ffffff;border-width: 0;border-radius: 26px;letter-spacing: 1px;font-size: 13px;font-weight: 800;background:<?php echo $button_background; ?>;padding:15px 30px;text-decoration: none;margin-bottom: 10px;cursor: pointer;" href="<?php echo $myaccount_page_url; ?>" target="_blank"><?php echo __('Continue to PayPal Connection', 'usb-swiper'); ?></a></p>
        <p><?php _e("Thank you for choosing our service.", 'usb-swiper'); ?></p>
        <p><?php _e("– USBSwiper Team", 'usb-swiper'); ?></p>
    </div>
<?php
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
