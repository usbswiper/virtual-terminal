<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Email class for refund Email Notification to customer
 *
 * @extends \WC_Email
 */

class UsbSwiperInvoiceEmailRefunded extends WC_Email {

    /**
     * Set email defaults
     */
    public function __construct() {
        $this->customer_email = true;

        // set ID, this simply needs to be a unique name
        $this->id = 'payment_email_refund';

        // this is the title in WooCommerce Email settings
        $this->title = __('Payment Refund Email', 'usb-swiper');

        // this is the description in WooCommerce email settings
        $this->description = __('Email Sent when a Payment is Refunded.', 'usb-swiper');

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = __( '{#transaction_type#} {#transaction_id#} refunded', 'usb-swiper' );
        $this->subject = __( 'Your {#transaction_type#} {#transaction_id#} has been refunded', 'usb-swiper');

        // these define the locations of the templates that this email should use
        $this->template_base  = USBSWIPER_PATH . 'templates/';
        $this->template_html  = 'emails/payment-email-refund.php';
        $this->template_plain = 'emails/plain/payment-email-refund.php';

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();
    }

    /**
     * get_content_html function.
     *
     * @since 1.1.17
     *
     * @return string
     */
    public function get_content_html() {

        return wc_get_template_html(
            $this->template_html,
            array(
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
                'admin_email'        => $this->recipient,
                'profile_args'       => $this->profile_args
            ),
            '',
            $this->template_base,
        );
    }


    /**
     * get_content_plain function.
     *
     * @since 1.1.17
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'email_heading'      => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'      => false,
                'plain_text'         => true,
                'email'              => $this,
                'admin_email'        => $this->recipient,
                'profile_args'       => $this->profile_args
            ),
            '',
            $this->template_base,
        );

    }

    /**
     * Determine if the email has any attachments.
     *
     * @since 1.1.17
     *
     * @return array|mixed|null
     */
    public function get_attachments() {
        $attachments = !empty( $this->attachments ) ? $this->attachments : array();
        return apply_filters( 'woocommerce_email_attachments', $attachments, $this->id, $this->object, $this );
    }

    /**
     * Determine if the email should actually be sent and setup email merge variables.
     *
     * @since 1.1.17
     *
     * @param array $args
     * @return void
     */
    public function trigger( $args ) {

        if ( !$args ) {
            return;
        }

        $this->attachments = !empty( $args['attachment'] ) ? $args['attachment'] : '';

        unset($args['attachment']);

        $this->profile_args = $args;

        if($this->get_recipient()){
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
    }


    /**
     * Admin Notify email form field.
     *
     * @since 1.1.17
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'            => array(
                'title'   => __( 'Enable/Disable', 'usb-swiper' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'usb-swiper' ),
                'default' => 'yes',
            ),
            'subject'            => array(
                'title'       => __( 'Subject', 'usb-swiper' ),
                'type'        => 'text',
                'placeholder' => __( 'Your {#transaction_type#} {#transaction_id#} has been refunded', 'usb-swiper'),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __( 'Email Heading', 'usb-swiper' ),
                'type'        => 'text',
                'placeholder' => __( '{#transaction_type#} {#transaction_id#} refunded', 'usb-swiper' ),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'usb-swiper' ),
                'description' => __( 'Text to appear below the main email content.', 'usb-swiper' ),
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'usb-swiper' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type'         => array(
                'title'   => __( 'Email type', 'usb-swiper' ),
                'type'    => 'select',
                'default' => 'html',
                'class'   => 'email_type',
                'options' => array(
                    'plain'     => __( 'Plain text', 'usb-swiper' ),
                    'html'      => __( 'HTML', 'usb-swiper' ),
                    'multipart' => __( 'Multipart', 'usb-swiper' ),
                ),
            ),
        );
    }
}

return new UsbSwiperInvoiceEmailRefunded();