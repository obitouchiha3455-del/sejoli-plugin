<?php

namespace SejoliSA\Payment;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Illuminate\Database\Capsule\Manager as Capsule;

final class Moota extends \SejoliSA\Payment{

    /**
     * Table name
     * @since 1.0.0
     * @var string
     */
    protected $table = 'sejolisa_moota_transaction';

    /**
     * Unique code
     * @since 1.0.0
     * @var float
     */
    protected $unique_code = 0.0;

    /**
     * Order price
     * @since 1.0.0
     * @var float
     */
    protected $order_price = 0.0;

    /**
     * Account identification
     * @since   1.0.0
     * @var     false|integer
     */
    protected $account_id = false;

    /**
     * The name of selected bank
     * @since   1.0.0
     * @var     string
     */
    protected $bank_name = false;

    /**
     * Account number
     * @since   1.0.0
     * @var     false|string
     */
    protected $account_number = false;

    /**
     * Mutation Data
     * @since   1.0.0
     * @access  protected
     * @var     false|array
     */
    protected $mutation_data;

    /**
     * Get account from webhook
     * @since   1.0.0
     * @access  protected
     * @var     array
     */
    protected $accounts = false;

    /**
     * Order Data
     * @since   1.0.0
     * @var     false|array
     */
    protected $order_data;

    /**
     * Webhook URL
     * @since   1.0.0
     * @access  protected
     * @var     string
     */
    protected $webhook_url;

    /**
     * Results for moota checking
     * @since   1.0.0
     * @access  protected
     * @var     array
     */
    protected $results = array();

    /**
     * Moota bank lsit
     * @since   1.4.3
     * @var     array
     */
    protected $banks = array(

    );

    /**
     * Construction
     */
    public function __construct() {

        global $wpdb;

        parent::__construct();

        $this->id          = 'moota';
        $this->name        = __('Moota', 'sejoli');
        $this->title       = __('Moota', 'sejoli');
        $this->description = __('Pengecekan mutasi bank', 'sejoli');
        $this->table       = $wpdb->prefix . $this->table;
        $this->webhook_url = add_query_arg([
                                'moota-check'   => true
                              ],home_url('/'));

        add_action('parse_request',                     [$this, 'check_webhook_request'],       1);
        add_action('admin_init',                        [$this, 'register_transaction_table'],  1);
        add_action('wp_ajax_moota-check-connection',    [$this, 'check_available_account'],     1);
        add_action('sejoli/order/new',                  [$this, 'save_unique_code'],            999);
        add_action('admin_footer',                      [$this, 'add_js_script'],               999);
        add_action('sejoli/moota/available-accounts',   [$this, 'get_available_accounts'],      1, 2);
        add_filter('sejoli/payment/payment-options',    [$this, 'add_payment_options'],         1);
    }

    /**
     * Register transaction table
     * @return void
     */
    public function register_transaction_table() {

        if(!Capsule::schema()->hasTable( $this->table )):
            Capsule::schema()->create( $this->table, function($table){
                $table->increments('ID');
                $table->datetime('created_at');
                $table->datetime('updated_at')->default('0000-00-00 00:00:00');
                $table->integer('order_id');
                $table->integer('user_id')->nullable();
                $table->string('account');
                $table->float('total', 12, 2);
                $table->integer('unique_code');
                $table->text('meta_data');
            });
        endif;
    }

    /**
     * Get available accounts
     * Hooked via filter sejoli/moota/availble-accounts, priority 1
     * @since   1.0.0
     * @param   array   $accounts   Array of bank account
     * @param   string  $apikey     Api key value
     * @return  array   Array of bank account
     */
    public function get_available_accounts(array $accounts, $apikey = '') {

        $apikey     = (empty($apikey)) ? sejolisa_carbon_get_theme_option('moota_api_key') : $apikey;

        $response   = wp_remote_get('https://app.moota.co/api/v1/bank', [
            'headers'   => [
                'Authorization' => 'Bearer ' . $apikey
            ]
        ]);

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if(200 === intval($code)) :

            $json = json_decode($body, true);

            if(0 < intval($json['total'])) :
                foreach($json['data'] as $account) :
                    $key = $account['bank_type'] . ':::' . $account['account_number'];
                    $accounts[$key] = strtoupper($account['bank_type']).' '.$account['account_number'];
                endforeach;
            endif;

        endif;

        return $accounts;
    }

