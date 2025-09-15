<?php

namespace SejoliSA\Front;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Facebook_Tiktok_Conversion {

    /**
	 * The ID of this plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.2
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.3.2
	 * @param   string    $plugin_name      The name of the plugin.
	 * @param   string    $version    		The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

    /**
     * Set facebook pixel in local data
     * Hooked via action wp_enqueue_scripts, priority 888
     * @since   1.3.2
     * @since 	1.5.1.1 	Remove content_type and content_category
     * @return  void
     */
    public function set_localize_js_vars() {

    	if( sejolisa_is_member_area_page('akses') )  :

	        wp_localize_script( 'sejoli-member-area', 'sejoli_fb_tiktok_conversion', [
	            'fb_tiktok_access_pixel_conversion' => [
					'ajaxurl' => add_query_arg([
						'action' => 'sejoli-fb-tiktok-access-pixel-conversion',
					], admin_url('admin-ajax.php')),
					'nonce'	=> wp_create_nonce('sejoli-fb-tiktok-access-pixel-conversion')
				],
	        ]);

	    endif;

    }

    /**
     * Track user click access pixel conversion
     * Hooked via wp_ajax_sejoli-fb-tiktok-access-pixel-conversion, priority 1
     * @since   1.0,0
     * @return  json
     */
    public function fb_tiktok_click_access_pixel_conversion() {

        $post_data = wp_parse_args($_GET,[
            'nonce'       => NULL,
            'product'     => NULL,
            'access_link' => NULL,
            'access'      => NULL,
        ]);

        if( wp_verify_nonce($post_data['nonce'], 'sejoli-fb-tiktok-access-pixel-conversion') && !empty($post_data['product']) && !empty($post_data['access_link']) && !empty($post_data['access']) ) :

            $user_id    			  = get_current_user_id();
        	$meta_value 			  = "access-".$post_data['access'];
		    $fb_access_get_value      = get_user_meta( $user_id, 'fb_user_click_access_tracks', true );
		    $fb_conversion_active     = boolval(sejolisa_carbon_get_post_meta($post_data['product'], 'fb_conversion_active'));
		    $fb_eventString           = esc_attr(sejolisa_carbon_get_post_meta($post_data['product'], 'fb_conversion_event_click_link_access_page'));
		    $tiktok_access_get_value  = get_user_meta( $user_id, 'tiktok_user_click_access_tracks', true );
		    $tiktok_conversion_active = boolval(sejolisa_carbon_get_post_meta($post_data['product'], 'tiktok_conversion_active'));
		    $tiktok_eventString       = esc_attr(sejolisa_carbon_get_post_meta($post_data['product'], 'tiktok_conversion_event_click_link_access_page'));

		    if( !empty( $fb_access_get_value ) ) :
			    
			    if ( ! array($fb_access_get_value) ) :
			        $fb_access_get_value = array();
			    endif;

			    if( !in_array( $meta_value, $fb_access_get_value ) ) :

			    	$fb_access_get_value[] = $meta_value;

		            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
		            	update_user_meta( $user_id, 'fb_user_click_access_tracks', $fb_access_get_value );
		            	sejoli_facebook_tracker( $post_data['product'], $fb_eventString );
		            endif;

	        	endif;

	        else:

			    $fb_access_get_value   = array();
	        	$fb_access_get_value[] = $meta_value;

	            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
	            	update_user_meta( $user_id, 'fb_user_click_access_tracks', $fb_access_get_value );
	            	sejoli_facebook_tracker( $post_data['product'], $fb_eventString );
	            endif;

	       	endif;

	       	if( !empty( $tiktok_access_get_value ) ) :
			    
			    if ( ! array($tiktok_access_get_value) ) :
			        $tiktok_access_get_value = array();
			    endif;

			    if( !in_array( $meta_value, $tiktok_access_get_value ) ) :

			    	$tiktok_access_get_value[] = $meta_value;

		            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
		            	update_user_meta( $user_id, 'tiktok_user_click_access_tracks', $tiktok_access_get_value );
		            	sejoli_tiktok_tracker( $post_data['product'], $tiktok_eventString );
		            endif;

	        	endif;

	        else:

			    $tiktok_access_get_value   = array();
	        	$tiktok_access_get_value[] = $meta_value;

	            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
	            	update_user_meta( $user_id, 'tiktok_user_click_access_tracks', $tiktok_access_get_value );
	            	sejoli_tiktok_tracker( $post_data['product'], $tiktok_eventString );
	            endif;

	       	endif;

        endif;

        return;
        
    }

