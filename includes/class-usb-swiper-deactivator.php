<?php

/**
 * Check Usb_Swiper_Activator class exists or not.
 *
 * @since 1.0.0
 */
if( !class_exists( 'Usb_Swiper_Deactivator' ) ) {

	/**
	 * Fired during plugin deactivation.
	 *
	 * This class defines all code necessary to run during the plugin's deactivation.
	 *
	 * @since      1.0.0
	 * @package    usb-swiper
	 * @subpackage usb-swiper/includes
	 * @author     AngellEYE <andrew@angelleye.com>
	 */
	class Usb_Swiper_Deactivator {

		/**
		 * Add the code on plugin deactivate.
		 *
		 * @since   1.0.0
		 */
		public static function deactivate() {

		}
	}
}
