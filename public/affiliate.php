<?php

namespace SejoliSA\Front;

use Delight\Cookie\Cookie;

class Affiliate {

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
	 * Affiliate data
	 * @since 	1.0.0
	 * @access	protected
	 */
	protected $affiliate = false;

	/**
	 * Product data
	 * @since 	1.0.0
	 * @access	protected
	 */
	protected $product = false;

	/**
	 * Refere data
	 * @since 	1.0.0
	 * @access	protected
	 */
	protected $target = false;

	/**
	 * Set if current request is already checked
	 * @since 	1.4.1
	 * @var 	boolean
	 */
	protected $checked = false;

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
     *  Set end point custom menu
     *  Hooked via action init, priority 999
     *  @since 1.0.0
     *  @access public
     *  @return void
     */
    public function set_endpoint()
    {
		add_rewrite_rule( '^aff/([^/]*)/([^/]*)/([^/]*)/?',		'index.php?affiliate-link=1&affiliate=$matches[1]&product=$matches[2]&target=$matches[3]','top');
		add_rewrite_rule( '^aff/([^/]*)/([^/]*)/?',				'index.php?affiliate-link=1&affiliate=$matches[1]&product=$matches[2]','top');

        flush_rewrite_rules();
    }

    /**
     * Set custom query vars
     * Hooked via filter query_vars, priority 999
     * @since   1.0.0
     * @access  public
     * @param   array $vars
     * @return  array
     */
    public function set_query_vars($vars = array())
    {
        $vars[] = 'affiliate-link';
		$vars[] = 'affiliate';
		$vars[] = 'product';
		$vars[] = 'target';

        return $vars;
    }

	/**
	 * Set affiliate data to cookie
	 * Hooked via action sejoli/affiliate/set-cookie, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$args
	 * @return 	void
	 */
	public function set_cookie(array $args) {

		$affiliate_id        = $args['affiliate']->ID;
		$product_id          = $args['product']->ID;

		$cookie_name         = sejolisa_get_cookie_name();
		$cookie_age          = absint(sejolisa_carbon_get_theme_option('sejoli_cookie_age'));
		$cookie_age          = (0 < $cookie_age) ? $cookie_age : 30;
		$lifespan_cookie_day = time() + ( DAY_IN_SECONDS * $cookie_age );
		$affiliate_data      = $current_cookie = sejolisa_get_affiliate_cookie();

		$affiliate_data['general']              = $affiliate_id;

		if( true === sejolisa_user_can_affiliate_the_product($product_id, $affiliate_id ) ) :
			$affiliate_data['product'][$product_id] = $affiliate_id;
		endif;

		$affiliate_data = apply_filters('sejoli/affiliate/cookie-data', $affiliate_data, $product_id, $affiliate_id);

		setcookie($cookie_name, serialize($affiliate_data), $lifespan_cookie_day, COOKIEPATH, COOKIE_DOMAIN);
	}

	/**
     * Set coupon data to affiliate cookie
     * Hooked via filter sejoli/affiliate/cookie-data, priority 20
     * @since   1.5.1
     * @param   array   $cookie_data    Current cookie data
     * @param   int     $product_id     The ID of affiliate product ID
     * @param   int     $affiliate_id   The ID of affiliate ID
     * @return  array   Manipulated cookie data
     */
    public function set_coupon_to_cookie(array $cookie_data, int $product_id, int $affiliate_id) {

        if( isset($_GET['coupon']) ) :

            $cookie_data['coupon'][$product_id] = sanitize_text_field( $_GET['coupon'] );

        endif;

        return $cookie_data;
    }

