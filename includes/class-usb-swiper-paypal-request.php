<?php

defined('ABSPATH') || exit;

/**
 * The Usb_Swiper_Paypal_request class is responsible for all PayPal request.
 *
 * @since 1.0.0
 */
class Usb_Swiper_Paypal_request{

	public $is_sandbox = '';
	public $merchant_id = '';
	public $seller_merchant_id = '';
	public $api_log ='';
	public $generate_token_url ='';
	protected static $_instance = null;

    /**
     * Create the self instance of the class.
     *
     * @since 1.1.17
     *
     * @return Usb_Swiper_Paypal_request|null
     */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.1.17
     */
	public function __construct() {

		$settings = usb_swiper_get_settings('general');
		$this->settings = $settings;
		$this->is_sandbox = !empty( $settings['is_paypal_sandbox'] );

        $brand_name = !empty( get_bloginfo('name') ) ? get_bloginfo('name') : "";
		$user_brand = get_user_meta(get_current_user_id(),'brand_name', true);
        if( !empty( $user_brand ) ) {
			$brand_name = $user_brand;
		}

		$this->brand_name = apply_filters( 'usb_swiper_brand_name',  $brand_name);

		$this->landing_page = apply_filters( 'usb_swiper_landing_page', 'NO_PREFERENCE');
		$this->advanced_card_payments = apply_filters( 'usb_swiper_advanced_card_payments', 'yes');
		$this->enable_checkout_button = apply_filters( 'usb_swiper_enable_checkout_button', 'yes');
		$this->payee_preferred = 'yes' === apply_filters( 'usb_swiper_payee_preferred', 'no');
		$this->soft_descriptor = 'yes' === apply_filters( 'usb_swiper_soft_descriptor', '$brand_name');

		if( $this->is_sandbox ) {
			$this->token_url = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
			$this->order_url = 'https://api-m.sandbox.paypal.com/v2/checkout/orders/';
			$this->paypal_order_api = 'https://api-m.sandbox.paypal.com/v2/checkout/orders/';
			$this->paypal_refund_api = 'https://api-m.sandbox.paypal.com/v2/payments/captures/';
			$this->auth = 'https://api-m.sandbox.paypal.com/v2/payments/authorizations/';
			$this->generate_token_url = 'https://api-m.sandbox.paypal.com/v1/identity/generate-token';
			$this->partner_client_id = usb_swiper_get_field_value('sandbox_client_id');
			$this->partner_client_secret = usb_swiper_get_field_value('sandbox_client_secret');
			$this->attribution_id = usb_swiper_get_field_value('sandbox_attribution_id');
		} else{
			$this->token_url = 'https://api-m.paypal.com/v1/oauth2/token';
			$this->order_url = 'https://api-m.paypal.com/v2/checkout/orders/';
			$this->paypal_order_api = 'https://api-m.paypal.com/v2/checkout/orders/';
			$this->paypal_refund_api = 'https://api-m.paypal.com/v2/payments/captures/';
			$this->auth = 'https://api-m.paypal.com/v2/payments/authorizations/';
			$this->generate_token_url = 'https://api-m.paypal.com/v1/identity/generate-token';
			$this->partner_client_id = usb_swiper_get_field_value('client_id');
			$this->partner_client_secret = usb_swiper_get_field_value('client_secret');
			$this->attribution_id = usb_swiper_get_field_value('attribution_id');
		}

		$seller_merchant_user = usbswiper_get_onboarding_user();
		$this->merchant_id = !empty( $seller_merchant_user['merchantIdInPayPal'] ) ? $seller_merchant_user['merchantIdInPayPal'] :'' ;
		$this->seller_merchant_id = !empty( $seller_merchant_user['merchantIdInPayPal'] ) ? $seller_merchant_user['merchantIdInPayPal'] :'' ;

		$this->api_log = new Usb_Swiper_Log();
	}

