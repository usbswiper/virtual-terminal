<?php
/**
 * Check Usb_Swiper_Log class exists or not.
 *
 * @since 1.0.0
 */
if( ! class_exists( 'Usb_Swiper_Log')  ) {

	/**
	 * The class use when manage logs.
	 *
	 * @since 1.0.0
	 */
	class  Usb_Swiper_Log {

		/**
		 * Manage log directory for this plugin logs.
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string
		 */
		public $handle = 'usb-swiper';

		/**
		 * Paypal environment.
		 *
		 * @since 1.0.0
		 * @access public
		 * @var bool|string
		 */
		public $is_sandbox = '';

		/**
		 * Upload base dit path.
		 *
		 * @since 1.0.0
		 * @access public
		 * @var mixed|string
		 */
		public $basedir ='';

		/**
		 * Initialize the log class actions.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$settings = usb_swiper_get_settings('general');
			$this->is_sandbox = !empty( $settings['is_paypal_sandbox'] );

			$upload_dir = wp_upload_dir();
			$this->basedir = !empty( $upload_dir['basedir']) ? $upload_dir['basedir'] : '';
		}

		/**
		 * Get log file name.
		 *
		 * @since 1.0.0
		 *
		 * @param string $file_name Get file name.
		 *
		 * @return string
		 */
		public function get_log_name( $file_name ) {

			$file = $this->handle;
			if( !empty( $file_name ) ) {
				$file .='-'.$file_name;
			}

			return $file.'.log';
		}

		/**
		 * Create log.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message
		 * @param string $file_name
		 * @param false  $default
		 */
		public function log( $message, $file_name = '', $default = false ) {

			$level = 'info';

			if( $default ) {
				$this->add_default( $file_name, $level );
			}

			$this->generate_log($message, $file_name, $level);
		}

		/**
		 * Add default log.
		 *
		 * @since 1.0.0
		 *
		 * @param string $file_name
		 * @param string $level
		 */
		public function add_default( $file_name = '', $level = 'info' ) {

			global $wp_version;
			$environment = ($this->is_sandbox === true) ? 'SANDBOX' : 'LIVE';
			$this->generate_log('PayPal Environment: ' . $environment, $file_name, $level);
			$this->generate_log('WordPress Version: ' . $wp_version, $file_name, $level);
			$this->generate_log('WooCommerce Version: ' . WC()->version, $file_name, $level);
		}

		/**
		 * Put log in log file.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message
		 * @param string $file_name
		 * @param string $level
		 */
		public function generate_log( $message, $file_name = '', $level = 'info' ) {

			if(empty( $message ) ){
				return;
			}

			if( !is_dir($this->basedir.'/'.$this->handle)) {
				mkdir($this->basedir.'/'.$this->handle);
			}

			$file_name = self::get_log_name($file_name);

			$file_path = $this->basedir.'/'.$this->handle.'/'.$file_name;

			$time    = date_i18n( 'm-d-Y @ H:i:s' );
			$entry   = "{$time} {$level} {$message}";

			$fopen = fopen( $file_path,"a");
			$result = fwrite( $fopen, $entry . PHP_EOL );
			fclose( $fopen );
		}

		/**
		 * Display log using filename.
		 *
		 * @since 1.0.0
		 *
		 * @param string $file_name
		 *
		 * @return string $logs
		 */
		public function display_log( $file_name ) {

			$file_name = self::get_log_name($file_name);

			$file_path = $this->basedir.'/'.$this->handle.'/'.$file_name;

			$logs = '';
			if( file_exists( $file_path ) ) {
				$logs = esc_html( file_get_contents( $file_path ) );
			}

			return $logs;
		}
	}
}
