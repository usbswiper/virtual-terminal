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
		private $options;

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
			wp_enqueue_style('select2-css', USBSWIPER_URL . 'assets/css/select2.css', array(), '4.1.0-rc.0');
			wp_enqueue_script('select2-js', USBSWIPER_URL . 'assets/js/select2.js', array('jquery'), '4.1.0-rc.0', true);
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
				'publicly_queryable' => false,
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
            $columns['transaction_type'] = __( 'Type', 'usb-swiper');
            $columns['date'] = $date;
            $columns['company'] = __('Company','usb-swiper');

			return $columns;
		}

		/**
		 * Set Query for Transaction Order By
		 *
		 * @since 1.1.9
		 *
		 * @param array $columns Get list table columns.
		 *
		 * @return array $columns
		 */
        public function transaction_custom_order_by($query){
	        if ( ! is_admin() )
		        return;
	        $orderby = $query->get( 'orderby');
	        if ( 'company' == $orderby ) {
		        $query->set( 'meta_key', 'company' );
		        $query->set( 'orderby', 'meta_value' );
	        }
	        if ( 'author' == $orderby ) {
		        $query->set( 'orderby', 'author' );
	        }
	        if ( 'transaction_id' == $orderby ) {
		        $query->set( 'meta_key', '_payment_response' );
		        $query->set( 'orderby', 'meta_value' );
	        }

	        if ( 'payment_status' == $orderby ) {
		        $query->set( 'meta_key', '_payment_status' );
		        $query->set( 'orderby', 'meta_value' );
	        }
            if ( 'transaction_type' == $orderby ) {
                $query->set( 'meta_key', '_transaction_type' );
                $query->set( 'orderby', 'meta_value' );
            }
	        if ( 'grand_total' == $orderby ) {
		        $query->set( 'meta_key', 'GrandTotal' );
		        $query->set( 'orderby', 'meta_value_num' );
	        }
            return $query;
        }

		/**
         * Sortable Columns in Transaction
         *
         * @since 1.1.17
         *
         * @param array $columns get all sortable column ids
         * @return mixed
		 */
        public function transactions_sortable_columns($columns){
	        $columns['transaction_id'] ='transaction_id';
	        $columns['grand_total'] = 'grand_total';
	        $columns['payment_status'] = 'payment_status';
	        $columns['company'] = 'company';
            $columns['transaction_type'] = 'transaction_type';
	        $columns['author'] = 'author';
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
			$payment_intent = usbswiper_get_transaction_type($post_id);
            $transaction_type = usbswiper_get_invoice_transaction_type($post_id);


            switch ( $column ) {
                case 'transaction_id':
                    $real_trans_id = usbswiper_get_transaction_id($post_id);
                    echo   !empty( $real_trans_id ) ? $real_trans_id : '';
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
                    $transaction_type = get_post_meta( $post_id, '_transaction_type', true);
				    $transaction_status = usbswiper_get_transaction_status($post_id);
					echo usbswiper_get_payment_status($transaction_status);
					break;
                case 'payment_intent' :
					echo !empty( $payment_intent ) ? strtoupper($payment_intent) : '';
                    break;
                case 'transaction_environment':
	                $environment = get_post_meta( $post_id, '_environment', true);
	                echo !empty( $environment ) ? strtoupper($environment) : '';
	                break;
                case 'company' :
                    $company = get_post_meta($post_id,'company',true);
                    echo $company;
                    break;
                case 'transaction_type' :
                    echo !empty( $transaction_type ) ? strtoupper($transaction_type) : '';
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

            $selected = 0;
            if( empty( $_GET['s'])) {
                $selected = !empty( $_GET['author'] ) ? $_GET['author']: 0;
            }

            wp_dropdown_users(
                array(
                    'name' => 'author',
                    'show_option_all' => __('All User Transactions','usb-swiper'),
                    'selected' => $selected,
                )
            );
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
		public function request_query_filter( $query ) {

            $current_page = isset( $_GET['post_type'] ) ? esc_attr( $_GET['post_type'] ) : '';

            if( is_admin() && !empty( $current_page ) && $this->post_type === $current_page ) {

                if( isset( $_GET['author'] ) && $_GET['author'] === '0' ) {
                    if (empty($query['s'])) {
                        unset($query['s']);
                    }
                }

                if( !empty( $_REQUEST['author'] ) && $_REQUEST['author'] > 0 ) {
                    if (empty($query['s'])) {
                        unset($query['s']);
                    }
                }

                if( !empty( $_GET['s'] ) ) {
                    $query['author'] = 0;
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

        /**
         * Exclude the form tab.
         *
         * @since    1.0.0
         *
         * @return mixed|null
         */
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
				    'type' => 'select',
				    'id' => 'vt_verification_page',
				    'name' => 'vt_verification_page',
				    'label' => __('VT Verification Page', 'usb-swiper'),
				    'wrapper' => false,
				    'required' => true,
				    'options' => $get_pages,
				    'attributes' => '',
				    'description' => '',
				    'class' => 'regular-text',
				    'value' => '',
			    ),
                array(
                    'type' => 'select',
                    'id' => 'vt_failure_page',
                    'name' => 'vt_failure_page',
                    'label' => __('Onboarding Failure Page', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'options' => $get_pages,
                    'attributes' => '',
                    'description' => '',
                    'class' => 'regular-text',
                    'value' => '',
                ),array(
                    'type' => 'select',
                    'id' => 'vt_paybyinvoice_page',
                    'name' => 'vt_paybyinvoice_page',
                    'label' => __('Pay by invoice Page', 'usb-swiper'),
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
                array(
                    'type' => 'text',
                    'id' => 'sandbox_merchant_id',
                    'name' => 'sandbox_merchant_id',
                    'label' => __('Sandbox Merchant ID', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter sandbox partner merchant id.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-sandbox',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'merchant_id',
                    'name' => 'merchant_id',
                    'label' => __('Merchant ID', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter partner merchant id.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-live',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'sandbox_client_id',
                    'name' => 'sandbox_client_id',
                    'label' => __('Sandbox Client ID', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter sandbox partner client id.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-sandbox',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'client_id',
                    'name' => 'client_id',
                    'label' => __('Client ID', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter partner client id.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-live',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'sandbox_client_secret',
                    'name' => 'sandbox_client_secret',
                    'label' => __('Sandbox Client Secret', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter sandbox partner client secret.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-sandbox',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'client_secret',
                    'name' => 'client_secret',
                    'label' => __('Client Secret', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter partner client secret.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-live',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'sandbox_attribution_id',
                    'name' => 'sandbox_attribution_id',
                    'label' => __('Sandbox Attribution ID', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter sandbox partner Attribution ID.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-sandbox',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'attribution_id',
                    'name' => 'attribution_id',
                    'label' => __('Attribution ID', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => true,
                    'attributes' => '',
                    'description' => __( 'Enter partner Attribution ID.','usb-swiper' ),
                    'class' => 'regular-text paypal-is-live',
                    'value' => 'true',
                ),
                array(
                    'type' => 'text',
                    'id' => 'paypal_partner_logo_url',
                    'name' => 'paypal_partner_logo_url',
                    'label' => __('Partner Logo URL', 'usb-swiper'),
                    'wrapper' => false,
                    'required' => false,
                    'attributes' => '',
                    'description' => __( 'Enter partner Logo URL. Default logo url https://www.usbswiper.com/img/usbswiper-logo-300x89.png','usb-swiper' ),
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
		 * @param int $col_id get column id
		 * @param array $args get all arguments
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
			$get_exclude_partner_users = get_option('get_exclude_partner_users', array());
			$get_users = get_users();
			$user_lists = array();
			if (!empty($get_users)) {
				foreach ($get_users as $key => $user) {
					$user_id = !empty($user->ID) ? $user->ID : '';
					$user_lists[$user_id] = !empty($user->display_name) ? $user->display_name : '';
				}
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
                        <tr>
                            <th for="<?php echo 'partner_checkbox_input'; ?>"><?php _e('Exclude Partner Fees', 'usb-swiper'); ?></th>
                            <td>
		                        <?php
		                        echo usb_swiper_get_html_field(array(
			                        'type' => 'multiselect',
			                        'id' => 'partner_checkbox_input',
			                        'name' => 'partner_checkbox_input',
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
			                        'class' => 'select2-original',
			                        'multiple' => 'multiple',
			                        'value' => $get_exclude_partner_users,
			                        'options' => $user_lists,
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
			$message = __('Nonce not verified. Please try again.', 'usb-swiper' );
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

			$fees = array();

            if( !empty( $total_row) && $total_row > 0 ) {

                for ( $i = 1; $i <= $total_row; $i ++ ) {

                    $country_code = ! empty( $_POST[ 'partner_fee_country_' . $i ] ) ? $_POST[ 'partner_fee_country_' . $i ] : '';

                    if ( ! empty( $country_code ) ) {

                        if ( ! empty( $_POST[ 'partner_fee_percentage_' . $i ] ) ) {
                            $fees[] = array(
                                'country_code' => $country_code,
                                'percentage'   => $_POST[ 'partner_fee_percentage_' . $i ],
                            );
                        }
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
			$is_partner_fee_exclude = !empty($_POST['partner_checkbox_input']) ? $_POST['partner_checkbox_input'] : '';
			if (!empty($_POST['is_partner_fee_exclude'])) {
				if (!empty($is_partner_fee_exclude) && is_array($is_partner_fee_exclude)) {
					$is_partner_fee_exclude[] = $user_id;
				} else {
					$is_partner_fee_exclude = array($user_id);
				}
			}
			update_option('get_exclude_partner_users', $is_partner_fee_exclude);
			do_action( 'usb_swiper_after_save_section_' . $current_section );

            self::add_message( __( 'Your settings has been saved.', 'usb-swiper' ) );
		}

		/**
		 * Remove partner fees.
		 *
		 * @since 1.0.0
		 */
		public function remove_partner_fee() {

			$status = false;
			$message = __('Nonce not verified. Please try again.', 'usb-swiper' );
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

		/**
		 * Manage USBSwiper Onboarding logs.
         *
         * @since 1.0.0
         *
         * @return void
         */
		public function logs_settings() {

		    $Usb_Swiper_Log = new Usb_Swiper_Log();
            $logs = $Usb_Swiper_Log->get_log_files();
            $default_log = !empty( $logs[0] ) ? $logs[0] : '';
			$log_filter = !empty( $_POST['log_filter']) ? $_POST['log_filter'] : $default_log;
			if( empty( $log_filter ) ) {
			    return;
			}
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

		/**
         * Add custom fields in user profile in admin area.
         *
         * @since 1.0.0
         *
		 * @param object $user Get user data.
		 */
		public function add_customer_meta_fields( $user ) {

		    $user_id = !empty( $user->ID ) ? $user->ID : 0;
		    if( empty( $user_id ) ) {
		        return;
		    }

			$merchant_data = usbswiper_get_onboarding_merchant_response($user_id);
			$get_countries = WC()->countries->get_countries();

		    ?>
            <h2><?php _e('PayPal Account Information','usb-swiper') ?></h2>
            <table class="form-table">
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
                    <tr>
                        <th><label for="brand_name"><?php _e('Company Name','usb-swiper' ); ?>:</label></th>
                        <td><input type="text" name="brand_name" value="<?php echo esc_attr(get_the_author_meta( 'brand_name', $user->ID )); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><?php _e('Exclude Partner Fees', 'usb-swiper') ?></th>
                        <td>
		                    <?php
		                    $get_exclude_partner_users = get_option('get_exclude_partner_users');

		                    $is_checked = false;
		                    if (!empty($get_exclude_partner_users) && is_array($get_exclude_partner_users) && in_array($user_id, $get_exclude_partner_users)) {
			                    $is_checked = true;
		                    }
		                    echo usb_swiper_get_html_field(array(
			                    'type' => 'checkbox',
			                    'id' => 'partner_checkbox_input',
			                    'name' => 'partner_checkbox_input',
			                    'label' => '',
			                    'required' => false,
			                    'default' => '',
			                    'checked' => $is_checked,
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

		/**
         * Save user custom field data.
         *
         * @since 1.0.0
         *
		 * @param int $user_id get $user_id
		 */
		public function save_customer_meta_fields( $user_id ) {

		    $currency = !empty( $_POST[ 'TransactionCurrency' ] ) ? $_POST[ 'TransactionCurrency' ] : 'USD';
			update_user_meta( $user_id, '_primary_currency',  $currency);
			$is_partner_fee_exclude = !empty($_POST['partner_checkbox_input']) ? true : false;

			$get_exclude_partner_users = get_option('get_exclude_partner_users', array());
            $get_exclude_partner_users = ! empty( $get_exclude_partner_users ) ? $get_exclude_partner_users : array();
			update_user_meta( $user_id,'brand_name', sanitize_text_field( $_POST['brand_name'] ) );
			$brand_name = get_the_author_meta( 'brand_name', $user_id );
			if (!empty($_POST['partner_checkbox_input'])) {
				if (!empty($get_exclude_partner_users) && is_array($get_exclude_partner_users)) {
					$get_exclude_partner_users[] = $user_id;
				} else {
					$get_exclude_partner_users = array($user_id);
				}
			} else {
                if( !empty( $get_exclude_partner_users ) ) {
                    $user_key = array_search($user_id, $get_exclude_partner_users);
                    unset($get_exclude_partner_users[$user_key]);
                }
			}

			update_option('get_exclude_partner_users', $get_exclude_partner_users);

            if( ! empty( $_POST['user-verify-vt-nonce'] ) && wp_verify_nonce( $_POST['user-verify-vt-nonce'],'user-verify-vt-nonce') ) {

                $user_verify_for_vt = ! empty( $_POST['user-verify-for-vt'] );

                update_user_meta( $user_id, 'vt_user_verification_status', $user_verify_for_vt );

                if( ! empty( $user_verify_for_vt ) ) {
                    $user_data    = get_user_by( 'id', $user_id );
                    $user_name    = !empty( $user_data->user_firstname ) ? $user_data->user_firstname : '';
                    update_user_meta( $user_id, 'verification_form_data', true );
                    $is_profile_approved = get_user_meta( $user_id, '_is_paypal_profile_approved', true);

                    if( !$is_profile_approved) {
                        $new_email = WC()->mailer()->emails['paypal_profile_verification_completed'];
                        $new_email->recipient = !empty($user_data->user_email) ? $user_data->user_email : '';
                        $new_email->trigger(array(
                            'user_id' => $user_id,
                            'user_name' => $user_name,
                        ));
                        update_user_meta($user_id, '_is_paypal_profile_approved', true);
                    }
                }
            }
		}

        /**
         * fixes Where clause for Trasaction Search in BackEnd
         *
         * @param $where
         * @param $query
         * @return array|mixed|string|string[]
         * @since 1.1.10
         *
         */
        public function transaction_search_query_replace(  $where, $query ){

            global $wpdb;

	        $transaction_search = !empty( $query->query_vars['transaction_search'] ) ? $query->query_vars['transaction_search'] : '';

            if ( '1' == $transaction_search){

                $table_prefix = $wpdb->prefix;

	            $where = preg_replace('/\s+/', '', $where);
	            $where = str_replace("AND(({$table_prefix}postmeta.meta_key='_payment_response'", "OR(({$table_prefix}postmeta.meta_key='_payment_response'", $where);
	            $where = str_replace( 'AND', ' AND ', $where);
	            $where = str_replace( 'OR', ' OR ', $where);
	            $where = str_replace( 'LIKE', ' LIKE ', $where);
	            $where = str_replace( '=', ' = ', $where);
            }

            return $where;
        }

        /**
         * Trasaction Search in BackEnd
         *
         * @param $query
         * @return mixed|void
         * @since 1.1.10
         *
         */
        public function transaction_search_query( $query ){
	        if ( ! is_admin() ) {
		        return $query;
	        }

            if ( 'transactions' == $query->get('post_type' ) && $query->is_search()){

                $query->set('transaction_search', true);
	            $query->set('meta_query', array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_payment_response',
                        'value'   => $query->get('s'),
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => '_payment_status',
                        'value'   => $query->get('s'),
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => 'TransactionType',
                        'value'   => $query->get('s'),
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => '_environment',
                        'value'   => $query->get('s'),
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => 'company',
                        'value'   => $query->get('s'),
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => 'GrandTotal',
                        'value'   => $query->get('s'),
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => '_transaction_type',
                        'value'   => $query->get('s'),
                        'compare' => 'LIKE',
                    ),
                ));

            }
        }

		/**
		 * Display Excluded Users List.
		 *
		 * @param $page
		 * @param $section
		 * @since 1.1.17
		 *
		 */
		public function display_partner_fees_exclude_user_list($page, $section)
		{
			$tab_field = !empty($_GET['tab']) ? $_GET['tab'] : '';
			$tab_page = !empty($page) ? $page : '';
			if ($tab_field == 'partner_fees' && $tab_page == 'usb-swiper') {
				$userTable = new Users_List_Table();
				echo '<div class="wrap"><h2>Users List Table</h2>';
				// Prepare table
				$userTable->prepare_items();
				// Display table
				$userTable->display();
				echo '</div>';
				//end wp list table
			}//main end if
		}

        /**
         * Adding verification tab fields in user edit page
         *
         * @param $user
         * @return void|null
         */
        public function register_settings_for_vt_verification($user)
        {
	        if ( ! current_user_can( 'edit_users' ) ) {
		        return null;
	        }

	        $user_id = ! empty( $user->ID ) ? $user->ID : 0;
            $verification_status = get_user_meta( $user_id, 'vt_user_verification_status', true );
            ?>
            <h3 id="verify_data"><?php _e('Verification Form Data','usb-swiper'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><label><?php _e('Verify User for VT','usb-swiper'); ?></label></th>
                    <td><input type="checkbox" name="user-verify-for-vt" <?php echo checked(true, $verification_status); ?> value="user-verify-for-vt"></td>
                    <td><input type="hidden" name="user-verify-vt-nonce" value="<?php echo wp_create_nonce('user-verify-vt-nonce'); ?>">
                </tr>
            </table>
            <?php
        }
	}
}