    /**
     * Generate the request id.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function generate_request_id() {
		static $pid = -1;
		static $addr = -1;

		if ($pid == -1) {
			$pid = uniqid('usb-swiper-request', true);
		}

		if ($addr == -1) {
			if (array_key_exists('SERVER_ADDR', $_SERVER)) {
				$addr = ip2long($_SERVER['SERVER_ADDR']);
			} else {
				$addr = php_uname('n');
			}
		}

		return $addr . $pid . $_SERVER['REQUEST_TIME'] . mt_rand(0, 0xffff);
	}

    /**
     * Get the paypal authentication assertion.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_paypal_auth_assertion() {
		$temp = array( "alg" => "none" );
		$returnData = base64_encode(json_encode($temp)) . '.';
		$temp = array(
			"iss" => $this->partner_client_id,
			"payer_id" => $this->merchant_id
		);
		$returnData .= base64_encode(json_encode($temp)) . '.';
		return $returnData;
	}

    /**
     * Get the generated token.
     *
     * @since 1.0.0
     *
     * @return mixed|string|void
     */
	public function get_generate_token() {
		try {
			$args = array(
				'method' => 'POST',
				'timeout' => 60,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$this->get_access_token(),
				),
				'cookies' => array(),
			);
			$response = $this->request($this->generate_token_url, $args, 'get client token');
			if (!empty($response['client_token'])) {
				return $response['client_token'];
			}
		} catch (Exception $ex) {

		}
	}

    /**
     * Get the access token.
     *
     * @since 1.0.0
     *
     * @return mixed|string
     */
	public function get_access_token() {

		$args = array(
			'method' => 'POST',
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => 'Basic '.base64_encode($this->partner_client_id.':'.$this->partner_client_secret)
			),
			'body' => http_build_query(array(
				'grant_type' => 'client_credentials',
			)),
		);

		$response = $this->request($this->token_url, $args, 'get access token');

		return !empty( $response['access_token'] ) ? $response['access_token'] : '';
	}

    /**
     * Trigger the api request.
     *
     * @since 1.0.0
     *
     * @param string $url get request url
     * @param array $args get all arguments
     * @param string $action_name get action name
     * @param string $log_file get log file name
     * @return mixed|string|void|null
     */
	public function request( $url, $args, $action_name = 'default', $log_file = '' ) {

		try {

			$args['timeout'] = '60';
			$args['user-agent'] = USBSWIPER_PLUGIN_NAME;

			$args['headers']['PayPal-Partner-Attribution-Id'] = $this->attribution_id;

			$this->result = wp_remote_get($url, $args);

			return $this->parse_response($this->result, $url, $args, $action_name, $log_file);

		} catch (Exception $ex) {

		}
	}

    /**
     * Parse the api response and add log.
     *
     * @since 1.0.0
     *
     * @param array $paypal_api_response get paypal api response
     * @param string $url get url.
     * @param array $request get api request
     * @param string $action_name get action name
     * @param string $log_file get log file
     * @return mixed|string|void
     */
	public function parse_response($paypal_api_response, $url, $request, $action_name, $log_file= '') {

		try {
			if ( is_wp_error($paypal_api_response) ) {
				$response = array(
					'status' => 'failed',
					'body' => array(
						'error_message' => $paypal_api_response->get_error_message(),
						'error_code' => $paypal_api_response->get_error_code()
					),
				);
			} else {

				$body = wp_remote_retrieve_body($paypal_api_response);

				$status_code = (int) wp_remote_retrieve_response_code($paypal_api_response);
				$headers = wp_remote_retrieve_headers($paypal_api_response);

				$response = !empty($body) ? json_decode($body, true) : '';
				$response = isset($response['body']) ? $response['body'] : $response;

				if (strpos($url, 'paypal.com') !== false) {
					do_action('usb_swiper_request_respose_data', $request, $response, $action_name);
				}

				$this->api_log->log("Action: ".ucwords(str_replace('_', ' ', $action_name)), $log_file);
				$this->api_log->log('Request URL: '.$url, $log_file);
				if ( !empty($request['body']) && is_array($request['body']) ) {
					$this->api_log->log( 'Request Body: ' . print_r( $request, true ), $log_file );
				} elseif ( !empty($request['body']) && is_string($request['body']) ) {
					$this->api_log->log( 'Request Body: ' . print_r(json_decode($request['body'], true), true), $log_file);
				}

				$this->api_log->log('Response headers: '.print_r($headers, true), $log_file);
				$this->api_log->log('Response Code: '.$status_code, $log_file);
				$this->api_log->log('Response Message: '.wp_remote_retrieve_response_message($paypal_api_response), $log_file);
				if ( !empty( $response['body']) && is_array($response['body'])) {
					$this->api_log->log('Response Body: ' . print_r($response['body'], true), $log_file);
				} elseif ( !empty($response) && is_array($response)) {
					$this->api_log->log('Response Body: ' . print_r($response, true), $log_file);
				} else {
					$this->api_log->log('Response Body: ' . print_r(json_decode(wp_remote_retrieve_body($response), true), true), $log_file);
				}

				return $response;
			}
		} catch (Exception $ex) {

		}
	}

    /**
     * Handle PayPal debug id.
     *
     * @since 1.0.0
     *
     * @param array $response get response array
     * @param int $transaction_id get transaction id
     * @return void
     */
	public function handle_paypal_debug_id( $response, $transaction_id ) {

		if(empty( $transaction_id ) ) {
			return;
		}

		$debug_id = !empty( $response['debug_id'] ) ? $response['debug_id'] : '';
		if( !empty( $debug_id ) && $transaction_id > 0 ) {
			update_post_meta( $transaction_id, '_paypal_transaction_debug_id', $debug_id );
		}
	}

    /**
     * Get transaction currency.
     *
     * @since 1.0.0
     *
     * @param int $transaction_id get transaction id
     * @return mixed|string
     */
	public function get_transaction_currency( $transaction_id ){

		$currency_code = get_woocommerce_currency();
		if( !empty( $transaction_id ) && $transaction_id > 0 ) {

			$transaction_currency = get_post_meta($transaction_id,'TransactionCurrency', true);
			if( !empty( $transaction_currency ) ) {
				$currency_code = $transaction_currency;
			}
		}

		return $currency_code;
	}

    /**
     * Get the shipping preferences.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function shipping_preference() {

		$transaction_id = usb_swiper_get_session('usb_swiper_woo_transaction_id');

		$shipping_preference = 'NO_SHIPPING';

		if( !empty( $transaction_id ) && $transaction_id > 0 ) {

			$shippingDisabled = get_post_meta( $transaction_id, 'shippingDisabled', true ) ;
			if( empty( $shippingDisabled) ) {
				$shipping_preference = 'SET_PROVIDED_ADDRESS';
			}
		}

		return $shipping_preference;
	}

    /**
     * Get the application context.
     *
     * @since 1.0.0
     *
     * @return array
     */
	public function application_context() {

		$application_context = array(
			'brand_name' => usbswiper_get_brand_name(),
            'brand_logo' => usbswiper_get_brand_logo(),
			'locale' => usbswiper_get_locale(),
			'landing_page' => $this->landing_page,
			'shipping_preference' => $this->shipping_preference(),
			'user_action' => 'PAY_NOW',
			'return_url' => '',
			'cancel_url' => ''
		);

		if ($this->enable_checkout_button === false && $this->advanced_card_payments === false) {
			$application_context['return_url'] = add_query_arg(array('angelleye_ppcp_action' => 'regular_capture', 'utm_nooverride' => '1'), WC()->api_request_url('AngellEYE_PayPal_PPCP_Front_Action'));
			$application_context['cancel_url'] = add_query_arg(array('angelleye_ppcp_action' => 'regular_cancel', 'utm_nooverride' => '1'), WC()->api_request_url('AngellEYE_PayPal_PPCP_Front_Action'));
		}

		return $application_context;
	}

    /**
     * Create the transaction request.
     *
     * @since 1.0.0
     *
     * @param int $transaction_id get transaction id
     * @return mixed|string|null
     */
	public function create_transaction_request( $transaction_id, $is_args = false ) {

        $InvoiceID = get_post_meta( $transaction_id,'InvoiceID', true);
        $reference_id = 'vt_transaction_'.$transaction_id;

        $payment_action = get_post_meta( $transaction_id,'TransactionType', true);

        $intent = ($payment_action === 'authorize') ? 'AUTHORIZE' : 'CAPTURE';
        $order_total = get_post_meta($transaction_id,'GrandTotal',true);

        update_post_meta( $transaction_id,'_payment_action', $payment_action);
        update_post_meta( $transaction_id,'_environment', ($this->is_sandbox) ? 'sandbox' : 'live');

        $body_request = array(
            'intent' => $intent,
            'application_context' => $this->application_context(),
            'payment_method' => array('payee_preferred' => ($this->payee_preferred) ? 'IMMEDIATE_PAYMENT_REQUIRED' : 'UNRESTRICTED'),
            'purchase_units' => array(
                0 =>
                    array(
                        'reference_id' => $reference_id,
                        'amount' =>
                            array(
                                'currency_code' => $this->get_transaction_currency($transaction_id),
                                'value' => usb_swiper_price_formatter($order_total),
                                'breakdown' => array()
                            )
                    ),
            ),
        );

        if( !empty( $InvoiceID ) ) {
            $body_request['purchase_units'][0]['invoice_id'] = usbswiper_create_invoice_prefix($transaction_id, $InvoiceID);
        }

        $body_request['purchase_units'][0]['custom_id'] = $reference_id;

        $body_request['purchase_units'][0]['soft_descriptor'] = $this->soft_descriptor;

        $transaction_type = get_post_meta($transaction_id,'_transaction_type', true);

        $platform_fees = usbswiper_get_platform_fees( $order_total, strtolower($transaction_type),$transaction_id );
        if( !empty( $platform_fees ) && 'capture' == $payment_action ) {

			if ($this->is_sandbox) {
				$admin_merchant_id = usb_swiper_get_field_value('sandbox_merchant_id');
			} else {
				$admin_merchant_id = usb_swiper_get_field_value('merchant_id');
			}

            $body_request['purchase_units'][0]['payment_instruction'] =array(
                'disbursement_mode' => 'INSTANT',
                'platform_fees' => array(
                    array(
                        'amount' => array(
                            "currency_code" => $this->get_transaction_currency($transaction_id),
                            "value" => usb_swiper_price_formatter($platform_fees)
                        ),
                        'payee' => array(
                            //'email_address' => $primary_email,
                            'merchant_id' => $admin_merchant_id,
                        ),
                    )
                ),
            );
        }

        $NetAmount = get_post_meta( $transaction_id,'NetAmount', true);

        if (isset($NetAmount) && $NetAmount > 0) {
            $body_request['purchase_units'][0]['amount']['breakdown']['item_total'] = array(
                'currency_code' => $this->get_transaction_currency($transaction_id),
                'value' => usb_swiper_price_formatter($NetAmount),
            );
        }

        $ShippingAmount = get_post_meta( $transaction_id,'ShippingAmount', true);

        if (isset($ShippingAmount) && $ShippingAmount > 0) {
            $body_request['purchase_units'][0]['amount']['breakdown']['shipping'] = array(
                'currency_code' => $this->get_transaction_currency($transaction_id),
                'value' => usb_swiper_price_formatter($ShippingAmount),
            );
        }

        $HandlingAmount = get_post_meta( $transaction_id,'HandlingAmount', true);

        if (isset($HandlingAmount) && $HandlingAmount > 0) {
            $body_request['purchase_units'][0]['amount']['breakdown']['handling'] = array(
                'currency_code' => $this->get_transaction_currency($transaction_id),
                'value' => usb_swiper_price_formatter($HandlingAmount),
            );
        }

        $TaxAmount = get_post_meta( $transaction_id,'TaxAmount', true);

		$transaction_user = $transaction_id ? get_post_field( 'post_author', $transaction_id ) : 0;
        $get_merchant_data = usbswiper_get_onboarding_merchant_response($transaction_user);
        $merchant_id = !empty( $get_merchant_data['merchant_id'] ) ? $get_merchant_data['merchant_id'] : '';

        if (isset($TaxAmount) && $TaxAmount > 0) {
            $body_request['purchase_units'][0]['amount']['breakdown']['tax_total'] = array(
                'currency_code' => $this->get_transaction_currency($transaction_id),
                'value' => usb_swiper_price_formatter($TaxAmount),
            );
        }

        $body_request['purchase_units'][0]['payee']['merchant_id'] = $merchant_id;

        $Notes = get_post_meta( $transaction_id,'Notes', true);
        if( !empty( $Notes ) && strlen( $Notes ) > 127 ) {
            $Notes = substr($Notes, 0, 127);
        }

        if( !empty( $Notes ) ) {
            $body_request['purchase_units'][0]['description'] = html_entity_decode( $Notes, ENT_NOQUOTES, 'UTF-8' );
        }

        $vt_products = get_post_meta( $transaction_id, 'vt_products', true );
        $vt_products = !empty($vt_products) ? $vt_products : array();

        if( ! empty( $vt_products ) && is_array( $vt_products ) ) {

            $purchase_units_items = array();

            foreach ( $vt_products as $products ) {
                $purchase_units_items[] =  array(
                    'name'        => $products['product_name'],
                    'description' => '',
                    'sku'         => '',
                    'category'    => '',
                    'quantity'    => $products['product_quantity'],
                    'unit_amount' => array(
                        'currency_code' => $this->get_transaction_currency( $transaction_id ),
                        'value'         => usb_swiper_price_formatter ( $products['product_price'] ),
                    ),
                );
            }

            $body_request['purchase_units'][0]['items'] = $purchase_units_items;
        }

        $body_request = $this->set_payer_shipping_details($body_request, $transaction_id);
        $body_request = $this->set_payer_details($body_request, $transaction_id);
        $body_request = $this->remove_empty_key($body_request);

        if( $is_args ) {
            return json_encode($body_request);
        }

        $args = array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->get_access_token(),
            ),
            'body' => json_encode($body_request),
        );

        $this->api_response = $this->request($this->paypal_order_api, $args, 'create_order', $transaction_id );

        $this->handle_paypal_debug_id($this->api_response, $transaction_id);

        return $this->api_response;
    }

    /**
     * Handle the cc transaction request.
     *
     * @since 1.0.0
     *
     * @param int $paypal_transaction_id get paypal transaction id
     * @return mixed|string|null
     */
	public function handle_cc_transaction_request( $paypal_transaction_id ) {

		$transaction_id = usb_swiper_get_session('usb_swiper_woo_transaction_id');
		$payment_action = get_post_meta( $transaction_id,'TransactionType', true);
		$payment_action = !empty( $payment_action ) ? $payment_action : 'capture';

		if ($payment_action === 'capture') {

			$args = array(
				'method' => 'POST',
				'timeout' => 60,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$this->get_access_token(),
				),
			);

			$this->api_response = $this->request($this->paypal_order_api . $paypal_transaction_id . '/capture', $args, 'capture_order', $transaction_id);
			$this->handle_paypal_debug_id($this->api_response, $transaction_id);
		} else {

			$args = array(
				'method' => 'POST',
				'timeout' => 60,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$this->get_access_token(),
				),
			);

			$this->api_response = $this->request($this->paypal_order_api . $paypal_transaction_id . '/authorize', $args, 'authorize_order', $transaction_id);
			$this->handle_paypal_debug_id($this->api_response, $transaction_id);
		}

		update_post_meta( $transaction_id,'_payment_action', $payment_action);
		update_post_meta( $transaction_id,'_environment', ($this->is_sandbox) ? 'sandbox' : 'live');

		return $this->api_response;
	}

    /**
     * Set the payer shipping details in transaction meta.
     *
     * @since 1.0.0
     *
     * @param array $body_request get transaction request body
     * @param int $transaction_id get transaction id
     * @return array
     */
	public function set_payer_shipping_details( $body_request, $transaction_id ) {

		$shippingDisabled = get_post_meta( $transaction_id,'shippingDisabled', true);

		if( $shippingDisabled !== 'true') {

			$shippingSameAsBilling = get_post_meta( $transaction_id,'shippingSameAsBilling', true);

			$shipping_first_name = get_post_meta( $transaction_id,'ShippingFirstName', true);
			$shipping_last_name = get_post_meta( $transaction_id,'ShippingLastName', true);
			$shipping_address_1 = get_post_meta( $transaction_id,'ShippingStreet', true);
			$shipping_address_2 = get_post_meta( $transaction_id,'ShippingStreet2', true);
			$shipping_city = get_post_meta( $transaction_id,'ShippingCity', true);
			$shipping_state = get_post_meta( $transaction_id,'ShippingState', true);
			$shipping_postcode = get_post_meta( $transaction_id,'ShippingPostalCode', true);
			$shipping_country = get_post_meta( $transaction_id,'ShippingCountryCode', true);

			if( $shippingSameAsBilling === 'true' ) {

				$shipping_first_name = get_post_meta( $transaction_id,'BillingFirstName', true);
				$shipping_last_name = get_post_meta( $transaction_id,'BillingLastName', true);
				$shipping_address_1 = get_post_meta( $transaction_id,'BillingStreet', true);
				$shipping_address_2 = get_post_meta( $transaction_id,'BillingStreet2', true);
				$shipping_city = get_post_meta( $transaction_id,'BillingCity', true);
				$shipping_state = get_post_meta( $transaction_id,'BillingState', true);
				$shipping_postcode = get_post_meta( $transaction_id,'BillingPostalCode', true);
				$shipping_country = get_post_meta( $transaction_id,'BillingCountryCode', true);
			}

			$body_request['purchase_units'][0]['shipping']['name']['full_name'] = $shipping_first_name . ' ' . $shipping_last_name;

			$body_request['purchase_units'][0]['shipping']['address'] = array(
				'address_line_1' => $shipping_address_1,
				'address_line_2' => $shipping_address_2,
				'admin_area_2' => $shipping_city,
				'admin_area_1' => $shipping_state,
				'postal_code' => $shipping_postcode,
				'country_code' => $shipping_country,
			);
		}

		return $body_request;
	}

    /**
     * Set the payer details in transaction meta.
     *
     * @since 1.0.0
     *
     * @param array $body_request get transaction request body
     * @param int $transaction_id get transaction id
     * @return array
     */
	public function set_payer_details( $body_request, $transaction_id ) {

		$first_name = get_post_meta( $transaction_id,'BillingFirstName', true);
		$last_name = get_post_meta( $transaction_id,'BillingLastName', true);
		$email_address = get_post_meta( $transaction_id,'BillingEmail', true);
		$billing_phone = get_post_meta( $transaction_id,'BillingPhoneNumber', true);
		$address_1 = get_post_meta( $transaction_id,'BillingStreet', true);
		$address_2 = get_post_meta( $transaction_id,'BillingStreet2', true);
		$city = get_post_meta( $transaction_id,'BillingCity', true);
		$state = get_post_meta( $transaction_id,'BillingState', true);
		$postcode = get_post_meta( $transaction_id,'BillingPostalCode', true);
		$country = get_post_meta( $transaction_id,'BillingCountryCode', true);

		if (!empty($first_name)) {
			$body_request['payer']['name']['given_name'] = $first_name;
		}
		if (!empty($last_name)) {
			$body_request['payer']['name']['surname'] = $last_name;
		}
		if (!empty($email_address)) {
			$body_request['payer']['email_address'] = $email_address;
		}
		if (!empty($billing_phone)) {
			$body_request['payer']['phone']['phone_type'] = 'HOME';
			$body_request['payer']['phone']['phone_number']['national_number'] = preg_replace('/[^0-9]/', '', $billing_phone);
		}

		if (!empty($address_1) && !empty($city) && !empty($state) && !empty($postcode) && !empty($country)) {
			$body_request['payer']['address'] = array(
				'address_line_1' => $address_1,
				'address_line_2' => $address_2,
				'admin_area_2' => $city,
				'admin_area_1' => $state,
				'postal_code' => $postcode,
				'country_code' => $country,
			);
		}

		return $body_request;
	}

    /**
     * Remove the empty key of array.
     *
     * @since 1.0.0
     *
     * @param array $data get the data array
     * @return mixed
     */
	public function remove_empty_key( $data ) {
		$original = $data;
		$data = array_filter($data);
		$data = array_map(function ($e) {
			return is_array($e) ? self::remove_empty_key($e) : $e;
		}, $data);
		return $original === $data ? $data : self::remove_empty_key($data);
	}

    /**
     * Get decimal digits.
     *
     * @since 1.0.0
     *
     * @param int $transaction_id get transaction id
     * @return int
     */
	public function get_decimal_digits( $transaction_id ) {
		$currency_code = $this->get_transaction_currency( $transaction_id );

		$decimal_digits = 2;
		if( in_array($currency_code, array('HUF', 'JPY', 'TWD')) ) {
			$decimal_digits = 0;
		}

		return $decimal_digits;
	}

    /**
     * Create the refund request.
     *
     * @since 1.0.0
     *
     * @param string $request_url get request url
     * @param array $args get all arguments.
     * @return mixed|string|null
     */
	public function refund_request( $request_url, $args ) {

		$transaction_id = !empty( $args['transaction_id'] ) ? $args['transaction_id'] : '';
		$refund_amount = !empty( $args['refund_amount'] ) ? $args['refund_amount'] : '';
		$paypal_transaction_id = !empty( $args['paypal_transaction_id'] ) ? $args['paypal_transaction_id'] : '';

		$get_decimal_digits = $this->get_decimal_digits( $transaction_id );

		$body_request = array(
			'note_to_payer' => 'Refund',
		);

		if (!empty($refund_amount) && $refund_amount > 0) {
			$body_request['amount'] = array(
				'value' => usbswiper_round_amount($refund_amount, $get_decimal_digits),
				'currency_code' => $this->get_transaction_currency( $transaction_id )
			);
		}

		$refun_args = array(
			'method' => 'POST',
			'timeout' => 60,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer '.$this->get_access_token(),
				'Prefer' => 'return=representation',
				'PayPal-Request-Id' => $this->generate_request_id(),
				'PayPal-Auth-Assertion' => $this->get_paypal_auth_assertion(),
			),
			'body' => json_encode($body_request),
		);

		$this->api_response = $this->request($request_url, $refun_args, 'order_refund', $transaction_id);
		$this->handle_paypal_debug_id($this->api_response, $transaction_id);
		if( !empty( $this->api_response['id'] ) ) {

			$order_args = array(
				'method' => 'GET',
				'timeout' => 60,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer '.$this->get_access_token(),
				),
			);

			$response = $this->request($this->order_url.$paypal_transaction_id, $order_args, 'order_response', $transaction_id);
			$this->handle_paypal_debug_id($response, $transaction_id);
            if( !empty( $response ) ) {
                update_post_meta($transaction_id, '_payment_response', $response);
                update_post_meta($transaction_id, '_payment_status', usbswiper_get_transaction_status($transaction_id) );
            }
			return $response;
		}

		return $this->api_response;
	}

    /**
     * Get the refund html.
     *
     * @since 1.0.0
     *
     * @param int $transaction_id get transaction id
     * @return false|string
     */
	public function get_refund_html( $transaction_id ) {

		$refund_html = '';

		if( !empty( $transaction_id ) && $transaction_id > 0 ) {

			$payment_response = get_post_meta( $transaction_id, '_payment_response', true);
			$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
			$payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
			$payment_refunds = !empty( $payment_details['refunds'] ) ? $payment_details['refunds'] : '';

			if( !empty( $payment_refunds ) && is_array($payment_refunds)) {

				ob_start();
				?>
				<h2 class="transaction-details__title" style="font-size: 1.625rem;padding: 10px 0;"><?php _e('Refund Details','usb-swiper'); ?></h2>
				<table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table refund_details">
					<thead>
					<tr>
						<th style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;" class="refund-id"><?php _e('ID','usb-swiper'); ?></th>
						<th style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;" class="refund-amount"><?php _e('Amount','usb-swiper'); ?></th>
						<th style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;" class="refund-date"><?php _e('Date','usb-swiper'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $payment_refunds as $key => $payment_refund ) {
						?>
						<tr>
							<td style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php echo !empty( $payment_refund['id'] ) ? $payment_refund['id'] : '' ?></td>
							<td style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php echo !empty( $payment_refund['amount']['value'] ) ? wc_price($payment_refund['amount']['value']) : '' ?></td>
							<td style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php echo !empty( $payment_refund['create_time'] ) ? date('Y/m/d g:i a', strtotime($payment_refund['create_time'])) : '' ?></td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
				<?php
				$refund_html = ob_get_contents();
				ob_get_clean();
			}
		}
		return $refund_html;
	}
}
