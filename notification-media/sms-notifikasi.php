<?php

namespace SejoliSA\NotificationMedia;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SMSNotifikasi extends SMS {

    /**
     * User key for sms-notifikasi.com
     * @since   1.0.0
     * @var     string
     */
    protected $user_key    = '';

    /**
     * Pass key for sms-notifikasi.com
     * @since   1.0.0
     * @var     string
     */
    protected $pass_key    = '';

    /**
     * Request URL for sms-notifikasi.com
     * @since   1.0.0
     * @var     string
     */
    protected $request_url = '';

    /**
     * Construction
     */
    public function __construct() {
        add_filter('sejoli/sms/setup-fields', [$this, 'setup_fields'], 1);
    }

    /**
     * Get name of service
     * @return string
     */
    public function get_label() {
        return 'sms-notifikasi.com';
    }

    /**
     * Add setup fields to sms fields
     * Hooked via filter sejoli/sms/setup-fields, priority 1
     * @since   1.0.0
     * @param   array  $fields
     * @return  array
     */
    public function setup_fields(array $fields) {

        $setup_fields = [
            Field::make('separator', 'sp_sms-notifikasi', 'SMS-notifikasi.com')
                ->set_conditional_logic([
                    [
                        'field' => 'notification_sms_service',
                        'value' => 'sms-notifikasi'
                    ]
                ]),

            Field::make('text', 'sms-notifikasi_user_key',  __('Userkey', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'notification_sms_service',
                        'value' => 'sms-notifikasi'
                    ]
                ]),

            Field::make('text', 'sms-notifikasi_pass_key',  __('Passkey', 'sejoli'))
                ->set_attribute('type', 'password')
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'notification_sms_service',
                        'value' => 'sms-notifikasi'
                    ]
                ]),

            Field::make('select', 'sms-notifikasi_type', __('Tipe SMS', 'sejoli'))
                ->set_required(true)
                ->set_options([
                    'one-way'   => 'One Way',
                    'two-way'   => 'Two Way',
                    'masking'   => 'SMS Masking'
                ])
                ->set_help_text(__('Disesuaikan dengan paket pembelian di sms-notifikasi.com', 'sejoli'))
                ->set_conditional_logic([
                    [
                        'field' => 'notification_sms_service',
                        'value' => 'sms-notifikasi'
                    ]
                ]),

            Field::make('text', 'sms-notifikasi_subdomain', __('Subdomain TWO WAY', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'notification_sms_service',
                        'value' => 'sms-notifikasi'
                    ],[

                        'field' => 'sms-notifikasi_type',
                        'value' => 'two-way'
                    ]
                ]),
        ];

        return array_merge($fields, $setup_fields);
    }

    /**
     * Setup any data for request sms-notifikasi.com
     * @since   1.0.0
     * @return  void
     */
    protected function prepare_setup() {
        $this->user_key = sejolisa_carbon_get_theme_option('sms-notifikasi_user_key');
        $this->pass_key = sejolisa_carbon_get_theme_option('sms-notifikasi_pass_key');
        $package        = sejolisa_carbon_get_theme_option('sms-notifikasi_type');

        switch($package) :

            case 'one-way' :
                $this->request_url = 'http://reguler.sms-notifikasi.com/apps/smsapi.php';
                break;

            case 'masking'  :
                $this->request_url = 'http://masking.sms-notifikasi.com/apps/smsapi.php';
                break;

            case 'two-way'  :
                $subdomain = sejolisa_carbon_get_theme_option('sms-notifikasi_subdomain');
                $this->request_url = 'http://' . $subdomain . '.sms-notifikasi.com/apps/smsapi.php';
                break;

        endswitch;
    }

    /**
     * Send content
     * @since   1.0.0
     * @param   array  $recipients
     * @param   string $content
     * @param   string $title
     * @return  void
     */
    public function send(array $recipients, $content, $title = '') {

        $this->prepare_setup();

        foreach($recipients as $recipient) :

            $phone_number = apply_filters('sejoli/user/phone', $recipient);

            if(empty($phone_number)) :
                continue;
            endif;

            do_action('sejoli/log/write', 'prepare sms-notifikasi', ['phone_number' => $phone_number, 'content' => $content]);

            $request_url = add_query_arg([
                'userkey' => $this->user_key,
                'passkey' => $this->pass_key,
                'nohp'    => $phone_number,
                'pesan'   => $content
            ],$this->request_url);

            $response = wp_remote_get($request_url);

            do_action('sejoli/log/write', 'response sms-notifikasi', (array) wp_remote_retrieve_body($response));

        endforeach;
    }
}
