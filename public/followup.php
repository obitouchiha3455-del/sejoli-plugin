<?php

namespace SejoliSA\Front;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class FollowUp {

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
	 * Check if current process is running
	 * @since	1.4.2
	 * @var 	boolean
	 */
	protected $is_running = false;

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
		add_rewrite_rule( '^followup/([^/]*)/([^/]*)/?',				'index.php?followup=1&order-id=$matches[1]&content=$matches[2]','top');

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
        $vars[] = 'followup';
		$vars[] = 'order-id';
		$vars[] = 'content';

        return $vars;
    }

    /**
     * Check parse query and if aff found, $enable_framework will be true
     * Hooked via action parse_query, priority 999
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function check_parse_query()
    {
		global $wp_query;

		if(is_admin()) :
			return;
		endif;

        if(
			isset($wp_query->query_vars['followup']) &&
			false === $this->is_running
		) :

			$this->is_running = true;
			$content_id       = intval($wp_query->query_vars['content']) - 1;
			$order_id         = intval($wp_query->query_vars['order-id']);
			$response         = sejolisa_get_order(['ID' => $order_id]);

			if(false !== $response['valid'] && 'on-hold' === $response['orders']['status']) :

				if(
					current_user_can('manage_options') ||
					(
						0 !== intval($response['orders']['affiliate_id']) &&
						get_current_user_id() === intval($response['orders']['affiliate_id'])
					)
				) :
					$product  = $response['orders']['product'];
					$contents = sejolisa_carbon_get_post_meta($product->ID, 'followup_content');

					if(isset($contents[$content_id])) :
						$buyer 	 = $response['orders']['user'];
						$content = $contents[$content_id]['content'];

						$content = rawurlencode(
										apply_filters(
											'sejoli/notification/content',
											$content,
											$response['orders'],
											'whatsapp',
											'on-hold'
										)
								   );
						$phone = apply_filters('sejoli/user/phone', $buyer->meta->phone, false);
						if ( wp_is_mobile() ) :
							$url = 'https://wa.me/' . $phone . '?text='. $content;
						else :
							$url = 'https://wa.me/' . $phone . '?text='. $content;
						endif;

						header("Location: ".$url."");
						exit;

					else :
						wp_die(
							__('Maaf, konten follow up untuk order ini tidak ada.', 'sejoli'),
							__('Tidak bisa difollowup', 'sejoli')
						);
					endif;

				else :

					wp_die(
						__('Maaf, anda tidak punya hak untuk followup order ini.', 'sejoli'),
						__('Tidak bisa difollowup', 'sejoli')
					);

				endif;

			else :
				wp_die(
					__('Maaf, order ini tidak bisa di-followup.', 'sejoli'),
					__('Tidak bisa difollowup', 'sejoli')
				);
			endif;

			exit;
        endif;
    }
}
