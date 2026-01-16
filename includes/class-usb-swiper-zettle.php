<?php

defined('ABSPATH') || exit;

/**
 * The UsbSwiperZettle class is responsible for all zettle request.
 *
 * @since 1.0.0
 */
class UsbSwiperZettle {
	
	/**
	 * Define zettle scopes.
	 *
	 * @var array|string[]
	 */
	public static array $scopes = [
		'READ:USERINFO',
		'WRITE:USERINFO',
		'READ:PRODUCT',
		'WRITE:PRODUCT',
		'WRITE:REFUND2',
		'READ:PAYMENT',
		'WRITE:PAYMENT',
		'READ:PURCHASE',
	];
	
	/**
	 * Define zettle apps api key URL.
 	 *
	 * @var string
	 */
	public static string $api_key_url = 'https://my.zettle.com/apps/api-keys';
	
	/**
	 * Define zettle oauth authorize URL.
	 *
	 * @var string
	 */
	public static string $authorize_url = 'https://oauth.zettle.com/authorize';
	
	/**
	 * Define zettle oauth token URL.
	 *
	 * @var string
	 */
	public static string $token_url = 'https://oauth.zettle.com/token';
	
	/**
	 * Define zettle oauth application connections URL.
	 *
	 * @var string
	 */
	public static string $disconnect_app_url = 'https://oauth.zettle.com/application-connections/self';
	
	/**
	 * Define reader integration URL.
	 *
	 * @var string
	 */
	public static string $reader_connect_url = 'https://reader-connect.zettle.com/v1/integrator';
	
	/**
	 * Define random number prefix.
	 *
	 * @var string
	 */
	public static string $number_prefix = 'usbswiper';
	
	/**
	 * Define default log file name.
	 *
	 * @var string
	 */
	public static $default_log_file ='Zettle';
	
	/**
	 * Get zettle setting fields for frontend and backed.
	 *
	 * @param string $type Get field display type. ie, public, admin, both. default is public.
	 * @return mixed|null
	 */
	public static function get_setting_fields( $type = 'public' ) {
		
		$api_create_link = add_query_arg(
			[
				'name' => __( 'USBSwiper Integration', 'usb-swiper' ),
				'scopes' => implode(
					'%20',
					self::$scopes
				),
			],
			self::$api_key_url,
		);
		
		$settings = self::get_settings('', 'admin');
		
		$get_admin_fields = [
			[
				'type' => 'text',
				'id' => 'zettle_client_id',
				'name' => 'zettle_client_id',
				'label' => __('Client ID', 'usb-swiper'),
				'required' => true,
				'attributes' => '',
				'class' => 'regular-text vt-input-field',
				'value' => !empty( $settings['zettle_client_id'] ) ? $settings['zettle_client_id'] : '' ,
				'description' => sprintf(__( 'Enter Zettle Client ID. If you have not created then create %s.','usb-swiper' ), '<a target="_blank" href="'.esc_url( 'https://developer.zettle.com/applications/create' ).'">'.__('Public API credentials','usb-swiper').'</a>'),
			],
			[
				'type' => 'text',
				'id' => 'zettle_client_secret',
				'name' => 'zettle_client_secret',
				'label' => __('Client Secret', 'usb-swiper'),
				'required' => true,
				'attributes' => '',
				'class' => 'regular-text vt-input-field',
				'value' => !empty( $settings['zettle_client_secret'] ) ? $settings['zettle_client_secret'] : '' ,
				'description' => sprintf(__( 'Enter Zettle Client Secret. Get Secret id from Zettle dashboard %s.','usb-swiper' ), '<a target="_blank" href="'.esc_url( 'https://developer.zettle.com/dashboard' ).'">'.__('here','usb-swiper').'</a>'),
			],
			[
				'type' => 'text',
				'id' => 'zettle_redirect_uri',
				'name' => 'zettle_redirect_uri',
				'label' => __('OAuth Redirect URIs', 'usb-swiper'),
				'required' => true,
				'attributes' => [ 'readonly' => true ],
				'class' => 'regular-text vt-input-field',
				'default' => self::get_redirection_uri(),
			]
		];
		
		$zettle_settings = self::get_settings();
		
		$get_front_fields = [
			[
				'type' => 'password',
				'id' => 'zettle_api_key',
				'name' => 'zettle_api_key',
				'label' => __('API key', 'usb-swiper'),
				'required' => true,
				'attributes' => '',
				'class' => 'regular-text vt-input-field',
				'value' => !empty( $zettle_settings['zettle_api_key'] ) ? $zettle_settings['zettle_api_key'] : '' ,
				'description' => sprintf(__( 'Enter Zettle API Key. %s in PayPal Zettle to allow access.','usb-swiper' ), '<a target="_blank" href="'.esc_url( $api_create_link ).'">'.__('Create an API key','usb-swiper').'</a>'),
			],
			[
				'type' => 'checkbox',
				'id' => 'enable_zettle_tipping',
				'name' => 'enable_zettle_tipping',
				'label' => __( 'Enable Tipping', 'usb-swiper'),
				'required' => false,
				'value' => true,
				'checked' => !empty( $zettle_settings['enable_zettle_tipping'] ),
				'description' => '',
				'class' => '',
				'wrapper_class' => 'vt-check-wrap',
			],
		];
		
		$get_fields = '';
		if( $type === 'public' ) {
			$get_fields = $get_front_fields;
		} elseif ( $type === 'admin' ) {
			$get_fields = $get_admin_fields;
		} elseif ( $type === 'both' ) {
			if ( !empty(  $get_front_fields ) && !empty( $get_admin_fields ) ) {
				$get_fields = array_merge($get_front_fields, $get_admin_fields);
			} elseif ( !empty( $get_front_fields) && empty( $get_admin_fields ) ) {
				$get_fields = $get_front_fields;
			} elseif ( !empty( $get_admin_fields) && empty( $get_front_fields ) ) {
				$get_fields = $get_admin_fields;
			}
		}
		
		return apply_filters( 'usb_swiper_zettle_setting_fields', $get_fields, $type );
	}

