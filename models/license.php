<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Class License extends \SejoliSA\Model
{
    static protected $table     = 'sejolisa_licenses';
    static protected $code      = '';
    static protected $string    = '';
    static protected $status    = 'pending';

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
                $table->integer('user_id');
                $table->integer('product_id');
                $table->string('code');
                $table->string('string')->nullable();
                $table->string('status', 100)->default('pending');
                $table->text('meta_data');
            });
        endif;
    }

    /**
     * Set license code
     * @var string
     */
    static public function set_code($code) {
        self::$code = $code;
        return new static;
    }

    /**
     * Set license string, use as mark where the license used in
     * @var string
     */
    static public function set_string($string) {
        self::$string = $string;
        return new static;
    }

    /**
     * Set license status
     * @param string $status
     */
    static public function set_status($status) {

        $status       = (!in_array($status, ['active', 'pending', 'inactive'])) ? 'pending' : $status;
        self::$status = $status;

        return new static;
    }

    /**
     * Reset properties
     * @var [type]
     */
    static public function reset() {

        parent::reset();

        self::$code   = "";
        self::$string = "";
        self::$status = 'pending';

        return new static;
    }

    /**
     * Validate data
     * @return void
     */
    static protected function validate() {

        if(in_array(self::$action, ['create'])) :

            if(empty(self::$order_id)) :
                self::set_valid(false);
                self::set_message( __('Order ID tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('User tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli'));
            endif;

            if(empty(self::$code)) :
                self::set_valid(false);
                self::set_message( __('Lisensi tidak boleh kosong', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['update-string'])) :

            if(empty(parent::$id)) :
                self::set_valid(false);
                self::set_message(__('ID Lisensi tidak valid', 'sejoli'));
            endif;

            if(empty(self::$string)) :
                self::set_valid(false);
                self::set_message(__('String lisensi harus diisi', 'sejoli'));
            endif;

        endif;
    }

    /**
     * Save commission data to database
     */
    static function create() {

        self::set_action('create');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $license = [
                'created_at' => current_time('mysql'),
                'updated_at' => '0000-00-00 00:00:00',
                'deleted_at' => '0000-00-00 00:00:00',
                'order_id'   => self::$order_id,
                'product_id' => self::$product->ID,
                'user_id'    => self::$user->ID,
                'code'       => self::$code,
                'string'     => self::$string,
                'status'     => self::$status,
                'meta_data'  => serialize(self::$meta_data)
            ];

            $license['ID'] = Capsule::table(self::table())
                            ->insertGetId($license);

            self::set_valid(true);
            self::set_respond('license', $license);
        endif;

        return new static;
    }

    /**
     * Update string license
     */
    static function update_string() {
        self::set_action('update-string');
        self::validate();

        if(false !== self::$valid) :
            parent::$table = self::$table;

            Capsule::table(self::table())
                ->where([
                    'ID' => parent::$id
                ])
                ->update([
                    'string' => self::$string
                ]);

            self::set_valid(true);
            self::set_respond('license',
                Capsule::table(self::table())
                    ->where('ID', parent::$id)
                    ->first()
            );
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

                if ( !is_null( $value['val'] ) ) :

                    if('user_id' === $value['name']) :
                        $query->where(function($query) use ($value) {
                            $query->where('license.user_id', $value['val']);
                            $query->orWhere('license.user_id', 0);
                        });
                    elseif(is_array($value['val'])) :
                        $query->whereIn( 'license.'.$value['name'], $value['val'] );
                    elseif(isset($value['compare']) && !is_null($value['compare'])) :
                        $query->where( 'license.'.$value['name'], $value['compare'], $value['val']);
                    else :
                        $query->where( 'license.'.$value['name'], $value['val'] );
                    endif;

                endif;

            endforeach;
        endif;

        return $query;
    }

    /**
     * Get order by given column and save to respond
     * @param  string  $column
     * @param  boolean $is_single
     * @return static
     */
    static function get_by($column = 'id', $value = '',$is_single = true) {

        parent::$table = self::$table;

        self::$filter['search'][] = [
            'name'  => $column,
            'val'   => $value
        ];

        $query    = Capsule::table(Capsule::raw(self::table() . ' AS license'));

        $query    = self::set_filter_query( $query );
        $licenses = (false !== $is_single) ? (array) $query->first() : $query->get()->toArray();

        if ( $licenses ) :
            self::set_respond('valid',        true);
            self::set_respond('licenses',     $licenses);
            self::set_respond('recordsTotal', (false !== $is_single) ? $query->count() : 1);
        else:
            self::set_respond('valid',      false);
            self::set_message(sprintf(__('License with %s %s doesn\'t exist', 'sejoli'), $column, $value));
        endif;

        return new static;
    }

    /**
     * Get single data by ID
     * @var [type]
     */
    static public function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS license ') );
        $query        = self::set_filter_query( $query );
        $recordsTotal = $query->count();

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_licenses')) :
        
            $query = Capsule::table( Capsule::raw( self::table() . ' AS license '))
                ->join( Capsule::raw($wpdb->users . ' AS user '), 'user.ID', '=', 'license.user_id')
                ->join( Capsule::raw($wpdb->posts . ' AS product '), 'product.ID', '=', 'license.product_id')
                ->join( Capsule::raw($wpdb->prefix . 'sejolisa_orders AS data_order'), 'data_order.ID', '=', 'license.order_id')
                ->select(
                   Capsule::raw( 'license.ID, license.order_id, license.user_id, license.string, license.status AS subscription_status, license.status AS status, license.product_id, license.code, product.post_title AS product_name, user.display_name AS owner_name' )
                );

        else:

            $query = Capsule::table(Capsule::raw(self::table() . ' AS license'))
                ->select('license.ID', 'license.order_id', 'license.user_id', 'license.string', 'license.status', 'license.product_id', 'license.code', 'product.post_title AS product_name', 'user.display_name AS owner_name')
                ->join($wpdb->users . ' AS user', 'user.ID', '=', 'license.user_id')
                ->join($wpdb->posts . ' AS product', 'product.ID', '=', 'license.product_id')
                ->join($wpdb->prefix . 'sejolisa_orders AS data_order', 'data_order.ID', '=', 'license.order_id')
                ->leftJoin($wpdb->prefix . 'sejolisa_subscriptions AS data_subscription_order', function ($join) {
                    $join->on('license.order_id', '=', 'data_subscription_order.order_id');
                })
                ->leftJoin($wpdb->prefix . 'sejolisa_subscriptions AS data_subscription_parent', function ($join) {
                    $join->on('license.order_id', '=', 'data_subscription_parent.order_parent_id')
                        ->where('data_subscription_parent.order_parent_id', '>', 0);
                })
                ->selectRaw('COALESCE(data_subscription_parent.status, data_subscription_order.status) AS subscription_status');

        endif;

        $query    = self::set_filter_query( $query );
        $query    = self::set_length_query($query);
        $licenses = $query->get()
                            ->toArray();

        if ( $licenses ) :
            self::set_respond('valid',true);
            self::set_respond('licenses', $licenses);
            self::set_respond('recordsTotal', $recordsTotal);
            self::set_respond('recordsFiltered', $recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('licenses', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Update multiple license status
     * @since   1.0.0ze
     * @return  void
     */
    static public function update_status() {

        parent::$table = self::$table;

        $query    = Capsule::table(Capsule::raw(self::table() . ' AS license'));
        $query    = self::set_filter_query($query);
        $response = $query->update([
            'status'     => self::$status,
            'updated_at' => current_time('mysql')
        ]);

        self::set_valid(boolval($response));

        return new static;
    }

    /**
     * Reset multiple license string
     * @since   1.0.0
     * @return  void
     */
    static public function reset_string() {

        parent::$table = self::$table;

        $query    = Capsule::table(Capsule::raw(self::table() . ' AS license'));
        $query    = self::set_filter_query($query);
        $response = $query->update([
            'updated_at' => current_time('mysql'),
            'string'     => ''
        ]);

        self::set_valid(boolval($response));

        return new static;
    }

    /**
     * Get license by order id
     * @since   1.0.0
     */
    static function get_license_by_order_id($order_id) {
        
        global $wpdb;

        $license = array();

        $result = Capsule::table( $wpdb->prefix.self::$table . ' AS license')
            ->select('order_id', 'status')
            ->where('license.order_id', $order_id)
            ->get();

        if($result) :
        
            foreach($result as $_data) :
        
                $license[] = $_data->order_id;
                $license[] = $_data->status;
        
            endforeach;
        
        endif;

        return $license;
    
    }

}