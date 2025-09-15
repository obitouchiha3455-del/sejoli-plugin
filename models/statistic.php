<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

Class Statistic extends \SejoliSA\Model
{
    static protected $product_id        = false;
    static protected $user_id           = false;
    static protected $affiliate_id      = false;
    static protected $order_status      = false;
    static protected $commission_status = false;
    static protected $start_date        = false;
    static protected $end_date          = false;
    static protected $tier              = 1;
    static protected $count_by          = 'order';
    static protected $sort              = 'desc';
    static protected $limit             = 10;

    /**
     * Reset all properties
     */
    static public function reset() {

        self::$affiliate_id      = NULL;
        self::$product_id        = false;
        self::$user_id           = false;
        self::$order_status      = false;
        self::$commission_status = false;
        self::$count_by          = 'order';
        self::$start_date        = false;
        self::$end_date          = false;
        self::$tier              = 1;
        self::$limit             = 10;

        return new static;
    }

    /**
     * Set multiple product id
     */
    static public function set_product($product_id) {

        if(!empty($product_id)) :
            self::$product_id = (array) $product_id;
        endif;

        return new static;
    }

    /**
     * Set multiple user id
     */
    static public function set_user($user_id) {

        if(!empty($user_id)) :
            self::$user_id = (array) $user_id;
        endif;

        return new static;
    }

    /**
     * Set multiple affiliate id
     */
    static public function set_affiliate($affiliate_id) {

        if(!empty($affiliate_id)) :
            self::$affiliate_id = (array) $affiliate_id;
        endif;

        return new static;
    }

    /**
     * Set order_status
     */
    static public function set_order_status($order_status) {

        if(!empty($order_status)) :
            self::$order_status = (array) $order_status;
        endif;

        return new static;
    }

    /**
     * Set commission_status
     */
    static public function set_commission_status($commission_status) {

        if(!empty($commission_status)) :
            self::$commission_status = (array) $commission_status;
        endif;

        return new static;
    }

    /**
     * Set count by
     */
    static public function set_count_by($count_by) {

        if( in_array($count_by, ['order', 'omset', 'quantity', 'total']) ) :
            self::$count_by = $count_by;
        endif;

        return new static;
    }

    /**
     * Set start date
     */
    static public function set_start_date($start_date) {

        if(!empty($start_date)) :
            self::$start_date = $start_date;
        endif;

        return new static;
    }

    /**
     * Set end date
     */
    static public function set_end_date($end_date) {

        if(!empty($end_date)) :
            self::$end_date = $end_date;
        endif;

        return new static;
    }

    /**
     * Set tier
     */
    static public function set_tier($tier) {

        if(!empty($tier)) :
            self::$tier = absint($tier);
        endif;

        return new static;
    }

    /**
     * Set sort
     */
    static public function set_sort($sort) {

        if( in_array($sort, ['desc', 'asc']) ) :
            self::$sort = $sort;
        endif;

        return new static;
    }

    /**
     * Set limit
     */
    static public function set_limit($limit) {

        if(!empty($limit)) :
            self::$limit = absint($limit);
        endif;

        return new static;
    }

    /**
     * Set filter query
     * @var [type]
     */
    static protected function set_statistic_filter_query( $query, $table) {

        if(is_array(self::$product_id)) :
            $query->whereIn($table . '.product_id', self::$product_id);
        endif;

        if(is_array(self::$affiliate_id)) :
            $query->whereIn($table . '.affiliate_id', self::$affiliate_id);
        endif;

        if(is_array(self::$user_id)) :
            $query->whereIn('data_order.user_id', self::$user_id);
        endif;

        if(is_array(self::$order_status)) :
            $query->whereIn('data_order.status', self::$order_status);
        endif;

        if(is_array(self::$commission_status)) :
            $query->whereIn('commission.status', self::$commission_status);
        endif;

        if(!empty(self::$start_date)) :
            $query->where($table.'.created_at', '>=', self::$start_date);
        endif;

        if(!empty(self::$end_date)) :
            $query->where($table.'.created_at', '<=', self::$end_date);
        endif;

        return $query;
    }

    /**
     * Calculate by affiliate data
     * @var [type]
     */
    static public function calculate_by_affiliate() {

        global $wpdb;

        $select = "user.ID, user.display_name AS user_name";

        if('order' === self::$count_by) :
            $select .= ', COUNT(commission.ID) AS total';
        else :
            $select .= ', SUM(commission.commission) AS total';
        endif;

        $query = Capsule::table($wpdb->prefix . 'sejolisa_affiliates AS commission')
                    ->select( Capsule::raw($select) )
                    ->join( $wpdb->users . ' AS user', 'user.ID', '=', 'commission.affiliate_id')
                    ->join( $wpdb->prefix . 'sejolisa_orders AS data_order', 'data_order.ID', '=', 'commission.order_id');

        $query = self::set_statistic_filter_query($query, 'commission');

        $query->limit( self::$limit );

        $response = $query->groupBy('commission.affiliate_id')
                    ->orderBy('total', self::$sort)
                    ->get();

        self::set_respond('statistic', $response);

        return new static;
    }

    /**
     * Calculate by product data
     * @var [type]
     */
    static public function calculate_by_product() {

        global $wpdb, $pagenow;

        $select = "product.ID, product.post_title AS product_name";

        if('quantity' === self::$count_by) :
            $select .= ', SUM(data_order.quantity) AS total';
        elseif('order' === self::$count_by) :
            $select .= ', COUNT(data_order.ID) AS total';
        else :
            $select .= ', SUM(data_order.grand_total) AS total';
        endif;

        $query = Capsule::table($wpdb->prefix . 'sejolisa_orders AS data_order')
                    ->select( Capsule::raw($select) )
                    ->join($wpdb->posts . ' AS product', 'product.ID', '=', 'data_order.product_id');

        $query = self::set_statistic_filter_query($query, 'data_order');

        $query = $query->groupBy('data_order.product_id')
                    ->orderBy('total', self::$sort);

        $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : null;
        if ( 'edit.php' !== $pagenow && !isset( $post_type ) && 'sejoli-product' !== get_post_type( $post_type ) ) :
            
            $query->limit( self::$limit );

        endif;

        $response = $query->get();

        self::set_respond('statistic', $response);

        return new static;
    }

    /**
     * Calculate by buyer data
     * @var [type]
     */
    static public function calculate_by_buyer() {

        global $wpdb;

        $select = "buyer.ID, buyer.display_name AS user_name";

        if('quantity' === self::$count_by) :
            $select .= ', SUM(data_order.quantity) AS total';
        elseif('order' === self::$count_by) :
            $select .= ', COUNT(data_order.ID) AS total';
        else :
            $select .= ', SUM(data_order.grand_total) AS total';
        endif;

        $query = Capsule::table($wpdb->prefix . 'sejolisa_orders AS data_order')
                    ->select( Capsule::raw($select) )
                    ->join($wpdb->users . ' AS buyer', 'buyer.ID', '=', 'data_order.user_id');

        $query = self::set_statistic_filter_query($query, 'data_order');

        $query->limit( self::$limit );

        $response = $query->groupBy('data_order.user_id')
                    ->orderBy('total', self::$sort)
                    ->get();

        self::set_respond('statistic', $response);

        return new static;
    }

    /**
     * Calculate commission by product
     * @since   1.0.0
     */
    static public function calculate_commission_by_product() {

        global $wpdb;

        $query = Capsule::table(Capsule::raw( $wpdb->prefix . 'sejolisa_affiliates AS commission'))
                    ->select(
                        Capsule::raw("product.ID, product.post_title AS product_name, SUM(commission.commission) AS total")
                      )
                    ->join($wpdb->posts . ' AS product', 'product.ID', '=', 'commission.product_id');

        $query    = self::set_statistic_filter_query($query, 'commission');
        $query->limit( self::$limit );
        $response = $query->groupBy('commission.product_id')
                        ->orderBy('total', self::$sort)
                        ->get();

        self::set_respond('statistic', $response);

        return new static;
    }
}
