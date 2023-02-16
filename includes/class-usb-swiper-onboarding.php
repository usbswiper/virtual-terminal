<?php

class Usb_Swiper_Onboarding{

	public $is_sandbox = '';
	public $merchant_id = '';
	public $seller_merchant_id = '';
	public $api_log ='';

	public function __construct() {

		$settings = usb_swiper_get_settings('general');

		$this->is_sandbox = !empty( $settings['is_paypal_sandbox'] );

		$seller_merchant_user = usbswiper_get_onboarding_user();

		$this->merchant_id = !empty( $seller_merchant_user['merchantIdInPayPal'] ) ? $seller_merchant_user['merchantIdInPayPal'] :'' ;
		$this->seller_merchant_id = !empty( $seller_merchant_user['merchantIdInPayPal'] ) ? $seller_merchant_user['merchantIdInPayPal'] :'' ;

		if( !class_exists('Usb_Swiper_Paypal_request') ) {
			include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
		}

		include_once ( USBSWIPER_PATH . 'includes/class-usb-swiper-dcc-validate.php');
		$this->dcc_applies = Usb_Swiper_Dcc_validate::instance();
		$this->api_request = Usb_Swiper_Paypal_request::instance();
		$this->api_log = new Usb_Swiper_Log();
	}

	private function default_data() {

		$is_sandbox = ($this->is_sandbox) ? 'yes' : 'no';

		$settings = usb_swiper_get_settings('general');
		$vt_page_id = !empty($settings['virtual_terminal_page']) ? (int)$settings['virtual_terminal_page'] : '';
		$return_url = !empty( $vt_page_id ) ? get_the_permalink($vt_page_id) : site_url();

		$tracking_id = usb_swiper_key_generator();

        $partner_logo_url = usb_swiper_get_field_value('paypal_partner_logo_url');

		return array(
			'testmode' => $is_sandbox,
			'tracking_id' => $tracking_id,
			'partner_config_override' => array(
				'partner_logo_url' => !empty( $partner_logo_url ) ? $partner_logo_url : USBSWIPER_PAYPAL_PARTNER_LOGO,
				'return_url' => apply_filters( 'usb_swiper_get_return_url', $return_url),
				'return_url_description' => __( 'Return to your shop.', 'usb-swiper' ),
				'show_add_credit_card' => true,
			),
			'legal_consents' => array(
				array(
					'type' => 'SHARE_DATA_CONSENT',
					'granted' => true,
				),
			),
			'operations' => array(
				array(
					'operation' => 'API_INTEGRATION',
					'api_integration_preference' => array(
						'rest_api_integration' => array(
							'integration_method' => 'PAYPAL',
							'integration_type' => 'THIRD_PARTY',
							'third_party_details' => array(
								'features' => array(
									'PAYMENT',
									'FUTURE_PAYMENT',
									'REFUND',
									'ADVANCED_TRANSACTIONS_SEARCH',
									'ACCESS_MERCHANT_INFORMATION',
									'PARTNER_FEE'
								),
							),
						),
					),
				),
			),
			'products' => array(
				$this->dcc_applies->for_country_currency() ? 'PPCP' : 'EXPRESS_CHECKOUT',
			)
        );
	}

	public function generate_signup_link() {

		$body = self::default_data();

		if( $this->is_sandbox) {
			$host_url = 'https://api-m.sandbox.paypal.com/v2/customer/partner-referrals';
		} else{
			$host_url = 'https://api-m.paypal.com/v2/customer/partner-referrals';
        }

		$args = array(
			'method' => 'POST',
			'body' => wp_json_encode($body),
			'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->api_request->get_access_token()
            ),
		);

		return $this->api_request->request($host_url, $args, 'signup-link' );
	}

	public function paypal_signup_button( $url, $id, $label ) {
		?>
		<a target="_blank" class="vt-button" id="<?php echo esc_attr($id); ?>" data-paypal-onboard-complete="onboardingCallback" href="<?php echo esc_url($url); ?>" data-paypal-button="true"><?php echo esc_html($label); ?></a>
		<?php
	}
}
