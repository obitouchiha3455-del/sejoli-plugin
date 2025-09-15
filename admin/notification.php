<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Notification {

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
	 * The container of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $container    The current container of this plugin.
	 */
	private $container;

	/**
	 * Notification event
	 *
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	string
	 */
	protected $event = 'on-hold';

	/**
	 * Notification media libraries
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $media_libraries = false;

	/**
	 * Whatsapp Libraries
	 * @since	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $whatsapp_services = [];

	/**
	 * SMS Libraries
	 * @since	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $sms_services = [];

	/**
	 * Notification libraries
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $libraries = false;

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
     * Display product setting fields
     * Hooked via filter sejoli/product/fields, priority 90
     * @since   1.0.0
     * @param   array  $fields   Container fields
     * @return  array
     */
    public function setup_product_setting_fields( array $fields ) {

        $fields[]   = array(
            'title'  => __('Notifikasi', 'sejoli'),
            'fields' => array(
                Field::make('separator', 'sep_product_notification', __('Pengaturan Notifikasi', 'sejoli'))
					->set_classes('sejoli-with-help'),

                Field::make('html', 'product_notification_info')
                    ->set_html( __('Fitur notifikasi ini akan menampilkan informasi notifikasi per produk untuk masing-masing status order tertentu', 'sejoli')),

                Field::make( 'html', 'product_notification_info_shortcode_html', __( 'Shortcode' ) )
                    ->set_html( '<b>Shortcode</b>: <pre><i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama url halaman member area.', 'sejoli').'">{{memberurl}}</code> <code title="'.__('Shortcode untuk menampilkan nama website.', 'sejoli').'">{{sitename}}</code> <code title="'.__('Shortcode untuk menampilkan url website.', 'sejoli').'">{{siteurl}}</code> <code title="'.__('Shortcode untuk menampilkan ID order.', 'sejoli').'">{{order-id}}</code> <code title="'.__('Shortcode untuk menampilkan nomor invoice.', 'sejoli').'">{{invoice-id}}</code> <code title="'.__('Shortcode untuk menampilkan total order.', 'sejoli').'">{{order-grand-total}}</code></br></br><code title="'.__('Shortcode untuk menampilkan nama pembeli.', 'sejoli').'">{{buyer-name}}</code> <code title="'.__('Shortcode untuk menampilkan email pembeli.', 'sejoli').'">{{buyer-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon pembeli.', 'sejoli').'">{{buyer-phone}}</code> <code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli').'">{{product-name}}</code> <code title="'.__('Shortcode untuk menampilkan jumlah produk.', 'sejoli').'">{{quantity}}</code> <code title="'.__('Shortcode untuk menampilkan url halaman konfirmasi pembayaran.', 'sejoli').'">{{confirm-url}}</code> <code title="'.__('Shortcode untuk menampilkan tanggal pembelian.', 'sejoli').'">{{order-day}}</code></br></br><code title="'.__('Shortcode untuk menampilkan masa berakhir pembelian.', 'sejoli').'">{{close-time}}</code> <code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli').'">{{affiliate-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli').'">{{affiliate-phone}}</code> <code title="'.__('Shortcode untuk menampilkan tier affiliasi.', 'sejoli').'">{{affiliate-tier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi komisi.', 'sejoli').'">{{commission}}</code></br></br><code title="'.__('Shortcode untuk menampilkan informasi detail order.', 'sejoli').'">{{order-detail}}</code> <code title="'.__('Shortcode untuk menampilkan informasi meta order.', 'sejoli').'">{{order-meta}}</code> <code title="'.__('Shortcode untuk menampilkan informasi metode pembayaran.', 'sejoli').'">{{payment-gateway}}</code> <code title="'.__('Shortcode untuk menampilkan informasi notifikasi per-produk.', 'sejoli').'">{{product-info}}</code></i></pre>' ),

                Field::make( 'checkbox', 'product_notification_enable', __('Aktifkan produk notifikasi', 'sejoli')),

                Field::make('textarea', 'product_notification_on_hold', __('Menunggu Pembayaran', 'sejoli'))
					->set_default_value('')
					->set_conditional_logic(array(
                        array(
                            'field' => 'product_notification_enable',
                            'value' => true
                        )
                    )),
				
				Field::make('textarea', 'product_notification_payment_confirm', __('Pembayaran dikonfirmasi', 'sejoli'))
					->set_default_value('')
					->set_conditional_logic(array(
                        array(
                            'field' => 'product_notification_enable',
                            'value' => true
                        )
                    )),

                Field::make('textarea', 'product_notification_in_progress', __('Pesanan diproses', 'sejoli'))
					->set_default_value('')
					->set_conditional_logic(array(
                        array(
                            'field' => 'product_notification_enable',
                            'value' => true
                        )
                    )),

                Field::make('textarea', 'product_notification_shipping', __('Proses pengiriman', 'sejoli'))
					->set_default_value('')
					->set_conditional_logic(array(
                        array(
                            'field' => 'product_notification_enable',
                            'value' => true
                        )
                    )),

                Field::make('textarea', 'product_notification_completed', __('Order selesai', 'sejoli'))
					->set_default_value('')
					->set_conditional_logic(array(
                        array(
                            'field' => 'product_notification_enable',
                            'value' => true
                        )
                    )),

                Field::make('textarea', 'product_notification_cancel', __('Pembatalan Invoice', 'sejoli'))
					->set_default_value('')
					->set_conditional_logic(array(
                        array(
                            'field' => 'product_notification_enable',
                            'value' => true
                        )
                    )),

                Field::make('textarea', 'product_notification_refund', __('Refund', 'sejoli'))
					->set_default_value('')
					->set_conditional_logic(array(
                        array(
                            'field' => 'product_notification_enable',
                            'value' => true
                        )
                    )),
            )
        );

        return $fields;

    }

    /**
	 * Setup custom fields for product
	 * Hooked via action carbon_fields_register_fields, priority 10
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function setup_carbon_fields() {

		if(false === sejolisa_check_own_license()) :
			return;
		endif;

		$fields       = apply_filters( 'sejoli/notification/fields', []);
        $main_setting = apply_filters( 'sejoli/general/container', '');

		if(is_array($fields) && 0 < count($fields)) :

			$this->container = Container::make('theme_options', __('Notifikasi', 'sejoli'))
                                ->set_page_parent($main_setting);

			foreach($fields as $field) :
				$this->container->add_tab($field['title'], $field['fields']);
			endforeach;
		endif;
	}

	/**
	 * Prepare notification media library
	 * Hooked via action init, priority 20
	 * @since 	1.0.0
	 * @since 	1.5.3.1		Add woowandroidv2 and starsender library
	 * @return 	void
	 */
	public function prepare_media_libraries() {

		require_once( SEJOLISA_DIR . 'notification-media/main.php');
		require_once( SEJOLISA_DIR . 'notification-media/email.php');
		require_once( SEJOLISA_DIR . 'notification-media/sms.php');
		require_once( SEJOLISA_DIR . 'notification-media/whatsapp.php');

		$this->media_libraries = [
			'email'    => new \SejoliSA\NotificationMedia\Email,
			'sms'      => new \SejoliSA\NotificationMedia\SMS,
			'whatsapp' => new \SejoliSA\NotificationMedia\WhatsApp,
		];

		$this->media_libraries = apply_filters('sejoli/notification/media-libraries', $this->media_libraries);

		// Whatsapp libraries
		require_once( SEJOLISA_DIR . 'notification-media/starsender.php');
		require_once( SEJOLISA_DIR . 'notification-media/wanotif.php');
		require_once( SEJOLISA_DIR . 'notification-media/woowa.php');

		$this->whatsapp_services = [
			'starsender'    => new \SejoliSA\NotificationMedia\StarSender,
			'wanotif'       => new \SejoliSA\NotificationMedia\Wanotif,
			'woowa'         => new \SejoliSA\NotificationMedia\WooWa,
		];

		$this->whatsapp_services = apply_filters('sejoli/notification/whatsapp-services', $this->whatsapp_services);

		// SMS libraries
		require_once( SEJOLISA_DIR . 'notification-media/sms-notifikasi.php');

		$this->sms_services = [
			'sms-notifikasi'	=> new \SejoliSA\NotificationMedia\SMSNotifikasi
		];

		$this->sms_services = apply_filters('sejoli/notification/sms-services', $this->sms_services);
	}

	/**
	 * Get available media libraries
	 * Hooked via filter sejoli/notification/available-media-libraries, 1
	 * @since 	1.0.0
	 * @param  	array  $media_libraries
	 * @return 	array
	 */
	public function get_available_media_libraries($media_libraries = array()) {
		return $this->media_libraries;
	}

	/**
	 * Set whatsapp service options
	 * Hooked via filter sejoli/whatsapp/service-options, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$options
	 * @return 	array
	 */
	public function set_whatsapp_service_options(array $options) {

		foreach($this->whatsapp_services as $key => $service) :
			$options[$key]	= $service->get_label();
		endforeach;

		return $options;
	}

	/**
	 * Get available whatsapp services
	 * Hooked via filter sejoli/whatsapp/available-services, priority 1
	 * @param  array  $services [description]
	 * @return array
	 */
	public function get_available_whatsapp_services(array $services) {
		return $this->whatsapp_services;
	}

	/**
	 * Set sms service options
	 * Hooked via filter sejoli/sms/service-options, priority 1
	 * @since 	1.0.0
	 * @param 	array 	$options
	 * @return 	array
	 */
	public function set_sms_service_options(array $options) {

		foreach($this->sms_services as $key => $service) :
			$options[$key]	= $service->get_label();
		endforeach;

		return $options;
	}

	/**
	 * Get available sms services
	 * Hooked via filter sejoli/sms/available-services, priority 1
	 * @param  array  $services [description]
	 * @return array
	 */
	public function get_available_sms_services(array $services) {
		return $this->sms_services;
	}

	/**
	 * Prepare notification library
	 * Hooked via action init, priority 30
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function prepare_libraries() {

		require_once( SEJOLISA_DIR . 'notification/main.php');
		require_once( SEJOLISA_DIR . 'notification/registration.php');
		require_once( SEJOLISA_DIR . 'notification/on-hold.php');
		require_once( SEJOLISA_DIR . 'notification/confirm-payment.php');
		require_once( SEJOLISA_DIR . 'notification/in-progress.php');
		require_once( SEJOLISA_DIR . 'notification/shipping.php');
		require_once( SEJOLISA_DIR . 'notification/completed.php');
		require_once( SEJOLISA_DIR . 'notification/cancel.php');
		require_once( SEJOLISA_DIR . 'notification/refund.php');
		require_once( SEJOLISA_DIR . 'notification/commission.php');
		require_once( SEJOLISA_DIR . 'notification/pay-commission.php');
		require_once( SEJOLISA_DIR . 'notification/bulk-notification.php');
		require_once( SEJOLISA_DIR . 'notification/reminder.php');

		$this->libraries = [
			'registration'      => new \SejoliSa\Notification\Registration,
			'on-hold'           => new \SejoliSA\Notification\OnHold,
			'confirm-payment'   => new \SejoliSA\Notification\ConfirmPayment,
			'in-progress'       => new \SejoliSA\Notification\InProgress,
			'shipping'          => new \SejoliSA\Notification\Shipping,
			'completed'         => new \SejoliSA\Notification\Completed,
			'cancelled'         => new \SejoliSA\Notification\Cancel,
			'refunded'          => new \SejoliSA\Notification\Refund,
			'commission'        => new \SejoliSA\Notification\Commission,
			'pay-commission'    => new \SejoliSA\Notification\PayCommission,
			'bulk-notification' => new \SejoliSA\Notification\BulkNotification,
			'reminder' 			=> new \SejoliSA\Notification\Reminder
		];

		$this->libraries = apply_filters('sejoli/notification/libraries', $this->libraries);
	}

	/**
	 * Add general fields setup to notification page
	 * Hooked via filter, sejoli/notification/fields priority 20
	 * @since 	1.0.0
	 * @param 	array $fields
	 * @return 	array
	 */
	public function add_general_fields(array $fields) {

		$order_status      = apply_filters('sejoli/order/status', []);
		$whatsapp_services = apply_filters('sejoli/whatsapp/service-options', [
			false => __('Tidak menggunakan notifikasi Whatsapp', 'sejoli')
		]);

		$sms_services = apply_filters('sejoli/sms/service-options', [
			false => __('Tidak mennggunakan notifikasi SMS', 'sejoli')
		]);

		// Email setup fields
		$email_fields = [

			Field::make('separator', 'sep_notification_email', __('Pengaturan Email','sejoli'))
				->set_classes('main-title'),

			Field::make('text',		'notification_email_from_address', __('Alamat email pengirim', 'sejoli'))
				->set_default_value(sejolisa_get_email_domain('ecommerce'))
				->set_required(true)
				->set_width(50)
				->set_help_text(__('Untuk bagian ini, pastikan alamat email yang diisi merupakan email dengan domain website ini. Jika anda ragu atau tidak mengerti, JANGAN UBAH bagian ini!', 'sejoli')),

			Field::make('text',		'notification_email_from_name', __('Nama email pengirim', 'sejoli'))
				->set_default_value(get_bloginfo('name'))
				->set_required(true)
				->set_width(50)
				->set_help_text(__('Diisi dengan nama yang akan tertera sebagai nama pengirim email', 'sejoli')),

			Field::make('text', 	'notification_email_reply_address', __('Alamat email balasan', 'sejoli'))
				->set_default_value(get_option('admin_email'))
				->set_required(true)
				->set_width(50)
				->set_help_text(__('Anda bisa menggunakan alamat email apapun, baik gmail, yahoo mail, hotmail dll', 'sejoli')),

			Field::make('text', 	'notification_email_reply_nama', __('Nama email balasan', 'sejoli'))
				->set_default_value(get_bloginfo('name'))
				->set_required(true)
				->set_width(50)
				->set_help_text(__('Nama yang tertera jika akan membalas email', 'sejoli')),

			Field::make('text', 	'notification_confirmation_recipients', __('Email Penerima Konfirmasi', 'sejoli'))
				->set_required(true)
				->set_default_value(get_option('admin_email'))
				->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli')),

			Field::make('image',		'notification_email_logo',	__('Logo', 'sejoli')),

			Field::make('rich_text',	'notification_email_footer', __('Footer', 'sejoli'))
				->set_help_text(__('Bisa diisi dengan informasi usaha anda seperti telpon dll', 'sejoli')),

			Field::make('textarea',	'notification_email_copyright', __('Copyright', 'sejoli'))
				->set_default_value(sprintf(__('Coopyright &copy; %s %s', 'sejoli'), date('Y'), get_bloginfo('name')))
		];

		// Whatsapp setup fields
		$whatsapp_fields = [
			Field::make('separator', 'sep_notification_whatsapp', __('Pengaturan Whatsapp','sejoli'))
				->set_classes('main-title'),

			Field::make('select',	'notification_whatsapp_service', __('Layanan yang digunakan', 'sejoli'))
				->add_options($whatsapp_services)
		];

		$whatsapp_fields = apply_filters('sejoli/whatsapp/setup-fields', $whatsapp_fields);

		// SMS setup fields
		$sms_fields = [
			Field::make('separator', 'sep_notification_sms', __('Pengaturan SMS','sejoli'))
				->set_classes('main-title'),

			Field::make('select',	'notification_sms_service', __('Layanan yang digunakan', 'sejoli'))
				->add_options($sms_services)
		];

		$sms_fields = apply_filters('sejoli/sms/setup-fields', $sms_fields);

		$fields['general'] = [
			'title'		=> __('Pengaturan Umum', 'sejoli'),
			'fields'	=> array_merge($email_fields, $whatsapp_fields, $sms_fields)
		];

		return $fields;
	}

	/**
	 * Set notification contents
	 * Hooked sejoli/notification/content , priority 1
	 * @since 	1.0.0
	 * @param 	string 	$content    	Content that will be manipulated
	 * @param 	array  	$order_data 	Order data in Array
	 * @param 	string 	$media 			Media library for rendering content
	 * @param 	string 	$event 			Notification event for rendering content
	 * @return 	string
	 */
	public function set_notification_content(string $content, array $order_data, string $media, string $event) {

		if(isset($this->media_libraries[$media]) && isset($this->libraries[$event])) :

			$this->libraries[$event]->prepare($order_data);
			$content = $this->libraries[$event]->set_notification_content($content, $media);
			$content = $this->libraries[$event]->render_shortcode($content);

			if('whatsapp' !== $media) :
				return nl2br($content);
			endif;

		endif;

		return $content;
	}

	/**
	 * Send registration notification
	 * Hooked via action sejoli/notification/registration, priority 100
	 * @since 	1.0.0
	 * @param  	array  $user_data
	 * @return 	void
	 */
	public function send_registration_notification(array $user_data) {
		$this->libraries['registration']->trigger($user_data);

	}

	/**
	 * Send on-hold notification
	 * Hooked via action sejoli/notification/order/on-hold, priority 100
	 * Hooked via action sejoli/order/set-status/on-hold, priority 100
	 * @since 	1.0.0
	 * @param  	array  $order_data [description]
	 * @return 	void
	 */
	public function send_on_hold_notification(array $order_data) {
		$this->libraries['on-hold']->trigger($order_data);

	}

	/**
	 * Send confirm-payment notification
	 * Hooked via action sejoli/notification/order/confirm-payment, priority 100
	 * Hooked via action sejoli/order/set-status/payment-confirm, priority 100
	 * @since 	1.0.0
	 * @param  	array  $order_data [description]
	 * @return 	void
	 */
	public function send_confirm_payment_notification(array $order_data) {

		global $wpdb;

		$attachments = array();
    	$order_id    = $order_data['ID'];

    	$get_data_payment_confirm = $wpdb->get_results( "
                SELECT * 
                FROM {$wpdb->prefix}sejolisa_confirmations
                WHERE order_id = '".$order_id."'
            " );

    	$get_payment_confirm_data = isset($get_data_payment_confirm[0]) ? $get_data_payment_confirm[0] : null;
    	$payment_confirm_detail   = isset($get_payment_confirm_data->detail) ? $get_payment_confirm_data->detail : null;
        $payment_confirm_data     = safe_unserialize($payment_confirm_detail);
        
    	if( isset( $payment_confirm_data ) ) :

    		$upload_dir = wp_upload_dir();
    		$proof_id   = isset($payment_confirm_data['proof_id']) ? $payment_confirm_data['proof_id'] : '';
    		$file       = isset($attachment_metadata['file']) ? $attachment_metadata['file'] : '';
			$attachment_metadata = wp_get_attachment_metadata( $proof_id );
    		$attachments[]       = $upload_dir['basedir'] . '/' . $file;
    	
    	else:

    		$attachments[] = '';

    	endif;

    	$this->libraries['confirm-payment']->trigger($order_data, $attachments);
    
	}
	
	/**
	 * Send in-progress notification
	 * Hooked via action sejoli/notification/order/in-progress, priority 100
	 * Hooked via action sejoli/order/set-status/in-progress, priority 100
	 * @since 	1.0.0
	 * @param  	array  $order_data [description]
	 * @return 	void
	 */
	public function send_in_progress_notification(array $order_data) {
		$this->libraries['in-progress']->trigger($order_data);
	}

	/**
	 * Send shipping notification
	 * Hooked via action sejoli/notification/order/shipping, priority 100
	 * Hooked via action sejoli/order/set-status/shipping, priority 100
	 * @since 	1.0.0
	 * @param  	array  $order_data [description]
	 * @return 	void
	 */
	public function send_shipping_notification(array $order_data) {
		$this->libraries['shipping']->trigger($order_data);
	}

	/**
	 * Send completed notification
	 * Hooked via action sejoli/order/set-status/completed, priority 300
	 * @since 	1.0.0
	 * @since 	1.4.2 	Only called by sejoli/order/set-status/completed
	 * @param  	array  $order_data [description]
	 * @return 	void
	 */
	public function send_completed_notification(array $order_data) {
		$this->libraries['completed']->trigger($order_data);

	}

	/**
	 * Send completed notification manually, triggered by user
	 * Hooked via action sejoli/notification/order/completed, priority 300
	 * @since 	1.4.2
	 * @param  	array  $order_data
	 * @return 	void
	 */
	public function send_completed_notification_manually( array $order_data ) {
		$this->libraries['completed']->trigger($order_data);

		$respond = sejolisa_get_commissions([
			'order_id'	=> $order_data['ID']
		]);

		if(false !== $respond['valid']) :

			foreach((array) $respond['commissions'] as $commission) :

				$commission = (array) $commission;
				$this->send_active_commission_notification($commission, $order_data);

			endforeach;

		endif;
	}

	/**
	 * Send completed notification
	 * Hooked via action sejoli/notification/order/refunded, priority 100
	 * Hooked via action sejoli/order/set-status/refunded, priority 100
	 * @since 	1.0.0
	 * @param  	array  $order_data [description]
	 * @return 	void
	 */
	public function send_refunded_notification(array $order_data) {
		$this->libraries['refunded']->trigger($order_data);

	}

	/**
	 * Send completed notification
	 * Hooked via action sejoli/notification/order/cancelled, priority 100
	 * Hooked via action sejoli/order/set-status/cancelled, priority 100
	 * @since 	1.0.0
	 * @param  	array  $order_data [description]
	 * @return 	void
	 */
	public function send_cancelled_notification(array $order_data) {
		$this->libraries['cancelled']->trigger($order_data);

	}

	/**
	 * Send commission added notification
	 * Hooked via action sejoli/commission/set-status/added, priority 100
	 * @sice 	1.0.0
	 * @param  	array 	$commission [description]
	 * @param  	array 	$order_data [description]
	 * @return 	void
	 */
	public function send_active_commission_notification($commission, $order_data) {
		$this->libraries['commission']->trigger($commission, $order_data);
	}

	/**
	 * Send bulk notification
	 * Hooked via action sejoli/bulk-notification/process, priority 100
	 * @sice 	1.0.0
	 * @param  	array 	$commission [description]
	 * @param  	array 	$order_data [description]
	 * @return 	void
	 */
	public function send_bulk_notification($content, $order_data) {
		$this->libraries['bulk-notification']->trigger($content, $order_data);
	}

   /**
   	 * Send commission paid notification
   	 * Hooked via action sejoli/comission/set-status/paid, priority 100
   	 * @sice 	1.0.0
   	 * @param  	array 	$commission_data
   	 * @return 	void
   	 */
   	public function send_commission_paid_notification($commission_data) {
   		$this->libraries['pay-commission']->trigger($commission_data);
   	}

	/**
	 * Prepare anything for reminder data
	 * Hooked via filter sejoli/reminder/content, priority 1
	 * @since 	1.1.9
	 * @param 	array 	$content
	 * @param  	array 	$order_data
	 * @param 	array 	$reminder_data
	 * @return 	array
	 */
	public function prepare_for_reminder($content, $order_data, $reminder_data) {

		$this->libraries['reminder']->setup_data($order_data, $reminder_data);

		return $this->libraries['reminder']->get_data();
	}

	/**
	 * Send reminder data
	 * Hooked via action sejoli/notification/reminder, priority 1
	 * @since 	1.1.9
	 * @param  	object  $reminder_data
	 * @return 	void
	 */
	public function send_reminder($reminder_data) {
		$this->libraries['reminder']->trigger($reminder_data);
	}

}
