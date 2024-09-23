<?php

/**
 * The Usb_Swiper_PPCP class is responsible for PPCP.
 *
 * @since 1.0.0
 */
class Usb_Swiper_PPCP{

	public $is_sandbox = '';
	public $merchant_id = '';
	public $seller_merchant_id = '';
	public $api_log ='';

	public function __construct() {

		$settings = usb_swiper_get_settings('general');

		$this->settings = $settings;

		$this->is_sandbox = !empty( $settings['is_paypal_sandbox'] );

		$seller_merchant_user = usbswiper_get_onboarding_user();

		$this->merchant_id = !empty( $seller_merchant_user['merchantIdInPayPal'] ) ? $seller_merchant_user['merchantIdInPayPal'] :'' ;
		$this->seller_merchant_id = !empty( $seller_merchant_user['merchantIdInPayPal'] ) ? $seller_merchant_user['merchantIdInPayPal'] :'' ;

		$this->api_log = new Usb_Swiper_Log();
	}

    /**
     * Connect to PayPal button.
     *
     * @since 1.0.0
     *
     * @param array $attributes get all attributes.
     * @return false|string
     */
	public function connect_to_paypal_button( $attributes ) {

		ob_start();

		if( ! class_exists('Usb_Swiper_Onboarding')) {
			include_once ( USBSWIPER_PATH . 'includes/class-usb-swiper-onboarding.php');
		}

		$Usb_Swiper_Onboarding = new Usb_Swiper_Onboarding();

		$args = array( 'displayMode' => 'minibrowser', );
		$id = ( $this->is_sandbox === 'no' ) ? 'connect-to-production' : 'connect-to-sandbox';
		$label = ( $this->is_sandbox === 'no') ? __('Connect to PayPal', 'usb-swiper') : __('Connect to PayPal Sandbox', 'usb-swiper');

		if( !empty( $attributes['label'] ) ) {
		    $label = $attributes['label'];
        }

		if ( !$this->seller_merchant_id ) {
			$signup_link = $this->get_signup_link();
			if ($signup_link) {
				$url = add_query_arg($args, $signup_link);
				$Usb_Swiper_Onboarding->paypal_signup_button($url, $id, $label);
				$script_url = 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
				?>
                <script type="text/javascript">
                    document.querySelectorAll('[data-paypal-onboard-complete=onboardingCallback]').forEach((element) => {
                        element.addEventListener('click', (e) => {
                            if ('undefined' === typeof PAYPAL) {
                                e.preventDefault();
                                console.log('PayPal');
                            }
                        });
                    });
                </script>
                <script id="paypal-js" src="<?php echo esc_url($script_url); ?>"></script> <?php
			} else {
				echo __('We could not properly connect to PayPal', 'usb-swiper');
			}
		}

		$paypal_button = ob_get_contents();
		ob_get_clean();
		return $paypal_button;
	}

    /**
     * Get the signup link.
     *
     * @since 1.0.0
     *
     * @return false|mixed|void
     */
	public function get_signup_link() {

		try {
			include_once ( USBSWIPER_PATH . 'includes/class-usb-swiper-onboarding.php');
			$seller_onboarding = new Usb_Swiper_Onboarding();
			$response = $seller_onboarding->generate_signup_link();
			if (isset($response['links'])) {
				foreach ($response['links'] as $link) {
					if (isset($link['rel']) && 'action_url' === $link['rel']) {
						return isset($link['href']) ? $link['href'] : false;
					}
				}
			} else {
				return false;
			}
		} catch (Exception $ex) {

		}
	}

