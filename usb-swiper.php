<?php
/**
 * Plugin Name: USBSwiper
 * Plugin URI: http://www.angelleye.com/product/usb-swiper
 * Description: Create paypal transaction using swiper or manually and manage transactions.
 * Version: 1.1.0
 * Author:  Angell EYE
 * Author URI:  http://www.angelleye.com/
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: usb-swiper
 * Domain Path: /languages
 * Tested up to: 5.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//Define plugin name.
if ( ! defined( 'USBSWIPER_PLUGIN_NAME' ) ) {
	define( 'USBSWIPER_PLUGIN_NAME', 'usb-swiper' );
}

//Define plugin version.
if ( ! defined( 'USBSWIPER_VERSION' ) ) {
	define( 'USBSWIPER_VERSION', '1.0.0' );
}

// Define plugin dir.
if (!defined('USBSWIPER_PLUGIN_DIR')) {
	define('USBSWIPER_PLUGIN_DIR', dirname(__FILE__));
}

//Define plugin dir url.
if ( ! defined( 'USBSWIPER_URL' ) ) {
	define( 'USBSWIPER_URL', plugin_dir_url( __FILE__ ) );
}

//Define plugin dir path.
if ( ! defined( 'USBSWIPER_PATH' ) ) {
	define( 'USBSWIPER_PATH', plugin_dir_path( __FILE__ ) );
}

//Define plugin basename.
if( !defined('USBSWIPER_BASENAME')) {
	define('USBSWIPER_BASENAME', plugin_basename( __FILE__ ));
}

// Define partner live merchant id.
if (!defined('USBSWIPER_PARTNER_MERCHANT_ID')) {
	define('USBSWIPER_PARTNER_MERCHANT_ID', '4XQXFNHGHLK7J');
}

// Define partner sandbox merchant id.
if (!defined('USBSWIPER_SNADBOX_PARTNER_MERCHANT_ID')) {
	define('USBSWIPER_SNADBOX_PARTNER_MERCHANT_ID', 'QEV4T5D83THAJ');
}

// Define plugin live paypal partner client id.
if( !defined('USBSWIPER_PAYPAL_PARTNER_CLIENT_ID')) {
	define('USBSWIPER_PAYPAL_PARTNER_CLIENT_ID','');
}

// Define plugin live paypal partner client sandbox secret.
if( !defined('USBSWIPER_PAYPAL_PARTNER_CLIENT_SECRET')) {
	define('USBSWIPER_PAYPAL_PARTNER_CLIENT_SECRET','');
}

// Define plugin sandbox paypal partner client sandbox id.
if( !defined('USBSWIPER_PAYPAL_SANDBOX_PARTNER_CLIENT_ID')) {
	define('USBSWIPER_PAYPAL_SANDBOX_PARTNER_CLIENT_ID','AV1AYU6p6U4lJDNWthB68AJYInU2zD_9rIQS6Q-9gPNZBQk__Aak31mBqoL5RmoUQIUy4rS19OuH3dlp');
}

// Define plugin sandbox paypal partner client sandbox secret.
if( !defined('USBSWIPER_PAYPAL_SANDBOX_PARTNER_CLIENT_SECRET')) {
	define('USBSWIPER_PAYPAL_SANDBOX_PARTNER_CLIENT_SECRET','EKZN1w3VqHEezFpP6y6kQWuOFizP7297hVFI1wdM-pmoozz4uJyLKF5xl7PmpOP6aRlI_WYEqfZigGh2');
}

// Define plugin paypal partner logo url.
if( !defined('USBSWIPER_PAYPAL_PARTNER_LOGO')) {
	define('USBSWIPER_PAYPAL_PARTNER_LOGO', 'https://www.usbswiper.com/img/usbswiper-logo-300x89.png');
}

if( !defined('USBSWIPER_PAYPAL_PARTNER_ATTRIBUTION_ID')) {
	define('USBSWIPER_PAYPAL_PARTNER_ATTRIBUTION_ID', '');
}

if( !defined('USBSWIPER_PAYPAL_SANDBOX_PARTNER_ATTRIBUTION_ID')) {
	define('USBSWIPER_PAYPAL_SANDBOX_PARTNER_ATTRIBUTION_ID', 'FLAVORsb-wm93913837016_MP');
}

/**
 * Check activate_usb_swiper function exists or not.
 *
 * @since 1.0.0
 */
if( !function_exists( 'activate_usb_swiper' ) ) {

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-usb-swiper-activator.php
	 *
	 * @since 1.0.0
	 */
	function activate_usb_swiper() {

		/**
		 * Check deactivate_plugins function exists or not.
		 * if function not exists then include plugin.php file.
		 *
		 * @since 1.0.0
		 */
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = (array) get_option( 'active_plugins', array() );

		/**
		 * Check activate WooCommerce plugins or not.
		 * if WooCommerce plugins not activate then USBSwiper plugin deactivate and display notice.
		 *
		 * @since 1.0.0
		 */
		if ( empty( $active_plugins ) || ! in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {

			deactivate_plugins( plugin_basename( __FILE__ ) );

			wp_die( sprintf( __( '%1$s requires %2$sWooCommerce%3$s plugin to be installed and active. go to %4$s Plugin %5$s page.', 'usb-swiper' ), 'USBSwiper', '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>', '<a href="'.esc_url(admin_url( 'plugins.php' )).'">', '</a>' ) );

		} else {

			require_once 'includes/class-usb-swiper-activator.php';
			Usb_Swiper_Activator::activate();
		}
	}
}
register_activation_hook( __FILE__, 'activate_usb_swiper' );

/**
 * Check deactivate_usb_swiper function exists or not.
 *
 * @since 1.0.0
 */
if( !function_exists('deactivate_usb_swiper' ) ) {

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-usb-swiper-deactivator.php
	 *
	 * @since 1.0.0
	 */
	function deactivate_usb_swiper() {

		require_once 'includes/class-usb-swiper-deactivator.php';
		Usb_Swiper_Deactivator::deactivate();

	}
}

register_deactivation_hook( __FILE__, 'deactivate_usb_swiper' );

require_once 'includes/class-usb-swiper.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

add_action( 'plugins_loaded', 'load_usb_swiper' );

if( !function_exists( 'load_usb_swiper' ) ) {

	function load_usb_swiper() {

		$plugin = new Usb_Swiper();
		$plugin->run();
	}
}