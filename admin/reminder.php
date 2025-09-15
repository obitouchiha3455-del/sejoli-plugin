<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Reminder {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.9
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.9
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Hold detail for shortcode
	 *
	 * @since    1.1.9
	 * @access   protected
	 * @var      array 		$shortcode_detail
	 */
	protected $shortcode_detail = array();

	/**
	 * Product data
	 * @since	1.1.9
	 * @var 	array
	 */
	protected $products = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.9
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Get reminder data
	 * @since 	1.1.9
	 * @return 	array 	$matches
	 */
	protected function get_reminder_data() {

		$reminder_data = \SejoliSA\Model\Post::set_args(array(
					'post_type'       => SEJOLI_REMINDER_CPT,
					'posts_per_page'  => 40
				))->get();

		$matches = array(
			'order'     => array(),
			'recurring' => array()
		);

		foreach((array) $reminder_data as $_data) :

			$type  = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_type');
			$media = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_media');
			$reminder_interval = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_interval');
			$diff = 0;
			$diffhour = 0;

			if($reminder_interval === "reminder_per_day") :

				$diff  = $day = absint(sejolisa_carbon_get_post_meta($_data->ID, 'reminder_day'));

				if('order' === $type) :

					$content = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_order_content');
					$diff = '-'.$day;

				else :

					$subscription_type = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_subscription_type');
					$content           = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_recurring_content');

					if('after' === $subscription_type) :
						$diff = '-'.$day;
					endif;

				endif;

				$date = date('Y-m-d', strtotime($diff. ' days'));

			else:

				$diffhour  = $day = absint(sejolisa_carbon_get_post_meta($_data->ID, 'reminder_hour'));

				if('order' === $type) :

					$content = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_order_content');
					$diffhour = '-'.$day;

				else :

					$subscription_type = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_subscription_type');
					$content           = sejolisa_carbon_get_post_meta($_data->ID, 'reminder_recurring_content');

					if('after' === $subscription_type) :
						$diffhour = '-'.$day;
					endif;


				endif;

				$timestamp = strtotime(current_time('Y-m-d H:i')) + $diffhour*60*60;

				$date = date('Y-m-d H', $timestamp);

			endif;

			$matches[$type][$day][] = array(
				'title'    => $_data->post_title,
				'content'  => $content,
				'media'    => $media,
				'interval' => $reminder_interval,
				'day'      => $day,
				'diff'	   => $diff,
				'diffhour' => $diffhour,
				'date'     => $date,
			);
		endforeach;

		return $matches;
	}

	/**
	 * Add subscription shortcode data for reminder
	 * Hooked via filter sejoli/nofitication/shortcode, priority 100
	 * @since 	1.2.0.1 Fix problem with returning shortcodes
	 * @since 	1.1.9
	 * @param 	array 	$shortcodes   	All previous shortcode data
	 * @param 	array 	$invoice_data 	Invoice Data
	 * @return 	array 	Shortcode data
	 */
	public function add_subscription_notification_shortcode(array $shortcodes, array $invoice_data) {

		if(!isset($this->shortcode_detail['media'])) :
			return $shortcodes;
		endif;

		$recurring_template = sejoli_get_notification_content('recurring-data', $this->shortcode_detail['media']);
		$end_date = !empty($this->shortcode_detail['end_date']) 
				    ? date('d F Y', safe_strtotime($this->shortcode_detail['end_date'])) 
				    : '-'; // atau bisa pakai string lain sesuai kebutuhanmu
		$recurring_template = safe_str_replace(array(
			'{{invoice-id}}',
			'{{product-name}}',
			'{{end-date}}',
		),array(
			$this->shortcode_detail['order_id'],
			$invoice_data['product_data']->post_title,
			date('d F Y', safe_strtotime($this->shortcode_detail['end_date']))
		), $recurring_template);

		$shortcodes = $shortcodes + array(
			'{{subscription-day}}'	=> $this->shortcode_detail['subscription-day'],
			'{{renew-url}}'			=> site_url('/checkout/renew/?order_id=' . $this->shortcode_detail['order_id']),
			'{{recurring-data}}'	=> $recurring_template
		);
		return $shortcodes;
	}

	/**
	 * Set order data to queue
	 * @since 	1.1.9
	 * @param 	array $reminder_type Should be order or recurring
	 * @param 	array $orders        Order data
	 * @param 	array $reminder_data Reminder configuration data
	 */
	protected function set_order_to_queue($reminder_type, array $orders, array $reminder_data) {

		foreach( $orders as $order ) :

			if( $reminder_type === 'recurring' ):

				$order_end_date = '';
				if (is_object($order) && isset($order->end_date)) :
					$order_end_date = $order->end_date;
				endif;

				if( $reminder_data['interval'] === 'reminder_per_hour' ):

					$get_order_date = date("Y-m-d H", strtotime($order_end_date));

				else:

					$get_order_date = date("Y-m-d", strtotime($order_end_date));

				endif;

			else:

				if( $reminder_data['interval'] === 'reminder_per_hour' ):

					$get_order_date = date("Y-m-d H", strtotime($order->created_at));

				else:

					$get_order_date = date("Y-m-d", strtotime($order->created_at));

				endif;

			endif;

			$order_id = (property_exists($order, 'ID')) ? $order->ID : $order->order_id;

			$get_parent_order = sejolisa_get_order(array('order_parent_id' => $order_id, 'status' => 'completed'));
			if( $reminder_data['date'] !== $get_order_date ) :

				return false;

			endif;

			if( false !== $get_parent_order['valid'] ) :

				return false;

			endif;

			$response = sejolisa_get_order(array('ID' => $order_id));

			if( false !== $response['valid'] ) :

				$reminder_media = array();
		        $i = 0;

		        foreach ( $reminder_data['media'] as $media ) :

		            $reminder_media[] = $media;

			            $this->shortcode_detail = array(
						'subscription-day'	=> abs($reminder_data['diff']),
						'order_id'			=> $order_id,
						'media'				=> $reminder_media[$i],
						'reminder_type'		=> $reminder_type,
						'end_date'			=> ('recurring' === $reminder_type) ? $order->end_date : NULL
					);

					$setup_data = apply_filters('sejoli/reminder/content', array(), $response['orders'], $reminder_data);

					$response_add = sejolisa_add_reminder_queue(array(
						'order_id'		=> $order_id,
						'title'         => $setup_data['title'],
						'content'       => $setup_data['content'],
						'recipient'     => $setup_data['recipient'],
						'send_day'      => $reminder_data['diff'],
						'send_hours'    => $reminder_data['diffhour'],
						'media_type'    => $reminder_media[$i],
						'reminder_type' => $reminder_type,
					));

					if(false !== $response_add['valid']) :

						do_action('sejoli/log/write', 'reminder-queue', array(
							'order_id'		=> $order_id,
							'title'         => $setup_data['title'],
							'recipient'     => $setup_data['recipient'],
							'send_day'      => $reminder_data['diff'],
							'send_hours'    => $reminder_data['diffhour'],
							'media_type'    => $reminder_media[$i],
							'reminder_type' => $reminder_type,
						));

					endif;

					$i++;

		        endforeach;

			endif;

		endforeach;

	}

	/**
	 * Check reminders data and match with order and/or subscription
	 * Hooked via action sejoli/reminder/check, priority 1
	 * @since 	1.1.9
	 * @since 	1.5.6 	Fix warning in $response['orders']
	 * @since 	1.6.2	Fix problem in queue subscription reminder
	 * @return 	void
	 */
	public function check_reminder_data() {

		$found   = 0;
		$matches = $this->get_reminder_data();

		// CHECK FOR ORDER DATA
		if(0 < count($matches['order'])) :

			foreach($matches['order'] as $_reminder) :

				foreach ($_reminder as $reminder) :

					$date     = $reminder['date'];
					$interval = $reminder['interval'];

					if($interval === "reminder_per_day"):

						$diff = $reminder['diff'];

					else:

						$diff = $reminder['diffhour'];

					endif;

					$response = sejolisa_get_orders_for_reminder($interval, $date, $diff);

					if(
						false !== $response['valid'] &&
						isset($response['orders']) &&	// @since 	1.5.6
						is_array($response['orders'])	// @since 	1.5.6
					) :

						foreach($_reminder as $_reminder_data) :

							$found += count($response['orders']);
							$this->set_order_to_queue('order', $response['orders'], $_reminder_data);

						endforeach;

					endif;

				endforeach;

			endforeach;

		endif;

		// CHECK FOR RECURRING DATA
		if(0 < count($matches['recurring'])) :

			foreach($matches['recurring'] as $_reminder) :

				foreach ($_reminder as $reminder) :

					$date     = $reminder['date'];
					$interval = $reminder['interval'];

					if($interval === "reminder_per_day"):

						$diff = $reminder['diff'];

					else:

						$diff = $reminder['diffhour'];

					endif;

					$response = sejolisa_get_subscriptions_for_reminder($interval, $date, $diff);

					if(
						false !== $response['valid'] &&
						isset($response['subscriptions']) &&	// @since 	1.6.2
						is_array($response['subscriptions'])	// @since 	1.6.2
					) :

						foreach($_reminder as $_reminder_data) :
							$found += count($response['subscriptions']);
							$this->set_order_to_queue('recurring', $response['subscriptions'], $_reminder_data);
						endforeach;

					endif;

				endforeach;

			endforeach;

		endif;

		if(0 < $found) :
			do_action('sejoli/log/write', 'reminder queue', sprintf(__('Found %d data', 'sejoli'), $found));
		endif;

	}

	/**
	 * Register custom post type for reminder data
	 * Hooked via action init, priority 100
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function register_post_type() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		$labels = [
    		'name'               => _x( 'Reminders', 'post type general name', 'sejoli' ),
    		'singular_name'      => _x( 'Reminder', 'post type singular name', 'sejoli' ),
    		'menu_name'          => _x( 'Reminders', 'admin menu', 'sejoli' ),
    		'name_admin_bar'     => _x( 'Reminder', 'add new on admin bar', 'sejoli' ),
    		'add_new'            => _x( 'Add New', 'reminder', 'sejoli' ),
    		'add_new_item'       => __( 'Tambah Reminder', 'sejoli' ),
    		'new_item'           => __( 'Tambah Reminder', 'sejoli' ),
    		'edit_item'          => __( 'Ubah Reminder', 'sejoli' ),
    		'view_item'          => __( 'View Reminder', 'sejoli' ),
    		'all_items'          => __( 'All Reminders', 'sejoli' ),
    		'search_items'       => __( 'Search Reminders', 'sejoli' ),
    		'parent_item_colon'  => __( 'Parent Reminders:', 'sejoli' ),
    		'not_found'          => __( 'No reminders found.', 'sejoli' ),
    		'not_found_in_trash' => __( 'No reminders found in Trash.', 'sejoli' )
    	];

    	$args = [
    		'labels'             => $labels,
            'description'        => __( 'Description.', 'sejoli' ),
    		'public'             => false,
    		'publicly_queryable' => false,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => [ 'slug' => 'reminder' ],
    		'capability_type'    => 'sejoli_reminder',
			'capabilities'		 => array(
				'publish_posts'      => 'publish_sejoli_reminders',
				'edit_posts'         => 'edit_sejoli_reminders',
				'edit_others_posts'  => 'edit_others_sejoli_reminders',
				'read_private_posts' => 'read_private_sejoli_reminders',
				'edit_post'          => 'edit_sejoli_reminder',
				'delete_posts'       => 'delete_sejoli_reminder',
				'read_post'          => 'read_sejoli_reminder'
			),
    		'has_archive'        => true,
    		'hierarchical'       => false,
    		'menu_position'      => null,
    		'supports'           => ['title'],
			'menu_icon'			 => plugin_dir_url( __FILE__ ) . 'images/icon.png'
    	];

    	register_post_type( 'sejoli-reminder', $args );
	}

	/**
	 * Add sub-menu log under Reminders
	 * Hooked via action admin_menu, priority 100
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function register_log_menu() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		$submenu = add_submenu_page(
			'edit.php?post_type=sejoli-reminder',
			__('Log Pengiriman', 'sejoli'),
			__('Log Pengiriman', 'sejoli'),
			'manage_sejoli_orders',
			'sejoli-reminder-log',
			array($this, 'display_log')
		);
	}

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['reminder'] = [
			'table' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-reminder-table'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-render-reminder-table')
			],
			'resend' => [
				'ajaxurl' => add_query_arg([
					'action' => 'sejoli-reminder-resend'
				], admin_url('admin-ajax.php')),
				'nonce' => wp_create_nonce('sejoli-reminder-resend')
			]
		];

		return $js_vars;
	}

	/**
	 * Setup post meta for reminder
	 * Hooked via action carbon_fields_register_fields, priority 100
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function setup_post_meta() {
		Container::make('post_meta', __('Pengaturan', 'sejoli'))
			->where('post_type', '=', 'sejoli-reminder')
			->add_fields(array(
				Field::make('select', 'reminder_type', __('Tipe pengingat', 'sejoli'))
					->add_options(array(
						'order'     => __('Pengingat pembayaran yang belum lunas', 'sejoli'),
						'recurring' => __('Pengingat masa berlangganan', 'sejoli')
					)),

				Field::make('multiselect', 'reminder_media', __('Media pengirim', 'sejoli'))
					->add_options(array(
						'email'    => __('Email', 'sejoli'),
						'whatsapp' => __('WhatsApp', 'sejoli'),
						'sms'      => __('SMS', 'sejoli')
					)),

				Field::make('select', 'reminder_interval', __('Interval Pengiriman', 'sejoli'))
					->add_options(array(
						'reminder_per_day'  => __('per Hari', 'sejoli'),
						'reminder_per_hour' => __('per Jam', 'sejoli')
					))
					->set_default_value('reminder_per_day'),

				Field::make('text', 'reminder_day', __('Hari pengingat', 'sejoli'))
					->set_attribute('type', 'number')
					->set_default_value(1)
					->set_required(true)
					->set_conditional_logic(array(
						array(
							'field'	=> 'reminder_interval',
							'value'	=> 'reminder_per_day'
						)
					)),

				Field::make('text', 'reminder_hour', __('Jam pengingat', 'sejoli'))
					->set_attribute('type', 'number')
					->set_default_value(1)
					->set_required(true)
					->set_conditional_logic(array(
						array(
							'field'	=> 'reminder_interval',
							'value'	=> 'reminder_per_hour'
						)
					)),

				Field::make('select', 'reminder_subscription_type', __('Pengingat belangganan', 'sejoli'))
					->add_options(array(
						'before'	=> __('Sebelum masa berlaku habis', 'sejoli'),
						'after'		=> __('Setelah masa berlaku habis', 'sejoli')
					))
					->set_conditional_logic(array(
						array(
							'field'	=> 'reminder_type',
							'value'	=> 'recurring'
						)
					)),

				Field::make('rich_text', 'reminder_order_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_conditional_logic(array(
						array(
							'field'	=> 'reminder_type',
							'value'	=> 'order'
						)
					))
					->set_default_value(sejoli_get_notification_content('reminder-order')),

				Field::make('rich_text', 'reminder_recurring_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_conditional_logic(array(
						array(
							'field'	=> 'reminder_type',
							'value'	=> 'recurring'
						)
					))
					->set_default_value(sejoli_get_notification_content('reminder-recurring'))
			));
	}

	/**
	 * Check if sejolisa_reminders table exists
	 * Hooked via action admin_notices, priority 100
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function check_table_exists() {
		global $wpdb;

		$result      = $wpdb->get_var(sprintf(__("SHOW TABLES LIKE '%s'", 'sejoli'), $wpdb->prefix . 'sejolisa_reminders'));
		$check_table = boolval(get_option('_sejoli_check_reminder_table'));

		if(true !== $check_table && NULL === $result) :
			?>
			<div class="notice notice-error">
				<h3>ERROR</h3>
				<p>
					<?php _e('Tabel untuk data reminder tidak terbuat. SILAHKAN nonaktifkan SEJOLI dan aktifkan kembali untuk mengatasi masalah ini'); ?>
				</p>
			</div>
			<?php

		elseif(true !== $check_table) :
			update_option('_sejoli_check_reminder_table', true);
		endif;
	}

	/**
	 * Add reminder custom columns
	 * Hooked via filter manage_sejoli-reminder_posts_columns, priority 100
	 * @since 	1.1.9
	 * @param 	array $columns 	Array of reminder table columns
	 * @return 	array
	 */
	public function add_reminder_columns(array $columns) {

		unset($columns['date']);

		$columns['sejoli-reminder-type'] = __('Tipe Pengingat', 'sejoli');
		$columns['sejoli-media']		 = __('Media', 'sejoli');
		$columns['sejoli-reminder-day']	 = __('Interval Pengiriman', 'sejoli');

		return $columns;

	}

	/**
	 * Display log page
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function display_log() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/reminder/log.php';
	}

	/**
	 * Display custom reminder column data
	 * Hooked via manage_posts_custom_column, priority 100
	 * @since 	1.1.9
	 * @param  	string 		$column
	 * @param  	integer 	$post_id
	 * @return 	void
	 */
	public function display_custom_column_data($column, $post_id) {

		switch($column) :

			case 'sejoli-reminder-type' :

				$reminder_type = sejolisa_carbon_get_post_meta($post_id, 'reminder_type');

				if('order' === $reminder_type) :
					_e('Pengingat order', 'sejoli');
				else :
					_e('Pengingat masa waktu berlangganan', 'sejoli');
				endif;

				break;

			case 'sejoli-media' :

				$media_type = sejolisa_carbon_get_post_meta($post_id, 'reminder_media');

				$output = '';

				foreach ( $media_type as $media ) :

				  $output .= $media . ', ';

				endforeach;

				$output = rtrim( $output, ', ' );

				echo strtoupper($output);

				break;

			case 'sejoli-reminder-day' :
				$reminder_type     = sejolisa_carbon_get_post_meta($post_id, 'reminder_type');
				$send_day          = absint(sejolisa_carbon_get_post_meta($post_id, 'reminder_day'));
				$send_hour         = absint(sejolisa_carbon_get_post_meta($post_id, 'reminder_hour'));
				$subscription_type = sejolisa_carbon_get_post_meta($post_id, 'reminder_subscription_type');

				if('order' === $reminder_type || 'after' === $subscription_type) :

					if($send_day) :
						echo 'Hari +' . $send_day;
					else:
						echo 'Jam +' . $send_hour;
					endif;

				else :

					if($send_day) :
						echo 'Hari -' . $send_day;
					else:
						echo 'Jam -' . $send_hour;
					endif;

				endif;

				break;

		endswitch;

	}

	/**
	 * Register routine to check and send
	 * Hooked via action admin_init, priority 100
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function register_routine() {

		if(false === wp_next_scheduled('sejoli/reminder/delete')) :
			wp_schedule_event(time() + 60, 'daily', 'sejoli/reminder/delete');
		else :

			$recurring 	= wp_get_schedule('sejoli/reminder/delete');

			if('daily' !== $recurring) :
				wp_reschedule_event(time() + 60, 'daily', 'sejoli/reminder/delete');
			endif;
		endif;

		if(false === wp_next_scheduled('sejoli/reminder/check')) :
			wp_schedule_event(time() + 60, 'every_30_min', 'sejoli/reminder/check');
		else :

			$recurring 	= wp_get_schedule('sejoli/reminder/check');

			if('every_30_min' !== $recurring) :
				wp_reschedule_event(time() + 60, 'every_30_min', 'sejoli/reminder/check');
			endif;
		endif;

		if(false === wp_next_scheduled('sejoli/reminder/send')) :
			wp_schedule_event(time() + 60, 'every_10_min', 'sejoli/reminder/send');
		else :

			$recurring 	= wp_get_schedule('sejoli/reminder/send');

			if('every_10_min' !== $recurring) :
				wp_reschedule_event(time() + 60, 'every_10_min', 'sejoli/reminder/send');
			endif;
		endif;
	}

	/**
	 * Send reminder data
	 * Hooked via action sejoli/reminder/send, priority 100
	 * @since 	1.1.9
	 * @return 	void
	 */
	public function send_reminder_data() {

		$response = sejolisa_get_reminders(
			array( 'reminder.status'	=> false ),
			array(
				'length'	=> 10,
				'order'		=> array(
					array(
						'column' => 'send_hours',
						'sort'	 => 'ASC'
					),
					array(
						'column' => 'send_day',
						'sort'	 => 'ASC'
					)
				)
			) // later
		);

		if(false !== $response['valid']) :
			$reminder_ids = array();
			foreach($response['reminders'] as $reminder_data) :
				$reminder_ids[] = $reminder_data->ID;
				$recipient      = sejolisa_get_user($reminder_data->user_id);

				if(in_array($reminder_data->media_type, array('sms', 'whatsapp')) ) :
					$reminder_data->recipient = $recipient->meta->phone;
				else :
					$reminder_data->recipient = $recipient->user_email;
				endif;

				sejolisa_update_reminder_status($reminder_ids);

				do_action('sejoli/notification/reminder', $reminder_data);
			endforeach;
		endif;

		exit;
	}

	/**
	 * Delete sent reminder data
	 * Hooked via action sejoli/reminder/delete, priority 1
	 * @since 	1.2.0
	 * @return 	void
	 */
	public function delete_sent_reminder_log() {

		$min_day  = 0;
		$response = sejolisa_delete_sent_reminder($min_day);

		if(false !== $response['valid']) :
	        do_action('sejoli/log/write', 'clean-reminder', implode('<br />', $response['messages']['success']) );
	    endif;

		exit;
	}

	/**
	 * Alter table reminder data
	 * Hooked via action admin_init, priority 100
	 * @since 	1.2.0
	 * @return 	void
	 */
	static function alter_table_reminder() {

		$sejoli_current_version = SEJOLISA_VERSION;
		$sejoli_old_version     = get_option( 'sejolisa_plugin_version' );

		if( !$sejoli_old_version ) :

			add_option( "sejolisa_plugin_version", $sejoli_current_version );

		endif;

		if ( !(version_compare( $sejoli_old_version, $sejoli_current_version ) < 0) ) :

                return FALSE;

        endif;

        update_option( 'sejolisa_plugin_version', $sejoli_current_version );

        \SejoliSA\Model\Reminder::alter_table();

		flush_rewrite_rules();

	}

}
