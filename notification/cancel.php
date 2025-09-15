<?php

namespace SejoliSA\Notification;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Cancel extends Main {

    /**
     * Recipient data
     * @since   1.0.0
     * @var     array
     */
    protected $recipients;

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
        add_filter('sejoli/notification/fields',    [$this, 'add_setting_fields'], 70);
    }

    /**
     * Add notification setting fields
     * Hooked via filter, sejoli/notification/fields priority 25
     * @since   1.0.0
     * @param   array $fields All fields for notification setting form
     */
    public function add_setting_fields(array $fields) {

        $fields['cancel'] = [
			'title'  => __('Pembatalan Invoice', 'sejoli'),
			'fields' => [

				// untuk customer
				Field::make( 'html', 'sejoli_cancel_shortcode_html', __( 'Shortcode' ) )
                    ->set_html( '<b>Shortcode</b>: <pre><i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama url halaman member area.', 'sejoli').'">{{memberurl}}</code> <code title="'.__('Shortcode untuk menampilkan informasi akses user.', 'sejoli').'">{{user-access}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nama user.', 'sejoli').'">{{user-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama website.', 'sejoli').'">{{sitename}}</code> <code title="'.__('Shortcode untuk menampilkan url website.', 'sejoli').'">{{siteurl}}</code> <code title="'.__('Shortcode untuk menampilkan ID order.', 'sejoli').'">{{order-id}}</code> <code title="'.__('Shortcode untuk menampilkan nomor invoice.', 'sejoli').'">{{invoice-id}}</code></br></br><code title="'.__('Shortcode untuk menampilkan total order.', 'sejoli').'">{{order-grand-total}}</code><code title="'.__('Shortcode untuk menampilkan nama pembeli.', 'sejoli').'">{{buyer-name}}</code> <code title="'.__('Shortcode untuk menampilkan email pembeli.', 'sejoli').'">{{buyer-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon pembeli.', 'sejoli').'">{{buyer-phone}}</code> <code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli').'">{{product-name}}</code> <code title="'.__('Shortcode untuk menampilkan jumlah produk.', 'sejoli').'">{{quantity}}</code> <code title="'.__('Shortcode untuk menampilkan url halaman konfirmasi pembayaran.', 'sejoli').'">{{confirm-url}}</code></br></br><code title="'.__('Shortcode untuk menampilkan link dokumen attachment konfirmasi pembayaran.', 'sejoli').'">{{confirm-payment-file}}</code> <code title="'.__('Shortcode untuk menampilkan tanggal pembelian.', 'sejoli').'">{{order-day}}</code> <code title="'.__('Shortcode untuk menampilkan masa berakhir pembelian.', 'sejoli').'">{{close-time}}</code> <code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli').'">{{affiliate-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli').'">{{affiliate-phone}}</code></br></br><code title="'.__('Shortcode untuk menampilkan tier affiliasi.', 'sejoli').'">{{affiliate-tier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi komisi.', 'sejoli').'">{{commission}}</code> <code title="'.__('Shortcode untuk menampilkan informasi detail order.', 'sejoli').'">{{order-detail}}</code> <code title="'.__('Shortcode untuk menampilkan informasi meta order.', 'sejoli').'">{{order-meta}}</code> <code title="'.__('Shortcode untuk menampilkan informasi metode pembayaran.', 'sejoli').'">{{payment-gateway}}</code> <code title="'.__('Shortcode untuk menampilkan informasi notifikasi per-produk.', 'sejoli').'">{{product-info}}</code></br></br><code title="'.__('Shortcode untuk menampilkan informasi kurir pengiriman.', 'sejoli').'">{{shipping-courier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nomor resi pengiriman.', 'sejoli').'">{{shipping-number}}</code></i></pre>' ),

                Field::make('separator', 'sep_cancel_buyer', __('Konten untuk pembeli','sejoli'))
					->set_classes('main-title'),

				Field::make('separator', 'sep_cancel_email', 	__('Email' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media email', 'sejoli')),

				Field::make('text', 	'cancel_email_title',	 __('Judul' ,'sejoli'))
					->set_required(true)
					->set_default_value(__('{{buyer-name}}, Order #{{invoice-id}} {{product-name}} telah kami batalkan', 'sejoli')),

				Field::make('rich_text', 'cancel_email_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('order-cancel-customer')),

				Field::make('separator'	,'sep_cancel_sms', 	__('SMS' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli')),

				Field::make('textarea', 'cancel_sms_content', __('Konten', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('order-cancel-customer', 'sms'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via sms', 'sejoli')),

				Field::make('separator'	,'sep_cancel_whatsapp', 	__('WhatsApp' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli')),

				Field::make('textarea', 'cancel_whatsapp_content', __('Konten', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('order-cancel-customer', 'whatsapp'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via whatsapp', 'sejoli')),

				// Untuk admin
				Field::make('separator', 	'sep_cancel_admin', 		__('Konten untuk admin dan lainnya','sejoli'))
					->set_classes('main-title'),
				Field::make('checkbox',		'cancel_admin_active',			__('Aktifkan notifikasi untuk admin', 'sejoli')),

				Field::make('separator',	'sep_cancel_admin_email', 	__('Email' ,'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'cancel_admin_email_recipient', __('Alamat Email Penerima', 'sejoli'))
					->set_default_value(get_option('admin_email'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('text', 		'cancel_admin_email_title',	 __('Judul' ,'sejoli'))
					->set_required(true)
					->set_default_value(__('Order #{{invoice-id}} {{product-name}} baru dari {{buyer-name}}', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('rich_text', 'cancel_admin_email_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('order-new-admin'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('separator'	,'sep_cancel_admin_sms', 	__('SMS' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'cancel_admin_sms_recipient', __('Nomor SMS Penerima', 'sejoli'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('textarea', 'cancel_admin_sms_content', __('Konten', 'sejoli'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via sms', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('order-new-admin', 'sms'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('separator'	,'sep_cancel_admin_whatsapp', 	__('WhatsApp' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'cancel_admin_whatsapp_recipient', __('Nomor WhatsApp Penerima', 'sejoli'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
							'value' => true,
						]
					]),

				Field::make('textarea', 'cancel_admin_whatsapp_content', __('Konten', 'sejoli'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via whatsapp', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('order-new-admin', 'whatsapp'))
					->set_conditional_logic([
						[
							'field'	=> 'cancel_admin_active',
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

		$this->set_recipient_title  ('buyer', 'email', sejolisa_carbon_get_theme_option('cancel_email_title'));
		$this->set_recipient_content('buyer', 'email', $this->set_notification_content(
															carbon_get_theme_option('cancel_email_content'),
															'email',
                                                            'buyer'
														));

		if(!empty(sejolisa_carbon_get_theme_option('cancel_whatsapp_content'))) :

            $this->set_enable_send      ('whatsapp', 'buyer', true);
			$this->set_recipient_content('buyer', 'whatsapp', $this->set_notification_content(
				                                                sejolisa_carbon_get_theme_option('cancel_whatsapp_content'),
				                                                'whatsapp',
                                                                'buyer'
                                                              ));
        endif;

		if(!empty(sejolisa_carbon_get_theme_option('cancel_sms_content'))) :
            $this->set_enable_send      ('sms', 'buyer', true);
			$this->set_recipient_content('buyer', 'sms', $this->set_notification_content(
                                            				carbon_get_theme_option('cancel_sms_content'),
                                            				'sms',
                                                            'buyer'
                                            			));
        endif;

		// ***********************
		// Setup content for buyer
		// ***********************

		if(false !== sejolisa_carbon_get_theme_option('cancel_admin_active')) :

            $this->set_enable_send('email', 'admin', true);
    		$this->set_recipient_title  ('admin', 'email', sejolisa_carbon_get_theme_option('cancel_admin_email_title'));
    		$this->set_recipient_content('admin', 'email', $this->set_notification_content(
    															carbon_get_theme_option('cancel_admin_email_content'),
    															'email',
                                                                'admin'
    														));

    		if(!empty(sejolisa_carbon_get_theme_option('cancel_admin_whatsapp_content'))) :

				$this->set_enable_send        ('whatsapp', 'admin', true);
				$this->set_recipient_content  ('admin', 'whatsapp', $this->set_notification_content(
                                                    					carbon_get_theme_option('cancel_admin_whatsapp_content'),
                                                    					'whatsapp',
                                                                        'admin'
                                                    				));
            endif;

    		if(!empty(sejolisa_carbon_get_theme_option('cancel_admin_sms_content'))) :
                $this->set_enable_send      ('sms',     'admin', true);
				$this->set_recipient_content('admin',   'sms', $this->set_notification_content(
                            					carbon_get_theme_option('cancel_admin_sms_content'),
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
        $recipients       = sejolisa_carbon_get_theme_option('cancel_' . $role . '_' . $media . '_recipient');
        $this->recipients = explode(',', $recipients);
    }

    /**
     * Trigger to send notification
     * @since   1.0.0
     * @param   array  $order_data   Order data
     * @return  void
     */
    public function trigger(array $order_data) {

        $media_libraries = $this->get_media_libraries();

        $this->prepare($order_data);
        $this->set_content();

        $this->trigger_email($media_libraries['email']);
        $this->trigger_whatsapp($media_libraries['whatsapp']);
        $this->trigger_sms($media_libraries['sms']);

    }

    /**
     * Trigger to send email
     * @since   1.0.0
     * @param   object   $media    Selected media object
     * @return  void
     */
    protected function trigger_email($media) {

        // send email for buyer
		$media->set_data([
			'order_data'     => $this->order_data,
			'product_data'   => $this->product_data,
			'buyer_data'     => $this->buyer_data,
			'affiliate_data' => $this->affiliate_data,
		]);

		$media->send(
			array($this->buyer_data->user_email),
			$this->render_shortcode($this->get_recipient_content('buyer', 'email')),
			$this->render_shortcode($this->get_recipient_title('buyer', 'email'))
		);

        if(false !== $this->is_able_to_send('email', 'admin')) :
            $this->check_recipients('email', 'admin');
            $media->send(
    			$this->recipients,
    			$this->render_shortcode($this->get_recipient_content('admin', 'email')),
    			$this->render_shortcode($this->get_recipient_title('admin', 'email'))
    		);
        endif;
    }

    /**
     * Trigger to send whatsapp
     * @since   1.0.0
     * @param   object   $media    Selected media object
     * @return  void
     */
    protected function trigger_whatsapp($media) {

        $media->set_data([
            'order_data'     => $this->order_data,
            'product_data'   => $this->product_data,
            'buyer_data'     => $this->buyer_data,
            'affiliate_data' => $this->affiliate_data,
        ]);

        // send email for buyer
        if(false !== $this->is_able_to_send('whatsapp', 'buyer')) :

            $media->send(
    			array($this->buyer_data->meta->phone),
    			$this->render_shortcode($this->get_recipient_content('buyer', 'whatsapp'))
    		);

        endif;

        if(false !== $this->is_able_to_send('whatsapp', 'admin')) :
            $this->check_recipients('whatsapp', 'admin');
            $media->send(
    			$this->recipients,
    			$this->render_shortcode($this->get_recipient_content('admin', 'whatsapp'))
    		);
        endif;
    }

    /**
     * Trigger to SMS whatsapp
     * @since   1.0.0
     * @param   object   $media    Selected media object
     * @return  void
     */
    protected function trigger_sms($media) {

        $media->set_data([
			'order_data'     => $this->order_data,
			'product_data'   => $this->product_data,
			'buyer_data'     => $this->buyer_data,
			'affiliate_data' => $this->affiliate_data,
		]);

        if(false !== $this->is_able_to_send('sms', 'buyer')) :
            // send email for buyer

            $media->send(
    			array($this->buyer_data->meta->phone),
    			$this->render_shortcode($this->get_recipient_content('buyer', 'sms'))
    		);

        endif;

        if(false !== $this->is_able_to_send('sms', 'admin')) :

            $this->check_recipients('sms', 'admin');
            $media->send(
                $this->recipients,
                $this->render_shortcode($this->get_recipient_content('admin', 'sms'))
            );

        endif;
    }
}
