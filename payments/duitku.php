<?php

namespace SejoliSA\Payment;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Illuminate\Database\Capsule\Manager as Capsule;

final class Duitku extends \SejoliSA\Payment{

    /**
     * Unique code
     * @since 1.3.0
     * @var float
     */
    protected $duitku_fee = 0.0;

    /**
     * Surcharge
     * @since 1.3.0
     * @var float
     */
    protected $surcharge_fee = 0.0;

    /**
     * Order price
     * @since 1.3.0
     * @var float
     */
    protected $order_price = 0.0;

    /**
     * Method options
     * @since   1.3.0
     * @var     array
     */
    protected $method_options = array();

    /**
     * Check if request is already called
     * @since   1.5.1.1
     * @var     boolean
     */
    protected $is_called = false;

    /**
     * Request urls
     * @since   1.3.0
     * @var     array
     */
    public $request_url = array(
        'sandbox' => 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry',
        'live'    => 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'
    );

    /**
     * Callback message status from duitku
     * @since   1.3.0
     * @var     array
     */
    protected $callback_status = array(
        "00"    => "success",
        "01"    => "failed"
    );

    /**
     * Return message status from duitku
     * @since   1.3.0
     * @var     array
     */
    protected $return_status = array(
        "00"    => "success",
        "01"    => "pending",
        "02"    => "cancelled"
    );

    /**
     * Table name
     * @since 1.0.0
     * @var string
     */
    protected $table = 'sejolisa_duitku_transaction';

    /**
     * Construction
     * @since   1.5.1.1     Remove Mandiri Clickpay, CIMBClickpay and danamon virtual pay
     *                      Add Indodana, shopeepay, shopeepay app, bank artha graha and bank sahabat sampoerna
     *
     * @since   1.5.6       Update payment method
     */
    public function __construct() {

        global $wpdb;

        $this->id             = 'duitku';
        $this->name           = __('Duitku', 'sejoli');
        $this->title          = __('Duitku', 'sejoli');
        $this->description    = __('Transaksi menggunakan duitku payment gateway.', 'sejoli');
        $this->table          = $wpdb->prefix . $this->table;
        $this->method_options = array(
            'VC'    => __('Credit Card (Visa / Master Card / JCB)', 'sejoli'),
            'BK'    => __('BCA KlikPay', 'sejoli'),
            'BC'    => __('BCA Virtual Account', 'sejoli'),
            // 'M1'    => __('Mandiri Virtual Account', 'sejoli'),
            'M2'    => __('Mandiri Virtual Account', 'sejoli'),
            'BT'    => __('Permata Bank Virtual Account', 'sejoli'),
            'A1'    => __('ATM Bersama', 'sejoli'),
            'B1'    => __('CIMB Niaga Virtual Account', 'sejoli'),
            'I1'    => __('BNI Virtual Account', 'sejoli'),
            'BR'    => __('BRI Virtual Acount', 'sejoli'),
            'BV'    => __('BSI Virtual Acount', 'sejoli'),
            'VA'    => __('Maybank Virtual Account', 'sejoli'),
            'NC'    => __('BNC Virtual Account', 'sejoli'),
            'FT'    => __('Ritel (Alfamart, Pegadaian, POS Indonesia)', 'sejoli'),
            'IR'    => __('Indomaret', 'sejoli'),
            'OV'    => __('OVO', 'sejoli'),
            'DN'    => __('Indodana Paylater', 'sejoli'),
            'SL'    => __('Shopee Pay Link', 'sejoli'),
            'SA'    => __('Shopee Pay Apps', 'sejoli'),
            'SP'    => __('Shopee Pay QRIS', 'sejoli'),
            'DA'    => __('DANA', 'sejoli'),
            'S1'    => __('Bank Sahabat Sampoerna', 'sejoli'),
            'AG'    => __('Bank Artha Graha', 'sejoli'),
            'LA'    => __('LinkAja Apps (Percentage Fee)', 'sejoli'),
            'LF'    => __('LinkAja Apps (Fixed Fee)', 'sejoli'),
            'LQ'    => __('LinkAja QRIS', 'sejoli'),
            'NQ'    => __('Nobu QRIS', 'sejoli')
        );

        add_action('admin_init',                     [$this, 'register_transaction_table'],  1);
        add_filter('sejoli/payment/payment-options', [$this, 'add_payment_options'] );
        add_filter('query_vars',                     [$this, 'set_query_vars'],     999);
        add_action('sejoli/thank-you/render',        [$this, 'check_for_redirect'], 1);
        add_action('init',                           [$this, 'set_endpoint'],       1);
        add_action('parse_query',                    [$this, 'check_parse_query'],  100);
        add_filter('sejoli/recalculate/grand-total', [$this, 'recalculate_grand_total'], 100, 2);
        add_filter('sejoli/recalculate/notif-grand-total', [$this, 'recalculate_notif_grand_total'], 100, 2);
        add_filter('sejoli/payment/data',            [$this, 'get_payment_data'], 100, 2);
        
        if(false === wp_next_scheduled('sejoli/duitku/check-mutation')) :

            wp_schedule_event(time(),'fourth_hourly','sejoli/duitku/check-mutation');

        else :

            $recurring  = wp_get_schedule('sejoli/duitku/check-mutation');

            if('fourth_hourly' !== $recurring) :
                wp_reschedule_event(time(), 'fourth_hourly', 'sejoli/duitku/check-mutation');
            endif;

        endif;

    }

