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
	
	public function __construct() {
	
	}
	
	/**
	 * Get zettle setting fields for frontend and backed.
	 *
	 * @param string $type Get field display type. ie, public, admin, both. default is public.
	 * @return mixed|null
	 */
	public static function get_setting_fields( $type = 'public' ) {
		
		$api_create_link = add_query_arg(
			[
				'name' => 'UsbSwiper integration',
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
	
	public static function get_access_token() {
		
		$access_token = self::get_token_data('access_token');
		$refresh_token = self::get_token_data('refresh_token');
		$time = self::get_token_data('time');
		$date1 = new DateTime($time);
		$date2 = new DateTime(date('Y-m-d h:i:s'));
		$interval = $date1->diff($date2);
		$difference = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->d * 86400);
		
		if( $difference >= 7200 ){
			$response = self::generate_refresh_token($refresh_token);
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
		
		$response = wp_remote_post( self::$token_url, array(
			'body' => array(
				'grant_type'    => 'authorization_code',
				'code'          => $authorization_code,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $redirect_uri,
			),
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
		));
		
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
			
			$response = wp_remote_post( self::$token_url, array(
				'body' => array(
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
				),
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			));
			
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
		}
		
		return $token_response;
	}
	
	/**
	 * Disconnect zettle application request.
	 *
	 * @return array|int[]
	 */
	public static function disconnect_app() {
		
		$access_token = self::get_access_token();
		$client_id = self::get_settings('zettle_client_id', 'admin' );
		$client_secret = self::get_settings('zettle_client_secret', 'admin' );
		
		$status_code = 404;
		
		if( !empty(  $access_token ) ) {
			$response = wp_remote_request(self::$disconnect_app_url, array(
				'method' => 'DELETE',
				'headers' => array(
					'Authorization' => "Bearer {$access_token}",
					'Content-Type' => 'application/json',
				),
				'body' => array(
					'client_id'     => $client_id,
					'client_secret' => $client_secret,
				),
			));
			
			$status_code = (int) wp_remote_retrieve_response_code( $response );
			
			if ( is_wp_error( $response ) ) {
				return [
					'status' => $status_code,
					'message' => $response->get_error_message(),
				];
			}
		}
		
		if( $status_code === 204 || $status_code === 200 ) {
			delete_user_meta(get_current_user_id(),'usb_swiper_zettle_token');
		}
		
		return [
			'status' => $status_code,
		];
	}
	
	public static function pair_reader( $args = [] ) {
		
		if( empty( $args ) || !is_array( $args  ) ){
			return [
				'status' => 404,
				'message' => __( 'Pair rreader', 'usb-swiper' ),
			];
		}
		
		$access_token = self::get_access_token();
		
		$response = wp_remote_post( self::$reader_connect_url.'/link-offers/claim', [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => "Bearer {$access_token}",
			],
			'body' =>  json_encode( [
				'code' => !empty( $args['code'] ) ? $args['code'] : '',
				'tags' => [
					'device_name' => !empty( $args['device_name'] ) ? $args['device_name'] : '',
				],
			]),
			'method'      => 'POST',
			'data_format' => 'body',
		]);
		
		$status_code = (int) wp_remote_retrieve_response_code( $response );
		
		if ( is_wp_error( $response ) ) {
			
			return [
				'status' => $status_code,
				'message' => $response->get_error_message(),
			];
			
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		$body_data = json_decode( $body, true );
		
		return [
			'status' => $status_code,
			'data' => $body_data
		];
	}
}