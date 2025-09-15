<?php
namespace SejoliSA\JSON;

Class Product extends \SejoliSA\JSON
{
    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set user options
     * Hooked via action wp_ajax_sejoli-product-options, priority 1
     * @since   1.0.0
     * @since   1.5.2   Add parameter to check if current product is able to be affiliated
     * @since   1.5.4   Add paremeter to check if current product is able to be affiliated by user that already bought
     * @return  json
     */
    public function set_for_options() {

        global $post;

        $options = [];
        $args    = wp_parse_args($_GET,[
            'term'    => ''
        ]);

        $product_limit = intval(sejolisa_carbon_get_theme_option('sejoli_limit_product_ajax'));

        $query = array(
            's'                      => $args['term'],
            'post_type'              => 'sejoli-product',
            'posts_per_page'         => $product_limit,
            'post_status'            => 'publish',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        );

        $products = new \WP_Query($query);

        if($products->have_posts()) :
            while($products->have_posts()) :

                $products->the_post();

                $enable            = boolval( sejolisa_carbon_get_post_meta(get_the_ID(), 'enable_sale'));
                $enable_affiliate  = sejolisa_user_can_affiliate_the_product( get_the_ID(), get_current_user_id() );
                $disable_sale_time = sejolisa_carbon_get_post_meta(get_the_ID(), 'disable_sale_time');

                if(
                    // skip if current user is not sejoli-permission
                    ! current_user_can('manage_sejoli_orders') &&
                    (
                        (
                            // check if current product is buy-able
                            false === $enable ||
                            (
                                // check if current product is disabled by time
                                !empty($disable_sale_time) &&
                                current_time('timestamp') > strtotime($disable_sale_time)
                            )
                        ) ||
                        // check if current product is able to be affiliated
                        true !== $enable_affiliate
                    )
                ) :
                    continue;
                endif;


                $options[] = [
                    'id'   => get_the_ID(),
                    'text' => sprintf( _x(' %s #%s', 'product-options', 'sejoli'), get_the_title(), get_the_ID())
                ];
            endwhile;
        endif;

        wp_reset_query();

        wp_send_json([
            'results' => $options
        ]);

        exit;
    }

    /**
     * Set user options
     * Hooked via action wp_ajax_sejoli-product-affiliate-options, priority 1
     * @since   1.0.0
     * @since   1.5.2   Add parameter to check if current product is able to be affiliated
     * @since   1.5.4   Add paremeter to check if current product is able to be affiliated by user that already bought
     * @return  json
     */
    public function set_for_options_product_affiliate() {

        global $post;

        $options = [];
        $args    = wp_parse_args($_GET,[
            'term'    => ''
        ]);

        $product_limit = intval(sejolisa_carbon_get_theme_option('sejoli_limit_product_ajax'));

        $query = array(
            's'                      => $args['term'],
            'post_type'              => 'sejoli-product',
            'posts_per_page'         => $product_limit,
            'post_status'            => 'publish',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        );

        $products = new \WP_Query($query);

        if($products->have_posts()) :
            while($products->have_posts()) :

                $products->the_post();

                $enable            = boolval( sejolisa_carbon_get_post_meta(get_the_ID(), 'enable_sale'));
                $enable_affiliate  = sejolisa_user_can_affiliate_the_product( get_the_ID(), get_current_user_id() );
                $disable_sale_time = sejolisa_carbon_get_post_meta(get_the_ID(), 'disable_sale_time');

                if(
                    current_user_can('manage_sejoli_orders') &&
                    (
                        (
                            // check if current product is buy-able
                            false === $enable ||
                            (
                                // check if current product is disabled by time
                                !empty($disable_sale_time) &&
                                current_time('timestamp') > strtotime($disable_sale_time)
                            )
                        ) ||
                        // check if current product is able to be affiliated
                        true !== $enable_affiliate
                    ) ||
                    !current_user_can('manage_sejoli_orders') &&
                    (
                        (
                            // check if current product is buy-able
                            false === $enable ||
                            (
                                // check if current product is disabled by time
                                !empty($disable_sale_time) &&
                                current_time('timestamp') > strtotime($disable_sale_time)
                            )
                        ) ||
                        // check if current product is able to be affiliated
                        true !== $enable_affiliate
                    )
                ) :
                    continue;
                endif;


                $options[] = [
                    'id'   => get_the_ID(),
                    'text' => sprintf( _x(' %s #%s', 'product-options', 'sejoli'), get_the_title(), get_the_ID())
                ];
            endwhile;
        endif;

        wp_reset_query();

        wp_send_json([
            'results' => $options
        ]);

        exit;
    }

    /**
     * Set table data
     * Hooked via action wp_ajax_sejoli-product-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

        $table = $this->set_table_args($_POST);

        $data = [];

        $products = new \WP_Query([
            'post_type'      => 'sejoli-product',
            'posts_per_page' => $table['length'],
            'offset'         => $table['start']
        ]);

        if($products->have_posts()) :
            while($products->have_posts()) :

                $products->the_post();

                $data[] = [
                    'id'   => get_the_ID(),
                    'title' => sprintf( _x(' %s #%s', 'product-options', 'sejoli'), get_the_title(), get_the_ID())
                ];
            endwhile;
        endif;

        wp_reset_query();

        if(class_exists('WP_CLI')) :
            __debug([
    			'table'           => $table,
    			'draw'            => $table['draw'],
    			'data'            => $data,
    			'recordsTotal'    => $products->post_count,
    			'recordsFiltered' => $products->post_count,
    		]);
        else :
    		echo wp_send_json([
    			'table'           => $table,
    			'draw'            => $table['draw'],
    			'data'            => $data,
    			'recordsTotal'    => $products->post_count,
    			'recordsFiltered' => $products->post_count,
    		]);
        endif;
		exit;
    }

    /**
     * List affiliate links
     * Hooked via action wp_ajax_sejoli-product-affiliate-link-list, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function list_affiliate_links() {

        $data = [];

        if(
            wp_verify_nonce($_POST['nonce'], 'sejoli-list-product-affiliate-link') ||
            class_exists('WP_CLI')
        ) :
            $product        = sejolisa_get_product($_POST['product_id']);
            $main_link      = sejolisa_carbon_get_post_meta($product->ID, 'sejoli_landing_page');
    		$other_links    = sejolisa_carbon_get_post_meta($product->ID, 'sejoli_affiliate_links');

            $args = [
                'user_id'   => get_current_user_id(),
                'product_id'=> $product->ID,
                'product'   => $product
            ];

            $i = 0;

            $data = [
                0   => [
                    'label'          => __('Halaman Penjualan / Sales Page', 'sejoli'),
                    'redirect_link'  => $main_link,
                    'affiliate_link' => esc_url(apply_filters('sejoli/affiliate/link', '', $args)),
                    'description'    => __('Link menuju ke halaman landing / sales page', 'sejoli')
                ],
                1   => [
                    'label'          => __('Halaman Checkout', 'sejoli'),
                    'redirect_link'  => get_permalink($product->ID),
                    'affiliate_link' => esc_url(apply_filters('sejoli/affiliate/link', '', $args, 'checkout')),
                    'description'    => __('Link menuju ke halaman checkout', 'sejoli')
                ]
            ];


            foreach( (array) $other_links as $link ) :
    	        $key         = $i .'-'.sanitize_title($link['title']);
    	        $data[$key] = [
    	            'label'         => $link['title'],
                    'redirect_link' => $link['link'],
                    'affiliate_link'=> esc_url(apply_filters('sejoli/affiliate/link', '', $args, $key)),
                    'description'   => $link['description']
    	        ];

    	        $i++;
    	    endforeach;
        endif;

        if(class_exists('WP_CLI')) :
            __debug($data);
        else :
            wp_send_json($data);
        endif;
        exit;

    }

    /**
     * List affiliate help
     * Hooked via action wp_ajax_sejoli-product-affiliate-helo-list, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function list_affiliate_help() {

        $data = [];

        if(
            wp_verify_nonce($_POST['nonce'], 'sejoli-list-product-affiliate-help') ||
            class_exists('WP_CLI')
        ) :
            $tools = sejolisa_carbon_get_post_meta($_POST['product_id'], 'sejoli_affiliate_tool');
            foreach($tools as $tool) :
                $data[] = [
                    'title'       => $tool['title'],
                    'description' => $tool['description'],
                    'file'        => wp_get_attachment_url($tool['file'])
                ];
            endforeach;
        endif;

        if(class_exists('WP_CLI')) :
            __debug($data);
        else :
            wp_send_json($data);
        endif;
        exit;

    }

    /**
     * Check autoresponder HTML Code
     * Hooked via action wp_ajax_sejoli-check-autoresponder, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function check_autoresponder() {
        $data = [
            'valid'    => false,
            'messages' => [
                __('Invalid request', 'sejoli')
            ]
        ];

        $post_data = wp_parse_args($_POST,[
            'nonce' => false,
            'form'  => ''
        ]);

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-check-autoresponder') && !empty($post_data['form'])) :
            $data = sejolisa_parsing_form_html_code($post_data['form']);
        endif;

        wp_send_json($data);
        exit;
    }
}
