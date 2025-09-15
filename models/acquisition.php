<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Class Acquisition extends \SejoliSA\Model
{
    static protected $data_id      = NULL;
    static protected $table        = 'sejolisa_acquisition';
    static protected $order_table  = 'sejolisa_acquisition_order';
    static protected $affiliate_id = 0;
    static protected $product_id   = 0;
    static protected $date         = NULL;
    static protected $source       = '';
    static protected $media        = '';
    static protected $data = [
        'view'  => 0,
        'lead'  => 0,
        'sales' => 0
    ];

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
                $table->date('created_at');
                $table->integer('affiliate_id');
                $table->integer('product_id');
                $table->string('source');
                $table->string('media');
                $table->integer('view')->default(0);
                $table->integer('lead')->default(0);
                $table->integer('sales')->default(0);
            });
        endif;

        parent::$table = self::$order_table;

        if(!Capsule::schema()->hasTable( self::table() )):
            Capsule::schema()->create( self::table(), function($table){
                $table->integer('order_id');
                $table->string('source');
                $table->string('media');
                $table->primary('order_id');
            });
        endif;
    }

    /**
     * Reset all properties data
     * @since   1.0.0
     */
    static public function reset() {

        self::$affiliate_id = 0;
        self::$product_id   = 0;
        self::$date         = date('Y-m-d');
        self::$source       = '';
        self::$media        = '';

        return new static;
    }

    /**
     * Set affiliate ID
     * @since   1.0.0
     * @param   integer     $affiliate_id   The ID of affiliate
     */
    static public function set_affiliate($affiliate_id) {
        self::$affiliate_id = absint($affiliate_id);
        return new static;
    }

    /**
     * Set date data
     * @since   1.0.0
     * @param   string  $date   Date value
     */
    static public function set_date($date) {
        self::$date = $date;
        return new static;
    }

    /**
     * Set source data
     * @since   1.0.0
     * @param   string  $source   Source value
     */
    static public function set_source($source) {
        self::$source = $source;
        return new static;
    }

    /**
     * Set media data
     * @since   1.0.0
     * @param   string  $media   Source value
     */
    static public function set_media($media) {
        self::$media = $media;
        return new static;
    }

    /**
     * Validate data
     * @since   1.0.0
     */
    static protected function validate() {

        if(in_array(self::$action, ['update', 'add', 'get'])) :
            if(empty(self::$affiliate_id)) :
                self::set_valid(false);
                self::set_message(__('ID affiliasi tidak valid', 'sejoli'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli'));
            endif;

            if(empty(self::$date)) :
                self::set_valid(false);
                self::set_message(__('Tanggal tidak boleh kosong', 'sejoli'));
            endif;

            if(empty(self::$source)) :
                self::set_valid(false);
                self::set_message(__('Source tidak boleh kosong', 'sejoli'));
            endif;

            if(empty(self::$media)) :
                self::set_valid(false);
                self::set_message(__('Media tidak boleh kosong', 'sejoli'));
            endif;
        endif;

        if(in_array(self::$action, ['add-order'])) :

            if(empty(self::$order_id)) :
                self::set_valid(false);
                self::set_message(__('ID order tidak valid', 'sejoli'));
            endif;

        endif;
    }

    /**
     * Get single data
     * @since   1.0.0
     */
    static public function get_single() {

        self::set_action('get');
        self::set_valid(true);
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $response   = Capsule::table(self::table())
                            ->where('affiliate_id', self::$affiliate_id)
                            ->where('created_at', self::$date)
                            ->where('product_id', self::$product->ID)
                            ->where('source', self::$source)
                            ->where('media', self::$media)
                            ->first();

            if($response) :
                self::set_valid(true);
                self::set_respond('data', $response);
                self::$data = wp_parse_args([
                    'view'  => $response->view,
                    'lead'  => $response->lead,
                    'sales' => $response->sales,
                ],self::$data);
                self::$data_id = $response->ID;
            else :
                self::set_valid(false);
            endif;
        endif;

        return new static;

    }

    /**
     * Add new data
     * @since   1.0.0
     */
    static public function add() {

        self::set_action('add');
        self::set_valid(true);
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $args       = [
                'affiliate_id'  => self::$affiliate_id,
                'product_id'    => self::$product->ID,
                'created_at'    => date('Y-m-d'),
                'source'        => self::$source,
                'media'         => self::$media
            ];

            $response   = Capsule::table(self::table())
                            ->insertGetId($args);


            if($response) :
                self::set_valid(true);
                $args['ID'] = self::$data_id = $response;
                self::set_respond('data', $args);
            else :
                self::set_valid(false);
                self::set_message(__('Cant create new data', 'sejoli'));
            endif;

        endif;

        return new static;

    }

    /**
     * Update view value
     * @since   1.0.0
     */
    static public function update_view() {

        self::set_action('update');
        self::set_valid(true);
        self::validate();

        if(false !== self::$valid) :

            self::get_single();

            if(false === self::$valid) :
                self::add();
            endif;

            if(!empty(self::$data_id)) :

                parent::$table = self::$table;

                $response = Capsule::table(self::table())
                                ->where('ID', self::$data_id)
                                ->update([
                                    'view'  => self::$data['view'] + 1
                                ]);

                if($response) :
                    self::set_valid(true);
                    self::set_message(__('View value updated', 'sejolisa'), 'info');
                else :
                    self::set_valid(false);
                    self::set_message(__('Cant update the value', 'sejolisa'));
                endif;
            endif;
        endif;

        return new static;
    }

    /**
     * Update lead value
     * @since   1.0.0
     */
    static public function update_lead() {

        self::set_action('update');
        self::set_valid(true);
        self::validate();

        if(false !== self::$valid) :

            self::get_single();

            if(false === self::$valid) :
                self::add();
            endif;

            if(!empty(self::$data_id)) :

                parent::$table = self::$table;

                $response = Capsule::table(self::table())
                                ->where('ID', self::$data_id)
                                ->update([
                                    'lead'  => self::$data['lead'] + 1
                                ]);

                if($response) :
                    self::set_valid(true);
                    self::set_message(__('Lead value updated', 'sejolisa'), 'info');
                else :
                    self::set_valid(false);
                endif;
            else :
                self::set_valid(false);
                self::set_message(__('Duh 2', 'sejoli'));
            endif;
        else :
            self::set_message(__('Duh', 'sejoli'));
        endif;

        return new static;
    }

    /**
     * Update sales value
     * @since   1.0.0
     */
    static public function update_sales() {

        self::set_action('update');
        self::set_valid(true);
        self::validate();

        if(false !== self::$valid) :

            self::get_single();

            if(false === self::$valid) :
                self::add();
            endif;

            if(!empty(self::$data_id)) :

                parent::$table = self::$table;

                $response = Capsule::table(self::table())
                                ->where('ID', self::$data_id)
                                ->update([
                                    'sales'  => self::$data['sales'] + 1
                                ]);

                if($response) :
                    self::set_valid(true);
                    self::set_message(__('Sales value updated', 'sejolisa'), 'info');
                else :
                    self::set_valid(false);
                endif;
            endif;
        endif;

        return new static;
    }

    /**
     * Add order acquisition
     * @since   1.0.0
     */
    static public function add_order() {

        self::set_action('add-order');
        self::set_valid(true);
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$order_table;

            $args = [
                'order_id' => self::$order_id,
                'source'   => self::$source,
                'media'    => self::$media
            ];

            $response = Capsule::table(self::table())
                            ->insertGetId($args);

            if($response) :
                self::set_valid(true);
                self::set_message(__('Order acquisition added', 'sejolisa'), 'info');
            else :
                self::set_valid(false);
            endif;

        endif;

        return new static;
    }

    /**
     * Get acquisition statistic data
     * @since   1.0.0
     * @return  void
     */
    static function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS acq ' ) )
                            ->select(Capsule::raw(
                                'acq.source, SUM(acq.view) AS total_view, SUM(acq.lead) AS total_lead, SUM(acq.sales) AS total_sales'
                            ));


        $query        = self::set_filter_query( $query );
        $acquisitions = $query->groupBy('source')
                            ->orderBy('source')
                            ->get()
                            ->toArray();

        if ( $acquisitions ) :
            self::set_respond('valid', true);
            self::set_respond('acquisitions', $acquisitions);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        else:
            self::set_respond('valid', false);
            self::set_respond('acquisitions', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get acquisisiton total order statistic data
     * @since   1.0.0
     * @return  void;
     */
    static public function get_total_order() {
        global $wpdb;

        parent::$table = self::$order_table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS acq_order ' ) )
                            ->select(Capsule::raw(
                                'acq_order.source, SUM(data_order.grand_total) AS total_order '
                            ))
                            ->join(Capsule::raw($wpdb->prefix . 'sejolisa_orders AS data_order'), 'data_order.ID', '=', 'acq_order.order_id');


        $query        = self::set_filter_query( $query );
        $acquisitions = $query->groupBy('source')
                            ->orderBy('source')
                            ->get()
                            ->toArray();

        if ( $acquisitions ) :
            self::set_respond('valid', true);
            self::set_respond('acquisitions', $acquisitions);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        else:
            self::set_respond('valid', false);
            self::set_respond('acquisitions', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get acquisition statistic member data
     * @since   1.0.0
     * @return  void
     */
    static function get_member() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS acq ' ) )
                            ->select(Capsule::raw(
                                'acq.source, acq.media, SUM(acq.view) AS total_view, SUM(acq.lead) AS total_lead, SUM(acq.sales) AS total_sales'
                            ));


        $query        = self::set_filter_query( $query );
        $acquisitions = $query->groupBy('source')
                            ->groupBy('media')
                            ->orderBy('source')
                            ->get()
                            ->toArray();

        if ( $acquisitions ) :
            self::set_respond('valid', true);
            self::set_respond('acquisitions', $acquisitions);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        else:
            self::set_respond('valid', false);
            self::set_respond('acquisitions', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Get acquisisiton total member order statistic data
     * @since   1.0.0
     * @return  void;
     */
    static public function get_total_member_order() {
        global $wpdb;

        parent::$table = self::$order_table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS acq_order ' ) )
                            ->select(Capsule::raw(
                                'acq_order.source, acq_order.media, SUM(data_order.grand_total) AS total_order '
                            ))
                            ->join(Capsule::raw($wpdb->prefix . 'sejolisa_orders AS data_order'), 'data_order.ID', '=', 'acq_order.order_id');


        $query        = self::set_filter_query( $query );
        $acquisitions = $query->groupBy('source')
                            ->groupBy('media')
                            ->orderBy('source')
                            ->get()
                            ->toArray();

        if ( $acquisitions ) :
            self::set_respond('valid', true);
            self::set_respond('acquisitions', $acquisitions);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        else:
            self::set_respond('valid', false);
            self::set_respond('acquisitions', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }
}
