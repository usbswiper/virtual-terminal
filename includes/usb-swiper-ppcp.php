<?php

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
                                alert('PayPal');
                            }
                        });
                    });</script>
				<script id="paypal-js" src="<?php echo esc_url($script_url); ?>"></script> <?php
			} else {
				echo __('We could not properly connect to PayPal', 'usb-swiper');
			}
		}

		$paypal_button = ob_get_contents();
		ob_get_clean();
		return $paypal_button;
	}

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

	public function get_onboarding_status( $merchant_id ) {

		$this->host = ( $this->is_sandbox) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
		$partner_merchant_id = ( $this->is_sandbox) ? USBSWIPER_SNADBOX_PARTNER_MERCHANT_ID : USBSWIPER_PARTNER_MERCHANT_ID;

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

	public function create_user() {

		if( isset( $_REQUEST['merchantIdInPayPal'] ) &&  !empty( $_REQUEST['merchantIdInPayPal'] ) ) {

			$response = $this->get_onboarding_status( esc_attr( $_REQUEST['merchantIdInPayPal'] ) );

			if ( ! empty( $response ) && ! empty( $response['merchant_id'] ) ) {

			    $country = '';
			    $primary_currency = '';

				$merchant_email = ! empty( $response['primary_email'] ) ? $response['primary_email'] : '';

				if( !empty( $merchant_email ) && is_email( $merchant_email ) ) {
					$user_id = 0;
					if( is_user_logged_in() ) {

						$user_id = get_current_user_id();
						$user_info = get_user_by( 'id', $user_id );
						$user_email = !empty( $user_info->user_email ) ? $user_info->user_email : '';
						if( !empty( $user_email ) && $user_email != $merchant_email ) {
							$user_id = $this->create_new_user_by_email($merchant_email);
							$country = !empty( $response['country'] ) ? $response['country'] : '';
							$primary_currency = !empty( $response['primary_currency'] ) ? $response['primary_currency'] : '';
						}

					} else {

						$user_id = $this->create_new_user_by_email($merchant_email);
						$country = !empty( $response['country'] ) ? $response['country'] : '';
						$primary_currency = !empty( $response['primary_currency'] ) ? $response['primary_currency'] : '';
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

						//setcookie( 'merchant_onboarding_user', $user_data, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

                        if( !empty( $country ) ) {
	                        update_user_meta( $user_id, "billing_country", $country );
                        }

						if( !empty( $primary_currency ) ) {
							update_user_meta( $user_id, "_primary_currency", $primary_currency );
						}

						wc_set_customer_auth_cookie( $user_id );
						update_user_meta( $user_id, '_merchant_onboarding_response', $response );
						update_user_meta( $user_id, '_merchant_onboarding_user', $user_data );


						$settings = usb_swiper_get_settings('general');
						$vt_page_id = !empty( $settings['virtual_terminal_page'] ) ? (int)$settings['virtual_terminal_page'] : '';
						$vt_page_url = !empty( $vt_page_id ) ? get_the_permalink() : site_url();
						wp_safe_redirect($vt_page_url);
						exit();
					}
				}
			}
		}
	}
}