	/**
	 * Redirect customer to selected sales page
	 * Hooked via action sejoli/affiliate/redirect, priority 999
	 * @since 	1.0.0
	 * @param 	array 	$args
	 * @return 	void
	 */
	public function redirect(array $args) {

		$links       = [];
		$i           = 0;
		$main_link   = sejolisa_carbon_get_post_meta($args['product']->ID, 'sejoli_landing_page');
		$other_links = sejolisa_carbon_get_post_meta($args['product']->ID, 'sejoli_affiliate_links');

		if( !empty($main_link) ) :
	        $links[$i] = [
	            'link'           => esc_url($main_link)
	        ];
	    endif;

	    foreach( (array) $other_links as $link ) :
	        $key         = $i .'-'.sanitize_title($link['title']);
	        $links[$key] = [
	            'link'           => esc_url($link['link'])
	        ];

	        $i++;
	    endforeach;

		$redirect_link = '';
		$target        = $args['target'];

		if ('checkout' === $target) :

		    $redirect_link = get_permalink($args['product']->ID);

		elseif (!empty($target) && isset($links[$target])) :

		    $redirect_link = $links[$target]['link'];

		elseif (is_array($links) && !empty($links) && isset($links[0])) :

		    $redirect_link = $links[0]['link'];

		else :

		    $redirect_link = '';

		endif;

		if(empty($redirect_link)) :
			$redirect_link = get_permalink($args['product']->ID);
		endif;

		if(empty($redirect_link)) :

			wp_die(
				__('Terjadi kesalahan pada link affiliasi. Kontak pemilik website ini', 'sejoli'),
				__('Kesalahan pada pengalihan', 'sejoli')
			);

		elseif( false === sejolisa_user_can_affiliate_the_product($args['product']->ID, $args['affiliate']->ID ) ) :

			wp_die(
				__('Link affiliasi ini tidak diperkenankan. Silahkan hubungi pemilik link affiliasi ini', 'sejoli'),
				__('Kesalahan pada link affiliasi', 'sejoli')
			);

		endif;

		wp_redirect($redirect_link);
		exit;
	}

    /**
     * Check parse query and if aff found, $enable_framework will be true
     * Hooked via action parse_query, priority 999
     * @since 	1.0.0
     * @since 	1.5.2 	Add conditional check if current product is affiliate-able
     * @access 	public
     * @return 	void
     */
    public function check_parse_query()
    {
		global $wp_query;

		if(is_admin()) :
			return;
		endif;

		if($this->checked) :
			return;
		endif;

        if(isset($wp_query->query_vars['affiliate-link']) && false === $this->checked) :

			$this->checked = true;

			if(isset($wp_query->query_vars['affiliate']) && !empty($wp_query->query_vars['affiliate'])) :
				$this->affiliate = intval($wp_query->query_vars['affiliate']);
			endif;

			if(isset($wp_query->query_vars['product']) && !empty($wp_query->query_vars['product'])) :
				$this->product = intval($wp_query->query_vars['product']);
			endif;

			if(isset($wp_query->query_vars['target']) && !empty($wp_query->query_vars['target'])) :
				$this->target = $wp_query->query_vars['target'];
			endif;

			$product   = sejolisa_get_product($this->product);
			$affiliate = sejolisa_get_user($this->affiliate);

			if(!is_a($product, 'WP_Post')) :

				wp_die(
					__('Produk tidak terdaftar di database', 'sejoli'),
					__('Produk tidak valid', 'sejoli')
				);

				exit;
			else :

				$enable_affiliate = boolval(sejolisa_carbon_get_post_meta( $product->ID, 'sejoli_enable_affiliate'));

				// Add conditional check if current product is affiliate-able
				if( true !== $enable_affiliate ) :

					wp_die(
						__('Produk ini sudah tidak diaffiliasikan lagi', 'sejoli'),
						__('Affiliasi tidak valid', 'sejoli')
					);

					exit;
				endif;

			endif;

			if(!is_a($affiliate, 'WP_User')) :
				wp_die(
					__('Affiliasi tidak terdaftar di database', 'sejoli'),
					__('Affiliasi tidak valid', 'sejoli')
				);
				exit;
			endif;

			$args = [
				'affiliate'	=> $affiliate,
				'product'	=> $product,
				'target'	=> $this->target
			];

			do_action('sejoli/affiliate/set-cookie', $args);
			do_action('sejoli/affiliate/redirect', $args);

			exit;

        endif;
    }

    /**
     * Generate affililiate link
     * Hooked via filter sejoli/affiliate/link, prioirty 1
     * @since   1.0.0
     * @param   string $link [description]
     * @param   array  $args [description]
     * @param   string $key  [description]
     * @return  string
     */
    public function set_affiliate_link($link, array $args, $key = '') {
		$link = home_url('/aff');
		$link = $link . '/' . $args['user_id'] . '/' .$args['product_id'] . '/' ;

		if(!empty($key)) :
			$link .= $key;
		endif;

		return esc_url( $link );
    }
}