    /**
     * Get the onboarding status by merchant id.
     *
     * @since 1.0.0
     *
     * @param int $merchant_id get merchant id
     * @return false|mixed|string|null
     */
	public function get_onboarding_status( $merchant_id ) {

		$this->host = ( $this->is_sandbox) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
		$partner_merchant_id = ( $this->is_sandbox) ? usb_swiper_get_field_value('sandbox_merchant_id') : usb_swiper_get_field_value('merchant_id');

		try {

			if( !class_exists('Usb_Swiper_Paypal_request') ) {
				include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
			}

			$this->api_request = Usb_Swiper_Paypal_request::instance();

			$url = trailingslashit($this->host) .'v1/customer/partners/' . $partner_merchant_id . '/merchant-integrations/' . $merchant_id;

			$args = array(
				'method' => 'GET',
				'headers' => array(
					'Authorization' => 'Bearer '.$this->api_request->get_access_token(),
					'Content-Type' => 'application/json',
				),
			);

			$file_name = 'onboarding-'.date('Y-m-d');
			return $this->api_request->request($url, $args, 'seller_onboarding_status', $file_name);

		} catch (Exception $ex) {

			return false;
		}
	}

    /**
     * Create new user by email id.
     *
     * @since 1.0.0
     *
     * @param string $merchant_email get merchant email.
     * @return int|string|WP_Error
     */
	public function create_new_user_by_email( $merchant_email ) {

		$user_info = get_user_by( 'email', $merchant_email );
		$user_id = 0;
		if ( empty( $user_info ) ) {

			$username   = wc_create_new_customer_username( $merchant_email );
			$password   = wp_generate_password();

			if ( ! username_exists( $username ) ) {

				$new_customer_data = array(
					'user_login' => $username,
					'user_pass'  => $password,
					'user_email' => $merchant_email,
					'first_name' => ! empty( $response['legal_name'] ) ? $response['legal_name'] : '',
					'last_name'  => '',
					'role'       => 'customer',
				);

				$user_id = wp_insert_user( $new_customer_data );

				do_action( 'woocommerce_created_customer', $user_id, $new_customer_data, true );
			}

		} else {
			$user_id = ! empty( $user_info->ID ) ? $user_info->ID : '';
		}

		return $user_id;
	}

    /**
     * Check merchant is applicable or not.
     *
     * @since 1.0.0
     *
     * @param array $response get api response.
     * @return bool
     */
    public function is_merchant_applicable( $response ) {

        $is_applicable = false;

        $products = !empty( $response['products'] ) ? $response['products'] : '';

        if( !empty( $products )) {
            foreach ( $products as $key => $product ) {

                $name = !empty( $product['name'] ) ? $product['name'] : '';
                $vetting_status = !empty( $product['vetting_status'] ) ? $product['vetting_status'] : '';
                $capabilities = !empty( $product['capabilities'] ) ? $product['capabilities'] : '';

                if( !empty( $name ) && $name === 'PPCP_CUSTOM' && !empty( $capabilities ) && !empty( $vetting_status ) && ( $vetting_status === 'APPROVED' || $vetting_status === 'SUBSCRIBED') ) {

                    $is_applicable = true;
                }
            }
        }

        return $is_applicable;
    }

