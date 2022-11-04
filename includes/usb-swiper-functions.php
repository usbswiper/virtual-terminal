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

	return apply_filters( 'usb_swiper_get_countries', array(
		'' => __('Choose a country','usb-swiper'),
		'US' => __('United States','usb-swiper'),
		'GB' => __('United Kingdom','usb-swiper'),
		'AF' => __('Afghanistan','usb-swiper'),
		'AX' => __('Aland Islands','usb-swiper'),
		'AL' => __('Albania','usb-swiper'),
		'DZ' => __('Algeria','usb-swiper'),
		'AS' => __('American Samoa','usb-swiper'),
		'AD' => __('Andorra','usb-swiper'),
		'AO' => __('Angola','usb-swiper'),
		'AI' => __('Anguilla','usb-swiper'),
		'AQ' => __('Antarctica','usb-swiper'),
		'AG' => __('Antigua & Barbuda','usb-swiper'),
		'AR' => __('Argentina','usb-swiper'),
		'AM' => __('Armenia','usb-swiper'),
		'AW' => __('Aruba','usb-swiper'),
		'AU' => __('Australia','usb-swiper'),
		'AT' => __('Austria','usb-swiper'),
		'AZ' => __('Azerbaijan','usb-swiper'),
		'BS' => __('Bahamas','usb-swiper'),
		'BH' => __('Bahrain','usb-swiper'),
		'BD' => __('Bangladesh','usb-swiper'),
		'BB' => __('Barbados','usb-swiper'),
		'BY' => __('Belarus','usb-swiper'),
		'BE' => __('Belgium','usb-swiper'),
		'BZ' => __('Belize','usb-swiper'),
		'BJ' => __('Benin','usb-swiper'),
		'BM' => __('Bermuda','usb-swiper'),
		'BT' => __('Bhutan','usb-swiper'),
		'BO' => __('Bolivia','usb-swiper'),
		'BA' => __('Bosnia & Herzegovina','usb-swiper'),
		'BW' => __('Botswana','usb-swiper'),
		'BV' => __('Bouvet Island','usb-swiper'),
		'BR' => __('Brazil','usb-swiper'),
		'IO' => __('British Indian Ocean Territory','usb-swiper'),
		'BN' => __('Brunei Darussalam','usb-swiper'),
		'BG' => __('Bulgaria','usb-swiper'),
		'BF' => __('Burkina Faso','usb-swiper'),
		'BI' => __('Burundi','usb-swiper'),
		'KH' => __('Cambodia','usb-swiper'),
		'CM' => __('Cameroon','usb-swiper'),
		'CA' => __('Canada','usb-swiper'),
		'CV' => __('Cape Verde','usb-swiper'),
		'KY' => __('Cayman Islands','usb-swiper'),
		'CF' => __('Central African Rep','usb-swiper'),
		'TD' => __('Chad','usb-swiper'),
		'CL' => __('Chile','usb-swiper'),
		'CN' => __('China','usb-swiper'),
		'CX' => __('Christmas Island','usb-swiper'),
		'CC' => __('Cocos (Keeling) Islands','usb-swiper'),
		'CO' => __('Colombia','usb-swiper'),
		'KM' => __('Comoros','usb-swiper'),
		'CG' => __('Congo','usb-swiper'),
		'CK' => __('Cook Islands','usb-swiper'),
		'CR' => __('Costa Rica','usb-swiper'),
		'CI' => __("Côte d'Ivoire",'usb-swiper'),
		'HR' => __('Croatia','usb-swiper'),
		'CU' => __('Cuba','usb-swiper'),
		'CY' => __('Cyprus','usb-swiper'),
		'CZ' => __('Czech Republic','usb-swiper'),
		'CD' => __('Dem Rep of Congo (Zaire)','usb-swiper'),
		'DK' => __('Denmark','usb-swiper'),
		'DJ' => __('Djibouti','usb-swiper'),
		'DM' => __('Dominica','usb-swiper'),
		'DO' => __('Dominican Republic','usb-swiper'),
		'EC' => __('Ecuador','usb-swiper'),
		'EG' => __('Egypt','usb-swiper'),
		'SV' => __('El Salvador','usb-swiper'),
		'GQ' => __('Equatorial Guinea','usb-swiper'),
		'ER' => __('Eritrea','usb-swiper'),
		'EE' => __('Estonia','usb-swiper'),
		'ET' => __('Ethiopia','usb-swiper'),
		'FK' => __('Falkland Islands (Malvinas)','usb-swiper'),
		'FO' => __('Faeroe Islands','usb-swiper'),
		'FJ' => __('Fiji','usb-swiper'),
		'FI' => __('Finland','usb-swiper'),
		'FR' => __('France','usb-swiper'),
		'GF' => __('French Guiana','usb-swiper'),
		'PF' => __('French Polynesia/Tahiti','usb-swiper'),
		'TF' => __('French Southern Territories','usb-swiper'),
		'GA' => __('Gabon','usb-swiper'),
		'GM' => __('Gambia','usb-swiper'),
		'GE' => __('Georgia','usb-swiper'),
		'DE' => __('Germany','usb-swiper'),
		'GH' => __('Ghana','usb-swiper'),
		'GI' => __('Gibraltar','usb-swiper'),
		'GR' => __('Greece','usb-swiper'),
		'GL' => __('Greenland','usb-swiper'),
		'GD' => __('Grenada','usb-swiper'),
		'GP' => __('Guadeloupe','usb-swiper'),
		'GU' => __('Guam','usb-swiper'),
		'GT' => __('Guatemala','usb-swiper'),
		'GG' => __('Guernsey','usb-swiper'),
		'GN' => __('Guinea','usb-swiper'),
		'GW' => __('Guinea-Bissau','usb-swiper'),
		'GY' => __('Guyana','usb-swiper'),
		'HT' => __('Haiti','usb-swiper'),
		'HM' => __('Heard Island & McDonald Islands','usb-swiper'),
		'VA' => __('Holy See (Vatican City State)','usb-swiper'),
		'HN' => __('Honduras','usb-swiper'),
		'HK' => __('Hong Kong','usb-swiper'),
		'HU' => __('Hungary','usb-swiper'),
		'IS' => __('Iceland','usb-swiper'),
		'IN' => __('India','usb-swiper'),
		'ID' => __('Indonesia','usb-swiper'),
		'IR' => __('Iran','usb-swiper'),
		'IQ' => __('Iraq','usb-swiper'),
		'IE' => __('Ireland','usb-swiper'),
		'IM' => __('Isle of Man','usb-swiper'),
		'IL' => __('Israel','usb-swiper'),
		'IT' => __('Italy','usb-swiper'),
		'CI' => __('Ivory Coast','usb-swiper'),
		'JM' => __('Jamaica','usb-swiper'),
		'JP' => __('Japan','usb-swiper'),
		'JE' => __('Jersey','usb-swiper'),
		'JO' => __('Jordan','usb-swiper'),
		'KZ' => __('Kazakhstan','usb-swiper'),
		'KE' => __('Kenya','usb-swiper'),
		'KI' => __('Kiribati','usb-swiper'),
		'KP' => __('Korea, Democratic Republic of','usb-swiper'),
		'KR' => __('Korea, Republic of','usb-swiper'),
		'KW' => __('Kuwait','usb-swiper'),
		'KG' => __('Kyrgyzstan','usb-swiper'),
		'LA' => __('Laos','usb-swiper'),
		'LV' => __('Latvia','usb-swiper'),
		'LB' => __('Lebanon','usb-swiper'),
		'LS' => __('Lesotho','usb-swiper'),
		'LR' => __('Liberia','usb-swiper'),
		'LY' => __('Libya','usb-swiper'),
		'LI' => __('Liechtenstein','usb-swiper'),
		'LT' => __('Lithuania','usb-swiper'),
		'LU' => __('Luxembourg','usb-swiper'),
		'MO' => __('Macau','usb-swiper'),
		'MK' => __('Macedonia','usb-swiper'),
		'MG' => __('Madagascar','usb-swiper'),
		'MW' => __('Malawi','usb-swiper'),
		'MY' => __('Malaysia','usb-swiper'),
		'MV' => __('Maldives','usb-swiper'),
		'ML' => __('Mali','usb-swiper'),
		'MT' => __('Malta','usb-swiper'),
		'MH' => __('Marshall Islands','usb-swiper'),
		'MQ' => __('Martinique','usb-swiper'),
		'MR' => __('Mauritania','usb-swiper'),
		'MU' => __('Mauritius','usb-swiper'),
		'MX' => __('Mexico','usb-swiper'),
		'FM' => __('Micronesia','usb-swiper'),
		'MD' => __('Moldova','usb-swiper'),
		'MC' => __('Monaco','usb-swiper'),
		'MN' => __('Mongolia','usb-swiper'),
		'MS' => __('Montserrat','usb-swiper'),
		'MA' => __('Morocco','usb-swiper'),
		'MZ' => __('Mozambique','usb-swiper'),
		'MM' => __('Myanmar','usb-swiper'),
		'NA' => __('Namibia','usb-swiper'),
		'NR' => __('Nauru','usb-swiper'),
		'NP' => __('Nepal','usb-swiper'),
		'NL' => __('Netherlands','usb-swiper'),
		'AN' => __('Netherlands Antilles','usb-swiper'),
		'NC' => __('New Caledonia','usb-swiper'),
		'NZ' => __('New Zealand','usb-swiper'),
		'NI' => __('Nicaragua','usb-swiper'),
		'NE' => __('Niger','usb-swiper'),
		'NG' => __('Nigeria','usb-swiper'),
		'NU' => __('Niue','usb-swiper'),
		'NF' => __('Norfolk Island','usb-swiper'),
		'MP' => __('Northern Mariana Islands','usb-swiper'),
		'NO' => __('Norway','usb-swiper'),
		'OM' => __('Oman','usb-swiper'),
		'PK' => __('Pakistan','usb-swiper'),
		'PW' => __('Palau','usb-swiper'),
		'PS' => __('Palestinian Territory','usb-swiper'),
		'PA' => __('Panama','usb-swiper'),
		'PG' => __('Papua New Guinea','usb-swiper'),
		'PY' => __('Paraguay','usb-swiper'),
		'PE' => __('Peru','usb-swiper'),
		'PH' => __('Philippines','usb-swiper'),
		'PN' => __('Pitcairn','usb-swiper'),
		'PL' => __('Poland','usb-swiper'),
		'PT' => __('Portugal','usb-swiper'),
		'PR' => __('Puerto Rico','usb-swiper'),
		'QA' => __('Qatar','usb-swiper'),
		'RE' => __('Reunion Is.','usb-swiper'),
		'RO' => __('Romania','usb-swiper'),
		'RU' => __('Russia','usb-swiper'),
		'RW' => __('Rwanda','usb-swiper'),
		'SH' => __('Saint Helena','usb-swiper'),
		'KN' => __('Saint Kitts & Nevis','usb-swiper'),
		'LC' => __('Saint Lucia','usb-swiper'),
		'PM' => __('Saint Pierre & Miquelon','usb-swiper'),
		'VC' => __('Saint Vincent & Grenadines','usb-swiper'),
		'AS' => __('Samoa (Amer.)','usb-swiper'),
		'WS' => __('Samoa (Western)','usb-swiper'),
		'SM' => __('San Marino','usb-swiper'),
		'KN' => __('Sao Tome & Principe','usb-swiper'),
		'SA' => __('Saudi Arabia','usb-swiper'),
		'SN' => __('Senegal','usb-swiper'),
		'CS' => __('Serbia & Montenegro','usb-swiper'),
		'SC' => __('Seychelles','usb-swiper'),
		'SL' => __('Sierra Leone','usb-swiper'),
		'SG' => __('Singapore','usb-swiper'),
		'SK' => __('Slovakia','usb-swiper'),
		'SI' => __('Slovenia','usb-swiper'),
		'SB' => __('Solomon Islands','usb-swiper'),
		'ZA' => __('South Africa','usb-swiper'),
		'GS' => __('South Georgia & S. Sandwich Islands','usb-swiper'),
		'ES' => __('Spain','usb-swiper'),
		'LK' => __('Sri Lanka','usb-swiper'),
		'SD' => __('Sudan','usb-swiper'),
		'SR' => __('Suriname','usb-swiper'),
		'SR' => __('Svalbard & Jan Mayen','usb-swiper'),
		'SZ' => __('Swaziland','usb-swiper'),
		'SE' => __('Sweden','usb-swiper'),
		'CH' => __('Switzerland','usb-swiper'),
		'SY' => __('Syria','usb-swiper'),
		'TW' => __('Taiwan','usb-swiper'),
		'TJ' => __('Tajikistan','usb-swiper'),
		'TZ' => __('Tanzania','usb-swiper'),
		'TH' => __('Thailand','usb-swiper'),
		'TL' => __('Timor-Leste','usb-swiper'),
		'TG' => __('Togo','usb-swiper'),
		'TK' => __('Tokelau','usb-swiper'),
		'TO' => __('Tonga','usb-swiper'),
		'TT' => __('Trinidad & Tobago','usb-swiper'),
		'TN' => __('Tunisia','usb-swiper'),
		'TR' => __('Turkey','usb-swiper'),
		'TM' => __('Turkmenistan','usb-swiper'),
		'TC' => __('Turks & Caicos Islands','usb-swiper'),
		'TV' => __('Tuvalu','usb-swiper'),
		'UG' => __('Uganda','usb-swiper'),
		'UA' => __('Ukraine','usb-swiper'),
		'AE' => __('United Arab Emirates','usb-swiper'),
		'GB' => __('United Kingdom','usb-swiper'),
		'US' => __('United States','usb-swiper'),
		'UM' => __('United States Minor Outlying Islands','usb-swiper'),
		'UY' => __('Uruguay','usb-swiper'),
		'UZ' => __('Uzbekistan','usb-swiper'),
		'VU' => __('Vanuatu','usb-swiper'),
		'VE' => __('Venezuela','usb-swiper'),
		'VN' => __('Vietnam','usb-swiper'),
		'VG' => __('Virgin Islands, British','usb-swiper'),
		'VI' => __('Virgin Islands, US','usb-swiper'),
		'WF' => __('Wallis & Futuna Isle','usb-swiper'),
		'EH' => __('Western Sahara','usb-swiper'),
		'YE' => __('Yemen','usb-swiper'),
		'ZM' => __('Zambia','usb-swiper'),
		'ZW' => __('Zimbabwe','usb-swiper'),
	));
}

