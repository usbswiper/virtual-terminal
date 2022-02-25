<?php
// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option('usb_swiper_settings', true);

$uninstall_settings = !empty($settings['uninstall']) ? $settings['uninstall'] : '';
$remove_data_on_uninstall = !empty($uninstall_settings['remove_data_on_uninstall']) ? $uninstall_settings['remove_data_on_uninstall'] : '';

if( !empty($remove_data_on_uninstall) && $remove_data_on_uninstall == 1 ) {

	delete_option('usb_swiper_settings');
}
