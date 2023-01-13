<?php
$user_id = $args['user_id'];
$user_name = $args['user_name'];
$verification_type = $args['verification_type'];
$profile_link = add_query_arg( array( 'user_id' => $user_id ), get_edit_user_link( get_current_user_id() ));
$settings     = usb_swiper_get_settings( 'general' );
$vt_page_id   = ! empty( $settings['virtual_terminal_page'] ) ? (int) $settings['virtual_terminal_page'] : '';
$vt_page_link = get_permalink( $vt_page_id );
?>

<div class="verification-email-wrapper">
    <?php if( ! empty( $verification_type ) && 'verification_started' === $verification_type ) {?>
    <p>Hello Admin,</p>
    <p><?php echo $user_name;?>, has submitted profile for verification.</p>
    <p>Kindly, visit the page for profile review: <a href="<?php echo $profile_link;?>"><?php echo $profile_link;?></a></p>
    <p>Thank You!</p>
    <p>--</p>
    <p>USBSwiper Team</p>
    <?php } elseif( ! empty( $verification_type ) && 'verification_completed' === $verification_type ) {?>
    <p>Hello <?php echo $user_name;?>,</p>
    <p>Your profile verification is completed. Your profile is now approved now.</p>
    <p>Kindly, visit the page to start the transaction: <a href="<?php echo $vt_page_link;?>"><?php echo $vt_page_link;?></a></p>
    <p>If you have any questions or concerns feel free to submit a ticket to <a href="https://usbswiper.com/support">https://usbswiper.com/support</a> and we'll get you taken care of.</p>
    <p>Thank You!</p>
    <p>--</p>
    <p>USBSwiper Team</p>
    <?php } ?>
</div>