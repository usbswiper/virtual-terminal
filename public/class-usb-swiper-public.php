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

			$smart_js_arg['currency'] = $this->currency;

			if ($this->is_sandbox) {
				if (is_user_logged_in() && WC()->customer && WC()->customer->get_billing_country() && 2 === strlen(WC()->customer->get_billing_country())) {
					$smart_js_arg['buyer-country'] = WC()->customer->get_billing_country();
				}
				$smart_js_arg['client-id'] = USBSWIPER_PAYPAL_SANDBOX_PARTNER_CLIENT_ID;
			} else {
				$smart_js_arg['client-id'] = USBSWIPER_PAYPAL_PARTNER_CLIENT_ID;
			}

			$get_merchant_data = get_user_meta(get_current_user_id(), '_merchant_onboarding_response', true);
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

			return $smart_js_arg;
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
		 * Register and enqueue style and script in public area.
         *
         * @since 1.0.0
		 */
		public function enqueue_scripts() {

			wp_enqueue_style('dashicons');

			$settings = usb_swiper_get_settings('general');
			$vt_page_id = !empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
			$myaccount_page_id = (int)get_option( 'woocommerce_myaccount_page_id' );
			if( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) {

                $sdk_obj = $this->get_paypal_sdk_obj();
                wp_register_script( 'usb-swiper-paypal-checkout-sdk', add_query_arg( $sdk_obj, 'https://www.paypal.com/sdk/js' ), array(), null, false );
                wp_enqueue_script( 'usb-swiper-paypal-checkout-sdk' );

				wp_enqueue_style( 'bootstrap-switch', USBSWIPER_URL . 'assets/css/bootstrap-switch.min.css' );
				wp_enqueue_script( 'bootstrap-min', USBSWIPER_URL . 'assets/js/bootstrap.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'bootstrap-switch', USBSWIPER_URL . 'assets/js/bootstrap-switch.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'pos-functions', USBSWIPER_URL . 'assets/js/pos-functions.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'validate-credit-card-number', USBSWIPER_URL . 'assets/js/validate-credit-card-number.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'parse-track-data', USBSWIPER_URL . 'assets/js/parse-track-data.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'autoNumeric', USBSWIPER_URL . 'assets/js/autoNumeric.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( 'jquery-validate', USBSWIPER_URL . 'assets/js/jquery.validate.min.js', array( 'jquery' ), $this->version, true );
				wp_enqueue_script( $this->plugin_name, USBSWIPER_URL . 'assets/js/usb-swiper.js', array( 'jquery' ), $this->version, true );

				wp_localize_script( $this->plugin_name, 'usb_swiper_settings', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'usb_swiper_transaction_nonce' => wp_create_nonce('usb_swiper_process_transaction'),
					'three_d_secure_contingency' => apply_filters('usb_swiper_three_d_secure_contingency', 'SCA_WHEN_REQUIRED'),
					'create_transaction_url' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'create_transaction', 'utm_nooverride' => '1', 'from' => 'vt_transaction' ), WC()->api_request_url( 'usb_swiper_transaction' ) ),
					'cc_capture' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'cc_capture', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'style_color' => apply_filters('usb_swiper_smart_button_style_color','gold'),
					'style_shape' => apply_filters('usb_swiper_smart_button_style_shape','rect'),
					'style_height' => apply_filters('usb_swiper_smart_button_style_height',''),
					'style_label' => apply_filters('usb_swiper_smart_button_style_label','paypal'),
					'style_layout' => apply_filters('usb_swiper_smart_button_style_layout','vertical'),
					'style_tagline' => apply_filters('usb_swiper_smart_button_style_tagline','yes'),
					'style_size' => apply_filters('usb_swiper_smart_button_style_size','responsive'),
                    'vt_page_url' => get_the_permalink($vt_page_id),
				) );
			} elseif ( $myaccount_page_id === get_the_ID() ) {

				$sdk_obj = $this->get_paypal_sdk_obj();
				wp_register_script( 'usb-swiper-paypal-checkout-sdk', add_query_arg( $sdk_obj, 'https://www.paypal.com/sdk/js' ), array(), null, false );
				wp_enqueue_script( 'usb-swiper-paypal-checkout-sdk' );

				wp_enqueue_script( $this->plugin_name, USBSWIPER_URL . 'assets/js/usb-swiper.js', array( 'jquery' ), $this->version, true );

				wp_localize_script( $this->plugin_name, 'usb_swiper_settings', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'usb_swiper_transaction_nonce' => wp_create_nonce('usb_swiper_process_transaction'),
					'three_d_secure_contingency' => apply_filters('usb_swiper_three_d_secure_contingency', 'SCA_WHEN_REQUIRED'),
					'create_transaction_url' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'create_transaction', 'utm_nooverride' => '1', 'from' => 'vt_transaction' ), WC()->api_request_url( 'usb_swiper_transaction' ) ),
					'cc_capture' => add_query_arg( array( 'usb_swiper_ppcp_action' => 'cc_capture', 'utm_nooverride' => '1' ), WC()->api_request_url('usb_swiper_transaction')),
					'style_color' => apply_filters('usb_swiper_smart_button_style_color','gold'),
					'style_shape' => apply_filters('usb_swiper_smart_button_style_shape','rect'),
					'style_height' => apply_filters('usb_swiper_smart_button_style_height',''),
					'style_label' => apply_filters('usb_swiper_smart_button_style_label','paypal'),
					'style_layout' => apply_filters('usb_swiper_smart_button_style_layout','vertical'),
					'style_tagline' => apply_filters('usb_swiper_smart_button_style_tagline','yes'),
					'style_size' => apply_filters('usb_swiper_smart_button_style_size','responsive'),
					'vt_page_url' => get_the_permalink($vt_page_id),
				) );
            }

			wp_enqueue_style( $this->plugin_name, USBSWIPER_URL . 'assets/css/usb-swiper.css' );
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

			if ('usb-swiper-paypal-checkout-sdk' === $handle) {

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

				$menu_links['transactions']    = __( 'Transactions', 'usb-swiper' );
				$menu_links['customer-logout'] = $logout;
			}

			return $menu_links;
		}

		/**
		 * Add new transaction endpoint.
         *
         * @since 1.0.0
		 */
		public function endpoint_init() {

			add_rewrite_endpoint( 'transactions', EP_PAGES );
			add_rewrite_endpoint( 'view-transaction', EP_PAGES );
		}

		/**
		 * Transactions endpoint callback method.
         *
         * @since 1.0.0
		 */
		public function transactions_endpoint_cb() {

			if( usb_swiper_allow_user_by_role('administrator')  || usb_swiper_allow_user_by_role('customer') ) {

				$current_page   = !empty( $_GET['vt-page'] ) ? $_GET['vt-page'] : 1;

				$transactions = new WP_Query( array(
					'post_type' => 'transactions',
					'post_status' => 'publish',
					'posts_per_page' => !empty( get_option( 'posts_per_page' ) ) ? get_option( 'posts_per_page' ) : 10,
					'paged' => $current_page,
					'author' => get_current_user_id(),
					'order' => 'DESC',
					'orderby' => 'date',
				) );

				$args = array(
					'transactions' => !empty( $transactions->posts ) ? $transactions->posts : '',
					'current_page'    => absint( $current_page ),
					'max_num_pages' => !empty( $transactions->max_num_pages ) ? $transactions->max_num_pages : '',
					'has_transactions'      => 0 < $transactions->have_posts(),
					'paginate'        => true,
				);

				extract( $args );

				usb_swiper_get_template('wc-transactions-lists.php', $args);
			}
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
				    usb_swiper_get_template( 'wc-transaction-history.php', array( 'transaction_id' => $transaction_id ) );
			    } else {
			        $message = __( "You can't access this transaction.",'usb-swiper');
			        echo apply_filters( 'usb_swiper_transaction_access_denied', $message);
                }
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
                'after_login_url' => !empty( $vt_page_id )? get_the_permalink($vt_page_id): site_url(),
            ), $args );

			ob_start();

			if( usb_swiper_allow_user_by_role('administrator')  || usb_swiper_allow_user_by_role('customer') ) {

				$get_merchant_data = get_user_meta(get_current_user_id(), '_merchant_onboarding_response', true);
				if( !empty( $get_merchant_data ) && is_array( $get_merchant_data )) {
				    ?>
                    <div class="vt-form-login-wrap">
                        <p><a class="vt-button" href="<?php echo !empty( $args['after_login_url'] ) ? $args['after_login_url'] : get_the_permalink($vt_page_id); ?>"><?php echo !empty( $args['after_login_label'] ) ? $args['after_login_label'] : __('Launch to Terminal','usb-swiper'); ?></a></p>
                    </div>
                    <?php

				} else {
					$Usb_Swiper_PPCP = new Usb_Swiper_PPCP();
					echo $Usb_Swiper_PPCP->connect_to_paypal_button($args);
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

			    $get_merchant_data = get_user_meta(get_current_user_id(), '_merchant_onboarding_response', true);
			    if( !empty( $get_merchant_data ) && is_array( $get_merchant_data )) {
				    usb_swiper_get_template( 'virtual-terminal-form.php', $args );
			    }
			} else {

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
			
			if( is_admin() || ( !empty($_GET['et_fb']) && '1' == $_GET['et_fb'] )) {
		        return;
		    }
			
			$settings = usb_swiper_get_settings('general');
			$vt_page_id = !empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
			$myaccount_page_id = (int)get_option( 'woocommerce_myaccount_page_id' );
			if( is_user_logged_in() ) {

			    if( ( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) || ( !empty( $myaccount_page_id ) && $myaccount_page_id === get_the_ID() ) ) {
				    $Usb_Swiper_PPCP = new Usb_Swiper_PPCP();
				    $Usb_Swiper_PPCP->handle_onboarding_user();
			    }
			}

			if( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) {

				if( isset($_REQUEST['merchantId']) && !empty( esc_attr( $_REQUEST['merchantId'] ) ) ) {

					$Usb_Swiper_PPCP = new Usb_Swiper_PPCP();
					$Usb_Swiper_PPCP->create_user();
				}

				if( isset( $_GET['_nonce'] ) && !empty( $_GET['_nonce'] ) && wp_verify_nonce( esc_attr( $_GET['_nonce'] ), 'login-with-paypal' ) && isset($_GET['ppcp'] ) && !empty( $_GET['ppcp'] ) && '1' === $_GET['ppcp'] ) {
					$merchant_user_info = usbswiper_get_onboarding_user();

					if( !empty( $merchant_user_info ) && is_array( $merchant_user_info ) && !empty( $merchant_user_info['merchant_email'] ) && is_email( $merchant_user_info['merchant_email'] )) {
						$user_info = get_user_by('email', $merchant_user_info['merchant_email']);
						if ( !empty( $user_info ) &&  isset( $user_info->ID ) && $user_info->ID > 0) {

							wc_set_customer_auth_cookie( $user_info->ID );
							wp_safe_redirect(get_the_permalink($vt_page_id));
							exit();
						}
					}
				}
			}

			if( isset( $_GET['_nonce'] ) && !empty( $_GET['_nonce'] ) && wp_verify_nonce( esc_attr( $_GET['_nonce'] ), 'disconnect-to-paypal' ) && isset($_GET['ppcp'] ) && !empty( $_GET['ppcp'] ) && '1' === $_GET['ppcp'] && !empty( $_GET['type'] ) && 'disconnect' === $_GET['type'] ) {
				delete_user_meta( get_current_user_id(),'_merchant_onboarding_response');
				delete_user_meta( get_current_user_id(),'_merchant_onboarding_user');
				delete_user_meta( get_current_user_id(),'_merchant_onboarding_tracking_response');
				//setcookie( 'merchant_onboarding_user', '', time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
				wp_safe_redirect(site_url());
				exit();
			}

			if( !empty( $_GET['action'] ) && 'capture' === $_GET['action'] && !empty( $_GET['unique_id'] ) ) {

			    $this->capture_authorize_transaction($_GET['unique_id']);
			}

			if( !is_user_logged_in()) {

				if( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) {
					wp_safe_redirect(site_url());
					exit();
				}
			} elseif ( is_user_logged_in() ) {

				if( !empty( $vt_page_id ) && $vt_page_id === get_the_ID() ) {
					$merchant_user_info = usbswiper_get_onboarding_user();
					if ( empty( $merchant_user_info ) ) {
						wp_safe_redirect( site_url() );
						exit();
					}
				}
			}
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
                        $this->create_new_transaction();
	                    break;
                    case "cc_capture":
                        $this->capture_transaction();
                        break;
                    default:
                }
            }
		}

		/**
		 * Create new transaction.
         *
         * @since 1.0.0
		 */
		public function create_new_transaction() {

			$tab_fields = usb_swiper_get_vt_tab_fields();

            $transaction = array();
			if( !empty( $tab_fields ) && is_array( $tab_fields ) ) {
			    foreach ( $tab_fields as $tab_id => $tab_field ) {
				    $form_fields = usb_swiper_get_vt_form_fields( $tab_id );
				    if( !empty( $form_fields ) && is_array( $form_fields ) ) {
				        foreach ( $form_fields as $key => $form_field ) {
					        $field_id = !empty( $form_field['id'] ) ?  $form_field['id'] : '';
					        $transaction[$field_id] = !empty( $_POST[$field_id] ) ? $_POST[$field_id] : '';
				        }
				    }
			    }
			}

			$BillingFirstName = !empty( $transaction['BillingFirstName'] ) ? $transaction['BillingFirstName'] : '';
			$BillingLastName = !empty( $transaction['BillingLastName'] ) ? $transaction['BillingLastName'] : '';

			$display_name = $BillingFirstName.' '.$BillingLastName;

			$transaction_id = wp_insert_post(array(
				'post_title'   => wp_strip_all_tags($display_name),
				'post_content' => !empty( $transaction['Notes'] ) ? esc_attr($transaction['Notes']) : '',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
				'post_type'   => 'transactions',
            ));

			if( !is_wp_error( $transaction_id ) ) {

				wp_update_post( array(
					'ID'         => $transaction_id,
					'post_title' => wp_strip_all_tags(sprintf( __( '#%s %s' ,'usb-swiper' ), $transaction_id ,$display_name)),
				) );

				usb_swiper_set_session('usb_swiper_woo_transaction_id', $transaction_id);

			    //update_post_meta($transaction_id,'wc_transaction_currency', get_woocommerce_currency());

			    if( !empty( $transaction ) && is_array( $transaction ) ) {
			        foreach ( $transaction as $key => $value ) {
			            update_post_meta( $transaction_id,$key, $value);
			        }
			    }

			    if( !class_exists('Usb_Swiper_Paypal_request') ) {
				    include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
			    }

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
				                ));
					            $Paypal_request->handle_paypal_debug_id($order_response, $transaction_id);
					            update_post_meta($transaction_id, '_payment_response', $order_response);
				            }
				        }
				    }
				    update_post_meta($transaction_id, '_paypal_transaction_id', $response['id']);
					usb_swiper_set_session('usb_swiper_woo_create_transaction_id', $response['id']);
					wp_send_json( array( 'orderID' => $response['id'] ), 200 );

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

			    $Paypal_request = Usb_Swiper_Paypal_request::instance();
			    $response = $Paypal_request->handle_cc_transaction_request($paypal_transaction_id);

			    update_post_meta($transaction_id, '_payment_response', $response);

			    $payment_status = !empty( $response['status'] ) ? $response['status'] : '';
			    update_post_meta($transaction_id, '_payment_status', $payment_status);

			    if( !empty( $response['payment_source'] )) {

			        $card_details = !empty( $response['payment_source']['card'] ) ? $response['payment_source']['card'] : '';

			        $card_number = !empty( $card_details['last_digits']) ? $card_details['last_digits'] :'';
			        $brand = !empty( $card_details['brand']) ? $card_details['brand'] :'';
			        $type = !empty( $card_details['type']) ? $card_details['type'] :'';

				    update_post_meta($transaction_id, '_payment_card_last_digits', $card_number);
				    update_post_meta($transaction_id, '_payment_card_brand', $brand);
				    update_post_meta($transaction_id, '_payment_card_type', $type);
			    }

			    if( !empty($response ) && is_array($response) && isset($response['id']) && !empty($response['id'])) {

				    update_post_meta($transaction_id, '_paypal_transaction_id', $response['id']);

			        $this->send_emails($transaction_id);

				    wp_send_json( array(
					    'result' => 'success',
					    'redirect' => esc_url( wc_get_endpoint_url( 'view-transaction', $transaction_id, wc_get_page_permalink( 'myaccount' ) ) ),
                    ), 200 );
			    } else{
			        //wp_delete_post($transaction_id);
				    wp_send_json( array(
					    'result' => 'error',
					    'message' => __('Transaction is not captured successfully.','usb_swiper'),
				    ), 200 );
			    }
		    } else{
			    $response = array( 'error' => true, 'message' => __('Transaction nonce not verified. Please try again.','usb_swiper') );
			    wp_send_json( $response, 200 );
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
				        $platform_fees = usbswiper_get_platform_fees( $order_total );
				        $body_request = array();
				        if( !empty( $platform_fees ) && $platform_fees > 0 ) {

					        if ($this->is_sandbox) {
						        $admin_merchant_id = USBSWIPER_SANDBOX_PARTNER_MERCHANT_ID;
					        } else{
						        $admin_merchant_id = USBSWIPER_PARTNER_MERCHANT_ID;
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
							        'Authorization' => 'Bearer '.$Paypal_request->get_access_token(),
						        ),
					        );

					        $response = $Paypal_request->request($Paypal_request->order_url.$paypal_transaction_id, $order_args, 'order_response', $post_id);
					        $Paypal_request->handle_paypal_debug_id($response, $post_id);
					        update_post_meta($post_id, '_payment_response', $response);
					        $payment_status = !empty( $response['status'] ) ? $response['status'] : '';
					        update_post_meta($post_id, '_payment_status', $payment_status);
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
                );

                usb_swiper_get_template( 'wc-transaction-history.php', $args );

                wc_get_template( 'emails/email-footer.php' );

		    $email_content = ob_get_contents();
		    ob_get_clean();

		    return apply_filters('usb_swiper_get_email_content', $email_content);
		}

		/**
         * Transaction Email send to admin and user.
         *
         * @since 1.0.0
         *
		 * @param $transaction_id
		 */
		public function send_emails( $transaction_id ) {

		    if( empty( $transaction_id)) {
		        return;
		    }

			if ( ! class_exists( 'WC_Email', false ) ) {
				include_once dirname( WC_PLUGIN_FILE ) . '/includes/emails/class-wc-email.php';
			}

			$WC_Email = new WC_Email();
			$get_headers = $WC_Email->get_headers();

			$transaction = get_post($transaction_id);
		    $transaction_author = !empty( $transaction->post_author ) ? $transaction->post_author : '';
		    $author_email = '';
		    if( !empty( $transaction_author ) && $transaction_author > 0 ) {
			    $user_info = get_user_by( 'id', $transaction_author );
			    $author_email = !empty( $user_info->user_email ) ? $user_info->user_email : '';
		    }

		    $BillingEmail = get_post_meta( $transaction_id,'BillingEmail', true);
			$BillingEmail = !empty( $BillingEmail ) ? $BillingEmail : $author_email;

            //send email to admin,
			$admin_email = array( get_option('admin_email'), );
			$site_title = get_option('blogname');
			$admin_subject = sprintf(__("[%s]: New Transaction #%s",'usb-swiper'),$site_title,$transaction_id);
			$admin_content = $this->get_email_content($transaction_id, array('email_heading' => sprintf(__('New Transaction: #%s','usb-swiper'), $transaction_id)));
			$admin_content = $WC_Email->format_string($admin_content);
			$admin_content = $WC_Email->style_inline($admin_content);
			wp_mail($admin_email, $admin_subject, $admin_content, $get_headers);

			//send email to user,
			$user_subject = sprintf( __("Your %s transaction has been received!",'usb-swiper'), $site_title);
			$user_content = $this->get_email_content($transaction_id, array('email_heading' => __('Thank you for your Transaction','usb-swiper')));
			$user_content = $WC_Email->format_string($user_content);
			$user_content = $WC_Email->style_inline($user_content);
			wp_mail($BillingEmail, $user_subject, $user_content, $get_headers);
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
		public function wc_edit_account_form() {
			$merchant_data = get_user_meta( get_current_user_id(),'_merchant_onboarding_response', true);

           if( empty( $merchant_data)) {
               return;
           }

			$get_countries = WC()->countries->get_countries();
		    ?>
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
				update_user_meta( $user_id, "_primary_currency", $primary_currency );
			}
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
				'html' => $refund_html,
			);

			wp_send_json( $response , 200 );
		}

		public function display_paypal_connect_button() {

		    echo '<div class="paypal-connect-button-wrap">';
		        echo do_shortcode('[usb_swiper_paypal_connect label="CONNECT WITH PAYPAL" after_login_label="Launch Virtual Terminal"]');
		    echo '</div>';
		}

		public function wc_before_edit_account_form() {

			$merchant_data = get_user_meta( get_current_user_id(),'_merchant_onboarding_response', true);

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

        public function update_order_status() {

            $status = false;

            $order_id = ! empty( $_POST['order_id'] ) ? $_POST['order_id'] : '';
            $message = ! empty( $_POST['message'] ) ? $_POST['message'] : '';

            if( !empty( $order_id ) ) {
                $transactions = get_posts( array(
                    'post_type' => 'transactions',
                    'posts_per_page' => 1,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => '_paypal_transaction_id',
                            'value' => $order_id,
                            'compare' => 'LIKE',
                        )
                    ),
                    'fields' => 'ids',
                ));

                $transaction_id = !empty( $transactions[0] ) ? $transactions[0] : '';
                if( !empty( $transaction_id ) && $transaction_id > 0 ) {
                    update_post_meta($transaction_id, '_payment_status', 'FAILED');
                    update_post_meta($transaction_id, '_payment_status_notes', $message);
                }
            }


            $response = array(
                'status' => $status,
                'message' => $message,
            );

            wp_send_json( $response);
        }
	}
}