/**
 * Get state lists.
 *
 * @since 1.0.0
 *
 * @return mixed|void
 */
function usb_swiper_get_states() {

	return apply_filters( 'usb_swiper_get_states', array(
		'' => __('Choose a state','usb-swiper'),
		'AL' => __('Alabama','usb-swiper'),
		'AK' => __('Alaska','usb-swiper'),
		'AS' => __('American Samoa','usb-swiper'),
		'AZ' => __('Arizona','usb-swiper'),
		'AR' => __('Arkansas','usb-swiper'),
		'CA' => __('California','usb-swiper'),
		'CO' => __('Colorado','usb-swiper'),
		'CT' => __('Connecticut','usb-swiper'),
		'DE' => __('Delaware','usb-swiper'),
		'DC' => __('District Of Columbia','usb-swiper'),
		'FM' => __('Federated States Of Micronesia','usb-swiper'),
		'FL' => __('Florida','usb-swiper'),
		'GA' => __('Georgia','usb-swiper'),
		'GU' => __('Guam','usb-swiper'),
		'HI' => __('Hawaii','usb-swiper'),
		'ID' => __('Idaho','usb-swiper'),
		'IL' => __('Illinois','usb-swiper'),
		'IN' => __('Indiana','usb-swiper'),
		'IA' => __('Iowa','usb-swiper'),
		'KS' => __('Kansas','usb-swiper'),
		'KY' => __('Kentucky','usb-swiper'),
		'LA' => __('Louisiana','usb-swiper'),
		'ME' => __('Maine','usb-swiper'),
		'MH' => __('Marshall Islands','usb-swiper'),
		'MD' => __('Maryland','usb-swiper'),
		'MA' => __('Massachusetts','usb-swiper'),
		'MI' => __('Michigan','usb-swiper'),
		'MN' => __('Minnesota','usb-swiper'),
		'MS' => __('Mississippi','usb-swiper'),
		'MO' => __('Missouri','usb-swiper'),
		'MT' => __('Montana','usb-swiper'),
		'NE' => __('Nebraska','usb-swiper'),
		'NV' => __('Nevada','usb-swiper'),
		'NH' => __('New Hampshire','usb-swiper'),
		'NJ' => __('New Jersey','usb-swiper'),
		'NM' => __('New Mexico','usb-swiper'),
		'NY' => __('New York','usb-swiper'),
		'NC' => __('North Carolina','usb-swiper'),
		'ND' => __('North Dakota','usb-swiper'),
		'MP' => __('Northern Mariana Islands','usb-swiper'),
		'OH' => __('Ohio','usb-swiper'),
		'OK' => __('Oklahoma','usb-swiper'),
		'OR' => __('Oregon','usb-swiper'),
		'PW' => __('Palau','usb-swiper'),
		'PA' => __('Pennsylvania','usb-swiper'),
		'PR' => __('Puerto Rico','usb-swiper'),
		'RI' => __('Rhode Island','usb-swiper'),
		'SC' => __('South Carolina','usb-swiper'),
		'SD' => __('South Dakota','usb-swiper'),
		'TN' => __('Tennessee','usb-swiper'),
		'TX' => __('Texas','usb-swiper'),
		'UT' => __('Utah','usb-swiper'),
		'VT' => __('Vermont','usb-swiper'),
		'VI' => __('Virgin Islands','usb-swiper'),
		'VA' => __('Virginia','usb-swiper'),
		'WA' => __('Washington','usb-swiper'),
		'WV' => __('West Virginia','usb-swiper'),
		'WI' => __('Wisconsin','usb-swiper'),
		'WY' => __('Wyoming','usb-swiper'),
		'AA' => __('Armed Forces Americas','usb-swiper'),
		'AE' => __('Armed Forces','usb-swiper'),
		'AP' => __('Armed Forces Pacific','usb-swiper'),
		'AB' => __('Alberta','usb-swiper'),
		'BC' => __('British Columbia','usb-swiper'),
		'MB' => __('Manitoba','usb-swiper'),
		'NB' => __('New Brunswick','usb-swiper'),
		'NF' => __('Newfoundland and Labrador','usb-swiper'),
		'NT' => __('Northwest Territories','usb-swiper'),
		'NS' => __('Nova Scotia','usb-swiper'),
		'NU' => __('Nunavut','usb-swiper'),
		'ON' => __('Ontario','usb-swiper'),
		'PE' => __('Prince Edward Island','usb-swiper'),
		'QC' => __('Quebec','usb-swiper'),
		'SK' => __('Saskatchewan','usb-swiper'),
		'YK' => __('Yukon','usb-swiper'),
	));
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

	$get_countries = usb_swiper_get_countries();
	$get_states = usb_swiper_get_states();

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
				'class' => 'vt-billing-address-field',
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
				'class' => 'vt-billing-address-field',
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
				'class' => 'vt-shipping-address-field',
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
				'class' => 'vt-shipping-address-field',
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

	if( !is_user_logged_in()){
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