    /**
     * Register transaction table
     * Hooked via action admin_init, priority 1
     * @since   1.3.0
     * @return  void
     */
    public function register_transaction_table() {

        if(!Capsule::schema()->hasTable( $this->table )):

            Capsule::schema()->create( $this->table, function($table){
                $table->increments('ID');
                $table->datetime('created_at');
                $table->datetime('last_check')->default('0000-00-00 00:00:00');
                $table->integer('order_id');
                $table->string('status');
                $table->text('detail')->nullable();
            });

        endif;

    }

    /**
     * Get duitku order data
     * @since   1.3.0
     * @param   int              $order_id
     * @return  false|object
     */
    protected function check_data_table(int $order_id) {

        return Capsule::table($this->table)
            ->where(array(
                'order_id'  => $order_id
            ))
            ->first();

    }

    /**
     * Add transaction data
     * @since   1.3.0
     * @param   integer $order_id   Order ID
     * @return  void
     */
    protected function add_to_table(int $order_id) {

        Capsule::table($this->table)
            ->insert([
                'created_at' => current_time('mysql'),
                'last_check' => '0000-00-00 00:00:00',
                'order_id'   => $order_id,
                'status'     => 'pending'
            ]);
    }

    /**
     * Update data status
     * @since   1.3.0
     * @param  integer  $order_id [description]
     * @param  string   $status   [description]
     * @return void
     */
    protected function update_status($order_id, $status) {
        Capsule::table($this->table)
            ->where(array(
                'order_id'  => $order_id
            ))
            ->update(array(
                'status'    => $status,
                'last_check'=> current_time('mysql')
            ));
    }

    /**
     * Update data detail
     * @since   1.3.0
     * @param   integer $order_id [description]
     * @param   array $detail   [description]
     * @return  void
     */
    protected function update_detail($order_id, $detail) {
        Capsule::table($this->table)
            ->where(array(
                'order_id'  => $order_id
            ))
            ->update(array(
                'detail'    => serialize($detail),
            ));
    }

    /**
     *  Set end point custom menu
     *  Hooked via action init, priority 999
     *  @since 1.3.0
     *  @access public
     *  @return void
     */
    public function set_endpoint()
    {
        add_rewrite_rule( '^duitku/([^/]*)/?',      'index.php?duitku-method=1&action=$matches[1]','top');

        flush_rewrite_rules();
    }

    /**
     * Set custom query vars
     * Hooked via filter query_vars, priority 999
     * @since   1.3.0
     * @access  public
     * @param   array $vars
     * @return  array
     */
    public function set_query_vars($vars)
    {
        $vars[] = 'duitku-method';

        return $vars;
    }

