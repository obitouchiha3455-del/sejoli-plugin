<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Class Order extends \SejoliSA\Model
{
    static protected $table            = 'sejolisa_orders';
    static protected $quantity         = 1;
    static protected $order_parent_id  = 0;
    static protected $total            = 0;
    static protected $type             = 'regular';
    static protected $status           = 'on-hold';
    static protected $available_status = [];
    static protected $payment_gateway  = 'manual';
    static protected $meta_data        = [];
    static protected $orders           = [];
    static protected $last_time        = NULL;

    static private $available_types    = [
        'regular',
        'subscription-tryout',
        'subscription-signup',
        'subscription-regular'
    ];

    /**
     * Create table if not exists
     * @return [type] [description]
     */
    static public function create_table()
    {
        parent::$table = self::$table;

        if(!Capsule::schema()->hasTable( self::table() )):
            Capsule::schema()->create( self::table(), function($table){
                $table->increments('ID');
                $table->datetime('created_at');
                $table->datetime('updated_at')->default('0000-00-00 00:00:00');
                $table->datetime('deleted_at')->default('0000-00-00 00:00:00');
                $table->integer('order_parent_id')->default(0);
                $table->integer('product_id');
                $table->integer('user_id');
                $table->integer('affiliate_id');
                $table->integer('coupon_id');
                $table->string('payment_gateway')->default('manual');
                $table->float('grand_total', 12 ,2);
                $table->integer('quantity')->default(1);
                $table->string('type', 100)->default('regular');
                $table->string('status', 100)->default('on-hold');
                $table->text('meta_data');
            });
        endif;
    }

    /**
     * Set order status but first it will cross-check with available order status
     * @param string $status
     */
    static public function set_status($status) {

        self::$available_status = apply_filters('sejoli/order/status', []);
        self::$status           = $status;

        return new static;
    }

    /**
     * Set quantity and forced it to be integer
     * @param mixed $quantity
     */
    static public function set_quantity($quantity) {

        self::$quantity = intval($quantity);

        return new static;
    }

    /**
     * Set order payment gateway, will cross-check with available payment gateway
     * @param string
     */
    static public function set_payment_gateway($payment_gateway) {

        $available_payment_gateways = apply_filters('sejoli/payment/available-payment-gateways', []);

        if(!isset($available_payment_gateways[$payment_gateway])) :
            $payment_gateway = 'manual';
        endif;

        self::$payment_gateway = $payment_gateway;

        return new static;
    }

    /**
     * Set total payment
     * @param  float $total;
     */
    static public function set_total($total) {

        self::$total = floatval($total);
        return new static;
    }

    /**
     * Set order parent ID
     * @since   1.0.0
     * @var     integer
     */
    static public function set_order_parent_id($order_parent_id) {
        self::$order_parent_id = absint($order_parent_id);
        return new static;
    }

    /**
     * Set order type
     * @var string
     */
    static public function set_type($type) {

        $type = (!in_array($type, self::$available_types)) ?
                    'regular' :
                    $type;

        self::$type = $type;

        return new static;
    }

    /**
     * Set orders
     * @var string
     */
    static public function set_orders($orders) {

        self::$orders = (array) $orders;

        return new static;
    }

    /**
     * Set last time with mysql value
     * @since   1.5.3.3
     * @var     DateTime
     */
    static public function set_last_time( $last_time ) {

        self::$last_time = $last_time;

        return new static;
    }

    /**
     * Reset all properties
     * @var [type]
     */
    static public function reset() {

        parent::reset();

        self::$quantity         = 1;
        self::$total            = 0;
        self::$status           = 'on-hold';
        self::$available_status = [];
        self::$payment_gateway  = 'manual';
        self::$type             = 'regular';
        self::$order_parent_id  = 0;
        self::$orders           = [];
        self::$meta_data        = [];
        self::$last_time        = NULL;

        return new static;
    }

    /**
     * Validate data
     * @return void
     */
    static protected function validate() {

        if( in_array(self::$action, ['create', 'get-user-bought-product', 'get-last-user-bought'] ) ) :

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('User tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Item tidak valid', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['create'])) :

            if(!is_numeric(self::$affiliate_id)) :
                self::set_valid(false);
                self::set_message( __('Affiliate ID tidak valid', 'sejoli'));
            endif;

            if(!is_numeric(self::$coupon_id)) :
                self::set_valid(false);
                self::set_message( __('Kupon ID tidak valid', 'sejoli'));
            endif;

            if(!is_numeric(self::$order_parent_id)) :
                self::set_valid(false);
                self::set_message( __('Order parent ID tidak valid', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['create', 'update-status'])) :
            if(!isset(self::$available_status[self::$status])) :
                self::set_valid(false);
                self::set_message( sprintf(__( 'Status order %s tidak tedaftar', 'sejoli'), self::$status ));
            endif;
        endif;

        if(in_array(self::$action, ['update-status', 'update-meta-data'])) :
            if(empty(self::$id)) :
                self::set_valid(false);
                self::set_message( __( 'Order ID tidak memiliki nilai', 'sejoli') );
            endif;
        endif;

        if('update-meta-data' === self::$action) :
            if(!is_array(parent::$meta_data) || 0 === count(parent::$meta_data)) :
                self::set_valid(false);
                self::set_message( __('Meta data tidak memiliki value', 'sejoli'));
            endif;
        endif;

        if('get-products' === self::$action) :
            if(empty(self::$user_id)) :
                self::set_valid(false);
                self::set_message( __( 'User ID tidak valid', 'sejoli') );
            endif;
        endif;

        if('get-by-physical-products' === self::$action) :
            if(!is_array(self::$orders) || 0 === count(self::$orders)) :
                self::set_valid(false);
                self::set_message( __('Order belum ada yang dipilih', 'sejoli'));
            endif;
        endif;

        if('count-total-order' === self::$action) :
            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Item tidak valid', 'sejoli'));
            endif;
        endif;

        if( in_array(self::$action, ['get-last-user-bought'] ) ) :

            if( empty(self::$last_time) ) :
                self::set_valid( false );
                self::set_message( __('Limit pembelian produk kosong', 'sejoli') );
            endif;

        endif;

    }

    /**
     * Create new order
     */
    static function create() {

        self::set_action('create');
        self::validate();

        if(true === self::$valid) :
            parent::$table = self::$table;

            $order = [
                'created_at'      => current_time('mysql'),
                'updated_at'      => '0000-00-00 00:00:00',
                'deleted_at'      => '0000-00-00 00:00:00',
                'order_parent_id' => self::$order_parent_id,
                'product_id'      => self::$product->ID,
                'user_id'         => self::$user->ID,
                'affiliate_id'    => self::$affiliate_id,
                'coupon_id'       => self::$coupon_id,
                'payment_gateway' => self::$payment_gateway,
                'grand_total'     => self::$total,
                'quantity'        => self::$quantity,
                'status'          => self::$status,
                'type'            => self::$type,
                'meta_data'       => serialize(parent::$meta_data)
            ];

            $order['ID'] = Capsule::table(self::table())
                            ->insertGetId($order);

            self::set_valid(true);
            self::set_respond('order',$order);

        endif;

        return new static;
    }

    /**
     * Set filter data to query
     */
    static protected function set_filter_query($query)
    {
        if ( !is_null( self::$filter['search'] ) && is_array( self::$filter['search'] ) ) :

            foreach ( self::$filter['search'] as $key => $value ) :

                if ( !empty( $value['val'] ) ) :

                    if(is_array($value['val'])) :

                        if( isset($value['compare']) && 'NOT IN' === $value['compare'] ) :
                            $query->whereNotIn( 'data_order.' . $value['name'],$value['val'] );
                        else :
                            $query->whereIn( 'data_order.' . $value['name'],$value['val'] );
                        endif;

                    elseif(isset($value['compare']) && !is_null($value['compare'])) :

                        $query->where( 'data_order.' . $value['name'], $value['compare'], $value['val']);

                    else :

                        $query->where( 'data_order.' . $value['name'], $value['val'] );

                    endif;

                endif;

            endforeach;

        endif;

        return $query;
    }

    /**
     * Get orders by filter
     * @since   1.0.0
     * @return  void
     */
    static function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS data_order ') )
                        ->select(
                            Capsule::raw('data_order.*, user.display_name AS user_name, user.user_email AS user_email , product.post_title AS product_name, coupon.code AS coupon_code, affiliate.display_name AS affiliate_name, CASE WHEN data_order.updated_at = "0000-00-00 00:00:00" THEN DATE(data_order.created_at) ELSE DATE(data_order.updated_at) END AS order_date')                        )
                        ->join( Capsule::raw( $wpdb->users . ' AS user '), 'user.ID', '=', 'data_order.user_id')
                        ->join( Capsule::raw( $wpdb->posts . ' AS product '), 'product.ID', '=', 'data_order.product_id')
                        ->leftJoin( Capsule::raw( $wpdb->prefix . 'sejolisa_coupons AS coupon'), 'coupon.ID', '=', 'data_order.coupon_id')
                        ->leftJoin( Capsule::raw( $wpdb->users . ' AS affiliate'), 'affiliate.ID', '=', 'data_order.affiliate_id')
                        ->orderBy( 'order_date', 'DESC' );

        $query        = self::set_filter_query( $query );
        $recordsTotal = $query->count();

        $query        = self::set_length_query($query);
        $orders       = $query->get()->toArray();

        if ( $orders ) :
            self::set_respond('valid',true);
            self::set_respond('orders',$orders);
            self::set_respond('recordsTotal',$recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('orders', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get order by given column and save to respond
     * @param  string  $column
     * @param  boolean $is_single
     * @return static
     */
    static function get_by($column = 'id', $value = '',$is_single = true) {

        global $wpdb;

        parent::$table = self::$table;

        self::$filter['search'][] = [
            'name' => $column,
            'val'  => $value
        ];
        
        $query        = Capsule::table( Capsule::raw( self::table() . ' AS data_order ') )
                        ->select(
                            Capsule::raw('data_order.*, user.display_name AS user_name, user.user_email AS user_email , product.post_title AS product_name, coupon.code AS coupon_code, affiliate.display_name AS affiliate_name, confirm.created_at as confirm_date, confirm.detail as confirm_detail ')
                        )
                        ->join( Capsule::raw( $wpdb->users . ' AS user '), 'user.ID', '=', 'data_order.user_id')
                        ->join( Capsule::raw( $wpdb->posts . ' AS product '), 'product.ID', '=', 'data_order.product_id')
                        ->leftJoin( Capsule::raw( $wpdb->prefix . 'sejolisa_coupons AS coupon'), 'coupon.ID', '=', 'data_order.coupon_id')
                        ->leftJoin( Capsule::raw( $wpdb->users . ' AS affiliate'), 'affiliate.ID', '=', 'data_order.affiliate_id')
                        ->leftJoin( Capsule::raw( $wpdb->prefix . 'sejolisa_confirmations AS confirm'), 'confirm.order_id', '=', 'data_order.ID');

        $query  = self::set_filter_query( $query );
        $orders = (false !== $is_single) ? (array) $query->first() : $query->get()->toArray();

        if ( $orders ) :
            self::set_respond('valid',        true);
            self::set_respond('orders',       $orders);
            self::set_respond('recordsTotal', (false !== $is_single) ? $query->count() : 1);
        else:
            self::set_respond('valid', false);
            self::set_message(sprintf(__('Order with %s %s doesn\'t exist', 'sejoli'), $column, $value));
        endif;

        return new static;
    }

    /**
     * Update order status
     */
    static function update_status() {

        self::set_action('update-status');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            Capsule::table(self::table())
                ->where('ID', self::$id)
                ->update([
                    'updated_at' => current_time('mysql'),
                    'status'     => self::$status
                ]);
            self::set_valid(true);
            self::set_message( sprintf(__('Order %s updated successfully', 'sejoli'), self::$id), 'success');
        endif;

        return new static;
    }

    /**
     * Update order meta data
     * @since   1.0.0
     */
    static public function update_meta_data() {

        self::set_action('update-meta-data');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $order = Capsule::table(self::table())
                        ->where('ID', self::$id)
                        ->first();

            $order->meta_data = maybe_unserialize($order->meta_data);

            foreach(parent::$meta_data as $_key => $_value) :
                if(is_array($_value)) :
                    foreach($_value as $__key => $__value) :
                        if(is_array($__value)) :
                            foreach($__value as $___key => $___value) :
                                $order->meta_data[$_key][$__key][$___key] = $___value;
                            endforeach;
                        else :
                            $order->meta_data[$_key][$__key] = $__value;
                        endif;
                    endforeach;
                else :
                    $order->meta_data[$_key] = $_value;
                endif;
            endforeach;

            Capsule::table(self::table())
                ->where('ID', self::$id)
                ->update([
                    'meta_data' => serialize($order->meta_data)
                ]);

            self::set_valid(true);
            self::set_respond( 'order', $order);
            self::set_message( sprintf( __('Order %s meta data updated successfully', 'sejoli'), self::$id), 'success');

        endif;

        return new static;
    }

    /**
     * Set data for chart purpose
     * @since 1.0.0
     */
    static function set_for_chart($type = 'total-order',$grouped_by_status = true) {

        parent::$table = self::$table;

        self::calculate_chart_range_date();
        $columns = [];

        switch ($type) :
            case 'total-order':
                $columns[] = Capsule::raw('count(ID) AS total');
                break;

            case 'total-paid':
                $columns[] = Capsule::raw('sum(grand_total) AS total');
                break;

            case 'total-quantity':
                $columns[] = Capsule::raw('sum(quantity) AS total');
                break;

        endswitch;

        if($grouped_by_status) :
            $columns[] ='status';
            $groups    = ['status'];
        endif;

        if('year' === self::$chart['type']) :
            $columns[] = Capsule::raw('CASE WHEN updated_at = "0000-00-00 00:00:00" THEN YEAR(created_at) ELSE YEAR(updated_at) END AS year ');
            $groups[]  = 'year';
        elseif('month' === self::$chart['type']) :
            $columns[] = Capsule::raw('CASE WHEN updated_at = "0000-00-00 00:00:00" THEN DATE_FORMAT(created_at, "%Y-%m") ELSE DATE_FORMAT(updated_at, "%Y-%m") END AS month ');
            $groups[]  = 'month';
        elseif('date' === self::$chart['type']) :
            $columns[] = Capsule::raw('CASE WHEN updated_at = "0000-00-00 00:00:00" THEN DATE(created_at) ELSE DATE(updated_at) END AS date ');
            $groups[]  = 'date';
        endif;

        $query = Capsule::table( Capsule::raw( self::table() . ' AS data_order ') )
                    ->select($columns);

        $query = self::set_filter_query($query);
        $data  = $query->groupBy($groups)
                    ->get();

        self::set_respond('data' ,$data);
        self::set_respond('chart',self::$chart);

        return new static;
    }

    /**
     * Get all products by order
     * @since 1.0.0
     */
    static function get_products() {

        global $wpdb;

        self::set_action('get-products');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $products = Capsule::table(Capsule::raw( self::table() . ' AS data_order ' ) )
                            ->join( Capsule::raw( $wpdb->posts . ' AS product'), 'product.ID', '=', 'data_order.product_id')
                            ->select( Capsule::raw( 'data_order.created_at, data_order.updated_at, data_order.product_id, product.post_title AS product_name ') )
                            ->where('data_order.user_id', self::$user_id)
                            ->where('type', 'regular')
                            ->whereIn('data_order.status', ['completed', 'in-progress', 'shipping'])
                            ->groupBy('data_order.product_id')
                            ->orderBy('data_order.updated_at', 'ASC')
                            ->orderBy('data_order.created_at', 'ASC')
                            ->get();

            self::set_valid(true);
            self::set_respond('products', $products);

        endif;

        return new static;
    }

    /**
     * Get all affiliatess by order
     * @since 1.0.0
     */
    static function get_affiliates() {

        global $wpdb;

        self::set_action('get-products');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $affiliates = Capsule::table(Capsule::raw( self::table() . ' AS data_order ' ) )
                            ->join( Capsule::raw( $wpdb->posts . ' AS product'), 'product.ID', '=', 'data_order.product_id')
                            ->join( Capsule::raw( $wpdb->users . ' AS user'), 'user.ID', '=', 'data_order.affiliate_id')
                            ->select(
                                Capsule::raw( 'data_order.affiliate_id, data_order.product_id, product.post_title AS product_name, user.display_name AS affiliate_name ')
                            )
                            ->where('data_order.user_id', self::$user_id)
                            ->where('data_order.type', 'regular')
                            ->where('data_order.affiliate_id', '!=', 0)
                            ->whereIn('data_order.status', ['completed', 'in-progress', 'shipping'])
                            ->groupBy('data_order.affiliate_id', 'data_order.product_id')
                            ->get();

            self::set_valid(true);
            self::set_respond('affiliates', $affiliates);

        endif;

        return new static;
    }

    /**
     * Get all order by physical product
     * @since   1.0.0
     */
    static public function get_by_physical_product() {

        global $wpdb;

        self::set_action('get-by-physical-product');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $orders = Capsule::table(Capsule::raw( self::table() . ' AS data_order ' ) )
                            ->join( Capsule::raw( $wpdb->posts . ' AS product'), 'product.ID', '=', 'data_order.product_id')
                            ->join( Capsule::raw( $wpdb->postmeta . ' AS product_type'), 'product_type.post_id', '=', 'data_order.product_id')
                            ->select(
                                Capsule::raw( 'data_order.ID AS order_id, data_order.meta_data , product.post_title AS product_name, product_type.meta_value AS product_type ')
                            )
                            ->whereIn('data_order.ID', self::$orders)
                            ->where('product_type.meta_key', '_product_type')
                            ->get();

            self::set_valid(true);
            self::set_respond('orders', $orders);

        endif;

        return new static;

    }

    /**
     * Count total order
     * @since   1.0.0
     */
    static public function get_total_order() {

        self::set_action('count-total-order');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $query = Capsule::table(self::table())
                        ->where('product_id', self::$product->ID);

            if(!empty(self::$status)) :
                $status = (array) self::$status;
                $query->whereIn('status', $status);
            endif;

            $total = $query->count();

            self::set_valid(true);
            self::set_respond('total', $total);

        endif;

        return new static;
    }

    /**
     * Count total order
     * @since   1.0.0
     * @since   1.5.4       Remove canclled order from total order
     * @param   boolean     $excelude_cancelled_order
     */
    static public function get_total_order_v2( $excelude_cancelled_order = false ) {

        parent::$table = self::$table;

        $query = Capsule::table( Capsule::raw( self::table() . ' AS data_order ' ) );

        if( true === $excelude_cancelled_order ) :
            $query = $query->whereNotIn('status', array('cancelled'));
        endif;

        $query = self::set_filter_query( $query );
        $total = $query->count();

        self::set_valid(true);
        self::set_respond('total', $total);

        return new static;
    }

    /**
     * Count total omset by affiliate
     * @since   1.0.0
     */
    static public function get_total_omset() {

        parent::$table = self::$table;

        $query = Capsule::table( Capsule::raw( self::table() . ' AS data_order ' ) )
                    ->select( Capsule::raw('SUM(data_order.grand_total) AS total_omset') );

        $query    = self::set_filter_query( $query );
        $response = $query->first();

        self::set_valid(true);
        self::set_respond('total', floatval($response->total_omset));

        return new static;
    }

    /**
     * Get order data for bulk actions
     * @since   1.0.0
     */
    static public function get_for_bulks() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS data_order ') )
                        ->select(Capsule::raw('data_order.ID'))
                        ->where('data_order.product_id', self::$product_id)
                        ->where('data_order.status', self::$status);

        $query        = self::set_filter_query( $query );
        $recordsTotal = $query->count();
        $query        = self::set_length_query($query);
        $orders       = $query->get()->toArray();

        if ( $orders ) :
            self::set_respond('valid',true);
            self::set_respond('orders',$orders);
            self::set_respond('recordsTotal',$recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('orders', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Check if user already bought the product
     * @since   1.5.3
     */
    static public function get_user_bought() {

        global $wpdb;

        self::set_action('get-user-bought-product');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $order = Capsule::table( self::table() )
                            ->where( 'status',     'completed')
                            ->where( 'user_id',    self::$user_id )
                            ->where( 'product_id', self::$product_id)
                            ->first();

            if($order) :
                $order->meta_data = maybe_unserialize( $order->meta_data );

                self::set_valid  ( true );
                self::set_respond( 'order', $order);
            else :
                self::set_valid( false );
            endif;

        endif;

        return new static;

    }

    /**
     * Get last bought
     * @since   1.5.3.3
     */
    static public function get_last_bought() {

        global $wpdb;

        self::set_action('get-last-bought-product');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $order = Capsule::table( self::table() )
                            ->whereNotIn( 'status', array('completed', 'in-progress', 'shipping') )
                            ->where( 'user_id',     self::$user_id )
                            ->where( 'product_id',  self::$product_id)
                            ->where( 'created_at',  '>', self::$last_time)
                            ->first();

            if($order) :
                $order->meta_data = maybe_unserialize( $order->meta_data );

                self::set_valid  ( true );
                self::set_respond( 'order', $order);
            else :
                self::set_valid( false );
            endif;

        endif;

        return new static;
    }
}