    /**
     * Fungsi untuk mencari string yang mengandung angka tertentu
     * @since   1.0,0
     * @return  json
     */
	public function containsNumber($string, $number) {
	    return mb_strpos($string, $number) !== false;
	}

    /**
     * Track user views some pages pixel conversion
     * @since   1.0,0
     * @return  json
     */
    public function fb_tiktok_view_some_pages_pixel_conversion() {

    	global $wp;

    	if( is_user_logged_in() ) :

	    	if(
				isset( $wp->query_vars['sejolisa_checkout_page'], $_GET['order_id'] ) &&
				$wp->query_vars['sejolisa_checkout_page'] === 'thank-you'
			) :

	    		$respond = sejolisa_get_order([
			        'ID' => $_GET['order_id']
			    ]);
				
				if(false !== $respond['valid'] && isset($respond['orders']) && isset($respond['orders']['ID'])) :
					
					$order  = $respond['orders'];

		    		$post_data = [
			            'ID'   => $order['ID'],
			            'page' => 'invoice'
			        ];

			        $order_id = $post_data['ID'];

			        $fb_conversion_active     = boolval(sejolisa_carbon_get_post_meta($order['product_id'], 'fb_conversion_active'));
			        $fb_eventString           = esc_attr(sejolisa_carbon_get_post_meta($order['product_id'], 'fb_conversion_event_on_invoice_page'));
			        $tiktok_conversion_active = boolval(sejolisa_carbon_get_post_meta($order['product_id'], 'tiktok_conversion_active'));
			        $tiktok_eventString       = esc_attr(sejolisa_carbon_get_post_meta($order['product_id'], 'tiktok_conversion_event_on_invoice_page'));

			        $user_id    = get_current_user_id();
		        	$meta_value = "views-".$post_data['page'].'-'.$post_data['ID'];
				    $fb_access_get_value      = get_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', true );
				    $tiktok_access_get_value  = get_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', true );
					
					if(is_array($fb_access_get_value)) :
						$filterDuplicateDataFb = array_filter($fb_access_get_value, function($item) use ($order_id) {
							return $this->containsNumber($item, $order_id);
						});
					else :
					    $filterDuplicateDataFb = null;
					endif;

					// Periksa apakah ada string yang mengandung angka tertentu dalam array
					if (empty($filterDuplicateDataFb)) :

					    if( !empty( $fb_access_get_value ) ) :
					    
						    if ( ! array($fb_access_get_value) ) :
						        $fb_access_get_value = array();
						    endif;

					    	$fb_access_get_value[] = $meta_value;

				            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
				            	update_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', $fb_access_get_value );
				            	sejoli_facebook_tracker( $order, $fb_eventString );
				            endif;

				        else:

						    $fb_access_get_value   = array();
				        	$fb_access_get_value[] = $meta_value;

				            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
				            	update_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', $fb_access_get_value );
				            	sejoli_facebook_tracker( $order, $fb_eventString );
				            endif;

				       	endif;

					endif;

					if(is_array($tiktok_access_get_value)) :
						$filterDuplicateDataTikTok = array_filter($tiktok_access_get_value, function($item) use ($order_id) {
							return $this->containsNumber($item, $order_id);
						});
					else :
					    $filterDuplicateDataTiktok = null;
					endif;

					if (empty($filterDuplicateDataTikTok)) :

				       	if( !empty( $tiktok_access_get_value ) ) :
						    
						    if ( ! array($tiktok_access_get_value) ) :
						        $tiktok_access_get_value = array();
						    endif;

					    	$tiktok_access_get_value[] = $meta_value;

				            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
				            	update_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', $tiktok_access_get_value );
				            	sejoli_tiktok_tracker( $order, $tiktok_eventString );
				            endif;

				        else:

						    $tiktok_access_get_value   = array();
				        	$tiktok_access_get_value[] = $meta_value;

				            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
				            	update_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', $tiktok_access_get_value );
				            	sejoli_tiktok_tracker( $order, $tiktok_eventString );
				            endif;

				       	endif;

				    endif;

		        	return true;

		       	endif;

		    elseif( is_singular( 'sejoli-product' ) ) :

		    	$post_data = [
		            'ID'   => get_the_ID(),
		            'page' => 'checkout'
		        ];

		        $fb_conversion_active     = boolval(sejolisa_carbon_get_post_meta($post_data['ID'], 'fb_conversion_active'));
		        $fb_eventString           = esc_attr(sejolisa_carbon_get_post_meta($post_data['ID'], 'fb_conversion_event_on_checkout_page'));
		        $tiktok_conversion_active = boolval(sejolisa_carbon_get_post_meta($post_data['ID'], 'tiktok_conversion_active'));
		        $tiktok_eventString       = esc_attr(sejolisa_carbon_get_post_meta($post_data['ID'], 'tiktok_conversion_event_on_checkout_page'));

		        $user_id    = get_current_user_id();
	        	$meta_value = "views-".$post_data['page'].'-'.$post_data['ID'].date('YmdHi');
			    $fb_access_get_value      = get_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', true );
			    $tiktok_access_get_value  = get_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', true );

			    $order_id = $post_data['ID'].date('YmdHi');
		        
		        if(is_array($fb_access_get_value)) :
					$filterDuplicateDataFb = array_filter($fb_access_get_value, function($item) use ($order_id) {
						return $this->containsNumber($item, $order_id);
					});
				else :
				    $filterDuplicateDataFb = null;
				endif;

				// Periksa apakah ada string yang mengandung angka tertentu dalam array
				if (empty($filterDuplicateDataFb)) :

				    if( !empty( $fb_access_get_value ) ) :
				    
					    if ( ! array($fb_access_get_value) ) :
					        $fb_access_get_value = array();
					    endif;

				    	$fb_access_get_value[] = $meta_value;

			            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
			            	update_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', $fb_access_get_value );
			            	sejoli_facebook_tracker( $post_data['ID'], $fb_eventString );
			            endif;

			        else:

					    $fb_access_get_value   = array();
			        	$fb_access_get_value[] = $meta_value;

			            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
			            	update_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', $fb_access_get_value );
			            	sejoli_facebook_tracker( $post_data['ID'], $fb_eventString );
			            endif;

			       	endif;

				endif;

				if(is_array($tiktok_access_get_value)) :
					$filterDuplicateDataTikTok = array_filter($tiktok_access_get_value, function($item) use ($order_id) {
						return $this->containsNumber($item, $order_id);
					});
				else :
				    $filterDuplicateDataTiktok = null;
				endif;

				if (empty($filterDuplicateDataTikTok)) :

			       	if( !empty( $tiktok_access_get_value ) ) :
					    
					    if ( ! array($tiktok_access_get_value) ) :
					        $tiktok_access_get_value = array();
					    endif;

				    	$tiktok_access_get_value[] = $meta_value;

			            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
			            	update_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', $tiktok_access_get_value );
			            	sejoli_tiktok_tracker( $post_data['ID'], $tiktok_eventString );
			            endif;

			        else:

					    $tiktok_access_get_value   = array();
			        	$tiktok_access_get_value[] = $meta_value;

			            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
			            	update_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', $tiktok_access_get_value );
			            	sejoli_tiktok_tracker( $post_data['ID'], $tiktok_eventString );
			            endif;

			       	endif;

			    endif;	

	        	return true;

	        elseif( sejolisa_verify_checkout_page('renew') ) :

	        	if(isset($_GET['order_id'])) {
		        	$respond = sejolisa_get_order([
						'ID' => $_GET['order_id']
					]);
		        	$product_id = $respond['orders']['product_id'];
		        } else {
		        	$product_id = $post->ID;
		        }

		    	$post_data = [
		            'ID'   => $product_id,
		            'page' => 'checkout_renew'
		        ];

		        $fb_conversion_active     = boolval(sejolisa_carbon_get_post_meta($post_data['ID'], 'fb_conversion_active'));
		        $fb_eventString           = esc_attr(sejolisa_carbon_get_post_meta($post_data['ID'], 'fb_conversion_event_on_checkout_page'));
		        $tiktok_conversion_active = boolval(sejolisa_carbon_get_post_meta($post_data['ID'], 'tiktok_conversion_active'));
		        $tiktok_eventString       = esc_attr(sejolisa_carbon_get_post_meta($post_data['ID'], 'tiktok_conversion_event_on_checkout_page'));
 
		        $user_id    = get_current_user_id();
	        	$meta_value = "views-".$post_data['page'].'-'.$post_data['ID'].date('YmdHi');
			    $fb_access_get_value      = get_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', true );
			    $tiktok_access_get_value  = get_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', true );
		     	
		     	$order_id = $post_data['ID'].date('YmdHi');

		     	if(is_array($fb_access_get_value)) :
					$filterDuplicateDataFb = array_filter($fb_access_get_value, function($item) use ($order_id) {
						return $this->containsNumber($item, $order_id);
					});
				else :
				    $filterDuplicateDataFb = null;
				endif;

				// Periksa apakah ada string yang mengandung angka tertentu dalam array
				if (empty($filterDuplicateDataFb)) :

				    if( !empty( $fb_access_get_value ) ) :
				    
					    if ( ! array($fb_access_get_value) ) :
					        $fb_access_get_value = array();
					    endif;

				    	$fb_access_get_value[] = $meta_value;

			            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
			            	update_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', $fb_access_get_value );
			            	sejoli_facebook_tracker( $post_data['ID'], $fb_eventString );
			            endif;

			        else:

					    $fb_access_get_value   = array();
			        	$fb_access_get_value[] = $meta_value;

			            if( true === $fb_conversion_active && !empty( $fb_eventString ) ) :
			            	update_user_meta( $user_id, 'fb_user_view_'.$post_data['page'].'_page_tracks', $fb_access_get_value );
			            	sejoli_facebook_tracker( $post_data['ID'], $fb_eventString );
			            endif;

			       	endif;

				endif;

				if(is_array($tiktok_access_get_value)) :
					$filterDuplicateDataTikTok = array_filter($tiktok_access_get_value, function($item) use ($order_id) {
						return $this->containsNumber($item, $order_id);
					});
				else :
				    $filterDuplicateDataTiktok = null;
				endif;

				if (empty($filterDuplicateDataTikTok)) :

			       	if( !empty( $tiktok_access_get_value ) ) :
					    
					    if ( ! array($tiktok_access_get_value) ) :
					        $tiktok_access_get_value = array();
					    endif;

				    	$tiktok_access_get_value[] = $meta_value;

			            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
			            	update_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', $tiktok_access_get_value );
			            	sejoli_tiktok_tracker( $post_data['ID'], $tiktok_eventString );
			            endif;

			        else:

					    $tiktok_access_get_value   = array();
			        	$tiktok_access_get_value[] = $meta_value;

			            if( true === $tiktok_conversion_active && !empty( $tiktok_eventString ) ) :
			            	update_user_meta( $user_id, 'tiktok_user_view_'.$post_data['page'].'_page_tracks', $tiktok_access_get_value );
			            	sejoli_tiktok_tracker( $post_data['ID'], $tiktok_eventString );
			            endif;

			       	endif;

			    endif;

	        	return true;

		    endif;

        else:

        	return false;

        endif;
    
    }

}