    /**
     * Check available account by ajax
     * Hooked via wp_ajax_moota-check-connection
     * @since   1.0.0
     * @return  json
     */
    public function check_available_account() {

        $valid = false;
        $messages = __('Tidak ditemukan rekening atau ada kesalahan koneksi dengan Moota', 'sejoli');
        $args     = wp_parse_args($_GET,[
            'apikey' => NULL
        ]);

        $available_accounts = apply_filters('sejoli/moota/available-accounts', [], $args['apikey']);

        if(is_array($available_accounts) && 0 < count($available_accounts)) :
            $valid    = true;
            $messages = sprintf(__('Ditemukan %s rekening di akun Moota anda', 'sejoli'), count($available_accounts));
            set_transient('sejolisa-moota-account', $available_accounts);
        endif;

        wp_send_json([
            'valid'    => $valid,
            'messages' => $messages,
            'data'     => $available_accounts
        ]);
        exit;
    }

    /**
     * Display payment instruction in notification
     * @param  array    $invoice_data
     * @param  string   $recipient_type
     * @param  string   $media
     * @return string
     */
    public function display_payment_instruction($invoice_data, $media = 'email') {

        if('on-hold' !== $invoice_data['order_data']['status']) :
            return;
        endif;

        $account_number = $invoice_data['order_data']['meta_data']['moota']['account_number'];
        $bank_name      = $invoice_data['order_data']['meta_data']['moota']['bank'];

        $content = sejoli_get_notification_content(
                        'payment-bank',
                        $media,
                        array(
                            'payment' => [
                                'bank'    => strtoupper($bank_name),
                                'account' => $account_number,
                                'owner'   => sejolisa_carbon_get_theme_option('moota_account_owner'),
                                'info'    => NULL
                            ]
                        )
                    );

        return $content;
    }

    /**
     * Display simple payment instruction in notification
     * @param  array    $invoice_data
     * @param  string   $recipient_type
     * @param  string   $media
     * @return string
     */
    public function display_simple_payment_instruction($invoice_data, $media = 'email') {

        if('on-hold' !== $invoice_data['order_data']['status']) :
            return;
        endif;

        $account_number = $invoice_data['order_data']['meta_data']['moota']['account_number'];
        $bank_name      = $invoice_data['order_data']['meta_data']['moota']['bank'];

        $content = '';
        $content .= sprintf(__("%s no rek %s", "sejoli"), $bank_name, $account_number );

        return $content;
    }