    /**
     * Create user for PayPal merchant.
     *
     * @since 1.0.0
     *
     * @return void
     */
	public function create_user() {


		if( isset( $_REQUEST['merchantIdInPayPal'] ) &&  !empty( $_REQUEST['merchantIdInPayPal'] ) ) {

			$response = $this->get_onboarding_status( esc_attr( $_REQUEST['merchantIdInPayPal'] ) );

			if ( ! empty( $response ) && ! empty( $response['merchant_id'] ) ) {

                $is_applicable = $this->is_merchant_applicable( $response );

                if( empty( $is_applicable ) ) {
                    $settings = usb_swiper_get_settings('general');
                    $failure_page_id = !empty( $settings['vt_failure_page'] ) ? (int)$settings['vt_failure_page'] : '';
                    $failure_page_url = !empty( $failure_page_id ) ? get_the_permalink($failure_page_id) : site_url();
                    wp_safe_redirect($failure_page_url);
                    exit();
                }

			    $country = '';
			    $primary_currency = '';

				$merchant_email = ! empty( $response['primary_email'] ) ? $response['primary_email'] : '';

				if( !empty( $merchant_email ) && is_email( $merchant_email ) ) {
					$user_id = 0;
					if( is_user_logged_in() ) {

						$user_id = get_current_user_id();
						$user_info = get_user_by( 'id', $user_id );
						$user_email = !empty( $user_info->user_email ) ? $user_info->user_email : '';
						$mailer = WC()->mailer()->get_emails();
                        $mailer['UsbSwiperPaypalConnectedEmail']->template_html_path = USBSWIPER_PATH . 'templates/emails/paypalconnected.php';
						$mailer['UsbSwiperPaypalConnectedEmail']->trigger( $user_id );


					}

                    else{
	                    $settings = usb_swiper_get_settings('general');
	                    $failure_page_id = !empty( $settings['vt_failure_page'] ) ? (int)$settings['vt_failure_page'] : '';
	                    $failure_page_url = !empty( $failure_page_id ) ? get_the_permalink($failure_page_id) : site_url();
	                    wp_safe_redirect($failure_page_url);
	                    exit();
					}

					if ( ! is_wp_error( $user_id ) ) {

						$get_user_info = get_user_by( 'id', $user_id );

						$user_data = base64_encode(json_encode(
							array(
								'merchant_email' => $merchant_email,
								'merchantIdInPayPal' => $_REQUEST['merchantIdInPayPal'],
								'merchantId' => $_REQUEST['merchantId'],
								'user_email' => !empty( $get_user_info->user_email ) ? $get_user_info->user_email : '',
							)
						));

                        if( !empty( $country ) ) {
	                        update_user_meta( $user_id, "billing_country", $country );
                        }

						if( !empty( $primary_currency ) ) {
							update_user_meta( $user_id, "_primary_currency", $primary_currency );
						}

						$brand_name = !empty( $response['legal_name'] ) ? $response['legal_name'] : '';
						if ( ! empty( $brand_name ) ) {
							update_user_meta( $user_id, 'brand_name', $brand_name );
						}

						wc_set_customer_auth_cookie( $user_id );
						update_user_meta( $user_id, '_merchant_onboarding_response', $response );
						update_user_meta( $user_id, '_merchant_onboarding_user', $user_data );

						$settings = usb_swiper_get_settings('general');
						$vt_page_id = !empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
						$vt_page_url = !empty( $vt_page_id ) ? get_the_permalink($vt_page_id) : site_url();
						$vt_response = array(array(
						    'type' => 'success',
						    'message' => __('You have successfully connected PayPal and you are ready to rock!','usb-swiper'),
                        ));

						set_transient('get_vt_connection_response' , maybe_serialize($vt_response), 10 );
						wp_safe_redirect($vt_page_url);
						exit();
					}
				}
			}
		}
	}

    /**
     * Handle onboarding user.
     *
     * @since 1.0.0
     *
     * @return void
     */
	public function handle_onboarding_user() {

		$tracking_response = get_user_meta( get_current_user_id(),'_merchant_onboarding_tracking_response', true);
        if( empty( $tracking_response ) ) {
	        $merchant_data = usbswiper_get_onboarding_merchant_response();
	        $tracking_id = ! empty( $merchant_data['tracking_id'] ) ? $merchant_data['tracking_id'] : '';

	        if( !empty( $tracking_id ) ) {

		        $this->host = ( $this->is_sandbox) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
		        $partner_merchant_id = ( $this->is_sandbox) ? usb_swiper_get_field_value('sandbox_merchant_id') : usb_swiper_get_field_value('merchant_id');

		        try {

			        if( !class_exists('Usb_Swiper_Paypal_request') ) {
				        include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
			        }

			        $this->api_request = Usb_Swiper_Paypal_request::instance();

			        $url = trailingslashit($this->host) .'v1/customer/partners/'.$partner_merchant_id.'/merchant-integrations?tracking_id='.$tracking_id;

			        $args = array(
				        'method' => 'GET',
				        'headers' => array(
					        'Authorization' => 'Bearer '.$this->api_request->get_access_token(),
					        'Content-Type' => 'application/json',
				        ),
			        );

			        $file_name = 'onboarding-'.date('Y-m-d' );
			        $this->api_response = $this->api_request->request( $url, $args, 'seller_onboarding_status using tracking_id', $file_name );

			        if( !empty( $this->api_response['merchant_id'] ) ) {

			            update_user_meta( get_current_user_id(),'_merchant_onboarding_tracking_response', true );
			        }

		        } catch (Exception $ex) {

		        }
	        }
        }
	}
}
