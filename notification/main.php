<?php

namespace SejoliSA\Notification;

use Carbon\Carbon;

class Main {

    /**
	 * Shortcode data
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $shortcode_data;

	/**
	 * Order data
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $order_data;

	/**
	 * Product data
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $product_data;

	/**
	 * Buyer data
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $buyer_data;

	/**
	 * Affiliate data
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $affiliate_data = false;

	/**
	 * Coupon data
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $coupon_data = false;

    /**
	 * Notification content
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	string
	 */
	protected $content = array(
		'buyer' => array(
			'active' => true,
			'email'	=> array(
				'active' => true,
				'title'   => '',
				'content' => ''
			),
			'sms'	=> array(
				'active'  => false,
				'content' => ''
			),
			'whatsapp' => array(
				'active'  => false,
				'content' => ''
			)
		),
		'admin' => array(
			'active' => false,
			'email'	=> array(
				'active' => true,
				'title'   => '',
				'content' => ''
			),
			'sms'	=> array(
				'active'  => false,
				'content' => ''
			),
			'whatsapp' => array(
				'active'  => false,
				'content' => ''
			)
		),
		'affiliate' => array(
			'active' => false,
			'email'	=> array(
				'active' => true,
				'title'   => '',
				'content' => ''
			),
			'sms'	=> array(
				'active'  => false,
				'content' => ''
			),
			'whatsapp' => array(
				'active'  => false,
				'content' => ''
			)
		),
	);

	/**
	 * Store value if is able to send to specific role and specific media
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $able_send = array(
		'email'	=> [
			'buyer'     => true,
			'admin'     => false,
			'affiliate' => false,
		],

		'whatsapp'	=> [
			'buyer'     => false,
			'admin'     => false,
			'affiliate' => false,
		],

		'sms'	=> [
			'buyer'     => false,
			'admin'     => false,
			'affiliate' => false,
		],
	);



    /**
     * Construction
     */
    public function __construct() {

    }

    /**
	 * Set notification per product content
	 * @since 	1.0.0
	 * @return 	string
	 */
    public function set_notif_product_content() {

    	$product_id   = isset($this->product_data->ID) ? $this->product_data->ID : '';
    	$order_status = isset($this->order_data['status']) ? $this->order_data['status'] : '';
    	
    	if(isset($this->product_data)) :
    		
	    	$product_notification_on_hold 		  = sejolisa_carbon_get_post_meta($this->product_data->ID, 'product_notification_on_hold');
			$product_notification_payment_confirm = sejolisa_carbon_get_post_meta($this->product_data->ID, 'product_notification_payment_confirm');
			$product_notification_in_progress 	  = sejolisa_carbon_get_post_meta($this->product_data->ID, 'product_notification_in_progress');
			$product_notification_shipping 		  = sejolisa_carbon_get_post_meta($this->product_data->ID, 'product_notification_shipping');
			$product_notification_completed 	  = sejolisa_carbon_get_post_meta($this->product_data->ID, 'product_notification_completed');
			$product_notification_cancel 		  = sejolisa_carbon_get_post_meta($this->product_data->ID, 'product_notification_cancel');
			$product_notification_refund 		  = sejolisa_carbon_get_post_meta($this->product_data->ID, 'product_notification_refund');
			$order_status 					      = $this->order_data['status'];

			switch ($order_status) {
				case "on-hold":
					$content = $product_notification_on_hold;
					break;
				case "payment-confirm":
					$content = $product_notification_payment_confirm;
					break;
				case "in-progress":
					$content = $product_notification_in_progress;
					break;
				case "shipping":
					$content = $product_notification_shipping;
					break;
				case "completed":
					$content = $product_notification_completed;
					break;
				case "cancelled":
					$content = $product_notification_cancel;
					break;
				case "refunded":
					$content = $product_notification_refund;
					break;
				default:
					$content = "";
					break;
			}

			return $content;

		endif;

    }

