<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli
 * @subpackage Sejoli/includes
 */

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
 * @package    Sejoli
 * @subpackage Sejoli/includes
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Sejoli {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      SejoliSA_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

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
		if ( defined( 'SEJOLISA_VERSION' ) ) {
			$this->version = SEJOLISA_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sejoli';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_json_hooks();
		$this->register_cli();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SejoliSA_Loader. Orchestrates the hooks of the plugin.
	 * - SejoliSA_i18n. Defines internationalization functionality.
	 * - SejoliSA_Admin. Defines all hooks for the admin area.
	 * - SejoliSA_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SEJOLISA_DIR . '/includes/class-sejoli-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SEJOLISA_DIR . '/includes/class-sejoli-i18n.php';

		/**
		 * The class responsible for integrating with database
		 * @var [type]
		 */
		require_once SEJOLISA_DIR . '/includes/class-sejoli-database.php';

		/**
		 * The class responsible for defining database process and system logic
		 */
		require_once SEJOLISA_DIR. '/models/main.php';
		require_once SEJOLISA_DIR. '/models/acquisition.php';
		require_once SEJOLISA_DIR. '/models/affiliate.php';
		require_once SEJOLISA_DIR. '/models/confirmation.php';
		require_once SEJOLISA_DIR. '/models/coupon.php';
		require_once SEJOLISA_DIR. '/models/license.php';
		require_once SEJOLISA_DIR. '/models/order.php';
		require_once SEJOLISA_DIR. '/models/post.php';
		require_once SEJOLISA_DIR. '/models/reminder.php';
		require_once SEJOLISA_DIR. '/models/shipment.php';
		require_once SEJOLISA_DIR. '/models/statistic.php';
		require_once SEJOLISA_DIR. '/models/subscription.php';
		require_once SEJOLISA_DIR. '/models/tree.php';
		require_once SEJOLISA_DIR. '/models/user.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SEJOLISA_DIR . '/admin/access.php';
		require_once SEJOLISA_DIR . '/admin/acquisition.php';
		require_once SEJOLISA_DIR . '/admin/admin.php';
		require_once SEJOLISA_DIR . '/admin/affiliate.php';
		require_once SEJOLISA_DIR . '/admin/attachment.php';
		require_once SEJOLISA_DIR . '/admin/bulk-notification.php';
		require_once SEJOLISA_DIR . '/admin/checkout.php';
		require_once SEJOLISA_DIR . '/admin/confirmation.php';
		require_once SEJOLISA_DIR . '/admin/coupon.php';
		require_once SEJOLISA_DIR . '/admin/followup.php';
		require_once SEJOLISA_DIR . '/admin/integration.php';
		require_once SEJOLISA_DIR . '/admin/license.php';
		require_once SEJOLISA_DIR . '/admin/leaderboard.php';
		require_once SEJOLISA_DIR . '/admin/log.php';
		require_once SEJOLISA_DIR . '/admin/member.php';
		require_once SEJOLISA_DIR . '/admin/member-message.php';
		require_once SEJOLISA_DIR . '/admin/notification.php';
		require_once SEJOLISA_DIR . '/admin/order.php';
		require_once SEJOLISA_DIR . '/admin/payment.php';
		require_once SEJOLISA_DIR . '/admin/price.php';
		require_once SEJOLISA_DIR . '/admin/product.php';
		require_once SEJOLISA_DIR . '/admin/reminder.php';
		require_once SEJOLISA_DIR . '/admin/reset.php';
		require_once SEJOLISA_DIR . '/admin/restrict.php';
		require_once SEJOLISA_DIR . '/admin/shipment.php';
		require_once SEJOLISA_DIR . '/admin/social-proof.php';
		require_once SEJOLISA_DIR . '/admin/statistic.php';
		require_once SEJOLISA_DIR . '/admin/subscription.php';
		require_once SEJOLISA_DIR . '/admin/user.php';
		require_once SEJOLISA_DIR . '/admin/user-group.php';
		require_once SEJOLISA_DIR . '/admin/variant.php';
		require_once SEJOLISA_DIR . '/admin/bump-sales.php';
		require_once SEJOLISA_DIR . '/admin/facebook-conversion.php';
		require_once SEJOLISA_DIR . '/admin/tiktok-conversion.php';
		require_once SEJOLISA_DIR . '/admin/fast-checkout.php';
		require_once SEJOLISA_DIR . '/admin/checkout-script.php';
		require_once SEJOLISA_DIR . '/admin/ppn.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SEJOLISA_DIR . '/public/access.php';
		require_once SEJOLISA_DIR . '/public/acquisition.php';
		require_once SEJOLISA_DIR . '/public/affiliate.php';
		require_once SEJOLISA_DIR . '/public/affiliasi-komisi.php';
		require_once SEJOLISA_DIR . '/public/affiliasi-link.php';
		require_once SEJOLISA_DIR . '/public/affiliasi-help.php';
		require_once SEJOLISA_DIR . '/public/affiliasi-kupon.php';
		require_once SEJOLISA_DIR . '/public/affiliasi-order.php';
		require_once SEJOLISA_DIR . '/public/checkout.php';
		require_once SEJOLISA_DIR . '/public/compatibility.php';
		require_once SEJOLISA_DIR . '/public/confirm.php';
		require_once SEJOLISA_DIR . '/public/download.php';
		require_once SEJOLISA_DIR . '/public/endpoint.php';
		require_once SEJOLISA_DIR . '/public/followup.php';
		require_once SEJOLISA_DIR . '/public/integration.php';
		require_once SEJOLISA_DIR . '/public/leaderboard.php';
		require_once SEJOLISA_DIR . '/public/license.php';
		require_once SEJOLISA_DIR . '/public/login.php';
		require_once SEJOLISA_DIR . '/public/menu-walker.php';
		require_once SEJOLISA_DIR . '/public/member-message.php';
		require_once SEJOLISA_DIR . '/public/public.php';
		require_once SEJOLISA_DIR . '/public/product.php';
		require_once SEJOLISA_DIR . '/public/profile.php';
		require_once SEJOLISA_DIR . '/public/restrict.php';
		require_once SEJOLISA_DIR . '/public/register.php';
		require_once SEJOLISA_DIR . '/public/social-proof.php';
		require_once SEJOLISA_DIR . '/public/facebook-tiktok-conversion.php';

		/**
		 * The class responsible for defining all actions that work for json functions
		 */
		require_once SEJOLISA_DIR . '/json/main.php'; // MUST BE PUT FIRST
		require_once SEJOLISA_DIR . '/json/affiliate.php';
		require_once SEJOLISA_DIR . '/json/affiliate-network.php';
		require_once SEJOLISA_DIR . '/json/access.php';
		require_once SEJOLISA_DIR . '/json/commission.php';
		require_once SEJOLISA_DIR . '/json/confirmation.php';
		require_once SEJOLISA_DIR . '/json/coupon.php';
		require_once SEJOLISA_DIR . '/json/license.php';
		require_once SEJOLISA_DIR . '/json/order.php';
		require_once SEJOLISA_DIR . '/json/product.php';
		require_once SEJOLISA_DIR . '/json/reminder.php';
		require_once SEJOLISA_DIR . '/json/statistic.php';
		require_once SEJOLISA_DIR . '/json/subscription.php';
		require_once SEJOLISA_DIR . '/json/user.php';

		/**
		 * The files responsible for defining all functions that will work as helper
		 */
		require_once SEJOLISA_DIR . '/functions/access.php';
		require_once SEJOLISA_DIR . '/functions/acquisition.php';
		require_once SEJOLISA_DIR . '/functions/affiliate.php';
		require_once SEJOLISA_DIR . '/functions/cache.php';
		require_once SEJOLISA_DIR . '/functions/confirmation.php';
		require_once SEJOLISA_DIR . '/functions/coupon.php';
		require_once SEJOLISA_DIR . '/functions/formatting.php';
		require_once SEJOLISA_DIR . '/functions/license.php';
		require_once SEJOLISA_DIR . '/functions/notification.php';
		require_once SEJOLISA_DIR . '/functions/options.php';
		require_once SEJOLISA_DIR . '/functions/order.php';
		require_once SEJOLISA_DIR . '/functions/other.php';
		require_once SEJOLISA_DIR . '/functions/product.php';
		require_once SEJOLISA_DIR . '/functions/reminder.php';
		require_once SEJOLISA_DIR . '/functions/shipment.php';
		require_once SEJOLISA_DIR . '/functions/statistic.php';
		require_once SEJOLISA_DIR . '/functions/subscription.php';
		require_once SEJOLISA_DIR . '/functions/template.php';
		require_once SEJOLISA_DIR . '/functions/user.php';
		require_once SEJOLISA_DIR . '/functions/ajax.php';
		require_once SEJOLISA_DIR . '/functions/checkout.php';
		require_once SEJOLISA_DIR . '/functions/datetime.php';
		require_once SEJOLISA_DIR . '/functions/page.php';
		require_once SEJOLISA_DIR . '/functions/menu.php';

		require_once SEJOLISA_DIR . '/functions/user-group.php';

		require_once SEJOLISA_DIR . '/functions/facebook.php';
    	require_once SEJOLISA_DIR . '/functions/tiktok.php';

    	require_once SEJOLISA_DIR . '/functions/recaptcha.php';

		/**
		 * The class responsible for defining CLI command and function
		 * side of the site.
		 */
		require_once SEJOLISA_DIR . '/cli/access.php';
		require_once SEJOLISA_DIR . '/cli/ajax.php';
		require_once SEJOLISA_DIR . '/cli/affiliate.php';
		require_once SEJOLISA_DIR . '/cli/checkout.php';
		require_once SEJOLISA_DIR . '/cli/commission.php';
 		require_once SEJOLISA_DIR . '/cli/coupon.php';
		require_once SEJOLISA_DIR . '/cli/license.php';
		require_once SEJOLISA_DIR . '/cli/main.php';
		require_once SEJOLISA_DIR . '/cli/notification.php';
		require_once SEJOLISA_DIR . '/cli/order.php';
		require_once SEJOLISA_DIR . '/cli/options.php';
		require_once SEJOLISA_DIR . '/cli/product.php';
		require_once SEJOLISA_DIR . '/cli/statistic.php';
		require_once SEJOLISA_DIR . '/cli/user.php';

		$this->loader = new SejoliSA_Loader();
		SejoliSA\Database::connection();

		do_action('sejoli/init');
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the SejoliSA_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new SejoliSA_i18n();

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register CLI commands
	 * @since  1.0.0
	 * @return void
	 */
	private function register_cli() {

		if ( !class_exists( 'WP_CLI' ) ) :
			return;
		endif;

		$access       = new SejoliSA\CLI\Access();
		$ajax         = new SejoliSA\CLI\AJAX();
		$affiliate    = new SejoliSA\CLI\Affiliate();
		$checkout     = new SejoliSA\CLI\Checkout();
		$commission   = new SejoliSA\CLI\Commission();
		$coupon       = new SejoliSA\CLI\Coupon();
		$license      = new SejoliSA\CLI\License();
		$notification = new SejoliSA\CLI\Notification();
		$options      = new SejoliSA\CLI\Options();
		$order        = new SejoliSA\CLI\Order();
		$product      = new SejoliSA\CLI\Product();
		$statistic    = new SejoliSA\CLI\Statistic();
		$user         = new SejoliSA\CLI\User();

		WP_CLI::add_command('sejolisa access'		, $access);
		WP_CLI::add_command('sejolisa ajax'			, $ajax);
		WP_CLI::add_command('sejolisa affiliate'	, $affiliate);
		WP_CLI::add_command('sejolisa checkout'		, $checkout);
		WP_CLI::add_command('sejolisa commission'	, $commission);
		WP_CLI::add_command('sejolisa coupon'		, $coupon);
		WP_CLI::add_command('sejolisa license'		, $license);
		WP_CLI::add_command('sejolisa notification'	, $notification);
		WP_CLI::add_command('sejolisa options'		, $options);
		WP_CLI::add_command('sejolisa order'		, $order);
		WP_CLI::add_command('sejolisa product'		, $product);
		WP_CLI::add_command('sejolisa statistic'	, $statistic);
		WP_CLI::add_command('sejolisa user'			, $user);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$access = new SejoliSA\Admin\Access( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',									$access, 'register_post_type', 				90);
		$this->loader->add_action( 'carbon_fields_register_fields', 		$access, 'setup_carbon_fields', 			1009);
		$this->loader->add_filter( 'manage_sejoli-access_posts_columns',	$access, 'add_access_columns', 				100);
		$this->loader->add_action( 'manage_posts_custom_column',			$access, 'display_product_protection_data',	100, 2);

		$acquisition = new SejoliSA\Admin\Acquisition( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/general/fields',						$acquisition, 'setup_acquisition_setting_fields', 		50);
		$this->loader->add_filter( 'sejoli/order/meta-data', 					$acquisition, 'add_acquisition_data_to_order_meta',		200, 2);
		$this->loader->add_action( 'sejoli/order/new', 							$acquisition, 'update_acquisition_data', 				20);
		$this->loader->add_action( 'sejoli/order/set-status/in-progress', 		$acquisition, 'update_acquisition_data_to_sales',		110);
		$this->loader->add_action( 'sejoli/order/set-status/completed', 		$acquisition, 'update_acquisition_data_to_sales',		110);

		$admin = new SejoliSA\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'cron_schedules', 				$admin, 'register_custom_cron',				999);
		$this->loader->add_action( 'after_setup_theme',				$admin, 'load_carbon_fields', 		 		999);
		$this->loader->add_action( 'admin_notices',					$admin, 'display_help',				 		1);
		$this->loader->add_action( 'carbon_fields_register_fields',	$admin, 'setup_carbon_fields', 		 		10);
		$this->loader->add_filter( 'sejoli/general/container',		$admin, 'get_container', 			 		999);
		$this->loader->add_filter( 'sejoli/general/fields',			$admin, 'setup_main_setting_fields', 		10);
		$this->loader->add_filter( 'sejoli/general/fields',			$admin, 'setup_desain_setting_fields', 		10);
		$this->loader->add_filter( 'sejoli/general/fields',			$admin, 'setup_affiliate_setting_fields', 	20);
		$this->loader->add_filter( 'sejoli/general/fields',			$admin, 'setup_recaptcha_setting_fields', 	300);
		$this->loader->add_action( 'admin_enqueue_scripts',			$admin, 'enqueue_styles', 	 				999);
		$this->loader->add_action( 'admin_enqueue_scripts',			$admin, 'enqueue_scripts', 	 				999);
		$this->loader->add_action( 'wp_dashboard_setup',			$admin, 'remove_unneeded_widgets',			999);
		$this->loader->add_action( 'admin_init',					$admin, 'check_page_request',    			999);
		$this->loader->add_action( 'admin_bar_menu',				$admin, 'add_member_area_link',				9999);
		$this->loader->add_action( 'admin_head',					$admin, 'set_inline_style',		 			999);
		$this->loader->add_filter( 'sejoli/admin/is-sejoli-page',	$admin, 'is_sejoli_page',			 		999);

		$affiliate = new SejoliSA\Admin\Affiliate( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/admin/js-localize-data',				$affiliate, 'set_localize_js_var',	 		  1);
		$this->loader->add_filter( 'sejoli/product/fields',						$affiliate, 'setup_affiliate_setting_fields', 20);
		$this->loader->add_filter( 'sejoli/product/commission/fields',			$affiliate, 'setup_commission_fields',		1);
		$this->loader->add_filter( 'sejoli/product/meta-data',					$affiliate, 'setup_product_affiliate_data', 10);
		$this->loader->add_filter( 'sejoli/order/commission',					$affiliate, 'get_order_commission', 1, 3);
		$this->loader->add_action( 'sejoli/order/new',							$affiliate, 'set_commission', 10);
		$this->loader->add_action( 'sejoli/order/set-status/completed', 		$affiliate, 'update_status_to_added',		100);
		$this->loader->add_action( 'sejoli/order/set-status/refunded', 			$affiliate, 'update_status_to_cancelled',	100);
		$this->loader->add_action( 'sejoli/order/set-status/cancelled', 		$affiliate, 'update_status_to_cancelled',	100);
		$this->loader->add_action( 'sejoli/order/set-status/on-hold', 			$affiliate, 'update_status_to_pending',		100);
		$this->loader->add_action( 'sejoli/order/set-status/in-progress', 		$affiliate, 'update_status_to_pending',		100);
		$this->loader->add_action( 'sejoli/order/set-status/shipped', 			$affiliate, 'update_status_to_pending',		100);
		$this->loader->add_action( 'sejoli/checkout/affiliate/set',				$affiliate, 'set_affiliate_checkout',		100, 2);
		$this->loader->add_filter( 'sejoli/checkout/affiliate-data',			$affiliate, 'get_affiliate_checkout_data',	1);
		$this->loader->add_filter( 'sejoli/user/meta-data',				 		$affiliate, 'set_user_meta',			    200);
		$this->loader->add_filter( 'sejoli/user/fields',						$affiliate, 'add_affiliate_data_fields',	200);
		$this->loader->add_filter( 'sejoli/affiliate/uplines',					$affiliate, 'get_list_uplines',				1, 3);
		$this->loader->add_action( 'admin_menu',								$affiliate, 'register_admin_menu', 	 		1001);
		$this->loader->add_action( 'admin_enqueue_scripts',						$affiliate, 'set_css_and_js_files',			200);
		$this->loader->add_action( 'sejoli/commission/recheck',					$affiliate, 'recheck_commission',			1);
		$this->loader->add_filter( 'sejoli/notification/content/order-meta',	$affiliate, 'display_commission', 			10, 4);
		$this->loader->add_filter( 'sejoli/order/order-detail',					$affiliate, 'set_affiliate_data_to_order_detail', 	10);
		$this->loader->add_filter( 'sejoli/user/affiliate',							$affiliate, 'get_affiliate_id', 					99, 2);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-commission-csv-export',$affiliate, 'export_affiliate_commission_csv',		1);
		$this->loader->add_action( 'init', 										$affiliate, 'add_misplaced_commission_endpoint', 999 );
		$this->loader->add_filter( 'query_vars', 								$affiliate, 'set_misplaced_commission_vars',     999 );
		$this->loader->add_action( 'template_redirect', 						$affiliate, 'proceed_misplaced_commission',      999 );

		$attachment = new SejoliSA\Admin\Attachment( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/product/fields',					$attachment, 'setup_attachment_setting_fields', 40);
		$this->loader->add_filter( 'sejoli/product/meta-data',				$attachment, 'setup_product_file_data', 10);
		$this->loader->add_filter( 'sejoli/attachments/links',				$attachment, 'get_links', 1, 2);
		$this->loader->add_filter( 'sejoli/notification/email/attachments', $attachment, 'set_email_attachments', 10, 2);

		$bulk_notification = new SejoliSA\Admin\BulkNotification( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_ajax_sejoli-send-bulk-notification', $bulk_notification, 'send_notification', 1);
		$this->loader->add_action( 'admin_enqueue_scripts',					$bulk_notification, 'register_css_and_js', 1010);
		$this->loader->add_action( 'admin_menu',							$bulk_notification, 'register_admin_menu', 1010);

		$checkout = new SejoliSA\Admin\Checkout( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/checkout/shipment-calculate', $checkout, 'do_shipping_calculation', 	100);
		$this->loader->add_action( 'sejoli/checkout/calculate',			 $checkout, 'do_calculation',			999);
		$this->loader->add_action( 'sejoli/checkout/calculate-renew',	 $checkout, 'do_renew_calculation', 	999);
		$this->loader->add_action( 'sejoli/checkout/do',				 $checkout, 'do_checkout', 				999);
		$this->loader->add_action( 'sejoli/checkout/check-cookie',		 $checkout, 'check_cookie',				1);
		$this->loader->add_action( 'sejoli/checkout/renew',				 $checkout, 'renew', 					999);
		$this->loader->add_filter( 'sejoli/product/meta-data',			 $checkout, 'setup_form_product_meta',	 100, 2);
		$this->loader->add_filter( 'sejoli/product/fields',				 $checkout, 'setup_form_product_fields', 55);
		$this->loader->add_filter( 'sejoli/product/fields',				 $checkout, 'setup_desain_product_fields', 60);
		$this->loader->add_filter( 'sejoli/checkout/design/options',     $checkout, 'sejoli_modify_checkout_design_options', 60 );

		$confirmation = new SejoliSA\Admin\Confirmation( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_init',						$confirmation, 'register_cron_jobs',	100);
		$this->loader->add_action( 'admin_menu',						$confirmation, 'register_admin_menu', 	1025);
		$this->loader->add_action( 'sejoli/admin/js-localize-data',		$confirmation, 'set_localize_js_var', 	1);
		$this->loader->add_action( 'sejoli/confirmation/delete',		$confirmation, 'delete_data',			1);

		$coupon = new SejoliSA\Admin\Coupon( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',								$coupon, 'register_post_type', 	999);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',		$coupon, 'set_localize_js_var',	1);
		$this->loader->add_action( 'sejoli/coupon/create',				$coupon, 'create', 				100);
		$this->loader->add_action( 'sejoli/coupon/update',				$coupon, 'update', 				100);
		$this->loader->add_action( 'sejoli/coupon/update-status',		$coupon, 'update_status', 		100);
		$this->loader->add_action( 'sejoli/coupon/update-usage',		$coupon, 'update_usage', 		100);
		$this->loader->add_action( 'sejoli/coupon/recheck-use',			$coupon, 'update_total_usage',	1);
		$this->loader->add_action( 'sejoli/coupon/delete-post',			$coupon, 'delete_coupon_post',	1);
		$this->loader->add_filter( 'sejoli/coupon/value',				$coupon, 'set_coupon_value',	1, 4);
		$this->loader->add_action( 'admin_menu',						$coupon, 'register_admin_menu', 1003);
		$this->loader->add_action( 'admin_init',						$coupon, 'prepare_before_edit', 999);
		$this->loader->add_action( 'admin_notices',						$coupon, 'display_admin_notices', 1000);
		$this->loader->add_filter( 'sejoli/checkout/is-coupon-valid',	$coupon, 'validate_coupon_when_checkout', 1, 4);
		$this->loader->add_filter( 'sejoli/order/grand-total',			$coupon, 'set_discount', 		200, 2);
		$this->loader->add_action( 'carbon_fields_register_fields',		$coupon, 'setup_carbon_fields', 999);
		$this->loader->add_action( 'admin_footer',						$coupon, 'set_inline_js_editor', 999);
		$this->loader->add_action( 'save_post',							$coupon, 'save_coupon_data', 999);
		$this->loader->add_filter( 'sejoli/order/cart-detail',			$coupon, 'set_cart_detail', 10, 2);
		$this->loader->add_filter( 'sejoli/order/cart-detail',			$coupon, 'set_free_shipping_in_cart_detail',	20, 2);
		$this->loader->add_filter( 'sejoli/order/order-detail',			$coupon, 'set_coupon_data_to_order_detail', 	10);
		$this->loader->add_filter( 'sejoli/order/meta-data',			$coupon, 'set_order_meta',						200, 2);
		$this->loader->add_filter( 'sejoli/order/is-free-shipping',		$coupon, 'is_free_shipping',	1);

		$followup = new SejoliSA\Admin\FollowUp( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/admin/js-localize-data',		$followup, 'set_localize_js_var',	   		1);
		$this->loader->add_action( 'sejoli/product/fields',				$followup, 'setup_followup_setting_fields', 80);
		$this->loader->add_action( 'sejoli/product/meta-data', 			$followup, 'setup_followup_product_meta',   100, 2);
		$this->loader->add_action( 'sejoli/order/table/meta-data',		$followup, 'setup_order_table_metadata',	10, 2);

		$integration = new SejoliSA\Admin\Integration( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/admin/js-localize-data',			$integration, 'set_localize_js_var', 				10);
		$this->loader->add_action( 'sejoli/product/fields',					$integration, 'setup_fb_pixel_setting_fields', 		60);
		$this->loader->add_action( 'sejoli/product/fields',					$integration, 'setup_autoresponder_setting_fields', 70);
		$this->loader->add_filter( 'sejoli/product/meta-data',				$integration, 'setup_autoresponder_info', 			50, 2);
		$this->loader->add_filter( 'sejoli/order/set-status/completed',		$integration, 'register_autoresponder',  			200);
		$this->loader->add_filter( 'sejoli/order/set-status/in-progress',	$integration, 'register_autoresponder',  			200);

		$license = new SejoliSA\Admin\License( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded',							$license, 'check_license', 				1);
		$this->loader->add_action( 'sejoli/admin/js-localize-data',				$license, 'set_localize_js_var',		1);
		$this->loader->add_action( 'wp_ajax_sejoli-validate-license',			$license, 'validate_sejoli_license',	1);
		$this->loader->add_action( 'wp_ajax_sejoli-reset-license',				$license, 'reset_sejoli_license',		1);
		$this->loader->add_action( 'admin_notices',								$license, 'display_license_message', 		1);
		$this->loader->add_action( 'admin_notices',								$license, 'display_your_license_message', 	1);
		$this->loader->add_action( 'admin_init',								$license, 'register_routine',			1);
		$this->loader->add_action( 'admin_init',								$license, 'check_license_form',			1);
		$this->loader->add_action( 'admin_menu',								$license, 'register_license_form',			1);
		// DEMI ALLAH, SIAPAPUN YANG MENGAKALI LICENSE INI, SAYA TIDAK IKHLAS. REZEKI KELUARGA DAN ANAK SAYA ADA DISINI
		// SAYA HANYA MENDOAKAN SIAPAPUN YANG MENGAKALI LICENSE INI AGAR BERTAUBAT
		$this->loader->add_action( 'sejoli/license/berkah',						$license, 'check_license_routine',			1);
		$this->loader->add_filter( 'sejoli/product/fields',						$license, 'setup_license_setting_fields', 	50);
		$this->loader->add_action( 'sejoli/order/set-status/completed',			$license, 'prepare_to_create_license', 		100);
		$this->loader->add_action( 'sejoli/order/set-status/refunded', 			$license, 'update_status_to_inactive',		200);
		$this->loader->add_action( 'sejoli/order/set-status/cancelled', 		$license, 'update_status_to_inactive',		200);
		$this->loader->add_action( 'sejoli/order/set-status/on-hold', 			$license, 'update_status_to_inactive',		200);
		$this->loader->add_action( 'sejoli/license/create',						$license, 'create_license',		 			999, 3);
		$this->loader->add_action( 'sejoli/product/meta-data', 					$license, 'setup_product_meta',  			100, 2);
		$this->loader->add_filter( 'sejoli/license/code',						$license, 'generate_license_code',			999, 2);
		$this->loader->add_action( 'admin_menu',								$license, 'register_admin_menu', 			1005);
		$this->loader->add_action( 'admin_menu',								$license, 'register_your_license_menu',		99999);
		$this->loader->add_filter( 'sejoli/notification/content/order-meta',	$license, 'display_license_code', 			10, 4);
		$this->loader->add_filter( 'sejoli/license/quantity',					$license, 'get_license_quantity',	  		1, 2);
		$this->loader->add_filter( 'sejoli/license/availability',				$license, 'get_license_availability', 		1, 2);

		$leaderboard = new SejoliSA\Admin\Leaderboard( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu',								$leaderboard, 'register_admin_menu', 1005);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',				$leaderboard, 'set_localize_js_var', 1);

		$log = new SejoliSA\Admin\Log( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts',						$log, 'register_css_and_js',	100);
		$this->loader->add_action( 'admin_head',								$log, 'set_inline_style',		100);
		$this->loader->add_action( 'sejoli/log/write',							$log, 'write_log', 				1, 2);
		$this->loader->add_action( 'admin_menu',								$log, 'register_admin_menu', 	9999);
		$this->loader->add_action( 'sejoli/log/delete',							$log, 'delete_logs', 			100);
		$this->loader->add_action( 'wp_ajax_sejoli-read-log',					$log, 'get_log_content',		100);

		$member = new SejoliSA\Admin\Member( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action('after_setup_theme',						$member, 'register_nav_menu',			1);
		$this->loader->add_action('admin_head-nav-menus.php',				$member, 'register_menu_links', 		1);
		$this->loader->add_action('carbon_fields_register_fields',			$member, 'register_custom_fields', 		1);
		$this->loader->add_filter('theme_templates',						$member, 'add_member_template',			999, 4);

		$member_message = new SejoliSA\Admin\MemberMessage( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',										$member_message, 'register_post_type', 	110);
		$this->loader->add_action( 'carbon_fields_register_fields', 			$member_message, 'setup_carbon_fields', 1009);
		$this->loader->add_filter( 'manage_sejoli-memmessage_posts_columns',	$member_message, 'add_table_columns', 	100);
		$this->loader->add_action( 'manage_posts_custom_column',				$member_message, 'display_column_data',	100, 2);

		// $this->loader->add_filter('page_attributes_dropdown_pages_args',	$member, 'register_member_templates', 	1);
		// $this->loader->add_filter('wp_insert_post_data',					$member, 'register_member_templates', 	1);

		$notification = new SejoliSA\Admin\Notification( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/product/fields',				$notification, 'setup_product_setting_fields', 91);

		$this->loader->add_action( 'carbon_fields_register_fields',		$notification, 'setup_carbon_fields', 20);
		$this->loader->add_action( 'plugins_loaded',					$notification, 'prepare_media_libraries', 20);
		$this->loader->add_action( 'plugins_loaded',					$notification, 'prepare_libraries', 30);

		$this->loader->add_filter( 'sejoli/whatsapp/service-options',	$notification, 'set_whatsapp_service_options', 1);
		$this->loader->add_filter( 'sejoli/whatsapp/available-services',$notification, 'get_available_whatsapp_services', 1);
		
		// Order event trigger
		$this->loader->add_action( 'sejoli/order/set-status/on-hold',		$notification, 'send_on_hold_notification', 100);
		$this->loader->add_action( 'sejoli/notification/order/on-hold',		$notification, 'send_on_hold_notification', 100);
		$this->loader->add_action( 'sejoli/order/set-status/payment-confirm',	$notification, 'send_confirm_payment_notification', 100);
		$this->loader->add_action( 'sejoli/notification/order/confirm-payment',	$notification, 'send_confirm_payment_notification', 100);
		$this->loader->add_action( 'sejoli/order/set-status/in-progress',	$notification, 'send_in_progress_notification', 100);
		$this->loader->add_action( 'sejoli/notification/order/in-progress',	$notification, 'send_in_progress_notification', 100);
		$this->loader->add_action( 'sejoli/order/set-status/shipping',		$notification, 'send_shipping_notification', 100);
		$this->loader->add_action( 'sejoli/notification/order/shipping',	$notification, 'send_shipping_notification', 100);
		$this->loader->add_action( 'sejoli/order/set-status/completed',		$notification, 'send_completed_notification', 300);
		$this->loader->add_action( 'sejoli/notification/order/completed',	$notification, 'send_completed_notification_manually', 300);
		$this->loader->add_action( 'sejoli/order/set-status/refunded',		$notification, 'send_refunded_notification', 100);
		$this->loader->add_action( 'sejoli/notification/order/refunded',	$notification, 'send_refunded_notification', 100);
		$this->loader->add_action( 'sejoli/order/set-status/cancelled',		$notification, 'send_cancelled_notification', 100);
		$this->loader->add_action( 'sejoli/notification/order/cancelled',	$notification, 'send_cancelled_notification', 100);

		// Commission event trigger
		$this->loader->add_action( 'sejoli/commission/set-status/added',	$notification, 'send_active_commission_notification', 100, 2);
		$this->loader->add_action( 'sejoli/commission/set-status/paid',		$notification, 'send_commission_paid_notification', 100, 2);

		// Reguistration event trigger
		$this->loader->add_action( 'sejoli/notification/registration',		$notification, 'send_registration_notification', 100);

		// Send bulk notification
		$this->loader->add_action( 'sejoli/bulk-notification/process',		$notification, 'send_bulk_notification', 100, 2);

		// Prepare for reminder
		$this->loader->add_filter( 'sejoli/reminder/content',				$notification, 'prepare_for_reminder', 1, 3);
		$this->loader->add_action( 'sejoli/notification/reminder',			$notification, 'send_reminder', 1);

		$this->loader->add_filter( 'sejoli/notification/fields',					$notification, 'add_general_fields',		 10);
		$this->loader->add_filter( 'sejoli/notification/content',					$notification, 'set_notification_content', 	 1, 4);
		$this->loader->add_filter( 'sejoli/notification/available-media-libraries', $notification, 'get_available_media_libraries', 1);
		
		$order = new SejoliSA\Admin\Order( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_init',							$order, 'register_cron_jobs',		100);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',			$order, 'set_localize_js_var',	 	1);
		$this->loader->add_filter( 'sejoli/order/status',					$order, 'get_status', 			 	999);
		$this->loader->add_filter( 'sejoli/order/grand-total',				$order, 'calculate_total',  	 	100, 2);
		$this->loader->add_action( 'sejoli/order/cancel-incomplete-order',	$order, 'cancel_incomplete_order', 	100);
		$this->loader->add_action( 'sejoli/order/create',					$order, 'create',				 999);
		$this->loader->add_action( 'sejoli/order/renew',					$order, 'renew',				 999);
		$this->loader->add_filter( 'sejoli/order/meta-data',				$order, 'set_status_log_meta_data',	100, 2);
		$this->loader->add_action( 'sejoli/order/update-status',			$order, 'update_status',		 999, 2);
		$this->loader->add_action( 'sejoli/order/delete',					$order, 'create',				 999);
		$this->loader->add_action( 'sejoli/order/set-user',					$order, 'set_user',		 		 999);
		$this->loader->add_action( 'sejoli/order/set-coupon',				$order, 'set_coupon',		 	 999);
		$this->loader->add_action( 'sejoli/order/set-affiliate',			$order, 'set_affiliate',		 999);
		$this->loader->add_action( 'wp_ajax_sejoli-order-update',			$order, 'update_status_by_ajax', 999);
		$this->loader->add_action( 'admin_menu',							$order, 'register_admin_menu', 	 999);
		$this->loader->add_action( 'sejoli_ajax_sejoli-order-export',		$order, 'export_csv',			 1);

		$payment = new SejoliSA\Admin\Payment( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded',								$payment, 'load_libraries', 10);
		$this->loader->add_filter( 'sejoli/general/fields',							$payment, 'setup_payment_setting_fields', 40);
		$this->loader->add_filter( 'sejoli/order/grand-total',						$payment, 'set_price',		900, 2);
		$this->loader->add_filter( 'sejoli/order/meta-data',						$payment, 'set_meta_data',	100, 2);
		$this->loader->add_filter( 'sejoli/payment/module',							$payment, 'get_payment_module', 1);
		$this->loader->add_filter( 'sejoli/notification/content/order-meta',		$payment, 'display_payment_instruction', 100, 4);
		$this->loader->add_filter( 'sejoli/notification/content/payment-gateway',	$payment, 'display_simple_payment_instruction', 100, 4);
		$this->loader->add_filter( 'sejoli/payment/available-payment-gateways',		$payment, 'get_available_payment_gateways', 10);
		$this->loader->add_filter( 'sejoli/order/cart-detail',						$payment, 'set_cart_detail', 10, 2);
		$this->loader->add_filter( 'sejoli/order/order-detail',						$payment, 'set_payment_data_to_order_detail', 10);
		$this->loader->add_filter( 'sejoli/payment/fee',							$payment, 'get_payment_fee', 1, 2);

		$ppn = new SejoliSA\Admin\PPN( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/order/grand-total',			  $ppn, 'add_ppn_cost',	300, 2);
		$this->loader->add_filter( 'sejoli/order/cart-detail',			  $ppn, 'set_cart_detail', 10, 2);
		$this->loader->add_filter( 'sejoli/order/meta-data',			  $ppn, 'set_order_meta', 100, 2);
		$this->loader->add_filter( 'sejoli/order/order-detail',			  $ppn, 'add_ppn_info_in_order_data', 100);
		$this->loader->add_filter( 'sejoli/commission/order-grand-total', $ppn, 'reduce_with_ppn_cost', 1, 2);


		$price = new SejoliSA\Admin\Price( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/product/price',			$price, 'get_price',		1, 2);
		$this->loader->add_action( 'sejoli/product/pricing-plan',	$price, 'get_pricing_plan',	999, 2);

		$product = new SejoliSA\Admin\Product( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',												$product, 'register_post_type', 		80);
		$this->loader->add_action( 'carbon_fields_register_fields', 					$product, 'setup_carbon_fields', 		999);
		$this->loader->add_filter( 'display_post_states',								$product, 'display_product_states', 	10, 2);
		$this->loader->add_action( 'admin_enqueue_scripts',								$product, 'register_css_and_js',		1200);
		$this->loader->add_filter( 'manage_sejoli-product_posts_columns',				$product, 'add_product_columns',		50);
		$this->loader->add_action( 'manage_posts_custom_column',						$product, 'display_product_custom_columns',		50, 2);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',						$product, 'set_localize_js_var',	   			1);
		$this->loader->add_filter( 'sejoli/product/fields',								$product, 'setup_sale_setting_fields', 			10);
		$this->loader->add_filter( 'sejoli/product/meta-data',							$product, 'setup_product_meta', 				1, 2);
		$this->loader->add_filter( 'sejoli/checkout/is-product-valid',					$product, 'validate_product_when_checkout', 	1, 3);
		$this->loader->add_filter( 'sejoli/checkout/is-product-valid',					$product, 'check_previous_order_when_checkout', 2, 3);
		$this->loader->add_filter( 'sejoli/order/order-detail',							$product, 'set_product_data_to_order_detail', 	10);

		$reset = new SejoliSA\Admin\Reset( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_notices',				$reset, 'display_notice', 999);
		$this->loader->add_action( 'admin_menu',				$reset, 'register_reset_menu', 9999999);
		$this->loader->add_action( 'wp_ajax_sejoli-reset-data',	$reset, 'reset_data', 1);


		$restrict = new SejoliSA\Admin\Restrict( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'carbon_fields_register_fields', $restrict, 'setup_carbon_fields', 999);

		$reminder = new SejoliSA\Admin\Reminder( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',									$reminder, 'register_post_type', 		100);
		$this->loader->add_action( 'admin_init',							$reminder, 'register_routine',			100);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',			$reminder, 'set_localize_js_var',		1);
		$this->loader->add_action( 'admin_menu',							$reminder, 'register_log_menu',			100);
		$this->loader->add_action( 'sejoli/reminder/check',					$reminder, 'check_reminder_data',		100);
		$this->loader->add_action( 'sejoli/reminder/send',					$reminder, 'send_reminder_data',		100);
		$this->loader->add_action( 'sejoli/reminder/delete',				$reminder, 'delete_sent_reminder_log',	1);
		$this->loader->add_filter( 'sejoli/notification/shortcode',			$reminder, 'add_subscription_notification_shortcode', 100, 2);
		$this->loader->add_filter( 'manage_sejoli-reminder_posts_columns',	$reminder, 'add_reminder_columns', 		100);
		$this->loader->add_action( 'manage_posts_custom_column',			$reminder, 'display_custom_column_data',100, 2);
		$this->loader->add_action( 'admin_notices',							$reminder, 'check_table_exists', 		100);
		$this->loader->add_action( 'carbon_fields_register_fields', 		$reminder, 'setup_post_meta', 	 		100);
		$this->loader->add_action( 'admin_init',							$reminder, 'alter_table_reminder',		100);

		$shipment = new SejoliSA\Admin\Shipment( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded',								$shipment, 'register_libraries',		10);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',		 			$shipment, 'set_localize_js_var',	   	10);
		$this->loader->add_action( 'carbon_fields_theme_options_container_saved',	$shipment, 'delete_cache_data',			10);
		$this->loader->add_filter( 'sejoli/general/fields',							$shipment, 'setup_shipping_fields', 	30);
		$this->loader->add_filter( 'sejoli/product/fields',							$shipment, 'setup_setting_fields', 		30);
		$this->loader->add_filter( 'sejoli/shipment/subdistricts',					$shipment, 'get_subdistrict_options', 	100);
		$this->loader->add_filter( 'sejoli/shipment/available-couriers',			$shipment, 'get_available_couriers',			100);
		$this->loader->add_filter( 'sejoli/shipment/available-courier-services', 	$shipment, 'get_available_courier_services',	100);
		$this->loader->add_action( 'sejoli/shipment/calculation',					$shipment, 'calculate_shipment_cost',			1);
		$this->loader->add_action( 'sejoli/product/meta-data', 						$shipment, 'setup_product_meta',    			100, 2);
		$this->loader->add_action( 'wp_ajax_get-subdistricts',						$shipment, 'get_json_subdistrict_options', 		1);
		$this->loader->add_filter( 'sejoli/checkout/is-shipping-valid',				$shipment, 'validate_shipping_when_checkout', 	1, 4);
		$this->loader->add_filter( 'sejoli/order/need-shipment',					$shipment, 'set_order_needs_shipment',			1);
		$this->loader->add_filter( 'sejoli/order/grand-total',						$shipment, 'add_shipping_cost',					300, 2); // after coupon calculate
		$this->loader->add_filter( 'sejoli/order/grand-total',						$shipment, 'add_markup_price',					300, 2); // after coupon calculate
		$this->loader->add_filter( 'sejoli/order/cart-detail',						$shipment, 'set_cart_detail', 					10, 2);
		$this->loader->add_filter( 'sejoli/order/meta-data',						$shipment, 'set_order_meta',					100, 2);
		$this->loader->add_filter( 'sejoli/order/meta-data',						$shipment, 'set_markup_price_meta',				100, 2);
		$this->loader->add_filter( 'sejoli/order/order-detail',						$shipment, 'add_shipping_info_in_order_data',	100);
		$this->loader->add_filter( 'sejoli/notification/content/order-meta',		$shipment, 'add_shipping_info_in_notification', 10, 4);
		$this->loader->add_filter( 'sejoli/commission/order-grand-total',			$shipment, 'reduce_with_shipping_cost',			1, 2);

		$social_proof = new SejoliSA\Admin\SocialProof( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/product/fields',				$social_proof, 'setup_product_setting_fields', 90);
		$this->loader->add_action( 'admin_footer',						$social_proof, 'add_js_code',	90);

		$statistic = new SejoliSA\Admin\Statistic( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_dashboard_setup',					$statistic, 'display_statistic_widgets',		1);
		$this->loader->add_action( 'admin_footer',							$statistic, 'display_widget_template',			1);
		$this->loader->add_action( 'admin_footer',							$statistic, 'display_full_width_statistic',		1);
		$this->loader->add_action( 'admin_enqueue_scripts',					$statistic, 'set_css_and_js',					1000);
		$this->loader->add_action( 'load-edit.php',							$statistic, 'check_product_page',				100);
		$this->loader->add_filter( 'manage_sejoli-product_posts_columns',	$statistic, 'add_product_columns', 				100);
		$this->loader->add_action( 'manage_posts_custom_column',			$statistic, 'display_product_statistic_data',	100, 2);

		$subscription = new SejoliSA\Admin\Subscription( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu',								$subscription, 'register_admin_menu', 	 		999);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',				$subscription, 'set_localize_js_var',	 		1);
		$this->loader->add_filter( 'sejoli/product/price',						$subscription, 'set_product_price',				10, 2);
		$this->loader->add_filter( 'sejoli/order/cart-detail',					$subscription, 'set_data_to_cart_detail', 		3, 2);
		$this->loader->add_filter( 'sejoli/order/grand-total',					$subscription, 'set_order_total',				150, 2);
		$this->loader->add_action( 'sejoli/product/meta-data', 					$subscription, 'setup_product_meta',     		120, 2);
		$this->loader->add_filter( 'sejoli/order/type',							$subscription, 'set_order_type',	     		999, 2);
		$this->loader->add_action( 'sejoli/order/status-updated',				$subscription, 'prepare_subscription_data',		999, 1);
		$this->loader->add_filter( 'sejoli/order/set-status/completed',			$subscription, 'add_subscription_data',  		200);
		$this->loader->add_filter( 'sejoli/order/set-status/on-hold',			$subscription, 'set_subcription_pending',  		999);
		$this->loader->add_filter( 'sejoli/order/set-status/cancelled',			$subscription, 'set_subcription_pending',  		999);
		$this->loader->add_filter( 'sejoli/order/set-status/refunded',			$subscription, 'set_subcription_pending',  		999);
		$this->loader->add_filter( 'sejoli/order/set-status/in-progress',		$subscription, 'set_subcription_pending',  		999);
		$this->loader->add_filter( 'sejoli/order/set-status/shipped',			$subscription, 'set_subcription_pending',  		999);
		$this->loader->add_filter( 'sejoli/checkout/is-subscription-valid',		$subscription, 'validate_when_renew',			999, 3);
		$this->loader->add_filter( 'sejoli/notification/content/order-meta',	$subscription, 'display_subscription_date', 	40, 4);
		$this->loader->add_filter( 'sejoli/subscription/is-active',				$subscription, 'is_subscription_active',		1, 2);
		$this->loader->add_action( 'sejoli_ajax_sejoli-subscription-export',	$subscription, 'export_csv',					1);

		$user = new SejoliSA\Admin\User( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',								 $user, 'create_member_role',		   		1);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data',		 $user, 'set_localize_js_var',	   	   		1);
		$this->loader->add_action( 'admin_footer',						 $user, 'add_profile_js',			   		999);
		$this->loader->add_action( 'carbon_fields_register_fields',		 $user, 'setup_profile_fields',  	   		999);
		$this->loader->add_action( 'sejoli/user/register',				 $user, 'register',					   		100);
		$this->loader->add_filter( 'sejoli/user/fields',				 $user, 'add_basic_fields',		       		100);
		$this->loader->add_filter( 'sejoli/user/fields',				 $user, 'add_shipping_fields',		   		100);
		$this->loader->add_filter( 'sejoli/user/meta-data',				 $user, 'declare_user_meta',			   	1);
		$this->loader->add_filter( 'sejoli/user/meta-data',				 $user, 'set_user_meta',			   		999);
		$this->loader->add_filter( 'sejoli/user/phone',				     $user, 'translate_phone_number',	   		1999, 2);
		$this->loader->add_filter( 'sejoli/checkout/is-user-data-valid', $user, 'validate_user_when_checkout', 		100, 2);
		$this->loader->add_filter( 'sejoli/checkout/user-data',			 $user, 'get_user_data_when_checkout', 		100, 2);
		$this->loader->add_filter( 'sejoli/order/order-detail',			 $user, 'set_user_data_to_order_detail', 	10);
		$this->loader->add_filter( 'show_admin_bar',					 $user, 'hide_admin_bar',				 	1);
		$this->loader->add_action( 'admin_init',						 $user, 'disable_admin_access',				1);
		$this->loader->add_filter( 'manage_users_columns', 				 $user, 'modify_user_table', 				1);
		$this->loader->add_filter( 'manage_users_custom_column', 		 $user, 'display_value_for_custom_table',   10, 3 );
		$this->loader->add_filter( 'user_row_actions',					 $user, 'display_detail_affiliate_link',	10, 4);
		$this->loader->add_action( 'admin_menu',						 $user, 'detail_user_network_page',    	10);
		$this->loader->add_filter( 'sejoli/user/affiliate-user',		 $user, 'get_affiliate_users', 	100);
		$this->loader->add_action( 'show_user_profile', 				 $user, 'set_user_affiliate', 10 );
		$this->loader->add_action( 'edit_user_profile', 				 $user, 'set_user_affiliate', 10 );
		$this->loader->add_action( 'personal_options_update', 			 $user, 'save_user_affiliate_profile_fields' );
		$this->loader->add_action( 'edit_user_profile_update', 			 $user, 'save_user_affiliate_profile_fields' );

		$usergroup = new SejoliSA\Admin\UserGroup( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',								 $usergroup, 'register_post_type', 		1010);
		$this->loader->add_filter( 'sejoli/admin/js-localize-data', 	 $usergroup, 'set_localize_js_var',		1);
		$this->loader->add_action( 'admin_menu',						 $usergroup, 'add_custom_user_menu',	100);
		$this->loader->add_action( 'admin_enqueue_scripts',				 $usergroup, 'register_css_and_js',		100);
		$this->loader->add_filter( 'sejoli/admin/is-sejoli-page',		 $usergroup, 'is_sejoli_page', 			1000);
		$this->loader->add_action( 'carbon_fields_register_fields',		 $usergroup, 'setup_group_fields',  	1010);
		$this->loader->add_filter( 'sejoli/product/fields',				 $usergroup, 'setup_group_setting_fields', 	25);
		$this->loader->add_filter( 'sejoli/product/meta-data',			 $usergroup, 'setup_group_product_meta',   	50, 2);
		$this->loader->add_action( 'sejoli/user/fields',		 		 $usergroup, 'setup_user_fields',  			20);
		$this->loader->add_filter( 'sejoli/user/meta-data',				 $usergroup, 'set_user_meta',				1001);
		$this->loader->add_action( 'pre_get_posts',								$usergroup, 'sort_group_by_priority',		1);
		$this->loader->add_filter( 'manage_sejoli-user-group_posts_columns',	$usergroup, 'modify_group_columns', 		1);
		$this->loader->add_action( 'manage_posts_custom_column',		 		$usergroup, 'display_custom_data_in_table',	100, 2);
		$this->loader->add_filter( 'manage_users_columns', 				 		$usergroup, 'modify_user_table', 			10);
		$this->loader->add_filter( 'manage_users_custom_column', 		 		$usergroup, 'display_value_for_user_table', 20, 3 );
		$this->loader->add_filter( 'sejoli/product/price',						$usergroup, 'set_discount_product_price',	100, 2);
		$this->loader->add_filter( 'sejoli/order/commission', 					$usergroup, 'set_affiliate_commission',		100, 5);
		$this->loader->add_action( 'sejoli/order/set-status/completed', 		$usergroup, 'update_user_group',			199);
		$this->loader->add_action( 'sejoli_ajax_sejoli-user-export',			$usergroup, 'export_csv',			 		1);
		$this->loader->add_filter( 'sejoli/user/roles',							$usergroup, 'set_role_names',				1, 2);

		$variant = new SejoliSA\Admin\Variant( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/product/fields',						$variant, 'setup_variant_product_fields', 	 10);
		$this->loader->add_filter( 'sejoli/product/meta-data',					$variant, 'setup_variant_product_meta', 	 200, 2);
		$this->loader->add_filter( 'sejoli/variant/are-variants-valid',			$variant, 'validate_variants_when_checkout', 1, 2);
		$this->loader->add_filter( 'sejoli/order/grand-total',					$variant, 'set_grand_total',				 101, 2);
		$this->loader->add_filter( 'sejoli/order/cart-detail',					$variant, 'set_data_to_cart_detail', 		 5, 2);
		$this->loader->add_filter( 'sejoli/order/meta-data',					$variant, 'set_data_to_order_meta_data',	 100, 2);
		$this->loader->add_filter( 'sejoli/product/weight',						$variant, 'set_product_weight', 			 1, 2);
		$this->loader->add_filter( 'sejoli/notification/content/order-meta', 	$variant, 'display_data_to_notification',	 5, 4);

		$bump_sales = new SejoliSA\Admin\BumpSales( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/product/fields', $bump_sales, 'setup_product_bump_sales_setting_fields', 10);
		$this->loader->add_filter( 'display_post_states', 	$bump_sales, 'display_product_states', 11);

		$facebook_conversion = new SejoliSA\Admin\FacebookConversion( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/product/fields',	$facebook_conversion, 'setup_fb_conversion_setting_fields', 60);

		$tiktok_conversion = new SejoliSA\Admin\TiktokConversion( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/product/fields',	$tiktok_conversion, 'setup_tiktok_conversion_setting_fields', 60);

		$fast_checkout = new SejoliSA\Admin\Fast_Checkout( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/product/fields', $fast_checkout, 'setup_setting_fields', 60);

		$checkout_script = new SejoliSA\Admin\CheckoutScript( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action('admin_init', $checkout_script, 'sejoli_register_settings');
		$this->loader->add_action('admin_menu', $checkout_script, 'sejoli_add_admin_menu', 1025);
		$this->loader->add_filter('pre_update_option_sejoli_checkout_script', $checkout_script, 'sejoli_generate_checkout_script', 10, 3);


	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$access = new SejoliSA\Front\Access( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp',						$access, 'check_access_page', 1);
		$this->loader->add_filter( 'sejoli/access/has-access',	$access, 'does_user_has_access', 1, 3);
		$this->loader->add_filter( 'template_include', $access, 'sejoli_custom_template_include', 99 );

		$acquisition = new SejoliSA\Front\Acquisition( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/affiliate/cookie-data',	$acquisition, 'add_acquisition_data_to_cookie', 100, 3);

		$affiliate = new SejoliSA\Front\Affiliate( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',	 						$affiliate, 'set_endpoint', 		999);
		$this->loader->add_filter( 'query_vars',					$affiliate, 'set_query_vars',		999);
		$this->loader->add_action( 'sejoli/affiliate/set-cookie',	$affiliate, 'set_cookie',			1);
		$this->loader->add_action( 'sejoli/affiliate/cookie-data',	$affiliate, 'set_coupon_to_cookie',	20, 3);
		$this->loader->add_action( 'sejoli/affiliate/redirect',		$affiliate, 'redirect',				999);
		$this->loader->add_action( 'parse_query',					$affiliate, 'check_parse_query',	999);
		$this->loader->add_filter( 'sejoli/affiliate/link',			$affiliate, 'set_affiliate_link',	1, 3);

		$compatibility 	= new SejoliSA\Front\Compatibility( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'plugins_loaded',			$compatibility, 'check_loaded_plugins'	, 999);
		$this->loader->add_action( 'after_setup_theme',			$compatibility, 'check_current_theme' 	, 999);
		$this->loader->add_filter( 'sejoli/css/permissions',	$compatibility, 'modify_css_enqeueu'	, 999);
		$this->loader->add_filter( 'sejoli/js/permissions',		$compatibility, 'modify_js_enqeueu'		, 999);

		$download = new SejoliSA\Front\Download( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',						$download,'set_endpoint',		  999);
		$this->loader->add_action( 'parse_query',				$download,'check_parse_query', 	  999);
		$this->loader->add_filter( 'query_vars',				$download,'set_query_vars', 	  999);

		$endpoint = new SejoliSA\Front\Endpoint( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',						$endpoint,'set_endpoint',		  999);
		$this->loader->add_action( 'parse_query',				$endpoint,'check_parse_query', 	  999);
		$this->loader->add_action( 'template_redirect',			$endpoint,'check_page_request',   999);
		$this->loader->add_filter( 'query_vars',				$endpoint,'set_query_vars', 	  999);
		$this->loader->add_filter( 'template_include',			$endpoint,'set_template_part',	  999);
		$this->loader->add_filter( 'body_class',				$endpoint,'add_body_classes', 	  999);
		$this->loader->add_filter( 'sejoli/enable',				$endpoint,'set_enable_framework', 10);
		// $this->loader->add_filter( 'sejoli/template-file',	$endpoint,'get_template_file',	  999);
		$this->loader->add_filter( 'sejoli/get-request',		$endpoint,'get_request',		  999);

		$followup = new SejoliSA\Front\FollowUp( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',	 						$followup, 'set_endpoint', 		999);
		$this->loader->add_filter( 'query_vars',					$followup, 'set_query_vars',	999);
		$this->loader->add_action( 'parse_query',					$followup, 'check_parse_query',	999);


		$public = new SejoliSA\Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'template_redirect', 	$public, 'redirect_to_member_area', 100);
		$this->loader->add_action( 'template_redirect',		$public, 'check_using_member_page',	200);
		$this->loader->add_action( 'template_redirect', 	$public, 'redirect_member_area_alihkan_url_setelah_login', 300);
		$this->loader->add_action( 'wp_enqueue_scripts', 	$public, 'enqueue_styles', 			999);
		$this->loader->add_action( 'wp_enqueue_scripts', 	$public, 'enqueue_scripts', 		999);
		$this->loader->add_filter( 'body_class',			$public, 'set_body_classes',		999);
		$this->loader->add_action( 'wp_head',				$public, 'add_inline_style',		1000);
		$this->loader->add_action( 'sejoli/set-messages', 	$public, 'set_messages',			999, 2);
		$this->loader->add_action( 'query_vars', 			$public, 'add_query_vars', 			999 );
		$this->loader->add_action( 'init', 					$public, 'add_endpoint', 			999 );
		$this->loader->add_action( 'parse_request', 		$public, 'add_ajax_action', 		999 );
		$this->loader->add_filter( 'sejoli/enable',			$public, 'enable_semantic',			100 );
		$this->loader->add_filter( 'template_include',		$public, 'view_member_template',	99 );

		$integration = new SejoliSA\Front\Integration( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts',	$integration, 'set_localize_js_vars',	1000);

		$license = new SejoliSA\Front\License( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',	 						$license, 'set_endpoint', 		999);
		$this->loader->add_filter( 'query_vars',					$license, 'set_query_vars',		999);
		$this->loader->add_action( 'parse_query',					$license, 'check_parse_query',	999);

		$login = new SejoliSA\Front\Login( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'register_url',			$login, 'register_url',			999);
		$this->loader->add_filter( 'login_url',				$login, 'login_url',			999);
		$this->loader->add_action( 'login_enqueue_scripts',	$login, 'modify_login_form',	100);
		$this->loader->add_filter( 'login_headerurl',		$login, 'login_header_url',		999);
		$this->loader->add_filter( 'login_headertitle',		$login, 'login_header_title',	100);
		$this->loader->add_action( 'login_footer',			$login, 'add_js_script',		1);
		$this->loader->add_action( 'template_redirect',		$login,	'check_user_login',		999);
		$this->loader->add_action( 'sejoli/login',			$login, 'check_login',			999);
		$this->loader->add_action( 'sejoli/login/rp',		$login, 'info_reset_password',	999);


		$register = new SejoliSA\Front\Register( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts',		$register, 'enqueue_scripts',		999);
		$this->loader->add_action( 'sejoli/register',			$register,'submit_register',		999);

		$product = new SejoliSA\Front\Product( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli_ajax_check-checkout-code-access', $product, 'check_access_code_by_ajax');

		$restrict = new SejoliSA\Front\Restrict( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp',				$restrict, 'check_if_page_is_protected', 1);

		$checkout = new SejoliSA\Front\Checkout( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'single_template',					$checkout, 'set_single_template',	100);
		$this->loader->add_filter( 'single_template',					$checkout, 'set_close_template',	9999);
		$this->loader->add_action( 'wp_enqueue_scripts',				$checkout, 'enqueue_scripts',		999);
		$this->loader->add_filter( 'query_vars', 						$checkout, 'custom_query_vars', 	999 );
		$this->loader->add_action( 'parse_request',						$checkout, 'get_calculate_by_ajax', 999);
		$this->loader->add_action( 'parse_request',						$checkout, 'get_renew_calculate_by_ajax',  999);
		$this->loader->add_action( 'parse_request',						$checkout, 'get_payment_gateway_by_ajax',  999);
		$this->loader->add_action( 'parse_request',						$checkout, 'get_shipping_methods_by_ajax', 999);
		$this->loader->add_action( 'parse_request',						$checkout, 'get_subdistrict_by_ajax', 	999);
		$this->loader->add_action( 'parse_request',						$checkout, 'apply_coupon_by_ajax', 		999);
		$this->loader->add_action( 'parse_request',						$checkout, 'submit_checkout_by_ajax', 	999);
		$this->loader->add_action( 'parse_request',						$checkout, 'submit_login_by_ajax', 		999);
		$this->loader->add_action( 'parse_request',						$checkout, 'get_current_user_by_ajax', 	999);
		$this->loader->add_action( 'parse_request',						$checkout, 'delete_coupon_by_ajax', 	999);
		$this->loader->add_action( 'parse_request',						$checkout, 'check_requested_page', 		999);
		$this->loader->add_action( 'template_redirect',					$checkout, 'check_requested_template',	800);
		$this->loader->add_action( 'template_include',					$checkout, 'set_template_file',			999);
		$this->loader->add_action( 'parse_request',						$checkout, 'setup_checkout_renew',		999);
		$this->loader->add_action( 'wp_footer',							$checkout, 'trigger_coupon_fill',		999);
		// $this->loader->add_action( 'parse_request',						 			$checkout, 'check_if_thankyou_page', 999);
		// $this->loader->add_action( 'parse_request',						 			$checkout, 'check_if_checkout_renew', 999);

		$this->loader->add_action( 'parse_request',						 			$checkout,'loading_by_ajax', 999);
		$this->loader->add_action( 'sejoli/frontend/checkout/calculate', 			$checkout,'calculate', 999);
		$this->loader->add_filter( 'sejoli/frontend/checkout/payment-gateway',  	$checkout,'payment_gateway', 999, 2);
		$this->loader->add_action( 'sejoli/frontend/checkout/apply-coupon', 		$checkout,'apply_coupon', 999, 2);
		$this->loader->add_action( 'sejoli/frontend/checkout/delete-coupon', 		$checkout,'delete_coupon', 999 );
		$this->loader->add_filter( 'sejoli/frontend/checkout/current-user', 		$checkout,'current_user', 999, 2);
		$this->loader->add_action( 'sejoli/frontend/checkout/loading', 	 			$checkout,'loading', 999 );
		$this->loader->add_filter( 'sejoli/frontend/checkout/shipping-methods',  	$checkout,'shipping_methods', 999, 2);
		$this->loader->add_action( 'parse_request',						 			$checkout,'check_user_email_by_ajax', 999);
		$this->loader->add_action( 'parse_request',						 			$checkout,'check_user_phone_by_ajax', 999);
		$this->loader->add_action( 'template_redirect',						 		$checkout,'close_checkout', 999);
		$this->loader->add_filter( 'sejoli/checkout/design/style', 		 			$checkout, 'sejoli_checkout_default_designs', 10, 1);
		$this->loader->add_filter( 'sejoli/checkout/design/template', 				$checkout, 'sejoli_checkout_template_filter', 10, 3);
		$this->loader->add_filter( 'sejoli/checkout/design/thankyou', 				$checkout, 'sejoli_checkout_thankyou_filter', 10, 5);

		$confirm = new SejoliSA\Front\Confirm( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( 'query_vars', 						 $confirm,'custom_query_vars', 999 );
		$this->loader->add_action( 'parse_request',						 $confirm,'display_confirm_page', 999);
		$this->loader->add_action( 'parse_request',						 $confirm,'confirm_by_ajax', 999);

		$affiliasi_komisi = new SejoliSA\Front\Affiliasi_Komisi( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'sejoli_ajax_get-commission', 		$affiliasi_komisi,'ajax_get_commission_current_user',999);
		$this->loader->add_action( 'sejoli_ajax_get-commission-detail', $affiliasi_komisi,'ajax_get_commission_detail',999);

		$affiliasi_link = new SejoliSA\Front\Affiliasi_Link( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'sejoli_ajax_get-affiliate-link', 	$affiliasi_link,'ajax_get_affiliate_link',999);

		$affiliasi_help = new SejoliSA\Front\Affiliasi_Help( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'sejoli_ajax_get-affiliate-help', 		$affiliasi_help,'ajax_get_affiliate_help',999);
		$this->loader->add_action( 'sejoli_ajax_get-affiliate-help-detail', $affiliasi_help,'ajax_get_affiliate_help_detail',999);

		$affiliasi_kupon = new SejoliSA\Front\Affiliasi_Kupon( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'sejoli_ajax_get-affiliate-coupon-user', 		$affiliasi_kupon,'ajax_get_affiliate_coupon_user',999);
		$this->loader->add_action( 'sejoli_ajax_add-affiliate-coupon-user', 		$affiliasi_kupon,'ajax_add_affiliate_coupon_user',999);
		$this->loader->add_action( 'sejoli_ajax_get-affiliate-coupon-parent-list', 	$affiliasi_kupon,'ajax_get_coupon_parent_list_select2',999);

		$affiliasi_order = new SejoliSA\Front\Affiliasi_Order( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'sejoli_ajax_get-affiliate-order', 				$affiliasi_order,'ajax_get_affiliate_order',999);
		$this->loader->add_action( 'sejoli_ajax_get-order-detail', 					$affiliasi_order,'ajax_get_order_detail',999);

		$leaderboard = new SejoliSA\Front\Leaderboard( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'sejoli_ajax_get-leaderboard', 					$leaderboard,'ajax_get_leaderboard',999);

		$profile = new SejoliSA\Front\Profile( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli_ajax_update-profile', 	$profile, 'ajax_update_profile',999);

		$member_message = new SejoliSa\Front\MemberMessage( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'template_redirect',					$member_message, 'prepare_messages', 999);
		$this->loader->add_action( 'sejoli/member-area/header',			$member_message, 'display_messages', 1);

		$social_proof 	= new SejoliSA\Front\SocialProof( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',								$social_proof, 'set_endpoint', 			1);
		$this->loader->add_filter( 'query_vars', 						$social_proof, 'set_query_vars', 		1019);
		$this->loader->add_action( 'parse_query',						$social_proof, 'check_parse_query', 	19);
		$this->loader->add_action( 'sejoli_ajax_get-social-proof-data',	$social_proof, 'get_order_data',		1);
		$this->loader->add_action( 'wp',								$social_proof, 'check_if_enabled',		1019);
		$this->loader->add_action( 'body_class',						$social_proof, 'set_body_classes',		1019);
		$this->loader->add_action( 'wp_enqueue_scripts',				$social_proof, 'set_localize_js_vars',	1019);
		$this->loader->add_action( 'wp_footer',							$social_proof, 'set_scripts',			1019);

		$fb_tiktok_conversion = new SejoliSA\Front\Facebook_Tiktok_Conversion( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts',	$fb_tiktok_conversion, 'set_localize_js_vars',	1000);
		$this->loader->add_action( 'wp_ajax_sejoli-fb-tiktok-access-pixel-conversion',    $fb_tiktok_conversion, 'fb_tiktok_click_access_pixel_conversion', 1);
		$this->loader->add_action( 'wp',    $fb_tiktok_conversion, 'fb_tiktok_view_some_pages_pixel_conversion', 1019);
	
	}

	/**
	 * Register all of the hooks related to json request
	 *
	 * @since 	1.0.0
	 * @access 	private
	 */
	private function define_json_hooks() {

		$access = new SejoliSA\JSON\Access();

		$this->loader->add_action( 'wp_ajax_sejoli-access-get-bonus', 			$access, 'get_bonus_content', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-access-list-by-product', 	$access, 'list_by_product', 1);

		$affiliate = new SejoliSA\JSON\Affiliate();

		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-get-bonus-content', 		$affiliate, 'get_bonus_content', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-update-bonus-content', 	$affiliate, 'update_bonus_content', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-get-facebook-pixel', 		$affiliate, 'get_facebook_pixel', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-update-facebook-pixel',	$affiliate, 'update_facebook_pixel', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-confirm-commission-transfer',		$affiliate, 'confirm_commission_transfer');
		$this->loader->add_action( 'wp_ajax_sejoli-pay-single-affiliate-commission',	$affiliate, 'confirm_single_commission_transfer');

		$network = new SejoliSA\JSON\AffiliateNetwork();
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-get-network-list', 		$network, 'get_network_list', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-get-user-network-list', 	$network, 'get_user_network_list', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-get-network-detail', 		$network, 'get_network_detail', 1);


		$commission = new SejoliSA\JSON\Commission();

		$this->loader->add_action( 'wp_ajax_sejoli-commission-table',				$commission, 'set_for_table',	1);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-commission-table',		$commission, 'set_for_affiliate_table',	1);
		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-commission-detail',	$commission, 'set_for_affiliate_commission_confirmation', 1);
		$this->loader->add_action( 'wp_ajax_sejoli-commission-chart',				$commission, 'set_for_chart',	1);
		$this->loader->add_action( 'wp_ajax_sejoli-commission-confirm',				$commission, 'set_for_paid_confirmation',	1);

		$confirmation = new SejoliSA\JSON\Confirmation();
		$this->loader->add_action( 'wp_ajax_sejoli-confirmation-table',			$confirmation, 'set_for_table',	1);
		$this->loader->add_action( 'wp_ajax_sejoli-confirmation-detail',		$confirmation, 'get_detail',	1);


		$coupon = new SejoliSA\JSON\Coupon();

		$this->loader->add_action( 'wp_ajax_sejoli-create-coupon',	$coupon, 'create_affiliate_coupon', 	1);
		$this->loader->add_action( 'wp_ajax_sejoli-list-coupons',	$coupon, 'list_parent_coupons',			1);
		$this->loader->add_action( 'wp_ajax_sejoli-coupon-table',	$coupon, 'set_for_table',				1);
		$this->loader->add_action( 'wp_ajax_sejoli-coupon-check', 	$coupon, 'check_coupon_availability', 	999);
		$this->loader->add_action( 'wp_ajax_sejoli-coupon-update',	$coupon, 'update_coupons',				1);
		$this->loader->add_action( 'wp_ajax_sejoli-coupon-delete',	$coupon, 'delete_coupons',				1);

		$license = new SejoliSA\JSON\License();

		$this->loader->add_action( 'wp_ajax_sejoli-license-table',	$license, 'set_for_table',		1);
		$this->loader->add_action( 'wp_ajax_sejoli-license-update',	$license, 'update_licenses',	1);

		// Setting Cron Jobs Update License Status to Inactive based on Subscription Expired
		$this->loader->add_filter( 'cron_schedules', $license, 'sejoli_update_license_status_cron_schedules', 1 );
		// $this->loader->add_action( 'admin_init', $license, 'schedule_update_license_status_to_inactive_based_on_subscription_status', 1 );
		// $this->loader->add_action( 'update_status_license_to_inactive', $license, 'update_license_status_to_inactive_based_on_subscription_status', 1 );

		$order = new SejoliSA\JSON\Order();

		$this->loader->add_action( 'wp_ajax_sejoli-affiliate-order-table',		$order, 'set_for_affiliate_table', 	1);
		$this->loader->add_action( 'wp_ajax_sejoli-order-export-prepare',		$order, 'prepare_for_exporting', 	1);
		$this->loader->add_action( 'wp_ajax_sejoli-order-shipping',				$order, 'check_for_shipping', 		1);
		$this->loader->add_action( 'wp_ajax_sejoli-order-input-resi',			$order, 'update_resi', 				1);
		$this->loader->add_action( 'wp_ajax_sejoli-order-table',				$order, 'set_for_table',			1);
		$this->loader->add_action( 'wp_ajax_sejoli-order-chart',				$order, 'set_for_chart',			1);
		$this->loader->add_action( 'wp_ajax_sejoli-order-detail',				$order, 'get_detail',	    		1);
		$this->loader->add_action( 'wp_ajax_sejoli-bulk-notification-order',	$order, 'check_order_for_bulk_notification', 1);
		$this->loader->add_action( 'sejoli_ajax_check-order-for-confirmation',	$order, 'get_order_confirmation',		1);

		$product  = new SejoliSA\JSON\Product();

		$this->loader->add_action( 'wp_ajax_sejoli-product-options',				$product, 'set_for_options',		1);
		$this->loader->add_action( 'wp_ajax_sejoli-product-affiliate-link-list',	$product, 'list_affiliate_links', 	1);
		$this->loader->add_action( 'wp_ajax_sejoli-product-affiliate-help-list',	$product, 'list_affiliate_help', 	1);
		$this->loader->add_action( 'wp_ajax_sejoli-product-affiliate-options',		$product, 'set_for_options_product_affiliate',		1);
		$this->loader->add_action( 'wp_ajax_sejoli-product-table',					$product, 'set_for_table', 			1);
		$this->loader->add_action( 'wp_ajax_sejoli-check-autoresponder',			$product, 'check_autoresponder',	1);

		$reminder = new SejoliSA\JSON\Reminder();

		$this->loader->add_action( 'wp_ajax_sejoli-reminder-table',		$reminder, 'set_for_table',		1);
		$this->loader->add_action( 'wp_ajax_sejoli-reminder-resend',	$reminder, 'resend_reminder',	1);

		$statistic = new SejoliSA\JSON\Statistic();

		$this->loader->add_action( 'wp_ajax_sejoli-statistic-commission',		$statistic, 'get_commission_data',				1);
		$this->loader->add_action( 'wp_ajax_sejoli-statistic-product',			$statistic, 'get_product_data',					1);
		$this->loader->add_action( 'sejoli_ajax_get-member-statistic-today', 	$statistic, 'get_member_today_statistic', 		1);
		$this->loader->add_action( 'sejoli_ajax_get-member-statistic-yesterday',$statistic, 'get_member_yesterday_statistic', 	1);
		$this->loader->add_action( 'sejoli_ajax_get-member-statistic-monthly', 	$statistic, 'get_member_monthly_statistic', 1);
		$this->loader->add_action( 'sejoli_ajax_get-member-statistic-all', 		$statistic, 'get_member_all_statistic', 	1);
		$this->loader->add_action( 'sejoli_ajax_get-chart-statistic-monthly',	$statistic, 'get_chart_monthly_statistic', 	1);
		$this->loader->add_action( 'sejoli_ajax_get-chart-statistic-yearly',	$statistic, 'get_chart_yearly_statistic', 	1);
		$this->loader->add_action( 'sejoli_ajax_get-top-ten',					$statistic, 'get_top_ten_data', 			1);
		$this->loader->add_action( 'sejoli_ajax_get-acquisition-data',			$statistic, 'get_acquisition_data',			1);
		$this->loader->add_action( 'sejoli_ajax_get-acquisition-member-data',	$statistic, 'get_acquisition_member_data',	1);

		$subscription = new SejoliSA\JSON\Subscription();

		$this->loader->add_action( 'wp_ajax_sejoli-subscription-table',			$subscription, 'set_for_table',	1);
		$this->loader->add_action( 'wp_ajax_sejoli-subscription-export',		$subscription, 'prepare_for_exporting',	1);

		// Setting Cron Jobs Update License Status to Inactive based on Subscription Expired
		$this->loader->add_filter( 'cron_schedules', $subscription, 'sejoli_update_subscription_to_expired_cron_schedules', 1 );
		$this->loader->add_action( 'admin_init', $subscription, 'schedule_update_subscription_status_to_expired_based_on_subscription_is_expired', 1 );
		$this->loader->add_action( 'update_status_subscription_to_expired', $subscription, 'set_subcription_expired', 1 );


		$user  = new SejoliSA\JSON\User();

		$this->loader->add_action( 'wp_ajax_sejoli-user-options',			$user, 'set_for_options',		1);
		$this->loader->add_action( 'wp_ajax_sejoli-user-table',				$user, 'set_for_table',			1);
		$this->loader->add_action( 'wp_ajax_sejoli-user-update',			$user, 'update_user',			1);
		$this->loader->add_action( 'wp_ajax_sejoli-user-export-prepare',	$user, 'prepare_for_exporting',	1);

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
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    SejoliSA_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
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

}
