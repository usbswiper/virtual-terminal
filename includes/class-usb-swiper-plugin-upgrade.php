<?php

/**
 * Check Usb_Swiper_Upgrade class exists or not.
 *
 * @since 4.1.6
 */
if( !class_exists( 'Usb_Swiper_Upgrade' ) ) {

    /**
     * The upgrade requirement functionality of the plugin.
     *
     * @link       http://www.angelleye.com/product/usb-swiper
     * @since      4.1.6
     *
     * @package    usb-swiper
     * @author     AngellEYE <andrew@angelleye.com>
     */
    class Usb_Swiper_Upgrade {

        /**
         * Add actions on upgrade the plugin.
         *
         * @return void
         */
        public static function vt_upgrade_plugin() {
            self::create_customer_table();
        }

        /**
         * Create a table on plugin update.
         *
         * It will generate the customers and customer_meta tables on plugin update if the tables do not exist.
         *
         * @return void
         */
        public static function create_customer_table() {
            $db_version = get_option('vt_plugin_db_version', '1.0.0');

            if (version_compare($db_version, USBSWIPER_VERSION, '<')) {
                require_once USBSWIPER_PATH . '/includes/class-usb-swiper-activator.php';
                Usb_Swiper_Activator::create_customer_table();
                update_option('vt_plugin_db_version', USBSWIPER_VERSION);
            }
        }
    }
}