    /**
	 * Set notification payment confirm content
	 * @since 	1.0.0
	 * @return 	string
	 */
    public function set_notif_confirm_payment_content() {

    	global $wpdb;

    	if( !empty($this->order_data) ):

	    	$order_id = $this->order_data['ID'];

	    	$get_data_payment_confirm = $wpdb->get_results( "
	                SELECT * 
	                FROM {$wpdb->prefix}sejolisa_confirmations
	                WHERE order_id = '".$order_id."'
	            " );

	    	$payment_detail = isset($get_data_payment_confirm[0]->detail) ? $get_data_payment_confirm[0]->detail : '';

	    	$payment_confirm_data = unserialize($payment_detail);

	    	if( isset( $payment_confirm_data ) && is_array( $payment_confirm_data ) ) :
	    	
	    		$content = $payment_confirm_data['proof'];
	    	
	    	else:

	    		$content = '';

	    	endif;

    		return $content;    	

	    endif;

    }

    /**
	 * Prepare shortcode data
	 * @since 	1.0.0
	 * @return 	void
	 */
	protected function prepare_shortcode_data() {

		$timer = absint(sejolisa_carbon_get_theme_option('sejoli_countdown_timer'));

		$payment_gateway = isset($this->order_data['payment_gateway']) ? $this->order_data['payment_gateway'] : '';

		$unique_code = 0;
        $total_wt_additionalfee = $this->order_data['grand_total'];
		if(isset($this->order_data['meta_data'][$payment_gateway]['unique_code'])):
            $total_wt_additionalfee = $this->order_data['grand_total'] - $this->order_data['meta_data'][$payment_gateway]['unique_code'];
            $unique_code = $this->order_data['meta_data'][$payment_gateway]['unique_code'];
        elseif(isset($this->order_data['meta_data']['shipping_data']['cost'])):
            $total_wt_additionalfee = $this->order_data['grand_total'] - $this->order_data['meta_data']['shipping_data']['cost'];
        elseif(isset($this->order_data['meta_data']['shipping_data']['cost']) && isset($this->order_data['meta_data'][$payment_gateway]['unique_code'])):
            $total_wt_additionalfee = $this->order_data['grand_total'] - $this->order_data['meta_data']['shipping_data']['cost'] - $this->order_data['meta_data'][$payment_gateway]['unique_code'];
        endif;

        $value_ppn = 0;
        $ppn = '';
        $enable_ppn = boolval(sejolisa_carbon_get_post_meta( $this->order_data['product_id'], 'enable_ppn' ));
        if(true === $enable_ppn && isset($this->order_data['meta_data']['ppn'])) :
            $price_without_ppn = ($total_wt_additionalfee / (1 + $this->order_data['meta_data']['ppn'] / 100));
            $value_ppn         = $price_without_ppn * $this->order_data['meta_data']['ppn'] / 100;
            $ppn 			   = $this->order_data['meta_data']['ppn'];
			$this->shortcode_data = [
				'{{memberurl}}'         => home_url('/member-area/'),
				'{{member-url}}'        => home_url('/member-area/'),
				'{{sitename}}'          => get_bloginfo('name'),
				'{{siteurl}}'           => home_url('/'),
				'{{site-url}}'          => home_url('/'),
				'{{order-id}}'          => $this->order_data['ID'],
				'{{invoice-id}}'        => $this->order_data['ID'],
				'{{order-grand-total}}' => trim(sejolisa_price_format($this->order_data['grand_total'])),
				'{{ppn}}' 				=> number_format(floatval($ppn), 2, ',', ' '),
				'{{ppn_total}}' 		=> sejolisa_price_format( $value_ppn ),
				'{{unique_code}}' 		=> sejolisa_price_format($unique_code),
				'{{buyer-name}}'        => $this->buyer_data->display_name,
				'{{buyer-email}}'       => $this->buyer_data->user_email,
				'{{buyer-phone}}'       => $this->buyer_data->meta->phone,
				'{{product-name}}'      => $this->product_data->post_title,
				'{{quantity}}'          => $this->order_data['quantity'],
				'{{shipping-courier}}'  => isset($this->order_data['meta_data']['shipping_data']) ? $this->order_data['meta_data']['shipping_data']['courier'] .' - '. $this->order_data['meta_data']['shipping_data']['service'] : '',
				'{{shipping-number}}'   => isset($this->order_data['meta_data']['shipping_data']['resi_number']) ? $this->order_data['meta_data']['shipping_data']['resi_number'] : '',
				'{{confim-url}}'		=> home_url('/confirm'),
				'{{confirm-url}}'		=> home_url('/confirm'),
				'{{order-day}}'			=> Carbon::createFromDate($this->order_data['created_at'])->diffInDays(Carbon::now()) + 1,
				'{{close-time}}'        => __('pukul', 'sejoli') . ' ' . date('H:i, d F Y', (strtotime($this->order_data['created_at']) + ($timer * HOUR_IN_SECONDS)) ),
				'{{renew-url}}'			=> site_url('/checkout/renew/?order_id=' . $this->order_data['ID']),
			];
		else:
			$this->shortcode_data = [
				'{{memberurl}}'         => home_url('/member-area/'),
				'{{member-url}}'        => home_url('/member-area/'),
				'{{sitename}}'          => get_bloginfo('name'),
				'{{siteurl}}'           => home_url('/'),
				'{{site-url}}'          => home_url('/'),
				'{{order-id}}'          => $this->order_data['ID'],
				'{{invoice-id}}'        => $this->order_data['ID'],
				'{{order-grand-total}}' => trim(sejolisa_price_format($this->order_data['grand_total'])),
				'{{unique_code}}' 		=> sejolisa_price_format($unique_code),
				'{{buyer-name}}'        => $this->buyer_data->display_name,
				'{{buyer-email}}'       => $this->buyer_data->user_email,
				'{{buyer-phone}}'       => $this->buyer_data->meta->phone,
				'{{product-name}}'      => $this->product_data->post_title,
				'{{quantity}}'          => $this->order_data['quantity'],
				'{{shipping-courier}}'  => isset($this->order_data['meta_data']['shipping_data']) ? $this->order_data['meta_data']['shipping_data']['courier'] .' - '. $this->order_data['meta_data']['shipping_data']['service'] : '',
				'{{shipping-number}}'   => isset($this->order_data['meta_data']['shipping_data']['resi_number']) ? $this->order_data['meta_data']['shipping_data']['resi_number'] : '',
				'{{confim-url}}'		=> home_url('/confirm'),
				'{{confirm-url}}'		=> home_url('/confirm'),
				'{{order-day}}'			=> Carbon::createFromDate($this->order_data['created_at'])->diffInDays(Carbon::now()) + 1,
				'{{close-time}}'        => __('pukul', 'sejoli') . ' ' . date('H:i, d F Y', (strtotime($this->order_data['created_at']) + ($timer * HOUR_IN_SECONDS)) ),
				'{{renew-url}}'			=> site_url('/checkout/renew/?order_id=' . $this->order_data['ID']),
			];
        endif;

		if(is_object($this->affiliate_data)) :
			$affiliate_phone = isset($this->affiliate_data->phone) ? $this->affiliate_data->phone : $this->affiliate_data->data->meta->phone;

			$this->shortcode_data['{{affiliate-name}}']  = $this->affiliate_data->display_name;
			$this->shortcode_data['{{affiliate-phone}}'] = $affiliate_phone;
			$this->shortcode_data['{{affiliate-email}}'] = $this->affiliate_data->user_email;
			$this->shortcode_data['{{affiliate-tier}}']  = $this->affiliate_data->tier;
			$this->shortcode_data['{{commission}}']      = $this->affiliate_data->commission;
		endif;

		$this->shortcode_data = apply_filters('sejoli/notification/shortcode', $this->shortcode_data, [
			'order_data'     => $this->order_data,
			'buyer_data'     => $this->buyer_data,
			'product_data'   => $this->product_data,
			'affiliate_data' => $this->affiliate_data
		]);

		$this->shortcode_data['{{product-info}}'] = safe_str_replace(
						array_keys($this->shortcode_data),
						array_values($this->shortcode_data),
						$this->set_notif_product_content()
					);

		$this->shortcode_data['{{confirm-payment-file}}'] = safe_str_replace(
						array_keys($this->shortcode_data),
						array_values($this->shortcode_data),
						$this->set_notif_confirm_payment_content()
					);

	}

	/**
	 * Set notification content
	 * Hooked via filter sejoli/notification/content, priority 999
	 * @since 	1.0.0
	 * @param 	string 	$content
	 * @param 	array  	$details
	 * @param 	string 	$media
	 * @return 	string
	 */
	public function set_notification_content($content, $media = 'email', $recipient_type = 'buyer') {

		$user_access = $order_detail = $order_meta = '';

	    $directory         = apply_filters(
								'sejoli/'. $media .'/template-directory',
								SEJOLISA_DIR . 'template/' .$media. '/',
								$media,
								NULL,
								array()
							);

	    if (is_array($this->order_data) && isset($this->order_data['product_id'])) {
            $enable_ppn = boolval(sejolisa_carbon_get_post_meta($this->order_data['product_id'], 'enable_ppn'));
        } else {
            $enable_ppn = false;  // Default value or other handling
        }
	    if(true === $enable_ppn):
	    	$order_detail_file = $directory . 'order-detail-ppn.php';
	    else:
	    	$order_detail_file = $directory . 'order-detail.php';
	    endif;
		$order_meta_file   = $directory . 'order-meta.php';
		$user_access_file  = $directory . 'user-access.php';

	    if(file_exists($order_detail_file)) :
	        ob_start();
	        require $order_detail_file;
	        $order_detail = ob_get_contents();
	        ob_end_clean();
	    endif;

		if(file_exists($order_meta_file)) :
	        ob_start();
	        require $order_meta_file;
	        $order_meta = ob_get_contents();
	        ob_end_clean();
	    endif;

        if(file_exists($user_access_file)) :
	        ob_start();
	        require $user_access_file;
	        $user_access = ob_get_contents();
	        ob_end_clean();
	    endif;

        $content = safe_str_replace('{{user-access}}', $user_access, $content);

		$order_detail = apply_filters(
						'sejoli/notification/content/order-detail',
						$order_detail,
						$media,
						$recipient_type,
						[
							'order_data'     => $this->order_data,
							'buyer_data'     => $this->buyer_data,
							'product_data'   => $this->product_data,
							'affiliate_data' => $this->affiliate_data,
						]
					);

		$order_meta = apply_filters(
						'sejoli/notification/content/order-meta',
						$order_meta,
						$media,
						$recipient_type,
						[
							'order_data'     => $this->order_data,
							'buyer_data'     => $this->buyer_data,
							'product_data'   => $this->product_data,
							'affiliate_data' => $this->affiliate_data,
						]
					);

		$payment_gateway = apply_filters(
							'sejoli/notification/content/payment-gateway',
							'',
							$media,
							$recipient_type,
							[
								'order_data'     => $this->order_data,
							]
						);
		
		$content = safe_str_replace('{{product-info}}', $this->set_notif_product_content(), $content);
		
		if( $media === 'email' ) :

			$content = safe_str_replace('{{confirm-payment-file}}', '', $content);
		
		else:

			$content = safe_str_replace('{{confirm-payment-file}}', $this->set_notif_confirm_payment_content(), $content);

		endif;	

		if( $recipient_type === "buyer" && !empty($this->order_data) ):

			// Recalculate notif grand total based on payment
			$grand_total = apply_filters('sejoli/recalculate/notif-grand-total', $this->order_data['grand_total'], $this->order_data);
			
			$content      = safe_str_replace('{{order-grand-total}}', trim(sejolisa_price_format($grand_total)), $content);
			$order_detail = safe_str_replace('{{order-grand-total}}', trim(sejolisa_price_format($grand_total)), $order_detail);

		endif;
		
		$content = safe_str_replace('{{order-detail}}', $order_detail, $content);
		$content = safe_str_replace('{{order-meta}}',   $order_meta, $content);
		$content = safe_str_replace('{{payment-gateway}}',   $payment_gateway, $content);

		return $content;
	}

    /**
	 * Render shortcode data
	 * @since 	1.0.0
	 * @param  	string $content
	 * @return 	string
	 */
	public function render_shortcode($content) {

		$content = safe_str_replace(
						array_keys($this->shortcode_data),
						array_values($this->shortcode_data),
						$content
					);

		return $content;
	}


    /**
     * Setup all related data via order_data and prepare for shortcode_data
     * @since 	1.0.0
     * @return
     */
    public function prepare(array $order_data) {

		$this->order_data   = $order_data;
		$this->buyer_data   = (
								isset($order_data['user']) &&
								is_a($order_data['user'], 'WP_User')
							  ) ? $order_data['user'] : sejolisa_get_user(intval($order_data['user_id']));

		$this->product_data = (
								isset($order_data['product']) &&
								is_a($order_data['product'], 'WP_Post')
							  ) ? $order_data['product'] : sejolisa_get_product(intval($order_data['product_id']));

		if(0 !== $order_data['affiliate_id'] && !isset($order_data['affiliate_data'])) :
			$this->affiliate_data = sejolisa_get_user(intval($order_data['affiliate_id']));
		endif;

		if(isset($order_data['affiliate_data'])) :
			$this->affiliate_data = $order_data['affiliate_data'];
		elseif(isset($order_data['affiliate'])) :
			$this->affiliate_data = $order_data['affiliate'];
		endif;

		$this->prepare_shortcode_data();
    }

	/**
	 * Get all media libraries
	 * @since 	1.0.0
	 * @return 	array
	 */
	protected function get_media_libraries() {
		return (array) apply_filters('sejoli/notification/available-media-libraries', []);
	}

	/**
	 * Set if current notification event is able to send to specific role and specific media
	 * @since 	1.0.0
	 * @param 	string 		$role 		The user role
	 * @param 	string 		$media 		The notification media
	 * @param 	boolean 	$enable		Set if is enabled to send
	 * @param 	boolean 	Enable value to send
	 */
	protected function set_enable_send($media, $role, $enable = false) {
		$this->able_send[$media][$role] = $enable;
		return $enable;
	}

	/**
	 * Get able value if current notification event is able to send to admin
	 * @since 	1.0.0
	 * @param 	string 		$role 		The user role
	 * @param 	string 		$media 		The notification media
	 * @return 	boolean
	 */
	protected function is_able_to_send($media, $role) {
		return (boolean) isset($this->able_send[$media][$role]) ? $this->able_send[$media][$role] : false;
	}

	/**
	 * Set recipient title
	 * @since 	1.0.0
	 * @param 	string $recipient
	 * @param 	string $media
	 * @param 	string $content
	 */
	protected function set_recipient_title($recipient, $media, $content) {
		$this->content[$recipient][$media]['title'] = $content;
	}

	/**
	 * Set recipient content
	 * @since 	1.0.0
	 * @param 	string $recipient
	 * @param 	string $media
	 * @param 	string $content
	 */
	protected function set_recipient_content($recipient, $media, $content) {
		$this->content[$recipient][$media]['content'] = $content;
	}

	/**
	 * Get recipient title
	 * @since 	1.0.0
	 * @param  	string $recipient
	 * @param  	string $media
	 * @return 	string
	 */
	protected function get_recipient_title($recipient, $media) {
		return $this->content[$recipient][$media]['title'];
	}

	/**
	 * Get recipient content
	 * @since 	1.0.0
	 * @param  	string $recipient
	 * @param  	string $media
	 * @return 	string
	 */
	protected function get_recipient_content($recipient, $media) {
		return $this->content[$recipient][$media]['content'];
	}
}
