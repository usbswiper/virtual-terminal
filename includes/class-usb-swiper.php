<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    usb-swiper
 * @subpackage usb-swiper/includes
 * @author     AngellEYE <andrew@angelleye.com>
 */
class Usb_Swiper {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Usb_Swiper_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The transactions post type of this plugin
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string $post_type The transactions post type.
	 */
	public $post_type = 'transactions';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = USBSWIPER_PLUGIN_NAME;
		$this->version = USBSWIPER_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_hooks();
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for manage logs for this plugin.
		 */
		require_once USBSWIPER_PATH.'includes/class-usb-swiper-log.php';

		/**
		 * Input fields of the plugin.
		 */
		require_once USBSWIPER_PATH.'includes/class-usb-swiper-input-fields.php';

		/**
		 * General functions of the plugin.
		 */
		require_once USBSWIPER_PATH.'includes/usb-swiper-functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once USBSWIPER_PATH.'includes/class-usb-swiper-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once USBSWIPER_PATH.'includes/class-usb-swiper-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once USBSWIPER_PATH.'/admin/class-usb-swiper-admin.php';
		/**
		 * The class responsible to manage User List Table.
		 */
		require_once USBSWIPER_PATH.'includes/class-usb-swiper-userlist-table.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once USBSWIPER_PATH.'/includes/usb-swiper-ppcp.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		 */
		require_once USBSWIPER_PATH.'/public/class-usb-swiper-public.php';

