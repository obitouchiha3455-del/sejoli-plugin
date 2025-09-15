<?php

namespace SejoliSA\Notification;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Commission extends Main {

    /**
     * Affiliate data
     * @since   1.0.0
     * @var     array
     */
    protected $affiliate;

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
        add_filter('sejoli/notification/fields',    [$this, 'add_setting_fields'], 90);
    }

    /**
     * Add notification setting fields
     * Hooked via filter, sejoli/notification/fields priority 25
     * @since   1.0.0
     * @param   array $fields All fields for notification setting form
     */
    public function add_setting_fields(array $fields) {

        $fields['commission'] = [
			'title'  => __('Komisi Affiliasi', 'sejoli'),
			'fields' => [

                // Untuk buyer
				Field::make( 'html', 'sejoli_commission_shortcode_html', __( 'Shortcode' ) )
					->set_html( '<b>Shortcode</b>: <pre><i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama url halaman member area.', 'sejoli').'">{{memberurl}}</code> <code title="'.__('Shortcode untuk menampilkan informasi akses user.', 'sejoli').'">{{user-access}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nama user.', 'sejoli').'">{{user-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama website.', 'sejoli').'">{{sitename}}</code> <code title="'.__('Shortcode untuk menampilkan url website.', 'sejoli').'">{{siteurl}}</code> <code title="'.__('Shortcode untuk menampilkan ID order.', 'sejoli').'">{{order-id}}</code> <code title="'.__('Shortcode untuk menampilkan nomor invoice.', 'sejoli').'">{{invoice-id}}</code></br></br><code title="'.__('Shortcode untuk menampilkan total order.', 'sejoli').'">{{order-grand-total}}</code><code title="'.__('Shortcode untuk menampilkan nama pembeli.', 'sejoli').'">{{buyer-name}}</code> <code title="'.__('Shortcode untuk menampilkan email pembeli.', 'sejoli').'">{{buyer-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon pembeli.', 'sejoli').'">{{buyer-phone}}</code> <code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli').'">{{product-name}}</code> <code title="'.__('Shortcode untuk menampilkan jumlah produk.', 'sejoli').'">{{quantity}}</code> <code title="'.__('Shortcode untuk menampilkan url halaman konfirmasi pembayaran.', 'sejoli').'">{{confirm-url}}</code></br></br><code title="'.__('Shortcode untuk menampilkan link dokumen attachment konfirmasi pembayaran.', 'sejoli').'">{{confirm-payment-file}}</code> <code title="'.__('Shortcode untuk menampilkan tanggal pembelian.', 'sejoli').'">{{order-day}}</code> <code title="'.__('Shortcode untuk menampilkan masa berakhir pembelian.', 'sejoli').'">{{close-time}}</code> <code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli').'">{{affiliate-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli').'">{{affiliate-phone}}</code></br></br><code title="'.__('Shortcode untuk menampilkan tier affiliasi.', 'sejoli').'">{{affiliate-tier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi komisi.', 'sejoli').'">{{commission}}</code> <code title="'.__('Shortcode untuk menampilkan informasi detail order.', 'sejoli').'">{{order-detail}}</code> <code title="'.__('Shortcode untuk menampilkan informasi meta order.', 'sejoli').'">{{order-meta}}</code> <code title="'.__('Shortcode untuk menampilkan informasi metode pembayaran.', 'sejoli').'">{{payment-gateway}}</code> <code title="'.__('Shortcode untuk menampilkan informasi notifikasi per-produk.', 'sejoli').'">{{product-info}}</code></br></br><code title="'.__('Shortcode untuk menampilkan informasi kurir pengiriman.', 'sejoli').'">{{shipping-courier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nomor resi pengiriman.', 'sejoli').'">{{shipping-number}}</code></i></pre>' ),

				Field::make('separator'	,'sep_commission_email', 	__('Email' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media email', 'sejoli')),
				Field::make('text', 	'commission_email_title',	 __('Judul' ,'sejoli'))
					->set_required(true)
					->set_default_value(__('{{affiliate-name}}, Komisi untuk anda dari order #{{invoice-id}} {{product-name}} ', 'sejoli')),
				Field::make('rich_text', 'commission_email_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('commission-affiliate')),

				Field::make('separator'	,'sep_commission_sms', 	__('SMS' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli')),
				Field::make('textarea', 'commission_sms_content', __('Konten', 'sejoli')),

				Field::make('separator'	,'sep_commission_whatsapp', 	__('WhatsApp' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli')),
				Field::make('textarea', 'commission_whatsapp_content', __('Konten', 'sejoli')),

                // Untuk admin
				Field::make('separator', 	'sep_commission_admin', 		__('Konten untuk admin dan lainnya','sejoli'))
					->set_classes('main-title'),

				Field::make('checkbox',		'commission_admin_active',	__('Aktifkan notifikasi untuk admin', 'sejoli'))
                    ->set_default_value(false),

				Field::make('separator',	'sep_commission_admin_email', 	__('Email' ,'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'commission_admin_email_recipient', __('Alamat Email Penerima', 'sejoli'))
					->set_default_value(get_option('admin_email'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('text', 		'commission_admin_email_title',	 __('Judul' ,'sejoli'))
					->set_required(true)
					->set_default_value(__('Komisi dari order #{{invoice-id}} untuk {{affiliate-name}}', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('rich_text', 'commission_admin_email_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('commission-admin'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('separator'	,'sep_commission_admin_sms', 	__('SMS' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'commission_admin_sms_recipient', __('Nomor SMS Penerima', 'sejoli'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('textarea', 'commission_admin_sms_content', __('Konten', 'sejoli'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via sms', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('order-commission-admin', 'sms'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('separator'	,'sep_commission_admin_whatsapp', 	__('WhatsApp' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('text',		'commission_admin_whatsapp_recipient', __('Nomor WhatsApp Penerima', 'sejoli'))
					->set_help_text(__('Gunakan tanda koma jika penerima ada lebih dari 1', 'sejoli'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
							'value' => true,
						]
					]),

				Field::make('textarea', 'commission_admin_whatsapp_content', __('Konten', 'sejoli'))
					->set_help_text(__('Dengan mengosongkan isian ini, tidak akan ada notifikasi yang dikirimkan via whatsapp', 'sejoli'))
					->set_default_value(sejoli_get_notification_content('order-commission-admin', 'whatsapp'))
					->set_conditional_logic([
						[
							'field'	=> 'commission_admin_active',
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

		$this->content['affiliate']['email']['title']      = sejolisa_carbon_get_theme_option('commission_email_title');
		$this->content['affiliate']['email']['content']    = $this->set_notification_content(
															carbon_get_theme_option('commission_email_content'),
															'email',
                                                            'affiliate'
														);

		if(!empty(sejolisa_carbon_get_theme_option('commission_whatsapp_content'))) :

            $this->set_enable_send('whatsapp', 'affiliate', true);
			$this->content['affiliate']['whatsapp']['content'] = $this->set_notification_content(
				                                                sejolisa_carbon_get_theme_option('commission_whatsapp_content'),
				                                                'whatsapp',
                                                                'affiliate'
			                                                 );
        endif;

		if(!empty(sejolisa_carbon_get_theme_option('commission_sms_content'))) :
            $this->set_enable_send('sms', 'affiliate', true);
			$this->content['affiliate']['sms']['content']     = $this->set_notification_content(
				sejolisa_carbon_get_theme_option('commission_sms_content'),
				'sms',
                'affiliate'
			);
        endif;

		// ***********************
		// Setup content for buyer
		// ***********************

		if(false !== sejolisa_carbon_get_theme_option('commission_admin_active')) :

            $this->set_enable_send('email', 'admin', true);
    		$this->content['admin']['email']['title']      = sejolisa_carbon_get_theme_option('commission_admin_email_title');
    		$this->content['admin']['email']['content']    = $this->set_notification_content(
    															carbon_get_theme_option('commission_admin_email_content'),
    															'email',
                                                                'admin'
    														);

    		if(!empty(sejolisa_carbon_get_theme_option('commission_admin_whatsapp_content'))) :

				$this->set_enable_send('whatsapp', 'admin', true);
				$this->content['admin']['whatsapp']['content'] = $this->set_notification_content(
					carbon_get_theme_option('commission_admin_whatsapp_content'),
					'whatsapp',
                    'admin'
				);
            endif;

    		if(!empty(sejolisa_carbon_get_theme_option('commission_admin_sms_content'))) :
                $this->set_enable_send('sms', 'admin', true);
				$this->content['admin']['sms']['content']      = $this->set_notification_content(
					carbon_get_theme_option('commission_admin_sms_content'),
					'sms',
                    'admin'
				);
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
        $recipients       = sejolisa_carbon_get_theme_option('commission_' . $role . '_' . $media . '_recipient');
        $this->recipients = explode(',', $recipients);
    }

    /**
     * Set commission data
     * @param array $commission
     */
    protected function set_affiliate_data($order_data, $commission) {
        $affiliate = sejolisa_get_user($commission['affiliate_id']);

        $order_data['affiliate_data'] = (object) array(
            'ID'           => $affiliate->ID,
            'display_name' => $affiliate->display_name,
            'phone'        => $affiliate->meta->phone,
            'user_email'   => $affiliate->user_email,
            'tier'         => $commission['tier'],
            'commission'   => sejolisa_price_format($commission['commission'])
        );

        return $order_data;

    }

    /**
     * Trigger to send notification
     * @since   1.0.0
     * @param   array   $commission
     * @param   array   $order_data   Order data
     * @return  void
     */
    public function trigger(array $commission, array $order_data) {

        $media_libraries = $this->get_media_libraries();
        $order_data =  $this->set_affiliate_data($order_data, $commission);
        $this->prepare($order_data);

        $this->set_content();

        // send email for buyer
		$media_libraries['email']->set_data([
			'order_data'     => $this->order_data,
			'product_data'   => $this->product_data,
			'buyer_data'     => $this->buyer_data,
			'affiliate_data' => $this->affiliate_data,
		]);

		$media_libraries['email']->send(
			array($this->affiliate_data->user_email),
			$this->render_shortcode($this->content['affiliate']['email']['content']),
			$this->render_shortcode($this->content['affiliate']['email']['title']),
            'affiliate'
		);

		$media_libraries['whatsapp']->set_data([
			'order_data'     => $this->order_data,
			'product_data'   => $this->product_data,
			'buyer_data'     => $this->buyer_data,
			'affiliate_data' => $this->affiliate_data,
		]);

        $media_libraries['whatsapp']->send(
            array($this->affiliate_data->phone),
            $this->render_shortcode($this->content['affiliate']['whatsapp']['content'])
        );

        $media_libraries['whatsapp']->send(
            array($this->buyer_data->meta->phone),
            $this->render_shortcode($this->content['buyer']['whatsapp']['content'])
        );

        if(false !== $this->is_able_to_send('email', 'admin')) :
            $this->check_recipients('email', 'admin');
            $media_libraries['email']->send(
    			$this->recipients,
    			$this->render_shortcode($this->content['admin']['email']['content']),
    			$this->render_shortcode($this->content['admin']['email']['title']),
                'admin'
    		);
        endif;
    }
}
