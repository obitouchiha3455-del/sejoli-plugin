<?php

namespace SejoliSA\Front;

class Access {

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
	 * Access post data
	 * @since 	1.0.0
	 * @access 	private
	 * @var 	WP_Post
	 */
	private $access;

	/**
	 * Product access data
	 * @since 	1.0.0
	 * @access 	private
	 * @var 	array
	 */
	private $access_products = array();

	/**
	 * Current user access data
	 * @since 	1.0.0
	 * @access 	private
	 * @var 	array
	 */
	private $user_access = array();

	/**
	 * State if current user can access or note
	 * @since 	1.0.0
	 * @access 	private
	 * @var 	boolean
	 */
	private $can_access = true;

	/**
	 * Product matches with access
	 * @since 	1.0.0
	 * @access 	private
	 * @var 	WP_Post
	 */
	private $found_product;

	/**
	 * Block reason
	 * @since 	1.0.0
	 * @access 	private
	 * @var 	string
	 */
	private $block_reason = NULL;

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
	 * Get product ID from access
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function get_products() {

		$products = sejolisa_carbon_get_post_meta($this->access->ID, 'product_association');

		foreach($products as $_product) :
			$this->access_products[]	= $_product['id'];
		endforeach;
	}

	/**
	 * Check access data with product data
	 * @return [type] [description]
	 */
	protected function check_access_by_products() {

		$can_access = false;

		foreach($this->access_products as $product_id) :
			if(array_key_exists($product_id, $this->user_access)) :
				$this->found_product = $this->user_access[$product_id];
				$can_access = true;
			endif;
		endforeach;

		if(false === $can_access) :
			$this->can_access = false;
			$this->block_reason = 'not-bought';
			return;
		endif;

		$this->check_subscription_active();
	}


	/**
	 * Check if current product subscription is valid
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function check_subscription_active() {

		$can_access = true;

		if(
			!empty($this->found_product['end_active']) &&
			current_time('timestamp') > strtotime($this->found_product['end_active'])
		) :
			$can_access = false;
		endif;

		if(false === $can_access) :
			$this->can_access = false;
			$this->block_reason = 'expired';
			return;
		endif;

		$this->check_drip_content();
	}

	/**
	 * Check drip content
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function check_drip_content() {

		if(is_a($this->access, 'WP_Post') && property_exists($this->access, 'ID')) :
			$drip_day	= intval(sejolisa_carbon_get_post_meta($this->access->ID, 'drip_day'));
			$order_day 	= intval($this->found_product['start_day']);

			if($drip_day > $order_day) :
				$this->can_access   = false;
				$this->block_reason = 'drip-content';
				return;
			endif;
		endif;
	}

	/**
	 * Get error content, both title and message
	 * @since 	1.0.0
	 * @return 	array
	 */
	protected function get_error_content() {

		$message = $title = '';

		switch($this->block_reason) :

			case 'not-bought' :
				$title   = __('Anda belum membeli produk untuk konten ini', 'sejoli');
				$message = sejolisa_carbon_get_post_meta($this->access->ID, 'access_block_message');
				break;

			case 'expired' :
				$title   = __('Akses untuk konten ini sudah berakhir.', 'sejoli');
				$message = sejolisa_carbon_get_post_meta($this->access->ID, 'access_expired_message');
				break;

			case 'drip-content' :
				$title   = __('Anda belum bisa mengakses untuk konten ini.', 'sejoli');
				$message = sejolisa_carbon_get_post_meta($this->access->ID, 'access_drip_day_message');
				break;

		endswitch;

		return [
			'title'   => $title,
			'message' => $message
		];
	}

	/**
	 * Display block access message
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function display_block_access() {

		if(false === $this->can_access) :

			$redirect_active = boolval(sejolisa_carbon_get_post_meta($this->access->ID, 'access_redirect_active'));

			if(true === $redirect_active) :
				wp_redirect(esc_url(sejolisa_carbon_get_post_meta($this->access->ID, 'access_redirect_link')));
				exit;
			else :

				$content = $this->get_error_content();

				wp_die(
					$content['message'],
					$content['title']
				);
			endif;

		endif;

	}

	/**
	 * Check access protection page
	 * Hooked via action wp, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function check_access_page() {

		global $post, $pagenow;

		if(
			is_singular('sejoli-access') &&
			property_exists($post, 'ID') &&
			(
				!current_user_can('manage_options') &&
				'post.php' !== $pagenow
			)
		):
			$this->access      = $post;
			$user_id           = get_current_user_id();
			$this->user_access = sejolisa_get_user_access_products($user_id);

			$this->get_products();
			$this->check_access_by_products();
			$this->display_block_access();
		endif;
	}

	/**
	 * Check if user has access
	 * Hooked via filter sejoli/access/has-access, priority 1
	 * @since 	1.0.0
	 * @param  	boolean 	$can_access 	Can access default value
	 * @param  	integer  	$user_id    	Obviously it's USER ID
	 * @param  	integer  	$product_id 	Obviously it's PRODUCT ID
	 * @return 	boolean 	State of able to access
	 */
	public function does_user_has_access($can_access = true, $user_id = 0, $product_id = 0) {

		$user_id                 = $user_id;
		$this->access_products[] = $product_id;
		$this->user_access       = sejolisa_get_user_access_products($user_id);

		$this->check_access_by_products();
		$this->display_block_access();

		return (bool) $this->can_access;
	}

	/**
	 * by pass issue if page template detected not works
	 * Hooked via action wp, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function sejoli_custom_template_include( $template ) {
	    
	    if ( is_singular('sejoli-access') ) {
	        // Path ke template custom
	        $new_template = SEJOLISA_DIR . 'template/member-template.php';

	        if ( file_exists( $new_template ) && $template === $new_template ) {
	            return $new_template;
	        }
	    }

	    return $template;

	}
	
}
