<?php



///**
// * Filter the cart template path to use our cart.php template instead of the theme's
// */
function usbswiper_locate_email_templates( $template, $template_name, $template_path ) {
	$basename = basename( $template );
	if( $basename == 'paypalconnected.php' ) {
		$template = USBSWIPER_PATH . 'templates/emails/paypalconnected.php';
		$template_path = USBSWIPER_PATH . 'templates/emails/' ;

	}

	return $template;
}
add_filter( 'wc_get_template', 'usbswiper_locate_email_templates', 10, 3 );


/**
 * Check usb_swiper_get_settings function exists or not.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'usb_swiper_get_settings' ) ) {

	/**
	 * Get plugin section settings.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section
	 * @param bool   $all
	 *
	 * @return false|mixed|string|void
	 */
	function usb_swiper_get_settings( $section = '', $all = false ) {

		$settings = get_option( 'usb_swiper_settings', true );

		if ( $all ) {
			return $settings;
		}

		return ! empty( $settings[ $section ] ) ? $settings[ $section ] : '';
	}
}

/**
 * Allow user by user role.
 *
 * @since 1.0.0
 *
 * @param string $role
 *
 * @return bool $is_allow_user
 */
function usb_swiper_allow_user_by_role( $role ) {

	$is_allow_user = false;

	if( is_user_logged_in() ) {

		$current_user = wp_get_current_user();
		$roles        = ! empty( $current_user->roles ) ? $current_user->roles : '';

		if( !empty( $roles ) &&  in_array($role, $roles) ) {

			$is_allow_user = true;
		}
	}

	return $is_allow_user;
}

/**
 * Include template in use template dir.
 *
 * @since 1.0.0
 *
 * @param string $template_name Get template name.
 * @param array  $args Get template arguments.
 * @param string $template_path Get template path.
 * @param string $default_path get template default path.
 */
function usb_swiper_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	if ( ! $template_path ) {
		$template_path = untrailingslashit( 'usb-swiper' );
	}

	if ( ! $default_path ) {
		$default_path = USBSWIPER_PATH . '/templates';
	}

	$locate_template_path = untrailingslashit( $template_path ) . '/' . $template_name;

	$template = locate_template( array( $locate_template_path , $template_name ) );

	if ( ! $template ) {
		$template = untrailingslashit( $default_path ) . '/' . $template_name;
	}

	$located = $template;

	if ( ! file_exists( $located ) ) {
		return;
	}

	$located = apply_filters( 'usb_swiper_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'usb_swiper_before_get_template', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'usb_swiper_after_get_template', $template_name, $template_path, $located, $args );
}

/**
 * Get form tab fields.
 *
 * @since 1.0.0
 *
 * @return array $tab_fields
 */
function usb_swiper_get_vt_tab_fields() {

	$tab_fields = array(
		//'swiper' => __( 'Swipe Card' ,'usb-swiper' ),
		'currency_info' => __( 'Currency Information' ,'usb-swiper' ),
		'personal_info' => __( 'Buyer Information' ,'usb-swiper' ),
		'payment_info' => __( 'Payment Information' ,'usb-swiper' ),
		'billing_address' => __( 'Billing Address' ,'usb-swiper' ),
		'shipping_address' => __( 'Shipping Address' ,'usb-swiper' ),
	);

	return apply_filters( 'usb_swiper_get_vt_tab_fields', $tab_fields );
}

/**
 * Get country lists.
 *
 * @since 1.0.0
 *
 * @return array
 */
function usb_swiper_get_countries() {

    return WC()->countries->get_allowed_countries();
}

/**
 * Get state lists.
 *
 * @since 1.0.0
 *
 * @return mixed|void
 */
function usb_swiper_get_states( $country = 'US' ) {
    $get_states = WC()->countries->get_states( $country );

    return !empty( $get_states ) ? array_merge( array('' => __('Select state','usb-swiper')), $get_states) : '';
}

/**
 * Get form fields.
 *
 * @since 1.0.0
 *
 * @param string $tab
 *
 * @return array $form_fields
 */