    /**
     * Display html content to check connection to Moota
     * @since   1.0.0
     * @access  protected
     * @return  string
     */
    protected function display_html_check() {
        ob_start()
        ?>
        <div class="moota-check">
            <button type="button" name="button" class='moota-connection-check button'><?php _e('Cek Koneksi ke Moota', 'sejoli'); ?></button>
            <div class="moota-check-result sejoli-html-message" style='margin-top:18px;display:none;'>

            </div>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * Add javascript code
     * Hooked via action admin_footer
     * @since   1.0.0
     * @return  void
     */
    public function add_js_script() {
        global $pagenow;

        if('admin.php' === $pagenow && 'crb_carbon_fields_container_sejoli.php' === $_GET['page']) :
        ?>
        <script type="text/javascript">
        (function( $ ) {
            'use strict';
            $(document).on('click', '.moota-connection-check', function(){
                $.ajax({
                    url : '<?php echo admin_url('admin-ajax.php'); ?>',
                    data : {
                        action : 'moota-check-connection',
                        apikey : $("input[name='carbon_fields_compact_input[_moota_api_key]']").val()
                    },
                    dataType : 'json',
                    beforeSend : function() {
                        $('.sejoli-html-message')
                            .removeClass('success error')
                            .addClass('info')
                            .show()
                            .html('<?php echo __('Mengecek koneksi dengan Moota', 'sejoli'); ?>');
                    }, success : function(response) {
                        if(true === response.valid) {
                            $('.sejoli-html-message').addClass('success').html('<p>' + response.messages + '</p>');
                            $.each(response.data, function(i,val){
                                $('.sejoli-html-message').append('<p>Detail Bank : ' + val + '</p>');
                            });

                            $('.sejoli-html-message').append('<p><?php _e('Agar nomor rekening di atas bisa dimunculkan pada pemilihan nomor rekening, tekan tombol <strong>SAVE CHANGES</strong>', 'sejoli'); ?></p>');
                        } else {
                            $('.sejoli-html-message').addClass('error').html('<p>' + response.messages + '</p>');
                        }
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
        endif;
    }

    /**
     * Get available accounts
     * @since   1.0.0
     * @return  array|false
     */
    public function get_accounts() {
        $options = false;
        $available_accounts = get_transient('sejolisa-moota-account');
        return (false !== $available_accounts && is_array($available_accounts)) ? $available_accounts : false;
    }

    /**
     * Add BCA setup fields to general form
     * Hooked via filter sejoli/general/fields, priority 40
     * @return array
     */
    public function get_setup_fields() {

        return [
            Field::make('separator', 'sep_moota_tranaction_setting',    __('Pengaturan Cek Mutasi via Moota', 'sejoli')),

            Field::make('checkbox', 'moota_transaction_active', __('Aktifkan metode transaksi ini', 'sejoli'))
                ->set_option_value('yes')
                ->set_default_value(false)
                ->set_help_text(__('Metode pembayaran ini membutuhkan akses username dan password ke akun Moota', 'sejoli')),

            Field::make('text',     'moota_transaction_unique_code', __('Maksimal kode unik', 'sejoli'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', 1)
                ->set_default_value(999)
                ->set_required(true)
                ->set_width(50)
                ->set_conditional_logic([
                    [
                        'field' => 'moota_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('select',   'moota_transaction_operation', __('Pengoperasian kode unik', 'sejoli'))
                ->set_width(50)
                ->set_options([
                    'added'   => __('Total nilai belanja ditambahkan kode unik', 'sejoli'),
                    'reduced' => __('Total nilai belanja dikurangi kode unik', 'sejoli')
                ])
                ->set_default_value('added')
                ->set_conditional_logic([
                    [
                        'field' => 'moota_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('text',     'moota_api_key', __('API Key', 'sejoli'))
                ->set_required(true)
                ->set_help_text(__('Silahkan copy API Key melalui link: https://app.moota.co/integrations/personal', 'sejoli'))
                ->set_conditional_logic([
                    [
                        'field' => 'moota_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('text',     'moota_account_owner', __('Nama Pemilik Rekening', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'moota_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('multiselect',     'moota_accounts', __('Nomor rekening yang digunakan', 'sejoli'))
                ->set_conditional_logic([
                    [
                        'field' => 'moota_transaction_active',
                        'value' => true
                    ]
                ])
                ->add_options([$this, 'get_accounts'])
                ->set_help_text(__('Untuk bisa mendapatkan nomor rekening yang terdaftar di Moota anda, silahkan lakukan cek koneksi di bawah ini', 'sejoli')),

            Field::make('html',     'moota_check', __('Cek Koneksi ke Moota', 'sejoli'))
                ->set_html($this->display_html_check())
                ->set_conditional_logic([
                    [
                        'field' => 'moota_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('select',   'moota_check_day',    __('Lama hari pengecekan mutasi', 'sejoli'))
                ->set_options($this->day)
                ->set_default_value(7),

            Field::make('separator',  'sep_moota_webhook',  __('Setup Webhook', 'sejoli')),
            Field::make('text', 'moota_webhook_url',    __('Webhook URL', 'sejoli'))
                ->set_default_value($this->webhook_url)
                ->set_attribute('readOnly', true)
                ->set_help_text(__('Salin link di atas ke pengaturan webhook pada moota', 'sejoli')),

        ];
    }

    /**
     * Check unique code
     * @since   1.0.0
     */
    protected function check_unique_code() {

        $operation = sejolisa_carbon_get_theme_option('moota_transaction_operation');

        if('' !== $operation) :
            $latest_id = Capsule::table($this->table)
                            ->select('ID')
                            ->latest()
                            ->first();

            $max_unique_code   = floatval(sejolisa_carbon_get_theme_option('moota_transaction_unique_code'));
            $this->unique_code = 1;

            if(false !== $latest_id) :

                $this->unique_code = (NULL === $latest_id) ? 1 : $latest_id->ID + 1;

                // if latest_id + 1 over max unique code, then back to 1
                while($max_unique_code < $this->unique_code) :
                    $this->unique_code = $this->unique_code - $max_unique_code;
                endwhile;

                if('added' == $operation) :
                    $this->order_price += $this->unique_code;
                else :
                    $this->order_price -= $this->unique_code;
                endif;

            endif;
        endif;
    }

    /**
     * Get actual bank name from moota bank ID
     * @since   1.4.3
     * @since   1.5.3.1 Modify bank list
     * @since   1.5.6   Add several bank ID
     * @param   string  $bank_id    Remember to format it uppercase
     * @return  string
     */
    protected function get_bank_name($bank_id) {

        $bank_name = 'ATM Bersama';

        $bank_list = array(
            'MANDIRI'              => 'Mandiri',
            'MANDIRILIVIN'         => 'MANDIRILIVIN',
            'MANDIRIONLINE'        => 'Mandiri',
            'MANDIRIONLINEV2'      => 'Mandiri',
            'MANDIRIBISNIS'        => 'Mandiri Bisnis',
            'MANDIRIBISNISV2'      => 'Mandiri Bisnis',
            'MANDIRIMCM'           => 'Mandiri',
            'MANDIRIMCM2'          => 'Mandiri',
            'MANDIRIMCMV2'         => 'Mandiri',
            'MANDIRIMCM2V2'        => 'Mandiri',
            'MANDIRIONLINEV2'      => 'Mandiri',
            'MANDIRISYARIAH'       => 'Mandiri Syariah',
            'MANDIRISYARIAHBISNIS' => 'Mandiri Syariah',
            'MANDIRISYARIAHMCM'    => 'Mandiri Syariah',
            'BCA'                  => 'BCA',
            'BCASYARIAH'           => 'BCA Syariah',
            'BCASYARIAHV2'         => 'BCA Syariah',
            'BCAGIRO'              => 'BCA Giro',
            'BNI'                  => 'BNI',
            'BNIV2'                => 'BNI',
            'BNISYARIAH'           => 'BNI Syariah',
            'BNISYARIAHV2'         => 'BNI Syariah',
            'BNIBISNIS'            => 'BNI',
            'BNIBISNIV2'           => 'BNI',
            'BNIBISNISSYARIAH'     => 'BNI Syariah',
            'BNIBISNISSYARIAHV2'   => 'BNI Syariah',
            'BRI'                  => 'BRI',
            'BRIGIRO'              => 'BRI Giro',
            'BRICMS'               => 'BRI',
            'BRICMSV2'             => 'BRI',
            'BRISYARIAH'           => 'BRI Syariah',
            'BRISYARIAHCMS'        => 'BRI Syariah',
            'BRISYARIAHCMSV2'      => 'BRI Syariah',
            'IBBIZBRI'             => 'IBBIZ BRI',
            'BTN'                  => 'BTN',
            'BTNV2'                => 'BTN',
            'BTNSYARIAH'           => 'BTN Syariah',
            'BTNSYARIAHV2'         => 'BTN Syariah',
            'MUAMALAT'             => 'Muamalat',
            'MUAMALATV2'           => 'Muamalat',
            'GOJEK'                => 'Gopay Gojek',
            'OVO'                  => 'OVO',
            'BSI'                  => 'Bank Syariah Indonesia',
            'BSIGIRO'              => 'BSI Giro',
            'JAGO'                 => 'JAGO',
            'JAGOSYARIAH'          => 'JAGO Syariah'
        );

        if(isset($bank_list[$bank_id])) :
            $bank_name = $bank_list[$bank_id];
        endif;

        return $bank_name;

    }

    /**
     * Get actual bank logo from moota bank ID
     * @since   1.4.3
     * @since   1.5.3.1     Modify bank list
     * @since   1.5.6       Add several bank ID
     * @param   string      $bank_id    Remember to format it uppercase
     * @return  string
     */
    protected function get_bank_image($bank_id) {

        $bank_image = 'ATM'; // default bank image;

        $bank_list = array(
            'MANDIRI'              => 'MANDIRI',
            'MANDIRILIVIN'         => 'MANDIRILIVIN',
            'MANDIRIONLINE'        => 'MANDIRI',
            'MANDIRIONLINEV2'      => 'MANDIRI',
            'MANDIRIBISNIS'        => 'MANDIRI',
            'MANDIRIBISNISV2'      => 'MANDIRI',
            'MANDIRIMCM'           => 'MANDIRI',
            'MANDIRIMCM2'          => 'MANDIRI',
            'MANDIRIMCMV2'         => 'MANDIRI',
            'MANDIRIMCM2V2'        => 'MANDIRI',
            'MANDIRIONLINEV2'      => 'MANDIRI',
            'MANDIRISYARIAH'       => 'MANDIRISYARIAH',
            'MANDIRISYARIAHBISNIS' => 'MANDIRISYARIAH',
            'MANDIRISYARIAHMCM'    => 'MANDIRISYARIAH',
            'BCA'                  => 'BCA',
            'BCASYARIAH'           => 'BCASYARIAH',
            'BCASYARIAHV2'         => 'BCASYARIAH',
            'BCAGIRO'              => 'BCA',
            'BNI'                  => 'BNI',
            'BNIV2'                => 'BNI',
            'BNISYARIAH'           => 'BNISYARIAH',
            'BNISYARIAHV2'         => 'BNISYARIAH',
            'BNIBISNIS'            => 'BNI',
            'BNIBISNIV2'           => 'BNI',
            'BNIBISNISSYARIAH'     => 'BNISYARIAH',
            'BNIBISNISSYARIAHV2'   => 'BNISYARIAH',
            'BRI'                  => 'BRI',
            'BRIGIRO'              => 'BRI',
            'BRICMS'               => 'BRI',
            'BRICMSV2'             => 'BRI',
            'BRISYARIAH'           => 'BRISYARIAH',
            'BRISYARIAHCMS'        => 'BRISYARIAH',
            'IBBIZBRI'             => 'IBBIZBRI',
            'BTN'                  => 'BTN',
            'BTNV2'                => 'BTN',
            'BTNSYARIAH'           => 'BTNSYARIAH',
            'BTNSYARIAHV2'         => 'BTNSYARIAH',
            'MUAMALAT'             => 'MUAMALAT',
            'MUAMALATV2'           => 'MUAMALAT',
            'GOJEK'                => 'GOJEK',
            'OVO'                  => 'OVO',
            'BSI'                  => 'BSI',
            'BSIGIRO'              => 'BSI',
            'MEGASYARIAHCMS'       => 'MEGASYARIAH',
            'MEGASYARIAH'          => 'MEGASYARIAH',
            'MEGASYARIAHV2'        => 'MEGASYARIAH'
        );

        if(isset($bank_list[$bank_id]) && file_exists(SEJOLISA_DIR . 'public/img/' . $bank_list[$bank_id] . '.png')) :
            $bank_image = $bank_list[$bank_id];
        endif;

        return $bank_image;
    }

    /**
     * Add payment options if moota transfer active
     * Hooked via filter sejoli/payment/payment-options
     * @since   1.0.0
     * @since   1.4.3   Add get_bank_name() and get_bank_image()
     * @param   array $options
     * @return  array
     */
    public function add_payment_options($options = array()) {

        $active = boolval( sejolisa_carbon_get_theme_option('moota_transaction_active') );

        if(true === $active) :
            $transaction_info = sejolisa_carbon_get_theme_option('moota_accounts');

            foreach( (array) $transaction_info as $i => $_trans ) :

                list($bank, $account) = explode(':::', $_trans);
                $bank_name            = $this->get_bank_name(strtoupper($bank));
                $bank_image           = $this->get_bank_image(strtoupper($bank));
                $key                  = 'moota:::'.$bank.'-'.$i;

                $options[$key] = [
                    'label' => sprintf(__('Bank %s', 'sejoli'), $bank_name),
                    'image' => SEJOLISA_URL . 'public/img/' . $bank_image . '.png'
                ];

            endforeach;
        endif;

        return $options;
    }

    /**
     * Set transaction fee
     * @since 1.0.0
     * @param array $order_data
     * @return string
     */
    public function add_transaction_fee(array $order_data) {

        $operation = sejolisa_carbon_get_theme_option('moota_transaction_operation');

        if('' === $operation)
            return;


        return ('added' === $operation ) ? $this->unique_code : '-'.$this->unique_code;
    }

    /**
     * Set order price
     * @param float $price
     * @param array $order_data
     * @return float
     */
    public function set_price(float $price, array $order_data) {

        if(0.0 !== $price ) :

            $this->order_price = $price;
            $this->check_unique_code();

            return floatval($this->order_price);
        endif;

        return $price;
    }

    /**
     * Set order meta data
     * @param array $meta_data
     * @param array $order_data
     * @param array $payment_subtype
     * @return array
     */
    public function set_meta_data(array $meta_data, array $order_data, $payment_subtype) {

        if(!empty($this->unique_code)) :

            list($payment,$bank)       = explode(':::', $order_data['payment_gateway']);
            list($bank_name, $bank_id) = explode('-', $bank);

            $accounts = sejolisa_carbon_get_theme_option('moota_accounts');
            $accounts = (isset($accounts[$bank_id])) ? $accounts[$bank_id] : $accounts[0];

            $this->account_id = $bank_id;
            list($this->bank_name, $this->account_number) = explode(':::', $accounts);

            $meta_data['moota'] = [
                'unique_code'    => $this->unique_code,
                'account_id'     => $this->account_id,
                'bank'           => $this->bank_name,
                'account_number' => $this->account_number,
            ];

        endif;

        return $meta_data;
    }

    /**
     * Save unique code
     * Hooked via action sejoli/order/new, priority 999
     * @param  array  $order_data
     */
    public function save_unique_code(array $order_data) {

        if('moota' == $order_data['payment_gateway'] && !empty($this->unique_code)) :

            Capsule::table($this->table)
                ->insert([
                    'created_at'  => current_time('mysql'),
                    'updated_at'  => '0000-00-00 00:00:00',
                    'order_id'    => $order_data['ID'],
                    'user_id'     => $order_data['user_id'],
                    'account'     => strtoupper( $this->bank_name . ':::' . $this->account_number),
                    'total'       => $order_data['grand_total'],
                    'unique_code' => $this->unique_code,
                    'meta_data'   => serialize([
                        'account_id'     => $this->account_id,
                        'bank'           => $this->bank_name,
                        'account_number' => $this->account_number
                    ])
                ]);
        endif;

    }

    /**
     * Set payment info to order datas
     * @since 1.0.0
     * @param array $order_data
     * @return array
     */
    public function set_payment_info(array $order_data) {

        $trans_data             = [];

        if(isset($order_data['meta_data']['moota'])) :
            $payment_data           = $order_data['meta_data']['moota'];
            $trans_data = [
                'bank'           => sprintf( __('Bank %s', 'sejoli'), $this->get_bank_name(strtoupper($payment_data['bank']))),
                'logo'           => SEJOLISA_URL . 'public/img/' . $this->get_bank_image(strtoupper($payment_data['bank'])) . '.png',
                'owner'          => sejolisa_carbon_get_theme_option('moota_account_owner'),
                'account_number' => $payment_data['account_number']
            ];
        endif;

        return $trans_data;
    }

    /**
     * Get orders that uses BCA payment gateway with status on-hold
     * @since   1.0.0
     * @return  void
     */
    protected function render_order() {

        global $wpdb;

        $table_order = $wpdb->prefix.'sejolisa_orders';
        $day         = intval(sejolisa_carbon_get_theme_option('moota_check_day'));

        $this->order_data = Capsule::table($this->table.' AS log')
                    ->select(Capsule::Raw('log.*, data_order.status '))
                    ->join(Capsule::Raw($table_order.' AS data_order'), 'log.order_id', '=', 'data_order.ID')
                    ->whereIn('data_order.status', ['on-hold', 'payment-confirm'])
                    ->where('log.created_at', '>', date('Y-m-d', strtotime('-' . $day . ' day')))
                    ->get();
    }

    /**
     * Check request from moota webhook
     * Hooked via action parse_request, priority 1
     * @return  void
     */
    public function check_webhook_request() {

        if(isset($_GET['moota-check']) && false !== boolval($_GET['moota-check'])) :

            $notifications = json_decode( file_get_contents("php://input") );

            if(!is_array($notifications)) :
                $notifications = json_decode( $notifications );
            endif;

            do_action('sejoli/log/write', 'moota-webhook-access', [
                'input' => $notifications
            ]);

            foreach((array) $notifications as $_notif) :

                if('CR' !== $_notif->type) :
                    continue;
                endif;

                $this->mutation_data[] = intval($_notif->amount);

            endforeach;

            $this->check_mutation();

            do_action('sejoli/log/write', 'moota-order', $this->results);

            echo json_encode($this->results);

            exit;
        endif;
    }

    /**
     * Check bank mutation
     * Hooked via action sejoli/moota/check-mutation
     * @since   1.0.0
     * @return  void
     */
    public function check_mutation() {

        $this->render_order();

        /**
         * Compare between mutation and order data
         */
        if($this->order_data && is_array($this->mutation_data)) :

            foreach( $this->order_data as $_order ) :

                $total = intval($_order->total);

                if(in_array($total, $this->mutation_data)) :

                    $this->update_order_status($_order->order_id);
                    $this->results[]    = sprintf(__('Order #%s completed', 'sejoli'), $_order->order_id);

                endif;

            endforeach;

        endif;
    }

    /**
     * Get unique code operational method
     * @since   1.1.6
     * @return  string
     */
    public function get_operational_method() {
        $operation = sejolisa_carbon_get_theme_option('bca_transaction_operation');

        if('added' === $operation) :
            return '';
        endif;

        return '-';
    }
}
