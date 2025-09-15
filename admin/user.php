<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli
 * @subpackage Sejoli/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli
 * @subpackage Sejoli/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class User {

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
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * All affiliate data
	 * @since 	1.1.0
	 * @var 	array
	 */
	protected $affiliates = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Create member role
	 * Hooked via init, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function create_member_role() {
		global $wp_roles;

		if( !isset( $wp_roles) ) :
			$wp_roles = new WP_Roles();
		endif;

		/**
		 * Add custom role to administrator
		 */
		$wp_roles->add_cap('administrator', 'manage_sejoli_sejoli');
		$wp_roles->add_cap('administrator', 'manage_sejoli_products');
		$wp_roles->add_cap('administrator', 'manage_sejoli_orders');
		$wp_roles->add_cap('administrator', 'manage_sejoli_subscriptions');
		$wp_roles->add_cap('administrator', 'manage_sejoli_coupons');
		$wp_roles->add_cap('administrator', 'manage_sejoli_commissions');
		$wp_roles->add_cap('administrator', 'manage_sejoli_licenses');

		// Reminders
		$wp_roles->add_cap('administrator', 'edit_others_sejoli_reminders');
		$wp_roles->add_cap('administrator', 'edit_sejoli_reminders');
		$wp_roles->add_cap('administrator', 'publish_sejoli_reminders');
		$wp_roles->add_cap('administrator', 'read_private_sejoli_reminders');
		$wp_roles->add_cap('administrator',	'edit_sejoli_reminder');
		$wp_roles->add_cap('administrator',	'delete_sejoli_reminder');
		$wp_roles->add_cap('administrator',	'read_sejoli_reminder');

		// Products
		$wp_roles->add_cap('administrator', 'edit_others_sejoli_products');
		$wp_roles->add_cap('administrator', 'edit_sejoli_products');
		$wp_roles->add_cap('administrator', 'publish_sejoli_products');
		$wp_roles->add_cap('administrator', 'read_private_sejoli_products');
		$wp_roles->add_cap('administrator',	'edit_sejoli_product');
		$wp_roles->add_cap('administrator',	'delete_sejoli_product');
		$wp_roles->add_cap('administrator',	'read_sejoli_product');

		// Access
		$wp_roles->add_cap('administrator', 'edit_others_sejoli_accesses');
		$wp_roles->add_cap('administrator', 'edit_sejoli_accesses');
		$wp_roles->add_cap('administrator', 'publish_sejoli_accesses');
		$wp_roles->add_cap('administrator', 'read_private_sejoli_accesses');
		$wp_roles->add_cap('administrator',	'edit_sejoli_access');
		$wp_roles->add_cap('administrator',	'delete_sejoli_access');
		$wp_roles->add_cap('administrator',	'read_sejoli_access');

		// Any Content
		$wp_roles->add_cap('administrator', 'edit_others_sejoli_content');
		$wp_roles->add_cap('administrator', 'edit_sejoli_content');
		$wp_roles->add_cap('administrator', 'publish_sejoli_content');
		$wp_roles->add_cap('administrator', 'read_private_sejoli_content');
		$wp_roles->add_cap('administrator',	'edit_sejoli_content');
		$wp_roles->add_cap('administrator',	'delete_sejoli_content');
		$wp_roles->add_cap('administrator',	'read_sejoli_content');

		$wp_roles->add_cap('administrator', 'sejoli_user_can_access_admin');

		/**
		 * Create manager role
		 */
		$manager_role = $wp_roles->get_role('subscriber');

		$wp_roles->add_role('sejoli-manager', 'Manager', $manager_role->capabilities);

		$wp_roles->add_cap('sejoli-manager', 'manage_sejoli_products');
		$wp_roles->add_cap('sejoli-manager', 'manage_sejoli_orders');
		$wp_roles->add_cap('sejoli-manager', 'manage_sejoli_subscriptions');
		$wp_roles->add_cap('sejoli-manager', 'manage_sejoli_coupons');
		$wp_roles->add_cap('sejoli-manager', 'manage_sejoli_commissions');
		$wp_roles->add_cap('sejoli-manager', 'manage_sejoli_licenses');

		// Reminders
		$wp_roles->add_cap('sejoli-manager', 'edit_others_sejoli_reminders');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_reminders');
		$wp_roles->add_cap('sejoli-manager', 'publish_sejoli_reminders');
		$wp_roles->add_cap('sejoli-manager', 'read_private_sejoli_reminders');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_reminder');
		$wp_roles->add_cap('sejoli-manager', 'delete_sejoli_reminder');
		$wp_roles->add_cap('sejoli-manager', 'read_sejoli_reminder');

		// Products
		$wp_roles->add_cap('sejoli-manager', 'edit_others_sejoli_products');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_products');
		$wp_roles->add_cap('sejoli-manager', 'publish_sejoli_products');
		$wp_roles->add_cap('sejoli-manager', 'read_private_sejoli_products');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_product');
		$wp_roles->add_cap('sejoli-manager', 'delete_sejoli_product');
		$wp_roles->add_cap('sejoli-manager', 'read_sejoli_product');

		// Access
		$wp_roles->add_cap('sejoli-manager', 'edit_others_sejoli_accesses');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_accesses');
		$wp_roles->add_cap('sejoli-manager', 'publish_sejoli_accesses');
		$wp_roles->add_cap('sejoli-manager', 'read_private_sejoli_accesses');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_access');
		$wp_roles->add_cap('sejoli-manager', 'delete_sejoli_access');
		$wp_roles->add_cap('sejoli-manager', 'read_sejoli_access');

		// Any Content
		$wp_roles->add_cap('sejoli-manager', 'edit_others_sejoli_content');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_content');
		$wp_roles->add_cap('sejoli-manager', 'publish_sejoli_content');
		$wp_roles->add_cap('sejoli-manager', 'read_private_sejoli_content');
		$wp_roles->add_cap('sejoli-manager', 'edit_sejoli_content');
		$wp_roles->add_cap('sejoli-manager', 'delete_sejoli_content');
		$wp_roles->add_cap('sejoli-manager', 'read_sejoli_content');

		$wp_roles->add_cap('sejoli-manager', 'sejoli_user_can_access_admin');

		/**
		 * Create member role
		 */
		$member_role = $wp_roles->get_role('subscriber');

		$wp_roles->add_role('sejoli-member', 'Member', $member_role->capabilities);

		$wp_roles->add_cap('sejoli-member', 'manage_sejoli_own_coupons');
		$wp_roles->add_cap('sejoli-member', 'manage_sejoli_own_affiliates');
		$wp_roles->add_cap('sejoli-member', 'manage_sejoli_own_orders');

		$wp_roles->add_cap('editor', 'sejoli_user_can_access_admin');
		$wp_roles->add_cap('author', 'sejoli_user_can_access_admin');
	}

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['user'] = [
			'select' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-user-options',
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-user-options')
			],
			'placeholder' => __('Pencarian user', 'sejoli')
		];

		return $js_vars;
	}

	/**
	 * Add profile fields
	 * Hooked via action carbon_fields_register_fields, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function setup_profile_fields() {

		$fields = apply_filters( 'sejoli/user/fields', []);

		if(is_array($fields) && 0 < count($fields)) :
			$container = Container::make('user_meta', __('Sejoli Profile', 'sejoli'))
				->set_classes('sejoli-metabox');

			foreach($fields as $field) :
				$container->add_tab($field['title'], $field['fields']);
			endforeach;
		endif;

	}

	/**
	 * Add basic fields to profile
	 * Hooked via action sejoli/user/fields, priority 100
	 * @since  1.0.0
	 * @param  array $fields
	 * @return array
	 */
	public function add_basic_fields(array $fields) {

		$fields[] = [
			'title'		=> __('Informasi Dasar', 'sejoli'),
			'fields'	=> [
				Field::make('text',	'phone',	__('Nomor Telpon/Whatsapp', 'sejoli'))
					->set_required(true)
			]
		];

		return $fields;
	}

	/**
	 * Add shipping fields to profile
	 * Hooked via action sejoli/user/fields, priority 100
	 * @since  1.0.0
	 * @param  array $fields
	 * @return array
	 */
	public function add_shipping_fields(array $fields) {

		$subdistrict_options = apply_filters('sejoli/shipment/subdistricts', []);

		$fields[] = [
			'title'  => __('Informasi Pengiriman', 'sejoli'),
			'fields' => [
				Field::make('textarea', 'address', 		__('Alamat Pengiriman', 'sejoli')),
				Field::make('select',	'destination', 	__('Kecamatan', 'sejoli'))
					->set_options($subdistrict_options),
				Field::make('text',	'postal_code',	__('Kode Pos', 'sejoli'))
			]
		];

		return $fields;
	}

	/**
	 * Declare property meta under WP_User
	 * Hooked via filter sejoli/user/meta-data, priority 11
	 * @since 	1.0.0
	 * @param 	WP_User $user
	 * @return 	WP_User
	 */
	public function declare_user_meta($user) {

		if(!property_exists($user, 'meta')) :
			$user->meta = new \stdClass();
		endif;

		return $user;
	}

	/**
	 * Set user meta
	 * Hooked via filter sejoli/user/meta-data, priority 100
	 * @since 	1.0.0
	 * @param 	WP_User $user
	 * @return 	WP_User
	 */
	public function set_user_meta($user) {

		$user->meta->phone       = sejolisa_carbon_get_user_meta($user->ID, 'phone');
		$user->meta->address     = sejolisa_carbon_get_user_meta($user->ID, 'address');
		$user->meta->destination = sejolisa_carbon_get_user_meta($user->ID, 'destination');
		$user->meta->postal_code = sejolisa_carbon_get_user_meta($user->ID, 'postal_code');

		return $user;
	}

	/**
	 * Add custom js in profile page
	 * Hooked via action admin_footer, priority 999
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function add_profile_js() {

		global $pagenow;

		if(in_array($pagenow, ['profile.php', 'user-edit.php', 'user-new.php'])) :
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			sejoli.helper.select_2(
				"select[name='carbon_fields_compact_input[_destination]']",
				sejoli_admin.get_subdistricts.ajaxurl,
				sejoli_admin.get_subdistricts.placeholder
			);

			sejoli.helper.select_2(
	            "select[name='carbon_fields_compact_input[_affiliate_id]']",
	            sejoli_admin.user.select.ajaxurl,
	            sejoli_admin.affiliate.placeholder
	        );
		});
		</script>
		<?php
		endif;
	}

	/**
	 * Hide admin bar for non administrator
	 * Hooked via filter show_admin_bar, priority 1
	 * @since 	1.0.0
	 * @since 	1.4.0		Enable show admin bar for all user
	 * @param 	boolean 	$is_show	Current state of admin bar
	 * @return 	boolean 	State if admin bar showed or n
	 */
	public function hide_admin_bar($is_show = true) {

		if(!is_user_logged_in()) :
			return false;
		endif;

		return $is_show;

	}
	/**
	 * Disable admin page access if user is not grnated
	 * Hooked via action admin_init, priority 1
	 * @since 	1.0.0
	 * @since 	1.5.0	Add conditional check if current user role is granted to access wp-admin
	 * 					and change the redirect url
	 * @return 	void
	 */
	public function disable_admin_access() {

		if(
			(
				! is_user_logged_in() ||
				! sejolisa_user_can_access_wp_admin()
			) &&
			false === wp_doing_ajax()
		) :

			$redirect_url = sejolisa_get_default_member_area_url();

			wp_redirect( $redirect_url );
			exit;

		endif;
	}

	/**
	 * Get user by post data when checkout
	 * Hooked via filter sejoli/checkout/user-data, priority 100
	 * @since 	1.0.0
	 * @param  	bool|WP_User 	$user_data
	 * @param  	array  			$post_data
	 * @return 	bool|WP_User
	 */
	public function get_user_data_when_checkout($user_data, array $post_data) {

		$product = sejolisa_get_product( $post_data['product_id'] );

		if(!is_a($user_data, 'WP_User')) :

			if(is_user_logged_in()) :
				$user_data = sejolisa_get_user(get_current_user_id());
			elseif ( class_exists( 'WP_CLI' ) && !empty($post_data['user_id'])) :
				$user_data = sejolisa_get_user(intval($post_data['user_id']));
			else :
				// user is not registered
				if(false === $user_data) :
        			if( $product->type === "physical" ) :
						$user_data = sejolisa_get_user($post_data['user_phone']);
					else:
						// If product is physical
						$post_data['user_email'] = $this->check_user_email($post_data);

						$user_data = sejolisa_get_user($post_data['user_email']);
					endif;
				endif;
			endif;

		endif;

		if(isset($user_data->meta->affiliate) && !empty($user_data->meta->affiliate)) :
			do_action('sejoli/checkout/affiliate/set', $user_data->meta->affiliate, 'user_meta');
		endif;

		return $user_data;
	}

	/**
	 * Translate phone number
	 * Hooked via filter sejoli/user/phone, priority 999
	 * @since 	1.0.0
	 * @since 	1.3.4 	Improve replace number with country code
	 * @param   string 	$phone_number
	 * @param 	boolean	$with_prefix
	 * @return  string
	 */
	public function translate_phone_number($number, $with_prefix = true) {

		$number = safe_str_replace(' ', '', $number);

		// $check_two = substr($number, 0, 2);

		// if(in_array($check_two, ['08', '62'])) :

		//  	if('08' === $check_two) :
		//  		$number = substr_replace($number, '+628', 0, 2);
		//    	elseif('62' === $check_two) :
		//   		$number = substr_replace($number, '+6', 0, 1);	
		//   	else :
		//   		$number = '+'.$number;
		//   	endif;

		// endif;

		// return $number;
		
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

		if (!empty($number)) :

		    try {

		        if (strpos($number, '+') !== 0) :

		            if (strpos($number, '0') === 0) :
		                $number = preg_replace('/^0/', '62', $number);
		            endif;

		            $number = '+' . $number;

		        endif;

		        $numberProto = $phoneUtil->parse($number, null);

		        if ($phoneUtil->isValidNumber($numberProto)) :

		            return $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
		        
		        else:

		            return $number;

		        endif;

		    } catch (\libphonenumber\NumberParseException $e) {

		        do_action('sejoli/log/write', 'validate-phone-number', sprintf(__('Uncaught error: %s', 'sejoli'), $e->getMessage()), $e);

		        return $number;

		    }
		
		endif;

		return $number;

	}

	/**
	 * Validate user when check if user data is not valid WP_User
	 * Hooked via filter sejoli/checkout/is-user-data-valid, priority 100
	 * @since 	1.0.0
	 * @param  	bool   $valid
	 * @param  	array  $post_data
	 * @return 	array
	 */
	public function validate_user_when_checkout(bool $valid, array $post_data) {

		$display_password = boolval(sejolisa_carbon_get_theme_option('sejoli_registration_display_password'));

		$post_data = wp_parse_args($post_data,[
			'user_email'      => NULL,
			'user_name'       => NULL,
			'user_password'   => NULL,
			'user_phone'      => NULL,
		]);

		if(!empty($post_data['user_email']) && !is_email($post_data['user_email']) && !is_sejolisa_product_physical($post_data['product_id'])) :
			$valid = false;
			sejolisa_set_message(__('Alamat email tidak valid', 'sejoli'));
		endif;

		if(empty($post_data['user_email'])) :
			$valid = false;
			sejolisa_set_message(__('Alamat email wajib diisi', 'sejoli'));
		endif;

		if(empty($post_data['user_name'])) :
			$valid = false;
			sejolisa_set_message(__('Nama wajib diisi', 'sejoli'));
		endif;

		if(empty($post_data['user_password'])) :
			$valid = false;
			sejolisa_set_message(__('Password wajib diisi', 'sejoli'));
		endif;

		if(5 > strlen($post_data['user_password']) && !is_sejolisa_product_physical($post_data['product_id']) && $display_password ) :
			$valid = false;
			sejolisa_set_message(__('Panjang password minimal 6 karakter', 'sejoli'));
		endif;

		if(empty($post_data['user_phone']) || 10 > strlen($post_data['user_phone'])) :
			$valid = false;
			sejolisa_set_message(__('Panjang nomor handphone/whatsapp minimal 10 karakter', 'sejoli'));
		else :
			$number = apply_filters('sejoli/user/phone', $post_data['user_phone']);

			if(!is_numeric($number)) {
				$valid = false;
				sejolisa_set_message(__('Nomor handphone/whatsapp tidak valid.', 'sejoli'));
			}

			$user = sejolisa_get_user( $post_data['user_phone'] );

            if ( is_a($user,'WP_User') && $user->ID > 0 ) :

            	$valid = false;
            	sejolisa_set_message(__('No Handphone sudah terdaftar silahkan login menggunakan akun anda', 'sejoli'));

            endif;
		endif;

		return $valid;
	}

	/**
	 * Check user email, if product is physical then will create dummy email address
	 * @since 	1.0.0
	 * @param  	array 	$user_data
	 * @return 	string
	 */
	protected function check_user_email($user_data) {

		$email = $user_data['user_email'];

		if(
			empty($email) &&
			!empty($user_data['product_id']) &&
			is_sejolisa_product_physical($user_data['product_id'])
		) :

			$email = sejolisa_get_email_domain( $user_data['user_phone'] );

		endif;

		return $email;
	}

	/**
	 * Generate unique username based on user email
	 * @since 	1.4.2
	 * @param  	string 	$username
	 * @return 	string
	 */
	protected function generate_username($username) {

		$username    = sanitize_user($username);
		$user_exists = username_exists($username);
		$suffix 	 = false;

		while(false === $user_exists) :

			$suffix      = sprintf("%0d", mt_rand(1, 999999));
			$user_exists = username_exists( $username . '_' . $suffix );

		endwhile;

		return ($suffix) ? $username . '_' . $suffix : $username ;

	}

	/**
	 * Register user
	 * Hooked via action sejoli/user/register, priority 100
	 * @since 	1.0.0
	 * @param  	array  $user_data Array of user data
	 * @return 	void
	 */
	public function register(array $user_data) {

		$user_data = wp_parse_args($user_data,[
			'user_email'      => NULL,
            'user_name'       => NULL,
            'user_password'   => NULL,
            'user_phone'      => NULL,
			'product_id'	  => NULL,
		]);

		$user_data['user_email'] = $this->check_user_email($user_data);

		if( empty($user_data['user_password']) ) :

			$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!!!';
	    	$charactersLength = strlen($characters);
	    	$password         = '';

	    	for ($i = 0; $i < 8; $i++) :
	        	$password .= $characters[rand(0, $charactersLength - 1)];
	    	endfor;

			$user_data['user_password'] = $password;

		endif;

		$user_id = wp_insert_user([
			'user_login'   => sanitize_user($user_data['user_email']),
			'user_email'   => $user_data['user_email'],
			'display_name' => $user_data['user_name'],
			'first_name'   => $user_data['user_name'],
			'user_pass'    => $user_data['user_password'],
			'role'		   => 'sejoli-member',
		]);

		if(!is_wp_error($user_id)) :

			update_user_meta($user_id, '_phone', $user_data['user_phone']);

			$affiliate_id = NULL;
			$affiliate_data = sejolisa_get_affiliate_checkout();

			do_action('sejoli/log/write', 'set affiliate', $affiliate_data);

			$affiliate_id = (!empty($affiliate_data['link'])) ? 	$affiliate_data['link'] : $affiliate_id;
			$affiliate_id = (!empty($affiliate_data['coupon'])) ? 	$affiliate_data['coupon'] : $affiliate_id;

			if(!empty($affiliate_id)) :
				update_user_meta($user_id, sejolisa_get_affiliate_key(), intval($affiliate_id));
			endif;

			do_action('sejoli/notification/registration', $user_data);

		endif;
	}

	/**
	 * Set user data to otder
	 * Hooked via filter sejoli/order/order-detail, priority 20
	 * @since 	1.0.0
	 * @param 	array $order_detail
	 * @return 	array
	 */
	public function set_user_data_to_order_detail(array $order_detail) {

		$user_id              = intval($order_detail['user_id']);
		$order_detail['user'] = sejolisa_get_user( $user_id );

		return $order_detail;
	}

	/**
	 * Add custom columns to user table
	 * Hooked via filter manage_user_columns, priority 1
	 * @since 	1.1.0
	 * @param  	array  $columns Current user columns
	 * @return 	array
	 */
	public function modify_user_table(array $columns) {

		unset($columns['posts']);

		$columns['sejoli-affiliate']	= __('Affiliasi', 'sejoli');
		// $columns['sejoli-history']		= __('Log', 'sejoli');

		return $columns;
	}

	/**
	 * Get affiliate name
	 * @since 	1.1.0
	 * @param  	integer $user_id
	 * @return 	string
	 */
	protected function get_affiliate_name($user_id) {
		$affiliate_id = get_user_meta($user_id, sejolisa_get_affiliate_key(), true);

		if(empty($affiliate_id)) :
			return '-';
		endif;

		if(!isset($this->affiliates[$affiliate_id])) :
			$this->affiliates[$affiliate_id] = sejolisa_get_user($affiliate_id);
		endif;

		return $this->affiliates[$affiliate_id]->display_name;
	}

	/**
	 * Display custom column value
	 * Hooked via filter manage_users_custom_column, priority 100
	 * @since 	1.1.0
	 * @param  	string 	$value
	 * @param  	string 	$column_name
	 * @param  	integer 	$user_id
	 * @return 	string
	 */
	public function display_value_for_custom_table($value, $column_name, $user_id) {

		if('sejoli-affiliate' === $column_name) :
			return $this->get_affiliate_name($user_id);
		endif;

		return $value;
	}

	/**
	 * Display detail affiliate
	 * Hooked via , priority 99
	 *
	 * @return void
	 */
	public function display_detail_affiliate_link( $actions, $user_object ){
		
		if ( current_user_can( 'administrator', $user_object->ID ) )
        	$actions['aff_link'] = "<a href=" . wp_nonce_url( "users.php?page=detail-user-network&amp;users=$user_object->ID", '_wpnonce_detail_affiliasi' ) . ">Lihat Detail Jaringan</a>";			
    	return $actions;

	}

	public function detail_user_network_page() {
		add_users_page(
			__( 'Detail user network', 'sejolisa' ),
			__( 'Detail user network', 'sejolisa' ),
			'activate_plugins',
			'detail-user-network',
			array($this, 'data_detail_user_network_page')			
		);


		remove_submenu_page( 'users.php', 'detail-user-network' );
	}

	public function data_detail_user_network_page(){

		$user_id = isset ($_REQUEST['users']) ? $_REQUEST['users']:'';
		$nonce = isset ($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce']:'';

		if ( ! wp_verify_nonce( $nonce, '_wpnonce_detail_affiliasi' ) && ! $user_id ):
		?>

		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'Terjadi kesalahan. Silahkan kembali <a href="'.admin_url( "users.php" ).'">list user</a>', 'sejolisa' ); ?></p>
		</div>

		<?php 
		else:
			// Do stuff here.
			require_once( plugin_dir_path( __FILE__ ) . 'partials/user/detail-affiliate.php' );
		endif;


    }

    /**
	 * Get all affiliate user
	 * Hooked via filter sejoli/user/affiliate-user, priority 1
	 * @since  1.0.0
	 * @return array
	 */
	public function get_affiliate_users($options = array()) {

	    $options = [];

	    $users = get_users();

	    foreach($users as $user){
	        $options[$user->ID] =  $user->display_name . ' - ' . $user->user_email;
	    }

	    asort($options);

	    return $options;

	}

	 /**
	 * Set User Affiliate Field
	 * Hooked via action show_user_profile, priority 10
	 * Hooked via action edit_user_profile, priority 10
	 * @since  1.0.0
	 * @return array
	 */
	function set_user_affiliate( $user ) {

		$html = '';

		require_once( plugin_dir_path( __FILE__ ) . 'partials/user/user-affiliate.php' );

		echo $html;

	}

	/**
	 * Save User Affiliate Field
	 * Hooked via action personal_options_update, priority 10
	 * Hooked via action edit_user_profile_update, priority 10
	 * @since  1.0.0
	 * @return array
	 */
	function save_user_affiliate_profile_fields( $user_id ) {

	    if ( !current_user_can( 'edit_user', $user_id ) )
	        return false;

	    /* Edit the following lines according to your set fields */
	    update_user_meta( $user_id, '_affiliate_id', $_POST['affiliate_id'] );
	
	}

}
