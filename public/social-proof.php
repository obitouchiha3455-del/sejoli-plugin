<?php

namespace SejoliSA\Front;

use Carbon\Carbon;

class SocialProof
{
    /**
     * The ID of this plugin.
     *
     * @since    1.5.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.5.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * If social proof enabled
     * @since   1.5.0
     * @var     boolean
     */
    protected $is_enabled = false;

    /**
     * If buyer name is sensored
     * @since   1.5.0
     * @var     boolean
     */
    protected $is_name_sensored = false;

    /**
     * If buyer avatar is showed
     * @since   1.5.0
     * @var     boolean
     */
    protected $is_avatar_showed  = false;

    /**
     * If product image is showed
     * @since   1.5.1.1
     * @var     boolean
     */
    protected $is_product_image_showed = false;

    /**
     * First popup show delay time in mileseconds
     * @since   1.5.0
     * @var     integer
     */
    protected $first_time   = 0;

    /**
     * Popup show time in mileseconds
     * @since   1.5.0
     * @var     integer
     */
    protected $display_time = 2000;

    /**
     * Delay between popup
     * @since   1.5.0
     * @var     integer
     */
    protected $delay_time = 2000;

    /**
     * Set popup position
     * @since   1.5.0
     * @var     string
     */
    protected $position = 'bottom right';

    /**
     * Popup text
     * @since   1.5.0
     * @var     string
     */
    protected $popup_text = '';

    /**
     * Current product ID
     * @since   1.5.0
     * @var     integer
     */
    protected $product_id = 0;

    /**
     * Check if query already running
     * @since   1.5.0
     * @var     boolean
     */
    protected $is_checked = false;

