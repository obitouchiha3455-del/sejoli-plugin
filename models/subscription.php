<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Class Subscription extends \SejoliSA\Model
{
    static protected $table           = 'sejolisa_subscriptions';
    static protected $order_parent_id = 0;
    static protected $end_time        = NULL;
    static protected $end_date        = NULL;
    static protected $type            = 'regular';
    static protected $status          = 'pending';

    /**
     * Set max renewal day available
     * @since   1.5.3
     * @var     integer
     */
    static protected $max_renewal_day = 0;

    /**
     * Subscription data
     * @since   1.5.3
     * @var     array
     */
    static protected $subscriptions = array(
        'running'   => array(), //Running subscriptions,
        'expired'   => array(), //Expired subscriptions
    );

    /**
     * Create table if not exists
     * @return void
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
                $table->integer('order_id');
                $table->integer('order_parent_id')->default(0);
                $table->integer('user_id');
                $table->integer('product_id');
                $table->string('type', 100)->default('regular');
                $table->datetime('end_date')->default('0000-00-00 00:00:00');
                $table->string('status', 100)->default('pending');
            });
        endif;
    }

    /**
     * Set subscription order parent id
     * @since   1.0.0
     * @var     integer
     */
    static public function set_order_parent_id($order_parent_id) {
        self::$order_parent_id = intval($order_parent_id);
        return new static;
    }

    /**
     * Set subscription type
     * @since  1.0.0
     * @var    string
     */
    static public function set_type($type) {
        $type = (!in_array($type, ['tryout', 'signup', 'regular'])) ? 'regular' : $type;
        self::$type = $type;
        return new static;
    }

    /**
     * Set subscription end time ( in seconds )
     * @since   1.0.0
     * @var     integer
     */
    static public function set_end_time($end_time) {
        self::$end_time = intval($end_time);
        return new static;
    }

    /**
     * Set max renewal days
     * @since   1.5.3
     */
    static public function set_max_renewal_day( $max_renewal_day ) {

        self::$max_renewal_day = absint( $max_renewal_day );

        return new static;
    }

    /**
     * Set subscription status
     * @since   1.0.0
     * @var     string
     */
    static public function set_status($status) {
        $status = (in_array($status,['pending', 'active', 'inactive', 'expired'])) ? $status : 'pending';
        self::$status = $status;
        return new static;
    }

    /**
     * Reset properties
     * @since   1.0.0
     * @since   1.5.3
     */
    static public function reset() {

        parent::reset();
        parent::$table         = self::$table;

        self::$order_parent_id = 0;
        self::$end_time        = NULL;
        self::$end_date        = NULL;
        self::$type            = 'regular';
        self::$status          = 'pending';
        self::$max_renewal_day = 0;
        self::$subscriptions   = array(
            'running'   => array(), //Running subscriptions,
            'expired'   => array(), //Expired subscriptions
        );

        return new static;
    }

    /**
     * Validate data
     */
    static public function validate() {

        if(in_array(self::$action, ['create', 'update-status'])) :

            if(!in_array(self::$status,['pending', 'active', 'expired'])) :
                self::set_valid(false);
                self::set_message(__('Status langganan tidak valid', 'sejoli'));
            endif;

        endif;

        if('update-status' === self::$action) :
            if(0 === self::$id) :
                self::set_valid(false);
                self::set_message(__('ID langganan tidak valid', 'sejoli'));
            endif;
        endif;

        if('create' === self::$action) :

            if(empty(self::$order_id)) :
                self::set_valid(false);
                self::set_message( __('Order ID tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('Affiliasi tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli'));
            endif;

            if(!in_array(self::$type, ['tryout', 'signup', 'regular'])) :
                self::set_valid(false);
                self::set_message(__('Tipe langganan tidak valid', 'sejoli'));
            endif;

            if(0 === self::$end_time) :
                self::set_valid(false);
                self::set_message(__('Waktu langganan tidak valid', 'sejoli'));
            endif;
        endif;

        if('get-products' === self::$action) :
            if(empty(self::$user_id)) :
                self::set_valid(false);
                self::set_message( __( 'User ID tidak valid', 'sejoli') );
            endif;
        endif;
    }

    /**
     * Set subscription end date
     */
    static protected function set_end_date() {

        if(0 === self::$order_parent_id || 'tryout' === self::$type) :
            self::$end_date = date('Y-m-d H:i:s', current_time('timestamp') + self::$end_time);
        else :

            $subscription = Capsule::table(self::table())
                                ->select('end_date')
                                ->where(function($query){
                                    $query->where('order_id', self::$order_parent_id);
                                    $query->orWhere('order_parent_id', self::$order_parent_id);
                                })
                                ->where('status', 'active')
                                ->latest()
                                ->first();

            $subs_end_date = $subscription ? $subscription->end_date : null;
            $end_date = safe_strtotime($subs_end_date);

            if($end_date < current_time('timestamp')) :
                self::$end_date = date('Y-m-d H:i:s', current_time('timestamp') + self::$end_time);
            else :
                self::$end_date = date('Y-m-d H:i:s', $end_date + self::$end_time);
            endif;

        endif;
    }

    /**
     * Create subscription
     */
    static public function create() {

        self::set_action('create');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;
            self::set_end_date();

            $subscription = [
                'created_at'      => current_time('mysql'),
                'updated_at'      => '0000-00-00 00:00:00',
                'deleted_at'      => '0000-00-00 00:00:00',
                'order_id'        => self::$order_id,
                'order_parent_id' => self::$order_parent_id,
                'user_id'         => self::$user->ID,
                'product_id'      => self::$product->ID,
                'type'            => self::$type,
                'end_date'        => self::$end_date,
                'status'          => self::$status
            ];

            $subscription['ID'] = Capsule::table(self::table())
                            ->insertGetId($subscription);

            self::set_valid(true);
            self::set_respond('subscription',$subscription);
        endif;

        return new static;
    }

    /**
     * Get subscription by order
     */
    static public function get_by_order() {
        parent::$table = self::$table;

        $subscription = Capsule::table(self::table())
            ->where('order_id', self::$order_id)
            ->latest()
            ->first();

        if($subscription) :
            self::set_valid(true);
            self::set_respond('subscription',$subscription);
        else :
            self::set_valid(false);
        endif;

        return new static;
    }

    /**
     * Check subscription
     */
    static public function check_subscription() {

        parent::$table = self::$table;

        $capsule = Capsule::table(self::table());

        if(!empty(self::$order_id) && !empty(self::$order_parent_id)) :

            $capsule->where(function($query){
                $query->where('order_id', self::$order_id);
                $query->orWhere('order_parent_id', self::$order_parent_id);
            });

        elseif(!empty(self::$order_parent_id)) :
            $capsule->where('order_parent_id', self::$order_parent_id);
        else :
            return new static;
        endif;

        $subscription = $capsule->whereIn('status', ['active', 'expired'])
                            ->latest()
                            ->first();

        if($subscription) :
            self::set_valid(true);
            self::set_respond('subscription',$subscription);                 
        else :
            self::set_valid(false);
        endif;

        return new static;
    }

    /**
     * Update subscription status
     */
    static public function update_status() {

        parent::$table = self::$table;
        self::set_action('update-status');
        self::validate();

        if(true === self::$valid) :
                         Capsule::table(self::table())
                ->where('ID', self::$id)
                ->update([
                    'updated_at' => current_time('mysql'),
                    'status'     => self::$status
                ]);
            self::set_valid(true);
            self::set_message( sprintf(__('Subscribe %s updated successfully to %s', 'sejoli'), self::$id, self::$status), 'success');
        endif;

        return new static;
    }

    /**
     * Update multiple coupon status
     * @since   1.0.0
     * @return  void
     */
    static public function update_status_multiple() {

        parent::$table = self::$table;

        $query    = Capsule::table(Capsule::raw(self::table() . ' AS subscription'));
        $query    = self::set_filter_query($query);
        $response = $query->update([
            'status'     => self::$status,
            'updated_at' => current_time('mysql')
        ]);

        self::set_valid(boolval($response));

        return new static;
        
    }

    /**
     * Set filter data to query
     */
    static protected function set_filter_query($query)
    {

        if ( !is_null( self::$filter['search'] ) && is_array( self::$filter['search'] ) ) :

            foreach ( self::$filter['search'] as $key => $value ) :

                if( 'user_id' === $value['name'] ) :
                    $value['name'] = 'subscription.user_id';
                endif;

                if( 'status' === $value['name'] ) :
                    $value['name'] = 'subscription.status';
                endif;

                if( 'product_id' === $value['name'] ) :
                    $value['name'] = 'subscription.product_id';
                endif;

                if( 'type' === $value['name'] ) :
                    $value['name'] = 'subscription.type';
                endif;

                if ( !empty( $value['val'] ) ) :
                    if(is_array($value['val'])) :
                        $query->whereIn( $value['name'],$value['val'] );
                    elseif(isset($value['compare']) && !is_null($value['compare'])) :
                        $query->where( $value['name'], $value['compare'], $value['val']);
                    elseif( 'ID' === $value['name']) :
                        $query->where( function($query) use ( $value) {
                            $query->where('subscription.order_id',  $value['val']);
                            $query->orWhere('subscription.order_parent_id',  $value['val']);
                        });
                    else :
                        $query->where( $value['name'],$value['val'] );
                    endif;
                endif;

            endforeach;

        endif;

        return $query;
    }

    /**
     * Get all subscriptions
     */
    static public function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query         = Capsule::table(Capsule::raw( self::table() . ' AS subscription' ))
                            ->select(Capsule::raw( 'subscription.*, product.post_title as product_name, user.display_name as user_name, data_order.type AS order_type'))
                            ->join($wpdb->prefix . 'sejolisa_orders AS data_order', 'data_order.ID', '=', 'subscription.order_id')
                            ->join($wpdb->posts . ' AS product', 'product.ID', '=', 'subscription.product_id')
                            ->join($wpdb->users . ' AS user', 'user.ID', '=', 'subscription.user_id');

        $query         = self::set_filter_query( $query );
        $recordsTotal  = $query->count();

        $query         = self::set_length_query($query);

        $subscriptions = $query->get()->toArray();

        if ( $subscriptions ) :

            self::set_respond('valid',true);
            self::set_respond('subscriptions',$subscriptions);
            self::set_respond('recordsTotal',$recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('subscriptions', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get all products by subscriptions
     * @since 1.0.0
     */
    static function get_products() {

        global $wpdb;

        self::set_action('get-products');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $products = Capsule::table(Capsule::raw( self::table() . ' AS subscription ' ) )
                            ->join( Capsule::raw( $wpdb->posts . ' AS product'), 'product.ID', '=', 'subscription.product_id')
                            ->select( Capsule::raw( 'subscription.created_at, subscription.updated_at, subscription.end_date, subscription.product_id, product.post_title AS product_name ') )
                            ->where('subscription.user_id', self::$user_id)
                            ->whereIn('subscription.status', ['active'])
                            ->where('subscription.end_date', '>', current_time('mysql'))
                            ->groupBy('subscription.product_id')
                            ->orderBy('subscription.updated_at', 'ASC')
                            ->orderBy('subscription.created_at', 'ASC')
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

            $affiliates = Capsule::table(Capsule::raw( self::table() . ' AS subscription ' ) )
                            ->join( Capsule::raw( $wpdb->posts . ' AS product'), 'product.ID', '=', 'subscription.product_id')
                            ->join( Capsule::raw( $wpdb->prefix . 'sejolisa_orders AS data_order'), 'data_order.ID', '=', 'subscription.order_id')
                            ->join( Capsule::raw( $wpdb->users . ' AS user'), 'user.ID', '=', 'data_order.affiliate_id')
                            ->select(
                                Capsule::raw( 'data_order.affiliate_id, user.display_name AS affiliate_name, subscription.product_id, product.post_title AS product_name ')
                                )

                            ->where('subscription.user_id', self::$user_id)
                            ->whereIn('subscription.status', ['active'])
                            ->where('subscription.end_date', '>', current_time('mysql'))
                            ->where('data_order.affiliate_id', '!=', 0)
                            ->groupBy('data_order.product_id')
                            ->groupBy('data_order.affiliate_id')
                            ->get();

            self::set_valid(true);
            self::set_respond('affiliates', $affiliates);

        endif;

        return new static;
    }

    /**
     * Get all renewal subscription data
     * @since   1.5.3
     */
    static protected function get_renewal_subscriptions() {

        global $wpdb;

        // Get expired renewed orders
        $result = Capsule::table( Capsule::raw( $wpdb->prefix.self::$table . ' AS subscription' ) )
                    ->select(Capsule::raw( 'subscription.ID, subscription.end_date, subscription.order_parent_id'))
                    ->where( 'subscription.order_parent_id', '>', 0)
                    ->where( 'subscription.status', '=', 'expired')
                    ->groupBy( 'subscription.order_parent_id' )
                    ->orderBy('ID', 'desc')
                    ->get()
                    ->toArray();

        foreach($result as $data) :

            if( current_time( 'timestamp') > strtotime( $data->end_date) ) :
                self::$subscriptions['expired'][ $data->order_parent_id ] = array(
                    'ID'       => $data->ID,
                    'end_date' => strtotime($data->end_date)
                );
            else :
                self::$subscriptions['running'][ $data->order_parent_id ] = array(
                    'ID'       => $data->ID,
                    'end_date' => strtotime($data->end_date)
                );
            endif;

        endforeach;
    }

    /**
     * Get first time subscription data
     * @since   1.5.3
     */
    static protected function get_first_subscriptions() {

        global $wpdb;

        $expired_renewal_date = date( 'Y-m-d H:i:s', current_time( 'timestamp') - ( self::$max_renewal_day * DAY_IN_SECONDS ) );

        // Get expired new subscriptions
        $result = Capsule::table(Capsule::raw( $wpdb->prefix.self::$table . ' AS subscription' ))
                    ->select(Capsule::raw( 'subscription.ID, subscription.end_date, subscription.order_id'))
                    ->where( 'subscription.end_date', '<', $expired_renewal_date )
                    ->where( 'subscription.order_parent_id', '=', 0)
                    ->where( 'subscription.status', '=', 'expired')
                    ->orderBy('ID', 'desc')
                    ->get()
                    ->toArray();

        foreach( $result as $data ) :

            if( ! isset( self::$subscriptions['running'][ $data->order_id] ) ):
                self::$subscriptions['expired'][ $data->order_id ] = array(
                    'ID'       => $data->ID,
                    'end_date' => strtotime($data->end_date)
                );
            endif;

        endforeach;
    }

    /**
     * Get expired subscriptions and without renewal
     * @since 1.5.3
     */
    static function get_expired_subscriptions() {

        self::get_renewal_subscriptions();
        self::get_first_subscriptions();

        if(
            is_array( self::$subscriptions['expired'] ) &&
            0 < count( self::$subscriptions['expired'] )
        ) :

            $ids = wp_list_pluck( self::$subscriptions['expired'], 'ID' );

            global $wpdb;

            parent::$table = self::$table;

            $result = Capsule::table(Capsule::raw( $wpdb->prefix.self::$table . ' AS subscription' ))
                        ->select(Capsule::raw( 'subscription.*, product.post_title as product_name, user.display_name as user_name, user.user_email as user_email, data_order.type AS order_type'))
                        ->join($wpdb->prefix . 'sejolisa_orders AS data_order', 'data_order.ID', '=', 'subscription.order_id')
                        ->join($wpdb->posts . ' AS product', 'product.ID', '=', 'subscription.product_id')
                        ->join($wpdb->users . ' AS user', 'user.ID', '=', 'subscription.user_id')
                        ->whereIn( 'subscription.ID', $ids )
                        ->get()
                        ->toArray();

            if( $result ) :

                self::set_valid     ( true );
                self::set_respond   ( 'subscriptions', $result );

            else :

                self::set_valid     ( false );
                self::set_message   ( __('Data not found', 'sejoli') );

            endif;

        else :

            self::set_valid     ( false );
            self::set_message   ( __('Data not found', 'sejoli') );

        endif;

        return new static;
    }

    /**
     * Get subscription is expired
     * @since   1.0.0
     */
    static function get_subscription_expired($status = 'expired') {
        
        global $wpdb;

        $subscription_expired = array();

        $result = Capsule::table( $wpdb->prefix.self::$table . ' AS subscription')
            ->select('order_id')
            ->where('subscription.status', $status)
            ->get();

        if($result) :
        
            foreach($result as $_data) :
        
                $subscription_expired[] = $_data->order_id;
        
            endforeach;
        
        endif;

        return $subscription_expired;
    
    }

}
