<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Class Affiliate extends \SejoliSA\Model
{
    static protected $ids         = [];
    static protected $table       = 'sejolisa_affiliates';
    static protected $commission  = 0.0;
    static protected $tier        = 1;
    static protected $status      = 'pending';
    static protected $paid_status = 0;
    static protected $paid_time   = '0000-00-00 00:00:00';

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
                $table->integer('affiliate_id');
                $table->integer('product_id');
                $table->integer('tier');
                $table->float('commission', 12, 2);
                $table->string('status', 100)->default('pending');
                $table->tinyInteger('paid_status')->default(0);
            });
        endif;
    }

    /**
     * Set commission ID
     * @var string
     */
    static public function set_id($id) {
        self::$ids = !is_array($id) ? array($id) : $id;
        return new static;
    }

    /**
     * Set multiple IDS
     * @var array
     */
    static public function set_multiple_id($ids) {
        self::$ids = (array) $ids;
        return new static;
    }

    /**
     * Set commission value
     * @var float
     */
    static public function set_commission($commission) {
        self::$commission = floatval($commission);
        return new static;
    }

    /**
     * Set commission tier
     * @var integer
     */
    static public function set_tier($tier) {
        self::$tier = intval($tier);
        return new static;
    }

    /**
     * Set status
     * @var string
     */
    static public function set_status($status) {
        self::$status = (empty($status)) ? 'pending' : $status;
        return new static;
    }

    /**
     * Set paid status
     * @var bool
     */
    static public function set_paid_status($paid_status) {
        self::$paid_status = boolval($paid_status);
        return new static;
    }

    /**
     * Set paid time
     * @since   1.5.1
     * @var string
     */
    static public function set_paid_time($paid_time) {
        self::$paid_time = $paid_time;
        return new static;
    }

    /**
     * Reset properties
     * @var [type]
     */
    static public function reset() {
        parent::reset();

        self::$commission  = 0.0;
        self::$tier        = 1;
        self::$status      = 'pending';
        self::$paid_status = 0;
        self::$ids         = NULL;

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
                self::set_message( __('Affiliasi tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli'));
            endif;

            if(0 === self::$tier) :
                self::set_valid(false);
                self::set_message( __('Tier minimal 1', 'sejoli'));
            endif;

            if(0 === self::$commission) :
                self::set_valid(false);
                self::set_message( __('Komisi tidak boleh 0', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['create', 'update-status', 'update-paid-status'])) :

            if(!in_array(self::$status, ['pending', 'added', 'cancelled'])) :
                self::set_valid(false);
                self::set_message( sprintf(__('Status komisi %s tidak valid', 'sejoli'), self::$status));
            endif;

        endif;

        if(in_array(self::$action, ['update-paid-status'])) :

            if(!is_array(self::$ids)) :
                self::set_valid(false);
                self::set_message( __('Komisi belum dipilih', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['get-affiliate-commission', 'update-single-affiliate-commission'])) :

            if(empty(self::$user_id)) :
                self::set_valid(false);
                self::set_message( __('Affiliate belum diisi', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action,['update-single-affiliate-commission'])) :

            if(!is_bool(self::$paid_status)) :
                self::set_valid(false);
                self::set_message( __('Status pembayaran tidak valid', 'sejoli'));
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

            $commission = [
                'created_at'   => current_time('mysql'),
                'updated_at'   => '0000-00-00 00:00:00',
                'deleted_at'   => '0000-00-00 00:00:00',
                'order_id'     => self::$order_id,
                'product_id'   => self::$product->ID,
                'affiliate_id' => self::$user->ID,
                'tier'         => self::$tier,
                'commission'   => self::$commission,
                'status'       => self::$status,
                'paid_status'  => self::$paid_status
            ];

            $commission['ID'] = Capsule::table(self::table())
                            ->insertGetId($commission);

            self::set_valid(true);
            self::set_respond('commission',$commission);
        endif;

        return new static;
    }

    /**
     * Get commissions by filter
     * @return [type] [description]
     */
    static function get() {
        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS commission' ) )
                            ->select( Capsule::raw('commission.*, user.display_name AS affiliate_name, product.post_title AS product_name') )
                            ->join( $wpdb->posts . ' AS product', 'product.ID', '=', 'commission.product_id')
                            ->join( $wpdb->users . ' AS user', 'user.ID', '=', 'commission.affiliate_id');

        $query        = self::set_filter_query( $query );
        $recordsTotal = $query->count();
        $query        = self::set_length_query($query);
        $commissions  = $query->get()->toArray();

        if ( $commissions ) :
            self::set_respond('valid',true);
            self::set_respond('commissions',$commissions);
            self::set_respond('recordsTotal',$recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('commissions', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get only first commission
     */
    static function first() {
        parent::$table = self::$table;

        $data = Capsule::table(self::$table())
                    ->whereIn('ID', self::$ids)
                    ->first();

        if( $data ) :
            self::set_valid(true);
            self::set_respond('commission', $data);
        else :
            self::set_valid(false);
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
                ->whereIn('ID', self::$ids)
                ->update([
                    'updated_at' => current_time('mysql'),
                    'status'     => self::$status
                ]);

            self::set_valid(true);
            self::set_message(
                sprintf(
                    __('Commission %s status updated to %s successfully', 'sejoli'),
                    implode(", ", self::$ids),
                    self::$status
                ),
                'success');
        endif;

        return new static;
    }

    /**
     * Update order status
     */
    static function update_paid_status() {

        self::set_action('update-paid-status');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            Capsule::table(self::table())
                ->whereIn('ID', self::$ids)
                ->update([
                    'updated_at'  => current_time('mysql'),
                    'paid_status' => self::$paid_status
                ]);
            self::set_valid(true);
            self::set_message( sprintf(__('Commission %s paid status updated to %s successfully', 'sejoli'), implode(',', self::$ids), self::$paid_status), 'success');
        endif;

        return new static;
    }

    /**
     * Set data for chart purpose
     * @since 1.0.0
     */
    static function set_for_chart($type = 'total-order') {

        parent::$table = self::$table;

        self::calculate_chart_range_date();
        $columns = [];

        switch ($type) :
            case 'total-order':
                $columns[] = Capsule::raw('count(ID) AS total');
                break;

            case 'total-paid':
                $columns[] = Capsule::raw('sum(commission) AS total');
                break;

        endswitch;

        $columns[] ='status';

        $groups = ['status'];

        if('year' === self::$chart['type']) :
            $columns[] = Capsule::raw('YEAR(updated_at) AS year');
            $groups[]  = 'year';
        elseif('month' === self::$chart['type']) :
            $columns[] = Capsule::raw('DATE_FORMAT(updated_at, "%Y-%m") AS month');
            $groups[]  = 'month';
        elseif('date' === self::$chart['type']) :
            $columns[] = Capsule::raw('DATE(updated_at) AS date');
            $groups[]  = 'date';
        endif;

        $query = Capsule::table(self::table())
                    ->select($columns);

        $query = self::set_filter_query($query);
        $data  = $query->groupBy($groups)
                    ->get();

        self::set_respond('data' ,$data);
        self::set_respond('chart',self::$chart);

        return new static;
    }

    /**
     * Caculate commission per affiliate
     */
    static public function calculate_commission_per_affiliate($commission_ids) {

        global $wpdb;

        parent::$table = self::$table;

        $query = Capsule::table( Capsule::raw( self::table() . ' AS commission') )
            ->select(
                Capsule::raw(
                    'commission.affiliate_id, SUM(commission.commission) AS total_commission, COUNT(commission.ID) AS total_order, '.
                    'user.display_name AS affiliate_name, '.
                    'user.user_email AS affiliate_email '
                )
            )
            ->join( $wpdb->users . ' AS user', 'user.ID', '=', 'commission.affiliate_id' )
            ->where( 'commission.paid_status', '=', self::$paid_status );


        $data = $query->whereIn( 'commission.ID', $commission_ids )
            ->groupBy( 'commission.affiliate_id' )
            ->get();

        self::set_respond('commissions', $data);

        return new static;
    }

    /**
     * Get total commission
     * @var [type]
     */
    static public function get_total_commission() {

        parent::$table = self::$table;

        $query = Capsule::table( self::table() )
                    ->select( Capsule::raw('SUM(commission) AS total_commission') );

        $query    = self::set_filter_query( $query );
        $response = $query->first();

        self::set_valid(true);
        self::set_respond('total', floatval($response->total_commission));

        return new static;
    }


    /**
     * Get commission status with misplaced order
     * @since   1.1.1
     */
    static public function get_misplaced_commission_status() {

        global $wpdb;

        parent::$table = self::$table;

        $query = Capsule::table( Capsule::raw( self::table() . ' AS commission') )
            ->select('commission.ID')
            ->join( $wpdb->prefix . 'sejolisa_orders AS data_order', 'data_order.ID', '=', 'commission.order_id' )
            ->where( 'commission.status', '!=', 'added' )
            ->where( 'data_order.status', '=', 'completed' );

        $result = $query->get()->toArray();

        if($result) :
            self::set_valid(true);
            self::set_respond('commission_order', $result);
        else :
            self::set_valid(false);
            self::set_respond('commission_order', array());
        endif;

        return new static;
    }

    /**
     * Get all total commission per status
     * @since   1.3.2
     */
    static public function get_total_affiliate_commission_info() {

        global $wpdb;

        parent::$table = self::$table;

        // Count per affiliate
        $query = Capsule::table( Capsule::raw( self::table() . ' AS commission') )
                    ->select(
                        Capsule::raw('SUM(CASE WHEN status = "pending" THEN commission.commission ELSE 0 END) AS pending_commission'),
                        Capsule::raw('SUM(CASE WHEN status = "added" AND paid_status = 0 THEN commission.commission ELSE 0 END) AS unpaid_commission '),
                        Capsule::raw('SUM(CASE WHEN status = "added" AND paid_status = 1 THEN commission.commission ELSE 0 END) AS paid_commission ')
                    );

        $query = self::set_filter_query( $query );

        $query->orderBy('unpaid_commission', 'DESC')
                ->orderBy('paid_commission', 'DESC');

        $result       = $query->first();
        $recordsTotal = is_array($result) ? count($result) : 0;

        if($result) :
            self::set_valid(true);
            self::set_respond('commissions',     $result);
            self::set_respond('recordsTotal',    $recordsTotal);
            self::set_respond('recordsFiltered', $recordsTotal);
        else :
            self::set_valid(false);
            self::set_respond('commissions', array());
            self::set_respond('recordsTotal',    0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get affiliate commission info
     * @since   1.1.3   Initialization
     */
    static public function get_affiliate_commission_info() {

        global $wpdb;

        parent::$table = self::$table;

        // Count per affiliate
        $query = Capsule::table( Capsule::raw( self::table() . ' AS commission') )
                    ->select(
                        'affiliate.ID',
                        'affiliate.display_name',
                        Capsule::raw('SUM(CASE WHEN status = "pending" THEN commission.commission ELSE 0 END) AS pending_commission'),
                        Capsule::raw('SUM(CASE WHEN status = "added" AND paid_status = 0 THEN commission.commission ELSE 0 END) AS unpaid_commission '),
                        Capsule::raw('SUM(CASE WHEN status = "added" AND paid_status = 1 THEN commission.commission ELSE 0 END) AS paid_commission ')
                    )
                    ->join( $wpdb->users . ' AS affiliate', 'affiliate.ID', '=', 'commission.affiliate_id' );

        $query = self::set_filter_query( $query );

        $query->groupBy('commission.affiliate_id')
                ->orderBy('unpaid_commission', 'DESC')
                ->orderBy('paid_commission', 'DESC');

        $result       = $query->get();
        $recordsTotal = count($result);

        if($result) :
            self::set_valid(true);
            self::set_respond('commissions',     $result);
            self::set_respond('recordsTotal',    $recordsTotal);
            self::set_respond('recordsFiltered', $recordsTotal);
        else :
            self::set_valid(false);
            self::set_respond('commissions', array());
            self::set_respond('recordsTotal',    0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get single affiliate commission info
     * @since   1.1.3
     */
    static public function get_single_affiliate_commission_info() {

        global $wpdb;

        self::set_action('get-affiliate-commission');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $query = Capsule::table( Capsule::raw( self::table() . ' AS commission') )
                        ->select(
                            'affiliate.ID',
                            'affiliate.display_name',
                            Capsule::raw('SUM(CASE WHEN status = "pending" THEN commission.commission ELSE 0 END) AS pending_commission'),
                            Capsule::raw('SUM(CASE WHEN status = "added" AND paid_status = 0 THEN commission.commission ELSE 0 END) AS unpaid_commission '),
                            Capsule::raw('SUM(CASE WHEN status = "added" AND paid_status = 1 THEN commission.commission ELSE 0 END) AS paid_commission ')
                        )
                        ->join( $wpdb->users . ' AS affiliate', 'affiliate.ID', '=', 'commission.affiliate_id' );

            $query = self::set_filter_query( $query );

            $result = $query->where('commission.affiliate_id', self::$user_id)
                            ->groupBy('commission.affiliate_id')
                            ->orderBy('unpaid_commission', 'DESC')
                            ->orderBy('paid_commission', 'DESC')
                            ->first();

            if($result) :
                self::set_valid(true);
                self::set_respond('affiliate',     $result);
            else :
                self::set_valid(false);
                self::set_respond('affiliate', array());
            endif;

        endif;

        return new static;
    }

    /**
     * Update all single affiliate commission paid status
     * @since   1.1.3
     * @since   1.5.2   Add added status to commission that has been paid
     */
    static public function update_single_affiliate_commission_paid_status() {

        global $wpdb;

        self::set_action('update-single-affiliate-commission');
        self::validate();

        if(true === self::$valid) :

            parent::$table = self::$table;

            $query = Capsule::table( self::table() )
                        ->where('affiliate_id', self::$user_id)
                        ->where('updated_at', '<=', self::$paid_time)
                        ->where('status', 'added');

            $query = self::set_filter_query( $query );

            $result = $query->update(array(
                        'paid_status' => self::$paid_status
                    ));

            if($result) :
                self::set_valid(true);
            else :
                self::set_valid(false);
            endif;

        endif;

        return new static;
    }
}
