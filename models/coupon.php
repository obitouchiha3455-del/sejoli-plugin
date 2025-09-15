<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Class Coupon extends \SejoliSA\Model
{
    static protected $table            = 'sejolisa_coupons';
    static protected $coupon_parent_id = 0;
    static protected $code             = NULL;
    static protected $rule             = [];
    static protected $discount         = [
        'value' => NULL,
        'type'  => 'fixed',
        'usage' => 'per_item'
    ];
    static protected $usage            = 0;
    static protected $limit_use        = 0;
    static protected $limit_date       = NULL;
    static protected $status           = 'pending';

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
                $table->integer('user_id');
                $table->integer('coupon_parent_id')->default(0);
                $table->string('code');
                $table->text('rule');           // data will be serialized
                $table->string('discount');     // data will be serialized
                $table->integer('usage')->default(0);
                $table->integer('limit_use')->default(0);
                $table->datetime('limit_date')->nullable();
                $table->string('status');
            });
        endif;
    }

    /**
     * Set coupon parent id value
     */
    static public function set_coupon_parent_id($coupon_parent_id) {
        self::$coupon_parent_id = absint($coupon_parent_id);
        return new static;
    }

    /**
     * Set coupon code
     */
    static public function set_code($code) {
        self::$code = strtoupper(sanitize_title($code));
        return new static;
    }

    /**
     * Set rule data, must be an array
     */
    static public function set_rule(array $rule) {
        self::$rule = $rule;
        return new static;
    }

    /**
     * Set discount data, must be an array
     */
    static public function set_discount(array $discount) {
        self::$discount = wp_parse_args($discount,[
            'value' => NULL,
            'type'  => 'fixed',
            'usage' => 'per_item'
        ]);

        self::$discount['value'] = floatval(self::$discount['value']);
        return new static;
    }

    /**
     * Set coupon usage
     */
    static public function set_usage($usage) {
        self::$usage = absint($usage);
        return new static;
    }

    /**
     * Set limit use
     */
    static public function set_limit_use($limit_use) {
        self::$limit_use = absint($limit_use);
        return new static;
    }

    /**
     * Set limit date
     */
    static public function set_limit_date($limit_date) {

        if(!empty($limit_date)) :
            self::$limit_date = strtotime($limit_date);
        endif;

        return new static;
    }

    /**
     * Set coupon status
     */
    static public function set_status($status) {
        self::$status = $status;
        return new static;
    }

    /**
     * Reset properties
     */
    static public function reset() {
        parent::reset();

        self::$code       = NULL;
        self::$discount   = NULL;
        self::$rule       = NULL;
        self::$usage      = 0;
        self::$limit_use  = 0;
        self::$limit_date = NULL;

        return new static;
    }

    /**
     * Validate based on action
     */
    static public function validate() {

        if(in_array(self::$action, ['single'] )) :

            if(empty(self::$code) && empty(parent::$id) ) :
                self::set_valid(false);
                self::set_message( __('Kode kupon belum diisi', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['create'])) :

            if(empty(self::$code)) :
                self::set_valid(false);
                self::set_message( __('Kode kupon belum diisi', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['update', 'update-status', 'update-total-usage', 'update-usage'])) :

            if(empty(self::$id)) :
                self::set_valid(false);
                self::set_message( __('Coupon ID kosong', 'sejoli'));
            endif;
        endif;

        if(in_array(self::$action, ['delete'])) :

            if(!is_array(self::$ids) || 0 === count(self::$ids)) :
                self::set_valid(false);
                self::set_message( __('Coupon ID kosong', 'sejoli'));
            endif;
        endif;

        if(in_array(self::$action, ['create', 'update', 'update-status'])) :

            if(!in_array(self::$status, ['pending', 'active', 'need-approve'])) :
                self::set_valid(false);
                self::set_message( sprintf(__('Status %s tidak terdaftar', 'sejoli'), self::$status) );
            endif;

        endif;

        if(in_array(self::$action, ['create', 'update'])) :

            // If is child coupon or update
            if(empty(self::$coupon_parent_id)) :

                if (!is_array(self::$discount) || !isset(self::$discount['value'])) :
                    self::set_valid(false);
                    self::set_message( __('Nilai discount belum diisi', 'sejoli') );
                endif;

                // Validate limit date
                $current_time = current_time('timestamp');

                if(!empty(self::$limit_date) && $current_time > self::$limit_date) :
                    self::set_valid(false);
                    self::set_message( __('Tanggal batas aktif tidak bisa lebih rendah dari sekarang', 'sejoli') );
                endif;

            endif;

        endif;

        if(in_array(self::$action, ['update-total-usage', 'update-usage'])) :

            if(absint(self::$usage) < 0) :
                self::set_valid(false);
                self::set_message( __('Total penggunaan tidak boleh kosong', 'sejoli') );
            endif;
        endif;

        if(in_array(self::$action, ['get-total-affiliate-coupon'])) :

            if(empty(self::$coupon_parent_id)) :
                self::set_valid(false);
                self::set_message( __('Kupon utama belum diisi', 'sejoli') );
            endif;

            if(empty(self::$user_id)) :
                self::set_valid(false);
                self::set_message( __('User belum diisi', 'sejoli') );
            endif;
        endif;
    }

    /**
     * Check parent coupon data
     * @since 1.1.0
     */
    static protected function check_parent_coupon($coupon) {

        if(0 !== intval($coupon['coupon_parent_id'])) :

            parent::$table = self::$table;

            $parent_coupon  = Capsule::table(self::table())
                                ->where('ID', $coupon['coupon_parent_id'])
                                ->where(function($query){
                                    $query->where('deleted_at', '0000-00-00 00:00:00')
                                        ->orWhereNull('deleted_at');
                                })
                                ->first();

            // coupon parent data valid
            if($parent_coupon) :

                $parent_coupon        = (array) $parent_coupon;
                $coupon['rule']       = $parent_coupon['rule'];
                $coupon['discount']   = $parent_coupon['discount'];
                $coupon['limit_date'] = $parent_coupon['limit_date'];
                $coupon['limit_use']  = $parent_coupon['limit_use'];
                $coupon['status']     = 'pending' === $parent_coupon['status'] ? 'pending' : $coupon['status'];

                return $coupon;
            else :

                return false;

            endif;

        endif;

        return $coupon;
    }

    /**
     * Translate data
     */
    static protected function translate($coupon) {
        $coupon['code']     = strtoupper($coupon['code']);
        $coupon['rule']     = maybe_unserialize($coupon['rule']);
        $coupon['discount'] = maybe_unserialize($coupon['discount']);

        return $coupon;
    }

    /**
     * Create coupon
     */
    static public function create() {
        self::$action = 'create';
        self::validate();

        if(false !== self::$valid) :
            parent::$table = self::$table;
            $coupon = [
                'code'             => self::$code,
                'created_at'       => current_time('mysql'),
                'updated_at'       => '0000-00-00 00:00:00',
                'deleted_at'       => '0000-00-00 00:00:00',
                'user_id'          => self::$user_id,
                'coupon_parent_id' => self::$coupon_parent_id,
                'rule'             => serialize(self::$rule),
                'discount'         => serialize(self::$discount),
                'limit_use'        => self::$limit_use,
                'status'           => self::$status,
            ];

            if(!is_null(self::$limit_date)) :
                $coupon['limit_date'] = date('Y-m-d H:i:s', self::$limit_date);
            endif;

            $coupon['ID']  = Capsule::table(self::table())
                                ->insertGetId($coupon);

            if($coupon) :
                self::set_valid(true);
                self::set_respond('coupon', self::translate($coupon));
            else :
                self::set_valid(false);
                self::set_respond('coupon', self::translate($coupon));
            endif;

        endif;

        return new static;
    }

    /**
     * Update coupon
     */
    static public function update() {
        self::$action = 'update';
        self::validate();

        if(false !== self::$valid) :
            parent::$table = self::$table;

            $coupon = [
                'updated_at' => current_time('mysql'),
                'rule'       => serialize(self::$rule),
                'discount'   => serialize(self::$discount),
                'limit_use'  => self::$limit_use,
                'status'     => self::$status
            ];


            if(!is_null(self::$limit_date)) :
                $coupon['limit_date'] = date('Y-m-d H:i:s', self::$limit_date);
            else:
                $coupon['limit_date'] = NULL;
            endif;

            Capsule::table(self::table())
                ->where([
                    'ID' => self::$id
                ])
                ->update($coupon);

            $coupon = (array) Capsule::table(self::table())
                                ->where(['ID' => self::$id])
                                ->first();

            if($coupon) :
                self::set_valid(true);
                self::set_respond('coupon', self::translate($coupon));
            else :
                self::set_valid(false);
            endif;

        endif;

        return new static;
    }

    /**
     * Delete coupon
     */
    static public function delete() {
        self::$action = 'delete';
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            Capsule::table(self::table())
                ->whereIn('ID', self::$ids)
                ->update(array(
                    'deleted_at'    => current_time('mysql')
                ));

            self::set_valid( true );
            self::set_message( sprintf( __('Kupon ID %s telah dihapus', 'sejoli'), implode(', ', self::$ids)), 'success');
        endif;

        return new static;
    }

    /**
     * Update coupon status
     */
    static public function update_status() {
        self::$action = 'update-status';
        self::validate();

        if(false !== self::$valid) :
            parent::$table = self::$table;

            Capsule::table(self::table())
                ->where([
                    'ID'      => self::$id,
                    'user_id' => self::$user_id
                ])
                ->update([
                    'updated_at' => current_time('mysql'),
                    'status'     => self::$status,
                ]);

            $coupon = (array) Capsule::table(self::table())
                                ->where([
                                    'ID'      => self::$id,
                                    'user_id' => self::$user_id
                                ])
                                ->first();

            if($coupon) :
                self::set_valid(true);
                self::set_respond('coupon', self::translate($coupon));
            else :
                self::set_valid(false);
            endif;

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

        $query    = Capsule::table(Capsule::raw(self::table() . ' AS coupon'));
        $query    = self::set_filter_query($query);
        $response = $query->update([
            'status'     => self::$status,
            'updated_at' => current_time('mysql')
        ]);

        self::set_valid(boolval($response));

        return new static;
    }

    /**
     * Update coupon usage
     */
    static public function update_usage() {
        // $able_to_use = true;
        //
        // if(!is_null(self::$limit_date)) :
        //     $current    = current_time('timestamp');
        //     $limit_date = strtotime(self::$limit_date);
        //
        //     if($current > $limit_date) :
        //         $able_to_use = false;
        //         self::set_message( __('Penggunaan kupon sudah melewati batas waktu', 'sejoli') );
        //     endif;
        // endif;
        //
        // if(0 < self::$limit_use && self::$usage >= self::$limit_use) :
        //     $able_to_use = false;
        //     self::set_message( __('Penggunaan kupon sudah melewati batas pemakaian', 'sejoli') );
        // endif;
        //
        // if(false !== $able_to_use) :

        self::$action = 'update-usage';
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $result = Capsule::table(self::table())
                ->where([ 'ID' => self::$id ])
                ->update([
                    'updated_at' => current_time('mysql'),
                    'usage'      => self::$usage + 1
                ]);

            self::set_valid(boolval($result));

        endif;

        return new static;
    }

    /**
     * Set filter data to query
     */
    static protected function set_filter_query($query){
        if ( !is_null( self::$filter['search'] ) && is_array( self::$filter['search'] ) ) :
            foreach ( self::$filter['search'] as $key => $value ) :

                if ( !is_null( $value['val'] ) ) :

                    if('user_id' === $value['name']) :
                        $query->where(function($query) use ($value) {
                            $query->where('coupon.user_id', $value['val']);
                            $query->orWhere('coupon.user_id', 0);
                        });
                    elseif(is_array($value['val'])) :
                        $query->whereIn( 'coupon.'.$value['name'], $value['val'] );
                    elseif(isset($value['compare']) && !is_null($value['compare'])) :
                        $query->where( 'coupon.'.$value['name'], $value['compare'], $value['val']);
                    else :
                        $query->where( 'coupon.'.$value['name'], $value['val'] );
                    endif;

                endif;

            endforeach;
        endif;

        return $query;
    }

    /**
     * Get single data by ID
     * @var [type]
     */
    static public function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS coupon ') );
        $query        = self::set_filter_query( $query );

        $recordsTotal = $query->count();

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS coupon '))
                            ->leftJoin( Capsule::raw($wpdb->users . ' AS user '), 'user.ID', '=', 'coupon.user_id')
                            ->leftJoin( Capsule::raw( self::table() . ' AS parent_coupon '), 'parent_coupon.ID', '=', 'coupon.coupon_parent_id')
                            ->select(
                                Capsule::raw(
                                    implode(', ', array(
                                        'coupon.* ',
                                        'parent_coupon.code AS parent_code',
                                        'parent_coupon.discount AS parent_discount',
                                        'parent_coupon.limit_date AS parent_limit_date',
                                        'parent_coupon.limit_use AS parent_limit_use',
                                        'user.display_name AS owner_name'
                                    ))
                                )
                            )
                            ->where(function($query){
                                $query->where('coupon.deleted_at', '0000-00-00 00:00:00')
                                    ->orWhereNull('coupon.deleted_at');
                            })
                            ->where(function($query){
                                $query->where('parent_coupon.deleted_at', '0000-00-00 00:00:00')
                                    ->orWhereNull('parent_coupon.deleted_at');
                            });

        $query        = self::set_filter_query( $query );
        $query        = self::set_length_query($query);

        $coupons      = $query->get()
                            ->toArray();

        if ( $coupons ) :
            $temp = [];

            foreach($coupons as $coupon) :
                // $coupon = self::check_parent_coupon((array) $coupon);
                $temp[] = self::translate((array) $coupon);
            endforeach;

            $coupons = $temp;

            self::set_respond('valid',true);
            self::set_respond('coupons',$coupons);
            self::set_respond('recordsTotal',$recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('coupons', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get single data by coupon code
     */
    static public function single() {

        self::$action = 'single';
        self::validate();

        if(false !== self::$valid) :
            parent::$table = self::$table;

            $column = (empty(self::$code)) ? 'ID' : 'code';
            $value  = (empty(self::$code)) ? parent::$id : self::$code;
            $query  = Capsule::table(self::table())
                        ->where($column, $value)
                        ->where(function($query){
                            $query->where('deleted_at', '0000-00-00 00:00:00')
                                ->orWhereNull('deleted_at');
                        });

            $coupon = $query->first();

            if($coupon) :

                $coupon = (array) $coupon;
                $coupon = self::check_parent_coupon($coupon);

                if(false !== $coupon) :
                    self::set_valid(true);
                    self::set_respond('coupon', self::translate($coupon));
                else :
                    self::set_valid(false);
                    self::set_message( __('Kupon ini tidak valid untuk digunakan', 'sejoli'));
                endif;
            else :
                self::set_valid(false);
            endif;

        endif;

        return new static;
    }

    /**
     * Update   total usage coupon
     * @since   1.1.4
     */
    static public function update_total_usage() {

        self::$action = 'update-total-usage';
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $result = Capsule::table(self::table())
                    ->where([ 'ID' => self::$id ])
                    ->update([
                        'usage'      => self::$usage
                    ]);

            if($result) :
                self::set_valid(true);
            else :
                self::set_valid(false);
            endif;

        endif;

        return new static;
    }

    /**
     * Get total use for all coupons
     * @since   1.1.4
     */
    static public function get_total_use_all_coupons() {

        global $wpdb;

        $result  = Capsule::table( Capsule::raw( $wpdb->prefix . 'sejolisa_orders AS data_order '))
                        ->select(
                            'coupon_id',
                            Capsule::raw( 'COUNT(data_order.coupon_id) AS total_use ' )
                        )
                        ->where('coupon_id', '!=', 0)
                        ->groupBy('coupon_id')
                        ->orderBy('total_use', 'DESC')
                        ->get();

        if($result) :
            self::set_valid(true);
            self::set_respond('coupons', $result);
        else :
            self::set_valid(false);
            self::set_respond('coupons', array());
        endif;

        return new static;
    }

    /**
     * Get total affiliate coupon
     * @since   1.1.9
     */
    static public function get_total_affiliate_coupon() {

        self::$action = 'get-total-affiliate-coupon';
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $count = Capsule::table( self::table() )
                        ->where('user_id', self::$user_id)
                        ->where('coupon_parent_id', self::$coupon_parent_id)
                        ->where(function($query){
                            $query->where('deleted_at', '0000-00-00 00:00:00')
                                ->orWhereNull('deleted_at');
                        })
                        ->count();

            self::set_valid(true);
            self::set_respond('total', absint($count));
        endif;

        return new static;
    }
}
