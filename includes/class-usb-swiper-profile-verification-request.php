<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Email class for profile verification request Notification
 *
 * @extends \WC_Email
 */
class Usb_Swiper_Profile_Verification_Request_Email extends WC_Email {

    /**
     * Set email defaults options.
     */
    public function __construct() {

        $this->customer_email = false;

        // set ID, this simply needs to be a unique name
        $this->id = 'paypal_profile_verification_request';

        // this is the title in WooCommerce Email settings
        $this->title = __( "Merchant Profile Verification", 'usb-swiper');

        // this is the description in WooCommerce email settings
        $this->description = __( 'Email Sent when a merchant has applied for profile approval.', 'usb-swiper');

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = __( "Profile Verification", 'usb-swiper');
        $this->subject = __( "Profile Verification", 'usb-swiper');

        $this->recipient = $this->get_option( 'recipient' );

        if ( ! $this->recipient ) {
            $this->recipient = 'richard@usbswiper.com,andrew@usbswiper.com';
        }

        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
        $this->template_base  = USBSWIPER_PATH . 'templates/';
        $this->template_html  = 'emails/profile-verification-request.php';
        $this->template_plain = 'emails/plain/profile-verification-request.php';

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();
    }

    /**
     * get_content_html function.
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
                'profile_args'       => $this->profile_args,
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
                'profile_args'       => $this->profile_args,
                'admin_email'        => $this->recipient,
            ),
            '',
            $this->template_base,
        );

    }

    /**
     * Determine if the email should actually be sent and setup email merge variables
     */
    public function trigger( $args ) {

        if ( !$args ) {
            return;
        }

        $this->profile_args = $args;

        if($this->get_recipient()){
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        }
    }

    /**
     * Admin Notify email form field.
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
                'description' => sprintf( __( 'Email Sent when a merchant has applied for profile approval. Defaults to %s.', 'usb-swiper' ), '<code>richard@usbswiper.com,andrew@usbswiper.com</code>' ),
                'placeholder' => '',
                'default'     => $this->get_recipient(),
                'desc_tip'    => true,
            ),
            'subject'            => array(
                'title'       => __( 'Subject', 'usb-swiper' ),
                'type'        => 'text',
                'placeholder' => __( 'Profile Verification', 'usb-swiper' ),
                'default'     => '',
            ),
            'heading'            => array(
                'title'       => __( 'Email Heading', 'usb-swiper' ),
                'type'        => 'text',
                'placeholder' => __( 'Profile Verification', 'usb-swiper' ),
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
                ),
            ),
        );
    }
}

return new Usb_Swiper_Profile_Verification_Request_Email();