	/**
	 * Get settings values.
	 *
	 * @param string $key Get setting id.
	 * @param string $type Get field display type. ie, public, admin, both. default is public.
	 * @param int $user_id Get user id.
	 * @return array|false|mixed|string|null $settings
	 */
	public static function get_settings( $key = '', $type = 'public', $user_id =  0 ) {
	
		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		$public_settings = get_user_meta( $user_id, 'usb_swiper_zettle_settings', true);
		$admin_settings = usb_swiper_get_settings('zettle');
		
		$settings = '';
		if( $type === 'public' ) {
			$settings = $public_settings;
		} elseif ( $type === 'admin' ) {
			$settings = $admin_settings;
		} elseif ( $type === 'both' ) {
			if( !empty( $admin_settings ) && !empty( $public_settings ) ) {
				$settings = array_merge( $admin_settings, $public_settings );
			} elseif( !empty( $admin_settings ) && empty( $public_settings ) ) {
				$settings = $admin_settings;
			} elseif( !empty( $public_settings ) && empty( $admin_settings ) ) {
				$settings = $public_settings;
			}
		}
		
		if( !empty(  $key ) ) {
			return !empty( $settings[$key] ) ? $settings[$key] : '';
		}
		
		return $settings;
	}
	
	/**
	 * Get zettle reader data.
	 *
	 * @param int $user_id Get login user id.
	 * @return mixed|string
	 */
	public static function get_zettle_reader_data( $user_id = 0 ) {
		
		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		$reader_data = get_user_meta( $user_id, 'usb_swiper_zettle_reader_data', true);
		
		return !empty( $reader_data ) ? $reader_data : '';
	}
	
	/**
	 * Get zettle token data.
	 *
	 * @param string $key Get field id.
	 * @param int $user_id Get user id.
	 * @return mixed|string $settings
	 */
	public static function get_token_data( $key = '', $user_id =  0 ) {
		
		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}
		
		$settings = get_user_meta( $user_id, 'usb_swiper_zettle_token', true);
		
		if( !empty( $key ) ) {
			return !empty( $settings[$key] ) ? $settings[$key] : '';
		}
		