    /**
     * Construction
    */
    public function __construct( $plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Get social proof image
     * @since   1.5.1.1
     * @param   object  $order
     * @return  string  Image url based
     */
    protected function get_image( $order ) {

        if( $this->is_avatar_showed ) :
            return get_avatar_url( $order->user_email );
        elseif( $this->is_product_image_showed ):
            return get_the_post_thumbnail_url( $order->product_id, 'post-thumbnail' );
        endif;

        return SEJOLISA_URL . 'public/img/placeholder.png';

    }

    /**
     * Get social proof order data
     * Hooked via sejoli_ajax-get-social-proof-data, priority 1
     * @since   1.5.0
     * @return  string
     */
    public function get_order_data() {

        $response = array(
            'success'   => false,
            'data'      => array()
        );

        $post     = wp_parse_args($_POST, array(
            'product_id'    => NULL,
            'orders'        => NULL
        ));

        $product_id = intval( $post['product_id'] );

        if( 0 !== $product_id ) :

            $this->is_avatar_showed        = boolval( sejolisa_carbon_get_post_meta( $product_id, 'social_proof_display_avatar' ) );
            $this->is_product_image_showed = boolval( sejolisa_carbon_get_post_meta( $product_id, 'social_proof_display_product' ) );
            $current_orders                = ( !empty($post['orders']) ) ? explode(',', $post['orders']) : array();
            $order_status                  = sejolisa_carbon_get_post_meta( $product_id, 'social_proof_order_status' );
            $order_status                  = 'both' === $order_status ? array( 'on-hold', 'completed' ) : array( $order_status );

            $query = \SejoliSA\Model\Order::reset()
                        ->set_filter('product_id', $product_id)
                        ->set_filter('status', $order_status );

            if( 0 < count( $current_orders ) ) :
                $query->set_filter('ID', $current_orders, 'NOT IN');
            endif;

            $query = $query->set_data_order('created_at', 'DESC')
                        ->set_data_length(1)
                        ->get()
                        ->respond();

            if(true === $query['valid']) :

                $order            = $query['orders'][0];
                $current_orders[] = $order->ID;
                $sensored         = boolval( sejolisa_carbon_get_post_meta( $product_id, 'social_proof_sensor_buyer_name' ) );

                Carbon::setLocale('id');

                $response['success'] = true;
                $response['data']    = array(
                    'orders'    => implode(',', $current_orders),
                    'name'      => ( $sensored ) ? sejolisa_get_sensored_string( $order->user_name ) : $order->user_name,
                    'avatar'    => $this->get_image( $order ),
                    'product'   => $order->product_name,
                    'time'      => Carbon::parse($order->created_at)->diffForHumans(current_time('mysql'))
                );

            endif;

        endif;

        wp_send_json($response);
        exit;
    }

    /**
     *  Set end point custom menu
     *  Hooked via action init, priority 1019
     *  @since 1.5.0
     *  @access public
     *  @return void
     */
    public function set_endpoint()
    {
		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		add_rewrite_rule( '^sejoli-social-proof/([^/]*)/?',		                'index.php?sejoli-social-proof=$matches[1]','top');;
        add_rewrite_rule( '^sejoli-social-proof-iframe/([^/]*)/([^/]*)/?',		'index.php?sejoli-social-proof-iframe-js=$matches[1]&request=$matches[2]','top');
        add_rewrite_rule( '^sejoli-social-proof-ajax/([^/]*)/?',		        'index.php?sejoli-social-proof-ajax=$matches[1]','top');;

        flush_rewrite_rules();
    }

    /**
     * Set custom query vars
     * Hooked via filter query_vars, priority 1019
     * @since   1.5.0
     * @access  public
     * @param   array $vars
     * @return  array
     */
    public function set_query_vars($vars)
    {
        $vars[] = 'sejoli-social-proof';
        $vars[] = 'sejoli-social-proof-ajax';
        $vars[] = 'sejoli-social-proof-iframe-js';
        $vars[] = 'request';

        return $vars;
    }

    /**
     * Set product data based on product ID
     * @since   1.5.0
     * @param   int $product_id
     * @return  WP_Post|WP_Error
     */
    protected function set_product( int $product_id ) {

        $product    = sejolisa_get_product( $product_id );

        if( is_wp_error($product) ) :

            wp_die(
                $product->get_error_message(),
                __('Problem happened', 'sejoli')
            );

            exit;

        elseif(
            !is_a($product, 'WP_Post') ||
            'sejoli-product' !== $product->post_type
        ) :

            wp_die(
                __('Not valid product', 'sejoli'),
                __('Problem happened', 'sejoli')
            );

        else :

            global $post;

            $post = $product;

            $this->check_if_enabled();

            wp_reset_postdata();

        endif;

        return $product;
    }

    /**
     * Check parse query and if member-area found, $enable_framework will be true
     * Hooked via action parse_query, priority 19
     * @since 1.5.0
     * @access public
     * @return void
     */
    public function check_parse_query()
    {
        global $wp_query;

		if(is_admin() || $this->is_checked) :
			return;
		endif;

        if( isset($wp_query->query_vars['sejoli-social-proof']) ) :

            $this->is_checked = true;

            /** Allow CORS **/
            header("Access-Control-Allow-Origin: *");
            header("Content-type: application/javascript");

            $product = $this->set_product( intval( $wp_query->query_vars['sejoli-social-proof'] ) );

            if( $product && $this->is_enabled ) :

                $popup_text = safe_str_replace(
                                array(
                                    '{{buyer_name}}',
                                    '{{product_name}}'
                                ),
                                array(
                                    "<span class='buyer-name'>Buyer name</span>",
                                    "<span class='product-name'>" . $product->post_title . "</span>"
                                ),
                                $this->popup_text
                              );

                require_once( plugin_dir_path( __FILE__ ) . 'partials/social-proof/iframe-js.php' );

            else :

                wp_die(
                    __('Social proof is not active', 'sejoli'),
                    __('Problem happened', 'sejoli')
                );

            endif;

            exit;

        elseif( isset($wp_query->query_vars['sejoli-social-proof-ajax']) ) :

            $this->is_checked = true;

            /** Allow CORS **/
            header("Access-Control-Allow-Origin: *");
            header("Content-type: application/json");

            $product = $this->set_product( intval( $wp_query->query_vars['sejoli-social-proof-ajax'] ) );

            if( $product && $this->is_enabled ) :

                $this->get_order_data();

            else :

                wp_die(
                    __('Social proof is not active', 'sejoli'),
                    __('Problem happened', 'sejoli')
                );

            endif;

            exit;

        elseif(
            isset($wp_query->query_vars['sejoli-social-proof-iframe-js']) &&
            isset($wp_query->query_vars['request']) &&
            in_array( $wp_query->query_vars['request'], array('css', 'js') )
        ) :

            $this->is_checked = true;

            /** Allow CORS **/
            header("Access-Control-Allow-Origin: *");
            header("Content-type: text/css");

            $product = $this->set_product( intval( $wp_query->query_vars['sejoli-social-proof-iframe-js'] ) );

            if( $product && $this->is_enabled ) :

                require_once( plugin_dir_path( __FILE__ ) . 'css/sejoli-social-proof-iframe-inline.css' );

            else :

                wp_die(
                    __('Social proof is not active', 'sejoli'),
                    __('Problem happened', 'sejoli')
                );

            endif;

        exit;

        endif;

    }

    /**
     * Check if social proof popup is enabled
     * Hooked via action wp, priority 1999
     * @since   1.5.0
     * @since   1.5.1.1     Add set value for is_product_image_showed
     * @return  void
     */
    public function check_if_enabled() {

        global $post;

        if(
            is_a( $post, 'WP_Post' ) &&
            (
                ( sejolisa_is_checkout_page() && ! is_admin() ) ||
                $this->is_checked
            )

        ) :

            $this->is_enabled              = boolval( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_enable' ) );
            $this->is_name_sensored        = boolval( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_sensor_buyer_name' ) );
            $this->is_avatar_showed        = boolval( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_display_avatar' ) );
            $this->is_product_image_showed = boolval( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_display_product' ) );
            $this->first_time              = intval( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_first' ) );
            $this->display_time            = intval( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_display' ) );
            $this->delay_time              = intval( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_delay' ) );
            $this->position                = sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_position' );
            $this->popup_text              = wp_strip_all_tags( sejolisa_carbon_get_post_meta( $post->ID, 'social_proof_text' ) );

        endif;

    }

    /**
     * Set current body classes
     * Hooked via filter body_class, priority 1999
     * @since   1.5.0
     * @param   array $body_classes
     * @return  array
     */
    public function set_body_classes( array $body_classes ) {

        if( $this->is_enabled ) :

            $body_classes[] = 'sejoli-social-proof';

        endif;

        return $body_classes;
    }

    /**
     * Set localize js variables
     * Hooked via action wp_enqueue_scripts, priority 888
     * @since 1.5.0
     */
    public function set_localize_js_vars() {

        global $post;

        if( $this->is_enabled ) :

            wp_localize_script('sejoli-checkout', 'sejoli_social_proof', [
                'main_css'      => SEJOLISA_URL . 'public/css/sejoli-social-proof.css?v=' . $this->version,
                'ajax_url'      => home_url('sejoli-ajax/get-social-proof-data'),
                'product_id'    => $post->ID,
                'first_time'    => $this->first_time,
                'show_time'     => $this->display_time,
                'delay_time'    => $this->delay_time,
                'position'      => $this->position,
            ]);

        endif;
    }

    /**
     * Set CSS and JS need files
     * Hooked via action wp_footer, priority 1999
     * @since   1.5.0
     * @return  void
     */
    public function set_scripts() {

        if ( $this->is_enabled ) :

            require_once( plugin_dir_path( __FILE__ ) . 'partials/social-proof/setup.php' );

        endif;

    }
}
