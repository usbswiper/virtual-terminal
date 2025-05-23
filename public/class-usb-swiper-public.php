<?php

/**
 * Check Usb_Swiper_Public class exists or not.
 *
 * @since 1.0.0
 */
if( !class_exists( 'Usb_Swiper_Public' ) ) {

	/**
	 * The public-facing functionality of the plugin.
	 *
	 * @link       http://www.angelleye.com/product/usb-swiper
	 * @since      1.0.0
	 *
	 * @package    usb-swiper
	 * @subpackage usb-swiper/public
	 * @author     AngellEYE <andrew@angelleye.com>
	 */
	class Usb_Swiper_Public {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var string  $version    The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since   1.0.0
		 * @param   string  $plugin_name    The name of this plugin.
		 * @param   string  $version    The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version = $version;

			$settings = usb_swiper_get_settings('general');
			$this->settings = $settings;
			$this->is_sandbox = !empty( $settings['is_paypal_sandbox'] );
			$this->payment_action = !empty( $settings['payment_action'] ) ? $settings['payment_action']: 'capture';
		}

		/**
         * Get Paypal checkout sdk object.
         *
         * @since 1.0.0
         *
		 * @return array $smart_js_arg
		 */
		public function get_paypal_sdk_obj( ) {

			$smart_js_arg = array();

			$this->currency_list = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD');
			$this->currency = in_array(usbswiper_get_default_currency(), $this->currency_list) ? usbswiper_get_default_currency() : 'USD';

			//$smart_js_arg['currency'] = $this->currency;

			if ($this->is_sandbox) {
				if (is_user_logged_in() && WC()->customer && WC()->customer->get_billing_country() && 2 === strlen(WC()->customer->get_billing_country())) {
					$smart_js_arg['buyer-country'] = WC()->customer->get_billing_country();
				}
				$smart_js_arg['client-id'] = usb_swiper_get_field_value('sandbox_client_id');
			} else {
				$smart_js_arg['client-id'] = usb_swiper_get_field_value('client_id');
			}

			$get_merchant_data = usbswiper_get_onboarding_merchant_response();
			$merchant_id = !empty( $get_merchant_data['merchant_id'] ) ? $get_merchant_data['merchant_id'] : '';

			if( !empty( $merchant_id ) ) {
				$smart_js_arg['merchant-id'] = $merchant_id;
			}

			$smart_js_arg['commit'] = apply_filters( 'usb_swiper_skip_final_review', 'true' );
			$smart_js_arg['locale'] = self::get_button_locale_code();

			$components = array("buttons","hosted-fields","funding-eligibility","messages");

			if (!empty($components)) {
				$smart_js_arg['components'] = apply_filters('usb_swiper_paypal_checkout_sdk_components', implode(',', $components));
			}

            $settings = usb_swiper_get_settings('general');
            $vt_invoice_page_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';

            if( $vt_invoice_page_id === get_the_ID() ) {
                $invoice_session = !empty($_GET['invoice-session']) ? json_decode( base64_decode($_GET['invoice-session'])) : '';
                $invoice_id = !empty( $invoice_session->id ) ? trim($invoice_session->id, 'invoice_') :'';
                $payment_intent = usbswiper_get_transaction_type($invoice_id);
                $smart_js_arg['intent'] = !empty( $payment_intent ) ? strtolower( $payment_intent ) : '';
            }

			return $smart_js_arg;
        }


        /**
         * Register end point in wc_endpoints.
         *
         * @since 1.1.17
         *
         * @param  $query_vars
         *
         * @return
         */
        public function update_wc_endpoints( $query_vars ){

            $query_vars['view-transaction'] = 'view-transaction';
            $query_vars['transactions'] = 'transactions';
            //$query_vars['invoices'] = 'invoices';
	        //$query_vars['zettle-transactions'] = 'zettle-transactions';
            $query_vars['vt-products'] = 'vt-products';
            $query_vars['vt-tax-rules'] = 'vt-tax-rules';
            $query_vars['vt-zettle'] = 'vt-zettle';
            $query_vars['vt-customers'] = 'vt-customers';
            return $query_vars;
        }

		/**
         * Get current locale.
         *
         * @since 1.0.0
         *
		 * @return string|bool $locale
		 */
		public static function get_wpml_locale() {

			$locale = false;

			if(defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id')){

				global $sitepress;
				if ( isset( $sitepress )) { // avoids a fatal error with Polylang
					$locale = $sitepress->get_current_language();
				} else if ( function_exists( 'pll_current_language' ) ) { // adds Polylang support
					$locale = pll_current_language('locale'); //current selected language requested on the broswer
				} else if ( function_exists( 'pll_default_language' ) ) {
					$locale = pll_default_language('locale'); //default lanuage of the blog
				}
			}

			return $locale;
        }

		/**
         * Get button locale code.
         *
         * @since 1.0.0
         *
		 * @return bool|string $locale
		 */
		public static function get_button_locale_code() {

	        $_supportedLocale = array(
		        'en_US', 'fr_XC', 'es_XC', 'zh_XC', 'en_AU', 'de_DE', 'nl_NL',
		        'fr_FR', 'pt_BR', 'fr_CA', 'zh_CN', 'ru_RU', 'en_GB', 'zh_HK',
		        'he_IL', 'it_IT', 'ja_JP', 'pl_PL', 'pt_PT', 'es_ES', 'sv_SE', 'zh_TW', 'tr_TR'
	        );

	        $wpml_locale = self::get_wpml_locale();

	        if ($wpml_locale) {
		        if (in_array($wpml_locale, $_supportedLocale)) {
			        return $wpml_locale;
		        }
	        }

	        $locale = get_locale();
	        if (get_locale() != '') {
		        $locale = substr(get_locale(), 0, 5);
	        }

	        if (!in_array($locale, $_supportedLocale)) {
		        $locale = 'en_US';
	        }

	        return $locale;
        }

		/**
		 * Dequeue PayPal sdk scripts in VT selected pages and endpoint.
		 *
		 * @since 2.0.1
		 *
		 * @return void
		 */
		public function dequeue_script() {

			$settings = usb_swiper_get_settings('general');

			$allow_pages = [];
			$allow_pages[] = !empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
			$allow_pages[] = !empty( $settings['vt_verification_page'] ) ? (int)$settings['vt_verification_page'] : '';
			$allow_pages[] = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';

			$allow_pages = apply_filters( 'usb_swiper_dequeue_script_allow_pages', $allow_pages );

			if( ( !empty( $allow_pages ) && is_array( $allow_pages ) && in_array( get_the_ID(), $allow_pages ) ) || is_wc_endpoint_url('view-transaction') || is_wc_endpoint_url('transactions') || is_wc_endpoint_url('vt-products') || is_wc_endpoint_url('vt-tax-rules') || is_wc_endpoint_url('invoices') ) {
				wp_dequeue_script('angelleye-paypal-checkout-sdk');
				wp_dequeue_script('angelleye-paypal-checkout-sdk-async');
			}
		}

		/**
		 * Register and enqueue style and script in public area.
         *
         * @since 1.0.0
		 */
		public function enqueue_scripts() {

			wp_enqueue_style('dashicons');

			$settings = usb_swiper_get_settings('general');
			$vt_page_id = !empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
			$vt_verification_page_id = !empty( $settings['vt_verification_page'] ) ? (int)$settings['vt_verification_page'] : '';
			$myaccount_page_id = (int)get_option( 'woocommerce_myaccount_page_id' );
            $vt_pay_by_invoice_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_style( 'jquery-ui', USBSWIPER_URL . 'assets/css/jquery-ui.css' );
			/**
			 * Dequeue script before VT script enqueue.
			 *
			 * @since 2.0.1
			 */
			$this->dequeue_script();

			if( ! empty( $vt_page_id ) && $vt_page_id === get_the_ID() || ( ! empty( $vt_verification_page_id ) && $vt_verification_page_id === get_the_ID() ) || ( ! empty( $vt_pay_by_invoice_id ) && $vt_pay_by_invoice_id === get_the_ID() ) ) {

                $sdk_obj = $this->get_paypal_sdk_obj();
                wp_register_script( 'usb-swiper-paypal-checkout-sdk', add_query_arg( $sdk_obj, 'https://www.paypal.com/sdk/js?enable-funding=venmo' ), array(), null, false );
                wp_enqueue_script( 'usb-swiper-paypal-checkout-sdk' );

				wp_enqueue_style( 'bootstrap-switch', USBSWIPER_URL . 'assets/css/bootstrap-switch.min.css' );
				wp_enqueue_style( 'select2', USBSWIPER_URL . 'assets/css/select2.min.css' );
				wp_enqueue_script( 'bootstrap-min', USBSWIPER_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'bootstrap-switch', USBSWIPER_URL . 'assets/js/bootstrap-switch.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'pos-functions', USBSWIPER_URL . 'assets/js/pos-functions.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'validate-credit-card-number', USBSWIPER_URL . 'assets/js/validate-credit-card-number.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'parse-track-data', USBSWIPER_URL . 'assets/js/parse-track-data.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'autoNumeric', USBSWIPER_URL . 'assets/js/autoNumeric.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'jquery-validate', USBSWIPER_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( $this->plugin_name, USBSWIPER_URL . 'assets/js/usb-swiper.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'select2', USBSWIPER_URL . 'assets/js/select2.min.js', array( 'jquery' ), $this->version, true );

				wp_localize_script( $this->plugin_name, 'usb_swiper_settings', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'usb_swiper_transaction_nonce' => wp_create_nonce('usb_swiper_process_transaction'),
					'three_d_secure_contingency' => apply_filters('usb_swiper_three_d_secure_contingency', 'SCA_WHEN_REQUIRED'),
					'create_transaction_url' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'create_transaction', 'utm_nooverride' => '1', 'from' => 'vt_transaction' ), WC()->api_request_url( 'usb_swiper_transaction' ) ),
					'cc_capture' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'cc_capture', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'create_zettle_request' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'create_zettle_request', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'zettle_payment_response' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'zettle_payment_response', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'style_color' => apply_filters('usb_swiper_smart_button_style_color','gold'),
					'style_shape' => apply_filters('usb_swiper_smart_button_style_shape','rect'),
					'style_height' => apply_filters('usb_swiper_smart_button_style_height',''),
					'style_label' => apply_filters('usb_swiper_smart_button_style_label','paypal'),
					'style_layout' => apply_filters('usb_swiper_smart_button_style_layout','vertical'),
					'style_tagline' => apply_filters('usb_swiper_smart_button_style_tagline','yes'),
					'style_size' => apply_filters('usb_swiper_smart_button_style_size','responsive'),
                    'vt_page_url' => get_the_permalink($vt_page_id),
					'email_validation_message' => __( 'Please enter a valid email address.', 'usb-swiper' ),
                    'vt_page_id' => $vt_page_id,
                    'vt_paybyinvoice_page_id' => $vt_pay_by_invoice_id,
                    'vt_max_image_size' => __('Failed to upload an Brand logo. The Brand logo image maximum dimension is 250.','usb-swiper'),
                    'vt_timeout_message' => __('You are about to be logged out.', 'usb-swiper'),
                    'current_page_id' => get_the_ID(),
					'product_min_qty_message' => __("Only positive numbers are allowed for QTY.", 'usb-swiper'),
					'create_transaction_message' => __("Create a new transaction request for zettle", 'usb-swiper'),
					'zettle_socket_error_message' => __("Something went wrong. Please try again", 'usb-swiper'),
					'default_tax_tooltip_message' => __( 'Tax Rule: ', 'usb-swiper'),
					'delete_customer_confirm_message' => __( 'Are you sure you want to delete this customer?', 'usb-swiper'),
                    'start_new_order_conformation' => __( 'Are you sure you want to discard the current order details?', 'usb-swiper'),
                    'start_new_order_success' => __( 'New order started successfully.', 'usb-swiper'),
                    'is_customers' => false,
					'timeout_option' => usb_swiper_get_user_timeout_option(),
                    'default_customer_label' => __('Do you want to Save this customer’s record?', 'usb-swiper'),
                    'update_customer_label' => __('Do you want to Update this customer’s record?', 'usb-swiper'),
                    'update_customer_tooltip' => __('Select if you want to update the customer’s record.', 'usb-swiper'),
                    'display_vt_product_wrapper' => $this->display_vt_product_wrapper(),
				) );
			} elseif ( $myaccount_page_id === get_the_ID() ) {

				if( is_wc_endpoint_url('vt-customers') )  {
                    wp_enqueue_style( 'bootstrap-switch', USBSWIPER_URL . 'assets/css/bootstrap-switch.min.css' );
                    wp_enqueue_style( 'select2', USBSWIPER_URL . 'assets/css/select2.min.css' );
                    wp_enqueue_script( 'bootstrap-min', USBSWIPER_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), $this->version, true );
                    wp_enqueue_script( 'bootstrap-switch', USBSWIPER_URL . 'assets/js/bootstrap-switch.min.js', array( 'jquery' ), $this->version, true );
                    wp_enqueue_script( 'pos-functions', USBSWIPER_URL . 'assets/js/pos-functions.js', array( 'jquery' ), $this->version, true );
                    wp_enqueue_script( 'validate-credit-card-number', USBSWIPER_URL . 'assets/js/validate-credit-card-number.js', array( 'jquery' ), $this->version, true );
                    wp_enqueue_script( 'parse-track-data', USBSWIPER_URL . 'assets/js/parse-track-data.js', array( 'jquery' ), $this->version, true );
                    wp_enqueue_script( 'autoNumeric', USBSWIPER_URL . 'assets/js/autoNumeric.js', array( 'jquery' ), $this->version, true );
				}

				$sdk_obj = $this->get_paypal_sdk_obj();
				wp_register_script( 'usb-swiper-paypal-checkout-sdk', add_query_arg( $sdk_obj, 'https://www.paypal.com/sdk/js' ), array(), null, false );
				wp_enqueue_script( 'usb-swiper-paypal-checkout-sdk' );
				wp_enqueue_script( 'autoNumeric', USBSWIPER_URL . 'assets/js/autoNumeric.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'jquery-validate', USBSWIPER_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( $this->plugin_name, USBSWIPER_URL . 'assets/js/usb-swiper.js', array( 'jquery' ), $this->version, true );

				wp_localize_script( $this->plugin_name, 'usb_swiper_settings', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'usb_swiper_transaction_nonce' => wp_create_nonce('usb_swiper_process_transaction'),
					'three_d_secure_contingency' => apply_filters('usb_swiper_three_d_secure_contingency', 'SCA_WHEN_REQUIRED'),
					'create_transaction_url' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'create_transaction', 'utm_nooverride' => '1', 'from' => 'vt_transaction' ), WC()->api_request_url( 'usb_swiper_transaction' ) ),
					'cc_capture' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'cc_capture', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'create_zettle_request' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'create_zettle_request', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'zettle_payment_response' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'zettle_payment_response', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'style_color' => apply_filters('usb_swiper_smart_button_style_color','gold'),
					'style_shape' => apply_filters('usb_swiper_smart_button_style_shape','rect'),
					'style_height' => apply_filters('usb_swiper_smart_button_style_height',''),
					'style_label' => apply_filters('usb_swiper_smart_button_style_label','paypal'),
					'style_layout' => apply_filters('usb_swiper_smart_button_style_layout','vertical'),
					'style_tagline' => apply_filters('usb_swiper_smart_button_style_tagline','yes'),
					'style_size' => apply_filters('usb_swiper_smart_button_style_size','responsive'),
					'vt_page_url' => get_the_permalink($vt_page_id),
                    'confirm_message' => apply_filters( 'usb_swiper_product_delete_confirm_message', __('Are you sure you want to delete "{#product_title#}" product?','usb-swiper')),
                    'logo_delete_confirm_message' => apply_filters( 'usb_swiper_logo_delete_confirm_message', __('Are you sure you want to delete brand logo?','usb-swiper')),
                    'product_min_price' => apply_filters( 'usb_swiper_add_product_min_price_message',sprintf(__('Price must be greater than %s','usb-swiper'), strip_tags(wc_price(0, ['currency' => usbswiper_get_default_currency()])))),
                    'price_step_message' => apply_filters( 'usb_swiper_price_step_message',sprintf(__('Please enter a valid value. Allow only %s format.','usb-swiper'), strip_tags(wc_price(0, ['currency' => usbswiper_get_default_currency()])))),
                    'vt_page_id' => $vt_page_id,
                    'vt_paybyinvoice_page_id' => $vt_pay_by_invoice_id,
                    'vt_timeout_message' => __('You are about to be logged out.', 'usb-swiper'),
                    'vt_max_image_size' => __('Failed to upload an Brand logo. The Brand logo image maximum dimension is 250.','usb-swiper'),
                    'current_page_id' => get_the_ID(),
					'create_transaction_message' => __("Create a new transaction request for zettle", 'usb-swiper'),
					'zettle_socket_error_message' => __("Something went wrong. Please try again", 'usb-swiper'),
					'default_tax_tooltip_message' => __( 'Tax Rule: ', 'usb-swiper'),
                    'is_customers' => is_wc_endpoint_url('vt-customers'),
					'delete_customer_confirm_message' => __( 'Are you sure you want to delete this customer?', 'usb-swiper'),
                    'start_new_order_conformation' => __( 'Are you sure you want to discard the current order details?', 'usb-swiper'),
                    'start_new_order_success' => __( 'New order started successfully.', 'usb-swiper'),
                    'timeout_option' => usb_swiper_get_user_timeout_option(),
				) );
            }

			wp_enqueue_script( 'usb-swiper-general', USBSWIPER_URL . 'assets/js/usb-swiper-general.js', array( 'jquery' ), $this->version, true );
            wp_enqueue_style( 'pay-by-invoice', USBSWIPER_URL . 'assets/css/pay-by-invoice.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, USBSWIPER_URL . 'assets/css/usb-swiper.css' );

            wp_localize_script( 'usb-swiper-general', 'usb_swiper_general', array(
                    'is_user_logged_in' => is_user_logged_in()
            ));

        }

		/**
         * Clean up paypal checkout sdk url.
         *
         * @since 1.0.0
         *
		 * @param string $tag Get style tag.
		 * @param $handle
		 *
		 * @return string $tag
		 */
		public function clean_paypal_checkout_sdk_url( $tag, $handle ) {
            $settings = usb_swiper_get_settings('general');
            $vt_page_id = !empty($settings['virtual_terminal_page']) ? (int)$settings['virtual_terminal_page'] : '';
            $paybyinvoice_page_id = !empty($settings['vt_paybyinvoice_page']) ? (int)$settings['vt_paybyinvoice_page'] : '';

            $current_page_id = get_the_ID();

            $invoice_session = !empty($_GET['invoice-session']) ? json_decode( base64_decode($_GET['invoice-session'])) : '';
            $invoice_id = !empty( $invoice_session->id ) ? $invoice_session->id : '';
            $invoice_status = usbswiper_get_transaction_status($invoice_id);

			if ('usb-swiper-paypal-checkout-sdk' === $handle && ( ( !empty($vt_page_id) && $vt_page_id === $current_page_id ) || ( !empty($paybyinvoice_page_id) && $paybyinvoice_page_id === $current_page_id && strtolower($invoice_status) !== 'paid') ) ) {

				if( !class_exists('Usb_Swiper_Paypal_request') ) {
					include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
				}

				$Paypal_request = Usb_Swiper_Paypal_request::instance();
				$generate_token = $Paypal_request->get_generate_token();

				$client_token = "data-client-token='{$generate_token}'";

				$tag = str_replace(' src=', ' ' . $client_token . ' data-namespace="paypal" src=', $tag);
			}

		    return $tag;
        }

		/**
         * Add transactions menu in WooCommerce my account.
         *
         * @since 1.0.0
         *
		 * @param array $menu_links Get menu links.
		 *
		 * @return array $menu_links
		 */
		public function wc_account_menu_items( $menu_links ) {

			if( usb_swiper_allow_user_by_role('administrator')  || usb_swiper_allow_user_by_role('customer') ) {

				$logout = ! empty( $menu_links['customer-logout'] ) ? $menu_links['customer-logout'] : '';

				unset( $menu_links['customer-logout'] );

				$menu_links['transactions']         =   __( 'Transactions', 'usb-swiper' );
                //$menu_links['invoices']             =   __( 'Invoices', 'usb-swiper' );
				//$menu_links['zettle-transactions']  =   __( 'Zettle Transactions', 'usb-swiper' );
                $menu_links['vt-products']          =   __( 'Products', 'usb-swiper' );
                $menu_links['vt-tax-rules']         =   __( 'Tax Rules', 'usb-swiper' );
                $menu_links['vt-zettle']            =   __( 'Zettle POS', 'usb-swiper' );
                $menu_links['vt-customers']            =   __( 'Customers', 'usb-swiper' );
				$menu_links['customer-logout']      =   $logout;
			}

			return $menu_links;
		}

        /**
         * Filter woocommerce currency with paypal supported.
         *
         * @param $currency_code_options
         * @return mixed
         * @since 4.1.0
         */
        public function paypal_supported_currency($currency_code_options) {
            // Supported PayPal currencies
            $paypal_supported_currency = [
                'AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF',
                'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP',
                'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'USD'
            ];

            foreach ($currency_code_options as $code => $name) {
                if (!in_array($code, $paypal_supported_currency)) {
                    unset($currency_code_options[$code]);
                }
            }

            return $currency_code_options;
        }

		/**
		 * Add new transaction endpoint.
         *
         * @since 1.0.0
		 */
		public function endpoint_init() {

			add_rewrite_endpoint( 'transactions',  EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( 'view-transaction', EP_ROOT | EP_PAGES );
			//add_rewrite_endpoint( 'invoices', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'vt-products', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'vt-tax-rules', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'vt-zettle', EP_ROOT | EP_PAGES );
			//add_rewrite_endpoint( 'zettle-transactions', EP_ROOT | EP_PAGES );
			add_rewrite_endpoint( 'vt-customers', EP_ROOT | EP_PAGES );
		}

		/**
		 * Transactions endpoint callback method.
         *
         * @since 1.0.0
		 */
		public function transactions_endpoint_cb() {

			if( usb_swiper_allow_user_by_role('administrator') || usb_swiper_allow_user_by_role('customer') ) {

				$current_page   = !empty( $_GET['vt-page'] ) ? $_GET['vt-page'] : 1;
                $transaction_type   = !empty( $_GET['vt-type'] ) ? sanitize_text_field( $_GET['vt-type'] ) : "";
                $transaction_search   = !empty( $_GET['vt-search'] ) ? sanitize_text_field( $_GET['vt-search'] ) : "";

                $search_date = '';
                if( !empty($transaction_search) && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$transaction_search) ){
                    $search_date = $transaction_search;
                }

                $start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : '';
                $end_date = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : '';

                $order = isset( $_GET['date_toggle'] ) && sanitize_text_field( $_GET['date_toggle'] ) === 'asc' ? 'ASC' : 'DESC';

                $transaction_args = array(
                    'post_type' => 'transactions',
                    'post_status' => ['publish','future'],
                    'posts_per_page' => !empty( get_option( 'posts_per_page' ) ) ? get_option( 'posts_per_page' ) : 10,
                    'paged' => $current_page,
                    'author__in' => ( get_current_user_id() ),
                    'order' => $order,
                    'orderby' => 'date',
                    'transaction_search' => 1
                );

                if( empty( $start_date ) && empty( $end_date ) && !empty( $search_date ) ){
                    $start_date = $search_date;
                    $end_date = $search_date;
                }

                if( !empty($transaction_search) ){
                    $transaction_args['search_prod_title'] = $transaction_search;
                }
                $meta_query = array('relation' => 'AND');
                if (!empty($transaction_search) && empty($search_date) ) {

                    $meta_query['relation'] = 'OR';

                    $meta_query[] = array(
                        'key'     => '_payment_status',
                        'value'   => $transaction_search,
                        'compare' => 'LIKE'
                    );

                    $meta_query[] = array(
                        'key' => '_payment_response',
                        'value' => $transaction_search,
                        'compare' => 'LIKE'
                    );

                    $meta_query[] = array(
                        'key' => 'TransactionType',
                        'value' => $transaction_search,
                        'compare' => 'LIKE'
                    );

                    $meta_query[] = array(
                        'key' => 'GrandTotal',
                        'value' => $transaction_search,
                        'compare' => '='
                    );

                    $meta_query[] = array(
                        'key' => '_user_invoice_id',
                        'value' => $transaction_search,
                        'compare' => '='
                    );

                    $transaction_args['meta_query'] = $meta_query;
                }

                if ( !empty( $start_date ) && !empty( $end_date ) ) {
                    $transaction_args['date_query'] = array(
                        array(
                            'after' => $start_date,
                            'before' => $end_date,
                            'inclusive' => true
                        ),
                    );
                }

                add_filter( 'posts_where',array($this, 'vt_title_filter'), 10, 2 );
                $transactions = new WP_Query( $transaction_args );
                remove_filter( 'posts_where',array($this,'vt_title_filter'), 10, 2 );
				$args = array(
					'transactions' => !empty( $transactions->posts ) ? $transactions->posts : '',
					'current_page'    => absint( $current_page ),
					'max_num_pages' => !empty( $transactions->max_num_pages ) ? $transactions->max_num_pages : '',
					'has_transactions'      => 0 < $transactions->have_posts(),
					'paginate'        => true,
				);

				extract( $args );

                ?>
                <form class="transaction-filter-form" id="transaction_filter_form">
                    <div class="transaction-filter-wrap">
                        <div class="input-field-wrap form-row">
                            <input type="text" name="vt-search" id="vt_search" class="transaction-input-field" value="<?php echo isset($_GET['vt-search']) ? sanitize_text_field( $_GET['vt-search'] ) : ''; ?>" placeholder="<?php echo __('Search...'); ?>">
                        </div>
                        <div class="input-field-wrap date-field-wrap form-row">
                            <span class="date-range-label"><?php _e( 'From:' , 'usb-swiper' ); ?></span><input type="text" id="start-date" class="start-date vt-date-field transaction-input-field" name="start-date" value="<?php echo isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : ''; ?>" placeholder="<?php echo __('yyyy-mm-dd'); ?>" autocomplete="off">
                            <span class="date-range-label"><?php _e( 'To:', 'usb-swiper' ); ?></span><input type="text" id="end-date" class="vt-date-field transaction-input-field" name="end-date" value="<?php echo isset($_GET['end-date']) ? sanitize_text_field( $_GET['end-date'] ) : ''; ?>" placeholder="<?php echo __('yyyy-mm-dd'); ?>" autocomplete="off">
                        </div>
                        <div class="input-field-wrap form-row">
                            <?php echo usb_swiper_get_html_field(array(
                                'type' => 'select',
                                'id' => 'transaction_type',
                                'name' => 'transaction_type',
                                'options' => array(
                                    '' => __('All Types', 'usb-swiper'),
                                    'transaction' => __('Manual Entry (Keyed)', 'usb-swiper'),
                                    'invoice' => __('Invoice', 'usb-swiper'),
                                    'zettle' => __('Zettle', 'usb-swiper')
                                ),
                                'class' => 'transaction-select',
                                'default' => '',
                                'value' => isset($_GET['transaction_type']) ? sanitize_text_field($_GET['transaction_type']) : ''
                            )); ?>
                        </div>
                        <div class="input-field-wrap form-row">
                            <button type="submit" class="vt-button"><?php _e('FILTER','usb-swiper'); ?></button>
                        </div>
                    </div>
                </form>
                <?php
                //if( is_wc_endpoint_url('zettle-transactions') ) {
	                //usb_swiper_get_template('wc-transactions-zettle.php', $args);
                //} else {
	                usb_swiper_get_template('wc-transactions-lists.php', $args);
                //}
			}
		}


        /**
         * WP_Query title filter callback function.
         *
         * @param $where
         * @param $wp_query
         * @return mixed|string
         */
        public function vt_title_filter($where, $wp_query){

            global $wpdb;

            $user_id = get_current_user_id();

            $meta_key = '_transaction_type';
            $meta_value = !empty( $_REQUEST['transaction_type'] ) ? $_REQUEST['transaction_type'] : '';
            $transaction_type_query = '';
            // if( is_wc_endpoint_url('transactions') ){
            //     $meta_value = 'transaction';
            // }elseif ( is_wc_endpoint_url('invoices') ){
            //     $meta_value = 'invoice';
            // } elseif ( is_wc_endpoint_url('zettle-transactions') ){
	        //     $meta_value = 'zettle';
            // }

            if( !empty( $meta_value ) ){
                $transaction_type_query = $wpdb->prepare(
                    " AND {$wpdb->posts}.ID IN (
                        SELECT post_id
                        FROM {$wpdb->postmeta}
                        WHERE meta_key = %s AND meta_value = %s
                    )",
                    $meta_key,
                    $meta_value
                );

                $where .= $transaction_type_query;
            }

            $post_author = '';
	        if ( is_user_logged_in() && $user_id > 0 && !current_user_can( 'administrator' ) ) {
		        $post_author  = $wpdb->posts.'.post_author IN ('.$user_id.') AND ';
	        }

            $transaction_search = !empty( $wp_query->get('transaction_search') ) ? $wp_query->get('transaction_search') : '';
            $search_term = !empty( $wp_query->get( 'search_prod_title' ) ) ? $wp_query->get( 'search_prod_title' ) : '';
            if ( '1' == $transaction_search && !empty( $search_term ) ) {
                $where .= ' OR  ( (' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $search_term ) . '%\' AND '.$post_author.$wpdb->posts.'.post_type=\'transactions\')' . $transaction_type_query . ' )' ;
            }
            return $where;
        }


        /**
         * Transaction Detail page endpoint callback method.
         *
         * @since 1.0.0
         *
		 * @param int $transaction_id Get transaction id.
		 */
		public function view_transactions_endpoint_cb( $transaction_id ){

		    if( empty( $transaction_id ) ) {
		        return;
            }

			if( usb_swiper_allow_user_by_role('administrator')  || usb_swiper_allow_user_by_role('customer') ) {

			    $transaction = get_post($transaction_id);

			    if( !empty( $transaction->post_author ) && (int)$transaction->post_author === get_current_user_id() ) {
				    usb_swiper_get_template( 'wc-transaction-history.php', array( 'transaction_id' => $transaction_id, 'is_email_html' => true ) );
			    } else {
			        $message = __( "You can't access this transaction.",'usb-swiper');
			        echo apply_filters( 'usb_swiper_transaction_access_denied', $message);
                }
			}
		}

        /**
         * Invoice Detail page endpoint callback method
         *
         * @since 1.0.0
         *
         * @param int $transaction_id Get transaction id.
         */
        public function view_invoice_endpoint_cb( $transaction_id ) {

            if( empty( $transaction_id ) ) {
                return;
            }

            if( usb_swiper_allow_user_by_role('administrator')  || usb_swiper_allow_user_by_role('customer') ) {

                $transaction = get_post($transaction_id);

                if( !empty( $transaction->post_author ) && (int)$transaction->post_author === get_current_user_id() ) {
                    usb_swiper_get_template( 'vt-pay-by-invoice.php', array( 'invoice_id' => $transaction_id ) );
                    usb_swiper_get_template( 'wc-transaction-history.php',  );
                } else {
                    $message = __( "You can't access this transaction.",'usb-swiper');
                    echo apply_filters( 'usb_swiper_transaction_access_denied', $message);
                }
            }
        }

        /**
         * VT-Products endpoint callback method.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function vt_products_endpoint_cb() {

            if (usb_swiper_allow_user_by_role('administrator') || usb_swiper_allow_user_by_role('customer')) {

                $current_page = !empty($_GET['vt-page']) ? $_GET['vt-page'] : 1;

                $products = new WP_Query(array(
                    'post_type' => 'product',
                    'posts_per_page' => !empty(get_option('posts_per_page')) ? get_option('posts_per_page') : 10,
                    'paged' => $current_page,
                    'author' => get_current_user_id(),
                    'order' => 'DESC',
                ));

                $args = array(
                    'product' => !empty($products->posts) ? $products->posts : '',
                    'current_page' => absint($current_page),
                    'max_num_pages' => !empty($products->max_num_pages) ? $products->max_num_pages : '',
                    'has_product' => 0 < $products->have_posts(),
                    'paginate' => true,
                );

                extract($args);

                usb_swiper_get_template('vt-product-lists.php', $args);
            }
        }

        /**
         * VT-Tax-Rules endpoint callback method.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function vt_tax_rules_endpoint_cb() {

            if (usb_swiper_allow_user_by_role('administrator') || usb_swiper_allow_user_by_role('customer')) {
                usb_swiper_get_template('vt-tax-rules.php');
            }
        }

		/**
         * Create connect to PayPal button.
         *
         * @since 1.0.0
         *
		 * @param array $args get connection arguments.
		 *
		 * @return false|string $form
		 */
		public function usb_swiper_paypal_connect( $args ) {

			$settings = usb_swiper_get_settings('general');
			$vt_page_id = !empty($settings['virtual_terminal_page']) ? (int)$settings['virtual_terminal_page'] : '';

			$args = shortcode_atts( array(
                'label' => '',
                'label2' => __('Login with PayPal','usb-swiper'),
                'after_login_label' => __('Launch Virtual Terminal','usb-swiper'),
                'after_login_url' => !empty( $vt_page_id )? get_the_permalink($vt_page_id): get_the_permalink( get_option('woocommerce_myaccount_page_id') ),
            ), $args );

			ob_start();

			if( usb_swiper_allow_user_by_role('administrator')  || usb_swiper_allow_user_by_role('customer') ) {

                $get_merchant_data = usbswiper_get_onboarding_merchant_response(get_current_user_id());
                $profile_status = get_user_meta( get_current_user_id(),'vt_user_verification_status', true );
                $profile_data = get_user_meta( get_current_user_id(),'verification_form_data', true );
                $profile_status = filter_var($profile_status, FILTER_VALIDATE_BOOLEAN);
                $settings = usb_swiper_get_settings('general');
                $verification_page_id = !empty( $settings['vt_verification_page'] ) ? $settings['vt_verification_page'] : 0;

                if( empty( $profile_data ) ) { ?>
                    <div class="paypal-connect-button-wrap">
                        <p><a class="vt-button" href="<?php echo get_permalink($verification_page_id); ?>"><?php _e('Verify Profile','usb-swiper'); ?></a></p>
                    </div>
                    <?php
                } else {

                    if( $profile_status ) {

                        if( !empty( $get_merchant_data ) && is_array( $get_merchant_data )) {
                            ?>
                            <div class="vt-form-login-wrap paypal-connect-button-wrap">
                                <p><a class="vt-button" href="<?php echo !empty( $args['after_login_url'] ) ? $args['after_login_url'] : get_the_permalink($vt_page_id); ?>"><?php echo !empty( $args['after_login_label'] ) ? $args['after_login_label'] : __('Launch to Terminal','usb-swiper'); ?></a></p>
                            </div>
                            <?php
                        } else {
                            $Usb_Swiper_PPCP = new Usb_Swiper_PPCP();
                            echo "<div class='paypal-connect-button-wrap'>".$Usb_Swiper_PPCP->connect_to_paypal_button($args)."</div>";
                        }
                    }
                }

			} else {

				$Usb_Swiper_PPCP = new Usb_Swiper_PPCP();
				echo $Usb_Swiper_PPCP->connect_to_paypal_button($args);
            }

			$form = ob_get_contents();

			ob_get_clean();

			return $form;
        }

        /**
         * Get profile verification notification.
         *
         * @since 1.1.17
         *
         * @return void
         */
        public function add_notification_for_verify_profile() {

            $profile_status = get_user_meta( get_current_user_id(),'vt_user_verification_status', true );

            $profile_data = get_user_meta( get_current_user_id(),'verification_form_data', true );
            if(! empty( $profile_data ) && $profile_status === ''){
                ?>
                <div class="paypal-connect-button-wrap vt-form-notification">
                    <p class="vt-verification-message notification success"><?php _e("Thank you for providing the additional information requested.  We will review your details and let you know the status of your approval as soon as possible.","usb-swiper");?></p>
                </div>
                <?php
            } else if ( empty( $profile_data ) && $profile_status === '' ) {
                ?>
                <div class="paypal-connect-button-wrap vt-form-notification">
                    <p class="vt-verification-message notification warning"><?php _e("Thanks so much! Just one more step and you’ll be all set. Because of all the credit card fraud happening everywhere, we just need to verify the merchants who onboard with us. This saves everyone money by making sure we only allow legitimate businesses to process credit cards through our system.","usb-swiper");?></p>
                </div>
                <?php
            }
        }

		/**
         * Get hte Verification form.
         *
         * @since 1.1.17
         *
		 * @return false|string $form
		 */
        public function usb_swiper_vt_verification_form() {

	        ob_start();

            usb_swiper_get_template( 'virtual-terminal-verification-form.php' );

	        $form = ob_get_contents();

	        ob_get_clean();

	        return $form;
        }

		/**
         * USBSwiper virtual terminal form.
         *
         * @since 1.0.0
         *
		 * @param $args
		 *
		 * @return false|string $form
		 */
		public function usb_swiper_vt_form( $args ) {

			$args = shortcode_atts( array(
			        'notifications' => maybe_unserialize(get_transient('get_vt_connection_response')),
            ), $args, 'usb_swiper_vt_form' );

			ob_start();

			if( usb_swiper_allow_user_by_role('administrator')  || usb_swiper_allow_user_by_role('customer') ) {

			    $get_merchant_data = usbswiper_get_onboarding_merchant_response();
			    if( !empty( $get_merchant_data ) && is_array( $get_merchant_data )) {
				    usb_swiper_get_template( 'virtual-terminal-form.php', $args );
			    }
			}

			$form = ob_get_contents();

			ob_get_clean();

			return $form;
		}

		/**
		 * Manage form and transaction on template redirect.
         *
         * @since 1.0.0
		 */
		public function template_redirect() {

			if( is_admin() || ( !empty($_GET['et_fb']) && '1' == $_GET['et_fb'] ) ) {
		        return;
		    }

			$settings = usb_swiper_get_settings('general');
			$vt_page_id = ! empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
			$vt_verification_page = ! empty( $settings['vt_verification_page'] ) ? (int) $settings['vt_verification_page'] : '';

			$myaccount_page_id = (int)get_option( 'woocommerce_myaccount_page_id' );
			$merchant_user_info = get_user_meta( get_current_user_id(),'_merchant_onboarding_user',true);
			$profile_status = get_user_meta( get_current_user_id(), 'vt_user_verification_status', true );
			$profile_status = filter_var( $profile_status, FILTER_VALIDATE_BOOLEAN );

			if( is_user_logged_in() ) {

			    if( ( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) || ( !empty( $myaccount_page_id ) && $myaccount_page_id === get_the_ID() ) ) {
				    $Usb_Swiper_PPCP = new Usb_Swiper_PPCP();
				    $Usb_Swiper_PPCP->handle_onboarding_user();
			    }
			}

			if( isset( $_GET['_nonce'] ) && !empty( $_GET['_nonce'] ) && wp_verify_nonce( esc_attr( $_GET['_nonce'] ), 'disconnect-to-paypal' ) && isset($_GET['ppcp'] ) && !empty( $_GET['ppcp'] ) && '1' === $_GET['ppcp'] && !empty( $_GET['type'] ) && 'disconnect' === $_GET['type'] ) {
				delete_user_meta( get_current_user_id(),'_merchant_onboarding_response');
				delete_user_meta( get_current_user_id(),'_merchant_onboarding_user');
				delete_user_meta( get_current_user_id(),'_merchant_onboarding_tracking_response');
				$this->disconnect_email(get_current_user_id());
				//setcookie( 'merchant_onboarding_user', '', time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
				wp_safe_redirect( get_the_permalink( $myaccount_page_id ) );
				exit();
			}

            if( $vt_verification_page == get_the_ID() ) {
                $profile_status = get_user_meta( get_current_user_id(),'vt_user_verification_status', true );
                $profile_status = filter_var($profile_status, FILTER_VALIDATE_BOOLEAN);
                $profile_data = get_user_meta( get_current_user_id(),'verification_form_data', true );

                if( !empty( $profile_data ) || !empty( $profile_status ) ) {
                    if( !current_user_can('administrator') ) {
                        wp_safe_redirect(get_the_permalink($myaccount_page_id));
                        exit();
                    }
                } else {
                    if ( !empty( $profile_status ) && empty( $merchant_user_info ) ) {
                            wp_safe_redirect(get_the_permalink($myaccount_page_id));
                        exit();
                    } elseif( !empty( $merchant_user_info )) {
                        if (!current_user_can('manage_options')) {
                            wp_safe_redirect(get_the_permalink($vt_page_id));
                        }
                    }
                }
            }

			if( ! empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) {

				if( isset($_REQUEST['merchantId']) && !empty( esc_attr( $_REQUEST['merchantId'] ) ) ) {

					$Usb_Swiper_PPCP = new Usb_Swiper_PPCP();
					$Usb_Swiper_PPCP->create_user();
				}

				if ( ! empty( $merchant_user_info ) && false === $profile_status ) {
					wp_safe_redirect( get_the_permalink( $vt_verification_page ) );
					exit();
				}

				if( isset( $_GET['_nonce'] ) && !empty( $_GET['_nonce'] ) && wp_verify_nonce( esc_attr( $_GET['_nonce'] ), 'login-with-paypal' ) && isset($_GET['ppcp'] ) && !empty( $_GET['ppcp'] ) && '1' === $_GET['ppcp'] ) {
					$merchant_user_info = usbswiper_get_onboarding_user();

					if( !empty( $merchant_user_info ) && is_array( $merchant_user_info ) && !empty( $merchant_user_info['merchant_email'] ) && is_email( $merchant_user_info['merchant_email'] )) {
						$user_info = get_user_by('email', $merchant_user_info['merchant_email']);
						if ( !empty( $user_info ) &&  isset( $user_info->ID ) && $user_info->ID > 0) {

							wc_set_customer_auth_cookie( $user_info->ID );
							wp_safe_redirect( get_the_permalink( $myaccount_page_id ) );
							exit();
						}
					}
				}
			}

			if( !empty( $_GET['action'] ) && 'capture' === $_GET['action'] && !empty( $_GET['unique_id'] ) ) {

			    $this->capture_authorize_transaction($_GET['unique_id']);
			}

			if( !empty( $_GET['action'] ) && 'void' === $_GET['action'] && !empty( $_GET['unique_id'] ) ) {

				$this->void_authorize_transaction($_GET['unique_id']);
			}

			if( !is_user_logged_in()) {

				if( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) {
					wp_safe_redirect( site_url().'/wp-login.php' );
					exit();
				}
			} elseif ( is_user_logged_in() ) {

				if( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) {
					$merchant_user_info = usbswiper_get_onboarding_user();
					if ( empty( $merchant_user_info ) ) {
						wp_safe_redirect( get_the_permalink( $myaccount_page_id ) );
						exit();
					}
				}
			}
            
            $this->manage_zettle_settings();
		}

		/**
         * Trigger the paypal disconnect email.
         *
         * @since 1.0.0
         *
         * @param int $user_id get the user id
		 * @return void
		 */
        public function disconnect_email($user_id) {
	        $mailer = WC()->mailer()->get_emails();
	        $mailer['UsbSwiperPaypalDisconnectedEmail']->template_html_path = USBSWIPER_PATH . 'templates/emails/paypaldisconnected.php';
	        $mailer['UsbSwiperPaypalDisconnectedEmail']->trigger( $user_id );
        }

		/**
		 * Add new PayPal disconnect button in transaction lists.
		 *
         * @since 1.0.0
		 */
		public function paypal_disconnect_button() {

			$merchant_user_info = usbswiper_get_onboarding_user();

			if( isset( $merchant_user_info ) && !empty( $merchant_user_info ) && is_array( $merchant_user_info ) ){

			    $current_user = wp_get_current_user();

			    if( isset( $merchant_user_info['user_email'] ) && !empty( $merchant_user_info['user_email'] ) && isset( $current_user->user_email ) && $current_user->user_email == $merchant_user_info['user_email'] ) {

                    $settings = usb_swiper_get_settings('general');
                    $vt_page_id = !empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
                    $paypal_login_url = !empty( $vt_page_id ) ? add_query_arg( array( '_nonce' => wp_create_nonce('disconnect-to-paypal'), 'ppcp' => true, 'type'=>'disconnect', ), get_the_permalink($vt_page_id) ): '#';
                    ?>
                    <div class="vt-form-disconnect-wrap">
                        <p style="margin-bottom:10px;"><a class="vt-button" href="<?php echo !empty( $paypal_login_url ) ? esc_url($paypal_login_url) : ''; ?>"><?php _e('Disconnect From PayPal','usb-swiper'); ?></a></p>
                    </div>
                    <?php
			    }
			}
		}

		/**
		 * Handle transaction action.
         *
         * @since 1.0.0
		 */
		public function handle_usb_swiper_transaction() {

            if( isset( $_GET['usb_swiper_ppcp_action'] ) && !empty( $_GET['usb_swiper_ppcp_action']) ) {

                switch ( $_GET['usb_swiper_ppcp_action'] ) {

                    case "create_transaction":
                        $transaction_id = !empty( $_POST['transaction_id'] ) ? $_POST['transaction_id'] : 0;

                        if( !empty( $_POST['transaction_type'] ) && 'retry' === $_POST['transaction_type'] ) {
                            $this->create_new_transaction($transaction_id);
                        } else {
                            if( !empty( $transaction_id ) && (int)$transaction_id > 0 ) {
                                $this->pay_by_invoice_transaction($transaction_id);
                            } else {
                                $this->create_new_transaction();
                            }
                        }

                        break;
                    case "cc_capture":
                        $this->capture_transaction();
                        break;
                    case "create_zettle_request":
	                    $this->create_zettle_request();
                        break;
	                case "zettle_payment_response":

                        if( !empty( $_POST['action'] ) && 'zettle_payment_failed_response' === $_POST['action'] ) {

                            $transaction_id = !empty( $_POST['transaction_id'] ) ? $_POST['transaction_id'] : 0;
                            if( !empty( $transaction_id ) ) {
	                            update_post_meta($transaction_id, '_payment_status', 'FAILED');
	                            update_post_meta($transaction_id, '_payment_response', [] );
                            }

	                        wp_send_json( [
		                        'status' => false,
		                        'message' => __( 'Zettle Payment Failed. Please try again.', 'usb-swiper' ),
		                        'message_type' => "FAILED",
	                        ], 200 );

                        } elseif ( !empty( $_POST['action'] ) && 'zettle_refund_payment_response' === $_POST['action'] ) {
	                        $this->handle_zettle_refund_payment_response();
                        } else {
	                        $this->handle_zettle_payment_response();
                        }
		                break;
                    default:
                }
            }
		}
        
        public function create_zettle_request() {
         
	        $current_user_id = get_current_user_id();
	        
	        if( empty($current_user_id) ){
		        wp_send_json( array(
			        'status' => 'error',
			        'message' => __("You are not able to create a transaction without a login.",'usb-swiper'),
			        'message_type' => __("ERROR",'usb-swiper'),
		        ), 200 );
	        }
	        
	        $zettle_payment = isset( $_POST['PayWithZettleDisabled'] ) && (bool)$_POST['PayWithZettleDisabled'] === true;
            
            if( !$zettle_payment )  {
                return false;
            }
	        
	        $tab_fields = usb_swiper_get_fields_for_transaction();
	        $transaction = array();
	        if( !empty( $tab_fields ) && is_array( $tab_fields ) ) {
		        foreach ( $tab_fields as $tab_id => $tab_field ) {
			        $form_fields = usb_swiper_get_vt_form_fields( $tab_id );
			        if( !empty( $form_fields ) && is_array( $form_fields ) ) {
				        foreach ( $form_fields as $key => $form_field ) {
					        $field_id = !empty( $form_field['id'] ) ?  $form_field['id'] : '';
					        if( !empty( $_POST[$field_id] ) && is_array( $_POST[$field_id] ) ) {
						        $_POST[$field_id] = array_filter($_POST[$field_id], function($value) { return !is_null($value) && $value !== ''; });
					        }
					        $transaction[$field_id] = !empty( $_POST[$field_id] ) ? $_POST[$field_id] : '';
				        }
			        }
		        }
	        }
	        
	        $BillingFirstName = !empty( $transaction['BillingFirstName'] ) ? $transaction['BillingFirstName'] : '';
	        $BillingLastName = !empty( $transaction['BillingLastName'] ) ? $transaction['BillingLastName'] : '';
	        
	        $display_name = $BillingFirstName. ' ' . $BillingLastName;
	        
	        $transaction_type = 'ZETTLE';

	        $post_args = array(
		        'post_title'   => wp_strip_all_tags($display_name),
		        'post_content' => !empty( $transaction['Notes'] ) ? esc_attr($transaction['Notes']) : '',
		        'post_status'  => 'publish',
		        'post_author'  => $current_user_id,
		        'post_type'   => 'transactions',
		        'post_date' => usbswiper_get_user_date_i18n( $current_user_id ),
		        'post_date_gmt' => usbswiper_get_user_date_i18n( $current_user_id ),
	        );
	        
	        $transaction_id = wp_insert_post($post_args);
	        
	        if( !is_wp_error( $transaction_id ) ) {
		        
		        $user_invoice_id = count_user_invoice_numbers();
		        update_post_meta($transaction_id, '_transaction_type', $transaction_type);
		        update_post_meta($transaction_id, '_transaction_user_id', get_current_user_id());
		        update_post_meta($transaction_id, '_user_invoice_id', sprintf("%04d", $user_invoice_id));
		        update_post_meta($transaction_id, '_payment_status', 'CREATED');
		        update_post_meta($transaction_id, 'payment_source', 'Zettle');
		        update_post_meta( $transaction_id,'_environment', 'live');

		        wp_update_post(array(
			        'ID' => $transaction_id,
			        'post_title' => wp_strip_all_tags(sprintf(__('#%s %s', 'usb-swiper'), $transaction_id, $display_name)),
		        ));
		        
		        usb_swiper_set_session('usb_swiper_woo_transaction_id', $transaction_id);
          
		        if (!empty($transaction) && is_array($transaction)) {
			        foreach ($transaction as $key => $value) {
				        update_post_meta($transaction_id, $key, $value);
			        }
		        }
		        
		        $vt_product = get_post_meta($transaction_id, 'VTProduct', true);
		        $vt_product_quantity = get_post_meta($transaction_id, 'VTProductQuantity', true);
		        $vt_product_price = get_post_meta($transaction_id, 'VTProductPrice', true);
		        $vt_product_ids = get_post_meta($transaction_id, 'VTProductID', true);
		        $vt_products = array();
		        
		        if (!empty($vt_product) && is_array($vt_product)) {
			        
			        for ($i = 0; $i < count($vt_product); $i++) {
				        
				        $product = !empty($vt_product[$i]) ? $vt_product[$i] : '';
				        $quantity = !empty($vt_product_quantity[$i]) ? ltrim($vt_product_quantity[$i], '0') : 1;
				        $price = !empty($vt_product_price[$i]) ? $vt_product_price[$i] : '';
				        $product_id = !empty($vt_product_ids[$i]) ? $vt_product_ids[$i] : '';
				        
				        $vt_products[] = array(
					        'product_name' => $product,
					        'product_quantity' => $quantity,
					        'product_price' => $price,
					        'product_id' => $product_id
				        );
			        }
		        }
		        
		        update_post_meta($transaction_id, 'vt_products', $vt_products);

		        if( !class_exists('Usb_Swiper_Customers') ) {
			        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-customers.php';
		        }

                $usb_swiper_customers = new Usb_Swiper_Customers();
		        $usb_swiper_customers->handle_customer($_POST);

		        if( !class_exists('UsbSwiperZettle') ) {
			        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-zettle.php';
		        }
          
		        $reader_data = UsbSwiperZettle::get_zettle_reader_data( $current_user_id );
		        $id = !empty( $reader_data['id'] ) ? $reader_data['id'] : '';
		        $response = UsbSwiperZettle::websocket_connection($id);
		        $response_status = !empty( $response['status'] ) ? $response['status'] :  '';
		        $message = !empty( $response['message'] ) ? $response['message']: __('Something went wrong. Please try again', 'usb-swiper');

		        $tipping = UsbSwiperZettle::get_settings('enable_zettle_tipping', 'public' );
		        if( !empty( $response_status ) && ( 200 === (int) $response_status || 201 === (int) $response_status ) ) {
			        $websocket_url  = !empty( $response['data']['location'] ) ? $response['data']['location'] :  '';
			        $grand_total = get_post_meta( $transaction_id, 'GrandTotal', true );
			        $payment_request = UsbSwiperZettle::payment_request( $websocket_url, [
                        'transaction_id' => $transaction_id,
                        'reader_data' => $reader_data,
                        'amount' => $grand_total,
                        'tipping' => !empty( $tipping ),
                    ]);
           
			        wp_send_json( [
				        'status' => true,
				        'message' => $message,
				        'data' => $payment_request,
				        'transaction_id' => $transaction_id,
				        'websocket_message' => __('Zettle device is not ready.', 'usb-swiper'),
			        ], 200 );
           
		        } else {
			        wp_delete_post($transaction_id);
			        wp_send_json( [
				        'status' => false,
				        'message' => $message,
			        ], 200 );
		        }
	        }
        }

        public function manage_refund_payment_request() {

	        $transaction_id = !empty( $_POST['transaction_id'] ) ? $_POST['transaction_id'] : 0;
	        $refund_amount = !empty( $_POST['refund_amount'] ) ? $_POST['refund_amount'] : 0;

	        $current_user_id = get_current_user_id();

	        if( !class_exists('UsbSwiperZettle') ) {
		        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-zettle.php';
	        }

	        $reader_data = UsbSwiperZettle::get_zettle_reader_data( $current_user_id );
	        $id = !empty( $reader_data['id'] ) ? $reader_data['id'] : '';
	        $response = UsbSwiperZettle::websocket_connection($id);
	        $response_status = !empty( $response['status'] ) ? $response['status'] :  '';
	        $message = !empty( $response['message'] ) ? $response['message']: __('Something went wrong. Please try again', 'usb-swiper');

	        if( !empty( $response_status ) && ( 200 === (int) $response_status || 201 === (int) $response_status ) ) {
		        $websocket_url = !empty($response['data']['location']) ? $response['data']['location'] : '';
		        $payment_request = UsbSwiperZettle::refund_payment_request( $websocket_url, [
			        'transaction_id' => $transaction_id,
			        'reader_data' => $reader_data,
			        'amount' => $refund_amount,
		        ]);

		        wp_send_json( [
			        'status' => true,
			        'message' => $message,
			        'data' => $payment_request,
			        'transaction_id' => $transaction_id,
			        'websocket_message' => __('Zettle device is not ready.', 'usb-swiper'),
		        ], 200 );

	        } else {
		        wp_send_json( [
			        'status' => false,
			        'message' => $message,
		        ], 200 );
	        }
        }

        public function handle_zettle_payment_response() {

            $status = false;
            $message = __( 'Something went wrong. Please try again.','usb-swiper');
	        $message_type = 'error';
	        $redirect_url = '';

            $transaction_id = !empty( $_POST['message_id'] ) ? UsbSwiperZettle::get_transaction_id_from_message_id( $_POST['message_id'] ) : '';
            $response = !empty( $_POST['response'] ) ? (array) json_decode( stripslashes( $_POST['response'] ) ) : [];

	        UsbSwiperZettle::add_log( $response, '', '','websocket_payment_response', $transaction_id );

            if( !empty( $_POST['action'] ) && $_POST['action'] === 'zettle_payment_response' && !empty( $transaction_id ) && $transaction_id > 0 ) {
                $result_status = !empty( $response['resultStatus'] ) ? $response['resultStatus'] : 'CREATED';

	            update_post_meta( $transaction_id, '_payment_response', $response);
	            update_post_meta($transaction_id, '_payment_status', strtoupper( $result_status ) );

                if( !empty( $result_status ) && strtolower( $result_status ) === 'completed'  ) {

	                $status = true;

	                $BillingFirstName = get_post_meta( $transaction_id,'BillingFirstName', true);

	                $email_args = array(
		                'display_name' => wp_strip_all_tags($BillingFirstName)
	                );

	                $BillingEmail = get_post_meta( $transaction_id,'BillingEmail', true);

	                $current_user_id = get_current_user_id();
	                $current_user = get_user_by('id', $current_user_id );
	                $ignore_email = get_user_meta( $current_user_id,'ignore_transaction_email', true );

	                $customer_email = WC()->mailer()->emails['transaction_email'];
	                $customer_email->recipient = $BillingEmail;
	                $customer_email->trigger( array(
		                'transaction_id' => $transaction_id,
		                'email_args' => $email_args,
	                ));

	                $admin_email = WC()->mailer()->emails['transaction_email_admin'];
                    $get_recipient = [];
                    if (isset($admin_email->enabled) && $admin_email->enabled === 'yes') {
                        $get_recipient[] = $admin_email->recipient;
                    }
                    if (false === (bool)$ignore_email) {
                        $get_recipient[] = $current_user->user_email;
                    }

	                $admin_email->recipient = implode(', ',$get_recipient);
	                $admin_email->trigger( array(
		                'transaction_id' => $transaction_id,
		                'email_args' => $email_args,
	                ));

	                $redirect_url = esc_url(wc_get_endpoint_url('view-transaction', $transaction_id, wc_get_page_permalink('myaccount')));
                }  else {
	                $message = __( 'Zettle transaction failed. Please try again.','usb-swiper');
                }
            }

	        wp_send_json( [
		        'status' => $status,
		        'message' => $message,
		        'message_type' => $message_type,
		        'redirect_url' => $redirect_url,
	        ], 200 );
        }

        public function handle_zettle_refund_payment_response() {

	        $status = false;
	        $message = __( 'Something went wrong. Please try again.','usb-swiper');
	        $message_type = 'error';
	        $redirect_url = '';
	        $refund_response = [];

	        $transaction_id = !empty( $_POST['message_id'] ) ? UsbSwiperZettle::get_transaction_id_from_message_id( $_POST['message_id'] ) : '';
	        $response = !empty( $_POST['response'] ) ? (array) json_decode( stripslashes( $_POST['response'] ) ) : [];

	        UsbSwiperZettle::add_log( $response, '', '','websocket_payment_response', $transaction_id );

	        if( !empty( $_POST['action'] ) && $_POST['action'] === 'zettle_refund_payment_response' && !empty( $transaction_id ) && $transaction_id > 0 ) {
		        $result_status = !empty( $response['resultStatus'] ) ? $response['resultStatus'] : '';

		        $payment_refund_response = get_post_meta( $transaction_id, '_payment_refund_response',  true);

		        $response['create_time'] = current_time('mysql');

		        $refund_response[] = $response;
		        if( !empty( $payment_refund_response ) && is_array( $payment_refund_response ) ) {
			        $refund_response = array_merge( $refund_response, $payment_refund_response);
		        }

		        update_post_meta( $transaction_id, '_payment_refund_response', $refund_response );

		        if( !empty( $result_status ) && strtolower( $result_status ) === 'completed'  ) {

			        $status = true;
                    $refund_amount = usbswiper_get_zettle_transaction_refund_total( $transaction_id );
                    $original_amount = usbswiper_get_zettle_transaction_total($transaction_id);

                    $payment_status = 'partially_refunded';
                    if( $refund_amount >= $original_amount ) {
	                    $payment_status = 'refunded';
                    }

			        update_post_meta($transaction_id, '_payment_status', strtoupper( $payment_status ) );

			        $billing_email = get_post_meta( $transaction_id,'BillingEmail', true);
			        $billing_first_name = get_post_meta( $transaction_id,'BillingFirstName', true);
			        $attachment = apply_filters( 'usb_swiper_email_attachment', '', $transaction_id );

			        $customer_email = WC()->mailer()->emails['payment_email_refund'];
			        $customer_email->recipient = $billing_email;
			        $customer_email->heading = __( '{#transaction_type#} Transaction {#transaction_id#} refunded', 'usb-swiper' );
			        $customer_email->subject = __( 'Your {#transaction_type#} Transaction {#transaction_id#} has been refunded', 'usb-swiper');
			        $customer_email->trigger([
				        'transaction_id' => $transaction_id,
				        'email_args' => [
					        'invoice' => false,
					        'display_name' => wp_strip_all_tags($billing_first_name)
				        ],
				        'attachment' => [ $attachment ],
                    ]);

			        $get_recipient = '';
			        $author_id = get_post_field( 'post_author', $transaction_id );
			        $author_id = ! empty( $author_id ) ? $author_id : 1;
			        $current_user = get_user_by('id', $author_id );
			        $ignore_email = get_user_meta( $author_id,'ignore_transaction_email', true );
			        if( true !== (bool)$ignore_email ){
				        $get_recipient = $current_user->user_email;
			        }

			        $admin_email = WC()->mailer()->emails['payment_email_refund_admin'];
			        $admin_email->recipient = $get_recipient;
			        $admin_email->heading = __( '{#transaction_type#} Transaction {#transaction_id#} refunded', 'usb-swiper' );
			        $admin_email->subject = __( '{#transaction_type#} Transaction {#transaction_id#} has been refunded', 'usb-swiper');

			        $admin_email->trigger( [
				        'transaction_id' => $transaction_id,
				        'email_args' => [
					        'invoice' => false,
					        'display_name' => wp_strip_all_tags($billing_first_name)
                        ],
                    ]);

			        $redirect_url = esc_url(wc_get_endpoint_url('view-transaction', $transaction_id, wc_get_page_permalink('myaccount')));
		        }  else {
			        $message = __( 'Zettle refund request failed. Please try again.', 'usb-swiper' );
		        }
	        }

	        wp_send_json( [
		        'status' => $status,
		        'message' => $message,
		        'message_type' => $message_type,
		        'redirect_url' => $redirect_url,
	        ], 200 );
        }

		/**
		 * Create new transaction.
         *
         * @since 1.0.0
		 */
		public function create_new_transaction( $transaction_id = 0) {

            $current_user_id = get_current_user_id();

            if( empty($current_user_id) ){
                wp_send_json( array(
                    'status' => 'error',
                    'message' => __("You are not able to create a transaction without a login.",'usb-swiper'),
                    'message_type' => __("ERROR",'usb-swiper'),
                ), 200 );
            }

            $get_merchant_data = usbswiper_get_onboarding_merchant_response($current_user_id);
            $merchant_id = !empty( $get_merchant_data['merchant_id'] ) ? $get_merchant_data['merchant_id'] : '';
            if( empty($merchant_id) ){
                wp_send_json( array(
                    'status' => 'error',
                    'message' => __("Merchant Id is invalid, please reconnect the paypal.", "usb-swiper"),
                    'message_type' => __("ERROR",'usb-swiper'),
                ), 200 );
            }
			$tab_fields = usb_swiper_get_fields_for_transaction();
            $invoice_payment = isset( $_POST['PayByInvoiceDisabled'] ) && (bool)$_POST['PayByInvoiceDisabled'] === true;
            $transaction = array();
			if( !empty( $tab_fields ) && is_array( $tab_fields ) ) {
			    foreach ( $tab_fields as $tab_id => $tab_field ) {
				    $form_fields = usb_swiper_get_vt_form_fields( $tab_id );
				    if( !empty( $form_fields ) && is_array( $form_fields ) ) {
				        foreach ( $form_fields as $key => $form_field ) {
					        $field_id = !empty( $form_field['id'] ) ?  $form_field['id'] : '';
                            if( !empty( $_POST[$field_id] ) && is_array( $_POST[$field_id] ) ) {
                                $_POST[$field_id] = array_filter($_POST[$field_id], function($value) { return !is_null($value) && $value !== ''; });
                            }
					        $transaction[$field_id] = !empty( $_POST[$field_id] ) ? $_POST[$field_id] : '';
				        }
				    }
			    }
			}

			$BillingFirstName = !empty( $transaction['BillingFirstName'] ) ? $transaction['BillingFirstName'] : '';
			$BillingLastName = !empty( $transaction['BillingLastName'] ) ? $transaction['BillingLastName'] : '';

			$display_name = $BillingFirstName. ' ' . $BillingLastName;

            $transaction_type = 'TRANSACTION';
            $invoice_status = '';
            if( $invoice_payment ) {
                $transaction_type = 'INVOICE';
                $invoice_status = 'PENDING';
            }

            if( !$transaction_id ) {
                $post_args = array(
                    'post_title'   => wp_strip_all_tags($display_name),
                    'post_content' => !empty( $transaction['Notes'] ) ? esc_attr($transaction['Notes']) : '',
                    'post_status'  => 'publish',
                    'post_author'  => $current_user_id,
                    'post_type'   => 'transactions',
                    'post_date' => usbswiper_get_user_date_i18n( $current_user_id ),
                    'post_date_gmt' => usbswiper_get_user_date_i18n( $current_user_id ),
                );

                $transaction_id = wp_insert_post($post_args);
            }

			if( !is_wp_error( $transaction_id ) ) {

                $user_invoice_id = count_user_invoice_numbers();
                update_post_meta($transaction_id, '_transaction_type', $transaction_type);
                update_post_meta($transaction_id, '_transaction_user_id', get_current_user_id());
                update_post_meta($transaction_id, '_user_invoice_id', sprintf("%04d", $user_invoice_id ) );

                if( !empty( $invoice_status ) ){
                    update_post_meta($transaction_id, '_payment_status', $invoice_status );
                }

                wp_update_post( array(
					'ID'         => $transaction_id,
					'post_title' => wp_strip_all_tags(sprintf( __( '#%s %s' ,'usb-swiper' ), $transaction_id ,$display_name)),
				) );

				usb_swiper_set_session('usb_swiper_woo_transaction_id', $transaction_id);

			    //update_post_meta($transaction_id,'vt_transaction_currency', get_woocommerce_currency());

			    if( !empty( $transaction ) && is_array( $transaction ) ) {
			        foreach ( $transaction as $key => $value ) {
			            update_post_meta( $transaction_id,$key, $value);
			        }
			    }

                $vt_product = get_post_meta( $transaction_id,'VTProduct', true);
                $vt_product_quantity = get_post_meta( $transaction_id,'VTProductQuantity', true);
                $vt_product_price = get_post_meta( $transaction_id,'VTProductPrice', true);
				$vt_product_ids = get_post_meta( $transaction_id,'VTProductID', true);
                $vt_products = array();

                if( !empty( $vt_product ) && is_array( $vt_product ) ) {

                    for ($i = 0; $i < count($vt_product); $i++) {

                        $product = !empty($vt_product[$i]) ? $vt_product[$i] : '';
                        $quantity = !empty($vt_product_quantity[$i]) ? ltrim($vt_product_quantity[$i], '0') : 1;
                        $price = !empty($vt_product_price[$i]) ? $vt_product_price[$i] : '';
						$product_id = !empty($vt_product_ids[$i]) ? $vt_product_ids[$i] : '';

                        $vt_products[] = array(
                            'product_name' => $product,
                            'product_quantity' => $quantity,
                            'product_price' => $price,
							'product_id' => $product_id
                        );
                    }
                }

                update_post_meta( $transaction_id, 'vt_products', $vt_products );

				if( !class_exists('Usb_Swiper_Customers') ) {
					include_once USBSWIPER_PATH.'/includes/class-usb-swiper-customers.php';
				}

				$usb_swiper_customers = new Usb_Swiper_Customers();
				$usb_swiper_customers->handle_customer($_POST);

			    if( !class_exists('Usb_Swiper_Paypal_request') ) {
				    include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
			    }

                if( ! $invoice_payment ) {
                    $Paypal_request = Usb_Swiper_Paypal_request::instance();
                    $response = $Paypal_request->create_transaction_request($transaction_id);

                    if( !empty( $response['id'] )) {

                        if( !empty( $response['links'] ) && is_array( $response['links'] ) ) {
                            foreach ( $response['links'] as $key => $links ) {
                                if( !empty( $links['rel'] ) && 'self' === $links['rel'] && !empty( $links['href'] ) ) {
                                    $order_response = $Paypal_request->request($links['href'], array(
                                        'method' => 'GET',
                                        'timeout' => 60,
                                        'redirection' => 5,
                                        'httpversion' => '1.1',
                                        'blocking' => true,
                                        'headers' => array(
                                            'Content-Type' => 'application/json',
                                            'Authorization' => 'Bearer ' . $Paypal_request->get_access_token(),
                                        ),
                                    ), 'order_response', $transaction_id);
                                    $Paypal_request->handle_paypal_debug_id($order_response, $transaction_id);
                                    if( !empty( $order_response ) ) {
                                        update_post_meta($transaction_id, '_payment_response', $order_response);
                                        update_post_meta($transaction_id, '_payment_status', usbswiper_get_transaction_status($transaction_id) );
                                    }
                                }
                            }
                        }

                        update_post_meta($transaction_id, '_paypal_transaction_id', $response['id']);
                        usb_swiper_set_session('usb_swiper_woo_create_transaction_id', $response['id']);

                        $settings = get_option( 'usb_swiper_settings' );

                        $response = array(
                            'orderID' => $response['id'],
                            'transaction_id' => $transaction_id,
                        );

                        wp_send_json( $response, 200 );

                    } else {
                        //wp_delete_post($transaction_id);
                        $message_name = !empty( $response['name'] ) ? $response['name'] :'';
                        $message = !empty( $response['message'] ) ? $response['message'] :'';
                        $details = !empty( $response['details'][0] ) ? $response['details'][0] :'';

                        wp_send_json( array(
                            'status' => 'error',
                            'message' => !empty( $details['description'] ) ? $details['description'] : $message,
                            'message_type' => !empty( $details['issue'] ) ? $details['issue'] : $message_name,
                        ), 200 );
                    }

                } elseif( !empty( $invoice_payment ) && $transaction_type === 'INVOICE' ) {

                    $email_args = array(
                        'invoice' => true,
                        'display_name' => wp_strip_all_tags($BillingFirstName)
                    );

                    $BillingEmail = get_post_meta( $transaction_id,'BillingEmail', true);
                    $attachment = apply_filters('usb_swiper_email_attachment', '', $transaction_id);

                    $customer_email = WC()->mailer()->emails['invoice_email_pending'];
                    $customer_email->recipient = $BillingEmail;
                    $customer_email->trigger( array(
                        'transaction_id' => $transaction_id,
                        'email_args' => $email_args,
                        'attachment' => array( $attachment ),
                    ));

                    $current_user = get_user_by('id', get_current_user_id() );
                    $ignore_email = get_user_meta( get_current_user_id(),'ignore_transaction_email', true );

                    $admin_email = WC()->mailer()->emails['invoice_email_pending_admin'];
                    $get_recipient = '';

                    if( true !== (bool)$ignore_email ){
                        $get_recipient = $current_user->user_email;
                    }

                    $admin_email->recipient = $get_recipient;

                    $admin_email->trigger( array(
                        'transaction_id' => $transaction_id,
                        'email_args' => $email_args,
                    ));

                    wp_send_json( array('invoiceUrl' => wc_get_account_endpoint_url( 'view-transaction' ) . $transaction_id), 200 );
                }
			}
		}

		/**
		 * handle capture or authorize transaction request.
         *
         * @since 1.0.0
		 */
		public function capture_transaction() {

		    if( !empty( $_GET['wc-process-transaction-nonce'] ) && wp_verify_nonce($_GET['wc-process-transaction-nonce'],'usb_swiper_process_transaction')) {

			    if( !class_exists('Usb_Swiper_Paypal_request') ) {
				    include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
			    }

				$paypal_transaction_id = !empty( $_GET['paypal_transaction_id'] ) ? $_GET['paypal_transaction_id'] : '';

				$transaction_id = usb_swiper_get_session('usb_swiper_woo_transaction_id');

				if( !empty( $_REQUEST['transaction_id'] ) && $_REQUEST['transaction_id'] > 0 ) {
					usb_swiper_set_session('usb_swiper_woo_transaction_id', 0);
					usb_swiper_set_session('usb_swiper_woo_transaction_id', $_REQUEST['transaction_id']);
				}

				if( empty( $_REQUEST['transaction_id'] )  && !empty( $_REQUEST['pbi_transaction_id'] ) ) {
					usb_swiper_set_session('usb_swiper_woo_transaction_id', 0);
					$transaction_type = get_post_meta( $_REQUEST['pbi_transaction_id'], '_transaction_type', true );
					usb_swiper_set_session('usb_swiper_woo_transaction_id',  $_REQUEST['pbi_transaction_id']);
				} else {
					$transaction_type = get_post_meta( $_REQUEST['transaction_id'], '_transaction_type', true );
				}

				$transaction_id = usb_swiper_get_session('usb_swiper_woo_transaction_id');

				$redirect_url =  esc_url( wc_get_endpoint_url( 'view-transaction', $transaction_id, wc_get_page_permalink( 'myaccount' ) ) );
				
			    $Paypal_request = Usb_Swiper_Paypal_request::instance();
			    $response = $Paypal_request->handle_cc_transaction_request($paypal_transaction_id);

                $payment_source = !empty( $_GET['payment_source'] ) ? sanitize_text_field($_GET['payment_source']) : '';

                update_post_meta( $transaction_id, 'payment_source', $payment_source);

                if( !empty( $response['links'] ) && is_array( $response['links'] ) && count( $response['links'] ) === 1 ) {
                    foreach ($response['links'] as $key => $links) {
                        if (!empty($links['rel']) && 'self' === $links['rel'] && !empty($links['href'])) {
                            $order_response = $Paypal_request->request($links['href'], array(
                                'method' => 'GET',
                                'timeout' => 60,
                                'redirection' => 5,
                                'httpversion' => '1.1',
                                'blocking' => true,
                                'headers' => array(
                                    'Content-Type' => 'application/json',
                                    'Authorization' => 'Bearer ' . $Paypal_request->get_access_token(),
                                ),
                            ), 'order_response', $transaction_id);
                            $Paypal_request->handle_paypal_debug_id($order_response, $transaction_id);
                            if( !empty( $order_response ) ) {
                                update_post_meta($transaction_id, '_payment_response', $order_response);
                            }
                        }
                    }
                }else{
                    if( !empty( $response ) ){
                        update_post_meta($transaction_id, '_payment_response', $response);
                    }
                }

			    $payment_status = !empty( $response['status'] ) ? $response['status'] : '';
                update_post_meta($transaction_id, '_payment_status', usbswiper_get_transaction_status($transaction_id) );

                if( !empty( $transaction_type ) && strtolower($transaction_type) === 'invoice' ){
                    $settings = usb_swiper_get_settings('general');
                    $paybyinvoice_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';
                    $redirect_url = add_query_arg( array('invoice-session'=> base64_encode(json_encode(array('id' => "invoice_$transaction_id", 'status' => $payment_status)))), get_the_permalink( $paybyinvoice_id ) );
                    $temp_payment_status = ( !empty( $payment_status ) && strtolower( $payment_status ) === 'completed' ) ? usbswiper_get_transaction_status($transaction_id) : 'PENDING';
                    update_post_meta($transaction_id, '_payment_status', $temp_payment_status);
                }

			    if( !empty( $response['payment_source'] )) {

			        $card_details = !empty( $response['payment_source']['card'] ) ? $response['payment_source']['card'] : '';

			        $card_number = !empty( $card_details['last_digits']) ? $card_details['last_digits'] :'';
			        $brand = !empty( $card_details['brand']) ? $card_details['brand'] :'';
			        $type = !empty( $card_details['type']) ? $card_details['type'] :'';

				    update_post_meta($transaction_id, '_payment_card_last_digits', $card_number);
				    update_post_meta($transaction_id, '_payment_card_brand', $brand);
				    update_post_meta($transaction_id, '_payment_card_type', $type);
			    }

                $order_status = '';

                if( !empty($response['response_code']) && !is_success_status_code($response['response_code']) ){
                    update_post_meta($transaction_id, '_payment_status', 'FAILED');
                    $order_status_response = (object)[
                            'message' => !empty($response['details'][0]['description']) ? $response['details'][0]['description'] : ''
                    ];
                }

                if(!empty($response) && !empty($response['purchase_units'][0]['payments'])){

                    $order_payments = $response['purchase_units'][0]['payments'];
                    if( !empty( $order_payments ) ){
                        foreach($order_payments as $order_payment){
                            $order_status = !empty( $order_payment[0]['status'] ) ? $order_payment[0]['status'] : '';

                            if( !empty($order_status) || strtolower($order_status) === 'declined' ) {
                                $order_status_response = [
                                    'response_code' => $order_payments['captures'][0]['processor_response']['response_code'],
                                    'response_description' => $order_payments['captures'][0]['processor_response']['response_description'],
                                ];

                                if (is_array($order_status_response)) {
                                    $order_status_response = !empty($order_status_response['body']) ? json_decode($order_status_response['body']) : new stdClass();

                                    $response_code = !empty($order_payments['captures'][0]['processor_response']['response_code']) ? $order_payments['captures'][0]['processor_response']['response_code'] : '';
                                    $response_description = !empty($order_payments['captures'][0]['processor_response']['response_description']) ? $order_payments['captures'][0]['processor_response']['response_description'] : '';

                                    $order_status_response->message = !empty($order_status_response->message)
                                        ? $order_status_response->message
                                        : sprintf(__('%s (Response Code: <strong>%s</strong>)', 'usb-swiper'), $response_description, $response_code);

                                    $response['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code'] = $order_status_response->message;
                                }
                            }

                        }
                    }
                }

			    if( !empty($response ) && is_array($response) && isset($response['id']) && !empty($response['id']) ) {

				    update_post_meta($transaction_id, '_paypal_transaction_id', $response['id']);
                    $BillingFirstName = get_post_meta( $transaction_id,'BillingFirstName', true);
                    $email_args = array(
                        'display_name' => wp_strip_all_tags($BillingFirstName)
                    );

                    $BillingEmail = get_post_meta( $transaction_id,'BillingEmail', true);
                    $attachment = apply_filters('usb_swiper_email_attachment', '', $transaction_id);
                    $transaction_type = get_post_meta($transaction_id, '_transaction_type', true);

                    $current_user_id = get_current_user_id();
                    $current_user = get_user_by('id', $current_user_id );
                    $ignore_email = get_user_meta( $current_user_id,'ignore_transaction_email', true );

                    if( $transaction_type === 'INVOICE' ) {
                        if( empty( $current_user_id ) || $current_user_id < 1 ){
                            $author_id = get_post_field( 'post_author', $transaction_id );
                            $author_id = !empty($author_id) ? $author_id : 0;
                            $current_user = get_user_by('id', $author_id );
                        }
                        $customer_email = WC()->mailer()->emails['invoice_email_paid'];
                        $customer_email->recipient = $BillingEmail;
                        $customer_email->trigger( array(
                            'transaction_id' => $transaction_id,
                            'email_args' => $email_args,
                            'attachment' => array( $attachment ),
                        ));

                        $admin_email = WC()->mailer()->emails['invoice_email_paid_admin'];

                        $get_recipient = '';
                        if( true !== (bool)$ignore_email ){
                            $get_recipient = $current_user->user_email;
                        }

                        $admin_email->recipient = $get_recipient;
                        $admin_email->trigger( array(
                            'transaction_id' => $transaction_id,
                            'email_args' => $email_args,
                        ));

                    } else {
                        $customer_email = WC()->mailer()->emails['transaction_email'];
                        $customer_email->recipient = $BillingEmail;
                        $customer_email->trigger( array(
                            'transaction_id' => $transaction_id,
                            'email_args' => $email_args,
                        ));

                        $admin_email = WC()->mailer()->emails['transaction_email_admin'];
                        $recipients = [];
                        if (isset($admin_email->enabled) && $admin_email->enabled === 'yes') {
                            $recipients[] = $admin_email->recipient;
                        }
                        if (false === (bool)$ignore_email) {
                            $recipients[] = $current_user->user_email;
                        }
                        $admin_email->recipient = implode(', ',$recipients);
                        $admin_email->trigger( array(
                            'transaction_id' => $transaction_id,
                            'email_args' => $email_args,
                        ));
                    }

                    if( !empty( $order_status ) && strtolower($order_status) == 'declined' ){
                        $message = !empty( $order_status_response->message ) ? $order_status_response->message : __('Transaction is not captured successfully.','usb-swiper');
                        wp_send_json( array(
                            'result' => 'error',
                            'message' => $message,
                        ), 200 );
                    }

				    wp_send_json( array(
					    'result' => 'success',
					    'redirect' => $redirect_url,
                    ), 200 );
			    } else{
                    $message = !empty( $order_status_response->message ) ? $order_status_response->message : __('Transaction is not captured successfully.','usb-swiper');

                    //wp_delete_post($transaction_id);
				    wp_send_json( array(
					    'result' => 'error',
					    'message' => $message,
				    ), 200 );
			    }
		    } else{
			    $response = array( 'error' => true, 'message' => __('Transaction nonce not verified. Please try again.','usb_swiper') );
			    wp_send_json( $response, 200 );
		    }
		}

        /**
         * Pay by invoice method.
         *
         * @since 1.1.17
         *
         * @param $transaction_id
         * @return void
         */
        public function pay_by_invoice_transaction($transaction_id) {

            if( !class_exists('Usb_Swiper_Paypal_request') ) {
                include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
            }

			usb_swiper_set_session('usb_swiper_woo_transaction_id', 0);
			usb_swiper_set_session('usb_swiper_woo_transaction_id', $transaction_id);

            $Paypal_request = Usb_Swiper_Paypal_request::instance();
            $response = $Paypal_request->create_transaction_request($transaction_id);

            if( !empty( $response['id'] )) {

                if( !empty( $response['links'] ) && is_array( $response['links'] ) ) {
                    foreach ( $response['links'] as $key => $links ) {
                        if( !empty( $links['rel'] ) && 'self' === $links['rel'] && !empty( $links['href'] ) ) {
                            $order_response = $Paypal_request->request($links['href'], array(
                                'method' => 'GET',
                                'timeout' => 60,
                                'redirection' => 5,
                                'httpversion' => '1.1',
                                'blocking' => true,
                                'headers' => array(
                                    'Content-Type' => 'application/json',
                                    'Authorization' => 'Bearer '.$Paypal_request->get_access_token(),
                                ),
                            ), 'order_response', $transaction_id);
                            $Paypal_request->handle_paypal_debug_id($order_response, $transaction_id);
                            if( !empty( $order_response ) ) {
                                update_post_meta($transaction_id, '_payment_response', $order_response);
                                update_post_meta($transaction_id, '_payment_status', usbswiper_get_transaction_status($transaction_id) );
                            }
                        }
                    }
                }

                update_post_meta($transaction_id, '_paypal_transaction_id', $response['id']);
                usb_swiper_set_session('usb_swiper_woo_create_transaction_id', $response['id']);
                wp_send_json( array( 'orderID' => $response['id'], 'transaction_id' => $transaction_id ), 200 );

            } else{
                //wp_delete_post($transaction_id);
                $message_name = !empty( $response['name'] ) ? $response['name'] :'';
                $message = !empty( $response['message'] ) ? $response['message'] :'';
                $details = !empty( $response['details'][0] ) ? $response['details'][0] :'';

                wp_send_json( array(
                    'status' => 'error',
                    'message' => !empty( $details['description'] ) ? $details['description'] : $message,
                    'message_type' => !empty( $details['issue'] ) ? $details['issue'] : $message_name,
                ), 200 );
            }
        }

		/**
         * Capture authorize transaction.
         *
         * @since 1.0.0
         *
		 * @param $unique_id
		 */
		public function capture_authorize_transaction( $unique_id ) {

		    $data =  usb_swiper_get_unique_id_data($unique_id);
			$transaction_url = wc_get_endpoint_url( 'transactions' ,'');
		    if( !empty( $data ) && is_array( $data) ) {
			    $post_id = !empty( $data['transaction_id'] ) ? $data['transaction_id'] : '';
		        $paypal_transaction_id = !empty( $data['paypal_transaction_id'] ) ? $data['paypal_transaction_id'] : '';
		        if( !empty( $post_id ) && $post_id > 0 ) {

                    $transaction_status = usbswiper_get_transaction_status($post_id);
                    $payment_intent = usbswiper_get_transaction_type($post_id);

		            $payment_response = get_post_meta( $post_id,'_payment_response', true);
			        $purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
			        $payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
			        $payment_authorizations = !empty( $payment_details['authorizations'][0] ) ? $payment_details['authorizations'][0] : '';
			        $payment_links = !empty( $payment_authorizations['links'] ) ? $payment_authorizations['links'] : '';

                    $log_action_name = 'capture_authorized_order';
                    if( !empty( $payment_intent ) && $payment_intent === 'CAPTURE' && !empty( $transaction_status ) && $transaction_status === 'CREATED' ) {
                        $log_action_name = 'capture_created_order';
                        $payment_links = !empty( $payment_response['links'] ) ? $payment_response['links'] : '';
                    }

			        if( !empty( $payment_links ) && is_array( $payment_links ) ) {

				        $capture_url = '';
				        foreach ( $payment_links as $key => $value ) {

					        if( !empty( $value['rel']) && 'capture' === $value['rel']) {
						        $capture_url = !empty( $value['href'] ) ? $value['href'] : '';
					        }
				        }

				        $this->api_log = new Usb_Swiper_Log();

				        if( !class_exists('Usb_Swiper_Paypal_request') ) {
					        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
				        }

				        $Paypal_request = Usb_Swiper_Paypal_request::instance();

				        $order_total = get_post_meta($post_id,'GrandTotal',true);
                        $transaction_type = get_post_meta($post_id,'_transaction_type', true);
				        $platform_fees = usbswiper_get_platform_fees( $order_total, strtolower($transaction_type),$post_id );
				        $body_request = array();
				        if( !empty( $platform_fees ) && $platform_fees > 0 ) {

					        if ($this->is_sandbox) {
						        $admin_merchant_id = usb_swiper_get_field_value('sandbox_merchant_id');
					        } else{
						        $admin_merchant_id = usb_swiper_get_field_value('merchant_id');
					        }

					        $body_request['payment_instruction'] =array(
						        'disbursement_mode' => 'INSTANT',
						        'platform_fees' => array(
							        array(
								        'amount' => array(
									        "currency_code" => $Paypal_request->get_transaction_currency($post_id),
									        "value" => $platform_fees
								        ),
								        'payee' => array(
									        'merchant_id' => $admin_merchant_id,
								        ),
							        )
						        ),
					        );
				        }

				        $args = array(
					        'method' => 'POST',
					        'timeout' => 60,
					        'redirection' => 5,
					        'httpversion' => '1.1',
					        'blocking' => true,
					        'headers' => array(
						        'Content-Type' => 'application/json',
						        'Authorization' => 'Bearer '.$Paypal_request->get_access_token(),
					        ),
				        );

				        if( !empty( $body_request ) ) {
				            $args['body'] = json_encode($body_request);
				        }

				        $this->api_response = $Paypal_request->request($capture_url, $args, $log_action_name, $post_id);
				        $Paypal_request->handle_paypal_debug_id($this->api_response, $post_id);
				        if( !empty( $this->api_response['id'] ) ) {

                            $order_args = array(
                                'method' => 'GET',
                                'timeout' => 60,
                                'redirection' => 5,
                                'httpversion' => '1.1',
                                'blocking' => true,
                                'headers' => array(
                                    'Content-Type' => 'application/json',
                                    'Authorization' => 'Bearer ' . $Paypal_request->get_access_token(),
                                ),
                            );

                            $response = $Paypal_request->request($Paypal_request->order_url . $paypal_transaction_id, $order_args, 'order_response', $post_id);
                            $Paypal_request->handle_paypal_debug_id($response, $post_id);
                            if (!empty($response)) {
                                update_post_meta($post_id, '_payment_response', $response);
                                update_post_meta($post_id, '_payment_status', usbswiper_get_transaction_status($post_id) );
                            }
                            $payment_status = !empty($response['status']) ? $response['status'] : '';

                            $transaction_type = get_post_meta($post_id, '_transaction_type', true);
                            $BillingFirstName = get_post_meta($post_id, 'BillingFirstName', true);
                            $BillingEmail = get_post_meta($post_id, 'BillingEmail', true);
                            $author_id = get_post_field('post_author', $post_id);
                            $author_id = !empty($author_id) ? $author_id : 1;
                            $current_user = get_user_by('id', $author_id);
                            $ignore_email = get_user_meta($author_id, 'ignore_transaction_email', true);
                            $email_args = array(
                                'display_name' => wp_strip_all_tags($BillingFirstName)
                            );
                            if (!empty($payment_status) && strtolower($payment_status) === 'completed' && !empty( $transaction_type ) && strtolower( $transaction_type ) === 'invoice') {

                                $attachment = apply_filters('usb_swiper_email_attachment', '', $post_id);

                                $customer_email = WC()->mailer()->emails['invoice_email_paid'];
                                $customer_email->recipient = $BillingEmail;
                                $customer_email->trigger(array(
                                    'transaction_id' => $post_id,
                                    'email_args' => $email_args,
                                    'attachment' => array($attachment),
                                ));

                                $admin_email = WC()->mailer()->emails['invoice_email_paid_admin'];
                                $get_recipient = '';
                                if (true !== (bool)$ignore_email) {
                                    $get_recipient = $current_user->user_email;
                                }
                                $admin_email->recipient = $get_recipient;
                                $admin_email->trigger(array(
                                    'transaction_id' => $post_id,
                                    'email_args' => $email_args,
                                ));
                            } else if ( !empty($payment_status) && strtolower($payment_status) === 'completed' && !empty( $transaction_type ) ){

                                $customer_email = WC()->mailer()->emails['transaction_email'];
                                $customer_email->recipient = $BillingEmail;
                                $customer_email->trigger( array(
                                    'transaction_id' => $post_id,
                                    'email_args' => $email_args,
                                ));

                                $admin_email = WC()->mailer()->emails['transaction_email_admin'];
                                $recipients = [];
                                if (isset($admin_email->enabled) && $admin_email->enabled === 'yes') {
                                    $recipients[] = $admin_email->recipient;
                                }
                                if (false === (bool)$ignore_email) {
                                    $recipients[] = $current_user->user_email;
                                }
                                $admin_email->recipient = implode(', ',$recipients);
                                $admin_email->trigger( array(
                                    'transaction_id' => $post_id,
                                    'email_args' => $email_args,
                                ));
                            }

					        //update_post_meta($post_id, '_payment_status', $payment_status);
                            update_post_meta($post_id, '_payment_status', usbswiper_get_transaction_status($post_id) );
				        }
			        }
		        }

		        $transaction_url = esc_url( wc_get_endpoint_url( 'view-transaction', $post_id, wc_get_page_permalink( 'myaccount' ) ) );
		    }

		    wp_safe_redirect($transaction_url);
		    exit();
		}

		/**
         * Get email content for transaction.
         *
         * @since 1.0.0
         *
		 * @param $transaction_id
		 * @param array $args
		 *
		 * @return mixed|void
		 */
		public function get_email_content( $transaction_id, $args = array() ) {

		    $email_heading = !empty( $args['email_heading'] ) ? $args['email_heading'] : '';

		    ob_start();

                wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );

                $args = array(
                    'transaction_id' => $transaction_id,
                    'is_email' => true,
                );

                usb_swiper_get_template( 'wc-transaction-history.php', $args );

                wc_get_template( 'emails/email-footer.php' );

		    $email_content = ob_get_contents();
		    ob_get_clean();

		    return apply_filters('usb_swiper_get_email_content', $email_content);
		}

        /**
         * Get email content for invoice.
         *
         * @since 1.0.0
         *
         * @param $transaction_id
         * @param array $args
         *
         * @return mixed|void
         */
        public function get_invoice_email_content( $transaction_id, $args = array() ) {

            $email_heading = !empty( $args['email_heading'] ) ? $args['email_heading'] : '';
            $display_name = !empty( $args['display_name'] ) ? $args['display_name'] : '';
            ob_start();

            wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );


            $payment_link = '';
            if( !empty($args['payment_link']) && (bool)$args['payment_link'] ){
                $settings = usb_swiper_get_settings('general');
                $paybyinvoice_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';
                $payment_link = add_query_arg(array('invoice-session'=>base64_encode(json_encode(array('id' => "invoice_$transaction_id", 'status' => false))) ),get_the_permalink( $paybyinvoice_id ));
            }

            $args = array(
                'transaction_id' => $transaction_id,
                'payment_link' =>  $payment_link,
                'display_name' => $display_name,
                'is_email' => true,
            );

            usb_swiper_get_template( 'wc-transaction-history.php', $args );

            wc_get_template( 'emails/email-footer.php' );

            $email_content = ob_get_contents();
            ob_get_clean();

            return apply_filters('usb_swiper_get_email_content', $email_content);
        }

		/**
         * After logout page redirect to home page.
         *
         * @since 1.0.0
         *
		 * @param $logout_url
		 */
		public function wp_logout( $logout_url ) {
			wp_redirect( home_url() );
			exit();
		}

        /**
         * Added General Information title in additional info page.
         *
         * @since 1.0.0
         *
         * @return void
         */

		public function wc_edit_account_form_start() {
		    ?>
            <h2 class="wc-account-title general-info"><?php _e('General Information','usb-swiper'); ?></h2>
            <?php
		}

		/**
		 * Add custom fields in additional information tab in my account page.
         *
         * @since 1.0.0
		 */
		public function wc_edit_account_form($string) {
			$merchant_data = usbswiper_get_onboarding_merchant_response();

           if( empty( $merchant_data)) {
               return;
           }

			$get_countries = WC()->countries->get_countries();
			$ignore_email_checkbox = get_user_meta( get_current_user_id(),'ignore_transaction_email', true );
		    ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <?php
                echo  usb_swiper_get_html_field( array(
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
                    'class' => 'woocommerce-Select',
                    'wrapper' => false
                ));
                ?>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <?php

                echo  usb_swiper_get_html_field( array(
                    'type' => 'text',
                    'value' => usbswiper_get_invoice_prefix(),
                    'id' => 'InvoicePrefix',
                    'name' => 'InvoicePrefix',
                    'label' => __( 'Invoice Prefix', 'usb-swiper'),
                    'attributes' => '',
                    'description' => 'Enter a short, 3-4 character unique value here to avoid duplicate invoice ID errors from PayPal.  For example, if your 
                    company name is Acme, Inc. you might use ACME- as your prefix.',
                    'readonly' => false,
                    'disabled' => false,
                    'class' => 'woocommerce-Input woocommerce-Input--text input-text',
                    'wrapper' => false
                ));
                ?>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <?php

                echo  usb_swiper_get_html_field( array(
                    'type' => 'text',
                    'value' => usbswiper_get_brand_name(),
                    'id' => 'BrandName',
                    'name' => 'BrandName',
                    'label' => __( 'Brand Name', 'usb-swiper'),
                    'attributes' => '',
                    'description' => 'This is the name your customers should see on their credit card statement.',
                    'readonly' => false,
                    'disabled' => false,
                    'class' => 'woocommerce-Input woocommerce-Input--text input-text',
                    'wrapper' => false
                ));
                ?>
            </p>
            <div class="vt-upload-brand-logo-wrapper">
                <div class="vt-image-upload-button-wrap">
                    <?php echo  usb_swiper_get_html_field( array(
                        'type' => 'file',
                        'id' => 'vt_product_image',
                        'name' => 'BrandLogo',
                        'label' => __( 'Upload Brand Logo', 'usb-swiper'),
                        'required' => false,
                        'class' => 'p-2 vt-image-upload',
                        'wrapper_class' => 'vt-image-upload-wrap',
                        'attributes' => array('accept'=>'.png, .jpg, .jpeg')
                    )); ?>
                    <span><?php _e('Upload Image with max-width 250px','usb-swiper'); ?></span>
                </div>
                <div class="brand-logo-preview">
                    <?php $brand_logo = usbswiper_get_brand_logo(get_current_user_id(), false);

                    $attachment_id = $brand_logo['attachment_id'];
                    if ($brand_logo['image_html']) {
                        echo $brand_logo['image_html'];
                        ?>
                        <a title="<?php _e('Delete product', 'usb-swiper'); ?>" class="delete_brand_logo" data-delete-nonce="<?php echo wp_create_nonce('vt-remove-brand-logo'); ?>" data-attachment-id="<?php echo $attachment_id; ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="vt_user_timezone"><?php _e( 'Timezone', 'usb-swiper' ); ?></label>
                <select id="vt_user_timezone" name="vt_user_timezone" aria-describedby="timezone-description" class="woocommerce-Select">
                    <?php echo wp_timezone_choice( usbswiper_get_user_timezone(), get_user_locale() ); ?>
                </select>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide timezone-info">
                <span id="utc-time" class="timezone-time">
                    <?php echo sprintf(__( 'Universal time is %s' ), '<code>' .date_i18n( 'Y-m-d H:i:s', false, true ) . '</code>'); ?>
                </span>
                <span id="local-time" class="timezone-time">
                    <?php echo sprintf(__( 'Local time is %s' ), '<code>' . usbswiper_get_user_date_i18n() . '</code>'); ?>
                </span>
            </p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<?php
				echo  usb_swiper_get_html_field( array(
					'type' => 'checkbox',
					'id' => 'ignore_transaction_email',
					'name' => 'ignore-transaction-email',
					'label' => __( 'Ignore Transaction Emails', 'usb-swiper'),
					'required' => false,
					'attributes' => '',
					'description' => __( 'Disable admin email notifications for transactions.', 'usb-swiper'),
					'readonly' => false,
					'disabled' => false,
					'class' => 'woocommerce-checkbox',
					'wrapper' => false,
					'checked' => ! empty( $ignore_email_checkbox )
				));
				?>
			</p>
            <P class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<?php
				echo  usb_swiper_get_html_field( array(
					'type' => 'select',
					'id' => 'timeout_option',
					'name' => 'timeout_option',
					'label' => __( 'Screen Timout Options', 'usb-swiper'),
					'required' => true,
                    'value' => usb_swiper_get_user_timeout_option(),
					'options' => usb_swiper_get_timeout_options(),
					'default' => usb_swiper_get_default_timeout(),
					'attributes' => '',
					'description' => '',
					'readonly' => false,
					'disabled' => false,
					'class' => 'woocommerce-Select',
					'wrapper' => false,
					'tooltip' => true,
					'tooltip_text' => __( 'This setting allows you to specify the duration of time before you are automatically logged out.  The default is 30 min.', 'usb-swiper'),
				));
				?>
            </P>
            <h2 class="wc-account-title paypal-accpunt-info"><?php _e('PayPal Account Information','usb-swiper'); ?></h2>
            <table class="form-table paypal-account-information" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <th><?php _e('Merchant ID','usb-swiper' ); ?>:</th>
                        <td><?php echo !empty( $merchant_data['merchant_id'] ) ? $merchant_data['merchant_id'] : ''; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Primary Email','usb-swiper' ); ?>:</th>
                        <td><?php echo !empty( $merchant_data['primary_email'] ) ? $merchant_data['primary_email'] : ''; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Primary Email Confirmed','usb-swiper' ); ?>:</th>
                        <td>
		                    <?php
		                    $email_confirmed = !empty( $merchant_data['primary_email_confirmed'] ) ? $merchant_data['primary_email_confirmed'] : '';
		                    $is_email_confirm = '';
		                    $is_email_confirm_icon = 'dashicons-no';
		                    $is_email_confirm_label = __('Primary email is not confirmed','usb-swiper');
		                    if( !empty( $email_confirmed ) && $email_confirmed == 1 ) {
			                    $is_email_confirm = 'is-confirmed';
			                    $is_email_confirm_icon ='dashicons-yes';
			                    $is_email_confirm_label = __('Primary email is confirmed','usb-swiper');
		                    }
		                    ?>
                            <p class="vt-confirmation-icon paypal-email <?php echo $is_email_confirm; ?>" title="<?php echo $is_email_confirm_label; ?>"><span class="dashicons <?php echo $is_email_confirm_icon; ?>"></span></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Payments Receivable','usb-swiper' ); ?>:</th>
                        <td>
		                    <?php
		                    $payments_receivable = !empty( $merchant_data['payments_receivable'] ) ? $merchant_data['payments_receivable'] : '';
		                    $is_payments_receivable = '';
		                    $is_payments_receivable_icon = 'dashicons-no';
		                    $is_payments_receivable_label = __('Payments is not receivable','usb-swiper');
		                    if( !empty( $payments_receivable ) && $payments_receivable == 1 ) {
			                    $is_payments_receivable = 'is-confirmed';
			                    $is_payments_receivable_icon ='dashicons-yes';
			                    $is_payments_receivable_label = __('Payments is receivable','usb-swiper');
		                    }
		                    ?>
                            <p class="vt-confirmation-icon paypal-payments-receivable <?php echo $is_payments_receivable; ?>" title="<?php echo $is_payments_receivable_label; ?>"><span class="dashicons <?php echo $is_payments_receivable_icon; ?>"></span></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('OAuth Third Party','usb-swiper' ); ?>:</th>
                        <td>
		                    <?php
		                    $oauth_integrations = !empty( $merchant_data['oauth_integrations'][0] ) ? $merchant_data['oauth_integrations'][0] : '';
		                    $oauth_third_party = !empty( $oauth_integrations['oauth_third_party'][0] ) ? $oauth_integrations['oauth_third_party'][0] : '';
		                    $scopes = !empty( $oauth_third_party['scopes'] ) ? $oauth_third_party['scopes'] : '';
		                    if( !empty( $scopes ) && is_array( $scopes ) ) {
			                    echo '<ul class="oauth-third-party-scopes">';
			                    foreach ( $scopes as $s_key => $scope ) {
				                    echo "<li>".esc_url($scope)."</li>";
			                    }
			                    echo '</ul>';
		                    }
		                    ?>
                        </td>
                    </tr>
                    <?php if (!empty($merchant_data['products'])) { ?>
                        <tr>
                            <th><?php _e('Products', 'usb-swiper'); ?>:</th>
                            <td>
                                <?php
                                    foreach ($merchant_data['products'] as $product) {
                                        $product_name = !empty($product['name']) ? esc_html($product['name']) : __('Unknown Product', 'usb-swiper');
                                        $vetting_status = !empty($product['vetting_status']) ? esc_html($product['vetting_status']) : __('Unknown', 'usb-swiper');

                                        echo "<h4>{$product_name} (Status: {$vetting_status})</h4>";

                                        if (!empty($product['capabilities']) && is_array($product['capabilities'])) {
                                            echo '<ul class="capabilities-list">';
                                            foreach ($product['capabilities'] as $capability) {
                                                echo "<li><strong>" . esc_html($capability) . "</strong></li>";
                                            }
                                            echo '</ul>';
                                        }
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php } if (!empty($merchant_data['capabilities'])) { ?>
                        <tr>
                            <th><?php _e('Capabilities', 'usb-swiper'); ?>:</th>
                            <td>
                                <?php
                                $capabilities = !empty($merchant_data['capabilities']) ? $merchant_data['capabilities'] : array();

                                if (!empty($capabilities) && is_array($capabilities)) {
                                    echo '<ul class="capabilities-list">';
                                    foreach ($capabilities as $capability) {
                                        $name = !empty($capability['name']) ? esc_html($capability['name']) : __('Unknown', 'usb-swiper');
                                        $status = !empty($capability['status']) ? esc_html($capability['status']) : __('Unknown', 'usb-swiper');
                                        echo "<li><strong>{$name}</strong>: {$status}</li>";
                                    }
                                    echo '</ul>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th><?php _e('Country','usb-swiper' ); ?>:</th>
                        <td><?php
		                    $country_code = !empty( $merchant_data['country'] ) ? $merchant_data['country'] : 'US';
		                    echo !empty( $get_countries[$country_code] ) ? $get_countries[$country_code] : '';
		                    ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="woocommerce-form-row paypal-disconnect-button"><?php $this->paypal_disconnect_button(); ?></div>
            <div class="clear"></div>
            <?php
		}

		/**
         * Save custom fields in user data.
         *
         * @since 1.0.0
         *
		 * @param $user_id
		 */
		public function wc_save_account_details( $user_id ) {

			if ( is_user_logged_in() ) {

				$primary_currency = !empty( $_POST['TransactionCurrency'] ) ? $_POST['TransactionCurrency'] : 'USD';
				$brand_name = !empty( $_POST['BrandName'] ) ? $_POST['BrandName'] : '';
                $brand_logo = !empty( $_FILES['BrandLogo'] ) ? $_FILES['BrandLogo'] : '';
                $user_timezone = !empty( $_POST['vt_user_timezone'] ) ? $_POST['vt_user_timezone'] : '';
                $timeout_option = !empty( $_POST['timeout_option'] ) ? $_POST['timeout_option'] : usb_swiper_get_default_timeout();

                $logo_id = !empty( $brand_logo ) ? $this->vt_upload_from_path( $brand_logo ) : 0;

                $invoice_prefix = !empty( $_POST['InvoicePrefix'] ) ? sanitize_text_field($_POST['InvoicePrefix']) : '';
                $ignore_transaction_email = !empty( $_POST['ignore-transaction-email'] ) ? $_POST['ignore-transaction-email'] : '';
                update_user_meta( $user_id, "_primary_currency", $primary_currency );
                update_user_meta( $user_id, "brand_name", $brand_name );
                update_user_meta( $user_id, "invoice_prefix", $invoice_prefix );
                update_user_meta( $user_id, "ignore_transaction_email", $ignore_transaction_email );
                update_user_meta( $user_id, "vt_user_timezone", $user_timezone );
                update_user_meta( $user_id, "timeout_option", $timeout_option );

                if( !empty( $logo_id ) && $logo_id > 0 ) {
                    update_user_meta( $user_id, "brand_logo", $logo_id );
                }
			}
		}

        /**
         * Delete user brand logo.
         *
         * @since 2.2.4
         *
         * @return void
         */
        public function delete_brand_logo_cb() {

            $brand_logo_id = !empty( $_POST['attachment_id'] ) ? sanitize_text_field($_POST['attachment_id']) : 0;
            $status = false;
            $message_type = __('ERROR','usb-swiper');
            $message = __('Nonce not verified, please try after some time.', 'usb-swiper');

            if( !empty( $_POST['attachment_nonce'] ) && wp_verify_nonce($_POST['attachment_nonce'],'vt-remove-brand-logo') ) {
                $message = __('Brand logo id not verified, please try after some time.', 'usb-swiper');
                if( !empty($brand_logo_id) && $brand_logo_id > 0 ){
                    $message = __('Something went wrong, please try after some time.', 'usb-swiper');
                    $result = wp_delete_attachment($brand_logo_id, true);
                    if ($result !== false) {
                        $status = true;
                        $message_type = __('SUCCESS','usb-swiper');
                        $message = __('Brand logo successfully deleted.','usb-swiper');
                    }
                }
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'message_type' => $message_type,
            );

            wp_send_json( $response , 200 );
        }

		/**
		 * Create new refund request.
         *
         * @since 1.0.0
		 */
		public function create_refund_request() {

			$status = false;
			$message = __('Something went wrong. Please try again.','usb-swiper');
			$message_type = __('ERROR','usb-swiper');
			$refund_html = '';
            $refund_status = '';
            $refund_amount = '';
			if( !empty( $_POST['_nonce'] ) && wp_verify_nonce($_POST['_nonce'],'refund-request') ) {

				$transaction_id = !empty( $_POST['transaction_id'] ) ? (int)$_POST['transaction_id'] : '';
				if( !empty( $transaction_id ) && $transaction_id > 0 ) {

					$payment_response = get_post_meta( $transaction_id,'_payment_response', true);
					$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
					$payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
					$captures = !empty( $payment_details['captures'][0] ) ? $payment_details['captures'][0] : '';
					$payment_links = !empty( $captures['links'] ) ? $captures['links'] : '';

					if( !empty( $payment_links ) && is_array( $payment_links ) ) {
					    foreach ( $payment_links as $key => $payment_link ) {
					        if( !empty( $payment_link['rel']) && 'refund' === $payment_link['rel'] && !empty( $payment_link['href'] ) ) {

						        if ( ! class_exists( 'Usb_Swiper_Paypal_request' ) ) {
							        include_once USBSWIPER_PATH . '/includes/class-usb-swiper-paypal-request.php';
						        }

						        $args = array(
							        'refund_amount' => ! empty( $_POST['refund_amount'] ) ? $_POST['refund_amount'] : '',
							        'transaction_id' => $transaction_id,
							        'paypal_transaction_id' => !empty( $payment_response['id'] ) ? $payment_response['id'] : '',
						        );

						        $Paypal_request = Usb_Swiper_Paypal_request::instance();
						        $response = $Paypal_request->refund_request( $payment_link['href'], $args );

						        if( !empty( $response['id'] ) ) {
						            $status = true;
							        $message = __( 'Transaction amount refunded successfully.','usb-swiper' );
							        $refund_html =  $Paypal_request->get_refund_html($transaction_id);
                                    $payment_status = usbswiper_get_transaction_status($transaction_id);
                                    $BillingFirstName = get_post_meta( $transaction_id,'BillingFirstName', true);
                                    $BillingEmail = get_post_meta( $transaction_id,'BillingEmail', true);
                                    $attachment = apply_filters('usb_swiper_email_attachment', '', $transaction_id);
                                    $refund_status = usbswiper_get_payment_status($payment_status);
                                    $refund_amount = get_total_refund_amount($transaction_id);
                                    $email_args = array(
                                        'invoice' => true,
                                        'display_name' => wp_strip_all_tags($BillingFirstName)
                                    );

                                    $customer_email = WC()->mailer()->emails['payment_email_refund'];
                                    $customer_email->recipient = $BillingEmail;
                                    $customer_email->trigger( array(
                                        'transaction_id' => $transaction_id,
                                        'email_args' => $email_args,
                                        'attachment' => array( $attachment ),
                                    ));

                                    $admin_email = WC()->mailer()->emails['payment_email_refund_admin'];
                                    $get_recipient = '';
                                    $author_id = get_post_field( 'post_author', $transaction_id );
                                    $author_id = ! empty( $author_id ) ? $author_id : 1;
                                    $current_user = get_user_by('id', $author_id );
                                    $ignore_email = get_user_meta( $author_id,'ignore_transaction_email', true );
                                    $recipients = [];
                                    if (isset($admin_email->enabled) && $admin_email->enabled === 'yes') {
                                        $recipients[] = $admin_email->recipient;
                                    }
                                    if (false === (bool)$ignore_email) {
                                        $recipients[] = $current_user->user_email;
                                    }

                                    $recipient_string = implode(',', $recipients);
                                    $admin_email->recipient = $recipient_string;

                                    $admin_email->trigger(array(
                                        'transaction_id' => $transaction_id,
                                        'email_args' => $email_args,
                                    ));

						        } else{
						            $message = __( 'Transaction amount not refund. Please try again.','usb-swiper');
						            if( !empty( $response['error_description'] ) ) {
							            $message = $response['error_description'];
							            $message_type = !empty( $response['error'] ) ? $response['error'] : '';
						            } elseif ( isset($response['details'][0]['description']) && !empty( $response['details'][0]['description'] ) ) {
							            $message = $response['details'][0]['description'];
							            $message_type = !empty( $response['details'][0]['issue'] ) ? $response['details'][0]['issue'] : '';
						            } elseif ( !empty( $response['message'] ) ) {
							            $message = $response['message'];
							            $message_type = !empty( $response['name'] ) ? $response['name'] : '';
						            }
						        }
					        }
					    }
					}
				}

			} else {
				$message = __('Nonce not verified. Please try again.','usb-swiper');
			}

			$response = array(
				'status' => $status,
				'message' => $message,
				'message_type' => $message_type,
                'refund_status' => $refund_status,
                'remain_amount' => $refund_amount,
				'html' => $refund_html,
			);

			wp_send_json( $response , 200 );
		}

        /**
         * Callback function of woocommerce_after_my_account and woocommerce_after_customer_login_form hook.
         *
         * @since 1.0.0
         *
         * @return void
         */
		public function display_paypal_connect_button() {

            if( is_user_logged_in() ) {
                echo do_shortcode('[usb_swiper_paypal_connect label="Connect to PayPal" after_login_label="Launch Virtual Terminal"]');
            }
		}

        /**
         * Callback function of woocommerce_before_edit_account_form hook.
         *
         * @since 1.0.0
         *
         * @return void
         */
		public function wc_before_edit_account_form() {

			$merchant_data = usbswiper_get_onboarding_merchant_response();

            if( empty( $merchant_data ) ) {
                return;
            }

			$primary_email_confirmed = false;
			if( !empty( $merchant_data['primary_email_confirmed'] ) && $merchant_data['primary_email_confirmed'] == 1 ) {
				$primary_email_confirmed = true;
			}

			$payments_receivable = false;
			if( !empty( $merchant_data['payments_receivable'] ) && $merchant_data['payments_receivable'] == 1 ) {
				$payments_receivable = true;
			}

			?>
            <div class="vt-form-notification">
                <?php if( !$primary_email_confirmed ) { ?>
                    <p class='notification error'><strong><?php _e('Primary email is not confirmed:','usb-swiper'); ?></strong><?php _e('Your PayPal account email address needs to be confirmed before you can use this Virtual Terminal.  Please complete that process and come back when you are done.','usb-swiper' ); ?></p>
                <?php } ?>
			    <?php if( !$payments_receivable ) { ?>
                    <p class='notification error'><strong><?php _e('Account is not fully approved:','usb-swiper'); ?></strong>Your PayPal account is not fully approved for Complete Payments / Advanced Credit Cards.  Please contact PayPal to complete this process and then come back here to use the Virtual Terminal.</p>
			    <?php } ?>
            </div>
            <?php
		}

        /**
         * Update the transaction order status.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function update_order_status() {

            $status = false;

            if( !class_exists('Usb_Swiper_Log') ) {
                include_once USBSWIPER_PATH.'/includes/class-usb-swiper-log.php';
            }

            $api_log = new Usb_Swiper_Log();

            $order_id = ! empty( $_POST['order_id'] ) ? $_POST['order_id'] : '';
            $message = ! empty( $_POST['message'] ) ? $_POST['message'] : '';
            $transaction_id = ! empty( $_POST['transaction_id'] ) ? $_POST['transaction_id'] : '';
            $error = !empty( $_POST['error'] ) ? (array) json_decode( stripslashes( $_POST['error'] ) ) : [];
            $debug_id = !empty( $error['debug_id'] ) ? $error['debug_id'] : '';
            if( !empty( $transaction_id ) && $transaction_id > 0  ) {
                // $transactions = get_posts( array(
                //     'post_type' => 'transactions',
                //     'posts_per_page' => 1,
                //     'meta_query' => array(
                //         'relation' => 'AND',
                //         array(
                //             'key' => '_paypal_transaction_id',
                //             'value' => $order_id,
                //             'compare' => 'LIKE',
                //         )
                //     ),
                //     'fields' => 'ids',
                // ));

                // $transaction_id = !empty( $transactions[0] ) ? $transactions[0] : '';

                $response = get_post_meta( $transaction_id, '_payment_response', true);
                $order_status = !empty( $response['status'] ) ? $response['status'] : '';
                $order_intent = !empty( $response['intent'] ) ? $response['intent'] : '';
                update_post_meta($transaction_id, '_payment_status', 'FAILED');
                // update_post_meta($transaction_id, '_payment_status_notes', '');
                $transaction_type = get_post_meta($transaction_id, '_transaction_type', true);
                if( !empty( $transaction_type ) && strtolower($transaction_type) === 'invoice' ){
                    update_post_meta($transaction_id, '_payment_status', 'PENDING');
                }
                update_post_meta( $transaction_id, '_payment_failed_response', $error );
                update_post_meta( $transaction_id, '_paypal_transaction_debug_id', $debug_id );

                $api_log->log("", $transaction_id, true);

                $api_log->log("Action: ".ucwords(str_replace('_', ' ', 'order_failed')), $transaction_id);
                $api_log->log('Response Transaction ID: '.$order_id, $transaction_id);
                $api_log->log('Response Order Status: '.$order_status, $transaction_id);
                $api_log->log('Response Order Intent: '.$order_intent, $transaction_id);
                $api_log->log('Response Message: '.$message, $transaction_id);
                $api_log->log('Debug ID: '.$debug_id, $transaction_id);
                $api_log->log('Transaction failed with error: '.json_encode($error), $transaction_id);

                $status = true;
            }

            $response = array(
                'status' => $status,
                'message' => $message,
            );

            wp_send_json( $response);
        }


		/**
		 * Adds Email Template Path
		 *
		 * @since    1.1.8
		 */
		public function add_paypal_connected_email( $email_classes ) {

			// add the email class to the list of email classes that WooCommerce loads
			$email_classes['UsbSwiperPaypalConnectedEmail'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-paypal-connected-email.php';
			$email_classes['UsbSwiperPaypalDisconnectedEmail'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-paypal-disconnected-email.php';
			$email_classes['invoice_email_pending'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-Invoice-email-pending.php';
			$email_classes['invoice_email_pending_admin'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-Invoice-email-pending-admin.php';
			$email_classes['invoice_email_paid'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-Invoice-email-paid.php';
			$email_classes['invoice_email_paid_admin'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-Invoice-email-paid-admin.php';
            $email_classes['payment_email_refund'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-Invoice-email-refund.php';
            $email_classes['payment_email_refund_admin'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-Invoice-email-refund-admin.php';			$email_classes['transaction_email'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-transactions-email.php';
			$email_classes['transaction_email_admin'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-transactions-email-admin.php';
			$email_classes['paypal_profile_verification_request'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-profile-verification-request.php';
			$email_classes['paypal_profile_verification_completed'] =  include USBSWIPER_PATH . 'includes/class-usb-swiper-profile-verification-completed.php';

			return $email_classes;
		}

        /**
         * Redirects User to My Account or Virtual Terminal.
         *
         * @since   1.1.9
         */
		public function redirect_on_login($user_login, $user) {

            $user_id = !empty( $user->ID ) ? $user->ID : '';

            $settings = usb_swiper_get_settings('general');
            $vt_page_id = ! empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
            $vt_verification_page = ! empty( $settings['vt_verification_page'] ) ? (int) $settings['vt_verification_page'] : '';
            $myaccount_page_id = (int)get_option( 'woocommerce_myaccount_page_id' );

            $merchant_user_info = get_user_meta( $user_id,'_merchant_onboarding_user',true);
            $profile_status = get_user_meta( $user_id,'vt_user_verification_status', true );
            $profile_status = filter_var($profile_status, FILTER_VALIDATE_BOOLEAN);
            $profile_data = get_user_meta( $user_id,'verification_form_data', true );

			if( !empty( $merchant_user_info ) && empty( $profile_status ) && empty( $profile_data ) ) {
				wp_safe_redirect( get_the_permalink( $myaccount_page_id ) );
				exit();
			} elseif ( !empty( $merchant_user_info ) && !empty( $profile_status ) && !empty( $profile_data ) ) {
				wp_safe_redirect( get_the_permalink( $vt_page_id ) );
				exit();
			} else {
				wp_safe_redirect(get_the_permalink($vt_verification_page));
				exit();
			}
		}

		/**
		 * Create and update product.
		 *
		 * @return void
		 */
        public function vt_create_update_product() {

			$status = false;
			$message = __('Something went wrong. Please try again.','usb-swiper');
			$message_type = __('ERROR','usb-swiper');

			parse_str($_POST['fields'], $fields);

			if( ! empty( $fields['vt-add-product-form-nonce'] ) && wp_verify_nonce( $fields['vt-add-product-form-nonce'],'vt-add-product-form') ) {

				$vt_action = ! empty( $fields['vt-product-action'] ) ?  sanitize_text_field( $fields['vt-product-action']) : '';
				$product_id = !empty( $fields['vt_product_id'] ) ? sanitize_text_field( $fields['vt_product_id']) : '';
				$product_name = ! empty( $fields['product-name'] ) ? sanitize_text_field( $fields['product-name'] ) : '';
				$description  = ! empty( $fields['description'] ) ? sanitize_text_field( $fields['description'] ) : '';
				$price        = ! empty( $fields['price'] ) ? sanitize_text_field( $fields['price'] ) : '';
				$sku          = ! empty( $fields['sku'] ) ? sanitize_text_field( $fields['sku'] ) : '';
				$is_taxable     = !empty($fields['is_product_taxable']) ? sanitize_text_field($fields['is_product_taxable']) : false;

				$images    = ! empty( $_FILES['product_image'] ) ?  $_FILES['product_image'] : '';
				$images_id = !empty( $images ) ? $this->vt_upload_from_path( $images ) : 0;

				try {
					$product = '';
					if( !empty( $product_id ) && !empty( $vt_action ) && 'edit' === $vt_action ) {
						$product = wc_get_product( $product_id );
					} elseif ( !empty( $vt_action ) && 'add' === $vt_action ) {

						$product = new WC_Product_Simple();
						if (!empty($product_name)) {
							$product->set_slug(str_replace(' ', '-', $product_name));
						}
					}

					if( !empty( $product ) ) {

						if (!empty($product_name)) {
							$product->set_name($product_name);
						}

						if (!empty($price)) {
							$product->set_regular_price($price);
						}

						if (!empty($description)) {
							$product->set_description($description);
						}

						if (!empty($images_id)) {
							$product->set_image_id($images_id);
						}

						if (!empty($sku)) {
							$get_sku = usbswiper_get_product_sku($sku);
							$product->set_sku($get_sku);
						}

                        $product->update_meta_data( 'is_product_taxable', $is_taxable );

						$product->save();
					}
					$status = true;
					$message = __('Product created successfully.','usb-swiper');
					$message_type = __('SUCCESS','usb-swiper');
				} catch(Exception $e) {
					$message = $e->getMessage();
				}
			} else {
				$status = false;
				$message = __('Nonce not verified. Please try again.','usb-swiper');
			}

			wp_send_json(
				array(
					'status' => $status,
					'redirect_url' => wc_get_endpoint_url( 'vt-products', '', wc_get_page_permalink( 'myaccount' )),
					'message' => $message,
					'message_type' => $message_type,
				)
			);
        }

        /**
         * This function upload file from path.
         *
         * @since 1.1.17
         *
         * @param string $file
         * @return int|string|WP_Error
         */
        public function vt_upload_from_path( $file ) {

            $attach_id = '';

            if ( ! function_exists( 'wp_crop_image' ) ) {
                include( ABSPATH . 'wp-admin/includes/image.php' );
            }

            if ( ! empty( $file['tmp_name'] ) ) {

                $upload = wp_upload_bits( $file['name'], null, file_get_contents( $file['tmp_name'] ) );

                if ( $upload['error'] === false ) {

                    $attachment = array(
                        'post_mime_type' => $upload['type'],
                        'post_title' => $file['name'],
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attach_id = wp_insert_attachment( $attachment, $upload['file'] );

                    if( ! empty( $attach_id ) && $attach_id > 0 ) {
                        $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);

                        wp_update_attachment_metadata($attach_id, $attach_data);
                    }
                }
            }

            return $attach_id;
        }

        /**
         * Delete product of current users from database
         *
         * @return void
         */
        public function vt_delete_product_cb() {

            $status = false;
            $message = __( 'Something went Wrong', 'usb-swiper');
            $message_type = __('ERROR','usb-swiper');
            if( ! empty( $_POST['product_id'] ) ){

                $status = true;
                $message_type = __('SUCCESS','usb-swiper');
                $message = __( 'Product deleted successfully', 'usb-swiper');
                $product_id = sanitize_text_field( $_POST['product_id'] );
                wp_delete_post($product_id);
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'message_type' => $message_type,
            );

            wp_send_json( $response);
        }

        /**
         * Extend product query and show only admins products.
         *
         * @since 1.1.17
         *
         * @param $query
         * @return void
         */
        public function extend_product_query( $query ) {

            $args = array(
                'role'    => 'administrator',
                'order'   => 'ASC'
            );
            $admin_ids = array();

            $users = get_users( $args );
            if( ! empty( $users ) ) {

                foreach ($users as $user ) {
                    $admin_ids[] = $user->ID;
                }
            }

            $query->set( 'author__in', $admin_ids );
        }

        /**
         * Append HTML on click of add item in vt-product-wrapper
		 *
         * @since 1.1.9
         */
        public function add_vt_product_wrapper() {

            $status = false;
            $message = __( 'Something went Wrong', 'usb-swiper');
            $message_type = __('ERROR','usb-swiper');
            $html = '';
            $data_id = '';

            if( ! empty( $_POST['vt-add-product-nonce'] ) && wp_verify_nonce( $_POST['vt-add-product-nonce'],'vt_add_product_nonce') ) {
                $status = true;
                $message_type = __('SUCCESS','usb-swiper');
                $data_id = ! empty( $_POST['data-id'] ) ? (int)$_POST['data-id'] : 0;
                $html = get_product_html($data_id);
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'message_type' => $message_type,
                'html' => $html,
            );

            wp_send_json( $response , 200 );
        }

        /**
         * Display HTML on currency change.
         *
         * @since 4.0.1
         *
         * @return string
         */
        public function display_vt_product_wrapper() {

            $product_fields = usb_swiper_get_vt_form_fields('product_info');

            $html = '<div id="vt_fields_wrap_index_id" class="vt-fields-wrap" data-id="index_id">';
            if (!empty($product_fields) && is_array($product_fields)) {
                foreach ($product_fields as $product_field) {
                    $field_id = !empty($product_field['id']) ? $product_field['id'] : '';
                    if (!empty($field_id)) {
                        $product_field['id'] = $field_id . "_index_id";
                    }
                    $html .= usb_swiper_get_html_field($product_field);
                }
            }
            $html .= '<span class="vt-remove-fields-wrap">';
            $html .= '<svg viewBox="0 0 24 24" width="25" height="25" stroke="#d00" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            $html .= '</span>';
            $html .= '</div>';

            return $html;

        }

		/**
		 * Append product list in search fields
		 *
		 * @since 1.1.9
		 *
		 * @return void
		 */
        public function vt_search_product() {
            $status = false;
            $message = __( 'Something went Wrong', 'usb-swiper');
            $message_type = __('ERROR','usb-swiper');
            $product_option = array();

            if( ! empty( $_POST['vt-add-product-nonce'] ) && wp_verify_nonce( $_POST['vt-add-product-nonce'],'vt_add_product_nonce') ) {
                $status = true;
                $message_type = __('SUCCESS', 'usb-swiper');
                $product_key = ! empty( $_POST['product-key'] ) ? $_POST['product-key'] : '';
                $data = '';

                $products = new WP_Query(array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'author' => get_current_user_id(),
                    'order' => 'DESC',
                    's' => $product_key
                ));

                if (!empty($products->posts)) {
                    foreach ( $products->posts as $product ) {
                        $data .= "<span class='product-item' data-id='$product->ID'>$product->post_title</span>";
                    }
                }
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'message_type' => $message_type,
                'product_select' => $data,
            );

            wp_send_json( $response , 200 );
        }

        /**
         * Append tax list in search fields
         *
         * @since 1.1.9
         *
         * @return void
         */
        public function vt_search_tax() {
            $status = false;
            $message = __( 'Something went Wrong, please try after some time.', 'usb-swiper');
            $message_type = __('ERROR','usb-swiper');

            if( ! empty( $_POST['vt-add-tax-nonce'] ) && wp_verify_nonce( $_POST['vt-add-tax-nonce'],'vt_add_tax_nonce') ) {
                $status = true;
                $message_type = __('SUCCESS', 'usb-swiper');
                $tax_key = ! empty( $_POST['tax-key'] ) ? $_POST['tax-key'] : '';
                $data = '';
                $message = __( 'No any tax found.', 'usb-swiper');
                $tax_options = get_user_meta(get_current_user_id(), 'user_tax_data', true);
                if (!empty($tax_options) && is_array($tax_options)) {
                    $message = __( 'Tax options found successfully.', 'usb-swiper');
                    foreach ( array_reverse($tax_options) as $tax_option_key => $tax ) {
                        $tax_rate = !empty( $tax['tax_rate'] ) ? $tax['tax_rate'] : '';
                        $tax_label = !empty( $tax['tax_label'] ) ? $tax['tax_label'] : '';
                        $tax_on_shipping = !empty( $tax['tax_on_shipping'] ) ? $tax['tax_on_shipping'] : false;

                        if( !empty($tax_label) && !empty($tax_key) && str_contains(strtolower($tax_label), strtolower($tax_key)) ){
                            $data .= "<span class='tax-item' data-include-tax='$tax_on_shipping' data-id='$tax_rate'>$tax_label</span>";
                        }else if(!empty($tax_label) && empty($tax_key)) {
                            $data .= "<span class='tax-item' data-include-tax='$tax_on_shipping' data-id='$tax_rate'>$tax_label</span>";
                        }

                    }
                }
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'message_type' => $message_type,
                'product_select' => $data,
            );

            wp_send_json( $response , 200 );
        }

        /**
         * Append product price in input fields.
		 *
         * @since   1.1.9
         */
        public function vt_add_product_value_in_inputs() {

            $status = false;
            $message = __( 'Something went Wrong', 'usb-swiper');
            $message_type = __('ERROR','usb-swiper');
            $product_name = '';
            $product_price = '';
	        $is_taxable = false;

			$product_id = 0;
            if( ! empty( $_POST['vt-add-product-nonce'] ) && wp_verify_nonce( $_POST['vt-add-product-nonce'],'vt_add_product_nonce') ) {
                $status = true;
                $message_type = __('SUCCESS', 'usb-swiper');
                $product_id = !empty($_POST['product-id']) ? $_POST['product-id'] : '';

                $product = wc_get_product( $product_id );

                $product_name  = $product->get_name();
                $product_price = $product->get_price();
	            $is_taxable    = get_post_meta( $product_id, 'is_product_taxable', true );
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'message_type' => $message_type,
                'product_name' => $product_name,
                'product_price' => $product_price,
                'product_id' => $product_id,
                'is_taxable' => ( $is_taxable ) ? true : false,
            );

            wp_send_json( $response , 200 );
        }

        /**
         * Get pay by invoice template.
         *
         * @since 1.1.17
         *
         * @param array $args
         * @return false|string
         */
        public function usb_swiper_pay_by_invoice( $args ) {

            $args = shortcode_atts( array(
                'notifications' => maybe_unserialize(get_transient('get_vt_connection_response')),
                'invoice_id' => ''
            ), $args, 'usb_swiper_pay_by_invoice' );

            ob_start();
            $settings = usb_swiper_get_settings('general');
            $paybyinvoice_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';
            $by_link = false;
            if( !empty($paybyinvoice_id) && is_page($paybyinvoice_id) ) {
                $by_link = true;
                $invoice_session = !empty($_GET['invoice-session']) ? json_decode( base64_decode($_GET['invoice-session'])) : '';
                $args['invoice_id'] = !empty( $invoice_session->id ) ? trim($invoice_session->id, 'invoice_') :'';
                $args['invoice_status'] = !empty( $invoice_session->status ) ? $invoice_session->status :'';
            }

            if( usb_swiper_allow_user_by_role('administrator') || (bool)$by_link ) {
                usb_swiper_get_template( 'vt-pay-by-invoice.php', $args );
            }

            $form = ob_get_contents();

            ob_get_clean();

            return $form;
        }

        /**
         * Manage the invoice pdf attachment.
         *
         * @since 1.1.17
         *
         * @param string $attachment
         * @param int $transaction_id
         * @return mixed|string
         */
        public function manage_invoice_pdf_attachment( $attachment, $transaction_id ) {

            $transaction_type = get_post_meta( $transaction_id, '_transaction_type', true);

            if( !empty( $transaction_type ) && strtolower($transaction_type) === 'invoice') {

                require_once USBSWIPER_PLUGIN_DIR.'/library/usb-swiper-invoice-pdf.php';

                $Usb_Swiper_Invoice_PDF = new Usb_Swiper_Invoice_PDF();
                $get_attachment = $Usb_Swiper_Invoice_PDF->generate_invoice($transaction_id);
                $attachment = !empty( $get_attachment['invoice_path'] ) ? $get_attachment['invoice_path'] : '';
            }

            return $attachment;
        }

        /**
         * Pay using PayPal transaction handel callback function.
         *
         * @since 1.1.17
         *
         * @return void
         */
        public function manage_pay_with_paypal_transaction() {

            if (!class_exists('Usb_Swiper_Paypal_request')) {
                include_once USBSWIPER_PATH . '/includes/class-usb-swiper-paypal-request.php';
            }

            $transaction_id = !empty($_POST['transaction_id']) ? $_POST['transaction_id'] : '';

            $Paypal_request = Usb_Swiper_Paypal_request::instance();

            if (!empty($_POST['is_error'])) {
                $order_data = !empty($_POST['orderData']) ? explode(').', $_POST['orderData']) : '';
                $paypal_err_response = !empty($order_data[0]) ? stripslashes(trim($order_data[0])) : '';
                $paypal_response = !empty($order_data[1]) ? json_decode(stripslashes(trim($order_data[1]))) : '';
                $paypal_response = object_to_array($paypal_response);
                $Paypal_request->handle_paypal_debug_id($paypal_response, $transaction_id);
                $log_arr = array(
                    'response' => array(
                        'code' => 400,
                        'message' => $paypal_err_response,
                    ),
                    'headers' => '',
                    'body' => json_encode($paypal_response),
                );
                $Paypal_request->parse_response($log_arr, '', '', 'order_failed', $transaction_id);
                $error_message_type = !empty($paypal_response['name']) ? $paypal_response['name'] : '';
                $error_message = !empty($paypal_response['message']) ? $paypal_response['message'] : '';
                $response = array(
                    'message' => sprintf('%s %s', '<strong>' . $error_message_type . '</strong>', $error_message),
                );

                wp_send_json($response, 200);
            }

            $order_data = !empty($_POST['orderData']) ? json_decode(stripslashes($_POST['orderData'])) : '';
            $orderData = object_to_array($order_data);
            if( !empty( $orderData['links'] ) && is_array( $orderData['links'] ) ) {
                foreach ($orderData['links'] as $key => $links) {
                    if (!empty($links['rel']) && 'self' === $links['rel'] && !empty($links['href'])) {
                        $order_response = $Paypal_request->request($links['href'], array(
                            'method' => 'GET',
                            'timeout' => 60,
                            'redirection' => 5,
                            'httpversion' => '1.1',
                            'blocking' => true,
                            'headers' => array(
                                'Content-Type' => 'application/json',
                                'Authorization' => 'Bearer ' . $Paypal_request->get_access_token(),
                            ),
                        ), 'order_response', $transaction_id);
                        $Paypal_request->handle_paypal_debug_id($order_response, $transaction_id);;
                        if( !empty( $order_response ) ) {
                            update_post_meta($transaction_id, '_payment_response', $order_response);
                            update_post_meta($transaction_id, '_payment_status', usbswiper_get_transaction_status($transaction_id) );
                        }
                    }
                }
            }

            /*$purchase_units = ! empty( $orderData['purchase_units'][0] ) ? $orderData['purchase_units'][0] : '';
            if( empty( $transaction_id )) {
                $reference_id = !empty($purchase_units['reference_id']) ? $purchase_units['reference_id'] : 0;
                $transaction_id = !empty($reference_id) ? str_replace('vt_transaction_', '', $reference_id) : 0;
            }

            $Paypal_request->handle_paypal_debug_id( $orderData, $transaction_id);*/
            $log_arr = array(
                'response' => array(
                    'code' => 200,
                    'message' => '',
                ),
                'headers' => '',
                'body' => json_encode($orderData),
            );

            $Paypal_request->parse_response( $log_arr, '','','capture_order',$transaction_id);
            $order_status = !empty( $orderData['status'] ) ? $orderData['status'] : '';
            $settings = usb_swiper_get_settings('general');
            $pay_by_invoice_id = !empty( $settings['vt_paybyinvoice_page'] ) ? (int)$settings['vt_paybyinvoice_page'] : '';
            $redirect_url = add_query_arg( array('invoice-session'=> base64_encode(json_encode(array('id' => "invoice_$transaction_id", 'status' => $order_status)))), get_the_permalink( $pay_by_invoice_id ) );

            $payment_status = 'PENDING';
            if( !empty( $order_status ) && strtolower( $order_status ) === 'completed' ){
                $payment_status = 'PAID';
            }

            update_post_meta($transaction_id, '_payment_status', $payment_status);
            //update_post_meta($transaction_id, '_payment_Status', usbswiper_get_payment_status($transaction_id));

            $BillingFirstName = get_post_meta( $transaction_id,'BillingFirstName', true);
            $BillingEmail = get_post_meta( $transaction_id,'BillingEmail', true);
            $attachment = apply_filters('usb_swiper_email_attachment', '', $transaction_id);

            $email_args = array(
                'display_name' => wp_strip_all_tags($BillingFirstName)
            );

            $customer_email = WC()->mailer()->emails['invoice_email_paid'];
            $customer_email->recipient = $BillingEmail;
            $customer_email->trigger( array(
                'transaction_id' => $transaction_id,
                'email_args' => $email_args,
                'attachment' => array( $attachment ),
            ));

            $admin_email = WC()->mailer()->emails['invoice_email_paid_admin'];
            $get_recipient = '';
            $author_id = get_post_field( 'post_author', $transaction_id );
            $author_id = ! empty( $author_id ) ? $author_id : 1;
            $current_user = get_user_by('id', $author_id );
            $ignore_email = get_user_meta( $author_id,'ignore_transaction_email', true );
            if( true !== (bool)$ignore_email ){
                $get_recipient = $current_user->user_email;
            }
            $admin_email->recipient = $get_recipient;
            $admin_email->trigger( array(
                'transaction_id' => $transaction_id,
                'email_args' => $email_args,
            ));

            $response = array(
                'redirect_url' => $redirect_url
            );

            wp_send_json( $response, 200 );
        }

        /**
         * Add Transaction id in email subject and heading.
         *
         * @since 1.1.17
         *
         * @param string $string
         * @param object $data
         * @return array|string|string[]
         */
        public function format_email_subject_and_heading( $string, $data ) {

            $transaction_id = !empty( $data->profile_args['transaction_id'] ) ? $data->profile_args['transaction_id'] : '';

            if( !empty( $transaction_id ) ) {
                $transaction_type = get_post_meta( $transaction_id, '_transaction_type', true);

                if( 'invoice' === strtolower( $transaction_type ) ){
                    $user_invoice_id = get_post_meta( $transaction_id,'_user_invoice_id', true );
                    $string = str_replace('{#invoice_number#}', '#'.$user_invoice_id, $string );
                } else {
	                $string = str_replace('{#invoice_number#}', '#'.$transaction_id, $string );
                }

				$author_id = get_post_field( 'post_author', $transaction_id );
				$author_id = !empty($author_id) ? $author_id : 1;
				$brand_name = get_user_meta( (int)$author_id,'brand_name', true );
				$brand_name = !empty( $brand_name ) ? $brand_name : '';
				$string  = str_replace('{#merchant_brand_name#}', $brand_name, $string );
				$string  = str_replace('{#transaction_id#}', '#'.$transaction_id, $string );
                $string = str_replace('{#transaction_type#}',ucfirst(strtolower($transaction_type)), $string);
            }

            return $string;
        }

		/**
		 * Send transaction email to customer again.
		 *
		 * @since 1.1.17
		 */
		public function send_transaction_email() {

			$status  = false;
			$message = '';

			if( ! empty( $_POST['vt-send-email-nonce'] ) && wp_verify_nonce( $_POST['vt-send-email-nonce'],'vt-send-email-form') ) {
				$status            = true;
				$billing_email     = ! empty( $_POST['billing_email'] ) ? sanitize_text_field( $_POST['billing_email'] ) : '';
				$transaction_id    = ! empty( $_POST['transaction_id'] ) ? sanitize_text_field( $_POST['transaction_id'] ) : '';
				$message           = sprintf( __( 'Transaction(#%s) receipt copy has been sent via email.','usb-swiper' ), $transaction_id);
			}

			if( ! empty( $billing_email ) ) {

				if ( ! class_exists( 'WC_Email', false ) ) {
					include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php';
				}

				$WC_Email     = new WC_Email();
				$get_headers  = $WC_Email->get_headers();
				$site_title   = get_option( 'blogname' );
				$user_subject = sprintf( __( "Your %s transaction has been received!", 'usb-swiper' ), $site_title );
				$user_content = $this->get_email_content( $transaction_id, array( 'email_heading' => __( 'Thank you for your Transaction', 'usb-swiper' ) ) );
				$user_content = $WC_Email->format_string( $user_content );
				$user_content = $WC_Email->style_inline( $user_content );

				wp_mail( $billing_email, $user_subject, $user_content, $get_headers );
			}

			$response = array(
				'status' => $status,
				'message' => $message,
			);

			wp_send_json( $response , 200 );
		}

		/**
		 * Send the transaction email html.
		 *
		 * @since 1.1.17
		 *
		 * @return void
		 */
		public function send_transaction_email_html() {

			$status = false;
			$html = '';

			$transaction_id = !empty($_POST['transaction_id']) ? $_POST['transaction_id'] : 0;
			if (!empty($transaction_id) && $transaction_id > 0) {
				$status = true;
				$html = usbswiper_send_email_receipt_html($transaction_id);
			}

			$response = array(
				'status' => $status,
				'html' => $html,
			);

			wp_send_json( $response , 200 );
		}

        /**
         * Save function for VT verification form data in user meta.
         *
         * @since 1.1.17
         *
         * @return void
         */
        public function vt_verification_form_cb() {
            $status       = false;
            $message      = '';
            $redirect_url = '';

            if( ! empty( $_POST['vt-verification-nonce'] ) && wp_verify_nonce( $_POST['vt-verification-nonce'],'vt-verification-form') ) {

                $status           = true;
                $message          = __( 'Thank you for submitting data, Please wait for profile verification.','usb-swiper' );
                $first_name        = ! empty( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
                $last_name        = ! empty( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
                $phone            = ! empty( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
                $url              = ! empty( $_POST['website-url'] ) ? sanitize_url( $_POST['website-url'] ) : '';
                $company_name     = ! empty( $_POST['company-name'] ) ? sanitize_text_field( $_POST['company-name'] ) : '';
                $email            = ! empty( $_POST['email-address'] ) ? sanitize_email( $_POST['email-address'] ) : '';
                $billing_street = ! empty( $_POST['billing_address_1'] ) ? sanitize_text_field( $_POST['billing_address_1'] ) : '';
                $billing_street2 = ! empty( $_POST['billing_address_2'] ) ? sanitize_text_field( $_POST['billing_address_2'] ) : '';
                $billing_city = ! empty( $_POST['billing_city'] ) ? sanitize_text_field( $_POST['billing_city'] ) : '';
                $billing_state = ! empty( $_POST['billing_state'] ) ? sanitize_text_field( $_POST['billing_state'] ) : '';
                $billing_postal_code = ! empty( $_POST['billing_postcode'] ) ? sanitize_text_field( $_POST['billing_postcode'] ) : '';
                $billing_country_code = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
                $redirect_url     = get_the_permalink( get_option('woocommerce_myaccount_page_id') );

                $current_user_id = get_current_user_id();

                wp_update_user( array(
                    'ID' => $current_user_id,
                    'user_url' => $url,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                ));

                update_user_meta($current_user_id ,'billing_first_name', $first_name);
                update_user_meta($current_user_id ,'billing_last_name', $last_name);
                update_user_meta($current_user_id ,'billing_company', $company_name);
                update_user_meta($current_user_id ,'billing_phone', $phone);
                update_user_meta($current_user_id ,'billing_email', $email);
                update_user_meta($current_user_id ,'billing_address_1', $billing_street);
                update_user_meta($current_user_id ,'billing_address_2', $billing_street2);
                update_user_meta($current_user_id ,'billing_city', $billing_city);
                update_user_meta($current_user_id ,'billing_state', $billing_state);
                update_user_meta($current_user_id ,'billing_postcode', $billing_postal_code);
                update_user_meta($current_user_id ,'billing_country', $billing_country_code);
                update_user_meta($current_user_id, 'verification_form_data', true );

                $brand_name = get_user_meta( get_current_user_id(),'brand_name', true);
                if( empty( $brand_name ) ) {
                    update_user_meta($current_user_id ,'brand_name', $company_name);
                }
                $new_email = WC()->mailer()->emails['paypal_profile_verification_request'];
                $new_email->trigger( array(
                    'user_id' => $current_user_id,
                    'user_name' => $first_name.' '.$last_name,
                ));
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'location_redirect' => $redirect_url
            );

            wp_send_json( $response , 200 );
        }

        /**
         * Add admin email in header bcc.
         *
         * @since 1.1.17
         *
         * @param string $header
         * @param string $email_id
         *
         * @return mixed|string
         */
        public function vt_woocommerce_email_headers( $header, $email_id ) {

            if ( !empty( $email_id ) && $email_id === 'transaction_email_admin' ) {
                $admin_email_id = get_option('admin_email');
                $admin_email_id = !empty( $admin_email_id ) ? $admin_email_id : '';
                $header .= 'Bcc: '.$admin_email_id . "\r\n";
            }

            return $header;
        }

		/**
		 * Update the email header.
		 *
		 * @since 1.1.17
		 *
         * @param string $header
         * @param string $id
         * @param object $header_object
         * @param object $email
         *
         * @return string
         */
        public function vt_email_headers( $header, $id, $header_object, $email ) {

            $header .= "From: {$email->get_from_name()} <{$email->get_from_address()}>\r\n";

            return $header;
        }

        /**
         * Redirect user to verification page after register from WooCommerce account page.
         *
         * @since 1.1.17
         *
         * @return void
         */
        public function wc_registration_redirect() {
            $settings = usb_swiper_get_settings('general');
            $vt_verification_page = ! empty( $settings['vt_verification_page'] ) ? (int) $settings['vt_verification_page'] : '';
            wp_safe_redirect(get_the_permalink($vt_verification_page));
        }

        /**
         * Get the states based on country code.
         *
         * @since 1.1.17
         *
         * @return void
         */
        public function vt_get_states() {

            $country_code = !empty($_POST['country_code']) ? sanitize_text_field($_POST['country_code']) : 'US';
            $field_id = !empty($_POST['field_id']) ? sanitize_text_field($_POST['field_id']) : 'BillingCountryCode';
            $get_states = usb_swiper_get_states($country_code);

			$form_field = '';
			$is_shipping = false;
			if( !empty( $field_id ) && 'BillingCountryCode' === $field_id ) {

				if( !empty( $get_states ) ) {

					$form_field = array(
						'type' => 'select',
						'id' => 'BillingState',
						'name' => 'BillingState',
						'label' => __( 'State', 'usb-swiper'),
						'required' => true,
						'attributes' => '',
						'options' => $get_states,
						'description' => '',
						'class' => 'vt-billing-address-field vt-select-field vt-billing-states',
						'wrapper' =>  false,
					);
				} else {

					$form_field = array(
						'type' => 'text',
						'id' => 'BillingState',
						'name' => 'BillingState',
						'label' => __( 'State', 'usb-swiper'),
						'required' => true,
						'attributes' => '',
						'description' => '',
						'class' => 'vt-billing-address-field vt-input-field vt-billing-states',
						'wrapper' =>  false,
					);
				}
			} elseif ( !empty( $field_id ) && 'ShippingCountryCode' === $field_id ) {

				$is_shipping = true;
				if( !empty( $get_states ) ) {

					$form_field = array(
						'type' => 'select',
						'id' => 'ShippingState',
						'name' => 'ShippingState',
						'label' => __( 'State', 'usb-swiper'),
						'required' => true,
						'options' => $get_states,
						'attributes' => '',
						'description' => '',
						'class' => 'vt-shipping-address-field vt-select-field vt-shipping-states',
						'wrapper' =>  false,
					);
				} else {

					$form_field = array(
						'type' => 'text',
						'id' => 'ShippingState',
						'name' => 'ShippingState',
						'label' => __( 'State', 'usb-swiper'),
						'required' => true,
						'attributes' => '',
						'description' => '',
						'class' => 'vt-shipping-address-field vt-input-field vt-shipping-states',
						'wrapper' =>  false,
					);
				}
			} elseif ( !empty( $field_id ) && 'billing_country' === $field_id ) {

				if( !empty( $get_states ) ) {

					$form_field = array(
						'type' => 'select',
						'id' => 'billing_state',
						'name' => 'billing_state',
						'label' => __( 'State / County', 'usb-swiper'),
						'required' => true,
						'options' => $get_states,
						'attributes' => '',
						'description' => '',
						'class' => 'vt-billing-address-field vt-select-field vt-billing-states',
						'wrapper' =>  false,
					);
				} else {

					$form_field = array(
						'type' => 'text',
						'id' => 'billing_state',
						'name' => 'billing_state',
						'label' => __( 'State / County', 'usb-swiper'),
						'required' => true,
						'attributes' => '',
						'description' => '',
						'class' => 'vt-billing-address-field vt-input-field vt-billing-states',
						'wrapper' =>  false,
					);
				}
			}

			$state_html = !empty( $form_field ) ? usb_swiper_get_html_field( $form_field ) : '';

            wp_send_json( array(
				'state_html' => $state_html,
				'is_shipping' => $is_shipping,
			) , 200 );
        }

        /**
         *
         * Add enctype for edit account form.
         *
         * @since 2.2.2
         *
         * @return void
         */
        public function add_enctype_edit_account_form() {
            echo 'enctype="multipart/form-data"';
        }

        /**
         * Manage email header template.
         *
         * @since 2.2.2
         *
         * @return void
         */
        public function manage_wc_email_template( $template, $template_name ) {

            if( !empty( $template_name) && $template_name === 'emails/email-header.php' ) {
                $template = USBSWIPER_PATH . 'templates/emails/email-header.php';
            }

            if( !empty( $template_name) && $template_name === 'emails/email-footer.php' ) {
                $template = USBSWIPER_PATH . 'templates/emails/email-footer.php';
            }

            return $template;
        }

        /**
         * Add tax rules on my-account page.
         *
         * @return void
         */
        public function vt_create_update_product_tax() {

            $status = false;
            $message = __('Nonce not verified. Please try again.','usb-swiper');
            $message_type = __('ERROR','usb-swiper');

            if( !empty($_POST['fields']) ){
                parse_str($_POST['fields'], $fields);
            }

            if( ! empty( $fields['vt-add-taxrule-form-nonce'] ) && wp_verify_nonce( $fields['vt-add-taxrule-form-nonce'],'vt-add-taxrule-form') ) {

                $vt_action = ! empty( $fields['vt-taxrule-action'] ) ?  sanitize_text_field( $fields['vt-taxrule-action']) : '';

                $tax_label = ! empty( $fields['tax_label'] ) ? sanitize_text_field( $fields['tax_label'] ) : '';
                $tax_rate  = ! empty( $fields['tax_rate'] ) ? sanitize_text_field( $fields['tax_rate'] ) : '';

                if( empty($tax_label) || empty($tax_rate) ) {
                    $message = __('Tax label and Tax rate both fields is required','usb-swiper');
                } else {
                    $tax_id = strtolower(str_replace([' ', '+', '%', '!', '@', '#', '$', '^', '&', '*', '(', ')', '-', '=', '/', '>', '<', ','], '_', $tax_label)) . '_' . $tax_rate;

                    $include_shipping = isset($fields['tax_on_shipping']) ? true : false;

                    try {
                        $user_id = get_current_user_id();

                        $tax_data = get_user_meta($user_id, 'user_tax_data', true);
                        $tax_data = !empty($tax_data) ? $tax_data : array();

                        if (!empty($tax_data) && is_array($tax_data)) {
                            $labels = array_column($tax_data, 'tax_label');
                            $label_exists = in_array($tax_label, $labels);
                        }

                        if ((empty($tax_id) || empty($vt_action) || 'edit' !== $vt_action) && $label_exists) {
                            $message = __('Tax label exists.', 'usb-swiper');
                        } else {
                            $new_tax_item = array(
                                'tax_label' => $tax_label,
                                'tax_rate' => $tax_rate,
                                'tax_on_shipping' => $include_shipping
                            );
                            $message = __('Tax created successfully.', 'usb-swiper');

                            if (!empty($tax_data) && is_array($tax_data)) {
                                if (!empty($tax_id) && !empty($vt_action) && 'edit' === $vt_action) {
                                    if (!empty($fields['vt_taxrule_id']) && isset($tax_data[$fields['vt_taxrule_id']])) {
                                        unset($tax_data[$fields['vt_taxrule_id']]);
                                    }
                                    $tax_data[$tax_id] = $new_tax_item;
                                    $message = __('Tax updated successfully.', 'usb-swiper');
                                } elseif (!empty($vt_action) && 'add' === $vt_action) {
                                    $tax_data[$tax_id] = $new_tax_item;
                                }
                            } else {
                                $tax_data[$tax_id] = $new_tax_item;
                            }

                            update_user_meta($user_id, 'user_tax_data', $tax_data);

                            $status = true;
                            $message_type = __('SUCCESS', 'usb-swiper');
                        }
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                    }
                }
            }

            wp_send_json(
                array(
                    'status' => $status,
                    'redirect_url' => wc_get_endpoint_url( 'vt-tax-rules', '', wc_get_page_permalink( 'myaccount' )),
                    'message' => $message,
                    'message_type' => $message_type,
                )
            );
        }

        /**
         * Delete tax rules on the my-account page.
         *
         * @return void
         */
        public function vt_delete_tax_data() {
            $status = false;
            $message_type = "error";
            $message = __('Nonce not verified, please try after some time.', 'usb-swiper');
            if ( !empty( $_POST['tax_nonce'] ) && wp_verify_nonce($_POST['tax_nonce'],'vt-remove-tax') && isset($_POST['tax_id']) && !empty($_POST['tax_id']) ) {
                $message = __('Something went wrong, please try after some time', 'usb-swiper');
                $tax_id = sanitize_text_field($_POST['tax_id']);
                $user_id = get_current_user_id();
                $tax_data = get_user_meta($user_id, 'user_tax_data', true);
                if (is_array($tax_data) && isset($tax_data[$tax_id])) {
                    $status = true;
                    $message_type = "success";
                    $message = __('Tax removed successfully.', 'usb-swiper');
                    unset($tax_data[$tax_id]);
                    update_user_meta($user_id, 'user_tax_data', $tax_data);
                }
            }
            wp_send_json(
                array(
                    'status' => $status,
                    'redirect_url' => wc_get_endpoint_url( 'vt-tax-rules', '', wc_get_page_permalink( 'myaccount' )),
                    'message' => $message,
                    'message_type' => $message_type,
                )
            );
        }
		
		/**
         * Handle default tax.
         *
		 * @return void
		 */
        public function handle_default_tax(){
            if(isset($_POST['default_tax_nonce']) && wp_verify_nonce($_POST['default_tax_nonce'],'vt-default-tax-form')) {
                $default_tax = !empty($_POST['default-tax']) ? sanitize_text_field($_POST['default-tax']) : "";
                $user_id = get_current_user_id();
                if( empty( $user_id ) ){
                    return ;
                }
                update_user_meta($user_id,'default_tax',$default_tax);
            }
        }
		
		/**
         * Manage zettle my-account endpoint template.
         *
         * @since 2.3.4
         *
		 * @return void
		 */
        public function vt_zettle_endpoint_cb() {
         
	        usb_swiper_get_template('vt-zettle.php', [
                'get_zettle_fields' => UsbSwiperZettle::get_setting_fields(),
                'zettle_settings' => UsbSwiperZettle::get_settings('', 'both'),
                'zettle_token' => UsbSwiperZettle::get_token_data(),
            ]);
        }

		/**
         * Manage zettle settings.
         *
         * @since 2.3.4
         *
		 * @return void
		 * @throws Exception
		 */
        public function manage_zettle_settings() {
	        
	        if( is_wc_endpoint_url('vt-zettle') ) {
                
                if( !empty( $_POST['action'] ) && 'vt-zettle-form' === $_POST['action'] && !empty( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], 'vt-zettle-form-nonce') ) {
	                
	                $get_setting_fields = UsbSwiperZettle::get_setting_fields();
	                
	                $settings = [];
                    if( !empty( $get_setting_fields ) && is_array( $get_setting_fields  ) ) {
                    
                        foreach ( $get_setting_fields as $key => $get_setting_field ) {
                            $field_id = !empty( $get_setting_field['id']) ? $get_setting_field['id'] : '';
	                        $settings[$field_id] = !empty($_POST[$field_id]) ? sanitize_text_field($_POST[$field_id]) : '';
	                    }
                    }
                    
	                update_user_meta(get_current_user_id(), 'usb_swiper_zettle_settings', $settings);
                }
	        }
	        
	        $this->manage_zettle_outh_code();
	        $this->disconnect_zettle_app();
	        $this->unpairing_zettle_device();
        }
		
		/**
         * Manage zettle outh code after successfully get the access.
         *
         * @since 2.3.4
         *
		 * @return void
		 */
        public function manage_zettle_outh_code() {
	        
	        if( is_wc_endpoint_url('vt-zettle') && !empty( $_GET['code'] ) ) {
		        $token_response = UsbSwiperZettle::generate_token( esc_attr( $_GET['code'] ) );
		        if( !empty( $token_response ) && (int) $token_response['status'] == 200 ) {
			        update_user_meta( get_current_user_id(), 'usb_swiper_zettle_token', $token_response  );
		        }
		        
		        wp_safe_redirect(UsbSwiperZettle::get_redirection_uri());
		        exit();
	        }
        }

		/**
         * Disconnect zettle application.
         *
         * @since 2.3.4
         *
		 * @return void
		 * @throws Exception
		 */
        public function disconnect_zettle_app() {
	        
	        if( is_wc_endpoint_url('vt-zettle') && !empty( $_GET['disconnect_app'] ) ) {
		        UsbSwiperZettle::disconnect_app();
		        wp_safe_redirect(UsbSwiperZettle::get_redirection_uri());
		        exit();
	        }
        }

		/**
         * Unpairing zettle device.
         *
         * @since 2.3.4
         *
		 * @return void
		 * @throws Exception
		 */
		public function unpairing_zettle_device()  {
	        
	        if( is_wc_endpoint_url('vt-zettle') && is_user_logged_in() && !empty( $_GET['unpairing'] ) ) {
		        
                $user_id = get_current_user_id();
                
		        $reader_data = UsbSwiperZettle::get_zettle_reader_data( $user_id );
                $id =  !empty( $reader_data['id'] ) ? $reader_data['id'] : '';
		        $response = UsbSwiperZettle::unpairing_zettle_device($id);
                if(  !empty( $response['status'] ) ) {

	                delete_user_meta($user_id, 'usb_swiper_zettle_reader_data');
	                wp_safe_redirect(UsbSwiperZettle::get_redirection_uri());
	                exit();
                }
	        }
        }

		/**
         * Pair zettle device using code and device name.
         *
         * @since 2.3.4
         *
		 * @return void
		 * @throws Exception
		 */
        public function vt_zettle_pair_reader() {
         
	        $status = false;
	        $message = __('Nonce not verified, please try after some time.', 'usb-swiper');
	        $message_type = __("ERROR",'usb-swiper');
            
            if( !empty( $_POST['_nonce'] ) &&  wp_verify_nonce( $_POST['_nonce'],'vt-zettle-pair-reader') ) {
                
                $reader_code = !empty( $_POST['zettle_pair_reader_code'] ) ? $_POST['zettle_pair_reader_code'] : '';
                $reader_device_name = !empty( $_POST['zettle_pair_reader_device_name'] ) ? $_POST['zettle_pair_reader_device_name'] : __( 'USBSwiper Terminal', 'usb-swiper' );

                if( !empty( $reader_code ) ) {
                 
	                $response = UsbSwiperZettle::pair_reader([
                       'code' => $reader_code,
                       'device_name' => $reader_device_name,
                    ]);
                    
                    if( !empty( $response['status'] ) && 200 === (int) $response['status'] ) {
	                    $status = true;
                        $data = !empty( $response['data'] ) ? $response['data'] : [];
	                    $data['zettle_pair_reader_code'] = $reader_code;
	                    $data['zettle_pair_reader_device_name'] = $reader_device_name;
                        update_user_meta( get_current_user_id(),  'usb_swiper_zettle_reader_data', $data );
	                    $message = __("<strong>$reader_code</strong> Reader paired successfully.", 'usb-swiper');
                    } else {
	                    $message = !empty( $response['data']['developerMessage'] ) ? $response['data']['developerMessage'] : __( 'Reader Pairing Failed. Please try again.', 'usb-swiper' );
                        $message_type = !empty( $response['data']['errorType'] ) ? $response['data']['errorType'] : __( 'Error', 'usb-swiper' );
                        delete_user_meta( get_current_user_id(), 'usb_swiper_zettle_reader_data');
                    }
                }
            }
            
	        wp_send_json(
		        array(
			        'status' => $status,
			        'message' => $message,
			        'message_type' => $message_type,
		        )
	        );
        }

		/**
         * Disable vt form zettle warning.
         *
		 * @return void
         * @since 3.0.1
		 */
		public function disable_vt_form_warning() {

            $status = false;
			if( is_user_logged_in() ) {
				$status = true;
				update_user_meta( get_current_user_id(), 'usb_swiper_vt_notice', 1 );
			}

			wp_send_json([
				'status' => $status,
            ]);
		}

		/**
		 * Void authorize transaction.
		 *
		 * @since 3.0.3
		 *
		 * @param string $unique_id Get transaction data.
		 */
        public function void_authorize_transaction( $unique_id ) {

	        $data =  usb_swiper_get_unique_id_data($unique_id);
	        $transaction_url = wc_get_endpoint_url( 'transactions' ,'');

	        if( !empty( $data ) && is_array( $data) ) {

		        $post_id = !empty( $data['transaction_id'] ) ? $data['transaction_id'] : '';
		        $paypal_transaction_id = !empty( $data['paypal_transaction_id'] ) ? $data['paypal_transaction_id'] : '';
		        if( !empty( $post_id ) && $post_id > 0 ) {
			        $transaction_status = usbswiper_get_transaction_status($post_id);
			        $payment_intent = usbswiper_get_transaction_type($post_id);

			        $payment_response = get_post_meta( $post_id,'_payment_response', true);
			        $purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
			        $payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
			        $payment_authorizations = !empty( $payment_details['authorizations'][0] ) ? $payment_details['authorizations'][0] : '';
			        $payment_links = !empty( $payment_authorizations['links'] ) ? $payment_authorizations['links'] : '';

			        $log_action_name = 'void_authorized_order';
			        if( !empty( $payment_intent ) && $payment_intent === 'CAPTURE' && !empty( $transaction_status ) && $transaction_status === 'CREATED' ) {
				        $log_action_name = 'void_created_order';
				        $payment_links = !empty( $payment_response['links'] ) ? $payment_response['links'] : '';
			        }

			        if( !empty( $payment_links ) && is_array( $payment_links ) ) {

				        $void_url = '';
				        foreach ( $payment_links as $key => $value ) {

					        if( !empty( $value['rel']) && 'void' === $value['rel']) {
						        $void_url = !empty( $value['href'] ) ? $value['href'] : '';
					        }
				        }

				        $this->api_log = new Usb_Swiper_Log();

				        if( !class_exists('Usb_Swiper_Paypal_request') ) {
					        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
				        }

				        $Paypal_request = Usb_Swiper_Paypal_request::instance();

				        $args = array(
					        'method' => 'POST',
					        'timeout' => 60,
					        'redirection' => 5,
					        'httpversion' => '1.1',
					        'blocking' => true,
					        'headers' => array(
						        'Content-Type' => 'application/json',
						        'Authorization' => 'Bearer '.$Paypal_request->get_access_token(),
					        ),
                            'body' => '',
				        );

				        $this->api_response = $Paypal_request->request($void_url, $args, $log_action_name, $post_id);

				        $status_code = !empty( $Paypal_request->result ) ? (int) wp_remote_retrieve_response_code( $Paypal_request->result ) : '';

                        if( !empty( $status_code ) && 204 === $status_code )  {

	                        $Paypal_request->handle_paypal_debug_id($this->api_response, $post_id);

	                        $order_args = array(
		                        'method' => 'GET',
		                        'timeout' => 60,
		                        'redirection' => 5,
		                        'httpversion' => '1.1',
		                        'blocking' => true,
		                        'headers' => array(
			                        'Content-Type' => 'application/json',
			                        'Authorization' => 'Bearer ' . $Paypal_request->get_access_token(),
		                        ),
	                        );

	                        $response = $Paypal_request->request($Paypal_request->order_url . $paypal_transaction_id, $order_args, 'order_response', $post_id);
	                        $Paypal_request->handle_paypal_debug_id($response, $post_id);

	                        if ( !empty( $response ) ) {
		                        update_post_meta($post_id, '_payment_response', $response);
		                        update_post_meta($post_id, '_payment_status', usbswiper_get_transaction_status($post_id) );
	                        }
                        }
			        }

			        $transaction_url = esc_url( wc_get_endpoint_url( 'view-transaction', $post_id, wc_get_page_permalink( 'myaccount' ) ) );
		        }
	        }

	        wp_safe_redirect($transaction_url);
	        exit();
        }

        public function vt_search_customer() {

	        $status = false;
	        $message = __( 'Something went Wrong, please try after some time.', 'usb-swiper');
	        $message_type = __('ERROR','usb-swiper');
	        $customer_html = '';

            if( !empty( $_POST['customer'] ) ) {

	            if( !class_exists('Usb_Swiper_Customers') ) {
		            include_once USBSWIPER_PATH.'/includes/class-usb-swiper-customers.php';
	            }

	            $usb_swiper_customers = new Usb_Swiper_Customers();
	            $customer_results = $usb_swiper_customers->get_customers([
                    'customer' => esc_attr( $_POST['customer'] ),
                    'per_page' => 100,
                ]);

	            $customer_lists = !empty( $customer_results['customers'] ) ? $customer_results['customers'] : [];

                if( !empty( $customer_lists ) && is_array( $customer_lists ) ) {
	                $status = true;
                    foreach ( $customer_lists as $customer_list ) {
                        $customer_id = !empty( $customer_list['customer_id'] ) ? $customer_list['customer_id'] : 0;
                        $billing_email = !empty( $customer_list['BillingEmail'] ) ? $customer_list['BillingEmail'] : '';
                        $first_name = !empty( $customer_list['BillingFirstName'] ) ? $customer_list['BillingFirstName'] : '';
                        $last_name = !empty( $customer_list['BillingLastName'] ) ? $customer_list['BillingLastName'] : '';
                        $full_name = "{$first_name} {$last_name} ({$billing_email})";
	                    $customer_html .= "<span class='customer-item' data-customer_id='$customer_id' >$full_name</span>";
                    }
                } else {
	                $message = __( 'Customers not found.', 'usb-swiper');
                }
            }

	        $response = array(
		        'status' => $status,
		        'message' => $message,
		        'message_type' => $message_type,
		        'customer_html' => $customer_html,
	        );

	        wp_send_json( $response , 200 );
        }

        public function vt_get_customer_by_id() {

	        $status = false;
	        $message = __( 'Something went Wrong, please try after some time.', 'usb-swiper');
	        $message_type = __('ERROR','usb-swiper');
	        $customer = '';

	        if( !empty( $_POST['customer_id'] ) ) {

		        if( !class_exists('Usb_Swiper_Customers') ) {
			        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-customers.php';
		        }

		        $usb_swiper_customers = new Usb_Swiper_Customers();
		        $customer_results = $usb_swiper_customers->get_customer_by_id(esc_attr( $_POST['customer_id'] ));
                if( !empty( $customer_results ) && is_array( $customer_results ) ) {
                    $status = true;
	                $message = __('Customer data found','usb-swiper');
	                $message_type = __('SUCCESS','usb-swiper');
	                $customer = json_encode( $customer_results );
                }
	        }

	        $response = array(
		        'status' => $status,
		        'message' => $message,
		        'message_type' => $message_type,
		        'customer' => $customer,
	        );

	        wp_send_json( $response , 200 );
        }

        public function vt_customers_endpoint_cb() {

	        if( !class_exists('Usb_Swiper_Customers') ) {
		        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-customers.php';
	        }

	        $usb_swiper_customers = new Usb_Swiper_Customers();

            if( !empty( $_GET['action'] ) && in_array( $_GET['action'],['create', 'view', 'edit'])) {
	            $customer_id = !empty( $_GET['customer_id'] ) ? esc_attr( $_GET['customer_id'] ) : 0;
	            $customer = $usb_swiper_customers->get_customer_by_id($customer_id);

	            usb_swiper_get_template('wc-customer-detail.php', [
                    'action' => esc_attr( $_GET['action'] ),
                    'customer_id' => ( 'create' !== $_GET['action'] ) ? $customer_id : 0,
                    'customer_data' => ( 'create' !== $_GET['action'] ) ? $customer : '',
                ]);
            } else {

	            $customers = $usb_swiper_customers->get_customers([
		            'customer' => !empty($_GET['s']) ? $_GET['s'] : '',
		            'per_page' => 20,
		            'current_page' => !empty( $_GET['vt-page'] ) ? (int) $_GET['vt-page'] : 1,
	            ]);

	            $args = !empty( $customers ) ? $customers : [];

	            usb_swiper_get_template('wc-customers-lists.php', $args);
            }
        }

        public function vt_delete_customer_by_id() {

	        $status = false;
	        $message = __( 'Something went Wrong, please try after some time.', 'usb-swiper');
	        $message_type = __('ERROR','usb-swiper');
            if( !empty( $_POST['customer_id'] ) && $_POST['customer_id'] > 0 ) {
	            if( !class_exists('Usb_Swiper_Customers') ) {
		            include_once USBSWIPER_PATH.'/includes/class-usb-swiper-customers.php';
	            }

	            $usb_swiper_customers = new Usb_Swiper_Customers();
	            $deleted_customer = $usb_swiper_customers->delete_customer($_POST['customer_id']);
                if( !empty( $deleted_customer['status'] ) && true === $deleted_customer['status'] ) {
	                $status = true;
	                $message = !empty( $deleted_customer['message'] ) ? $deleted_customer['message'] : '';
	                $message_type = __('SUCCESS','usb-swiper');
                }
            }

	        $response = array(
		        'status' => $status,
		        'message' => $message,
		        'message_type' => $message_type,
	        );

	        wp_send_json( $response , 200 );
        }

        public function vt_handle_customer_form() {
	        $status = false;
	        $message = __('Something went wrong. Please try again.','usb-swiper');
	        $message_type = __('ERROR','usb-swiper');
	        $redirection_url = '';

	        if( !empty( $_POST['nonce'] ) && wp_verify_nonce($_POST['nonce'],'vt-customer-form') ) {

		        if( !class_exists('Usb_Swiper_Customers') ) {
			        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-customers.php';
		        }

                $action_type = !empty( $_POST['action_type'] ) ? $_POST['action_type'] : 'create';

                $customer_id = !empty( $_POST['customer_id']) ? $_POST['customer_id'] : 0;
                $customer_data = $_POST;
                unset($customer_data['customer_id']);
                unset($customer_data['action']);
                unset($customer_data['action_type']);
                unset($customer_data['nonce']);

                $customer_data['merchant_id'] = get_current_user_id();

		        $usb_swiper_customers = new Usb_Swiper_Customers();
		        $customer_data = $usb_swiper_customers->handle_customer($customer_data,$customer_id);

                if( !empty( $customer_data['status'] ) ) {

                    $status = true;
	                $message = !empty( $customer_data['message'] ) ? $customer_data['message'] : '';
	                $message_type = __('SUCCESS','usb-swiper');
	                $redirection_url = esc_url( wc_get_endpoint_url( 'vt-customers', '', wc_get_page_permalink( 'myaccount' )) );
                }
	        }

	        $response = array(
		        'status' => $status,
		        'message' => $message,
		        'message_type' => $message_type,
		        'redirection_url' => $redirection_url,
	        );

	        wp_send_json( $response , 200 );
        }

        /**
         * Check email is exists or not.
         *
         * @since 4.0.0
         *
         * @return void
         */
        public function vt_check_email_exists(){
            $email = isset($_POST['data']['BillingEmail']) ? sanitize_email($_POST['data']['BillingEmail']) : '';
            $data = !empty($_POST['data']) ?  $_POST['data'] : [];

            if (empty($email)) {
                wp_send_json_error(['message' => 'No email provided']);
            }
            $customer = new Usb_Swiper_Customers();
            $customers = $customer->get_customers(array('customer'=> $email));
            $current_user = !empty($customers['customers'][0]) ?  $customers['customers'][0] : '';

            $existing_customer = !empty($current_user);
            $response = [];
            if( !empty($data)  && !empty($current_user) ){
                foreach($data as $key => $value){
                    if( isset($current_user[$key]) ) {
                        $response[] = $key;
                        if( $current_user[$key] === $value ){
                            $response_key = array_search($key, $response);
                            unset($response[$response_key]);
                        }
                    }
                }
            }

            if (!empty($response)) {
                wp_send_json_success([
                    'email_exists' => true,
                    'customer_data' => $response
                ]);
            } else {
                wp_send_json_success(['email_exists' => false]);
            }
        }
    }
}
