<?php

namespace SejoliSA\Notification;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Registration extends Main {

    /**
     * Recipient data
     * @since   1.0.0
     * @var     array
     */
    protected $recipients;

    /**
     * Set user data
     * @var array
     */
    protected $user_data;

    /**
     * Attachment for file
     * @since   1.0.0
     * @var     bool|array
     */
    public $attachments = false;

    /**
     * Construction
     */
    public function __construct() {
        add_filter('sejoli/notification/fields',    [$this, 'add_setting_fields'], 20);
        // add_filter('sejoli/notification/content',   [$this, 'set_user_detail_content'], 30, 3);
    }

    /**
     * Add notification setting fields
     * Hooked via filter, sejoli/notification/fields priority 25
     * @since   1.0.0
     * @param   array $fields All fields for notification setting form
     */
    public function add_setting_fields(array $fields) {

        $fields['registration'] = [
			'title'  => __('Pendaftaran User Baru', 'sejoli'),
			'fields' => [

                // Untuk buyer
				Field::make( 'html', 'sejoli_registration_shortcode_html', __( 'Shortcode' ) )
                    ->set_html( '<b>Shortcode</b>: <pre><i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama url halaman member area.', 'sejoli').'">{{memberurl}}</code> <code title="'.__('Shortcode untuk menampilkan informasi akses user.', 'sejoli').'">{{user-access}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nama user.', 'sejoli').'">{{user-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama website.', 'sejoli').'">{{sitename}}</code> <code title="'.__('Shortcode untuk menampilkan url website.', 'sejoli').'">{{siteurl}}</code> <code title="'.__('Shortcode untuk menampilkan ID order.', 'sejoli').'">{{order-id}}</code> <code title="'.__('Shortcode untuk menampilkan nomor invoice.', 'sejoli').'">{{invoice-id}}</code></br></br><code title="'.__('Shortcode untuk menampilkan total order.', 'sejoli').'">{{order-grand-total}}</code><code title="'.__('Shortcode untuk menampilkan nama pembeli.', 'sejoli').'">{{buyer-name}}</code> <code title="'.__('Shortcode untuk menampilkan email pembeli.', 'sejoli').'">{{buyer-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon pembeli.', 'sejoli').'">{{buyer-phone}}</code> <code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli').'">{{product-name}}</code> <code title="'.__('Shortcode untuk menampilkan jumlah produk.', 'sejoli').'">{{quantity}}</code> <code title="'.__('Shortcode untuk menampilkan url halaman konfirmasi pembayaran.', 'sejoli').'">{{confirm-url}}</code></br></br><code title="'.__('Shortcode untuk menampilkan link dokumen attachment konfirmasi pembayaran.', 'sejoli').'">{{confirm-payment-file}}</code> <code title="'.__('Shortcode untuk menampilkan tanggal pembelian.', 'sejoli').'">{{order-day}}</code> <code title="'.__('Shortcode untuk menampilkan masa berakhir pembelian.', 'sejoli').'">{{close-time}}</code> <code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli').'">{{affiliate-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli').'">{{affiliate-phone}}</code></br></br><code title="'.__('Shortcode untuk menampilkan tier affiliasi.', 'sejoli').'">{{affiliate-tier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi komisi.', 'sejoli').'">{{commission}}</code> <code title="'.__('Shortcode untuk menampilkan informasi detail order.', 'sejoli').'">{{order-detail}}</code> <code title="'.__('Shortcode untuk menampilkan informasi meta order.', 'sejoli').'">{{order-meta}}</code> <code title="'.__('Shortcode untuk menampilkan informasi metode pembayaran.', 'sejoli').'">{{payment-gateway}}</code> <code title="'.__('Shortcode untuk menampilkan informasi notifikasi per-produk.', 'sejoli').'">{{product-info}}</code></br></br><code title="'.__('Shortcode untuk menampilkan informasi kurir pengiriman.', 'sejoli').'">{{shipping-courier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nomor resi pengiriman.', 'sejoli').'">{{shipping-number}}</code></i></pre>' ),

                Field::make('separator'	,'sep_registration_email', 	__('Email' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media email', 'sejoli')),
				Field::make('text', 	'registration_email_title',	 __('Judul' ,'sejoli'))
					->set_required(true)
					->set_default_value(__('{{user-name}}, berikut data anda di website {{sitename}}', 'sejoli')),
				Field::make('rich_text', 'registration_email_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('registration-user')),

				Field::make('separator'	,'sep_registration_sms', 	__('SMS' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli')),
				Field::make('textarea', 'registration_sms_content', __('Konten', 'sejoli'))
                    ->set_default_value(sejoli_get_notification_content('registration-user', 'sms')),

				Field::make('separator'	,'sep_registration_whatsapp', 	__('WhatsApp' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli')),
				Field::make('textarea', 'registration_whatsapp_content', __('Konten', 'sejoli'))
                    ->set_default_value(sejoli_get_notification_content('registration-user', 'whatsapp')),

                // Untuk admin
				Field::make('separator', 	'sep_registration_admin', 		__('Konten untuk admin dan lainnya','sejoli'))
					->set_classes('main-title'),

				Field::make('checkbox',		'registration_admin_active',	__('Aktifkan notifikasi untuk admin', 'sejoli'))
                    ->set_default_value(true),

				Field::make('separator',	'sep_registration_admin_email', 	__('Email' ,'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'registration_admin_email_recipient', __('Alamat Email Penerima', 'sejoli'))
					->set_default_value(get_option('admin_email'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('text', 		'registration_admin_email_title',	 __('Judul' ,'sejoli'))
					->set_required(true)
					->set_default_value(__('User baru telah didaftarkan pada website {{sitename}}', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('rich_text', 'registration_admin_email_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('registration-admin'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('separator'	,'sep_registration_admin_sms', 	__('SMS' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'registration_admin_sms_recipient', __('Nomor SMS Penerima', 'sejoli'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('textarea', 'registration_admin_sms_content', __('Konten', 'sejoli'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via sms', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('registration-admin', 'sms'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('separator'	,'sep_registration_admin_whatsapp', 	__('WhatsApp' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'registration_admin_whatsapp_recipient', __('Nomor WhatsApp Penerima', 'sejoli'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),

				Field::make('textarea', 'registration_admin_whatsapp_content', __('Konten', 'sejoli'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via whatsapp', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('registration-admin', 'whatsapp'))
					->set_conditional_logic([
						[
							'field'	=> 'registration_admin_active',
							'value' => true,
						]
					]),
			]
		];



        return $fields;
    }

    /**
     * Prepare content for notification
     * @since   1.0.0
     * @return  void
     */
    protected function set_content() {

        // ***********************
		// Setup content for buyer
		// ***********************

		$this->set_recipient_title  ('buyer', 'email', sejolisa_carbon_get_theme_option('registration_email_title'));
		$this->set_recipient_content('buyer', 'email', $this->set_notification_content(
												sejolisa_carbon_get_theme_option('registration_email_content'),
												'email',
                                                'buyer'
											 ));

		if(!empty(sejolisa_carbon_get_theme_option('registration_whatsapp_content'))) :

            $this->set_enable_send('whatsapp', 'buyer', true);
			$this->set_recipient_content('buyer', 'whatsapp', $this->set_notification_content(
		                                                sejolisa_carbon_get_theme_option('registration_whatsapp_content'),
		                                                'whatsapp',
                                                        'buyer'
                                                    ));
        endif;

		if(!empty(sejolisa_carbon_get_theme_option('registration_sms_content'))) :
            $this->set_enable_send('sms', 'buyer', true);
			$this->set_recipient_content('buyer', 'sms', $this->set_notification_content(
                                    				sejolisa_carbon_get_theme_option('registration_sms_content'),
                                    				'sms',
                                                    'buyer'
                                    			));
        endif;

		// ***********************
		// Setup content for buyer
		// ***********************

		if(false !== sejolisa_carbon_get_theme_option('registration_admin_active')) :

            $this->set_enable_send('email', 'admin', true);
    		$this->set_recipient_title('admin', 'email', sejolisa_carbon_get_theme_option('registration_admin_email_title'));
    		$this->set_recipient_content('admin', 'email', $this->set_notification_content(
													sejolisa_carbon_get_theme_option('registration_admin_email_content'),
													'email',
                                                    'admin'
												));

    		if(!empty(sejolisa_carbon_get_theme_option('registration_admin_whatsapp_content'))) :

				$this->set_enable_send('whatsapp', 'admin', true);
				$this->set_recipient_content('admin', 'whatsapp', $this->set_notification_content(
                                                			sejolisa_carbon_get_theme_option('registration_admin_whatsapp_content'),
                                                			'whatsapp',
                                                            'admin'
                                                		));
            endif;

    		if(!empty(sejolisa_carbon_get_theme_option('registration_admin_sms_content'))) :
                $this->set_enable_send('sms', 'admin', true);
				$this->set_recipient_content('admin', 'sms', $this->set_notification_content(
                                        				sejolisa_carbon_get_theme_option('registration_admin_sms_content'),
                                        				'sms',
                                                        'admin'
                                        			));
            endif;
        endif;
    }

    /**
     * Check current media recipients, the data will be stored in $this->recipients
     * @since   1.0.0
     * @param   string  $media
     * @param   string  $role
     * @return  void
     */
    protected function check_recipients($media = 'email', $role = 'admin') {
        $recipients       = sejolisa_carbon_get_theme_option('registration_' . $role . '_' . $media . '_recipient');
        $this->recipients = explode(',', $recipients);
    }

    /**
     * Add user data to shortcodes
     * Hooked via filter sejoli/notification/shortcodes, priority 10
     * @param array $shortcodes
     * @return array
     */
    public function add_shortcode_detail(array $shortcodes) {

        $shortcodes['{{memberurl}}']    = home_url('/member-area/');
        $shortcodes['{{sitename}}']   = get_bloginfo('name');
        $shortcodes['{{siteurl}}']    = home_url('/');
        $shortcodes['{{user-name}}']  = $this->user_data['user_name'];
        $shortcodes['{{user-email}}'] = $this->user_data['user_email'];
        $shortcodes['{{user-pass}}']  = $this->user_data['user_password'];
        $shortcodes['{{user-phone}}'] = $this->user_data['user_phone'];

        return $shortcodes;
    }

    /**
     * Trigger to send notification
     * @since   1.0.0
     * @param   array  $order_data   Order data
     * @return  void
     */
    public function trigger(array $user_data) {

        $this->user_data = $user_data;
        $media_libraries = $this->get_media_libraries();

        $this->shortcode_data = $this->add_shortcode_detail([]);
        $this->set_content();

        $this->trigger_email($user_data, $media_libraries);
        $this->trigger_whatsapp($user_data, $media_libraries);
        $this->trigger_sms($user_data, $media_libraries);

    }

    /**
     * Trigger to send email
     * @since   1.0.0
     * @param   array   $user_data          Array of recipient data
     * @param   array   $media_libraries    Array of available media libraries
     * @return  void
     */
    protected function trigger_email($user_data, $media_libraries) {

        // send email for buyer
		$media_libraries['email']->set_data([
			'user_data' => $user_data,
		]);

		$media_libraries['email']->send(
			array($user_data['user_email']),
			$this->render_shortcode($this->get_recipient_content('buyer', 'email')),
			$this->render_shortcode($this->get_recipient_title('buyer', 'email'))
		);

        if(false !== $this->is_able_to_send('email', 'admin')) :
            $this->check_recipients('email', 'admin');
            $media_libraries['email']->send(
    			$this->recipients,
    			$this->render_shortcode($this->get_recipient_content('admin', 'email')),
    			$this->render_shortcode($this->get_recipient_title('admin', 'email')),
                'admin'
    		);
        endif;
    }

    /**
     * Trigger to send whatsapp
     * @since   1.0.0
     * @param   array   $user_data          Array of recipient data
     * @param   array   $media_libraries    Array of available media libraries
     * @return  void
     */
    protected function trigger_whatsapp($user_data, $media_libraries) {

        // send whatsapp for buyer
        if(false !== $this->is_able_to_send('whatsapp', 'buyer')) :
    		$media_libraries['whatsapp']->set_data([
    			'order_data'     => $this->order_data,
    			'product_data'   => $this->product_data,
    			'buyer_data'     => $this->buyer_data,
    			'affiliate_data' => $this->affiliate_data,
    		]);

            $media_libraries['whatsapp']->send(
    			array($user_data['user_phone']),
    			$this->render_shortcode($this->get_recipient_content('buyer', 'whatsapp'))
    		);
        endif;

        if(false !== $this->is_able_to_send('whatsapp', 'admin')) :
            $this->check_recipients('whatsapp', 'admin');
            $media_libraries['whatsapp']->send(
    			$this->recipients,
    			$this->render_shortcode($this->get_recipient_content('admin', 'whatsapp'))
    		);
        endif;

    }

    /**
     * Trigger to SMS whatsapp
     * @since   1.0.0
     * @param   array   $user_data          Array of recipient data
     * @param   array   $media_libraries    Array of available media libraries
     * @return  void
     */
    protected function trigger_sms($user_data, $media_libraries) {

        // send sms for buyer
        if(false !== $this->is_able_to_send('sms', 'buyer')) :
    		$media_libraries['sms']->set_data([
    			'order_data'     => $this->order_data,
    			'product_data'   => $this->product_data,
    			'buyer_data'     => $this->buyer_data,
    			'affiliate_data' => $this->affiliate_data,
    		]);

            $media_libraries['sms']->send(
    			array($user_data['user_phone']),
    			$this->render_shortcode($this->get_recipient_content('buyer', 'sms'))
    		);
        endif;

        if(false !== $this->is_able_to_send('sms', 'admin')) :
            $this->check_recipients('sms', 'admin');
            $media_libraries['sms']->send(
                $this->recipients,
                $this->render_shortcode($this->get_recipient_content('admin', 'sms'))
            );
        endif;

    }
}
