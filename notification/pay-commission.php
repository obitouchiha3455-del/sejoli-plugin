<?php

namespace SejoliSA\Notification;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class PayCommission extends Main {

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
     * Commission data
     * @since   1.1.0
     * @var     array
     */
    protected $commission_data = array();

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
        add_filter('sejoli/notification/fields',    [$this, 'add_setting_fields'], 100);
    }

    /**
     * Add notification setting fields
     * Hooked via filter, sejoli/notification/fields priority 25
     * @since   1.0.0
     * @param   array $fields All fields for notification setting form
     */
    public function add_setting_fields(array $fields) {

        $fields['pay-commission'] = [
			'title'  => __('Pembayaran Komisi', 'sejoli'),
			'fields' => [

                // Untuk buyer
				Field::make( 'html', 'sejoli_pay_shortcode_html', __( 'Shortcode' ) )
                    ->set_html( '<b>Shortcode</b>: <pre><i><code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama url halaman member area.', 'sejoli').'">{{memberurl}}</code> <code title="'.__('Shortcode untuk menampilkan informasi akses user.', 'sejoli').'">{{user-access}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nama user.', 'sejoli').'">{{user-name}}</code> <code title="'.__('Shortcode untuk menampilkan nama website.', 'sejoli').'">{{sitename}}</code> <code title="'.__('Shortcode untuk menampilkan url website.', 'sejoli').'">{{siteurl}}</code> <code title="'.__('Shortcode untuk menampilkan ID order.', 'sejoli').'">{{order-id}}</code> <code title="'.__('Shortcode untuk menampilkan nomor invoice.', 'sejoli').'">{{invoice-id}}</code></br></br><code title="'.__('Shortcode untuk menampilkan total order.', 'sejoli').'">{{order-grand-total}}</code><code title="'.__('Shortcode untuk menampilkan nama pembeli.', 'sejoli').'">{{buyer-name}}</code> <code title="'.__('Shortcode untuk menampilkan email pembeli.', 'sejoli').'">{{buyer-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon pembeli.', 'sejoli').'">{{buyer-phone}}</code> <code title="'.__('Shortcode untuk menampilkan nama produk.', 'sejoli').'">{{product-name}}</code> <code title="'.__('Shortcode untuk menampilkan jumlah produk.', 'sejoli').'">{{quantity}}</code> <code title="'.__('Shortcode untuk menampilkan url halaman konfirmasi pembayaran.', 'sejoli').'">{{confirm-url}}</code></br></br><code title="'.__('Shortcode untuk menampilkan link dokumen attachment konfirmasi pembayaran.', 'sejoli').'">{{confirm-payment-file}}</code> <code title="'.__('Shortcode untuk menampilkan tanggal pembelian.', 'sejoli').'">{{order-day}}</code> <code title="'.__('Shortcode untuk menampilkan masa berakhir pembelian.', 'sejoli').'">{{close-time}}</code> <code title="'.__('Shortcode untuk menampilkan nama affiliasi.', 'sejoli').'">{{affiliate-name}}</code> <code title="'.__('Shortcode untuk menampilkan email affiliasi.', 'sejoli').'">{{affiliate-email}}</code> <code title="'.__('Shortcode untuk menampilkan no. telepon affiliasi.', 'sejoli').'">{{affiliate-phone}}</code></br></br><code title="'.__('Shortcode untuk menampilkan tier affiliasi.', 'sejoli').'">{{affiliate-tier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi komisi.', 'sejoli').'">{{commission}}</code> <code title="'.__('Shortcode untuk menampilkan informasi detail order.', 'sejoli').'">{{order-detail}}</code> <code title="'.__('Shortcode untuk menampilkan informasi meta order.', 'sejoli').'">{{order-meta}}</code> <code title="'.__('Shortcode untuk menampilkan informasi metode pembayaran.', 'sejoli').'">{{payment-gateway}}</code> <code title="'.__('Shortcode untuk menampilkan informasi notifikasi per-produk.', 'sejoli').'">{{product-info}}</code></br></br><code title="'.__('Shortcode untuk menampilkan informasi kurir pengiriman.', 'sejoli').'">{{shipping-courier}}</code> <code title="'.__('Shortcode untuk menampilkan informasi nomor resi pengiriman.', 'sejoli').'">{{shipping-number}}</code></i></pre>' ),

                Field::make('separator'	,'sep_pay_commission_email', 	__('Email' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media email', 'sejoli')),

				Field::make('text', 	'pay_commission_email_title',	 __('Judul' ,'sejoli'))
					->set_required(true)
					->set_default_value(__('{{affiliate-name}}, Komisi untuk anda sudah dibayarkan ', 'sejoli')),

				Field::make('rich_text', 'pay_commission_email_content', __('Konten', 'sejoli'))
					->set_required(true)
					->set_default_value(sejoli_get_notification_content('pay-affiliate-commission')),

				Field::make('separator'	,'sep_pay_commission_sms', 	__('SMS' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media sms', 'sejoli')),

				Field::make('textarea', 'pay_commission_sms_content', __('Konten', 'sejoli')),

				Field::make('separator'	,'sep_pay_commission_whatsapp', 	__('WhatsApp' ,'sejoli'))
					->set_help_text(__('Pengaturan konten untuk media whatsapp', 'sejoli')),

				Field::make('textarea', 'pay_commission_whatsapp_content', __('Konten', 'sejoli')),

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

		$this->content['affiliate']['email']['title']      = sejolisa_carbon_get_theme_option('pay_commission_email_title');
		$this->content['affiliate']['email']['content']    = $this->set_notification_content(
    															carbon_get_theme_option('pay_commission_email_content'),
    															'email',
                                                                'affiliate'
														    );

		if(!empty(sejolisa_carbon_get_theme_option('pay_commission_whatsapp_content'))) :

            $this->set_enable_send('whatsapp', 'affiliate', true);
			$this->content['affiliate']['whatsapp']['content'] = $this->set_notification_content(
				                                                sejolisa_carbon_get_theme_option('pay_commission_whatsapp_content'),
				                                                'whatsapp',
                                                                'affiliate'
			                                                 );
        endif;

		if(!empty(sejolisa_carbon_get_theme_option('pay_commission_sms_content'))) :
            $this->set_enable_send('sms', 'affiliate', true);
			$this->content['affiliate']['sms']['content']     = $this->set_notification_content(
				carbon_get_theme_option('pay_commission_sms_content'),
				'sms',
                'affiliate'
			);
        endif;

    }

    /**
     * Render shortcode, overwrite parent class
     * @since   1.1.0
     * @param   string  $content
     * @return  string
     */
    public function render_shortcode($content) {

        foreach( $this->commission_data as $key => $value) :
            if('attachments' !== $key) :
                $content = safe_str_replace("{{".$key."}}", $value, $content);
            endif;
        endforeach;

        return $content;
    }

    /**
     * Trigger to send notification
     * @since   1.0.0
     * @param   array   $commission
     * @return  void
     */
    public function trigger(array $commission) {

        $media_libraries       = $this->get_media_libraries();
        $this->commission_data = $commission;

        $this->set_content();

		$media_libraries['email']->send(
			array($commission['affiliate-email']),
			$this->render_shortcode($this->content['affiliate']['email']['content']),
			$this->render_shortcode($this->content['affiliate']['email']['title']),
            'affiliate',
            $commission['attachments']
		);

        if(!empty(sejolisa_carbon_get_theme_option('pay_commission_whatsapp_content'))) :

            $media_libraries['whatsapp']->send(
                array($commission['affiliate-phone']),
                $this->render_shortcode($this->content['affiliate']['whatsapp']['content'])
            );

        endif;

        if(!empty(sejolisa_carbon_get_theme_option('pay_commission_sms_content'))) :

            $media_libraries['sms']->send(
                array($commission['affiliate-phone']),
                $this->render_shortcode($this->content['buyer']['sms']['content'])
            );

        endif;
    }
}
