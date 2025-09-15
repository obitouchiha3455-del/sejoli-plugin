<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Member {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.4
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.4
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Member menu setup
	 * @since 	1.1.4
	 * @access 	private
	 * @var 	array
	 */
	private $menu = array();

	/**
	 * Member template pages
	 * @since 	1.1.7
	 * @var 	array
	 */
	protected $templates = array();


	/**
	 * Set post type that Sejoli Member Area tempate page cant be used in
	 * @since 	1.3.2
	 * @var 	array
	 */
	protected $exclude_post_types = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.1.4
	 * @param 	string    $plugin_name       The name of this plugin.
	 * @param 	string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->menu = array(
			0 => array(
				'title'	=>	__('Dashboard', 'sejoli'),
				'object'=> 'sejoli-dashboard',
				'url'	=> home_url('/member-area/')
			),
			1 => array(
				'title'	=> __('Affiliasi', 'sejoli'),
				'object'=> 'sejoli-affiliasi',
				'url'	=> home_url(),
			),
			2 => array(
				'title'	=> __('Leaderboard', 'sejoli'),
				'object'=> 'sejoli-leaderboard',
				'url'	=> home_url('/member-area/leaderboard'),
			),
			3 => array(
				'title'	=> __('Order', 'sejoli'),
				'object'=> 'sejoli-order',
				'url'	=> home_url('/member-area/order')
			),
			8 => array(
				'title'	=> __('Langganan', 'sejoli'),
				'object'=> 'sejoli-order',
				'url'	=> home_url('/member-area/subscription')
			),
			4 => array(
				'title'	=> __('Akses', 'sejoli'),
				'object'=> 'sejoli-akses',
				'url'	=> home_url('/member-area/akses')
			),
			5 => array(
				'title'	=> __('Profile', 'sejoli'),
				'object'=> 'sejoli-profile',
				'url'	=> home_url('/member-area/profile')
			),
			6 => array(
				'title'	=> __('Lisensi', 'sejoli'),
				'object'=> 'sejoli-lisensi',
				'url'	=> home_url('/member-area/license')
			),
			7 => array(
				'title'	=> __('Logout', 'sejoli'),
				'object'=> 'sejoli-logout',
				'url'	=> home_url('/member-area/logout')
			)
		);

		$this->templates = array(
			'sejoli-member-page.php'	=> __('Sejoli Member Page', 'sejoli')
		);

		$this->exclude_post_types = array(
			SEJOLI_PRODUCT_CPT
		);
    }

	/**
	 * Register member menu nav
	 * Hooked via action admin_head-nav-menus.php, priority 1
	 * @since 	1.1.4
	 * @return 	void
	 */
	public function register_menu_links() {
		add_meta_box( 'sejoli-member-link-menu', __('Sejoli Member Links', 'sejoli'), array($this, 'register_links'), 'nav-menus', 'side', 'default');
	}

	/**
	 * Register member links
	 * @since 	1.1.4
	 * @param  	string 	$object
	 * @param  	array 	$args   	Parameter and arguments
	 * @return 	void
	 */
	public function register_links( $object, $args ) {

		global $nav_menu_selected_id;

		$member_items 	= array();
		$member_menu 	= apply_filters('sejoli/member-area/backend/menu', $this->menu);

		$i = 1;

		foreach($member_menu as $_menu) :

			$menu                     = $_menu;
			$menu['ID']               = $i;
			$menu['db_id']            = 0;
			$menu['menu_item_parent'] = 0;
			$menu['object_id']        = 1;
			$menu['post_parent']      = 0;
			$menu['type']             = 'sejoli-member-link';
			$menu['type_label']       = 'Sejoli Member Endpoint';
			$menu['target']           = '';
			$menu['attr_title']       = '';
			$menu['description']      = '';
			$menu['classes']          = array();
			$menu['xfn']              = '';
			$member_items[]           = (object) $menu;

			$i++;

		endforeach;

		$this->display_meta_box($member_items);
	}

	/**
	 * Display member menu metabox
	 * @since 	1.1.4
	 * @param 	array 	$member_items
	 * @return 	void
	 */
	private function display_meta_box(array $member_items) {

		global $nav_menu_selected_id;

		$db_fields = false;
		// If your links will be hieararchical, adjust the $db_fields array bellow

		if ( false ) :
			$db_fields = array( 'parent' => 'parent', 'id' => 'post_parent' );
		endif;

		$walker = new \Walker_Nav_Menu_Checklist( $db_fields );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		); ?>

		<div id="my-plugin-div">
			<div id="tabs-panel-my-plugin-all" class="tabs-panel tabs-panel-active">
			<ul id="my-plugin-checklist-pop" class="categorychecklist form-no-clear" >
			<?php echo walk_nav_menu_tree(
					array_map(
						'wp_setup_nav_menu_item',
						$member_items
					), 0,
					(object) array(
						'walker' => $walker
					)
				);
			?>
			</ul>

			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php
						echo esc_url(add_query_arg(
							array(
								'my-plugin-all' => 'all',
								'selectall' => 1,
							),
							remove_query_arg( $removed_args )
						));
					?>#sejoli-member-link-menu" class="select-all"><?php _e( 'Select All' ); ?></a>
				</span>

				<span class="add-to-menu">
					<input
						type="submit"
						<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?>
						class="button-secondary submit-add-to-menu right"
						value="<?php esc_attr_e( 'Add to Menu' ); ?>"
						name="add-my-plugin-menu-item"
						id="submit-my-plugin-div"
					/>
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Register custom navigation
	 * Hooked via action after_setup_theme, priority 1
	 * @since 	1.1.4
	 * @return 	void
	 */
	public function register_nav_menu() {
		register_nav_menu('sejoli-member-nav', __('Sejoli Member Menu', 'sejoli'));
	}

	/**
	 * Register carbon fields for menu
	 * Hooked via action carbon_fields_register_fields, priority 1
	 * @since 	1.1.7
	 * @return 	void
	 */
	public function register_custom_fields() {

		Container::make( 'nav_menu_item', __( 'Menu Settings' ) )
		    ->add_fields( array(
		        Field::make( 'text', 'menu_icon', __( 'Menu Icon', 'sejoli' ))
					->set_help_text(__('Untuk icon bisa diambil dari halaman <a href="https://semantic-ui.com/elements/icon.html">ini</a>', 'sejoli')),
		    ));
	}

	/**
	 * Adds our template to the page dropdown for v4.7+
	 * Hooked via filter theme_template, priority 1
	 * @since 	1.1.7
	 * @since 	1.3.0 	Change filter from theme_page_template to theme_template
	 * @since 	1.3.2 	Add several parameters to set the template page to be shown
	 * @param 	array 	$posts_templates
	 * @return  array
	 */
	public function add_member_template( $posts_templates, $theme, $post, $post_type ) {

		if(!in_array($post_type, $this->exclude_post_types)) :
			$posts_templates = $posts_templates + $this->templates;
		endif;

		return $posts_templates;
	}

	/**
	 * Register member page templates
	 * Hooked via action page_attributes_dropdown_pages_args, priority 1
	 * Hooked via action wp_insert_post_data, priority 1
	 * @since 	1.1.7
	 * @param  	array 	$atts 	Page attributes
	 * @return 	array
	 */
	public function register_member_templates($atts) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();

		if ( empty( $templates ) ) :
			$templates = array();
		endif;

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = $templates + $this->templates;

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}

}
