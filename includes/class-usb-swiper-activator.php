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

            $vt_page = get_page_by_title( 'Virtual Terminal' );
            $vt_paybyinvoice_page = get_page_by_title( 'Pay By Invoice' );

            if( empty( $vt_page ) ){
                $vt_page_id = wp_insert_post( array(
                    'post_title'    => __('Virtual Terminal', 'usb-swiper'),
                    'post_content'  => '[usb_swiper_vt_form]',
                    'post_status'   => 'publish',
                    'post_author'   => 1,
                    'post_type' => 'page'
                ) );
            }else{
                $vt_page_id = $vt_page->ID;
            }

            if( empty( $vt_paybyinvoice_page ) ){
                $vt_paybyinvoice_page_id = wp_insert_post( array(
                    'post_title'    => __('Pay By Invoice', 'usb-swiper'),
                    'post_content'  => '[usb_swiper_pay_by_invoice]',
                    'post_status'   => 'publish',
                    'post_author'   => 1,
                    'post_type' => 'page'
                ) );
            }else{
                $vt_paybyinvoice_page_id = $vt_paybyinvoice_page->ID;
            }

            if( empty( $settings ) && !is_array( $settings )) {
                $settings = array(
                    'general' => array(
                        'virtual_terminal_page' => $vt_page_id,
                        'vt_paybyinvoice_page' => $vt_paybyinvoice_page_id,
                        'is_paypal_sandbox' => false,
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
            }

            $settings['general']['virtual_terminal_page'] = !empty( $settings['general']['virtual_terminal_page'] ) ? $settings['general']['virtual_terminal_page'] : $vt_page_id;
            $settings['general']['vt_paybyinvoice_page'] = !empty( $settings['general']['vt_paybyinvoice_page'] ) ? $settings['general']['vt_paybyinvoice_page'] : $vt_paybyinvoice_page_id;

			update_option('usb_swiper_settings', $settings );
		}
	}
}