function usb_swiper_get_vt_form_fields( $tab = '' ) {

    $merchant_data = get_user_meta( get_current_user_id(),'_merchant_onboarding_response', true);
    $country_code = !empty( $merchant_data['country'] ) ? $merchant_data['country'] : 'US';
	$get_countries = usb_swiper_get_countries();
	$get_states = usb_swiper_get_states($country_code);

	$form_fields = array(
		'swiper' => apply_filters( 'usb_swiper_swipe_card_fields', array(
			array(
				'type' => 'password',
				'id' => 'swiper',
				'name' => 'swiper',
				'label' => __( 'Click to Swipe', 'usb-swiper'),
				'required' => false,
				'attributes' =>'',
				'class' => '',
				'description' => sprintf( __( 'Note: A %1$sUSB credit card reader%2$s is required for swipe functionality.','usb-swiper'), '<a target="_blank" href="https://www.usbswiper.com/usbswiper-usb-magnetic-stripe-credit-card-reader.html?utm_source=angelleye&utm_medium=paypal-pos&utm_campaign=usbswiper">' ,'</a>')
			)
		)),
		'currency_info' => apply_filters( 'usb_swiper_payment_info_fields1', array(
			array(
				'type' => 'select',
				'id' => 'TransactionCurrency',
				'name' => 'TransactionCurrency',
				'label' => __( 'Currency', 'usb-swiper'),
				'required' => true,
				'options' => usbswiper_get_currency_code_options(),
				'default' => usbswiper_get_default_currency(),
				'attributes' => '',
				'description' => '',
				'readonly' => false,
				'disabled' => false,
				'class' => 'usbswiper-change-currency',
			))),
		'personal_info' => apply_filters( 'usb_swiper_personal_info_fields', array(
			array(
				'type' => 'text',
				'id' => 'company',
				'name' => 'company',
				'label' => __( 'Company Name', 'usb-swiper'),
				'required' => false,
				'options' => array(),
				'attributes' => array(
					//'maxlength' => 25
				),
				'class' => '',
			),
			array(
				'type' => 'text',
				'id' => 'BillingFirstName',
				'name' => 'BillingFirstName',
				'label' => __( 'First Name', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 35
				),
				'description' => '',
				'class' => '',
			),
			array(
				'type' => 'text',
				'id' => 'BillingLastName',
				'name' => 'BillingLastName',
				'label' => __( 'Last Name', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 35
				),
				'description' => '',
				'class' => '',
			),
			array(
				'type' => 'text',
				'id' => 'BillingEmail',
				'name' => 'BillingEmail',
				'label' => __( 'Email Address', 'usb-swiper'),
				'required' => false,
				'options' => array(),
				'attributes' => array(
					//'maxlength' => 25
				),
				'class' => '',
			),

		)),
		'payment_info' => apply_filters( 'usb_swiper_payment_info_fields', array(
			array(
				'type' => 'select',
				'id' => 'TransactionType',
				'name' => 'TransactionType',
				'label' => __( 'Transaction Type', 'usb-swiper'),
				'required' => true,
				'options' => array(
					'capture' => __( 'Capture', 'usb-swiper' ),
					'authorize' => __( 'Authorize', 'usb-swiper' ),
				),
				'default' => 'capture',
				'attributes' => '',
				'description' => '',
				'readonly' => false,
				'disabled' => false,
				'class' => '',
			),
			array(
				'type' => 'text',
				'id' => 'NetAmount',
				'name' => 'NetAmount',
				'label' => __( 'Net Order Amount', 'usb-swiper'),
				'required' => true,
				'is_currency' => true,
				'attributes' => array(
					'pattern' => '([0-9]|\$|,|.)+'
				),
				'description' => '',
				'class' => '',
				'is_symbol' => true,
				'symbol' => usbswiper_get_currency_symbol(),
				'symbol_wrap_class' => 'currency-sign'
			),
			array(
				'type' => 'text',
				'id' => 'ShippingAmount',
				'name' => 'ShippingAmount',
				'label' => __( 'Shipping Amount', 'usb-swiper'),
				'required' => false,
				'is_currency' => true,
				'attributes' => array(
					'pattern' => '([0-9]|\$|,|.)+'
				),
				'description' => '',
				'class' => 'currency-sign',
				'is_symbol' => true,
				'symbol' => usbswiper_get_currency_symbol(),
				'symbol_wrap_class' => 'currency-sign'
			),
			array(
				'type' => 'text',
				'id' => 'HandlingAmount',
				'name' => 'HandlingAmount',
				'label' => __( 'Handling Amount', 'usb-swiper'),
				'required' => false,
				'is_currency' => true,
				'attributes' => array(
					'pattern' => '([0-9]|\$|,|.)+'
				),
				'description' => '',
				'class' => 'currency-sign',
				'is_symbol' => true,
				'symbol' => usbswiper_get_currency_symbol(),
				'symbol_wrap_class' => 'currency-sign'
			),
			array(
				'type' => 'text',
				'id' => 'TaxRate',
				'name' => 'TaxRate',
				'label' => __( 'Tax Rate', 'usb-swiper'),
				'required' => false,
				'is_percentage' => true,
				'attributes' => array(
					'maxlength' => '4'
				),
				'description' => '',
				'class' => 'tax-rate-sign',
				'is_symbol' => true,
				'symbol' => '%',
				'symbol_wrap_class' => 'currency-sign after'
			),
			array(
				'type' => 'text',
				'id' => 'TaxAmount',
				'name' => 'TaxAmount',
				'label' => __( 'Tax Amount', 'usb-swiper'),
				'required' => false,
				'readonly' => true,
				'attributes' => '',
				'description' => '',
				'class' => 'currency-sign',
				'is_symbol' => true,
				'symbol' => usbswiper_get_currency_symbol(),
				'symbol_wrap_class' => 'currency-sign'
			),
			array(
				'type' => 'text',
				'id' => 'GrandTotal',
				'name' => 'GrandTotal',
				'label' => __( 'Grand Total', 'usb-swiper'),
				'required' => false,
				'readonly' => true,
				'attributes' => '',
				'description' => '',
				'class' => 'currency-sign',
				'is_symbol' => true,
				'symbol' => usbswiper_get_currency_symbol(),
				'symbol_wrap_class' => 'currency-sign'
			),
			array(
				'type' => 'text',
				'id' => 'InvoiceID',
				'name' => 'InvoiceID',
				'label' => __( 'Invoice Number', 'usb-swiper'),
				'required' => false,
				'attributes' => array(
					'maxlength' => '35'
				),
				'description' => '',
				'class' => '',
			),
			array(
				'type' => 'text',
				'id' => 'ItemName',
				'name' => 'ItemName',
				'label' => __( 'ItemName', 'usb-swiper'),
				'required' => false,
				'attributes' => array(
					'maxlength' => '70'
				),
				'description' => '',
				'class' => '',
			),
			array(
				'type' => 'textarea',
				'id' => 'Notes',
				'name' => 'Notes',
				'label' => __( 'Notes', 'usb-swiper'),
				'required' => false,
				'attributes' => array(
					'maxlength' => '127'
				),
				'description' => '',
				'class' => '',

			),
		)),
		'billing_address' => apply_filters( 'usb_swiper_billing_address_fields', array(
			array(
				'type' => 'checkbox',
				'id' => 'billingInfo',
				'name' => 'billingInfo',
				'label' => __( 'Enter Billing Address', 'usb-swiper'),
				'required' => false,
				'value' => "true",
				'checked' => true,
				'attributes' => array(
					'data-default-checked' => 'FALSE'
				),
				'description' => '',
				'class' => '',
			),
			array(
				'type' => 'text',
				'id' => 'BillingStreet',
				'name' => 'BillingStreet',
				'label' => __( 'Street', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-billing-address-field',
			),
			array(
				'type' => 'text',
				'id' => 'BillingStreet2',
				'name' => 'BillingStreet2',
				'label' => __( 'Street 2', 'usb-swiper'),
				'required' => false,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-billing-address-field',
			),
			array(
				'type' => 'text',
				'id' => 'BillingCity',
				'name' => 'BillingCity',
				'label' => __( 'City', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-billing-address-field',
			),
			array(
				'type' => 'select',
				'id' => 'BillingState',
				'name' => 'BillingState',
				'label' => __( 'State', 'usb-swiper'),
				'required' => true,
				'attributes' => '',
				'options' => $get_states,
				'description' => '',
				'class' => 'vt-billing-address-field vt-billing-states',
			),
			array(
				'type' => 'text',
				'id' => 'BillingPostalCode',
				'name' => 'BillingPostalCode',
				'label' => __( 'Postal Code', 'usb-swiper'),
				'required' => true,
				'options' => array(),
				'attributes' => array(
					'maxlength' => 25
				),
				'class' => 'vt-billing-address-field',
			),
			array(
				'type' => 'select',
				'id' => 'BillingCountryCode',
				'name' => 'BillingCountryCode',
				'label' => __( 'Country', 'usb-swiper'),
				'required' => true,
				'attributes' => '',
				'options' => $get_countries,
				'description' => '',
                'default' => $country_code,
				'class' => 'vt-billing-address-field vt-billing-country',
			),
			array(
				'type' => 'text',
				'id' => 'BillingPhoneNumber',
				'name' => 'BillingPhoneNumber',
				'label' => __( 'Phone Number', 'usb-swiper'),
				'required' => false,
				'options' => array(),
				'attributes' => array(
					'maxlength' => 25
				),
				'class' => 'vt-billing-address-field',
			),
		)),
		'shipping_address' => apply_filters( 'usb_swiper_shipping_address_fields', array(
			array(
				'type' => 'checkbox',
				'id' => 'shippingDisabled',
				'name' => 'shippingDisabled',
				'label' => __( 'Shipping Not Req.', 'usb-swiper'),
				'required' => false,
				'value' => "true",
				'attributes' => array(
					'data-default-checked' => "TRUE"
				),
				'description' => '',
				'class' => '',
			),
			array(
				'type' => 'checkbox',
				'id' => 'shippingSameAsBilling',
				'name' => 'shippingSameAsBilling',
				'label' => __( 'Same as Billing', 'usb-swiper'),
				'required' => false,
				'value' => "true",
				'attributes' => array(
					'data-default-checked' => "TRUE"
				),
				'description' => '',
				'class' => 'vt-enable-shipping-field',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingFirstName',
				'name' => 'ShippingFirstName',
				'label' => __( 'First Name', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingLastName',
				'name' => 'ShippingLastName',
				'label' => __( 'Last Name', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingStreet',
				'name' => 'ShippingStreet',
				'label' => __( 'Street', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingStreet2',
				'name' => 'ShippingStreet2',
				'label' => __( 'Street 2', 'usb-swiper'),
				'required' => false,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingCity',
				'name' => 'ShippingCity',
				'label' => __( 'City', 'usb-swiper'),
				'required' => true,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
			array(
				'type' => 'select',
				'id' => 'ShippingState',
				'name' => 'ShippingState',
				'label' => __( 'State', 'usb-swiper'),
				'required' => true,
				'options' => $get_states,
				'attributes' => '',
				'description' => '',
				'class' => 'vt-shipping-address-field vt-shipping-states',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingPostalCode',
				'name' => 'ShippingPostalCode',
				'label' => __( 'Postal Code', 'usb-swiper'),
				'required' => true,
				'attributes' => '',
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
			array(
				'type' => 'select',
				'id' => 'ShippingCountryCode',
				'name' => 'ShippingCountryCode',
				'label' => __( 'Country', 'usb-swiper'),
				'required' => true,
				'options' => $get_countries,
				'attributes' => '',
				'description' => '',
                'default' => $country_code,
				'class' => 'vt-shipping-address-field vt-shipping-country',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingPhoneNumber',
				'name' => 'ShippingPhoneNumber',
				'label' => __( 'Phone Number', 'usb-swiper'),
				'required' => false,
				'attributes' => array(
					'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
			array(
				'type' => 'text',
				'id' => 'ShippingEmail',
				'name' => 'ShippingEmail',
				'label' => __( 'Email Address', 'usb-swiper'),
				'required' => false,
				'attributes' => array(
					//'maxlength' => 25
				),
				'description' => '',
				'class' => 'vt-shipping-address-field',
			),
		)),
	);

	$form_fields = apply_filters( 'usb_swiper_get_vt_form_fields', $form_fields );

	if( !empty( $tab ) ) {

		return !empty( $form_fields[$tab] ) ? $form_fields[$tab] : array();
	}

	return $form_fields;
}

/**
 * Get input field html.
 *
 * @since 1.0.0
 *
 * @param array $field
 *
 * @return string $html
 */
function usb_swiper_get_html_field( $field ) {

	if( empty( $field ) ) {
		return '';
	}

	$Input_Fields = new Usb_Swiper_Input_Fields();

	$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

	ob_start();

	if ( method_exists( $Input_Fields, $type ) ) {

		echo $Input_Fields->$type( $field );

	} else {

		do_action( 'usb_swiper_get_html_field', $field );

		do_action( 'usb_swiper_get_html_field_' . $type, $field );
	}

	$html = ob_get_contents();
	ob_get_clean();

	return $html;
}

/**
 * Current logged in merchant details.
 *
 * @since 1.0.0
 *
 * @return array
 */
function usbswiper_get_onboarding_user() {

	$merchant_user = array();

	if( is_user_logged_in() ) {
		$merchant_user = get_user_meta( get_current_user_id(),'_merchant_onboarding_user',true);
		$merchant_user = !empty( $merchant_user ) ? json_decode(base64_decode( ($merchant_user))) : '';
	}

	/*if( isset( $_COOKIE['merchant_onboarding_user'] ) && !empty( $_COOKIE['merchant_onboarding_user'] ) ) {
		$merchant_user = json_decode(base64_decode( ($_COOKIE['merchant_onboarding_user'])));
	}*/

	return !empty( $merchant_user ) ? (array)$merchant_user : '';
}

/**
 * Get partner fee based on cart total.
 *
 * @since 1.0.0
 *
 * @param float|int $cart_total
 *
 * @return float|int $platform_fees
 */
function usbswiper_get_platform_fees( $cart_total ) {


	if( !is_user_logged_in() || empty( $cart_total ) ){
		return 0;
	}

	$user_id = get_current_user_id();
	$exclude_partner_users = get_option('get_exclude_partner_users');
	if( !empty( $exclude_partner_users ) && is_array( $exclude_partner_users ) && in_array( $user_id, $exclude_partner_users) ) {
		return 0;
	}
	$billing_country = get_user_meta( $user_id, 'billing_country', true);
	$merchant_response = get_user_meta( $user_id, '_merchant_onboarding_response', true);
	$merchant_country = !empty( $merchant_response['country'] ) ? $merchant_response['country'] :'';
	$settings = usb_swiper_get_settings('partner_fees');
	$fees = !empty( $settings['fees']) ? $settings['fees'] : '';
	$default_partner_percentage = !empty( $settings['default_partner_percentage']) ? $settings['default_partner_percentage'] : '';

	$country = !empty( $billing_country ) ? $billing_country : $merchant_country;

	$country_fees = array();
	if( !empty( $fees ) && is_array( $fees )) {

		foreach ( $fees as $key => $fee ) {

			$country_code = !empty( $fee['country_code'] ) ? $fee['country_code'] : '';
			if( !empty( $country_code ) ) {

				$country_fees[$country_code] = !empty( $fee['percentage'] ) ? $fee['percentage'] : '';
			}
		}
	}

	$percentage = $default_partner_percentage;
	if( isset( $country_fees[$country] ) && !empty( $country_fees[$country] ) ) {
		$percentage = $country_fees[$country];
	}
	if( !empty( $percentage ) && $percentage > 0 ) {
		$platform_fees = ( $cart_total * $percentage ) / 100;
	}

	return !empty( $platform_fees ) ? number_format( $platform_fees, 2, '.', '' ) : 0;
}

if (!function_exists('usb_swiper_key_generator')) {

	function usb_swiper_key_generator() {
		$key = md5(microtime());
		$new_key = '';
		for ($i = 1; $i <= 19; $i++) {
			$new_key .= $key[$i];
			if ($i % 5 == 0 && $i != 19)
				$new_key .= '';
		}
		return strtoupper($new_key);
	}
}


if (!function_exists('usb_swiper_set_session')) {

	function usb_swiper_set_session($key, $value) {

		if (!class_exists('WooCommerce') || WC()->session == null) {
			return false;
		}

		$usb_swiper_ppcp_session = WC()->session->get('usb_swiper_ppcp_session');
		if (!is_array($usb_swiper_ppcp_session)) {
			$usb_swiper_ppcp_session = array();
		}

		$usb_swiper_ppcp_session[$key] = $value;

		WC()->session->set('usb_swiper_ppcp_session', $usb_swiper_ppcp_session);
	}
}


if (!function_exists('usb_swiper_get_session')) {

	function usb_swiper_get_session($key) {

		if (!class_exists('WooCommerce') || WC()->session == null) {
			return false;
		}

		$usb_swiper_ppcp_session = WC()->session->get('usb_swiper_ppcp_session');

		return !empty($usb_swiper_ppcp_session[$key]) ? $usb_swiper_ppcp_session[$key] : false;
	}
}

if( !function_exists('usb_swiper_unique_id')) {

	function usb_swiper_unique_id( $args ) {

		if( empty( $args ) ) {
			return;
		}

		if( is_array($args) ) {

			$temp_args = array();

			foreach ( $args as $key => $value ) {
				$temp_args[] = $key.':'.$value;
			}

			$unique_id = implode(',', $temp_args);
		} else {
			$unique_id = $args;
		}

		return !empty( $unique_id ) ? base64_encode($unique_id): '';
	}
}

if( !function_exists( 'usb_swiper_get_unique_id_data') ) {

	function usb_swiper_get_unique_id_data( $unique_id  ) {

		if( empty( $unique_id ) ) {
			return;
		}

		$unique_id = base64_decode($unique_id);

		$unique_id_data = array();
		if( !empty( $unique_id ) ) {
			$unique_id = explode(',', $unique_id );
			if( is_array( $unique_id ) ) {
				foreach ( $unique_id as $value ) {
					if( !empty( $value )) {
						$unique_id = explode( ':', $value );
						$data_key = !empty( $unique_id[0] ) ? $unique_id[0] : '';
						$data_value = !empty( $unique_id[1] ) ? $unique_id[1] : '';
						if( !empty( $data_key ) && !empty( $data_value ) ) {
							$unique_id_data[ $data_key ] = $data_value;
						}
					}
				}
			}
		}

		return $unique_id_data;
	}
}

function usbswiper_get_currency_code_options() {

	$currency_code_options = get_woocommerce_currencies();

	foreach ( $currency_code_options as $code => $name ) {
		$currency_code_options[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
	}

	return $currency_code_options;
}

function usbswiper_get_default_currency( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$currency = 'USD';
	if( is_user_logged_in() ) {
		$currency = get_user_meta( $user_id, '_primary_currency', true);
		$currency = !empty( $currency ) ? $currency : 'USD';
	}

	if( isset($_GET['TransactionCurrency']) && !empty( $_GET['TransactionCurrency'])) {
		$currency = esc_html($_GET['TransactionCurrency']);
	}

	return $currency;
}

function usbswiper_get_currency_symbol() {

	$currency = usbswiper_get_default_currency();

	return get_woocommerce_currency_symbol( $currency );
}

function usbswiper_round_amount( $price, $precision ) {
	$round_price = round($price, $precision);
	return number_format($round_price, $precision, '.', '');
}

function usbswiper_get_payment_status( $status ) {

	if( empty( $status ) ) {
		return '';
	}

	return str_replace( array('_','-'),' ', $status);
}

function usbswiper_get_refund_status() {

	return apply_filters('usbswiper_get_refund_status', array('COMPLETED','PARTIALLY_REFUNDED'));
}

function get_total_refund_amount( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return;
	}

	$GrandTotal = get_post_meta( $transaction_id, 'GrandTotal', true);

	$payment_response = get_post_meta( $transaction_id,'_payment_response', true);

	if( empty( $payment_response ) ) {
		return;
	}

	$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
	$payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
	$captures = !empty( $payment_details['captures'] ) ? $payment_details['captures'] : '';
	$refunds = !empty( $payment_details['refunds'] ) ? $payment_details['refunds'] : '';

	$total_refund_amount = 0;
	if( !empty( $refunds ) && is_array( $refunds ) ) {
		foreach ( $refunds as $key => $refund ) {

			if( !empty( $refund['amount']['value'] ) && $refund['amount']['value'] > 0 ) {
				$total_refund_amount = $total_refund_amount + $refund['amount']['value'];
			}

		}
	}

	$remaining_amount = $GrandTotal - $total_refund_amount;

	$args = array(
		'ex_tax_label'       => false,
		'currency'           => '',
		'decimal_separator'  => wc_get_price_decimal_separator(),
		'thousand_separator' => wc_get_price_thousand_separator(),
		'decimals'           => wc_get_price_decimals(),
		'price_format'       => get_woocommerce_price_format(),
	);

	return !empty( $remaining_amount ) ? number_format( $remaining_amount, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ) : '';
}

function usbswiper_get_transaction_type( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$TransactionType = get_post_meta($transaction_id,'TransactionType', true);
	$TransactionType = !empty( $TransactionType ) ? $TransactionType : 'capture';

	return strtoupper( $TransactionType );
}

function usbswiper_get_transaction_status( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);
	$status = !empty( $payment_response['status'] ) ? $payment_response['status'] : '';

	$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
	$payments = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
	$captures = !empty( $payments['captures'][0] ) ? $payments['captures'][0] : '';
	$authorizations = !empty( $payments['authorizations'][0] ) ? $payments['authorizations'][0] : '';

	if ( !empty( $captures ) && is_array($captures) && !empty( $captures['id'] ) ) {
		$status = !empty( $captures['status']) ? $captures['status'] : '';
	}elseif( !empty( $authorizations ) && is_array($authorizations) && !empty( $authorizations['id'] ) ) {
		$status = !empty( $authorizations['status']) ? $authorizations['status'] : '';
	}

	return $status;
}

function usbswiper_get_intent_id( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);

	return !empty( $payment_response['id'] ) ? $payment_response['id'] : '';
}

function usbswiper_get_transaction_id( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);

	$payment_transaction_id = !empty( $payment_response['id'] ) ? $payment_response['id'] : '';

	$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
	$payments = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
	$captures = !empty( $payments['captures'][0] ) ? $payments['captures'][0] : '';
	$authorizations = !empty( $payments['authorizations'][0] ) ? $payments['authorizations'][0] : '';
	if ( !empty( $captures ) && is_array($captures) && !empty( $captures['id'] ) ) {
		$payment_transaction_id = $captures['id'];
	}elseif( !empty( $authorizations ) && is_array($authorizations) && !empty( $authorizations['id'] ) ) {
		$payment_transaction_id = $authorizations['id'];
	}

	return $payment_transaction_id;
}

function usbswiper_get_transaction_datetime( $transaction_id, $type = 'create_time' ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);

	$date_time = !empty( $payment_response[$type] ) ? $payment_response[$type] : '';

	$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
	$payments = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
	$captures = !empty( $payments['captures'][0] ) ? $payments['captures'][0] : '';
	$authorizations = !empty( $payments['authorizations'][0] ) ? $payments['authorizations'][0] : '';
	if ( !empty( $captures ) && is_array($captures) && !empty( $captures['id'] ) ) {
		$date_time = !empty( $captures[$type] ) ? $captures[$type] : '';
	}elseif( !empty( $authorizations ) && is_array($authorizations) && !empty( $authorizations['id'] ) ) {
		$date_time = !empty( $authorizations[$type] ) ? $authorizations[$type] : '';
	}

	return $date_time;

}

function usbswiper_get_locale() {

	$merchant_data = get_user_meta( get_current_user_id(),'_merchant_onboarding_response', true);
	$country_code = !empty( $merchant_data['country'] ) ? $merchant_data['country'] : '';

	if( empty( $country_code ) ) {
		return'';
	}

	$locale_info = include WC()->plugin_path() . '/i18n/locale-info.php';
	$country_locale = !empty( $locale_info[$country_code] ) ? $locale_info[$country_code] : '';

	return !empty( $country_locale['default_locale'] ) ? str_replace('_','-', $country_locale['default_locale']) : '';
}

function usbswiper_get_brand_name() {
	$company_name = get_user_meta( get_current_user_id(),'brand_name', true);
	return !empty( $company_name ) ? $company_name : get_bloginfo('name');
}

function usbswiper_is_allow_capture( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return false;
	}

	$payment_action = usbswiper_get_transaction_type($transaction_id);

	/*if( empty( $payment_action) || 'AUTHORIZE' !== $payment_action ) {
		return false;
	}*/

	$is_allow_capture = false;
	$payment_status = usbswiper_get_transaction_status($transaction_id);

	if( !empty( $payment_status ) && 'CREATED' === $payment_status ) {
		$is_allow_capture = true;
	}

	return $is_allow_capture;
}

function usb_swiper_price_formatter( $price ) {

    if( !empty( $price ) ) {
        $price = str_replace(',','', $price);
    }

    return $price;
}

/**
 * function to return user's name
 */

function usbswiper_get_user_name(){

    $user_name = '';
    if( is_user_logged_in() ) {
        $current_user = wp_get_current_user();

        $display_name = !empty( $current_user->display_name ) ? $current_user->display_name : '';
        $user_name =  !empty( $current_user->user_firstname ) ? $current_user->user_firstname : $display_name;
    }

	return $user_name;
}

if( !function_exists('usb_swiper_get_field_value') ) {

    function usb_swiper_get_field_value( $field , $tab ='general') {

        if( empty( $field ) ) {
            return '';
        }

        $settings = usb_swiper_get_settings($tab);

        return  !empty( $settings[$field] ) ? $settings[$field]: '';
    }
}

/**
 * Get button background color by email domain.
 *
 * @since 1.1.17
 *
 * @param string $email_id get email id
 * @param boolean $is_email
 * @return string
 */
function get_button_background_color( $email_id, $is_email = false ) {
    $background_color = 'linear-gradient(243deg,#3D72E7 0%,#53a0fe 100%)';
    if( !empty($email_id) && strpos(strtolower($email_id), '@outlook.com') !== false && $is_email){
        $background_color = '#53a0fe';
    }
    return $background_color;
}

/**
 * Get user address in single line.
 *
 * @since 1.1.17
 *
 * @param $user_id
 * @return string
 */
function get_user_address($user_id) {

    $merchant_business_street = get_user_meta($user_id ,'billing_address_1', true);
    $merchant_business_street2 = get_user_meta($user_id ,'billing_address_2', true);
    $merchant_business_city = get_user_meta($user_id ,'billing_city', true);
    $merchant_business_state = get_user_meta($user_id ,'billing_state', true);
    $merchant_business_postal_code = get_user_meta($user_id ,'billing_postcode', true);
    $merchant_business_country_code = get_user_meta($user_id ,'billing_country', true);

    $merchant_business_street = !empty( $merchant_business_street ) ? $merchant_business_street : '';
    $merchant_business_street2 = !empty( $merchant_business_street2 ) ? $merchant_business_street2 : '';
    $merchant_business_city = !empty( $merchant_business_city ) ? $merchant_business_city : '';
    $merchant_business_state = !empty( $merchant_business_state ) ? $merchant_business_state : '';
    $merchant_business_postal_code = !empty( $merchant_business_postal_code ) ? $merchant_business_postal_code : '';
    $merchant_business_country_code = !empty( $merchant_business_country_code ) ? $merchant_business_country_code : '';

    $merchant_address = $merchant_business_street;
    $merchant_address .= !empty( $merchant_address ) ? ', '.$merchant_business_street2 : '';
    $merchant_address .= !empty( $merchant_address ) ? ', '.$merchant_business_city : '';
    $merchant_address .= !empty( $merchant_address ) ? ', '.$merchant_business_state : '';
    $merchant_address .= !empty( $merchant_address ) ? ', '.$merchant_business_country_code : '';
    $merchant_address .= !empty( $merchant_address ) ? '. '.$merchant_business_postal_code : '';

    return !empty( $merchant_address ) ? $merchant_address : '';
}