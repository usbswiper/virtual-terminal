<?php

/**
 * Filter the cart template path to use our cart.php template instead of the theme's
 *
 * @since 1.1.17
 */
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
		'personal_info' => __( 'Buyer Information' ,'usb-swiper' ),
		'payment_info' => __( 'Payment Information' ,'usb-swiper' ),
		'billing_address' => __( 'Billing Address' ,'usb-swiper' ),
		'shipping_address' => __( 'Shipping Address' ,'usb-swiper' ),
		'save_customer_info' => __( 'Save Customer Details' ,'usb-swiper' ),
	);

	return apply_filters( 'usb_swiper_get_vt_tab_fields', $tab_fields );
}

/**
 * Get sub tabs for report section.
 *
 * @since 3.2.2
 *
 * @return mixed|null
 */
function get_report_sections() {
    $report_sections = array(
        'amex'
    );

    return apply_filters( 'usb_swiper_get_report_sections', $report_sections );
}

/**
 * Get form tab fields for transaction.
 *
 * @since 1.0.0
 *
 * @return array $tab_fields
 */
function usb_swiper_get_fields_for_transaction() {

    return array(
        'currency_info' => __( 'Currency Information' ,'usb-swiper' ),
        'personal_info' => __( 'Buyer Information' ,'usb-swiper' ),
        'product_info' => __( 'Product Information' ,'usb-swiper' ),
        'payment_info' => __( 'Payment Information' ,'usb-swiper' ),
        'billing_address' => __( 'Billing Address' ,'usb-swiper' ),
        'shipping_address' => __( 'Shipping Address' ,'usb-swiper' ),
    );
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
    $user_id = get_current_user_id();
    $merchant_data = usbswiper_get_onboarding_merchant_response($user_id);
    $country_code = !empty( $merchant_data['country'] ) ? $merchant_data['country'] : 'US';
	$get_countries = usb_swiper_get_countries();
	$get_states = usb_swiper_get_states($country_code);
    $tax_data = get_user_meta($user_id, 'user_tax_data', true);
    $default_tax = get_user_meta($user_id,'default_tax',true);
    $default_tax = ( !empty( $default_tax ) && isset($tax_data[$default_tax]) ) ? $tax_data[$default_tax] : '';
    $tax_label = !empty( $default_tax['tax_label'] ) ? $default_tax['tax_label'] : '';
    $tax_rate = !empty( $default_tax['tax_rate'] ) ? $default_tax['tax_rate'] : '';
    $tax_on_shipping = !empty( $default_tax['tax_on_shipping'] ) ? $default_tax['tax_on_shipping'] : false;

    $form_fields = array(
        'swiper' => apply_filters( 'usb_swiper_swipe_card_fields', array(
            array(
                'type' => 'password',
                'id' => 'swiper',
                'name' => 'swiper',
                'label' => __( 'Click to Swipe', 'usb-swiper'),
                'required' => false,
                'attributes' =>'',
                'class' => 'vt-input-field',
                'description' => sprintf( __( 'Note: A %1$sUSB credit card reader%2$s is required for swipe functionality.','usb-swiper'), '<a target="_blank" href="https://www.usbswiper.com/usbswiper-usb-magnetic-stripe-credit-card-reader.html?utm_source=angelleye&utm_medium=paypal-pos&utm_campaign=usbswiper">' ,'</a>')
            )
        )),
        'currency_info' => apply_filters( 'usb_swiper_payment_info_fields', array(
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
            )
        )),
        'product_info' => apply_filters( 'usb_swiper_product_info_fields', array(
            array(
                'type' => 'text',
                'id' => 'VTProduct',
                'name' => 'VTProduct[]',
                'required' => true,
                'placeholder' => __( 'Search Product', 'usb-swiper'),
	            'attributes'  => [
		            'data-product-taxable' => false
	            ],
                'description' => '',
                'readonly' => false,
                'disabled' => false,
                'class' => 'vt-input-field vt-product-input',
                'wrapper_class' => 'product'
            ),
            array(
                'type' => 'number',
                'id' => 'VTProductQuantity',
                'name' => 'VTProductQuantity[]',
                'placeholder' => __( 'Quantity', 'usb-swiper'),
                'required' => true,
				'attributes' => array(
					"step" => 1,
					"min" => 1,
				),
                'description' => '',
                'readonly' => false,
                'disabled' => false,
                'class' => 'vt-input-field vt-product-quantity',
                'wrapper_class' => 'product_quantity'
            ),
            array(
                'type' => 'text',
                'id' => 'VTProductPrice',
                'name' => 'VTProductPrice[]',
                'placeholder' => __( 'Price', 'usb-swiper'),
                'required' => true,
	            'attributes' => array(
		            'pattern' => '([0-9]|\$|,|.)+'
	            ),
                'description' => '',
                'readonly' => false,
                'disabled' => false,
                'class' => 'vt-input-field vt-product-price',
                'wrapper_class' => 'price'
            ),
			array(
				'type' => 'hidden',
				'id' => 'VTProductID',
				'name' => 'VTProductID[]',
				'attributes' => '',
				'description' => '',
				'class' => 'vt-input-field vt-product-id',
			)
        )),
        'personal_info' => apply_filters( 'usb_swiper_personal_info_fields', array(
            array(
                'type' => 'text',
                'id' => 'company',
                'name' => 'company',
                'placeholder' => __( 'Company Name', 'usb-swiper'),
                'required' => false,
                'options' => array(),
                'attributes' => array(
                    //'maxlength' => 25
                ),
                'class' => 'vt-input-field',
            ),
            array(
                'type' => 'text',
                'id' => 'BillingFirstName',
                'name' => 'BillingFirstName',
                'placeholder' => __( 'First Name', 'usb-swiper'),
                'required' => true,
                'attributes' => array(
                    'maxlength' => 35
                ),
                'description' => '',
                'class' => 'vt-input-field',
            ),
            array(
                'type' => 'text',
                'id' => 'BillingLastName',
                'name' => 'BillingLastName',
                'placeholder' => __( 'Last Name', 'usb-swiper'),
                'required' => true,
                'attributes' => array(
                    'maxlength' => 35
                ),
                'description' => '',
                'class' => 'vt-input-field',
            ),
            array(
                'type' => 'text',
                'id' => 'BillingEmail',
                'name' => 'BillingEmail',
                'placeholder' => __( 'Email Address', 'usb-swiper'),
                'options' => array(),
                'attributes' => array(
                    //'maxlength' => 25
                ),
                'class' => 'vt-input-field',
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
                'id' => 'OrderAmount',
                'name' => 'OrderAmount',
                'label' => __( 'Order Amount', 'usb-swiper'),
                'required' => true,
                'readonly' => true,
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
                'id' => 'Discount',
                'name' => 'Discount',
                'label' => __( 'Discount', 'usb-swiper'),
                'required' => false,
                'is_percentage' => true,
                'attributes' => array(
                    'maxlength' => '4'
                ),
                'description' => '',
                'class' => '',
            ),
            array(
                'type' => 'select',
                'id' => 'DiscountType',
                'name' => 'DiscountType',
                'required' => true,
                'options' => array(
                    'percent' => __( '%', 'usb-swiper' ),
                    'flat' => __( usbswiper_get_currency_symbol(), 'usb-swiper' ),
                ),
                'default' => '$',
                'attributes' => '',
                'description' => '',
                'readonly' => false,
                'disabled' => false,
                'class' => 'discount-type',
            ),
            array(
                'type' => 'text',
                'id' => 'DiscountAmount',
                'name' => 'DiscountAmount',
                'label' => __( 'Discount Amount', 'usb-swiper'),
                'required' => false,
                'readonly' => true,
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
				'id' => 'NetAmount',
				'name' => 'NetAmount',
				'label' => __( 'Net Order Amount', 'usb-swiper'),
				'required' => true,
                'readonly' => true,
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
				'label' => __( 'Tax Rate for Taxable Product', 'usb-swiper'),
				'required' => false,
				'is_percentage' => true,
                'placeholder' => __( 'Search Tax', 'usb-swiper'),
				'description' => '',
				'class' => 'tax-rate-sign vt-tax-input',
                'wrapper_class' => 'tax_rate_wrapper',
				'is_symbol' => true,
				'symbol' => '%',
				'symbol_wrap_class' => 'currency-sign after',
                'value' => $tax_rate,
                'tooltip' => !empty( $tax_rate ),
                'tooltip_text' => $tax_label,
                'default_tool_text' => __( 'Tax Rule: ', 'usb-swiper'),
			),
            array(
                'type' => 'checkbox',
                'id' => 'TaxOnShipping',
                'name' => 'TaxOnShipping',
                'label' => __( 'Tax on Shipping', 'usb-swiper'),
                'required' => false,
                'description' => '',
                'class' => 'hidden d-none',
                'wrapper_class' => 'hidden d-none',
                'symbol_wrap_class' => 'currency-sign after',
                'value' => true,
                'checked' => $tax_on_shipping
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
				'label' => __( 'Address Required?', 'usb-swiper'),
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
                'wrapper' =>  true,
                'wrapper_class' => 'state-field billing-states-wrap',
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
				'label' => __( 'Address Required?', 'usb-swiper'),
				'required' => false,
				'value' => "true",
				'checked' => true,
				'attributes' => array(
					'data-default-checked' => "FALSE"
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
				'checked' => true,
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
                'wrapper' =>  true,
                'wrapper_class' => 'state-field shipping-states-wrap',
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
        'save_customer_info' => apply_filters( 'usb_swiper_shipping_address_fields', array(
	        array(
		        'type' => 'checkbox',
		        'id' => 'save_customer_details',
		        'name' => 'save_customer_details',
		        'label' => __( 'Save customer’s details for future use.', 'usb-swiper'),
		        'required' => false,
		        'description' => '',
		        'class' => '',
		        'wrapper_class' => '',
		        'symbol_wrap_class' => '',
		        'value' => true,
		        'checked' => false
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
 * @param array $field get field array
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
function usbswiper_get_onboarding_user( $user_id = 0 ) {

    if( empty($user_id) && is_user_logged_in() ) {
        $user_id = get_current_user_id();
    }

    $settings = usb_swiper_get_settings('general');
    $vt_invoice_page_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';

    if( $vt_invoice_page_id === get_the_ID() ) {

        $invoice_session = !empty($_GET['invoice-session']) ? json_decode( base64_decode($_GET['invoice-session'])) : '';
        $invoice_id = !empty( $invoice_session->id ) ? trim($invoice_session->id, 'invoice_') :'';
        $user_id = $invoice_id ? get_post_field( 'post_author', $invoice_id ) : 0;
    }

    $merchant_user = !empty( $user_id ) ? get_user_meta( $user_id,'_merchant_onboarding_user',true) : 0;
    $merchant_user = !empty( $merchant_user ) ? json_decode(base64_decode( ($merchant_user))) : '';

    return !empty( $merchant_user ) ? (array)$merchant_user : '';
}

/**
 * Get partner fee based on cart total.
 *
 * @since 1.0.0
 *
 * @param float|int $cart_total get total cart value.
 *
 * @return float|int $platform_fees
 */
function usbswiper_get_platform_fees( $cart_total, $type = 'transaction', $transaction_id = 0 ) {

	if( empty( $cart_total ) ) {
		return 0;
	}

    $user_id = ( is_user_logged_in() ) ? get_current_user_id() : 0;
    if( $type === 'invoice' && $transaction_id > 0 ){
        $user_id = get_post_meta($transaction_id, '_transaction_user_id', true);
    }

	$exclude_partner_users = get_option('get_exclude_partner_users');
	if( !empty( $exclude_partner_users ) && is_array( $exclude_partner_users ) && in_array( $user_id, $exclude_partner_users) ) {
		return 0;
	}

	$billing_country = get_user_meta( $user_id, 'billing_country', true);

    $transaction_user = $transaction_id ? get_post_field( 'post_author', $transaction_id ) : 0;
	$merchant_response = usbswiper_get_onboarding_merchant_response($transaction_user);
	$merchant_country = !empty( $merchant_response['country'] ) ? $merchant_response['country'] :'';
	$settings = usb_swiper_get_settings('partner_fees');
	$fees = !empty( $settings['fees']) ? $settings['fees'] : '';
	$default_partner_percentage = !empty( $settings['default_partner_percentage']) ? $settings['default_partner_percentage'] : '';
    $default_amex_percentage = !empty( $settings['default_amex_percentage']) ? $settings['default_amex_percentage'] : '';
    $card_brand = !empty($_POST['card_type']) ? sanitize_text_field($_POST['card_type']) : '';
    $payment_card_brand = '';
    if(!empty($transaction_id)){
        $payment_card_brand = get_post_meta($transaction_id,'_payment_card_brand',true);
    }

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
    if( (!empty($card_brand) && strtolower($card_brand) === 'american express') || (!empty($payment_card_brand) && strtolower($payment_card_brand) === 'amex') ){
        $percentage = !empty( $default_amex_percentage ) ? $default_amex_percentage : 0;
    } else if (isset($country_fees[$country]) && !empty($country_fees[$country])) {
        $percentage = $country_fees[$country];
    }

	if( !empty( $percentage ) && $percentage > 0 ) {
		$platform_fees = ( $cart_total * $percentage ) / 100;
	}

	return !empty( $platform_fees ) ? number_format( $platform_fees, 2, '.', '' ) : 0;
}

/**
 * Check usb_swiper_key_generator function is exists or not.
 *
 * @since 1.0.0
 */
if (!function_exists('usb_swiper_key_generator')) {

    /**
     * Get the unique key.
     *
     * @since 1.0.0
     *
     * @return string
     */
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

/**
 * check usb_swiper_set_session function is exists or not.
 *
 * @since 1.0.0
 */
if (!function_exists('usb_swiper_set_session')) {

    /**
     * Set data in WooCommerce session.
     *
     * @since 1.0.0
     *
     * @param string $key
     * @param array $value
     * @return false|void
     */
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

/**
 * Check usb_swiper_get_session function is exists or not.
 *
 * @since 1.0.0
 */
if (!function_exists('usb_swiper_get_session')) {

    /**
     * Get the session by key.
     *
     * @since 1.0.0
     *
     * @param string $key get session key.
     * @return false|mixed
     */
	function usb_swiper_get_session($key) {

		if (!class_exists('WooCommerce') || WC()->session == null) {
			return false;
		}

		$usb_swiper_ppcp_session = WC()->session->get('usb_swiper_ppcp_session');

		return !empty($usb_swiper_ppcp_session[$key]) ? $usb_swiper_ppcp_session[$key] : false;
	}
}

/**
 * Check usb_swiper_unique_id function exists or not.
 *
 * @since 1.0.0
 */
if( !function_exists('usb_swiper_unique_id')) {

    /**
     * Get the unique id.
     *
     * @since 1.0.0
     *
     * @param array $args get all arguments
     * @return string|void
     */
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

/**
 * Check usb_swiper_get_unique_id_data function exists or not.
 *
 * @since 1.0.0
 */
if( !function_exists( 'usb_swiper_get_unique_id_data') ) {

    /**
     * Get the unique id data.
     *
     * @since 1.0.0
     *
     * @param string $unique_id get unique id
     * @return array|void
     */
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

/**
 * Get the currency code options.
 *
 * @since 1.0.0
 *
 * @return array
 */
function usbswiper_get_currency_code_options() {

	$currency_code_options = get_woocommerce_currencies();

	foreach ( $currency_code_options as $code => $name ) {
		$currency_code_options[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
	}

	return $currency_code_options;
}

/**
 * Get the default currency.
 *
 * @since 1.0.0
 *
 * @param int $user_id get user id
 * @return mixed|string
 */
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

/**
 * Get the current login user products.
 *
 * @since 1.1.17
 *
 * @return array
 */
function vt_get_curent_user_products() {

    $product_option = array('' => __( 'Select Product', 'usb-swiper'));

    $products = new WP_Query( array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'author' => get_current_user_id(),
        'order' => 'DESC',
    ));

    if( ! empty( $products->posts ) ) {
        foreach ( $products->posts as $product ) {
            $product_option[$product->ID] = $product->post_title;
        }
    }

    return $product_option;
}

/**
 * Get the currency symbol.
 *
 * @since 1.0.0
 *
 * @return string
 */
function usbswiper_get_currency_symbol() {

	$currency = usbswiper_get_default_currency();

	return get_woocommerce_currency_symbol( $currency );
}

/**
 * Get the round amount of price.
 *
 * @since 1.0.0
 *
 * @param float $price get price.
 * @param int $precision
 * @return string
 */
function usbswiper_round_amount( $price, $precision ) {
	$round_price = round($price, $precision);
	return number_format($round_price, $precision, '.', '');
}

/**
 * Get the payment status.
 *
 * @since 1.0.0
 *
 * @param string $status get payment status.
 * @return array|string|string[]
 */
function usbswiper_get_payment_status( $status ) {

	if( empty( $status ) ) {
		return '';
	}

	return str_replace( array('_','-'),' ', $status);
}

/**
 * Get the refund status.
 *
 * @since 1.0.0
 *
 * @return mixed|null
 */
function usbswiper_get_refund_status() {

	return apply_filters('usbswiper_get_refund_status', array('COMPLETED','PARTIALLY_REFUNDED','PAID'));
}

/**
 * get the total refund amount.
 *
 * @since 1.0.0
 *
 * @param int $transaction_id get transaction id.
 * @return string|void
 */
function get_total_refund_amount( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return;
	}

	$GrandTotal = get_post_meta( $transaction_id, 'GrandTotal', true);
	$payment_response = get_post_meta( $transaction_id,'_payment_response', true);

	$transaction_type = get_post_meta( $transaction_id, '_transaction_type', true);

    if( !empty(  $transaction_type ) && strtolower( $transaction_type ) === 'zettle' ) {

	    $GrandTotal = usbswiper_get_zettle_transaction_total( $transaction_id );
	    $payment_refund_response = get_post_meta( $transaction_id,'_payment_refund_response', true);

	    $total_refund_amount = 0;
        if( !empty( $payment_refund_response ) && is_array( $payment_refund_response ) ) {

            foreach ( $payment_refund_response as $key => $refund_response ) {

                $result_payload = !empty( $refund_response['result_payload'] ) ? $refund_response['result_payload'] : '';
                $result_payload = !empty( $refund_response['resultPayload'] ) ? $refund_response['resultPayload'] : $result_payload;
	            $refund_amount = !empty( $result_payload->REFUNDED_AMOUNT ) ? $result_payload->REFUNDED_AMOUNT : 0;
	            $refund_amount = !empty( $refund_amount ) ? $refund_amount/100 : 0;
	            $total_refund_amount =  $total_refund_amount + $refund_amount;
            }
        }

	    $remaining_amount = $GrandTotal - $total_refund_amount;
    } else {

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
    }

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

/**
 * Manage price format.
 *
 * @since 2.3.4
 *
 * @param float $price Get Price.
 * @return float|string
 */
function usbswiper_get_price_format( $price ) {

	$args = array(
		'ex_tax_label'       => false,
		'currency'           => '',
		'decimal_separator'  => wc_get_price_decimal_separator(),
		'thousand_separator' => wc_get_price_thousand_separator(),
		'decimals'           => wc_get_price_decimals(),
		'price_format'       => get_woocommerce_price_format(),
	);

	return !empty( $price ) ? number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ) : 0.00;
}

/**
 * Get the transaction type.
 *
 * @since 1.0.0
 *
 * @param int $transaction_id get transaction id.
 * @return string
 */
function usbswiper_get_transaction_type( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$TransactionType = get_post_meta($transaction_id,'TransactionType', true);
	$TransactionType = !empty( $TransactionType ) ? $TransactionType : 'capture';

	return strtoupper( $TransactionType );
}

/**
 * Get the invoice transaction type.
 *
 * @since 1.1.17
 *
 * @param int $transaction_id
 * @return string
 */
function usbswiper_get_invoice_transaction_type( $transaction_id ) {

    if( empty( $transaction_id ) ) {
        return '';
    }

    $transaction_type = get_post_meta($transaction_id,'_transaction_type', true);
    $transaction_type = !empty( $transaction_type ) ? $transaction_type : 'transaction';

    return strtoupper( $transaction_type );
}

/**
 * Get transaction status.
 *
 * @since 1.0.0
 *
 * @param int $transaction_id get transaction id.
 * @return mixed|string
 */
function usbswiper_get_transaction_status( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

    $payment_intent = usbswiper_get_transaction_type($transaction_id);
    $payment_intent = !empty( $payment_intent ) ? strtolower( $payment_intent ) : '';

    $global_payment_status = get_post_meta( $transaction_id, '_payment_status', true);

    $transaction_type = usbswiper_get_invoice_transaction_type($transaction_id);

	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);
	$status = !empty( $payment_response['status'] ) ? $payment_response['status'] : $global_payment_status;

	$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
	$payments = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
	$captures = !empty( $payments['captures'][0] ) ? $payments['captures'][0] : '';
	$authorizations = !empty( $payments['authorizations'][0] ) ? $payments['authorizations'][0] : '';

	if ( !empty( $captures ) && is_array($captures) && !empty( $captures['id'] ) ) {
		$status = !empty( $captures['status']) ? $captures['status'] : '';
        if( !empty( $transaction_type ) && $transaction_type === 'INVOICE' && !empty( $payment_intent ) && ( $payment_intent === 'authorize' || $payment_intent== 'capture' ) && $status === 'COMPLETED' ) {
            $status = __('Paid', 'usb-swiper');
        }
	} elseif( !empty( $authorizations ) && is_array($authorizations) && !empty( $authorizations['id'] ) ) {
		$status = !empty( $authorizations['status']) ? $authorizations['status'] : '';
        if( !empty( $transaction_type ) && $transaction_type === 'INVOICE' && !empty( $payment_intent ) && ( $payment_intent === 'authorize' || $payment_intent== 'capture' ) && $status !== 'VOIDED' ) {
            $status = __('Authorized', 'usb-swiper');
        }
	}

    if( strtolower($global_payment_status) === 'failed' || ( !empty( $transaction_type ) && strtolower($transaction_type) === 'invoice' && !in_array($status , array('PARTIALLY_REFUNDED', 'CREATED', 'VOIDED' ,'Authorized', 'REFUNDED', 'Paid')) )) {
        $status = $global_payment_status;
    }

	return strtoupper($status);
}

/**
 * Get the intent id.
 *
 * @since 1.0.0
 *
 * @param int $transaction_id get transaction id.
 * @return mixed|string
 */
function usbswiper_get_intent_id( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);

	return !empty( $payment_response['id'] ) ? $payment_response['id'] : '';
}

/**
 * Get the transaction id.
 *
 * @since 1.0.0
 *
 * @param int $transaction_id get transaction id
 * @return mixed|string
 */
function usbswiper_get_transaction_id( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

    $transaction_type = get_post_meta( $transaction_id, '_transaction_type', true);
	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);

    if( !empty(  $transaction_type ) && strtolower( $transaction_type ) === 'zettle' ) {

	    $result_status = !empty( $payment_response['result_status'] ) ? $payment_response['result_status'] : '';
	    $result_status = !empty( $payment_response['resultStatus'] ) ? $payment_response['resultStatus'] : $result_status;

	    $payment_transaction_id = '';
        if( !empty( $result_status ) && strtolower( $result_status ) == 'completed' ) {

            $result_payload = !empty( $payment_response['result_payload'] ) ? $payment_response['result_payload'] : '';
            $result_payload = !empty( $payment_response['resultPayload'] ) ? $payment_response['resultPayload'] : $result_payload;
	        $payment_transaction_id = !empty( $result_payload->REFERENCE_NUMBER ) ? $result_payload->REFERENCE_NUMBER : '';
        }

        return $payment_transaction_id;
    }

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

/**
 * Get the transaction date and time.
 *
 * @since 1.0.0
 *
 * @param int $transaction_id get transaction id.
 * @param string $type get date type
 * @return mixed|string
 */
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

/**
 * Get locale data.
 *
 * @since 1.0.0
 *
 * @return array|string|string[]
 */
function usbswiper_get_locale() {

	$merchant_data = usbswiper_get_onboarding_merchant_response();
	$country_code = !empty( $merchant_data['country'] ) ? $merchant_data['country'] : '';

	if( empty( $country_code ) ) {
		return'';
	}

	$locale_info = include WC()->plugin_path() . '/i18n/locale-info.php';
	$country_locale = !empty( $locale_info[$country_code] ) ? $locale_info[$country_code] : '';

	return !empty( $country_locale['default_locale'] ) ? str_replace('_','-', $country_locale['default_locale']) : '';
}

/**
 * Get the brand name.
 *
 * @since 1.0.0
 *
 * @return mixed|string|null
 */
function usbswiper_get_brand_name() {
	$company_name = get_user_meta( get_current_user_id(),'brand_name', true);
	return !empty( $company_name ) ? $company_name : '';
}

/**
 * Get the brand logo.
 *
 * @since 1.0.0
 *
 * @return mixed|string|null
 */
function usbswiper_get_brand_logo( $user_id, $is_url = true, $size = 'full', $is_email = false ) {

    if( empty( $user_id ) ) {
         return false;
    }

    $brand_logo = array(
        'attachment_id' => '',
        'image_html' => ''
    );

    $brand_logo_id = get_user_meta( $user_id,'brand_logo', true);

    if( empty( $brand_logo_id ) ) {
        return $brand_logo;
    }

    $brand_logo_url = !empty( $brand_logo_id ) ? wp_get_attachment_image_url($brand_logo_id,$size) : '';

    if( $is_email ){
        if( !empty($brand_logo_url) ){
            $brand_logo['image_html'] = "<img width='250' src='".esc_url($brand_logo_url)."' alt='' loading='lazy' style='height:auto;vertical-align: middle;max-width: 100%;'>";
        } else {
            $brand_name = get_user_meta( $user_id,'brand_name', true);
            $brand_logo['image_html'] = !empty( $brand_name ) ? "<h1 style='vertical-align:middle;text-align:center;font-size:32px;margin:0;font-weight:bold;'>".esc_html($brand_name)."</h1>" : '';
        }
    } else {
        $brand_logo = array(
            'attachment_id' => $brand_logo_id,
            'image_html' => wp_get_attachment_image($brand_logo_id, $size)
        );

        if( $is_url ) {
            $brand_logo['image_html'] = !empty($brand_logo_url) ? esc_url($brand_logo_url) : '';
        }
    }

    return $brand_logo;
}

function usb_swiper_brand_logo( $string ) {

    if( !empty( $string ) ) {
        $brand_logo = get_user_meta(get_current_user_id(), 'brand_logo', true);
        $string = str_replace('{#brand_logo#}', $brand_logo['image_html'], $string);
    }

    return $string;
}
/**
 * Get the current user invoice prefix value.
 *
 * @since 1.1.17
 *
 * @return mixed|string
 */
function usbswiper_get_invoice_prefix( $transaction_id = '' ) {
    $current_user_id = get_current_user_id();
    if( !empty( $transaction_id ) && (int)$transaction_id > 0 ){
        $author_id = get_post_field( 'post_author', $transaction_id );
        $current_user_id = ! empty( $author_id ) ? $author_id : 1;
    }
    $invoice_prefix = get_user_meta( $current_user_id,'invoice_prefix', true);
    return !empty( $invoice_prefix ) ? $invoice_prefix : '';
}

/**
 * Create the invoice id with the prefix.
 *
 * @since 1.1.17
 *
 * @param int $transaction_id
 * @param int $InvoiceID
 * @return string
 */
function usbswiper_create_invoice_prefix($transaction_id, $InvoiceID){
    $invoice_prefix = usbswiper_get_invoice_prefix($transaction_id);
    $transaction_type = get_post_meta($transaction_id,'_transaction_type', true);

    if( empty( $transaction_type ) || ( !empty( $transaction_type ) && strtolower( $transaction_type ) !== 'invoice' ) ){
        $transaction_type = 'trans';
    }

    return !empty( $invoice_prefix ) ? $invoice_prefix .'_'.$transaction_type.'_'. $InvoiceID : 'VT-' . $transaction_id . '_' . $InvoiceID;
}

/**
 * Check the capture is allowed or not.
 *
 * @since 1.0.0
 *
 * @param int $transaction_id get transaction id
 * @return bool
 */
function usbswiper_is_allow_capture( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return false;
	}

    $transaction_type = get_post_meta($transaction_id,'_transaction_type', true);
	$payment_action = usbswiper_get_transaction_type($transaction_id);

	/*if( empty( $payment_action) || 'AUTHORIZE' !== $payment_action ) {
		return false;
	}*/

	$is_allow_capture = false;
	$payment_status = usbswiper_get_transaction_status($transaction_id);

    if( !empty( $payment_status ) && ( 'created' === strtolower($payment_status) || 'authorized' === strtolower($payment_status) ) ) {
        $is_allow_capture = true;
        if( strtolower($transaction_type) === 'invoice' && 'created' === strtolower($payment_status) ) {
            $is_allow_capture = false;
        }
	}

	return $is_allow_capture;
}

/**
 * Get the price in format.
 *
 * @since 1.0.0
 *
 * @param float $price get price
 * @return array|mixed|string|string[]
 */
function usb_swiper_price_formatter( $price ) {

    if( !empty( $price ) ) {
        $price = str_replace(',','', $price);
    }

    return $price;
}

/**
 * function to return user's name
 *
 * @since 1.1.17
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

/**
 * Function use for set the content limit after added limit.
 *
 * @param string $content Get content.
 * @param int $limit Get content limit
 * @param string $more Get more text.
 * @return false|string $content
 */
function usbswiper_set_content_limit( $content, $limit = 120, $more = '...' ) {

    if( empty( $content ) ) {
        return false;
    }

    $content_len = strlen($content);

    if( $content_len >= $limit ) {
        $content = substr($content, 0, $limit).$more;
    }

    return $content;

}

/**
 * Get the product sku.
 *
 * @since 1.1.17
 *
 * @param string $sku
 * @param boolean $is_display
 * @return array|string|string[]|void
 */
function usbswiper_get_product_sku( $sku, $is_display = false ) {

    if(empty($sku) || !is_user_logged_in()) {
        return;
    }

    $current_user = wp_get_current_user();
    $user_login = !empty( $current_user->user_login ) ? $current_user->user_login : '';
    $prefix = get_user_meta( get_current_user_id(),'invoice_prefix', true);

    $default_prefix = $user_login.'-';

    if( $is_display ) {
        $get_product_sku = str_replace($default_prefix,'',$sku);
        if(  !empty( $prefix ) ) {
            $get_product_sku = str_replace($prefix,'',$get_product_sku);
        }
    } else {
        if(  !empty( $prefix ) ) {
            $get_product_sku = $prefix.$sku;
        } else {
            $get_product_sku = $default_prefix.$sku;
        }
    }

    return $get_product_sku;
}

/**
 * Check mobile_number_format function is exists or not.
 */
if( ! function_exists('mobile_number_format') ) {

    /**
     * Get the mobile number in specific format.
     *
     * @since 1.1.17
     *
     * @param int $number
     * @return int|string
     */
    function mobile_number_format( $number ){
        $number = !empty( $number ) ? (int)$number : "";
        if( !empty( $number ) && preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $number,  $matches ) ) {
            return '(' .$matches[1] . ') ' .$matches[2] . ' ' . $matches[3];
        } else {
            return $number;
        }
    }
}

/**
 * Get the product html.
 *
 * @since 1.1.17
 *
 * @param int $id
 * @return string $html
 */
function get_product_html( $id = 0 ) {

    $product_info_fields = usb_swiper_get_vt_form_fields('product_info');

    $html = '<div id="vt_fields_wrap_' . $id . '" class="vt-fields-wrap" data-id="'.$id.'">';

    if (!empty($product_info_fields) && is_array($product_info_fields)) {

        foreach ($product_info_fields as $product_field) {
            $field_id = !empty($product_field['id']) ? $product_field['id'] : '';
            if (!empty($field_id)) {
                $product_field['id'] = $field_id . "_" . $id;
            }
            $html .= usb_swiper_get_html_field($product_field);
        }
    }

    $html .= '<span class="vt-remove-fields-wrap">';

    if ($id > 0) {
        $html .= '<svg viewBox="0 0 24 24" width="25" height="25" stroke="#d00" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
    }

    $html .= '</span>';

	$html .= '</div>';

    return $html;
}

/**
 * Check usb_swiper_get_field_value function is exists or not.
 */
if( !function_exists('usb_swiper_get_field_value') ) {
    /**
     * Get the field value from settings.
     *
     * @since 1.1.17
     *
     * @param string $field
     * @param string $tab
     * @return mixed|string
     */
    function usb_swiper_get_field_value( $field , $tab ='general') {

        if( empty( $field ) ) {
            return '';
        }

        $settings = usb_swiper_get_settings($tab);

        return  !empty( $settings[$field] ) ? $settings[$field]: '';
    }
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

    $merchant_business_street = get_user_meta($user_id, 'billing_address_1', true);
    $merchant_business_street2 = get_user_meta($user_id, 'billing_address_2', true);
    $merchant_business_city = get_user_meta($user_id, 'billing_city', true);
    $merchant_business_state = get_user_meta($user_id, 'billing_state', true);
    $merchant_business_postal_code = get_user_meta($user_id, 'billing_postcode', true);
    $merchant_business_country_code = get_user_meta($user_id, 'billing_country', true);

    $merchant_business_street = !empty($merchant_business_street) ? $merchant_business_street : '';
    $merchant_business_street2 = !empty($merchant_business_street2) ? $merchant_business_street2 : '';
    $merchant_business_city = !empty($merchant_business_city) ? $merchant_business_city : '';
    $merchant_business_state = !empty($merchant_business_state) ? $merchant_business_state : '';
    $merchant_business_postal_code = !empty($merchant_business_postal_code) ? $merchant_business_postal_code : '';
    $merchant_business_country_code = !empty($merchant_business_country_code) ? $merchant_business_country_code : '';

    $merchant_address = $merchant_business_street;
    $merchant_address .= !empty($merchant_address) ? ', ' . $merchant_business_street2 : '';
    $merchant_address .= !empty($merchant_address) ? ', ' . $merchant_business_city : '';
    $merchant_address .= !empty($merchant_address) ? ', ' . $merchant_business_state : '';
    $merchant_address .= !empty($merchant_address) ? ', ' . $merchant_business_country_code : '';
    $merchant_address .= !empty($merchant_address) ? '. ' . $merchant_business_postal_code : '';

    return !empty($merchant_address) ? $merchant_address : '';
}

/**
 * Get the invoice status icon.
 *
 * @since 1.1.17
 *
 * @param $invoice_id
 * @return mixed|void|null
 */
function usb_swiper_get_invoice_status_icon( $invoice_id ) {

    if( empty( $invoice_id ) ) {
        return;
    }

    $transaction_type = get_post_meta( $invoice_id, '_transaction_type', true);

    $icon = '';

    if( !empty( $transaction_type ) && strtolower($transaction_type) === 'invoice' ) {

        $payment_status = usbswiper_get_transaction_status($invoice_id);

        if( !empty( $payment_status ) && strtolower( $payment_status ) === 'paid' ) {
            $icon = USBSWIPER_URL.'assets/images/paid.png';
        } else if ( !empty( $payment_status ) && strtolower( $payment_status ) === 'partially_refunded' ) {
            $icon = USBSWIPER_URL.'assets/images/partial-refund.png';
        } else if ( !empty( $payment_status ) && strtolower( $payment_status ) === 'refunded' ) {
            $icon = USBSWIPER_URL.'assets/images/full-refund.png';
        } else {
            $icon = USBSWIPER_URL.'assets/images/pending.png';
        }
    }

    return apply_filters( 'usb_swiper_invoice_status_icon', $icon, $invoice_id );
}

/**
 * Convert object into array.
 *
 * @since 1.1.17
 *
 * @param object $obj
 * @return array $response
 */
function object_to_array( $obj ) {

    $response = array();
    if( !empty($obj) && ( is_object($obj) || is_array($obj) )) {

        if ( is_object($obj)) {
            $obj = (array)$obj;
        }

        foreach ($obj as $key => $field) {

            if ( !empty( $field ) && ( is_object( $field ) || is_array( $field ) ) ) {
                $converted_value = object_to_array($field);
            } else {
                $converted_value = $field;
            }

            $response[$key] = $converted_value;
        }
    }

    return $response;
}

/**
 * This function will count invoice of user.
 *
 * @since 1.1.17
 *
 * @param int $count get invoice count.
 * @param int $paged get current page.
 *
 * @return int|mixed
 */
function count_user_invoice_numbers( $count = 1, $paged = 1 ) {

    if (!is_user_logged_in()) {
        return 0;
    }

    $args = array(
        'post_type' => 'transactions',
        'author__in' => array(get_current_user_id()),
        'posts_per_page' => 100,
        'paged' => $paged,
        'meta_key' => '_transaction_type',
        'meta_value' => 'INVOICE',
	    'post_status' => ['publish','future'],
    );

    $query = new WP_Query($args);

    $count = $count + $query->post_count;

    if (round($query->max_num_pages) > $paged) {
        $next_page = $paged + 1;
        $count = count_user_invoice_numbers($count, $next_page);
    }

    return $count > 0 ? $count : 1;
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
 * Get the paypal transaction url.
 *
 * @since 1.1.17
 *
 * @param int $transaction_id get the transaction id.
 *
 * @return string return the transaction paypal url.
 */
function get_paypal_transaction_url( $transaction_id ) {

    if( empty($transaction_id)) {
        return '';
    }

    $settings = usb_swiper_get_settings('general');
    $is_sandbox = !empty( $settings['is_paypal_sandbox'] ) ? '.sandbox': '';

    return "https://www{$is_sandbox}.paypal.com/activity/payment/{$transaction_id}";
}

/**
 * Get the address in format.
 *
 * @since 1.1.17
 *
 * @param int $transaction_id get the transaction id
 * @return array[] return the transaction billing and shipping address
 */
function get_transaction_address_format( $transaction_id , $is_email = false ){

    if( empty($transaction_id) ){
        return '';
    }

    $billing_first_name = get_post_meta( $transaction_id, 'BillingFirstName', true);
    $billing_first_name = !empty( $billing_first_name ) ? esc_html($billing_first_name) : '';
    $billing_last_name = get_post_meta( $transaction_id, 'BillingLastName', true);
    $billing_last_name = !empty( $billing_last_name ) ? esc_html($billing_last_name) : '';
    $billing_street = get_post_meta( $transaction_id, 'BillingStreet', true);
    $billing_street = !empty( $billing_street ) ? esc_html($billing_street) : '';
    $billing_street2 = get_post_meta( $transaction_id, 'BillingStreet2', true);
    $billing_street2 = !empty( $billing_street2 ) ? ', ' .esc_html($billing_street2) : '';
    $billing_city = get_post_meta( $transaction_id, 'BillingCity', true);
    $billing_city = !empty( $billing_city ) ? esc_html($billing_city) : '';
    $billing_state = get_post_meta( $transaction_id, 'BillingState', true);
    $billing_state = !empty( $billing_state ) ? ', '. esc_html($billing_state) : '';
    $billing_postal_code = get_post_meta( $transaction_id, 'BillingPostalCode', true);
    $billing_postal_code = !empty( $billing_postal_code ) ? esc_html($billing_postal_code) : '';
    $billing_country_code = get_post_meta( $transaction_id, 'BillingCountryCode', true);
    $billing_country_code = !empty( $billing_country_code ) ? esc_html($billing_country_code) : '';

    $shipping_first_name = get_post_meta( $transaction_id, 'ShippingFirstName', true);
    $shipping_first_name = !empty( $shipping_first_name ) ? esc_html($shipping_first_name) : '';
    $shipping_last_name = get_post_meta( $transaction_id, 'ShippingLastName', true);
    $shipping_last_name = !empty( $shipping_last_name ) ? esc_html($shipping_last_name) : '';
    $shipping_street = get_post_meta( $transaction_id, 'ShippingStreet', true);
    $shipping_street = !empty( $shipping_street ) ? esc_html($shipping_street) : '';
    $shipping_street2 = get_post_meta( $transaction_id, 'ShippingStreet2', true);
    $shipping_street2 = !empty( $shipping_street2 ) ? ', ' .esc_html($shipping_street2) : '';
    $shipping_city = get_post_meta( $transaction_id, 'ShippingCity', true);
    $shipping_city = !empty( $shipping_city ) ? esc_html($shipping_city) : '';
    $shipping_state = get_post_meta( $transaction_id, 'ShippingState', true);
    $shipping_state = !empty( $shipping_state ) ? ', '. esc_html($shipping_state) : '';
    $shipping_postal_code = get_post_meta( $transaction_id, 'ShippingPostalCode', true);
    $shipping_postal_code = !empty( $shipping_postal_code ) ? esc_html($shipping_postal_code) : '';
    $shipping_country_code = get_post_meta( $transaction_id, 'ShippingCountryCode', true);
    $shipping_country_code = !empty( $shipping_country_code ) ? esc_html($shipping_country_code) : '';

    $shippingDisabled = get_post_meta( $transaction_id, 'shippingDisabled', true);
    $shippingSameAsBilling = get_post_meta( $transaction_id, 'shippingSameAsBilling', true);

    $style = isset($is_email) ? 'margin: 0;font-size: 14px;padding:0;font-style: normal;' : 'font-style: normal;padding:0;margin:0';

    $billing_address_html = '';
    $billing_address_html .= "<p style='".$style."'>" . $billing_first_name . ' ' . $billing_last_name . "</p>";
    if( !empty($billing_street) && !empty($billing_city) ){
        $billing_address_html .= "<p style='".$style."'>" . $billing_street . $billing_street2 . "</p>";
        $billing_address_html .= "<p style='".$style."'>" . $billing_city . $billing_state . ' ' . $billing_postal_code . "</p>";
        $billing_address_html .= "<p style='".$style."'>" . $billing_country_code . "</p>";
    }

    $shipping_address_html = '';
    $shipping_address_html .= "<p style='".$style."'>" . $shipping_first_name . ' ' . $shipping_last_name . "</p>";
    if( !empty($shipping_street) && !empty($shipping_city) ){
        $shipping_address_html .= "<p style='".$style."'>" . $shipping_street .  $shipping_street2 . "</p>";
        $shipping_address_html .= "<p style='".$style."'>" . $shipping_city . $shipping_state . ' ' . $shipping_postal_code . "</p>";
        $shipping_address_html .= "<p style='".$style."'>" . $shipping_country_code . "</p>";
    }


    $response = array(
        'billing_address'=> $billing_address_html,
        'shipping_address'=> ''
    );

    if( 'true' === $shippingDisabled && 'true' !== $shippingSameAsBilling ){
        $response['shipping_address'] = $shipping_address_html;
    } elseif ( 'true' === $shippingDisabled && 'true' === $shippingSameAsBilling  ) {
        $response['shipping_address'] = $billing_address_html;
    }

    return !empty( $response ) ? $response : array();

}

/**
 * Get onboarding merchant PayPal data.
 *
 * @since 1.1.17
 *
 * @param int $user_id Get user id.
 *
 * @return mixed|string
 */
function usbswiper_get_onboarding_merchant_response( $user_id = 0 ) {

    if( empty( $user_id ) && is_user_logged_in() ) {
        $user_id = get_current_user_id();
    }

    $settings = usb_swiper_get_settings('general');
    $vt_invoice_page_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';

    if( $vt_invoice_page_id === get_the_ID() ) {

        $invoice_session = !empty($_GET['invoice-session']) ? json_decode( base64_decode($_GET['invoice-session'])) : '';
        $invoice_id = !empty( $invoice_session->id ) ? trim($invoice_session->id, 'invoice_') :'';
        $user_id = $invoice_id ? get_post_field( 'post_author', $invoice_id ) : 0;
    }

    $merchant_response = !empty( $user_id ) ? get_user_meta( $user_id,'_merchant_onboarding_response',true) :'';

    return !empty( $merchant_response ) ? $merchant_response : '';
}

/**
 * Get the Refund confirmation popup html
 *
 * @since 1.1.17
 *
 * @return string
 */
function refund_confirmation_html(){

    $html = '';
    ob_start(); ?>

    <div class="vt-capture-popup-wrapper">
        <div class="popup-loader"></div>
        <div class="vt-capture-popup-inner">
            <div class="close">
                <a href="javascript:void(0);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></a>
            </div>
            <div class="vt-notification-content">
                <div class="input-field-wrap ">
                    <p><?php _e('Are you sure you want to capture this authorization?','usb-swiper'); ?></p>
                </div>
                <div class="input-field-wrap button-wrap">
                    <button id="vt_capture_cancel" type="reset" class="vt-button"><?php _e('Cancel','usb-swiper'); ?></button>
                    <a class="vt-button capture-transaction" href="#"><?php _e('CAPTURE','usb-swiper'); ?></a>
                </div>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean();

    return !empty( $html ) ? $html : '';
}

/**
 * Check usbswiper_send_email_receipt_html function exists or not.
 */
if( !function_exists('usbswiper_send_email_receipt_html') ) {

    /**
     * Get the email receipt html
     *
     * @since 1.1.17
     *
     * @param int $transaction_id
     * @return string
     */
    function usbswiper_send_email_receipt_html( $transaction_id ) {

        if( empty( $transaction_id ) ) {
            return '';
        }

        $BillingEmail = get_post_meta( $transaction_id, 'BillingEmail', true);

        $send_email_form_fields = array(
            array(
                'type' => 'text',
                'id' => 'billing_email',
                'name' => 'billing_email',
                'label' => __( 'Billing Email:', 'usb-swiper'),
                'attributes' => '',
                'description' => __('Add multiple emails with "," separated' ,'usb-swiper'),
                'readonly' => false,
                'value' => ! empty( $BillingEmail ) ? esc_attr( $BillingEmail ) : '',
                'class' => 'vt-input-field',
            ),
            array(
                'type' => 'hidden',
                'id' => 'transaction_id',
                'name' => 'transaction_id',
                'attributes' => '',
                'description' => '',
                'readonly' => false,
                'value' => $transaction_id,
            ),
            array(
                'type' => 'hidden',
                'id' => 'vt_send_email_nonce',
                'name' => 'vt-send-email-nonce',
                'label' => '',
                'value' => wp_create_nonce('vt-send-email-form'),
                'required' => false,
            )
        );

        $html = '<div class="vt-resend-email-form">';
            $html .='<div class="vt-resend-email-form-wrapper">';
                $html .='<div class="close">';
                    $html .='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="512px" id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" width="512px" xml:space="preserve"><path d="M443.6,387.1L312.4,255.4l131.5-130c5.4-5.4,5.4-14.2,0-19.6l-37.4-37.6c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4  L256,197.8L124.9,68.3c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4L68,105.9c-5.4,5.4-5.4,14.2,0,19.6l131.5,130L68.4,387.1  c-2.6,2.6-4.1,6.1-4.1,9.8c0,3.7,1.4,7.2,4.1,9.8l37.4,37.6c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1L256,313.1l130.7,131.1  c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1l37.4-37.6c2.6-2.6,4.1-6.1,4.1-9.8C447.7,393.2,446.2,389.7,443.6,387.1z"/></svg>';
                $html .='</div>';
                $html .='<form id="vt_resend_email_form" method="post" action="" name="vt-resend-email-form">';
                        foreach ($send_email_form_fields as $form_field){
                            $html .= usb_swiper_get_html_field($form_field);
                        }
                    $html .='<div class="button-wrap">';
                        $html .='<button id="vt_send_email_cancel" type="reset" class="vt-button">'.__( 'Cancel', 'usb-swiper').'</button>';
                        $html .='<button id="vt_send_email_submit" type="submit" class="vt-button">'.__( 'Send Email Receipt', 'usb-swiper').'</button>';
                    $html .='</div>';
                $html .='</form>';
            $html .='</div>';
        $html .='</div>';

        return $html;
    }
}

/**
 * Get all transactions status.
 *
 * @since 2.0.2
 *
 * @return mixed|null
 */
function usbswiper_get_transaction_status_lists() {

    $status_lists = apply_filters(
        'usbswiper_get_transaction_status_lists',
        [
                'created' =>  __('Created','usb-swiper'),
                'pending' => __('Pending','usb-swiper'),
                'completed' => __('Completed','usb-swiper'),
                'paid' => __('Paid','usb-swiper'),
                'authorized' => __('Authorized','usb-swiper'),
                'partially_refunded' => __('Partially Refunded','usb-swiper'),
                'refunded' => __('Refunded','usb-swiper'),
                'failed' => __('Failed','usb-swiper'),
                'declined' => __('Declined','usb-swiper'),
        ]
    );

    return $status_lists;
}

/**
 * Get price decimal step.
 *
 * This function manage to input type number step value.
 * In this function manage step based on WooCommerce price decimals setting.
 *
 * @since 2.0.2
 *
 * @return string
 */
function usbswiper_get_price_step() {

	$decimals = wc_get_price_decimals();

	return sprintf("0.%0{$decimals}d", 1 );
}

/**
 * Get pagination links.
 *
 * @since 2.2.2
 *
 * @param array $args get pagination arguments.
 * @return string|bool
 */
function usbswiper_get_pagination( $args ) {

	if( empty( $args['max_num_pages'] ) ) {
		return false;
	}

	$big = 999999999;

	$main_pagenum = get_pagenum_link($big);

	$format =  !empty( $args['format'] ) ? $args['format'] : '?paged=%#%';
	$query_arg = !empty( $format ) ? explode('=', trim( $format, '?' )) : '';
	$query_key = !empty( $query_arg[0] ) ? $query_arg[0] : '';
	$get_link = !empty( $query_key ) ? remove_query_arg($query_key, $main_pagenum) : '';
	$get_link = str_replace('/page/'.$big, $format, esc_url($get_link));

	return paginate_links( array(
		'base' => $get_link,
		'format' => $format,
		'current' => max( 1, $args['current_page'] ),
		'total' => $args['max_num_pages'],
		'type' => !empty( $args['type'] ) ? $args['type'] : 'list',
		'prev_text'          => !empty( $args['prev_text'] ) ? $args['prev_text'] : '&laquo;',
		'next_text'          => !empty( $args['next_text'] ) ? $args['next_text'] : '&raquo;',
	) );
}

/**
 * Get the current user timezone.
 *
 * @sience 1.1.17
 *
 * @return false|mixed|null
 */
function usbswiper_get_user_timezone( $user_id = 0 ) {

    if( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    $current_offset = get_option( 'gmt_offset' );
    $tzstring       = get_option( 'timezone_string' );

    if ( str_contains( $tzstring, 'Etc/GMT' ) ) {
        $tzstring = '';
    }

    if ( empty( $tzstring ) ) {
        if ( 0 == $current_offset ) {
            $tzstring = 'UTC+0';
        } elseif ( $current_offset < 0 ) {
            $tzstring = 'UTC' . $current_offset;
        } else {
            $tzstring = 'UTC+' . $current_offset;
        }
    }

    $user_timezone = get_user_meta( $user_id,'vt_user_timezone', true);

    return !empty( $user_timezone ) ? $user_timezone : $tzstring;

}

/**
 * Get date from current timezone user wise.
 *
 * @since 2.3.3
 *
 * @param int $user_id Get user id
 * @param bool $is_timezone Check is timezone.
 * @return false|mixed|string|null
 * @throws Exception
 */
function usbswiper_get_user_date_i18n( $user_id = 0, $is_timezone = false ) {

    if( empty( $user_id ) ) {
        $user_id = get_current_user_id();
    }

    $timezone = usbswiper_get_user_timezone( $user_id );

    if( !empty( $timezone ) && str_contains( $timezone, 'UTC') !== false ) {

        if( $timezone !== 'UTC' ) {
            $gmt_offset = str_replace('UTC', '', $timezone);
            $offset  = (float) $gmt_offset;
            $hours   = (int) $offset;
            $minutes = ( $offset - $hours );

            $sign      = ( $offset < 0 ) ? '-' : '+';
            $abs_hour  = abs( $hours );
            $abs_mins  = abs( $minutes * 60 );
            $timezone = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
        }
    }


    if( $is_timezone ) {
        return $timezone;
    }

    $timezone   = new DateTimeZone( $timezone );
    $custom_datetime = new DateTime('now', $timezone);
    $date = $custom_datetime->format('Y-m-d H:i:s');

    return !empty( $date ) ? $date : '';
}

/**
 * Get zettle transaction tip amount.
 *
 * @since 2.3.4
 *
 * @param int $transaction_id Get transaction id.
 * @return float|int|string
 */
function usbswiper_get_zettle_transaction_tip_amount( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$payment_response = get_post_meta( $transaction_id, '_payment_response', true);

    $result_payload = !empty( $payment_response['result_payload'] ) ? $payment_response['result_payload'] : '';
	$result_payload = !empty( $payment_response['resultPayload'] ) ? $payment_response['resultPayload'] : $result_payload;

    $result_status = !empty( $payment_response['result_status'] ) ? $payment_response['result_status'] : '';
    $result_status = !empty( $payment_response['resultStatus'] ) ? $payment_response['resultStatus'] : $result_status;

	$tip_amount = 0;
	if( !empty( $result_status ) && !empty( $result_payload ) ) {
		$tip_amount =  !empty( $result_payload->REFERENCES->gratuityAmount ) ? $result_payload->REFERENCES->gratuityAmount : '';
		$tip_amount = !empty( $tip_amount) ? ( $tip_amount / 100 ) : 0;
	}

	return $tip_amount;
}

/**
 * Get zettle transaction grand total amount.
 *
 * @since 2.3.4
 *
 * @param int $transaction_id Get $transaction id.
 * @return float|int|mixed|string
 */
function usbswiper_get_zettle_transaction_total( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$transaction_type = get_post_meta( $transaction_id, '_transaction_type', true);

	$grand_total = get_post_meta( $transaction_id, 'GrandTotal', true );

    if( !empty( $transaction_type ) && strtolower( $transaction_type ) === 'zettle' ) {

	    $payment_response = get_post_meta( $transaction_id, '_payment_response', true);

        $result_payload = !empty( $payment_response['result_payload'] ) ? $payment_response['result_payload'] : '';
        $result_payload = !empty( $payment_response['resultPayload'] ) ? $payment_response['resultPayload'] : $result_payload;

	    $result_status = !empty( $payment_response['result_status'] ) ? $payment_response['result_status'] : '';
	    $result_status = !empty( $payment_response['resultStatus'] ) ? $payment_response['resultStatus'] : $result_status;

	    if( !empty( $result_status ) && !empty( $result_payload ) ) {

		    $tip_amount =  !empty( $result_payload->REFERENCES->gratuityAmount ) ? $result_payload->REFERENCES->gratuityAmount : '';
		    if( !empty( $tip_amount ) ) {
			    $grand_total = $grand_total + ( $tip_amount / 100);
		    }
	    }
    }

    return !empty( $grand_total ) ?  trim( $grand_total ) : 0;
}

/**
 * Get zettle transaction tracking id.
 *
 * @since 2.3.4
 *
 * @param int $transaction_id Get $transaction id.
 * @return string
 */
function usbswiper_get_zettle_tracking_id( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return '';
	}

	$tracking_id = '';
	$transaction_type = get_post_meta( $transaction_id, '_transaction_type', true);
	if( !empty( $transaction_type ) && strtolower( $transaction_type ) === 'zettle' ) {

		$payment_response = get_post_meta($transaction_id, '_payment_response', true);
		$result_payload = !empty( $payment_response['result_payload'] )  ? $payment_response['result_payload'] : [];
		$result_payload = !empty( $payment_response['resultPayload'] )  ? $payment_response['resultPayload'] : $result_payload;
		$tracking_id = !empty( $result_payload->REFERENCES->trackingId ) ? $result_payload->REFERENCES->trackingId : '';
	}

    return $tracking_id;
}

/**
 * Convert zettle amount to display amount.
 *
 * @since 2.3.4
 *
 * @param float $amount Get amount.
 * @return float|int
 */
function usbswiper_convert_zettle_amount( $amount ) {

    return !empty( $amount ) ? $amount / 100 : 0;
}

/**
 * Get zettle transaction refund total.
 *
 * @since 2.3.4
 *
 * @param int $transaction_id Get transaction id.
 * @return float|int|mixed $total_refund_amount
 */
function usbswiper_get_zettle_transaction_refund_total( $transaction_id ) {

	if( empty( $transaction_id ) ) {
		return 0;
	}

	$payment_refund_response = get_post_meta( $transaction_id,'_payment_refund_response', true);

	$total_refund_amount = 0;
	if( !empty( $payment_refund_response ) && is_array( $payment_refund_response ) ) {

		foreach ( $payment_refund_response as $key => $refund_response ) {

			$result_payload = !empty( $refund_response['result_payload'] ) ? $refund_response['result_payload'] : '';
			$result_payload = !empty( $refund_response['resultPayload'] ) ? $refund_response['resultPayload'] : $result_payload;
			$refund_amount = !empty( $result_payload->REFUNDED_AMOUNT ) ? $result_payload->REFUNDED_AMOUNT : 0;
			$refund_amount = !empty( $refund_amount ) ? $refund_amount/100 : 0;
			$total_refund_amount =  $total_refund_amount + $refund_amount;
		}
	}

    return !empty( $total_refund_amount ) ? trim($total_refund_amount) : 0;
}

/**
 * Get the Void confirmation popup html
 *
 * @since 1.1.17
 *
 * @return string
 */
function void_confirmation_html(){

	ob_start(); ?>

    <div class="vt-void-popup-wrapper">
        <div class="popup-loader"></div>
        <div class="vt-void-popup-inner">
            <div class="close">
                <a href="javascript:void(0);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></a>
            </div>
            <div class="vt-notification-content">
                <div class="input-field-wrap ">
                    <p><?php _e('Are you sure you want to void this authorization?','usb-swiper'); ?></p>
                </div>
                <div class="input-field-wrap button-wrap">
                    <button id="vt_void_cancel" type="reset" class="vt-button"><?php _e('Cancel','usb-swiper'); ?></button>
                    <a class="vt-button void-transaction" href="#"><?php _e('VOID','usb-swiper'); ?></a>
                </div>
            </div>
        </div>
    </div>
	<?php
	$html = ob_get_clean();

	return !empty( $html ) ? $html : '';
}

/**
 * Get Screen Timeout options.
 *
 * @since 3.2.2
 *
 * @return mixed|null
 */
function usb_swiper_get_timeout_options() {

    return apply_filters( 'usb_swiper_get_timeout_options', [
	    'never' => __( 'Never', 'usb-swiper'),
	    '15' => __( '15 Min of Inactivity', 'usb-swiper'),
	    '30' => __( '30 Min of Inactivity', 'usb-swiper'),
	    '60' => __( '60 Min of Inactivity', 'usb-swiper'),
    ]);
}

/**
 * Get Default Screen Timeout option.
 *
 * @since 3.2.2
 *
 * @return mixed|null
 */
function usb_swiper_get_default_timeout() {

	return apply_filters( 'usb_swiper_get_default_timeout', '30');
}

/**
 * Get user selected timout option using user id.
 *
 * @since 3.2.2
 *
 * @param int $user_id Get user id.
 * @return string $timeout
 */
function usb_swiper_get_user_timeout_option( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$timeout = usb_swiper_get_default_timeout();
	if( is_user_logged_in() ) {
		$timeout_option = get_user_meta( $user_id, 'timeout_option', true);
		$timeout = !empty( $timeout_option ) ? $timeout_option : usb_swiper_get_default_timeout();
	}

    return apply_filters( 'usb_swiper_get_user_timeout_option', $timeout);
}