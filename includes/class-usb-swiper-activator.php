<?php

/**
 * Check Usb_Swiper_Activator class exists or not.
 *
 * @since 1.0.0
 */
if( !class_exists( 'Usb_Swiper_Activator' ) ) {

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    usb-swiper
	 * @subpackage usb-swiper/includes
	 * @author     AngellEYE <andrew@angelleye.com>
	 */
	class Usb_Swiper_Activator {

		/**
		 * Add the code on plugin activate.
		 *
		 * @since   1.0.0
		 */
		public static function activate() {

            $settings = get_option( 'usb_swiper_settings' );

            if( empty( $settings ) ) {

                $vt_page = post_exists('Virtual Terminal');
                $vt_page_id = ( !empty( $vt_page ) && (int)$vt_page  > 0 ) ? $vt_page : 0;
                if( empty( $vt_page ) ) {
                    $vt_page_id = wp_insert_post(array(
                        'post_title' => __('Virtual Terminal', 'usb-swiper'),
                        'post_content' => '[usb_swiper_vt_form]',
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_type' => 'page'
                    ));
                }

                $vt_verification_page = post_exists('Virtual Terminal Verification');
                $vt_verification_page_id = ( !empty( $vt_verification_page ) && (int)$vt_verification_page  > 0 ) ? $vt_verification_page : 0;
                if( empty( $vt_verification_page ) ) {
                    $vt_verification_page_id = wp_insert_post(array(
                        'post_title' => __('Virtual Terminal Verification', 'usb-swiper'),
                        'post_content' => '[usb_swiper_vt_verification_form]',
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_type' => 'page'
                    ));
                }

                $vt_paybyinvoice_page = post_exists( 'Pay By Invoice' );
                $vt_paybyinvoice_page_id = ( !empty( $vt_paybyinvoice_page ) && (int)$vt_paybyinvoice_page  > 0 ) ? $vt_paybyinvoice_page : 0;
                if( empty( $vt_paybyinvoice_page ) ){
                    $vt_paybyinvoice_page_id = wp_insert_post( array(
                        'post_title'    => __('Pay By Invoice', 'usb-swiper'),
                        'post_content'  => '[usb_swiper_pay_by_invoice]',
                        'post_status'   => 'publish',
                        'post_author'   => 1,
                        'post_type' => 'page'
                    ) );
                }

                $settings = array(
                    'general' => array(
                        'virtual_terminal_page' => $vt_page_id,
                        'vt_verification_page' => $vt_verification_page_id,
                        'vt_paybyinvoice_page' => $vt_paybyinvoice_page_id,
                        'is_paypal_sandbox' => false,
                        'paypal_partner_logo_url' => 'https://www.usbswiper.com/img/usbswiper-logo-300x89.png',
                    ),
                    'partner_fees' => array(
                        'fees' => array(
                            array(
                                'country_code' => 'AU',
                                'percentage' => '',
                            ),
                            array(
                                'country_code' => 'AT',
                                'percentage' => '',
                            ),
                            array(
                                'country_code' => 'DE',
                                'percentage' => '',
                            ),
                            array(
                                'country_code' => 'GB',
                                'percentage' => '',
                            ),
                            array(
                                'country_code' => 'US',
                                'percentage' => '',
                            ),
                        )
                    ),
                    'uninstall' => array(
                        'remove_data_on_uninstall' => true,
                    ),
                );

                update_option('usb_swiper_settings', $settings);
            }
		}
	}
}
