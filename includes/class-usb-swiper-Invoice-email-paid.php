<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Email class for Invoice Email Paid Notification
 *
 * @since 1.1.9
 * @extends \WC_Email
 */

class UsbSwiperInvoiceEmailPaid extends WC_Email {

    /**
     * Set email defaults
     *
     * @since 1.1.9
     */

    public function __construct() {
        $this->customer_email = true;

        // set ID, this simply needs to be a unique name
        $this->id = 'invoice_email_paid';

        // this is the title in WooCommerce Email settings
        $this->title = 'Invoice Email Paid';

        // this is the description in WooCommerce email settings
        $this->description = __('Email Sent when a user Paid Invoice.', 'usb-swiper');

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = __( 'USBSwiper VT - Invoice Email Paid', 'usb-swiper' );
        $this->subject = __( 'USBSwiper VT - Invoice Email Paid', 'usb-swiper');

        // these define the locations of the templates that this email should use
        $this->template_base  = USBSWIPER_PATH . 'templates/';
        $this->template_html  = 'emails/invoice-email-paid.php';
        $this->template_plain = 'emails/plain/invoice-email-paid.php';

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();


    }

    /**
     * get_content_html function.
     *
     * @return string
     * @since 1.1.9
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
                'order'              => $this->object,
                'admin_email'        => $this->recipient,
            ),
            '',
            $this->template_base,
        );

    }


    /**
     * get_content_plain function.
     *
     * @return string
     * @since 1.1.9
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
                'order'              => $this->object,
                'admin_email'        => $this->recipient,
            ),
            '',
            $this->template_base,
        );

    }

    /**
     * Determine if the email should actually be sent and setup email merge variables
     *
     * @param int $user_id
     *
     * @since 0.1
     */
    public function trigger( $user_id ) {

        // bail if no order ID is present
        if ( ! $user_id ) {
            return;
        }
        if ( $user_id ) {
            $this->object = get_user_by('ID',$user_id);
            $this->user_email         = stripslashes( $this->object->user_email );
            $this->recipient = $this->user_email;
        }
        if($this->get_recipient()){
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        }
    }


    /**
     * Admin Notify email form field.
     *
     * @since 1.0.0
     */
    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'            => array(
                'title'   => __( 'Enable/Disable', 'usb-swiper' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'usb-swiper' ),
                'default' => 'yes',
            ),
            'recipient'          => array(
                'title'       => __( 'Recipient', 'usb-swiper' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'usb-swiper' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject'            => array(
                'title'       => __( 'Subject', 'usb-swiper' ),
                'type'        => 'text',
                'placeholder' => __( 'USBSwiper VT - Invoice Email Paid', 'usb-swiper' ),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __( 'Email Heading', 'usb-swiper' ),
                'type'        => 'text',
                'placeholder' => __( 'USBSwiper VT - Invoice Email Paid', 'usb-swiper' ),
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
return new UsbSwiperInvoiceEmailPaid();