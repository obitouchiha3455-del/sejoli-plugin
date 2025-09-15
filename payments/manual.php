<?php

namespace SejoliSA\Payment;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Illuminate\Database\Capsule\Manager as Capsule;

final class Manual extends \SejoliSA\Payment{

    /**
     * Table name
     * @since 1.0.0
     * @var string
     */
    protected $table = 'sejolisa_manual_transaction';

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
     * Construction
     */
    public function __construct() {

        global $wpdb;

        $this->id          = 'manual';
        $this->name        = __('Transaksi Manual', 'sejoli');
        $this->title       = __('Transaksi Manual', 'sejoli');
        $this->description = __('Transaksi manual tidak akan divalidasi secara otomatis.', 'sejoli');
        $this->table       = $wpdb->prefix . $this->table;

        add_action('admin_init',        [$this, 'register_transaction_table'], 1);
        add_action('sejoli/order/new',  [$this, 'save_unique_code'], 999);
        add_filter('sejoli/payment/payment-options', [$this, 'add_payment_options'] );

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
                $table->string('bank');
                $table->float('total', 12, 2);
                $table->integer('unique_code');
                $table->text('meta_data');
            });
        endif;
    }

    /**
     * Return setup field
     * @return array
     */
    public function get_setup_fields() {
        return [
            Field::make('separator', 'sep_manual_tranaction_setting',   __('Pengaturan Transaksi Manual', 'sejoli')),

            Field::make('checkbox', 'manual_transaction_active', __('Aktifkan metode transaksi ini', 'sejoli'))
                ->set_option_value('yes')
                ->set_default_value(true),

            Field::make('text',     'manual_transaction_unique_code', __('Maksimal kode unik', 'sejoli'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', 1)
                ->set_attribute('max', 999)
                ->set_default_value(999)
                ->set_required(true)
                ->set_width(50),

            Field::make('select',   'manual_transaction_operation', __('Pengoperasian kode unik', 'sejoli'))
                ->set_width(50)
                ->set_options([
                    ''        => __('Tidak ada pengoperasian', 'sejoli'),
                    'added'   => __('Total nilai belanja ditambahkan kode unik', 'sejoli'),
                    'reduced' => __('Total nilai belanja dikurangi kode unik', 'sejoli')
                ])
                ->set_default_value('added'),

            Field::make('complex',  'manual_transaction_info', __('Informasi nomor rekening', 'sejoli'))
                ->add_fields([
                    Field::make('select',   'bank',     __('Bank', 'sejoli'))
                        ->set_options([
                            'BCA'     => 'Bank BCA',
                            'MANDIRI' => 'Bank Mandiri',
                            'BRI'     => 'Bank BRI',
                            'BNI'     => 'Bank BNI',
                            'other'   => 'Bank Lainnya'
                        ])
                        ->set_required(true),
                    Field::make('text',    'bank_name',     __('Nama Bank', 'sejoli'))
                        ->set_required(true)
                        ->set_conditional_logic([
                            array(
                                'field' => 'bank',
                                'value' => 'other'
                            )
                        ]),
                    Field::make('image',    'logo',     __('Logo Bank', 'sejoli'))
                        ->set_value_type('url')
                        ->set_conditional_logic([
                            [
                                'field' => 'bank',
                                'value' => 'other'
                            ]
                        ]),
                    Field::make('text',     'owner',    __('Nama pemilik rekening', 'sejoli'))
                        ->set_required(true),
                    Field::make('text',     'account',  __('Nomor rekening', 'sejoli'))
                        ->set_required(true),
                    Field::make('textarea', 'info',     __('Informasi lainnya', 'sejoli'))
                ])
                ->set_layout('tabbed-vertical')
                ->set_header_template('
                    <% if( bank_name ) { %>
                        <%- bank_name %>
                    <% } else if ( bank ) { %>
                        <%- bank %>
                    <% } %>')
        ];
    }

    /**
     * Add payment options if manual transfer active
     * Hooked via filter sejoli/payment/payment-options
     * @since   1.0.0
     * @param   array $options
     * @return  array
     */
    public function add_payment_options($options = array()) {

        $active = boolval( sejolisa_carbon_get_theme_option('manual_transaction_active') );

        if(true === $active) :
            $transaction_info = sejolisa_carbon_get_theme_option('manual_transaction_info');

            foreach( (array) $transaction_info as $i => $_trans ) :

                $bank_key  = ('other' === $_trans['bank']) ? strtoupper(sanitize_title($_trans['bank_name'])) : $_trans['bank'];
                $bank_name = ('other' === $_trans['bank']) ? sprintf(__('Bank %s', 'sejoli'), $_trans['bank_name']) : sprintf(__('Bank %s', 'sejoli'), $_trans['bank']);
                $bank_image = SEJOLISA_URL . 'public/img/' . $_trans['bank'] . '.png';

                if('other' === $_trans['bank']) :
                    $bank_image = $_trans['logo'];
                    if(empty($bank_image)) :
                        $bank_image = 'https://via.placeholder.com/150';
                    endif;
                endif;

                $key = 'manual:::'.$bank_key.'-'.$i;
                $options[$key] = [
                    'label' => $bank_name,
                    'image' => $bank_image
                ];

            endforeach;
        endif;

        return $options;
    }

    /**
     * Check unique code
     */
    protected function check_unique_code() {

        $operation = sejolisa_carbon_get_theme_option('manual_transaction_operation');

        if('' !== $operation) :
            $latest_id = Capsule::table($this->table)
                            ->select('ID')
                            ->latest()
                            ->first();

            $the_latest_id = (is_null($latest_id)) ? 0 : $latest_id->ID;

            $max_unique_code   = floatval(sejolisa_carbon_get_theme_option('manual_transaction_unique_code'));
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

        else :

        endif;
    }

    /**
     * Set order price
     * @param float $price
     * @param array $order_data
     * @return float
     */
    public function set_price(float $price, array $order_data) {

        if(0.0 !== $price ) :

            $disable_wallet    = boolval(sejolisa_carbon_get_post_meta($order_data['product_id'], 'deactivate_wallet'));
            $using_wallet_only = boolval(sejolisa_carbon_get_post_meta($order_data['product_id'], 'buy_using_wallet_only'));

            $this->order_price = $price;
            if(false === $disable_wallet && true === $using_wallet_only) {
                return floatval($this->order_price);
            } else {
                $this->check_unique_code();
            }

            return floatval($this->order_price);

        endif;

        return $price;

    }

    /**
     * Set transaction fee
     * @since 1.0.0
     * @param array $order_data
     * @return string
     */
    public function add_transaction_fee(array $order_data) {

        $operation = sejolisa_carbon_get_theme_option('manual_transaction_operation');

        if('' === $operation)
            return;


        return ('added' === $operation ) ? $this->unique_code : '-'.$this->unique_code;
    }

    /**
     * Save unique code
     * Hooked via action sejoli/order/new, priority 999
     * @param  array  $order_data
     */
    public function save_unique_code(array $order_data) {

        if('manual' == $order_data['payment_gateway'] && !empty($this->unique_code)) :

            Capsule::table($this->table)
                ->insert([
                    'created_at'  => current_time('mysql'),
                    'updated_at'  => '0000-00-00 00:00:00',
                    'order_id'    => $order_data['ID'],
                    'user_id'     => $order_data['user_id'],
                    'bank'        => '',
                    'total'       => $order_data['grand_total'],
                    'unique_code' => $this->unique_code,
                    'meta_data'   => serialize(array())
                ]);
        endif;

    }

    /**
     * Set order meta data
     * @param array $meta_data
     * @param array $order_data
     * @param array $payment_subtype
     * @return array
     */
    public function set_meta_data(array $meta_data, array $order_data, $payment_subtype) {

        $meta_data['manual'] = [
            'unique_code' => $this->unique_code,
            'bank-chosen' => $payment_subtype
        ];

        return $meta_data;
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

        $transaction_info = sejolisa_carbon_get_theme_option('manual_transaction_info');
        $trans_data       = [];

        // list($bank, $iteration) = explode('
        $bank_data        = explode('-', $invoice_data['order_data']['meta_data']['manual']['bank-chosen']);
        $iteration        = end($bank_data);

        foreach($transaction_info as $i => $_trans) :
            if( intval($i) === intval($iteration) ) :
                $trans_data = $_trans;
                if('other' === $_trans['bank']) :
                    $trans_data['bank'] = $_trans['bank_name'];
                endif;
                break;
            endif;
        endforeach;

        $content = '';

        if(isset($trans_data['bank'])) :

            $content .= sejoli_get_notification_content(
                            'payment-bank',
                            $media,
                            array(
                                'payment' => [
                                    'bank'           => $trans_data['bank'],
                                    'account'        => $trans_data['account'],
                                    'account_number' => $trans_data['account'],
                                    'owner'          => $trans_data['owner'],
                                    'info'           => $trans_data['info']
                                ]
                            )
                        );

        endif;

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

        $transaction_info = sejolisa_carbon_get_theme_option('manual_transaction_info');
        $trans_data       = [];
        $bank_data        = explode('-', $invoice_data['order_data']['meta_data']['manual']['bank-chosen']);
        $iteration        = end($bank_data);

        foreach($transaction_info as $i => $_trans) :
            if( intval($i) === intval($iteration) ) :
                $trans_data = $_trans;
                if('other' === $_trans['bank']) :
                    $trans_data['bank'] = $_trans['bank_name'];
                endif;
                break;
            endif;
        endforeach;

        $content = '';

        if(isset($trans_data['bank'])) :

            $content .= sprintf(__("%s no rek %s", "sejoli"), $trans_data['bank'], $trans_data['account'] );

        endif;

        return $content;
    }


    /**
     * Set payment info to order datas
     * @since 1.0.0
     * @param array $order_data
     * @return array
     */
    public function set_payment_info(array $order_data) {

        $trans_data             = [];

        if(isset($order_data['meta_data']['manual'])) :
            $payment_data     = $order_data['meta_data']['manual'];
            $transaction_info = sejolisa_carbon_get_theme_option('manual_transaction_info');
            $bank_data        = explode('-', $payment_data['bank-chosen']);
            $iteration        = end($bank_data);

            foreach($transaction_info as $i => $_trans) :
                if( intval($i) === intval($iteration) ) :
                    $trans_data = $_trans;
                    $trans_data['account_number'] = $_trans['account'];
                    break;
                endif;
            endforeach;
        endif;

        return $trans_data;
    }

    /**
     * Get unique code operational method
     * @since   1.1.6
     * @return  string
     */
    public function get_operational_method() {
        $operation = sejolisa_carbon_get_theme_option('manual_transaction_operation');

        if(in_array($operation, array('added', ''))) :
            return '';
        endif;

        return '-';
    }
}