		$this->loader = new Usb_Swiper_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the class-usb-swiper-i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Usb_Swiper_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin and public area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {

		$plugin_admin = new Usb_Swiper_Admin($this->plugin_name, $this->version );
		$plugin_public = new Usb_Swiper_Public($this->plugin_name, $this->version );

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter('script_loader_tag', $plugin_public,'clean_paypal_checkout_sdk_url', 10, 2);
		$this->loader->add_action('init', $plugin_admin, 'register_transactions_post_type');
		$this->loader->add_action('init', $plugin_public, 'endpoint_init');
		$this->loader->add_filter('woocommerce_account_menu_items', $plugin_public, 'wc_account_menu_items');
		$this->loader->add_action('woocommerce_account_transactions_endpoint', $plugin_public, 'transactions_endpoint_cb');
		$this->loader->add_action('woocommerce_account_view-transaction_endpoint', $plugin_public, 'view_transactions_endpoint_cb');
        $this->loader->add_filter( 'woocommerce_get_query_vars', $plugin_public, 'update_wc_endpoints' );

		add_shortcode( 'usb_swiper_paypal_connect', array( $plugin_public, 'usb_swiper_paypal_connect') );
		add_shortcode( 'usb_swiper_vt_form', array( $plugin_public, 'usb_swiper_vt_form') );
		add_shortcode( 'usb_swiper_pay_by_invoice', array( $plugin_public, 'usb_swiper_pay_by_invoice') );

		$this->loader->add_action('template_redirect', $plugin_public, 'template_redirect');
		//$this->loader->add_action('usb_swiper_before_transactions', $plugin_public, 'paypal_disconnect_button');
		$this->loader->add_action('woocommerce_api_usb_swiper_transaction', $plugin_public, 'handle_usb_swiper_transaction');
		$this->loader->add_action('wp_logout', $plugin_public, 'wp_logout');
		$this->loader->add_action('woocommerce_edit_account_form_start', $plugin_public, 'wc_edit_account_form_start');
		$this->loader->add_action('woocommerce_edit_account_form', $plugin_public, 'wc_edit_account_form');
		$this->loader->add_action('woocommerce_save_account_details', $plugin_public, 'wc_save_account_details');
		$this->loader->add_action('woocommerce_before_edit_account_form', $plugin_public, 'wc_before_edit_account_form');

		$this->loader->add_action('wp_ajax_create_refund_request', $plugin_public,'create_refund_request');
		$this->loader->add_action('woocommerce_after_customer_login_form', $plugin_public,'display_paypal_connect_button');
		$this->loader->add_action('woocommerce_after_my_account', $plugin_public,'display_paypal_connect_button');
		$this->loader->add_action('wp_ajax_update_order_status', $plugin_public,'update_order_status');
		$this->loader->add_action('wp_ajax_nopriv_update_order_status', $plugin_public,'update_order_status');

		$this->loader->add_filter( 'woocommerce_email_classes',$plugin_public, 'add_paypal_connected_email' );
		$this->loader->add_filter( 'wp_login',$plugin_public, 'redirect_on_login',10,2 );
        $this->loader->add_filter( 'wp_ajax_add_vt_product_wrapper', $plugin_public, 'add_vt_product_wrapper');
        $this->loader->add_filter( 'wp_ajax_vt_search_product', $plugin_public, 'vt_search_product');
        $this->loader->add_filter( 'wp_ajax_vt_add_product_value_in_inputs', $plugin_public, 'vt_add_product_value_in_inputs');
        $this->loader->add_filter('usb_swiper_email_attachment', $plugin_public, 'manage_invoice_pdf_attachment', 10, 2 );
        $this->loader->add_action('wp_ajax_handel_pay_using_paypal_transaction', $plugin_public, 'handel_pay_using_paypal_transaction' );
        $this->loader->add_action('wp_ajax_nopriv_handel_pay_using_paypal_transaction', $plugin_public, 'handel_pay_using_paypal_transaction');

		if (!is_admin()) {
			return;
		}

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action('admin_init', $plugin_admin, 'check_plugin_dependency');
		$this->loader->add_action('admin_footer', $plugin_admin, 'admin_footer');
		$this->loader->add_action('plugin_action_links_'.USBSWIPER_BASENAME, $plugin_admin, 'plugin_action_links');
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_meta_boxes');
		$this->loader->add_action('admin_menu', $plugin_admin,'admin_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'save_settings');
		$this->loader->add_filter('manage_'.$this->post_type.'_posts_columns', $plugin_admin,'transactions_post_type_columns');
		$this->loader->add_action('manage_'.$this->post_type.'_posts_custom_column', $plugin_admin, 'transactions_column_html', 10, 2);
		$this->loader->add_action('restrict_manage_posts', $plugin_admin, 'manage_transactions_filter');
		$this->loader->add_filter('parse_query', $plugin_admin, 'parse_query_filter');
		$this->loader->add_action('usb_swiper_section_content_general', $plugin_admin, 'general_settings');
		$this->loader->add_action('usb_swiper_section_content_partner_fees', $plugin_admin, 'partner_fees_settings');
		$this->loader->add_action('usb_swiper_section_content_logs', $plugin_admin, 'logs_settings');
		$this->loader->add_action('usb_swiper_save_section_partner_fees', $plugin_admin, 'save_partner_fees');
		$this->loader->add_action('usb_swiper_section_content_uninstall', $plugin_admin, 'uninstall_settings');
		$this->loader->add_action('wp_ajax_insert_new_partner_fee', $plugin_admin, 'insert_new_partner_fee');
		$this->loader->add_action('wp_ajax_remove_partner_fee', $plugin_admin, 'remove_partner_fee');
		$this->loader->add_action( 'show_user_profile',  $plugin_admin, 'add_customer_meta_fields' );
		$this->loader->add_action( 'edit_user_profile',  $plugin_admin, 'add_customer_meta_fields' );
		$this->loader->add_action( 'personal_options_update', $plugin_admin, 'save_customer_meta_fields' );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'save_customer_meta_fields' );

		//Transaction Search in Backend
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'transaction_search_query' );
		$this->loader->add_action( 'posts_where', $plugin_admin, 'transaction_search_query_replace', 10, 2 );

		//Filter for Transactions Sorting
		$this->loader->add_filter('manage_edit-'.$this->post_type.'_sortable_columns', $plugin_admin,'transactions_sortable_columns');
		$this->loader->add_action('pre_get_posts',$plugin_admin,'transaction_custom_order_by');
		$this->loader->add_action('usb_swiper_after_form_content', $plugin_admin, 'display_partner_fees_exclude_user_list', 10, 2);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Usb_Swiper_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
}
