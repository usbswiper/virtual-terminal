<?php
$user_id = !empty( $args['user_id'] ) ? $args['user_id'] : '';
$user_name = !empty( $args['user_name'] ) ? $args['user_name'] : '';
$verification_type = !empty( $args['verification_type'] ) ? $args['verification_type'] : '';
$profile_link = add_query_arg( array( 'user_id' => $user_id ), get_edit_user_link( get_current_user_id() ));
$settings     = usb_swiper_get_settings( 'general' );
$vt_page_id   = ! empty( $settings['virtual_terminal_page'] ) ? (int) $settings['virtual_terminal_page'] : '';
$vt_page_link = get_the_permalink( $vt_page_id );
$myaccount_page_id = (int)get_option( 'woocommerce_myaccount_page_id' );

$user = get_user_by('id', $user_id);
$user_web_url = !empty($user->user_url) ? sanitize_url($user->user_url) : '';
$merchant_name = get_user_meta($user_id ,'billing_first_name', true);
$merchant_email = get_user_meta($user_id ,'billing_email', true);
$merchant_phone = get_user_meta($user_id ,'billing_phone', true);
$merchant_business_name = get_user_meta($user_id ,'billing_company', true);

$merchant_address = get_user_address($user_id);
?>

<div class="verification-email-wrapper">

    <?php if( ! empty( $verification_type ) && 'verification_started' === $verification_type ) { ?>

        <p><?php echo sprintf(__('Hello %s,','usb-swiper'), 'Admin'); ?></p>
        <p><?php _e("A new merchant has applied for approval. Please review the following information and verify the merchant's account:", "usb-swiper"); ?></p>
        <p><?php echo sprintf( __("Merchant Name: %s", "usb-swiper"), $merchant_name); ?></p>
        <p><?php echo sprintf( __("Email Address: %s", "usb-swiper"), $merchant_email); ?></p>
        <p><?php echo sprintf( __("Phone Number: %s", "usb-swiper"), $merchant_phone); ?></p>
        <p><?php echo sprintf( __("Business Name: %s", "usb-swiper"), $merchant_business_name); ?></p>
        <?php if( !empty( $user_web_url ) ){ ?>
            <p><?php echo sprintf( __("Website URL: %s", "usb-swiper"), '<a target="_blank" href="'.$user_web_url.'">'.$user_web_url.'</a>'); ?></p>
        <?php } ?>
        <p><?php echo sprintf( __("Business Add ress: %s", "usb-swiper"), $merchant_address); ?></p>
        <p><?php _e("Please underwrite this user and click below to verify them when ready.", "usb-swiper"); ?></p>
        <p style="text-align: center;display: block;"><a style="display: inline-block;color: #ffffff;border-width: 0;border-radius: 26px;letter-spacing: 1px;font-size: 13px;font-weight: 800;text-transform: uppercase;background-image: linear-gradient(243deg,#3D72E7 0%,#53a0fe 100%);padding:15px 30px;text-decoration: none;display: inline-block;margin-bottom: 10px;cursor: pointer;" target='_blank' href="<?php echo $profile_link.'#verify_data'; ?>"><?php _e('VERIFY MERCHANT','usb-swiper'); ?></a></p>

    <?php } elseif( ! empty( $verification_type ) && 'verification_completed' === $verification_type ) {
        $myaccount_page_url = get_the_permalink($myaccount_page_id);
        $myaccount_page_url = !empty( $myaccount_page_url ) ? sanitize_url( $myaccount_page_url ) : '#';
        $button_background = get_button_background_color($merchant_email,true);
        ?>
        <p><?php echo sprintf(__('Hi %s,','usb-swiper'), $user_name); ?></p>
        <p><?php _e("We are pleased to inform you that your account has been verified and is ready be connected to your PayPal account.", 'usb-swiper'); ?></p>
        <p><?php echo sprintf( __("%sPlease login%s to your account using the credentials you provided during registration.", 'usb-swiper') ,'<a target="_blank" href="'.get_the_permalink($myaccount_page_id).'">','</a>'); ?></p>
        <p><?php echo sprintf( __("Then click the “%sConnect to PayPal%s” button to continue setting up your account.", 'usb-swiper') ,'<a target="_blank" href="'.get_the_permalink($myaccount_page_id).'">','</a>'); ?></p>
        <p><?php _e("You’ll be using your Virtual Terminal within minutes!", 'usb-swiper'); ?></p>
        <p><?php echo sprintf(__("If you have any questions or encounter any issues, please do not hesitate to contact our support team at %s.", 'usb-swiper'),'<a href="mailto:support@usbswiper.atlassian.net">support@usbswiper.atlassian.net</a>'); ?></p>
        <p style="text-align:center;"><a style="display: inline-block;color: #ffffff;border-width: 0;border-radius: 26px;letter-spacing: 1px;font-size: 13px;font-weight: 800;background:<?php echo $button_background; ?>;padding:15px 30px;text-decoration: none;margin-bottom: 10px;cursor: pointer;" target="_blank" href="<?php echo $myaccount_page_url; ?>"><?php echo __('Continue to PayPal Connection', 'usb-swiper'); ?></a></p>
        <p><?php _e("Thank you for choosing our service.", 'usb-swiper'); ?></p>
        <p><?php _e("– USBSwiper Team", 'usb-swiper'); ?></p>

    <?php } ?>
</div>