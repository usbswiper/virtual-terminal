<?php

/**
 * Check Usb_Swiper_Admin class exists or not.
 *
 * @since 1.0.0
 */
if( !class_exists( 'Usb_Swiper_Admin' ) ) {

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       http://www.angelleye.com/product/usb-swiper
	 * @since      1.0.0
	 *
	 * @package    usb-swiper
	 * @subpackage usb-swiper/admin
	 * @author     AngellEYE <andrew@angelleye.com>
	 */
	class Usb_Swiper_Admin {

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
		 * @var string  $version The current version of this plugin.
		 */
		private $version;

		/**
		 * The transactions post type of this plugin
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string $post_type The transactions post type.
		 */
		public $post_type = 'transactions';

		/**
         * The menu slug for custom setting page.
         *
         * @since 1.0.0
		 * @var string $menu_slug Get sub menu page slug.
		 */
		public $menu_slug = 'usb-swiper';

		/**
		 * The errors of this plugin.
		 *
		 * @since    1.0.0
		 */
		private static $errors = array();

		/**
		 * The messages of this plugin.
		 *
		 * @since    1.0.0
		 */
		private static $messages = array();

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
		}

		/**
		 * Register the styles and scripts for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {

			wp_enqueue_style($this->plugin_name, USBSWIPER_URL . 'assets/css/usb-swiper-admin.css');
			wp_enqueue_script($this->plugin_name, USBSWIPER_URL . 'assets/js/usb-swiper-admin.js', array('jquery'), $this->version, true);
			wp_localize_script( $this->plugin_name, 'usb_swiper_settings', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'remove_fee_message' => __( 'Are you sure you want to remove this fee?','usb-swiper' ),
            ) );
		}

		/**
		 * Footer action for Hide add new button in Transaction post type.
         *
         * @since 1.0.0
		 */
		public function admin_footer() {
			if (isset($_GET['post_type']) && esc_attr( $_GET['post_type'] ) === $this->post_type) {
				echo '<style type="text/css">.page-title-action { display:none; }</style>';
			} elseif ( !empty( $_GET['post']) && get_post_type($_GET['post']) === $this->post_type ) {
				echo '<style type="text/css">.page-title-action { display:none; }</style>';
			}
        }

		/**
         * Check plugin dependency.
		 *
         * @since 1.0.0
		 */
		public function check_plugin_dependency() {

			if ( is_admin() ) {

				/**
				 * Check deactivate_plugins function exists or not.
				 * if function not exists then include plugin.php file.
				 *
				 * @since 1.0.0
				 */
				if ( ! function_exists( 'deactivate_plugins' ) ) {
					include_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$active_plugins = (array) get_option( 'active_plugins', array() );

				/**
				 * Check activate WooCommerce plugins or not.
				 * if WooCommerce plugins not activate then USBSwiper plugin deactivate and display notice.
				 *
				 * @since 1.0.0
				 */
				if ( empty( $active_plugins ) || ! in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {

					/**
					 * Deactivate USBSwiper plugin.
					 *
					 * @since 1.0.0
					 */
					deactivate_plugins( USBSWIPER_BASENAME );

					/**
					 * Display WooCommerce plugin requires notice.
					 *
					 * @since 1.0.0
					 */
					add_action(
						'admin_notices',
						function () {
							/* translators: %1$s: Product Title, %2$s: product link tag start, %3$s: product link tag end */
							echo '<div class="notice notice-error is-dismissible"><p><strong>' . sprintf( __( '%1$s requires %2$sWooCommerce%3$s plugin to be installed and active.', 'usb-swiper' ), 'USBSwiper', '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' ) . '</strong></p></div>';
						}
					);
				}
			}
        }

		/**
         * Add settings link in plugins actions.
         *
         * @since 1.0.0
         *
		 * @param array $links Get actions links.
		 *
		 * @return array Return plugin setting link.
		 */
        public function plugin_action_links( $links ) {

	        $action_links = array(
		        'settings' => '<a href="' . admin_url( 'admin.php?page='.$this->menu_slug ) . '" aria-label="' . esc_attr__( 'View USBSwiper settings', 'usb-swiper' ) . '">' . esc_html__( 'Settings', 'usb-swiper' ) . '</a>',
	        );

	        return array_merge( $action_links, $links );
        }

		/**
		 * Register custom "transaction" post type.
		 *
		 * @since	0.1.0
		 */
		public function register_transactions_post_type() {

			register_post_type( $this->post_type, array(
				'labels'    => array(
					'name' => __('Transactions', 'usb-swiper'),
					'singular_name' => __('Transactions', 'usb-swiper'),
					'add_new' => __('Add New', 'usb-swiper'),
					'add_new_item' => __('Add New Transaction', 'usb-swiper'),
					'edit' => __('Manage', 'usb-swiper'),
					'edit_item' => __('Manage Transactions', 'usb-swiper'),
					'new_item' => __('New Transactions', 'usb-swiper'),
					'view' => __('View', 'usb-swiper'),
					'view_item' => __('View Transactions', 'usb-swiper'),
					'search_items' => __( 'Search Transactions', 'usb-swiper'),
					'not_found' => __('No Transactions found', 'usb-swiper'),
					'not_found_in_trash' => __('No Transactions found in Trash', 'usb-swiper'),
					'parent' => __('Parent Transactions', 'usb-swiper')
				),
				'description'        => __( 'USBSwiper Transactions post type.', 'usb-swiper' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => $this->post_type ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 20,
				'supports'           => array( 'title', 'author'),
				'show_in_rest'       => false
			) );

			if( is_admin()) {

				if ( !empty( $_GET['post']) && get_post_type($_GET['post']) === $this->post_type ) {

					remove_post_type_support( $this->post_type, 'custom-fields' );
				}
            }
		}

		/**
		 * Add custom menu page.
         *
         * @since 1.0.0
		 */
		public function admin_menu() {

			global $submenu;
			unset($submenu['edit.php?post_type='.$this->post_type][10]);

			add_submenu_page(
				'options-general.php',
				__( 'USBSwiper', 'usb-swiper' ),
				__( 'USBSwiper', 'usb-swiper' ),
				'administrator',
				$this->menu_slug,
				array( $this, 'usb_swiper_settings' ),
				70,
			);
		}

		/**
		 * Add meta boxes for transactions post type.
		 *
		 * @since 1.0.0
		 */
		public function add_meta_boxes() {

			add_meta_box( 'transaction-history', __( 'Transaction History', 'usb-swiper' ), array( $this, 'transaction_history' ), $this->post_type );
			add_meta_box( 'transaction-log', __( 'Transaction Log', 'usb-swiper' ), array( $this, 'transaction_log' ), $this->post_type );
		}

		/**
		 * Callback - Action for "Transaction History" meta box in transactions post type.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post $post The object for the current post/page
		 */
		public function transaction_history( $post ) {

			?>
            <div class="usb-swiper-transaction-history-wrap">
				<?php
				if( !empty( $post->ID ) && $post->ID > 0 ) {
					$args = array(
						'transaction_id' => $post->ID,
					);

					usb_swiper_get_template( 'wc-transaction-history.php', $args );
				}
				?>
            </div>

			<?php
		}

		/**
         * Display log using meta box in each transaction.
         *
         * @since 1.0.0
         *
		 * @@param WP_Post $post The object for the current post/page
		 */
		public function transaction_log( $post ) {

		    $transaction_id = !empty( $post->ID ) ? $post->ID : '';

		    $Usb_Swiper_Log = new Usb_Swiper_Log();
		    ?>
            <div class="usb-swiper-log-viewer">
                <pre><?php echo $Usb_Swiper_Log->display_log($transaction_id);?></pre>
            </div>
            <?php
        }

		/**
         * Add custom columns for transactions post type.
         *
         * @since 1.0.0
         *
		 * @param array $columns Get list table columns.
		 *
		 * @return array $columns
		 */
		public function transactions_post_type_columns( $columns ) {

			$date = !empty( $columns['date']) ? $columns['date'] : '';
			unset($columns['date']);

			$columns['transaction_id'] = __( 'Transaction ID', 'usb-swiper');
			$columns['grand_total'] = __( 'Grand Total', 'usb-swiper');
			$columns['payment_status'] = __( 'Payment Status', 'usb-swiper');
			$columns['payment_intent'] = __( 'Payment Intent', 'usb-swiper');
			$columns['transaction_environment'] = __( 'Environment', 'usb-swiper');
			$columns['date'] = $date;

			return $columns;
		}

		/**
         * Display transactions post type custom column html.
         *
         * @since 1.0.0
         *
		 * @param string $column Get column name.
		 * @param int $post_id Get post id.
		 */
		public function transactions_column_html( $column, $post_id ) {

			$payment_response = get_post_meta( $post_id, '_payment_response', true);
			$purchase_units = !empty( $payment_response['purchase_units'][0] ) ? $payment_response['purchase_units'][0] : '';
			$payment_details = !empty( $purchase_units['payments'] ) ? $purchase_units['payments'] : '';
			$payment_captures = !empty( $payment_details['captures'][0] ) ? $payment_details['captures'][0] : '';
			$payment_authorizations = !empty( $payment_details['authorizations'][0] ) ? $payment_details['authorizations'][0] : '';
			$payment_intent = 'AUTHORIZE';
			if( !empty( $payment_captures ) && !empty( $payment_captures['id'] ) ) {
				$payment_intent = 'CAPTURE';
			}

			switch ( $column ) {
                case 'transaction_id':
                    echo !empty( $payment_response['id'] ) ? $payment_response['id'] : '';
                    break;
				case 'grand_total':
					if( !class_exists('Usb_Swiper_Paypal_request') ) {
						include_once USBSWIPER_PATH.'/includes/class-usb-swiper-paypal-request.php';
					}
					$Usb_Swiper_Paypal_request = new Usb_Swiper_Paypal_request();
					$transaction_currency = $Usb_Swiper_Paypal_request->get_transaction_currency( $post_id);
					$grand_total = get_post_meta( $post_id, 'GrandTotal', true );
					echo !empty( $grand_total ) ? wc_price($grand_total, array('currency' => $transaction_currency)) : '';
					break;
				case 'payment_status' :
					$payment_status = !empty( $payment_authorizations['status'] ) ? $payment_authorizations['status'] : '';
					if( !empty( $payment_captures ) && !empty( $payment_captures['id'] ) ) {
						$payment_status = !empty( $payment_captures['status'] ) ? $payment_captures['status'] : '';
					}
					echo $payment_status;
					break;
                case 'payment_intent' :
					echo !empty( $payment_intent ) ? strtoupper($payment_intent) : '';
                    break;
                case 'transaction_environment':
	                $environment = get_post_meta( $post_id, '_environment', true);
	                echo !empty( $environment ) ? strtoupper($environment) : '';
	                break;
				default;
					echo apply_filters( 'usb_swiper_transactions_column', '' , $column, $post_id );
			}
		}

		/**
         * Display custom filter by user for check individual transactions.
         *
         * @since 1.0.0
         *
		 * @param string $post_type Get Post type.
		 */
		public function manage_transactions_filter( $post_type ) {

			if ( $this->post_type !== esc_attr( $post_type ) ) {
			    return;
			}

            $get_users = get_users();

			$selected = '';
			if( isset( $_REQUEST['user_id'] ) && $_REQUEST['user_id'] > 0 ) {
				$selected = esc_attr( $_REQUEST['user_id'] );
            }
            ?>
            <label for="user_id" class="screen-reader-text"><?php _e('Filter by user','usb-swiper'); ?></label>
            <select name="user_id" id="user_id">
                <option value=""><?php _e('All User Transactions','usb-swiper'); ?></option>
                <?php
                if( !empty($get_users) && is_array( $get_users ) ) {

                    foreach ( $get_users as $key => $user ) {
                        ?>
                        <option <?php selected( $selected, $user->ID ); ?> value="<?php echo $user->ID; ?>"><?php echo $user->display_name; ?></option>
                        <?php
                    }
                }
                ?>
            </select>
            <?php
		}

		/**
         * Query filter for search transactions by user.
         *
         * @since 1.0.0
         *
		 * @param $query
		 *
		 * @return $query
		 */
		public function parse_query_filter( $query ) {

			$current_page = isset( $_GET['post_type'] ) ? esc_attr( $_GET['post_type'] ) : '';

		    if( is_admin() && !empty( $current_page ) && $this->post_type === $current_page ) {

		        if( !empty( $_REQUEST['user_id'] ) && $_REQUEST['user_id'] > 0 ) {
			        $query->query_vars['author'] = esc_attr( $_REQUEST['user_id'] );
		        }
		    }

		    return $query;
		}

		/**
		 * Get current page.
         *
		 * @since    1.0.0
         *
		 * @return string
		 */
		public function current_page() {

			return ! empty( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
		}

		/**
		 * Get current section.
         *
		 * @since    1.0.0
         *
		 * @return string
		 */
		public function current_section() {

			$current_section = $this->current_page();

			$current_menu = '';
			if ( ! empty( $current_section ) && $current_section === $this->menu_slug ) {
				$current_menu = ! empty( $_REQUEST['tab'] ) ? esc_attr( $_REQUEST['tab'] ) : '';
			}

			return $current_menu;
		}

		public function exclude_form_tab() {

		    return apply_filters( 'usb_swiper_exclude_form_tab' , array('logs'));
		}

		/**
		 * Get menu items.
		 *
		 * @since   1.0.0
		 */
		public function menu_items() {

			return apply_filters(
				'usb_swiper_menu_items',
				array(
					''              => __( 'General', 'usb-swiper' ),
					'partner_fees'  => __( 'Partner Fees', 'usb-swiper' ),
					'logs'  => __( 'Logs', 'usb-swiper' ),
					'uninstall'     => __( 'Uninstall', 'usb-swiper' ),
				)
			);
		}

		/**
		 * Get all publish pages.
		 *
		 * @since 1.0.0
		 *
		 * @return array $pages
		 */
		public function get_pages() {

			$get_pages = get_pages();

			$pages = array();
			if ( ! empty( $get_pages ) && is_array( $get_pages ) ) {
				foreach ( $get_pages as $key => $page ) {
					$page_id           = ! empty( $page->ID ) ? $page->ID : 0;
					$pages[ $page_id ] = ! empty( $page->post_title ) ? $page->post_title.' (#'.$page_id.')' : '';
				}
			}

			return $pages;
		}

		/**
		 * Display menu.
		 *
		 * @since   1.0.0
		 */
		public function menu() {

			$menu_items = $this->menu_items();

			if ( empty( $menu_items ) || ! is_array( $menu_items ) ) {
				return;
			}

			$current_section = $this->current_section();
			$menu_link       = admin_url( 'admin.php?page=' . $this->menu_slug ); ?>
            <nav class="nav-tab-wrapper">
				<?php
				foreach ( $menu_items as $key => $menu ) {
					if ( ! empty( $key ) ) {
						$menu_link .= '&tab=' . $key;
					}
					$active = '';
					if ( ! empty( $current_section ) && $key === $current_section ) {
						$active = 'nav-tab-active';
					} elseif ( empty( $current_section ) && strtolower( $menu ) === 'general' ) {
						$active = 'nav-tab-active';
					}
					?>
                    <a href="<?php echo esc_attr( $menu_link ); ?>" class="nav-tab <?php echo esc_attr( $active ); ?>"><?php echo esc_attr( $menu ); ?></a>
				<?php } ?>
            </nav>
			<?php
		}

		/**
		 * Display heading title.
		 *
		 * @since    1.0.0
		 */
		public function heading_title() {

			$menu_items      = $this->menu_items();
			$current_section = $this->current_section();
			$heading_title   = ! empty( $menu_items[ $current_section ] ) ? esc_attr( $menu_items[ $current_section ] ) . ' ' . __( 'settings', 'usb-swiper' ) : '';
			?>
            <h1 class="wp-heading-inline"><?php echo esc_attr( apply_filters( 'usb_swiper_heading_title', $heading_title ) ); ?></h1>
			<?php
		}

		/**
		 * Add messages for this plugin.
		 *
		 * @since    1.0.0
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text;
		}

		/**
		 * Add errors for this plugin.
		 *
		 * @since    1.0.0
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text;
		}

		/**
		 * Display notification.
		 *
		 * @since    1.0.0
		 */
		public function notification() {
			?>
            <div class="notification-wrap">
				<?php
				if ( count( self::$errors ) > 0 ) {
					foreach ( self::$errors as $error ) {
						echo '<div id="message" class="error inline notice is-dismissible"><p>' . esc_attr( $error ) . '</p></div>';
					}
				} elseif ( count( self::$messages ) > 0 ) {
					foreach ( self::$messages as $message ) {
						echo '<div id="message" class="updated inline notice is-dismissible"><p>' . esc_attr( $message ) . '</p></div>';
					}
				}
				?>
            </div>
			<?php
		}

		/**
		 * Callback method for custom setting menu page.
         *
         * @since 1.0.0
		 */
		public function usb_swiper_settings() {

			$current_page    = $this->current_page();
			$current_section = $this->current_section();
			$exclude_form_tab = $this->exclude_form_tab();
			?>
            <div class="usb-swiper-wrap wrap">
                <div class="nav-wrap">
                    <?php $this->menu(); ?>
                </div>
                <div class="content-row row">
                    <?php
                    $this->heading_title();
                    $this->notification();

                    if( !in_array($current_section,$exclude_form_tab)) { ?>
                    <form method="post" id="usb_swiper_form" action="" enctype="multipart/form-data">
                    <?php } ?>

                        <div class="content-wrap">
	                        <?php
	                        do_action( 'usb_swiper_section_before_content', $current_section );

	                        do_action( 'usb_swiper_section_content', $current_section );

	                        $section = '_general';
	                        if ( ! empty( $current_section ) ) {
		                        $section = '_' . strtolower( $current_section );
	                        }

	                        do_action( 'usb_swiper_section_content' . $section, $current_section );

	                        do_action( 'usb_swiper_section_after_content', $current_section );
	                        ?>
                        </div>
                        <?php if( !in_array($current_section,$exclude_form_tab)) { ?>
                        <p class="submit <?php echo ! empty( $current_section ) ? esc_attr( $current_section ) : ''; ?> ">
                            <button name="save" class="button-primary" type="submit" value="submit"><?php esc_attr_e( 'Save changes', 'usb-swiper' ); ?></button>
                            <input type="hidden" name="action" value="usb_swiper_settings">
                            <input type="hidden" name="_nonce" value="<?php echo esc_attr( wp_create_nonce( 'usb-swiper-form' ) ); ?>">
                            <input type="hidden" name="current_page" value="<?php echo esc_attr( $current_page ); ?>">
                            <input type="hidden" name="current_section" value="<?php echo esc_attr( $current_section ); ?>">
                        </p>
                    </form>
                    <?php } ?>
                </div>
            </div>
            <?php
		}

		/**
         * Save custom menu page settings.
         *
         * @since 1.0.0
         *
		 * @return bool|void
		 */
		public function save_settings() {

			if ( isset( $_POST['action'] ) && ! empty( $_POST['action'] ) && 'usb_swiper_settings' === sanitize_text_field( $_POST['action'] ) ) {

				if ( ! empty( $_POST['_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_nonce'] ), 'usb-swiper-form' ) ) {

					$current_section = ! empty( $_POST['current_section'] ) ? sanitize_text_field( $_POST['current_section'] ) : 'general';

					if ( has_action( 'usb_swiper_save_section_' . $current_section ) ) {

						do_action( 'usb_swiper_save_section_' . $current_section );
						return true;
					}

					$settings = $_POST;

					unset( $settings['save'] );
					unset( $settings['action'] );
					unset( $settings['_nonce'] );
					unset( $settings['current_page'] );
					unset( $settings['current_section'] );
					unset( $settings['current_sub_section'] );
					unset( $settings['section'] );

					$setting_key   = 'usb_swiper_settings';
					$usb_swiper_settings = get_option( $setting_key, true );

					if ( ! empty( $usb_swiper_settings ) && is_array( $usb_swiper_settings ) ) {

						$section = ! empty( $_POST['section'] ) ? $_POST['section'] : '';

						if ( ! empty( $section ) ) {
							$usb_swiper_settings[ $current_section ][ $section ] = $settings;
						} else {
							$usb_swiper_settings[ $current_section ] = $settings;
						}

					} else {

						$usb_swiper_settings = array( $current_section => $settings );

						$section = ! empty( $_POST['section'] ) ? $_POST['section'] : '';
						if ( ! empty( $section ) ) {
							$usb_swiper_settings[ $current_section ][ $section ] = $settings;
						} else {
							$usb_swiper_settings[ $current_section ] = $settings;
						}
					}

					update_option( $setting_key, $usb_swiper_settings );

					do_action( 'usb_swiper_after_save_section_' . $current_section );

					self::add_message( __( 'Your settings has been saved.', 'usb-swiper' ) );
				} else {
					self::add_error( __( 'Nonce not verified.', 'usb-swiper' ) );
				}
			}
		}

		/**
         * Get general menu settings fields.
         *
         * @since 1.0.0
         *
		 * @return mixed|void
		 */
		public function get_general_fields() {

			$get_pages = $this->get_pages();

		    $fields = array(
			    array(
				    'type' => 'select',
				    'id' => 'virtual_terminal_page',
				    'name' => 'virtual_terminal_page',
				    'label' => __('Virtual Terminal Page', 'usb-swiper'),
				    'wrapper' => false,
				    'required' => true,
				    'options' => $get_pages,
				    'attributes' => '',
				    'description' => '',
				    'class' => 'regular-text',
				    'value' => '',
			    ),
			    array(
				    'type' => 'checkbox',
				    'id' => 'is_paypal_sandbox',
				    'name' => 'is_paypal_sandbox',
				    'label' => __('PayPal Sandbox', 'usb-swiper'),
				    'wrapper' => false,
				    'required' => false,
				    'attributes' => '',
				    'description' => '',
				    'class' => 'regular-text',
				    'value' => 'true',
			    ),
            );

		    return apply_filters('usb_swiper_get_general_fields', $fields);
		}

		/**
		 * General settings callback method.
         *
         * @since 1.0.0
		 */
		public function general_settings() {

			$settings = usb_swiper_get_settings('general');
			$get_fields = self::get_general_fields();

		    ?>
            <table class="form-table">
                <tbody>
                    <?php
                    if( !empty( $get_fields ) && is_array( $get_fields ) ) {
                        foreach ( $get_fields as $key => $get_field ) {
                            $type = !empty( $get_field['type'] ) ? $get_field['type'] : '';
                            $field_id = !empty( $get_field['id'] ) ? $get_field['id'] : '';
                            $label = !empty( $get_field['label'] ) ? $get_field['label'] : '';
                            unset($get_field['label']);
	                        $value = !empty( $settings[$field_id] ) ? esc_attr( $settings[$field_id] ) : '';
	                        if( 'checkbox' == $type ) {
	                            if( $value == $get_field['value'] ) {
		                            $get_field['checked'] = true;
	                            }
	                        } else {
		                        $get_field['value'] = $value;
	                        }
                            ?>
                            <tr>
                                <th for="<?php echo $field_id; ?>"><?php echo $label; ?></th>
                                <td>
                                    <?php echo usb_swiper_get_html_field($get_field); ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
            <?php
		}

		/**
         * Get partner fee table row html.
         *
         * @since 1.0.0
         *
		 * @param int $col_id
		 * @param array $args
		 *
		 * @return false|string
		 */
		public function get_partner_fee_tr( $col_id, $args = array() ) {

			$col_id = $col_id + 1;

			$get_countries = WC()->countries->get_countries();
			$display_title = false;
			if( $col_id == 1 ) {
				$display_title = true;
			}

			$country_code = !empty( $args['country_code'] ) ? $args['country_code'] : '';
			$percentage = !empty( $args['percentage'] ) ? $args['percentage'] : '';

			ob_start();
		    ?>
            <tr class="partner-fee-row partner-fee-row-<?php echo $col_id; ?>">
                <td class="counter">
                    <p><?php echo $col_id; ?></p>
                </td>
                <td class="country">
                    <?php if( $display_title ) { ?>
                      <label for="partner_fee_country_<?php echo $col_id; ?>"><?php _e('Country','usb-swiper'); ?></label>
                    <?php } ?>
                    <select required name="partner_fee_country_<?php echo $col_id; ?>" id="partner_fee_country_<?php echo $col_id; ?>">
                        <option value=""><?php _e('Select Country', 'usb-swiper'); ?></option>
                        <?php
                        if( !empty( $get_countries ) && is_array( $get_countries ) ) {
                            foreach ( $get_countries as $code => $country) {
                                ?>
                                <option <?php selected($country_code, $code); ?> value="<?php echo $code; ?>"><?php echo $country; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
                <td class="partner-fee-percentage">
	                <?php if( $display_title ) { ?>
                        <label for="partner_fee_percentage_<?php echo $col_id; ?>"><?php _e('Percentage(%)','usb-swiper'); ?></label>
	                <?php } ?>
                    <input type="number" min="0" step="any" minlength="0" max="100" maxlength="100" class="regular-text" name="partner_fee_percentage_<?php echo $col_id; ?>" id="partner_fee_percentage_<?php echo $col_id; ?>" value="<?php echo esc_attr($percentage); ?>" />
                </td>
                <td class="partner-fee-actions">
	                <?php if( $display_title ) { ?>
                        <label for="partner_fee_actions_<?php echo $col_id; ?>"><?php _e('Actions','usb-swiper'); ?></label>
	                <?php } ?>
                    <button type="button" class="remove-partner-fee button button-primary" data-nonce="<?php echo wp_create_nonce('remove-partner-fee'); ?>" data-id="<?php echo $col_id; ?>" name="remove_partner_fee_<?php echo $col_id; ?>"><?php _e('Remove','usb-swiper'); ?></button>
                </td>
            </tr>
            <?php

            $html = ob_get_contents();
            ob_get_clean();

            return $html;
		}

		/**
		 * Partner fees settings callback method.
		 *
		 * @since 1.0.0
		 */
		public function partner_fees_settings() {

			$settings = usb_swiper_get_settings('partner_fees');
			$total_fees = 0;
			if( isset( $settings['fees'] ) && is_array( $settings['fees'] ) ){
			    $total_fees = count( $settings['fees'] );
			}

			?>
            <div class="default-partner-fee-wrap">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th for="<?php echo 'default_partner_percentage'; ?>"><?php _e('Default Partner Fee','usb-swiper'); ?></th>
                            <td>
                                <?php echo usb_swiper_get_html_field(array(
	                                'type' => 'number',
	                                'id' => 'default_partner_percentage',
	                                'name' => 'default_partner_percentage',
	                                'label' => '',
	                                'wrapper' => false,
	                                'required' => false,
	                                'attributes' => array(
                                        'min' => 0,
                                        'step' => 'any',
                                        'minlength' => 0,
                                        'max' => 100,
                                        'maxlength' => 100,
                                    ),
	                                'description' => '',
	                                'class' => 'regular-text',
	                                'value' => !empty( $settings['default_partner_percentage'] ) ? $settings['default_partner_percentage'] : '',
                                ),); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="add-new-partner-fee-wrap">
                <button type="button" data-nonce="<?php echo wp_create_nonce('add-partner-fee-nonce'); ?>" class="button button-primary add-new-partner-fee-btn"><?php _e('Add New Fee', 'usb-swiper'); ?></button>
                <input type="hidden" name="partner_fee_total_row" id="partner_fee_total_row" value="<?php echo $total_fees; ?>">
            </div>
            <table class="form-table partner-fees striped widefat">
                <tbody>
                    <?php
                    if( !empty( $total_fees ) && $total_fees > 0 ) {
                        for ( $i = 0; $i < $total_fees; $i++ ) {
                            $args = !empty( $settings['fees'][$i] ) ? $settings['fees'][$i] : '';
	                        echo $this->get_partner_fee_tr($i, $args);
                        }
                    }
                    ?>
                </tbody>
            </table>
            <?php
		}

		/**
		 * Uninstall settings callback method.
		 *
		 * @since 1.0.0
		 */
		public function uninstall_settings() {

		    $settings = usb_swiper_get_settings('uninstall');

		    $remove_data_on_uninstall = !empty( $settings['remove_data_on_uninstall'] ) ? $settings['remove_data_on_uninstall'] : '';
		    ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="remove_data_on_uninstall"><?php _e('Remove data on uninstall?', 'usb-swiper'); ?></label></th>
                        <td>
                            <label>
                                <input <?php checked($remove_data_on_uninstall, '1'); ?> type="checkbox" name="remove_data_on_uninstall" id="remove_data_on_uninstall" class="regular-text" value="1">
                                <?php _e('Check this box if you would like to remove all of data when the plugin is deleted.','usb-swiper') ?>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
		}

		/**
		 * Ajax callback for add new partner fee.
		 *
		 * @since 1.0.0
		 */
		public function insert_new_partner_fee() {

			$status = false;
			$message = __('Something went wrong. Please try again.', 'usb-swiper' );
			$html = '';

			if( !empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'add-partner-fee-nonce') ) {
				$status = true;
				$row_id = !empty( $_POST['row_id'] ) ? esc_attr($_POST['row_id']) : 0;
				$html = $this->get_partner_fee_tr($row_id);
				$message = __('Partner fee added successfully.', 'usb-swiper' );
			}

			$response = array(
				'status' => $status,
				'html' => $html,
				'message' => $message,
			);

			wp_send_json( $response , 200 );
		}

		/**
		 * Save partner fees.
         *
         * @since 1.0.0
		 */
		public function save_partner_fees() {

			$setting_key   = 'usb_swiper_settings';
			$usb_swiper_settings = get_option( $setting_key, true );

		    $total_row = !empty( $_POST['partner_fee_total_row'] ) ? (int) $_POST['partner_fee_total_row'] : 0;
		    $default_partner_percentage = !empty( $_POST['default_partner_percentage'] ) ? $_POST['default_partner_percentage'] : '';

		    if( !empty( $total_row) && $total_row > 0 ) {

		        $fees = array();
		        for ( $i = 1; $i <= $total_row; $i++ ) {

		            $country_code =  !empty( $_POST['partner_fee_country_'.$i] ) ? $_POST['partner_fee_country_'.$i] : '';

		            if( !empty( $country_code ) ) {

		                if( !empty( $_POST['partner_fee_percentage_'.$i] ) ) {
			                $fees[] = array(
			                   'country_code' => $country_code,
			                   'percentage' => $_POST[ 'partner_fee_percentage_' . $i ],
                            );
		                }
		            }
		        }

			    $current_section = ! empty( $_POST['current_section'] ) ? sanitize_text_field( $_POST['current_section'] ) : 'general';

		        if( !empty( $usb_swiper_settings ) && is_array( $usb_swiper_settings ) ) {

			        $usb_swiper_settings[$current_section] = array(
				        'fees' => $fees,
			        );

		        } else {

			        $usb_swiper_settings = array(
				        $current_section => array(
				            'fees' => $fees,
                        )
                    );
		        }

			    $usb_swiper_settings[$current_section]['default_partner_percentage'] = $default_partner_percentage;

			    update_option( $setting_key, $usb_swiper_settings );

			    do_action( 'usb_swiper_after_save_section_' . $current_section );

			    self::add_message( __( 'Your settings has been saved.', 'usb-swiper' ) );
		    }
		}

		/**
		 * Remove partner fees.
		 *
		 * @since 1.0.0
		 */
		public function remove_partner_fee() {

			$status = false;
			$message = __('Something went wrong. Please try again.', 'usb-swiper' );
			$html = '';

			if( !empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'remove-partner-fee') ) {
				$status = true;
				$row_id = !empty( $_POST['row_id'] ) ? (int)esc_attr($_POST['row_id']) : 0;

				if( !empty( $row_id ) && $row_id > 0 ) {
					$partner_fee_settings   = usb_swiper_get_settings( 'partner_fees' );
					$partner_fees = !empty( $partner_fee_settings['fees'] ) ? $partner_fee_settings['fees'] : '' ;

					if( !empty( $partner_fees ) && is_array( $partner_fees ) ) {
						$unset_id = $row_id - 1;
						unset($partner_fees[$unset_id]);
						$fees = !empty( $partner_fees ) ? array_values( $partner_fees ) : array();
						$settings = get_option( 'usb_swiper_settings', true );
						$settings['partner_fees']['fees'] = $fees;
						update_option('usb_swiper_settings', $settings);

						$html = $this->get_partner_fees_all_tr();
					}
				}

				$message = __('Partner fee removed successfully.', 'usb-swiper' );
			}

			$response = array(
				'status' => $status,
				'html' => $html,
				'message' => $message,
			);

			wp_send_json( $response , 200 );
		}

		/**
         * Get all partner fee table row html.
         *
         * @since 1.0.0
         *
		 * @return string
		 */
		public function get_partner_fees_all_tr() {

			$partner_fee_settings = usb_swiper_get_settings( 'partner_fees' );
			$partner_fees = !empty( $partner_fee_settings['fees'] ) ? $partner_fee_settings['fees'] : '' ;

			$total_fees = 0;
			if( isset( $partner_fees ) && is_array( $partner_fees ) ){
				$total_fees = count( $partner_fees );
			}

			$fee_html = '';
			if( !empty( $total_fees ) && $total_fees > 0 ) {
				for ( $i = 0; $i < $total_fees; $i++ ) {
					$args = !empty( $partner_fees[$i] ) ? $partner_fees[$i] : '';
					$fee_html .= $this->get_partner_fee_tr($i, $args);
				}
			}

			return $fee_html;
		}

		public function logs_settings() {

		    $Usb_Swiper_Log = new Usb_Swiper_Log();
            $logs = $Usb_Swiper_Log->get_log_files();
			$log_filter = !empty( $_POST['log_filter']) ? $_POST['log_filter'] : $Usb_Swiper_Log->handle.'.log';
		    ?>
            <div class="usb-swiper-log-filter">
                <h2><?php echo esc_html($log_filter); ?></h2>
                <form method="post" name="log_filter_form" id="log_filter_form">
                    <select class="regular-text" name="log_filter" id="log_filter">
                        <?php
                        if( !empty( $logs ) && is_array( $logs ) ) {
                            foreach ( $logs as $log ) {
	                            if( !empty( $log ) ) {
		                            $log_to_array = explode( '.', $log );
		                            $file_ext     = ! empty( $log_to_array ) ? end( $log_to_array ) : '';
		                            if ( ! empty( $file_ext ) && 'log' === $file_ext ) {
			                            ?>
                                        <option <?php selected($log_filter, $log); ?> value="<?php echo $log; ?>"><?php echo $log; ?></option>
			                            <?php
		                            }
	                            }
                            }
                        }
                        ?>
                    </select>
                    <button class="button button-primary" type="submit" name="log_btn" id="log_btn"><?php _e('View Log', 'usb-swiper'); ?></button>
                </form>
            </div>
            <div class="jc-log-viewer">
                <pre><?php echo $Usb_Swiper_Log->get_log_content($log_filter); ?></pre>
            </div>
            <?php
		}

		public function add_customer_meta_fields( $user ) {

		    $user_id = !empty( $user->ID ) ? $user->ID : 0;

		    ?>
            <h2><?php _e('Currency Setting','usb-swiper') ?></h2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><?php _e('Currency','usb-swiper') ?></th>
                        <td>
	                        <?php
	                        echo  usb_swiper_get_html_field( array(
		                        'type' => 'select',
		                        'id' => 'TransactionCurrency',
		                        'name' => 'TransactionCurrency',
		                        'label' => '',
		                        'required' => true,
		                        'options' => usbswiper_get_currency_code_options(),
		                        'default' => usbswiper_get_default_currency( $user_id ),
		                        'attributes' => '',
		                        'description' => '',
		                        'readonly' => false,
		                        'disabled' => false,
		                        'class' => '',
		                        'wrapper' => false
	                        ));
	                        ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
		}

		public function save_customer_meta_fields( $user_id ) {

		    $currency = !empty( $_POST[ 'TransactionCurrency' ] ) ? $_POST[ 'TransactionCurrency' ] : 'USD';
			update_user_meta( $user_id, '_primary_currency',  $currency);
		}
	}
}
