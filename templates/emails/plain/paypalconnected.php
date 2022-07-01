<?php
/**
 * Product available notify email for admin.
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>
    <p><?php echo sprintf( esc_html__( 'Hi %s,', 'usb-swiper' ), esc_html( usbswiper_get_user_name() ) ); ?></p>
    <p><?php echo esc_attr__( 'You have successfully connected your PayPal account to the USBSwiper Virtual Terminal.', 'usb-swiper' ); ?></p>
    <p><?php echo esc_attr__( 'You are ready to process credit cards and save money on fees!', 'usb-swiper' ); ?></p>
    <p><?php echo esc_attr__( "If you have any questions or concerns feel free to submit a ticket to https://usbswiper.com/support and we'll get you taken care of.", 'usb-swiper' ); ?></p>
    <?php
    if ( $additional_content ) {
        echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
    }
    ?>
    <p><?php echo esc_attr__( 'Thanks!', 'usb-swiper' ); ?></p>
    <p><?php echo esc_attr__( '-USBSwiper Team', 'usb-swiper' ); ?></p>
<?php

do_action( 'woocommerce_email_footer', $email );