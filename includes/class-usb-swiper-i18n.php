<?php
/**
 * Check Usb_Swiper_i18n class exists or not.
 *
 * @since 1.0.0
 */
if( !class_exists('Usb_Swiper_i18n' ) ) {

	/**
	 * Define the internationalization functionality.
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @since      1.0.0
	 * @package    usb-swiper
	 * @subpackage usb-swiper/includes
	 * @author     AngellEYE <andrew@angelleye.com>
	 */
	class Usb_Swiper_i18n {

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			load_plugin_textdomain(
				'usb-swiper',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);
		}
	}
}