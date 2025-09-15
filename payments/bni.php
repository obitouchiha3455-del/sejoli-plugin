<?php

namespace SejoliSA\Payment;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Illuminate\Database\Capsule\Manager as Capsule;

final class BNI extends \SejoliSA\Payment{

    /**
     * Table name
     * @since 1.0.0
     * @var string
     */
    protected $table = 'sejolisa_bni_transaction';

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
     * Account number
     * @since   1.0.0
     * @var     false|string
     */
    protected $account_number = false;

    /**
     * Mutation Data
     * @since   1.0.0
     * @var     false|array
     */
    protected $mutation_data;

    /**
     * Order Data
     * @since   1.0.0
     * @var     false|array
     */
    protected $order_data;

    /**
     * Mutation checking class
     * @var [type]
     */
    private $mutation;

    /**
     * Construction
     */
    public function __construct() {

        global $wpdb;

        parent::__construct();

        $this->id          = 'bni';
        $this->name        = __('BNI Auto Check Mutasi', 'sejoli');
        $this->title       = __('BNI Internet Banking', 'sejoli');
        $this->description = __('Pengecekan mutasi ke BNI Internet Banking. Invoice akan diaktifkan secara otomatis jika nilai pembayaran sesuai tepat dengan nilai order.', 'sejoli');
        $this->table       = $wpdb->prefix . $this->table;

        require_once(plugin_dir_path(__FILE__) . 'mutation/bni.php');

        $this->mutation = new \Mutasi\Bank\BNI;

        add_action('admin_init',                    [$this, 'register_transaction_table'],  1);
        add_action('admin_init',                    [$this, 'check_bni_mutation'],          1);
        add_action('wp_ajax_bni-check-connection',  [$this, 'check_available_account'],     1);
        add_action('sejoli/order/new',              [$this, 'save_unique_code'],            999);
        add_action('admin_footer',                  [$this, 'add_js_script'],               999);
        add_action('sejoli/bni/check-mutation',     [$this, 'check_mutation'],              1);
        add_filter('sejoli/payment/payment-options', [$this, 'add_payment_options'] );


		if(false === wp_next_scheduled('sejoli/bni/check-mutation')) :

			wp_schedule_event(time() + 60,'fourth_hourly','sejoli/bni/check-mutation');

		else :

			$recurring 	= wp_get_schedule('sejoli/bni/check-mutation');

			if('fourth_hourly' !== $recurring) :
				wp_reschedule_event(time() + 60, 'fourth_hourly', 'sejoli/bni/check-mutation');
			endif;
		endif;

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
     * Check available account by ajax
     * Hooked via wp_ajax_bni-check-connection
     * @since   1.0.0
     * @return  json
     */
    public function check_available_account() {

        $args = wp_parse_args($_GET,[
            'username' => NULL,
            'password' => NULL,
            'account'  => NULL
        ]);

        $response = $this->mutation->set_username($args['username'])
                        ->set_password($args['password'])
                        ->set_account($args['account'])
                        ->check_mutasi()
                        ->respond();

        wp_send_json($response);
        exit;
    }

    /**
     * Display html content to check connection to BNI Internet Banking
     * @since   1.0.0
     * @access  protected
     * @return  string
     */
    protected function display_html_check() {
        ob_start()
        ?>
        <div class="bni-check">
            <button type="button" name="button" class='bni-connection-check button'><?php _e('Cek Koneksi ke BNI Internet Banking', 'sejoli'); ?></button>
            <div class="bni-check-result sejoli-html-message" style='margin-top:18px;display:none;'>

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
            $(document).on('click', '.bni-connection-check', function(){
                $.ajax({
                    url : '<?php echo admin_url('admin-ajax.php'); ?>',
                    data : {
                        action : 'bni-check-connection',
                        username : $("input[name='carbon_fields_compact_input[_bni_bniib_username]']").val(),
                        password : $("input[name='carbon_fields_compact_input[_bni_bniib_password]']").val(),
                    },
                    dataType : 'json',
                    beforeSend : function() {
                        $('.sejoli-html-message')
                            .removeClass('success error')
                            .addClass('info')
                            .show()
                            .html('<?php echo __('Mengecek koneksi dengan BNI Internet Banking', 'sejoli'); ?>');
                    }, success : function(response) {
                        if(true === response.valid) {
                            $('.sejoli-html-message').addClass('success').html('<p>' + response.messages + '</p>');
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
     * Add BNI setup fields to general form
     * Hooked via filter sejoli/general/fields, priority 40
     * @return array
     */
    public function get_setup_fields() {

        return [
            Field::make('separator', 'sep_bni_transaction_setting',	__('Pengaturan Cek Mutasi BNI', 'sejoli')),

            Field::make('html', 'sp_bni_transaction_info', 'Informasi')
                ->set_html(
                    '<div class="sejoli-html-message info">
                        '.__('<p>Kami tidak menjamin sistem pengecekan mutasi ini berjalan normal 100% karena banyak faktor yang menyebabkan sistem tidak berjalan selain dari script.</p>', 'sejoli').'
                    </div>'
                ),

            Field::make('checkbox', 'bni_transaction_active', __('Aktifkan metode transaksi ini', 'sejoli'))
                ->set_option_value('yes')
                ->set_default_value(false)
                ->set_help_text(__('Metode pembayaran ini membutuhkan akses username dan password ke BNI Internet Banking personal anda', 'sejoli')),

            Field::make('text',     'bni_transaction_unique_code', __('Maksimal kode unik', 'sejoli'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', 1)
                ->set_attribute('max', 999)
                ->set_default_value(999)
                ->set_required(true)
                ->set_width(50)
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('select',   'bni_transaction_operation', __('Pengoperasian kode unik', 'sejoli'))
                ->set_width(50)
                ->set_options([
                    'added'   => __('Total nilai belanja ditambahkan kode unik', 'sejoli'),
                    'reduced' => __('Total nilai belanja dikurangi kode unik', 'sejoli')
                ])
                ->set_default_value('added')
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('text',     'bni_bniib_username', __('Username BNI Internet Banking', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),
            Field::make('text',     'bni_bniib_password', __('Password BNI Internet Banking', 'sejoli'))
                ->set_required(true)
                ->set_attribute('type', 'password')
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),
            Field::make('text',     'bni_bniib_account', __('Nomor rekening BNI internet banking anda', 'sejoli'))
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('html',     'bni_bniib_check', __('Cek Koneksi ke BNI Internet Banking', 'sejoli'))
                ->set_html($this->display_html_check())
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('text',     'bni_account_owner', __('Nama pemilik rekening', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('text',     'bni_account_branch', __('Cabang BNI', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),

            Field::make('select',   'bni_check_day',    __('Lama hari pengecekan mutasi', 'sejoli'))
                ->set_options($this->day)
                ->set_default_value(7)
                ->set_conditional_logic([
                    [
                        'field' => 'bni_transaction_active',
                        'value' => true
                    ]
                ]),
        ];
    }

    /**
     * Add payment options if BNI internet banking active
     * Hooked via filter sejoli/payment/payment-options
     * @since   1.0.0
     * @param   array $options
     * @return  array
     */
    public function add_payment_options($options = array()) {

        $active = boolval( sejolisa_carbon_get_theme_option('bni_transaction_active') );

        if(true === $active) :
            $options['bni'] = [
                'label' => 'Bank BNI',
                'image' => SEJOLISA_URL . 'public/img/BNI.png'
            ];
        endif;

        return $options;
    }

    /**
     * Check unique code
     * @since   1.0.0
     */
    protected function check_unique_code() {

        $operation = sejolisa_carbon_get_theme_option('bni_transaction_operation');

        if('' !== $operation) :
            $latest_id = Capsule::table($this->table)
                            ->select('ID')
                            ->latest()
                            ->first();

            $max_unique_code   = floatval(sejolisa_carbon_get_theme_option('bni_transaction_unique_code'));
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
     * Set order meta data
     * @param array $meta_data
     * @param array $order_data
     * @param array $payment_subtype
     * @return array
     */
    public function set_meta_data(array $meta_data, array $order_data, $payment_subtype) {

        if(!empty($this->unique_code)) :

            $meta_data['bni'] = [
                'unique_code'    => $this->unique_code
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

        if('bni' == $order_data['payment_gateway'] && !empty($this->unique_code)) :

            Capsule::table($this->table)
                ->insert([
                    'created_at'  => current_time('mysql'),
                    'updated_at'  => '0000-00-00 00:00:00',
                    'order_id'    => $order_data['ID'],
                    'user_id'     => $order_data['user_id'],
                    'account'     => 0,
                    'total'       => $order_data['grand_total'],
                    'unique_code' => $this->unique_code,
                    'meta_data'   => serialize(array())
                ]);
        endif;

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

        $account_number = sejolisa_carbon_get_theme_option('bni_bniib_account');
        $account_owner  = sejolisa_carbon_get_theme_option('bni_account_owner');
        $account_branch = sejolisa_carbon_get_theme_option('bni_account_branch');

        $content = '';

        $content .= sejoli_get_notification_content(
                        'payment-bank',
                        $media,
                        array(
                            'payment' => [
                                'bank'    => 'BNI',
                                'account' => $account_number,
                                'owner'   => $account_owner,
                                'info'    => $account_branch
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

        $account_number = sejolisa_carbon_get_theme_option('bni_bniib_account');
        $account_owner  = sejolisa_carbon_get_theme_option('bni_account_owner');
        $account_branch = sejolisa_carbon_get_theme_option('bni_account_branch');

        $content = '';

        $content .= sprintf(__("%s no rek %s", "sejoli"), 'BNI', $account_number );

        return $content;
    }

    /**
     * Set payment info to order datas
     * @since 1.0.0
     * @param array $order_data
     * @return array
     */
    public function set_payment_info(array $order_data) {

        $account_number = sejolisa_carbon_get_theme_option('bni_bniib_account');
        $account_owner  = sejolisa_carbon_get_theme_option('bni_account_owner');
        $account_branch = sejolisa_carbon_get_theme_option('bni_account_branch');

        return [
            'bank'    => 'BNI',
            'owner'   => $account_owner,
            'account' => $account_number,
            'account_number' => $account_number,
            'info'    => $account_branch
        ];
    }

    /**
     * Render mutation data
     * @since   1.0.0
     * @return  void
     */
    protected function render_mutation() {

        $response = $this->mutation->set_username(sejolisa_carbon_get_theme_option('bni_bniib_username'))
                        ->set_password(sejolisa_carbon_get_theme_option('bni_bniib_password'))
                        ->set_account(sejolisa_carbon_get_theme_option('bni_bniib_account'))
                        ->set_type('kredit')
                        ->check_mutasi()
                        ->respond();

        if(false !== $response['valid']) :

            set_transient('sejoli-bni-last-check-account', $account_id);

            foreach( (array) $response['data'] as $_mutation) :

                if(!isset($this->mutation_data[$_mutation['nominal']])) :
                    $this->mutation_data[$_mutation['nominal']] = [];
                endif;

                $this->mutation_data[$_mutation['nominal']][] = $_mutation;

            endforeach;

            ksort($this->mutation_data);
        endif;
    }

    /**
     * Get orders that uses BNI payment gateway with status on-hold
     * @since   1.0.0
     * @return  void
     */
    protected function render_order() {

        global $wpdb;

        $table_order = $wpdb->prefix.'sejolisa_orders';
        $day         = intval(sejolisa_carbon_get_theme_option('bni_check_day'));

        $this->order_data = Capsule::table($this->table.' AS log')
                    ->select(Capsule::Raw('log.*, data_order.status '))
                    ->join(Capsule::Raw($table_order.' AS data_order'), 'log.order_id', '=', 'data_order.ID')
                    ->whereIn('data_order.status', ['on-hold', 'payment-confirm'])
                    ->where('log.created_at', '>', date('Y-m-d', strtotime('-' . $bni . ' day')))
                    ->get();
    }

    /**
     * Check bank mutation
     * Hooked via action sejoli/bni/check-mutation
     * @since   1.0.0
     * @return  void
     */
    public function check_mutation() {

        $active = boolval(sejolisa_carbon_get_theme_option('bni_transaction_active'));

        if(false === $active) :
            return;
        endif;

        $accounts = $this->get_available_accounts();

        if(!is_array($accounts)) :
            return;
        endif;

        $this->render_mutation();
        $this->render_order();

        /**
         * Compare between mutation and order data
         * @var [type]
         */
        if($this->order_data && is_array($this->mutation_data)) :

            foreach( $this->order_data as $_order ) :

                $price      = intval($_order->total);

                if(isset($this->mutation_data[$price])) :
                    $this->update_order_status($_order->order_id);
                endif;
            endforeach;

        endif;
    }

    /**
     * Check bank mutation on admin page
     * Hooked via action admin_init
     * @since   1.0.0
     * @return  void
     */
    public function check_bni_mutation() {

        if(is_admin() && isset($_GET['sejoli-bank']) && 'bni' === $_GET['sejoli-bank']):

            $this->render_mutation();
            $this->render_order();

            if($this->order_data && is_array($this->mutation_data)) :
                $order_data    = $this->order_data;
                $mutation_data = $this->mutation_data;
                require_once( plugin_dir_path( __FILE__ ) . '/mutation/view-table.php');
            endif;
            exit;
        endif;
    }

    /**
     * Get unique code operational method
     * @since   1.1.6
     * @return  string
     */
    public function get_operational_method() {
        $operation = sejolisa_carbon_get_theme_option('bni_transaction_operation');

        if('added' === $operation) :
            return '';
        endif;

        return '-';
    }
}
