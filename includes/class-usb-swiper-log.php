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
		public $handle = 'USBSwiper';

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
				mkdir($this->basedir.'/'.$this->handle, 0755, true);
			}

			$file_name = self::get_log_name($file_name);
			$file_path = $this->basedir.'/'.$this->handle.'/'.$file_name;

			$time    = date_i18n( 'm-d-Y @ H:i:s' );
			$entry   = "{$time} {$level} {$message}";

			if ($fopen = fopen($file_path, "a")) {
				fwrite($fopen, $entry . PHP_EOL);
				fclose($fopen);
			} else {
				error_log("Failed to open file for writing: " . $file_path);
			}
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

        /**
         * Get the Log files.
         *
         * @since 1.0.0
         *
         * @return array
         */
		public function get_log_files() {

			$upload_dir = wp_upload_dir();
			$basedir = !empty( $upload_dir['basedir']) ? $upload_dir['basedir'] : '';
			$log_files = scandir($basedir.'/'.$this->handle);

			$files = array();

			if( !empty( $log_files ) && is_array( $log_files ) ) {
				foreach ( $log_files as $key => $log_file ) {
					if ( strpos($log_file, $this->handle . '-onboarding-') !== false ) {
						$files[] = $log_file;
					}
				}
			}

			return !empty( $files ) ? array_reverse($files) : array();
		}

        /**
         * Get the Log file content.
         *
         * @since 1.0.0
         *
         * @param string $log get log file name.
         * @return string
         */
		public function get_log_content( $log ) {

			if( empty( $log ) ) {
				return '';
			}

			$upload_dir = wp_upload_dir();
			$basedir = !empty( $upload_dir['basedir']) ? $upload_dir['basedir'] : '';

			$file = $basedir.'/'.$this->handle.'/'.$log;

			$log_content = '';
			if( file_exists( $file ) ) {

				$log_content = esc_html( file_get_contents( $file ) );
			}

			return $log_content;
		}
	}
}