		return $settings;
	}
	
	/**
	 * Get access token.
	 *
	 * @return mixed|string $access_token
	 * @throws Exception
	 */
	public static function get_access_token() {
		
		$access_token = self::get_token_data('access_token');
		$refresh_token = self::get_token_data('refresh_token');
		$time = self::get_token_data('time');
		$date1 = new DateTime($time);
		$date2 = new DateTime(current_time('mysql'));
		$interval = $date1->diff($date2);
		$difference = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->d * 86400);
		
		if( $difference >= 7200 ) {
			$response = self::generate_refresh_token( $refresh_token );
			$access_token = !empty( $response['access_token'] ) ? $response['access_token'] : '';
		}
		
		return $access_token;
	}
	
	/**
	 * Get random umber.
	 *
	 * @param int $min Get min value.
	 * @param int $max Get max value.
	 * @return string Return unique random number.
	 * @throws Exception
	 */
	public static function get_unique_random_id( $min = 1000, $max = 9999 ) {
		
		return self::$number_prefix.'_'.random_int( $min, $max );
	}
	
	/**
	 * Get zettle redirection url.
	 *
	 * @return string
	 */
	public static function get_redirection_uri() {
		
		return wc_get_endpoint_url( 'vt-zettle', '', wc_get_page_permalink( 'myaccount' ) );
	}
	
	/**
	 * Generate token link.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get_generate_token_link() {
		
		return add_query_arg(
			[
				'response_type' => 'code',
				'scope' => implode('%20', self::$scopes ),
				'client_id' => self::get_settings('zettle_client_id', 'admin' ),
				'redirect_uri' => self::get_redirection_uri(),
				'state' => self::get_unique_random_id(),
			],
			self::$authorize_url,
		);
	}
	
	/**
	 * Generate token request.
	 *
	 * @param string $code Get token code.
 	 * @return array|false
	 */
	public static function generate_token( $code ) {
		
		if(  empty( $code ) ) {
			return false;
		}
		
		$client_id = self::get_settings( 'zettle_client_id', 'admin' );
		$client_secret = self::get_settings( 'zettle_client_secret', 'admin' );
		$redirect_uri = self::get_redirection_uri();
		$authorization_code = $code;
		
		$request_url = self::$token_url;
		
		$request_args = [
			'body' => [
				'grant_type'    => 'authorization_code',
				'code'          => $authorization_code,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $redirect_uri,
			],
			'timeout' => 10,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
		];
		
		$response = wp_remote_post( $request_url, $request_args);
		
		self::add_log( $response, $request_url, $request_args,'generate_token' );
		
		$status_code = (int) wp_remote_retrieve_response_code( $response );
		
		if ( is_wp_error( $response ) ) {
			return [
				'status' => $status_code,
				'message' => $response->get_error_message(),
			];
		}
		
		$body = wp_remote_retrieve_body($response);
		$body_data = json_decode($body, true);
		
		return [
			'status' => $status_code,
			'access_token' => !empty( $body_data['access_token'] ) ? $body_data['access_token'] : '',
			'refresh_token' => !empty( $body_data['refresh_token'] ) ? $body_data['refresh_token'] : '',
			'time' => current_time('mysql'),
		];
	}
	
	/**
	 * Generate token request using refresh token.
	 *
	 * @param string $refresh_token Get refresh token.
	 * @return array $token_response
	 */
	public static function generate_refresh_token( $refresh_token = '' ) {
		
		if( empty( $refresh_token ) ) {
			
			$refresh_token  = UsbSwiperZettle::get_token_data('refresh_token');
		}
		
		$status_code = 404;
		$body_data = [];
		if( !empty( $refresh_token ) ) {
			
			$client_id = self::get_settings( 'zettle_client_id', 'admin' );
			$client_secret = self::get_settings('zettle_client_secret', 'admin' );
			
			$request_url = self::$token_url;
			
			$request_args  = [
				'body' => [
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
				],
				'timeout' => 10,
				'headers' => [
					'Content-Type' => 'application/x-www-form-urlencoded',
				]
			];
			
			$response = wp_remote_post( $request_url, $request_args);
			
			self::add_log( $response, $request_url, $request_args,'generate_refresh_token' );
			
			$status_code = (int) wp_remote_retrieve_response_code( $response );
			
			if ( is_wp_error( $response ) ) {
				return [
					'status' => $status_code,
					'message' => $response->get_error_message(),
				];
			}
			
			$body = wp_remote_retrieve_body($response);
			$body_data = json_decode($body, true);
		}

		$token_response = [
			'status' => $status_code,
			'access_token' => !empty( $body_data['access_token'] ) ? $body_data['access_token'] : '',
			'refresh_token' => !empty( $body_data['refresh_token'] ) ? $body_data['refresh_token'] : '',
			'time' => current_time('mysql'),
		];

		if( !empty( $token_response ) && (int) $token_response['status'] == 200 ) {

			update_user_meta( get_current_user_id(), 'usb_swiper_zettle_token', $token_response );
			return $token_response;
		} else {
			delete_user_meta( get_current_user_id(), 'usb_swiper_zettle_token' );
		}
		
		return [];
	}
	
	/**
	 * Disconnect zettle application request.
	 *
	 * @return array|int[]
	 * @throws Exception
	 */
	public static function disconnect_app() {
		
		$access_token = self::get_access_token();
		$client_id = self::get_settings('zettle_client_id', 'admin' );
		$client_secret = self::get_settings('zettle_client_secret', 'admin' );
		
		$status_code = 404;
		$message = __( 'Something went wrong', 'usb-swiper');
		
		if( !empty(  $access_token ) ) {
			
			$request_url = self::$disconnect_app_url;
			
			$request_args = [
				'method' => 'DELETE',
				'headers' => [
					'Authorization' => "Bearer {$access_token}",
					'Content-Type' => 'application/json',
				],
				'timeout' => 10,
				'body' => [
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
				],
			];
			
			$response = wp_remote_request( $request_url, $request_args );
			
			self::add_log( $response, $request_url, $request_args,'disconnect_app' );
			
			$status_code = (int) wp_remote_retrieve_response_code( $response );
			
			if ( is_wp_error( $response ) ) {
				return [
					'status' => $status_code,
					'message' => $response->get_error_message(),
				];
			}
			
			$message = wp_remote_retrieve_response_message($response);
		}
		
		if( $status_code === 204 || $status_code === 200 ) {
			delete_user_meta(get_current_user_id(),'usb_swiper_zettle_token');
		}
		
		return [
			'status' => $status_code,
			'message' => $message,
		];
	}
	
	/**
	 * Pair reader request.
	 *
	 * @param array $args Get request arguments.
	 * @return array
	 * @throws Exception
	 */
	public static function pair_reader( $args = [] ) {
		
		if( empty( $args ) || !is_array( $args  ) ){
			return [
				'status' => 404,
				'message' => __( 'Pair reader', 'usb-swiper' ),
			];
		}
		
		$access_token = self::get_access_token();
		
		$request_url = self::$reader_connect_url.'/link-offers/claim';
	
		$request_args = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => "Bearer {$access_token}",
			],
			'timeout' => 10,
			'body' =>  json_encode( [
				'code' => !empty( $args['code'] ) ? $args['code'] : '',
				'tags' => [
					'deviceName' => !empty( $args['device_name'] ) ? $args['device_name'] : '',
				],
			] ),
			'method'      => 'POST',
			'data_format' => 'body',
		];
		
		$response = wp_remote_post( $request_url, $request_args);
		
		self::add_log( $response, $request_url, $request_args,'pair_reader' );
		
		$status_code = (int) wp_remote_retrieve_response_code( $response );
		
		if ( is_wp_error( $response ) ) {
			
			return [
				'status' => $status_code,
				'message' => $response->get_error_message(),
			];
			
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		$body_data = json_decode( $body, true );
		
		$message = wp_remote_retrieve_response_message($response);
		
		return [
			'status' => $status_code,
			'data' => $body_data,
			'message' => $message,
		];
	}
	
	/**
	 * Unpairing zettle device request.
	 *
	 * @param string $id Get link device id.
	 * @return array
	 * @throws Exception
	 */
	public static function unpairing_zettle_device( $id )  {
		
		if( empty( $id ) ){
			
			return [
				'status' => 404,
				'message' => __( 'Zettle paired device id not found', 'usb-swiper' ),
			];
		}
		
		$access_token = self::get_access_token();
		
		$request_url = self::$reader_connect_url.'/links/'.$id;
		
		$request_args = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => "Bearer {$access_token}",
			],
			'timeout' => 10,
			'method'      => 'DELETE',
			'data_format' => 'body',
		];
		
		$response = wp_remote_post( $request_url, $request_args);
		
		self::add_log( $response, $request_url, $request_args,'unpairing_zettle_device' );
		
		$status_code = (int) wp_remote_retrieve_response_code( $response );
		
		if ( is_wp_error( $response ) ) {
			
			return [
				'status' => $status_code,
				'message' => $response->get_error_message(),
			];
			
		}
		
		$body = wp_remote_retrieve_body( $response );
		$body_data = json_decode( $body, true );
		
		$message = wp_remote_retrieve_response_message($response);
		
		return [
			'status' => $status_code,
			'data' => $body_data,
			'message' => $message,
		];
	}
	
	/**
	 * websocket connection request.
	 *
	 * @param string $link_id Get link id.
	 * @return array
	 * @throws Exception
	 */
	public static function websocket_connection( $link_id ) {
		
		if( empty( $link_id ) ) {
			
			return [
				'status' => 404,
				'message' => __( 'link id is missing for Websocket Connection.', 'usb-swiper' ),
			];
		}
		
		$access_token = self::get_access_token();
		
		$request_url = self::$reader_connect_url.'/sessions';
		
		$request_args = [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => "Bearer {$access_token}",
			],
			'timeout' => 10,
            'body' =>  json_encode( (object) [
                'links' => (object) [
                    "$link_id" => ["1"]
                ]
            ] ),
			'method'      => 'POST',
			'data_format' => 'body',
		];
		
		$response = wp_remote_post( $request_url, $request_args );
		
		self::add_log( $response, $request_url, $request_args,'websocket_connection' );
		
		$status_code = (int) wp_remote_retrieve_response_code( $response );
		
		if ( is_wp_error( $response ) ) {
			
			return [
				'status' => $status_code,
				'message' => $response->get_error_message(),
			];
			
		}
		
		$header = wp_remote_retrieve_header( $response, 'location' );
		
		$body = wp_remote_retrieve_body( $response );
		
		$body_data = json_decode( $body, true );
		
		$message = wp_remote_retrieve_response_message($response);
		
		return [
			'status' => $status_code,
			'header' => $header,
			'data' => $body_data,
			'message' => $message,
		];
	}

	/**
     * Get request uuid.
     *
     * @since 2.3.4
     *
	 * @param int $transaction_id Get transaction id.
	 * @return string
	 */
	public static function get_request_uuid( $transaction_id ) {

		return strtolower( sprintf(
			'%s-%s-%s-%s-%s',
			substr( uniqid(), 0, 8),
			$transaction_id,
			substr( uniqid(), 0, 4),
			substr( uniqid(), 0, 4),
			substr( uniqid(), 0, 12),
		) );
	}

	/**
     * Get transaction id from message id.
     *
     * @since 2.3.4
     *
	 * @param string $message_id Get message id.
	 * @return false|int
	 */
	public static function get_transaction_id_from_message_id( $message_id ) {

		if ( empty( $message_id ) ) {
			return false;
		}

		$message = explode('-',$message_id );

		return !empty( $message[1] ) ? (int) $message[1] : 0;
	}

	/**
     * Get Zettle payment request.
     *
	 * @param string $websocket_url Get websocket url.
	 * @param array $args Get payment request arguments.
	 * @return array
	 * @throws Exception
	 */
	public static function payment_request( $websocket_url, $args = [] ) {
		
		$transaction_id = !empty( $args['transaction_id'] ) ? $args['transaction_id'] : '';
		$amount = !empty( $args['amount'] ) ? $args['amount'] : 0;
		$link_id = !empty( $args['reader_data']['id'] ) ? $args['reader_data']['id'] : '';

		$get_request_uuid = self::get_request_uuid($transaction_id);

		$request_args = json_encode([
			"type" => "MESSAGE",
			"linkId" => $link_id,
			"channelId" => '1',
			"messageId" =>  $get_request_uuid,
			"payload" =>  [
				"type" => "PAYMENT_REQUEST",
				'accessToken' => self::get_access_token(),
				'expiresAt' => time() + ( 60 * 5 ),
				'internalTraceId' => $get_request_uuid,
				'amount' => !empty( $amount ) ? (int) round( $amount * 100 ) : 0,
				'tippingType' => !empty( $args['tipping'] ) ? 'DEFAULT' : 'NONE',
			]
		]);
		
		self::add_log( [], $websocket_url, $request_args,'websocket_payment_request', $transaction_id );
		
		return [
			'websocket_url' => $websocket_url,
			'transaction_id' => $transaction_id,
			'access_token' => self::get_access_token(),
			'message_id' => $get_request_uuid,
			'link_id' => $link_id,
			'expiresAt' => time() + ( 60 * 5 ),
			'payment_request' => $request_args,
			'payment_request_message' => sprintf(__( '%s amount request sent to zettle', 'usb-swiper' ), wc_price( $amount, ['currency' => usbswiper_get_default_currency()])),
		];
	}

	/**
     * Get Zettle refund payment request.
     *
	 * @param string $websocket_url Get websocket url.
	 * @param array $args Get payment request arguments.
	 * @return array
	 * @throws Exception
	 */
	public static function refund_payment_request( $websocket_url, $args = [] ) {

		$transaction_id = !empty( $args['transaction_id'] ) ? $args['transaction_id'] : '';
		$amount = !empty( $args['amount'] ) ? $args['amount'] : 0;
		$link_id = !empty( $args['reader_data']['id'] ) ? $args['reader_data']['id'] : '';

		$get_request_uuid = self::get_request_uuid( $transaction_id );

		$request_args = json_encode([
			"type" => "MESSAGE",
			"linkId" => $link_id,
            		"channelId" =>  '1',
			"messageId" =>  $get_request_uuid,
			"payload" => [
				"type" => "REFUND_REQUEST",
				'accessToken' => self::get_access_token(),
				'expiresAt' => time() + ( 60 * 5 ),
				'refundTraceId' => $get_request_uuid,
				'paymentTraceId' => usbswiper_get_zettle_tracking_id( $transaction_id ),
				'refundAmount' => !empty( $amount ) ? (int)   round( $amount * 100 ) : 0,
			]
		]);

		self::add_log( [], $websocket_url, $request_args,'websocket_refund_payment_request', $transaction_id );

		return [
			'websocket_url' => $websocket_url,
			'transaction_id' => $transaction_id,
			'access_token' => self::get_access_token(),
			'refund_request' => $request_args,
			'refund_request_message' => sprintf(__( '%s refund request sent to zettle', 'usb-swiper' ), wc_price( $amount, ['currency' => usbswiper_get_default_currency()])),
		];
	}

	public static function refund_api_payment_request( $args = [] ) {
		$transaction_id = $args['transaction_id'];
		$payment_uuid = $args['payment_uuid'];
    		$refund_amount = $args['refund_amount'];

		$access_token = self::get_access_token(); 

		$payload = json_encode([
			'amount' => intval($refund_amount * 100),
			'reference' => strtoupper(substr(bin2hex(random_bytes(5)), 0, 10)),
		]);

		$request_url = "https://api.zettle.com/v2/payments/{$payment_uuid}/refunds";

		self::add_log( [], $request_url, $payload, 'zettle_refund_payment_request', $transaction_id );

		$response = wp_remote_post(
			$request_url,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type' => 'application/json'
				],
				'body' => $payload,
				'timeout' => 30,
			]
		);

		self::add_log( $response, '', '', 'zettle_refund_payment_response', $transaction_id );

		if (is_wp_error($response)) {
			return [
				'state' => 'FAILED',
				'error' => $response->get_error_message()
			];
		}

		return json_decode(wp_remote_retrieve_body($response), true);
	}
	
	/**
	 * Add zettle request and response logs.
	 *
	 * @param array|object $response Get zettle api response
	 * @param string $url Get url.
	 * @param array|string $request Get api request
	 * @param string $action_name Get action name
	 * @param string $log_file Get log file
	 * @return void
	 */
	public static function add_log( $response, $url, $request, $action_name, $log_file= '' ) {
	
		if(  empty( $log_file ) ) {
			$log_file = self::$default_log_file;
		}
		
		$api_log = new Usb_Swiper_Log();
		
		try {
			
			$api_log->log("Action: ".ucwords( str_replace('_', ' ', $action_name) ), $log_file);
			$api_log->log("Time: ".current_time('mysql'), $log_file);
			$api_log->log('Request URL: '.$url, $log_file );
			
			if ( is_wp_error( $response ) ) {
				
				$error_message = $response->get_error_message();
				$error_code = $response->get_error_code();

				if( !empty( $error_code ) ) {
					$api_log->log('Response Error Code: ' . print_r($error_code, true), $log_file);
				}

				if( !empty( $error_message ) ) {
					$api_log->log('Response Error Message: ' . print_r($error_message, true), $log_file);
				}
				
			} else {

				$body = wp_remote_retrieve_body( $response );

				$body_response = !empty($body) ? json_decode($body, true) : '';
				$body_response = $body_response['body'] ?? $response;

				$status_code = (int) wp_remote_retrieve_response_code( $response );
				$headers = wp_remote_retrieve_headers( $response );

				if ( !empty( $request['body'] ) && is_array( $request['body'] ) ) {
					if( !empty( $request ) ) {
						$api_log->log('Request Body: ' . print_r($request, true), $log_file);
					}
				} elseif ( !empty( $request['body'] ) && is_string( $request['body'] ) ) {
					if( !empty( $request['body'] ) ) {
						$api_log->log('Request Body JSON: ' . print_r($request['body'], true), $log_file);
					}
				} elseif ( !empty( $request ) ) {
					$api_log->log('Request Body Json: ' . print_r($request, true), $log_file);
				}

				if( !empty( $headers ) ) {
					$api_log->log('Response headers: '.print_r( $headers, true), $log_file);
				}

				if(  !empty( $status_code ) ) {
					$api_log->log('Response Code: ' . $status_code, $log_file);
				}

				$message = wp_remote_retrieve_response_message($response);
				if( !empty( $message ) ) {
					$api_log->log('Response Message: ' . $message, $log_file);
				}
				
				if ( !empty( $body_response['body']) && is_array($body_response['body'])) {
					$api_log->log('Response Body: ' . print_r($body_response['body'], true), $log_file);
				} elseif ( !empty($body_response) && is_array($body_response)) {
					$api_log->log('Response Body: ' . print_r($body_response, true), $log_file);
				} elseif( !empty( $body_response ) ) {
					$api_log->log('Response Body: ' . print_r(json_decode(wp_remote_retrieve_body($body_response), true), true), $log_file);
				}
			}
			
		} catch (Exception $ex) {
		
		}
	}

	/**
	 * Get the refund html.
	 *
	 * @since 1.0.0
	 *
	 * @param int $transaction_id get transaction id
	 * @return false|string
	 */
	public static function get_refund_html( $transaction_id ) {

		$refund_html = '';

		if( !empty( $transaction_id ) && $transaction_id > 0 ) {

			$refund_response = get_post_meta( $transaction_id, '_payment_refund_response', true);

			if( !empty( $refund_response ) && is_array( $refund_response ) ) {

				ob_start();
				?>
				<h2 class="transaction-details__title" style="font-size: 1.625rem;padding: 10px 0;"><?php _e('Refund Details','usb-swiper'); ?></h2>
				<table style="width: 100%;display: table;border: 1px solid #ebebeb;border-radius: 0;margin-bottom: 0 !important;" cellspacing="0" cellpadding="0" width="100%" class="woocommerce-table woocommerce-table--order-details shop_table refund_details">
					<thead>
					<tr>
						<th style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;" class="refund-id"><?php _e('ID','usb-swiper'); ?></th>
						<th style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;" class="refund-amount"><?php _e('Amount','usb-swiper'); ?></th>
						<th style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;" class="refund-date"><?php _e('Date','usb-swiper'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$Usb_Swiper_Paypal_request = new Usb_Swiper_Paypal_request();
					$transaction_currency = $Usb_Swiper_Paypal_request->get_transaction_currency($transaction_id);

					foreach ( $refund_response as $key => $payment_refund ) {
						$amount = !empty( $payment_refund['amount'] ) ? usbswiper_convert_zettle_amount(abs($payment_refund['amount'])) : 0;
						$reference_number = !empty( $payment_refund['reference'] ) ? $payment_refund['reference'] : '';
						$created_date = !empty( $payment_refund['created'] ) ? $payment_refund['created'] : '';
						?>
						<tr>
							<td style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php echo esc_html($reference_number); ?></td>
							<td style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php echo wc_price( $amount, array('currency' => $transaction_currency) ); ?></td>
							<td style="text-align:left;width: 33.33%;padding: 10px;border-bottom: 1px solid #ebebeb;border-right: 1px solid #ebebeb;"><?php echo date('Y/m/d g:i a', strtotime($created_date)); ?></td>
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