    /**
     * Check parse query and if duitku-method exists and process
     * Hooked via action parse_query, priority 999
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function check_parse_query() {

        global $wp_query;

        if(is_admin() || $this->is_called ) :
            return;
        endif;

        if(
            isset($wp_query->query_vars['duitku-method']) &&
            isset($wp_query->query_vars['action']) && !empty($wp_query->query_vars['action'])
        ) :

            if('process' === $wp_query->query_vars['action']) :
                $this->is_called = true;
                $this->process_callback();

            elseif('return' === $wp_query->query_vars['action']) :
                $this->is_called = true;
                $this->receive_return();
            endif;

        endif;
    }

    /**
     * Return setup field
     * @return array
     */
    public function get_setup_fields() {

        return [
            Field::make('separator', 'sep_duitku_tranaction_setting',   __('Pengaturan Duitku', 'sejoli')),

            Field::make('checkbox', 'duitku_transaction_active', __('Aktifkan metode transaksi ini', 'sejoli'))
                ->set_option_value('yes')
                ->set_default_value(false),

            Field::make('select',   'duitku_mode',  __('Mode Duitku', 'sejoli'))
                ->set_help_text(__('Gunakan mode sandbox jika anda ingin melakukan uji coba', 'sejoli'))
                ->set_options(array(
                    'sandbox'   => 'Sandbox',
                    'live'      => 'Live'
                ))
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    )
                )),

            Field::make('text', 'duitku_sandbox_merchant_code',    __('Merchant Code (Sandbox)', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    ),array(
                        'field' => 'duitku_mode',
                        'value' => 'sandbox'
                    )
                )),

            Field::make('text', 'duitku_sandbox_api_key',    __('Project API Key (Sandbox)', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    ),array(
                        'field' => 'duitku_mode',
                        'value' => 'sandbox'
                    )
                )),

            Field::make('text', 'duitku_live_merchant_code',    __('Merchant Code', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    ),array(
                        'field' => 'duitku_mode',
                        'value' => 'live'
                    )
                )),

            Field::make('text', 'duitku_live_api_key',    __('Project API Key', 'sejoli'))
                ->set_required(true)
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    ),array(
                        'field' => 'duitku_mode',
                        'value' => 'live'
                    )
                )),

            Field::make('separator', 'sep_duitku_callback', __('Callback URL', 'sejoli'))
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    )
                )),

            Field::make('html',  'duitku_url_callback',    __('Callback URL', 'sejoli'))
                ->set_html(
                    'Copy callback URL berikut ke setup project duitku.com anda : <br />'.
                    '<strong>'. site_url('/duitku/process') . '</strong>'
                )
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    )
                )),

            Field::make('separator', 'sep_duitku_payment_method',   __('Pilih metode pembayaran', 'sejoli'))
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    )
                )),

            Field::make('set', 'duitku_payment_method', __('Metode pembayaran', 'sejoli'))
                ->set_required(true)
                ->set_options($this->method_options)
                ->set_help_text(
                    __('Wajib memilih minimal satu metode pembayaran dan PASTIKAN metode tersebut sudah aktif di pengaturan project duitku.com', 'sejoli') . '<br />' .
                    __('HARAP DIBACA! Khusus OVO HARUS ada kontak lagi dengan pihak duitku.com. Karena membutuhkan dokumen lebih lanjut', 'sejoli')
                )
                ->set_conditional_logic(array(
                    array(
                        'field' => 'duitku_transaction_active',
                        'value' => true
                    )
                ))
        ];
    }

    /**
     * Check unique code
     * @since   1.0.0
     */
    protected function set_surcharge_fee() {

        extract($this->get_setup_values());

        $active = boolval( carbon_get_theme_option('duitku_transaction_active') );

        if( true === $active ) :

            $mode = carbon_get_theme_option('duitku_mode');
            if ( $mode === 'live' ) :
            
                $request_url = 'https://passport.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';
                $baseAppUrl  = 'https://passport.duitku.com/merchant/';
            
            else:
           
                $request_url = 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';
                $baseAppUrl  = 'https://sandbox.duitku.com/merchant/';
            
            endif;

            $merchantCode  = $merchant_code; 
            $apiKey        = $api_key;
            $datetime      = date('Y-m-d H:i:s');  
            $paymentAmount = $this->order_price;
            $signature     = hash( 'sha256', $merchantCode . $paymentAmount . $datetime . $apiKey );

            $params = array(
                'merchantcode' => $merchantCode,
                'amount'       => $paymentAmount,
                'datetime'     => $datetime,
                'signature'    => $signature
            );

            $params_string = json_encode( $params );

            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, $request_url ); 
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );                                                                     
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $params_string );                                                                  
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );                                                                      
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($params_string))                                                                       
            );   
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );

            //execute post
            $request  = curl_exec( $ch );
            $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

            if( $httpCode == 200 ) :

                $results = json_decode( $request, true );

                foreach( $results['paymentFee'] as $channel ) :

                    // MUST PUT ::: after payment ID
                    $key = 'duitku:::' . $channel['paymentMethod'];
                    $fee = 0;

                    if( $_REQUEST['payment_gateway'] === $key ) :

                        if( $channel['totalFee'] === 0 ) :
                            
                            $fee = 0;
                            
                            $this->surcharge_fee = $fee;

                        else:

                            $fee = $channel['totalFee'];

                            $this->surcharge_fee = $fee;

                        endif;

                    endif;

                endforeach;

            else:

                $request = json_decode($request);
                $error_message = "Server Error " . $httpCode ." ". $request->Message;
                do_action('sejoli/log/write', 'error-duitku', $error_message);
                wp_die(
                    $error_message,
                    __('Terjadi kesalahan')
                );

            endif;

        endif;

    }

    /**
     * Add payment options if duitku transfer active
     * Hooked via filter sejoli/payment/payment-options
     * @since   1.3.0
     * @since   1.5.1   Remove mandiri clickpay, CIMBclick and danamon VA
     *                  Add Indodana, shopee, bank artha graha and sahabat sampoerna
     * @since   1.5.6   Update several payment method
     * @param   array $options
     * @return  array
     */
    public function add_payment_options($options = array()) {

        $active = boolval( carbon_get_theme_option('duitku_transaction_active') );

        if(true === $active) :

            $methods = carbon_get_theme_option('duitku_payment_method');

            foreach((array) $methods as $_method) :

                $key = 'duitku:::'.$_method;

                switch($_method) :

                    // Credit card
                    case 'VC' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/CC.png'
                        ];
                        break;

                    // BCA Klikpay
                    case 'BK' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/BCAKLIKPAY.png'
                        ];
                        break;

                    // BCA Klikpay
                    case 'BC' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/BCA.png'
                        ];
                        break;

                    // Mandiri
                    case 'M1' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/MANDIRIONLINE.png'
                        ];
                        break;
                    case 'M2' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/MANDIRIONLINE.png'
                        ];
                        break;

                    // Permata bank
                    case 'BT' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/PERMATA.png'
                        ];
                        break;

                    // CIMB
                    case 'B1' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/CIMB.png'
                        ];
                        break;

                    // ATM Bersama
                    case 'A1' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/ATM.png'
                        ];
                        break;

                    // BNI
                    case 'I1' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/BNI.png'
                        ];
                        break;

                    // BSI
                    case 'BV' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/BSI.png'
                        ];
                        break;

                    // BII Maybank
                    case 'VA' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/MAYBANK.jpg'
                        ];
                        break;

                    // BNC
                    case 'NC' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/NC.png'
                        ];
                        break;

                    // Retail
                    case 'FT' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/RETAIL.png'
                        ];
                        break;

                    // Ovo
                    case 'OV' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/OVO.png'
                        ];
                        break;

                    // Indodana
                    case 'DN' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/INDODANA.png'
                        ];
                        break;

                    // Shopee pay
                    case 'SL' :
                    case 'SP' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/qris.png'
                        ];
                        break;

                    // Shopee pay Apps
                    case 'SA' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/SHOPEEPAYAPP.png'
                        ];
                        break;

                        // Bank Sahabat Sampoerna
                        case 'S1' :
                            $options[$key] = [
                                'label' => $this->method_options[$_method],
                                'image' => SEJOLISA_URL . 'public/img/SAHABATSAMPOERNA.png'
                            ];
                            break;

                    // Bank Artha Graha
                    case 'AG' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/ARTHAGRAHA.png'
                        ];
                        break;

                    // Bank Sahabat Sampoerna
                    case 'S1' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/SAHABATSAMPOERNA.png'
                        ];
                        break;

                    // LinkAja
                    case 'LA' :
                    case 'LF' :
                    case 'LQ' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/qris.png'
                        ];
                        break;

                    // BRI VA
                    case 'BR' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/BR.png'
                        ];
                        break;

                    // DANA
                    case 'DA' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/DA.png'
                        ];
                        break;

                    // INDOMARET
                    case 'IR' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/IR.png'
                        ];
                        break;

                    case 'NQ' :
                        $options[$key] = [
                            'label' => $this->method_options[$_method],
                            'image' => SEJOLISA_URL . 'public/img/qris.png'
                        ];
                        break;

                endswitch;

            endforeach;

        endif;

        return $options;

    }

    /**
     * Check unique code
     * @since   1.0.0
     */
    protected function check_duitku_fee() {

        $operation = sejolisa_carbon_get_theme_option('moota_transaction_operation');

        $this->set_surcharge_fee();

        $this->duitku_fee = $this->surcharge_fee;
        $this->order_price += $this->duitku_fee;
            
    }

    /**
     * Set order price
     * @since   1.3.0
     * @param float $price
     * @param array $order_data
     * @return float
     */
    public function set_price( float $price, array $order_data ) {

        if( 0.0 !== $price ) :

            $this->order_price = $price;
            $this->check_duitku_fee();

            return floatval( $this->order_price );

        endif;

        return $price;

    }

    /**
     * Get payment channel duitku
     * @since   1.5.3.3
     * @param   integer     $product_id
     * @param   integer     $user_id
     * @return  array
     */
    public function sejolisa_get_payment_channel_duitku( $method_options ) {

        switch ($method_options) {
            case 'VC':
                $payment_channel = __('Credit Card (Visa / Master Card / JCB)', 'sejoli');
                break;
            case 'BK':
                $payment_channel = __('BCA KlikPay', 'sejoli');
                break;
            case 'BC':
                $payment_channel = __('BCA Virtual Account', 'sejoli');
                break;
            case 'M2':
                $payment_channel = __('Mandiri Virtual Account', 'sejoli');
                break;
            case 'BT':
                $payment_channel = __('Permata Bank Virtual Account', 'sejoli');
                break;
            case 'A1':
                $payment_channel = __('ATM Bersama', 'sejoli');
                break;
            case 'B1':
                $payment_channel = __('CIMB Niaga Virtual Account', 'sejoli');
                break;
            case 'I1':
                $payment_channel = __('BNI Virtual Account', 'sejoli');
                break;
            case 'BR':
                $payment_channel = __('BRI Virtual Acount', 'sejoli');
                break;
            case 'VA':
                $payment_channel = __('Maybank Virtual Account', 'sejoli');
                break;
            case 'NC':
                $payment_channel = __('BNC Virtual Account', 'sejoli');
                break;
            case 'FT':
                $payment_channel = __('Ritel (Alfamart, Pegadaian, POS Indonesia)', 'sejoli');
                break;
            case 'IR':
                $payment_channel = __('Indomaret', 'sejoli');
                break;
            case 'OV':
                $payment_channel = __('OVO', 'sejoli');
                break;
            case 'DN':
                $payment_channel = __('Indodana Paylater', 'sejoli');
                break;
            case 'SL':
                $payment_channel = __('Shopee Pay Link', 'sejoli');
                break;
            case 'SA':
                $payment_channel = __('Shopee Pay Apps', 'sejoli');
                break;
            case 'SP':
                $payment_channel = __('Shopee Pay QRIS', 'sejoli');
                break;
            case 'DA':
                $payment_channel = __('DANA', 'sejoli');
                break;
            case 'S1':
                $payment_channel = __('Bank Sahabat Sampoerna', 'sejoli');
                break;
            case 'AG':
                $payment_channel = __('Bank Artha Graha', 'sejoli');
                break;
            case 'LA':
                $payment_channel = __('LinkAja Apps (Percentage Fee)', 'sejoli');
                break;
            case 'LF':
                $payment_channel = __('LinkAja Apps (Fixed Fee)', 'sejoli');
                break;
            case 'LQ':
                $payment_channel = __('LinkAja QRIS', 'sejoli');
                break;
            case 'NQ':
                $payment_channel = __('Nobu QRIS', 'sejoli');
                break;
            default:
                $payment_channel = __('ATM Bersama', 'sejoli');
                break;
        }

        return $payment_channel;

    }

    /**
     * Recalculate grand total
     * Hooked via filter sejoli/recalculate/grand-total, priority 100
     * @param array $order_data
     */
    public function recalculate_grand_total($grand_total, array $order_data) {

        if(isset($order_data['meta_data']['duitku'])) :

            $order_data['grand_total'] -= floatval($order_data['meta_data']['duitku']['duitku_fee']);

            $grand_total = $order_data['grand_total'];

        endif;

        return $grand_total;

    }

    /**
     * Recalculate grand total on notification
     * Hooked via filter sejoli/recalculate/notif-grand-total, priority 100
     * @param array $order_data
     */
    public function recalculate_notif_grand_total($grand_total, array $order_data) {

        if(isset($order_data['meta_data']['duitku'])) :

            $grand_total  = floatval($order_data['grand_total']) + floatval($order_data['meta_data']['duitku']['duitku_fee']);
        
        endif;

        return $grand_total;

    }

    /**
     * Recalculate grand total on notification
     * Hooked via filter sejoli/payment/data, priority 100
     * @param array $order_data
     */
    public function get_payment_data(array $order_data) {

        if(isset($order_data['duitku'])) :

            $payment_channel = isset($order_data['duitku']['channel']) ? $order_data['duitku']['channel'] : null;
            $duitku_fee      = isset($order_data['duitku']['duitku_fee']) ? $order_data['duitku']['duitku_fee'] : null;

            $payment_data = array(
                'payment_channel' => $payment_channel,
                'payment_fee'     => $duitku_fee
            );

        else:

            $payment_data = array();
        
        endif;

        return $payment_data;

    }

    /**
     * Set order meta data
     * @since   1.3.0
     * @param array $meta_data
     * @param array $order_data
     * @param array $payment_subtype
     * @return array
     */
    public function set_meta_data(array $meta_data, array $order_data, $payment_subtype) {

        $payment_duitku = $this->sejolisa_get_payment_channel_duitku($payment_subtype);
        $meta_data['duitku'] = [
            'duitku_fee' => $this->duitku_fee,
            'trans_id'    => '',
            'duitku_key'  => substr(md5(rand(0,1000)), 0, 16),
            'method'      => $payment_subtype,
            'channel'     => $payment_duitku
        ];

        return $meta_data;

    }

    /**
     * Set transaction fee
     * @since 1.0.0
     * @param array $order_data
     * @return string
     */
    public function add_transaction_fee( array $order_data ) {

        return $this->duitku_fee;

    }

    /**
     * Get setup values
     * @return array
     */
    protected function get_setup_values() {

        $mode          = sejolisa_carbon_get_theme_option('duitku_mode');
        $merchant_code = trim(sejolisa_carbon_get_theme_option('duitku_'.$mode.'_merchant_code'));
        $api_key       = trim(sejolisa_carbon_get_theme_option('duitku_'.$mode.'_api_key'));
        $request_url   = $this->request_url[$mode];

        return array(
            'mode'          => $mode,
            'merchant_code' => $merchant_code,
            'api_key'       => $api_key,
            'request_url'   => $request_url
        );

    }

    /**
     * Setup duitku data
     * @since   1.3.0
     * @since   1.5.1.1     Modify expiry period
     * @param   array  $order Order data
     * @return  void
     */
    protected function setup_duitku_data(array $order) {

        extract($this->get_setup_values());

        $redirect_link     = '';
        $request_to_duitku = false;
        $data_order        = $this->check_data_table($order['ID']);

        if( NULL === $data_order ) :

            $request_to_duitku = true;

        else :

            $detail = unserialize($data_order->detail);

            if(!isset($detail['paymentUrl']) || empty($detail['paymentUrl'])) :
                $request_to_duitku = true;
            else :
                $redirect_link = $detail['paymentUrl'];
            endif;
            
        endif;

        if( true === $request_to_duitku ) :

            $this->add_to_table($order['ID']);

            // $price             = $order['grand_total'] - $order['meta_data']['duitku']['duitku_fee'];
            $price             = $order['grand_total'];
            $payment_amount    = (int) $price;
            $merchant_order_ID = $order['ID'];
            $signature         = md5($merchant_code . $merchant_order_ID . $payment_amount . $api_key);

            $params = array(
                'merchantCode'     => $merchant_code,
                'paymentAmount'    => (int) $price,
                'paymentMethod'    => $order['meta_data']['duitku']['method'],
                'merchantOrderId'  => $order['ID'],
                'productDetails'   => sprintf(__('Pembayaran invoice %s', 'sejoli'), $order['ID']),
                'additionalParam'  => '',
                'merchantUserInfo' => $order['user']->user_email,
                'customerVaName'   => $order['user']->display_name,
                'email'            => $order['user']->user_email,
                'phoneNumber'      => $order['user']->meta->phone,
                'callbackUrl'      => add_query_arg(array(
                                        'order_id'   => $order['ID'],
                                        'duitku_key' => $order['meta_data']['duitku']['duitku_key']
                                    ), site_url('/duitku/process')),

                'returnUrl'        => add_query_arg(array(
                                        'order_id'   => $order['ID'],
                                        'duitku_key' => $order['meta_data']['duitku']['duitku_key']
                                    ), site_url('/duitku/return')),
                'signature'        => $signature,
                'expiryPeriod'     => 60 * intval( sejolisa_carbon_get_theme_option( 'sejoli_countdown_time' ) ),
                'itemDetails'      => array(
                    array(
                        'name'     => $order['product']->post_title,
                        'quantity' => $order['quantity'],
                        'price'    => (int) $price
                    )
                )
            );

            $params_string = json_encode($params);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params_string))
            );
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            //execute post
            $raw_respond = curl_exec( $ch );
            $request     = json_decode( $raw_respond, true );
            $http_code   = intval( curl_getinfo($ch, CURLINFO_HTTP_CODE) );

            if( 200 === $http_code ) :

                $request['duitku_fee'] = $order['meta_data']['duitku']['duitku_fee'];
                $request['duitku_channel'] = $order['meta_data']['duitku']['method'];

                do_action('sejoli/log/write', 'success-duitku', $request);

                $this->update_detail( $order['ID'], $request );
                $redirect_link = $request['paymentUrl'];

                if( $request['statusCode'] === '-100' ) :
                    wp_die(
                        __('Terjadi kesalahan saat request ke duitku.com. <b>Error: '.$request['statusMessage'],'</b>', 'sejoli'),
                        __('Terjadi kesalahan')
                    );
                endif;

            else :

                do_action('sejoli/log/write', 'error-duitku', array($raw_respond, $request, $http_code, $params));

                if( $request ) {
                    wp_die(
                        __('Terjadi kesalahan saat request ke duitku.com. <b>Error: '.$request['Message'],'</b>', 'sejoli'),
                        __('Terjadi kesalahan')
                    );
                } else {
                    wp_die(
                        __('Terjadi kesalahan saat request ke duitku.com. Silahkan kontak pemilik website ini', 'sejoli'),
                        __('Terjadi kesalahan')
                    );
                }

                exit;

            endif;

        endif;

        ?>

        <script>
            if (window.top !== window.self) {
                window.top.location.href = "<?php echo $redirect_link; ?>";
            } else {
                window.location.href = "<?php echo $redirect_link; ?>";
            }
        </script>

        <?php

    }

    /**
     * Receive return process
     * @since   1.3.0
     * @return  void
     */
    protected function receive_return() {

        $args = wp_parse_args($_GET, array(
            'merchantOrderId' => NULL,
            'resultCode'      => NULL,
            'reference'       => NULL
        ));

        if(
            !empty($args['merchantOrderId']) &&
            !empty($args['resultCode']) &&
            !empty($args['reference'])
        ) :
            $order_id = intval($args['merchantOrderId']);

            sejolisa_update_order_meta_data($order_id, array(
                'duitku' => array(
                    'trans_id'  => $args['reference']
                )
            ));

            wp_redirect(add_query_arg(array(
                'order_id' => $order_id
            ), site_url('checkout/thank-you')));

        endif;

        exit;

    }

    /**
     * Process callback from duitku
     * @since   1.3.0
     * @return  void
     */
    protected function process_callback() {

        extract($this->get_setup_values());

        $setup = $this->get_setup_values();

        $args  = wp_parse_args($_POST, array(
                    'merchantCode'    => NULL,
                    'amount'          => NULL,
                    'merchantOrderId' => NULL,
                    'productDetail'   => NULL,
                    'additionalParam' => NULL,
                    'paymentCode'     => NULL,
                    'resultCode'      => NULL,
                    'merchantUserId'  => NULL,
                    'reference'       => NULL,
                    'signature'       => NULL
                 ));

        if(
            !empty($args['merchantCode']) &&
            !empty($args['amount']) &&
            !empty($args['merchantOrderId']) &&
            !empty($args['signature'])
        ) :

            $params          = $args['merchantCode'] . $args['amount'] . $args['merchantOrderId'] . $setup['api_key'];
            $calc_signature  = md5($params);

            if( $args['signature'] === $calc_signature ) :

                if( "00" === $args['resultCode'] ) :

                    $order_id = intval($args['merchantOrderId']);
                    $response = sejolisa_get_order(array('ID' => $order_id));

                    if( false !== $response['valid'] ) :

                        $order    = $response['orders'];
                        $product  = $order['product'];

                        $product_cod      = isset($product->cod['cod-active']) ? $product->cod['cod-active'] : false;
                        $product_shipping = isset($product->shipping['active']) ? $product->shipping['active'] : false;

                        // if product is need of shipment
                        if( false !== $product_shipping || false !==  $product_cod) :
                            $status = 'in-progress';
                        else :
                            $status = 'completed';
                        endif;

                        $this->update_order_status( $order['ID'] );

                        $args['status'] = $status;

                        do_action('sejoli/log/write', 'duitku-update-order', $args);
                    else :
                        do_action('sejoli/log/write', 'duitku-wrong-order', $args);
                    endif;

                endif;

            else :

                $args['calcSignature'] = $calc_signature;
                $args['setup_api']     = $setup;

                do_action('sejoli/log/write', 'duitku-bad-signature', $args);

            endif;
        else :
            wp_die(
                __('You don\'t have permission to access this page', 'sejoli'),
                __('Forbidden access by SEJOLI', 'sejoli')
            );
        endif;

        exit;

    }

    /**
     * Check if current order is using duitku and will be redirected
     * Hooked via action sejoli/thank-you/render, priority 10
     * @since   1.3.0
     * @param  array  $order Order data
     * @return void
     */
    public function check_for_redirect(array $order) {
        if(
            isset($order['payment_info']['bank']) &&
            'DUITKU' === strtoupper($order['payment_info']['bank'])
        ) :

            if('on-hold' === $order['status']) :

                $this->setup_duitku_data($order);

            elseif(in_array($order['status'], array('refunded', 'cancelled'))) :

                $title = __('Order telah dibatalkan', 'sejoli');
                require SEJOLISA_DIR . 'template/checkout/order-cancelled.php';

            else :

                $title = __('Order sudah diproses', 'sejoli');
                require SEJOLISA_DIR . 'template/checkout/order-processed.php';

            endif;
            exit;
        endif;

    }

    /**
     * Display payment instruction in notification
     * @since 1.3.0
     * @param  array    $invoice_data
     * @param  string   $recipient_type
     * @param  string   $media
     * @return string
     */
    public function display_payment_instruction($invoice_data, $media = 'email') {

        if('on-hold' !== $invoice_data['order_data']['status']) :
            return;
        endif;

        $content = sejoli_get_notification_content(
                        'duitku',
                        $media,
                        array(
                            'order' => $invoice_data['order_data']
                        )
                    );

        return $content;

    }

    /**
     * Display simple payment instruction in notification
     * @since 1.3.0
     * @param  array    $invoice_data
     * @param  string   $recipient_type
     * @param  string   $media
     * @return string
     */
    public function display_simple_payment_instruction($invoice_data, $media = 'email') {

        if('on-hold' !== $invoice_data['order_data']['status']) :
            return;
        endif;

        $content = __('via Duitku', 'sejoli');

        return $content;

    }

    /**
     * Set payment info to order datas
     * @since 1.3.0
     * @param array $order_data
     * @return array
     */
    public function set_payment_info(array $order_data) {

        $trans_data = [
            'bank'  => 'Duitku'
        ];

        return $trans_data;

    }

}
