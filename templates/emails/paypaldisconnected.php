<?php
/**
 * Product available notify email for admin.
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

    <p><?php echo sprintf( esc_html__( 'Hi %s,', 'usb-swiper' ), esc_html( usbswiper_get_user_name() ) ); ?></p>	<p><?php echo esc_attr__( 'You have successfully disconnected your PayPal account from the USBSwiper Virtual Terminal.', 'usb-swiper' ); ?></p>
	<p><?php echo esc_attr__( 'Please log in and connect to PayPal again in order to use the Virtual Terminal in the future with the same or any different PayPal account.', 'usb-swiper' ); ?></p>
    <?php
        if ( $additional_content ) {
        echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
    } ?>
    <p><?php echo esc_attr__( 'Thanks!', 'usb-swiper' ); ?></p>
	<p><?php echo esc_attr__( '-USBSwiper Team', 'usb-swiper' ); ?></p>
<?php


do_action( 'woocommerce_email_footer', $email );