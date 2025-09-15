<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.3.0
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
class UserGroup {

    /**
	 * The ID of this plugin.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Commission fields
	 * @since	1.3.0
	 * @var 	array
	 */
	protected $commission_fields = array();

	/**
	 * Avalaible user roles
	 * @since	1.3.0
	 * @var 	array
	 */
	protected $available_roles = NULL;

	/**
	 * Set dropdown options
	 * @since	1.0.0
	 * @var 	false|array
	 */
	protected $options = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name     = $plugin_name;
		$this->version         = $version;
	}

    /**
     * Register access post type
     * Hooked via action init, priority 1010
     * @return void
     */
    public function register_post_type() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		$labels = [
    		'name'               => _x( 'User Groups', 'post type general name', 'sejoli' ),
    		'singular_name'      => _x( 'User Group', 'post type singular name', 'sejoli' ),
    		'menu_name'          => _x( 'User Groups', 'admin menu', 'sejoli' ),
    		'name_admin_bar'     => _x( 'User Groups', 'add new on admin bar', 'sejoli' ),
    		'add_new'            => _x( 'Add New', 'access', 'sejoli' ),
    		'add_new_item'       => __( 'Add New User Group', 'sejoli' ),
    		'new_item'           => __( 'New User Group', 'sejoli' ),
    		'edit_item'          => __( 'Edit User Group', 'sejoli' ),
    		'view_item'          => __( 'View User Group', 'sejoli' ),
    		'all_items'          => __( 'All User Groups', 'sejoli' ),
    		'search_items'       => __( 'Search User Group', 'sejoli' ),
    		'parent_item_colon'  => __( 'Parent User Group:', 'sejoli' ),
    		'not_found'          => __( 'No access found.', 'sejoli' ),
    		'not_found_in_trash' => __( 'No access found in Trash.', 'sejoli' )
    	];

    	$args = [
    		'labels'             => $labels,
            'description'        => __( 'Description.', 'sejoli' ),
    		'public'             => true,
    		'publicly_queryable' => false,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => [ 'slug' => 'sejoli-user-group' ],
    		'capability_type'    => 'post',
			'capabilities'		 => array(
				'publish_posts'       => 'publish_sejoli_content',
				'edit_posts'          => 'edit_sejoli_content',
				'edit_others_posts'   => 'edit_others_sejoli_content',
				'read_private_posts'  => 'read_private_sejoli_content',
				'edit_post'           => 'edit_sejoli_content',
				'delete_post'         => 'delete_sejoli_content',
				'delete_posts'        => 'delete_sejoli_content',
				'delete_others_posts' => 'delete_sejoli_content',
				'read_post'           => 'read_sejoli_content'
			),
    		'has_archive'        => true,
    		'hierarchical'       => false,
    		'menu_position'      => null,
    		'supports'           => [ 'title' ],
			'menu_icon'			 => plugin_dir_url( __FILE__ ) . 'images/icon.png'
    	];

    	register_post_type( SEJOLI_USER_GROUP_CPT, $args );
    }

	/**
	 * Setup per product
	 * @since 	1.3.3
	 * @return 	return
	 */
	protected function setup_per_product() {

		$fields = array(

			Field::make( 'select',	'product', __('Produk', 'sejoli'))
				->set_options('sejolisa_get_product_options'),

			Field::make('separator', 'sep_discount', __('Pengaturan Diskon', 'sejoli'))
				->set_classes('sejoli-with-help'),

			Field::make( 'checkbox', 'discount_enable', __('Aktifkan diskon harga', 'sejoli'))
				->set_default_value(false)
				->set_conditional_logic(array(
					array(
						'field'   => 'product',
						'value'   => '',
						'compare' => '!='
					)
				)),

			Field::make('text',	'discount_price', sprintf(__('Diskon harga (%s)','sejoli'), sejolisa_currency_format()))
				->set_attribute('type', 'number')
				->set_required(true)
				->set_conditional_logic(array(
					array(
						'field'	=> 'discount_enable',
						'value'	=> true
					),array(
						'field'   => 'product',
						'value'   => '',
						'compare' => '!='
					)
				))
				->set_width(50),


			Field::make('select', 'discount_price_type', __('Tipe diskon', 'sejoli'))
				->set_options(array(
					'fixed'			=> __('Tetap', 'sejoli'),
					'percentage'	=> __('Persentase', 'sejoli')
				))
				->set_width(50)
				->set_conditional_logic(array(
					array(
						'field'	=> 'discount_enable',
						'value'	=> true
					),array(
						'field'   => 'product',
						'value'   => '',
						'compare' => '!='
					)
				)),

			Field::make('separator', 'sep_commission', __('Pengaturan Komisi', 'sejoli'))
				->set_classes('sejoli-with-help'),

			Field::make('complex', 'commission',__('Komisi','sejoli'))
				->add_fields(
					apply_filters('sejoli/product/commission/fields', array())
				)
				->set_layout('tabbed-vertical')
				->set_header_template(__('Tier','sejoli').' <%- $_index+1 %>'),
		);

		return apply_filters('sejoli/user-group/per-product/fields', $fields);
	}

	/**
	 * Modify group column data
	 * Hooked via filter manage_sejoli-user-group_posts_columns, priority 1
	 * @since 	1.3.0
	 * @param  	array  $columns
	 * @return 	array
	 */
	public function modify_group_columns(array $columns) {

		$columns = array(
			// 'sejoli-group-priority' => __('Level', 'sejoli'),
			'title'                 => __('Nama Group', 'sejoli'),
			'sejoli-group-rule'		=> __('Pengaturan', 'sejoli')
		);

		return $columns;
	}

	/**
	 * Sort group data by priority
	 * Hooked via action pre_get_posts, priority 1
	 * @since 	1.3.0
	 * @param  	WP_Query	$query
	 * @return	void
	 */
	public function sort_group_by_priority($query) {

		if(
			is_admin() &&
			isset($_GET['post_type']) && 'sejoli-user-group' === $_GET['post_type']
		) :

			// $query->set('meta_key', '_priority');
			$query->set('orderby', 'meta_value');
			$query->set('order', 'ASC');

		endif;
	}

	/**
	 * Display custom
	 * @param  [type] $column  [description]
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	public function display_custom_data_in_table($column, $post_id) {

		switch($column) :

			// case 'sejoli-group-priority' :
			// 	echo sejolisa_carbon_get_post_meta($post_id, 'priority');
			// 	break;

			case 'sejoli-group-rule' :

				$info = [];
				$group_detail = sejolisa_get_group_detail($post_id);

				if(false !== $group_detail['enable_discount']) :
					$info[] = __('Diskon ke semua produk', 'sejoli');
				endif;

				if(is_array($group_detail['commissions']) && 0 < count($group_detail['commissions']) ) :
					$info[] = __('Komisi ke semua produk', 'sejoli');
				endif;

				// $per_product = sejolisa_carbon_get_post_meta($post_id, 'group_setup_per_product');
				//
				// if(0 < count($per_product)) :
				//
				// 	$info[] = '<u>' . __('Pengaturan per produk :', 'sejoli') . '</u>';
				//
				// 	foreach($per_product as $i => $_product) :
				//
				// 		$product_id = intval(get_post_meta($post_id, '_group_setup_per_product|product|' . $i. '|0|value', true));
				// 		$product    = sejolisa_get_product($product_id);
				// 		$info[]     = '<strong>' . $product->post_title . '</strong>';
				//
				// 		if(false !== $_product['discount_enable']) :
				// 			$info[]	= '- ' . __('Pengaturan diskon', 'sejoli');
				// 		endif;
				//
				// 		if(0 < count($_product['commission'])) :
				// 			$info[] = '- ' . __('Pengaturan komisi', 'sejoli');
				// 		endif;
				//
				// 		$info[] = '&nbsp;';
				//
				// 	endforeach;
				//
				// endif;

				echo implode('<br />', $info);

				break;

		endswitch;

	}

	/**
	 * Get product setting field
	 * @since 	1.3.0
	 * @param  	array  $fields
	 * @return 	array
	 */
	protected function get_product_setting_fields() {

		$fields = array(

			Field::make( 'separator', 'sep_group_product_price', __('Pengaturan Semua Produk', 'sejoli'))
				->set_classes('sejoli-with-help')
				->set_help_text('<a href="#" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

			Field::make('html',	'sep_group_general_info')
				->set_html(
					'<div class="sejoli-html-message info"><p>'.
					__('Pengaturan ini akan berlaku di semua produk', 'sejoli').
					'</p></div>'
				),

			Field::make('separator', 'sep_discount', __('Pengaturan Diskon', 'sejoli'))
				->set_classes('sejoli-with-help'),

			Field::make( 'checkbox', 'group_discount_enable', __('Aktifkan diskon harga', 'sejoli'))
				->set_default_value(false),

			Field::make('text',	'group_discount_price', sprintf(__('Diskon harga (%s)','sejoli'), sejolisa_currency_format()))
				->set_attribute('type', 'number')
				->set_required(true)
				->set_conditional_logic(array(
					array(
						'field'	=> 'group_discount_enable',
						'value'	=> true
					)
				))
				->set_width(50),


			Field::make('select', 'group_discount_price_type', __('Tipe diskon', 'sejoli'))
				->set_options(array(
					'fixed'			=> __('Tetap', 'sejoli'),
					'percentage'	=> __('Persentase', 'sejoli')
				))
				->set_width(50)
				->set_conditional_logic(array(
					array(
						'field'	=> 'group_discount_enable',
						'value'	=> true
					)
				)),

			Field::make('separator', 'sep_commission', __('Pengaturan Komisi', 'sejoli'))
				->set_classes('sejoli-with-help'),

			Field::make('complex', 'group_commissions',__('Komisi','sejoli'))
				->add_fields(
					apply_filters('sejoli/product/commission/fields', array())
				)
				->set_layout('tabbed-vertical')
				->set_header_template(__('Tier','sejoli').' <%- $_index+1 %>'),

			Field::make( 'separator', 'sep_group_product_setup', __('Pengaturan Per Produk', 'sejoli'))
				->set_classes('sejoli-with-help')
				->set_help_text('<a href="#" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

			Field::make('html',	'sep_group_product_setup_info')
				->set_html(
					'<div class="sejoli-html-message info"><p>'.
					__('Jika pengguna membeli produk yang sesuai dengan pengaturan per produk, maka aturan diskon dan komisi yang digunakan adalah yang tertera di pengaturan tersebut', 'sejoli').
					'</p></div>'
				),

			Field::make('complex', 'group_setup_per_product', __('Pengaturan per produk', 'sejoli'))

				->add_fields(
					$this->setup_per_product()
				)
				->set_layout('tabbed-vertical')
				->set_header_template('<% if (product) { %>
					<%- product %>
				  <% } %>')
		);

		return $fields;
	}

    /**
     * Setup custom fields for user group
     * Hooked via action carbon_fields_register_fields, priority 1010
     * @since 	1.3.0
     * @since 	1.3.3 	Change the field
     * @return 	void
     */
    public function setup_group_fields() {

        $container = Container::make('post_meta', __('Pengaturan', 'sejoli'))
            ->where( 'post_type', '=', 'sejoli-user-group')
            ->set_classes('sejoli-metabox')
			// ->add_tab( __('Pengaturan', 'sejoli'), array(

			// 	Field::make( 'separator', 'sep_user_priority_sale', __('Pengaturan', 'sejoli'))
			// 		->set_classes('sejoli-with-help')
			// 		->set_help_text('<a href="#" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

			// 	Field::make('text',	'priority', __('Prioritas Grup', 'sejoli'))
			// 		->set_attribute('type', 'number')
			// 		->set_attribute('min', 1)
			// 		->set_default_value(1)
			// 		->set_required(true)
			// 		->set_help_text(
			// 			__('Semakin rendah nilai, semakin tinggi tingkatan grup. 1 tertinggi', 'sejoli')
			// 		)
			// ))
			->add_tab( __('Produk', 'sejoli'),
				apply_filters('sejoli/user-group/fields', $this->get_product_setting_fields())
			)
			->add_tab(__('Pengaturan Lainnya', 'sejoli'), array(
				Field::make('checkbox', 'can_view_affiliate', __('Member bisa mengakses menu affiliasi', 'sejoli'))
			));
    }

	/**
	 * Setup user-group dropdown options to prevent duplicate query
	 * @since 	1.5.3.4
	 * @return 	array
	 */
	public function set_options() {

		if( false === $this->options ) :

			$this->options = array(
									'' => __('Tidak masuk ke grup apapun', 'sejoli')
								  ) + sejolisa_get_user_group_options();

		endif;

		return $this->options;
	}

	/**
     * Setup custom fields in user page
     * Hooked via filter sejoli/user/fields, priority 20
     * @since 	1.3.3
     * @return 	void
     */
	public function setup_user_fields($fields) {

		$fields[]	= array(
			'title'		=> __('Grup', 'sejoli'),
			'fields'	=> array(
				Field::make('select',	'user_group', __('User Grup', 'sejoli'))
					->add_options(array($this, 'set_options'))
			)
		);

		return $fields;

	}

	/**
	 * Add custom columns to user table
	 * Hooked via filter manage_user_columns, priority 10
	 * @since 	1.3.0
	 * @param  	array  $columns Current user columns
	 * @return 	array
	 */
	public function modify_user_table(array $columns) {

		unset($columns['posts']);

		$columns['sejoli-user-group']	= __('Grup', 'sejoli');

		return $columns;
	}

	/**
	 * Display custom column value
	 * Hooked via filter manage_users_custom_column, priority 20
	 * @since 	1.3.0
	 * @param 	string 	$value
	 * @param  	string 	$column_name
	 * @param  	integer $user_id
	 * @return 	string
	 */
	public function display_value_for_user_table($value, $column_name, $user_id) {

		if('sejoli-user-group' === $column_name) :

			$user_groups   = sejolisa_get_user_group_options();
			$user_group_id = sejolisa_carbon_get_user_meta($user_id, 'user_group');

			return ( array_key_exists($user_group_id, $user_groups) ) ? $user_groups[$user_group_id] : '-';

		endif;

		return $value;
	}

	/**
	 * Set user meta
	 * Hooked via filter sejoli/user/meta-data, priority 1001
	 * @since 	1.0.0
	 * @param 	WP_User $user
	 * @return 	WP_User
	 */
	public function set_user_meta($user) {

		$user_groups          = sejolisa_get_user_group_options();
		$user_group_id        = intval(sejolisa_carbon_get_user_meta($user->ID, 'user_group'));
		$user->meta->group    = ( array_key_exists($user_group_id, $user_groups) ) ? $user_groups[$user_group_id] : NULL;
		$user->meta->group_id = $user_group_id;

		return $user;
	}

	/**
	 * Set local JS variables
	 * Hooked via filter sejoli/admin/js-localize-data, priority 1
	 * @since 	1.3.0
	 * @param 	array $js_vars
	 * @return 	array
	 */
	public function set_localize_js_var($js_vars) {

		$js_vars['userlist']	= array(
			'table' => array(
				'ajaxurl' => add_query_arg(array(
					'action' => 'sejoli-user-table'
				), admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-user-table')
			),
			'update' => array(
				'ajaxurl'	=> add_query_arg(array(
					'action' => 'sejoli-user-update'
				), admin_url('admin-ajax.php')),
				'nonce'	=> wp_create_nonce('sejoli-user-update')
			),
			'export_prepare'   =>  [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-user-export-prepare'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-user-export-prepare')
			],
		);

		return $js_vars;
	}

	/**
     * Get all user role name
     * Hooked via filter sejoli/filter/roles, priority 1
     * @since   1.3.0
     * @param   array  	$role_names
     * @param 	array 	$roles
     * @return  array
     */
    public function set_role_names(array $role_names, array $roles) {

        $user_roles = array();

		if(!is_array($this->available_roles)) :
			global $wp_roles;

			$this->available_roles = $wp_roles;
		endif;

        foreach($roles as $role) :

            if(array_key_exists($role, $this->available_roles->roles)) :
                $user_roles[]['name'] = $this->available_roles->roles[$role]['name'];
            endif;

        endforeach;

        return $user_roles;
    }

	/**
	 * Export order data to CSV
	 * Hooked via action sejoli_ajax_sejoli-order-export, priority 1
	 * @since 	1.1.0
	 * @return 	void
	 */
	public function export_csv() {

		$post_data = wp_parse_args($_GET,[
			'sejoli-nonce' => NULL,
			'backend'      => false,
			'affiliate_id' => NULL,
			'user_id'      => NULL,
			'role'         => NULL,
			'group'        => NULL,
			'ID'           => NULL
		]);

		if(wp_verify_nonce($post_data['sejoli-nonce'], 'sejoli-user-export')) :

			$meta_query = array();
			$filename   = array();
			$filename[] = 'export-users';

			if(!current_user_can('manage_sejoli_orders') || false === $post_data['backend']) :
				$post_data['affiliate_id']	= get_current_user_id();
			endif;

			if(isset($post_data['affiliate_id'])) :
				$filename[] = 'affiliate-' . $post_data['affiliate_id'];
			endif;

			unset($post_data['backend'], $post_data['sejoli-nonce']);

			$args 	= array(
				'number'  => -1,
				'orderby' => 'display_name',
				'order'   => 'ASC'
			);

			if(!empty($post_data['role'])) :
	            $args['role'] = (array) $post_data['role'];
	        endif;

	        if(!empty($post_data['ID'])) :
	            $args['include'] = explode(',', $post_data['ID']);
	        endif;

			if(!empty($post_data['user_id'])) :
				if(!array_key_exists('include', $args)) :
					$args['include']	= array();
				endif;

				$args['include'][] = $post_data['user_id'];
			endif;

	        if(!empty($post_data['group'])) :

	            $meta_query[] = array(
	                'key'   => '_user_group',
	                'value' => $post_data['group']
	            );

				$filename[] = 'group-' . $post_data['group'];

	        endif;

	        if(!empty($post_data['affiliate_id'])) :

	            $meta_query[] = array(
	                'key'   => '_affiliate_id',
	                'value' => $post_data['affiliate_id']
	            );

	        endif;

	        if(0 < count($meta_query)) :
	            $args['meta_query']  = $meta_query;
	        endif;

			$csv_data = [];
			$csv_data[0]	= array(
				'User ID', 'name', 'email', 'phone', 'address', 'affiliate', 'group'
			);

			$i = 1;
			foreach(get_users($args) as $user) :

	            $user      = apply_filters('sejoli/user/meta-data', $user);
	            $affiliate = sejolisa_get_affiliate($user, 'wp_user');

	            $csv_data[$i] = array(
	                'ID'        => $user->ID,
	                'name'      => $user->display_name,
	                'email'     => $user->user_email,
					'phone'     => $user->meta->phone,
					'address'	=> $user->meta->address,
	                'affiliate' => (is_a($affiliate, 'WP_User')) ? $affiliate->display_name : '-',
	                'group'     => ( NULL === $user->meta->group ) ? '-' : $user->meta->group
	            );

	            $i++;

			endforeach;

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="' . implode('-', $filename) . '.csv"');

			$fp = fopen('php://output', 'wb');
			foreach ($csv_data as $line) :
			    fputcsv($fp, $line, ',');
			endforeach;
			fclose($fp);

		endif;
		exit;
	}

	/**
	 * Add submenu 'Sejoli User' under User menu
	 * Hooked via action admin_menu, priority 100
	 * @since 1.3.0
	 */
	public function add_custom_user_menu() {

		add_submenu_page(
			'edit.php?post_type=sejoli-user-group',
			__('Sejoli User - Manajemen', 'sejoli'),
			__('Manajemen User', 'sejoli'),
			'promote_users',
			'sejoli-user-management',
			array($this, 'display_user_list')
		);

	}

	/**
	 * Enqueue needed CSS and JS files
	 * Hooked via action admin_enqueue_scripts, priority 100
	 * @return 	void
	 */
	public function register_css_and_js() {
		wp_enqueue_style($this->plugin_name . '-post-table', SEJOLISA_URL . 'admin/css/post-table.css', [], $this->version, 'all');
	}

	/**
	 * Set $is_sejoli_page true if current page is a sejoli user management
	 * Hooked via filter sejoli/admin/is-sejoli-page, priority 100
	 * @since 	1.3.0
	 * @param  	boolean $is_page
	 * @return	boolean
	 */
	public function is_sejoli_page($is_page) {

		global $pagenow;

		if('edit.php' === $pagenow && isset($_GET['page']) && 'sejoli-user-management' === $_GET['page']) :
			return true;
		endif;

		return $is_page;
	}

	/**
	 * Display list user
	 * @since 	1.3.0
	 * @return 	void
	 */
	public function display_user_list() {
		require plugin_dir_path( __FILE__ ) . 'partials/user/page.php';
	}

	/**
	 * Add group setting fields in product
	 * Hooked via filter sejoli/product/fields
	 * @since 	1.3.0
	 * @since 	1.5.3.4 	Change on how set user group dropdown options
	 * @param  	array  		$fields
	 * @return 	array
	 */
	public function setup_group_setting_fields(array $fields) {

		$fields[]	= array(
			'title'		=> __('Grup', 'sejoli'),
			'fields'	=> array(

				// Group Setting
				Field::make('separator', 'sep_sejoli_buy_group', __('Pengaturan Pembelian', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="#" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make('checkbox',	'user_group_buy_permission', __('Hanya grup user tertentu yang membeli produk ini', 'sejoli'))
					->set_help_text(__('Jika diaktifkan, hanya user dengan grup tertentu saja yang bisa membeli produk ini', 'sejoli')),

				Field::make('set', 	'user_group_buy_list', __('Pilih grup', 'sejoli'))
					->set_required(true)
					->set_options(array($this, 'set_options'))
					->set_help_text(__('Pilih grup yang boleh membeli produk ini', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'user_group_buy_permission',
							'value'	=> true,
						)
					)),

				Field::make('rich_text', 'user_group_buy_restricted_message', __('Pesan untuk user yang tidak diberikan akses membeli produk ini', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'user_group_buy_permission',
							'value'	=> true,
						)
					))
					->set_default_value(__('Maaf, anda tidak perkenankan untuk membeli produk ini.', 'sejoli')),

				// Group Setting
				Field::make('separator', 'sep_sejoli_update_group', __('Pengaturan Update Grup', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="#" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

				Field::make('checkbox',	'user_group_update_check', __('Ubah grup user yang telah membeli produk ini', 'sejoli'))
					->set_help_text(__('Jika diaktifkan, sistem akan mengubah grup user setelah pembelian selesai', 'sejoli')),

				Field::make('select', 'user_group_update', __('Ubah ke grup', 'sejoli'))
					->add_options( array($this, 'set_options') )
					->set_conditional_logic(array(
						array(
							'field'	=> 'user_group_update_check',
							'value'	=> true
						)
					)),

				Field::make('checkbox',	'user_group_update_condition', __('Ubah grup user dengan kondisi tertentu', 'sejoli'))
					->set_help_text(__('Jika diaktifkan, sistem akan mengubah grup user dengan kondisi yang sudah ditentukan', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'user_group_update_check',
							'value'	=> true
						)
					)),

				Field::make('set', 	'user_group_update_list', __('Ubah grup jika user termasuk dalam grup dibawah ini', 'sejoli'))
					->set_required(true)
					->set_options( array($this, 'set_options'))
					->set_help_text(__('Pilih grup yang akan diubah ketika membeli produk ini', 'sejoli'))
					->set_conditional_logic(array(
						array(
							'field'	=> 'user_group_update_check',
							'value'	=> true
						),
						array(
							'field'	=> 'user_group_update_condition',
							'value'	=> true,
						)
					)),
			)
		);

		return $fields;
	}

	/**
	* Set group metadata to product
	* Hooked via filter sejoli/product/meta, priority 50
	* @since 	1.3.0
	* @param  	WP_Post $product
	* @param  	int     $product_id
	* @return 	WP_Post
	*/
	public function setup_group_product_meta(\WP_Post $product, $product_id) {

		$product->group = array(
			'buy_group'              => sejolisa_carbon_get_post_meta($product_id, 'user_group_buy_permission'),
			'buy_group_list'         => sejolisa_carbon_get_post_meta($product_id, 'user_group_buy_list'),
			'buy_restricted_message' => sejolisa_carbon_get_post_meta($product_id, 'user_group_buy_restricted_message'),
			'update_group'           => sejolisa_carbon_get_post_meta($product_id, 'user_group_update_check'),
			'update_group_to'        => intval(sejolisa_carbon_get_post_meta($product_id, 'user_group_update')),
			'update_group_condition' => boolval(sejolisa_carbon_get_post_meta($product_id, 'user_group_update_condition')),
			'update_group_list'      => sejolisa_carbon_get_post_meta($product_id, 'user_group_update_list'),
		);

		return $product;
	}

	/**
	* Set discount based on current user group
	* Hooked via filter sejoli/product/price, priority 100
	* @since   1.3.0
	* @param   float   $price
	* @param   WP_Post $product
	* @return  float
	*/
	public function set_discount_product_price( float $price, \WP_Post $product) {

		$donation_active     = boolval( sejolisa_carbon_get_post_meta($product->ID, 'donation_active') );

		if(is_user_logged_in() && false === $donation_active) :

			$user_id       = get_current_user_id();
			$user_group_id = intval(sejolisa_carbon_get_user_meta($user_id, 'user_group'));

			if(0 < $user_group_id) :

				$group_detail     = sejolisa_get_group_detail($user_group_id);
				$discounted_price = $discount = 0;
				$type             = 'fixed';

				if(
					is_array($group_detail['per_product']) &&
					array_key_exists($product->ID, $group_detail['per_product'])
				) :

					$group_setup = $group_detail['per_product'][$product->ID];

				else :

					$group_setup = $group_detail;

				endif;

				if(false !== $group_setup['enable_discount']) :

					$discount = $group_setup['discount_price'];
					$type 	  = $group_setup['discount_price_type'];

				endif;

				if(0 < $discount) :

					if('percentage' === $type) :
						$discounted_price = $price - ( $price * $discount / 100 );
					elseif('fixed' === $type) :
						$discounted_price = $price - $discount;
					endif;

					$price = (0 > $discounted_price) ? 0 : $discounted_price;

				endif;

			endif;

		endif;
 	   	return $price;
    }

	/**
	 * Set affiliate commission by group
	 * Hooked via filter sejoli/order/commission, priority 100
	 * @since 	1.3.0
	 * @param 	float  	$commission
	 * @param 	array  	$commission_set
	 * @param 	array  	$order_data
	 * @param 	integer $tier
	 * @param 	integer $affiliate_id
	 * @return 	float
	 */
	public function set_affiliate_commission($commission = 0.0, $commission_set = array(), $order_data = array(), $tier = 0, $affiliate_id = 0) {

		$user_group_id = sejolisa_carbon_get_user_meta($affiliate_id, 'user_group');

		if(0 < $user_group_id) :

			$group_detail    = sejolisa_get_group_detail($user_group_id);
			$product_id      = $order_data['product_id'];
			$commission_data = array();

			// Check group commission set
			if(
				is_array($group_detail['per_product']) &&
				array_key_exists($product_id, $group_detail['per_product'])
			) :
				$commission_data = $group_detail['per_product'][$product_id]['commissions'];
			else :
				$commission_data = $group_detail['commissions'];
			endif;

			// Calculate commission by affiliate tier
			if(array_key_exists($tier, $commission_data)) :

				$commission_set = $commission_data[$tier];

				if('percentage' === $commission_set['type']) :

					$grand_total = apply_filters('sejoli/commission/order-grand-total', floatval($order_data['grand_total']), $order_data);
					$commission  = floatval( $grand_total * $commission_set['fee'] / 100 );

				elseif('fixed' === $commission_set['type']) :

					$commission = floatval($commission_set['fee']) * $order_data['quantity'];

				endif;

			endif;

		endif;

		return $commission;
	}

	/**
	 * Update user group when an order completed
	 * Hooked via action sejoli/order/set-status/completed, priority 199
	 * @since 	1.3.0
	 * @param  	array  $order_data
	 * @return 	void
	 */
	public function update_user_group(array $order_data) {

		$order_data = wp_parse_args($order_data,[
			'product_id' => NULL,
			'user_id'    => NULL
		]);

		if(
			!empty($order_data['product_id']) &&
			!empty($order_data['user_id'])
		) :

			$response = sejolisa_check_update_user_group_by_product($order_data['product_id'], $order_data['user_id']);

			if(false !== $response['update'] && !empty($response['group'])) :

				update_user_meta($order_data['user_id'], '_user_group', $response['group']);

				do_action(
					'sejoli/log/write',
					'update-user-group-success',
					sprintf(
						__('User %s group updated to %s', 'sejoli'),
						$order_data['user_id'],
						$response['group']
					)
				);

			else :

				do_action(
					'sejoli/log/write',
					'update-user-group-error',
					implode('. ', $response['error']['message'])
				);

			endif;
		endif;
	}
}
