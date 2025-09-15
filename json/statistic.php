<?php
namespace SejoliSA\JSON;

use Carbon\Carbon;

Class Statistic extends \SejoliSA\JSON
{
    /**
     * Affiliate ID
     * @since   1.0.0
     * @var     null|integer
     */
    protected $affiliate_id = NULL;

    /**
     * Range of date
     * @since   1.0.0
     * @var     null|array
     */
    protected $date_range   = NULL;

    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Get commission dat
     * Hooked via wp_ajax_sejoli-statistic-commission, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function get_commission_data() {
        $response = [];

        $post_data = wp_parse_args($_REQUEST,[
            'nonce' => false,
            'product_id'        => NULL,
            'affiliate_id'      => NULL,
            'calculate'         => 'order',
            'order_status'      => ['completed'],
            'commission_status' => NULL,
            'start_date'        => NULL,
            'end_date'          => NULL,
            'sort'              => NULL,
            'limit'             => 10,
        ]);

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-statistic-get-commission-data')) :

            $temp = [];
            $data = sejolisa_get_affiliate_statistic($post_data);

            if(isset($data['statistic'])) :
                $i = 1;
                foreach( $data['statistic'] as $_data ) :
                    $temp[] = [
                        'rank'      => $i,
                        'ID'        => $_data->ID,
                        'image'     => get_avatar_url($_data->ID),
                        'name'      => $_data->user_name,
                        'raw_total' => $_data->total,
                        'total'     => ('total' === $post_data['calculate']) ? sejolisa_price_format($_data->total) : $_data->total
                    ];
                    $i++;
                endforeach;
            endif;

            $response = $temp;
        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get product data
     * Hooked via wp_ajax_sejoli-statistic-product, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function get_product_data() {

        $response = [];

        $post_data = wp_parse_args($_GET,[
            'nonce'             => false,
            'product_id'        => NULL,
            'calculate'         => 'order',
            'order_status'      => NULL,
            'start_date'        => NULL,
            'end_date'          => NULL,
            'sort'              => NULL,
            'limit'             => 10,
        ]);

        if(!current_user_can('manage_sejoli_orders')) :
            $post_data['affiliate_id'] = [
                get_current_user_id()
            ];
        endif;

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-statistic-get-product-data')) :

            $temp = [];
            $data = sejolisa_get_product_statistic($post_data);

            if(isset($data['statistic'])) :
                $i = 1;
                foreach( $data['statistic'] as $_data ) :
                    $image = get_the_post_thumbnail_url($_data->ID, 'medium');
                    $temp[] = [
                        'rank'      => $i,
                        'ID'        => $_data->ID,
                        'image'     => (false === $image) ? SEJOLISA_URL . 'public/img/placeholder.png' : $image,
                        'name'      => $_data->product_name,
                        'raw_total' => $_data->total,
                        'total'     => ('omset' === $post_data['calculate']) ? sejolisa_price_format($_data->total) : $_data->total
                    ];

                    $i++;
                endforeach;
            endif;

            $response = $temp;
        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get affiliate total lead
     * Lead is order created
     * @since   1.0.0
     * @since   1.5.4   Add parameter to get_total_lead to exclude cancelled order
     * @param   boolean     $exclude_cancelled_order
     * @return  integer     Total lead
     */
    protected function get_total_lead( $exclude_cancelled_order = false ) {

        $total = 0;
        $query = \SejoliSA\Model\Order::reset();

        if(is_array($this->date_range)) :
            $query = $query->set_filter('created_at', $this->date_range['start'].' 00:00:00', '>=')
                        ->set_filter('created_at', $this->date_range['end'].' 23:59:59', '<=');
        endif;

        if(
            current_user_can('manage_sejoli_own_affiliates') &&
            !current_user_can('manage_options')
        ) :
            $query = $query->set_filter('affiliate_id', $this->affiliate_id);
        endif;

        $response = $query->get_total_order_v2( $exclude_cancelled_order )
                        ->respond();

        if(false !== $response['valid']) :
            $total = $response['total'];
        endif;

        return $total;
    }

    /**
     * Get affiliate total sales
     * Sales is order status is in-progress, shipping
     * @since   1.0.0
     * @return  integer     Total Sales
     */
    protected function get_total_sales() {

        $total = 0;
        $query = \SejoliSA\Model\Order::reset();

        if(is_array($this->date_range)) :
            $query = $query->set_filter('created_at', $this->date_range['start'].' 00:00:00', '>=')
                        ->set_filter('created_at', $this->date_range['end'].' 23:59:59', '<=');
        endif;

        if(
            current_user_can('manage_sejoli_own_affiliates') &&
            !current_user_can('manage_options')
        ) :
            $query = $query->set_filter('affiliate_id', $this->affiliate_id);
        endif;

        $response = $query->set_filter('status', ['in-progress', 'shipping', 'completed'])
                        ->get_total_order_v2()
                        ->respond();

        if(false !== $response['valid']) :
            $total = $response['total'];
        endif;

        return $total;
    }

    /**
     * Get affiliate total omset
     * Sales is order status is in-progress, shipping
     * @since   1.0.0
     * @return  integer     Total Sales
     */
    protected function get_total_omset() {

        $total = 0;
        $query = \SejoliSA\Model\Order::reset();

        if(is_array($this->date_range)) :
            $query = $query->set_filter('created_at', $this->date_range['start'].' 00:00:00', '>=')
                        ->set_filter('created_at', $this->date_range['end'].' 23:59:59', '<=');
        endif;

        if(
            current_user_can('manage_sejoli_own_affiliates') &&
            !current_user_can('manage_options')
        ) :
            $query = $query->set_filter('affiliate_id', $this->affiliate_id);
        endif;

        $response = $query->set_filter('status', ['in-progress', 'shipping', 'completed'])
                        ->get_total_omset()
                        ->respond();

        if(false !== $response['valid']) :
            $total = sejolisa_price_format($response['total']);
        endif;

        return $total;
    }

    /**
     * Get total commission
     * Where status is added
     * @since   1.0.0
     * @return  integer     Total Sales
     */
    protected function get_total_commission() {

        $total = 0;
        $query = \SejoliSA\Model\Affiliate::reset();

        if(is_array($this->date_range)) :
            $query = $query->set_filter('created_at', $this->date_range['start'].' 00:00:00', '>=')
                        ->set_filter('created_at', $this->date_range['end'].' 23:59:59', '<=');
        endif;

        if(
            current_user_can('manage_sejoli_own_affiliates') &&
            !current_user_can('manage_options')
        ) :
            $query = $query->set_filter('affiliate_id', $this->affiliate_id);
        endif;

        $response = $query->set_filter('status', 'added')
                        ->get_total_commission()
                        ->respond();

        if(false !== $response['valid']) :
            $total = sejolisa_price_format($response['total']);
        endif;

        return $total;
    }

    /**
     * Get member today statistic
     * @since   1.0.0
     * @since   1.5.4   Add parameter to get_total_lead to exclude cancelled order
     * @return  json
     */
    public function get_member_today_statistic() {

        $response = [
            'lead'       => 0,
            'sales'      => 0,
            'omset'      => 0,
            'commission' => 0
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-render-member-statistic')) :

            $this->affiliate_id = get_current_user_id();
            $this->date_range   = [
                                    'start' => date('Y-m-d', current_time('timestamp')),
                                    'end'   => date('Y-m-d', current_time('timestamp'))
                                  ];

            $response['lead']       = $this->get_total_lead( true );
            $response['sales']      = $this->get_total_sales();
            $response['omset']      = $this->get_total_omset();
            $response['commission'] = $this->get_total_commission();

        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get member yesterday statistic
     * @since   1.1.0
     * @return  json
     */
    public function get_member_yesterday_statistic() {

        $response = [
            'lead'       => 0,
            'sales'      => 0,
            'omset'      => 0,
            'commission' => 0
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-render-member-statistic')) :

            $this->affiliate_id = get_current_user_id();
            $this->date_range   = [
                                    'start' => date('Y-m-d', strtotime('-1 day')),
                                    'end'   => date('Y-m-d', current_time('timestamp'))
                                  ];

            $response['lead']       = $this->get_total_lead();
            $response['sales']      = $this->get_total_sales();
            $response['omset']      = $this->get_total_omset();
            $response['commission'] = $this->get_total_commission();

        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get member monthly statistic
     * @since   1.0.0
     * @since   1.5.4   Add parameter to get_total_lead to exclude cancelled order
     * @return  json
     */
    public function get_member_monthly_statistic() {

        $response = [
            'lead'       => 0,
            'sales'      => 0,
            'omset'      => 0,
            'commission' => 0
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-render-member-statistic')) :

            $this->affiliate_id = get_current_user_id();
            $this->date_range   = [
                                    'start' => date('Y-m-1'),
                                    'end'   => date('Y-m-t')
                                  ];

            $response['lead']       = $this->get_total_lead( true );
            $response['sales']      = $this->get_total_sales();
            $response['omset']      = $this->get_total_omset();
            $response['commission'] = $this->get_total_commission();

        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get member all statistic
     * @since   1.0.0
     * @return  json
     */
    public function get_member_all_statistic() {

        $response = [
            'lead'       => 0,
            'sales'      => 0,
            'omset'      => 0,
            'commission' => 0
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-render-member-statistic')) :

            $this->affiliate_id     = get_current_user_id();
            $response['lead']       = $this->get_total_lead();
            $response['sales']      = $this->get_total_sales();
            $response['omset']      = $this->get_total_omset();
            $response['commission'] = $this->get_total_commission();

        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get order chart data
     * @since   1.0.0
     * @param   string $type    Chart data type
     * @param   array  $status  Given order status
     * @return  array
     */
    protected function get_chart_data($type, array $status) {

        $start_date = $this->date_range['start'];
        $end_date   = $this->date_range['end'];

        $query = \SejoliSA\Model\Order::reset()
                        ->set_chart_start_date($start_date)
                        ->set_chart_end_date($end_date);

        if(
            current_user_can('manage_sejoli_own_affiliates') &&
            !current_user_can('manage_options')
        ) :
            $query = $query->set_filter('affiliate_id', $this->affiliate_id);
        endif;

        $response = $query->set_filter('status', $status)
                        ->set_for_chart($type, false)
                        ->respond();

        return $this->set_chart_data($response['data'], $response['chart']);
    }

    /**
     * Get chart monthly statistic
     * @since   1.0.0
     * @return  json
     */
    public function get_chart_monthly_statistic() {

        $response = [
            'labels' => [],
            'data'  => [
                'quantity'  => [],
                'omset'     => []
            ]
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-render-chart-member-statistic')) :

            $this->affiliate_id = get_current_user_id();
            $this->date_range   = [
                                    'start' => date('Y-m-d', strtotime('-30 days')),
                                    'end'   => date('Y-m-d')
                                  ];

            $quantity_data = $this->get_chart_data('total-quantity', ['in-progress', 'shipping', 'completed']);
            $omset_data    = $this->get_chart_data('total-paid', ['in-progress', 'shipping', 'completed']);

            $response['labels']           = $quantity_data['labels'];
            $response['data']['quantity'] = $quantity_data['datasets'][0]['data'];
            $response['data']['omset']    = $omset_data['datasets'][0]['data'];

        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get chart yearly statistic
     * @since   1.0.0
     * @return  json
     */
    public function get_chart_yearly_statistic() {

        $response = [
            'labels' => [],
            'data'  => [
                'quantity'  => [],
                'omset'     => []
            ]
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-render-chart-member-statistic')) :

            $now  = Carbon::now();
            $end_of_month = $now->endOfMonth()->format('Y-m-d');
            $end = new Carbon($end_of_month);

            $this->affiliate_id = get_current_user_id();
            $this->date_range   = [
                                    'start' => $end->subYear()->startOfMonth()->format('Y-m-d'),
                                    'end'   => $now->endOfMonth()->format('Y-m-d')
                                  ];

            $quantity_data = $this->get_chart_data('total-quantity', ['in-progress', 'shipping', 'completed']);
            $omset_data    = $this->get_chart_data('total-paid', ['in-progress', 'shipping', 'completed']);

            $response['labels']           = $quantity_data['labels'];
            $response['data']['quantity'] = $quantity_data['datasets'][0]['data'];
            $response['data']['omset']    = $omset_data['datasets'][0]['data'];

        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get affiliate product data
     * @return [type] [description]
     */
    protected function get_affiliate_product_statistic_data($type = 'omset', $date_range = NULL) {

        $response  = [];

        $post_data = [
            'calculate'    => $type,
            'order_status' => ['in-progress', 'shipping', 'completed'],
            'start_date'   => NULL,
            'end_date'     => NULL,
            'sort'         => NULL,
            'limit'        => 10,
            'affiliate_id' => get_current_user_id()
        ];

        if(is_array($date_range)) :
            $post_data['start_date'] = $date_range['start'];
            $post_data['end_date']   = $date_range['end'];
        endif;

        $data = sejolisa_get_product_statistic($post_data);

        if(isset($data['statistic'])) :
            $i = 1;
            foreach( $data['statistic'] as $_data ) :
                $image = get_the_post_thumbnail_url($_data->ID, 'medium');
                $response[] = [
                    'rank'      => $i,
                    'ID'        => $_data->ID,
                    'image'     => (false === $image) ? SEJOLISA_URL . 'public/img/placeholder.png' : $image,
                    'name'      => $_data->product_name,
                    'raw_total' => $_data->total,
                    'total'     => ('omset' === $post_data['calculate']) ? sejolisa_price_format($_data->total) : $_data->total
                ];

                $i++;
            endforeach;
        endif;

        return $response;
    }

    /**
     * Get affiliate commission data
     * @since   1.0.0
     * @param   array   $date_range     Date of range
     * @return  array
     */
    protected function get_affiliate_commission_statistic_data($date_range = NULL) {

        $response  = [];

        $post_data = [
            'status'       => ['added'],
            'start_date'   => NULL,
            'end_date'     => NULL,
            'sort'         => NULL,
            'limit'        => 10,
            'affiliate_id' => get_current_user_id()
        ];

        if(is_array($date_range)) :
            $post_data['start_date'] = $date_range['start'];
            $post_data['end_date']   = $date_range['end'];
        endif;

        $data = sejolisa_get_commission_statistic($post_data);

        if(isset($data['statistic'])) :
            $i = 1;
            foreach( $data['statistic'] as $_data ) :
                $image = get_the_post_thumbnail_url($_data->ID, 'medium');
                $response[] = [
                    'rank'      => $i,
                    'ID'        => $_data->ID,
                    'image'     => (false === $image) ? SEJOLISA_URL . 'public/img/placeholder.png' : $image,
                    'name'      => $_data->product_name,
                    'raw_total' => $_data->total,
                    'total'     => sejolisa_price_format($_data->total)
                ];

                $i++;
            endforeach;
        endif;

        return $response;
    }

    /**
     * Get top ten affiliate data
     * Hooked via action sejoli_ajax_get-top-ten, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function get_top_ten_data() {

        $response = [
            'top-ten-omset-alltime'      => [],
            'top-ten-omset-monthly'      => [],
            'top-ten-quantity-alltime'   => [],
            'top-ten-quantity-monthly'   => [],
            'top-ten-commission-alltime' => [],
            'top-ten-commission-monthly' => [],
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-render-top-ten-statistic')) :

            $this->affiliate_id = get_current_user_id();

            $date_range = [
                'start' => date('Y-m-1'),
                'end'   => date('Y-m-t'),
            ];

            $response['top-ten-omset-alltime']      = $this->get_affiliate_product_statistic_data('omset');
            $response['top-ten-omset-monthly']      = $this->get_affiliate_product_statistic_data('omset', $date_range);
            $response['top-ten-quantity-alltime']   = $this->get_affiliate_product_statistic_data('quantity');
            $response['top-ten-quantity-monthly']   = $this->get_affiliate_product_statistic_data('quantity', $date_range);
            $response['top-ten-commission-alltime'] = $this->get_affiliate_commission_statistic_data();
            $response['top-ten-commission-monthly'] = $this->get_affiliate_commission_statistic_data($date_range);
        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get acquisition data
     * Hooked via action sejoli_ajax_get-acquisition-data, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function get_acquisition_data() {

        $response = [
            'table' => [],
            'pie'   => []
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-get-acquisition-data')) :

            $args = [];

            if(
                current_user_can('manage_sejoli_own_affiliates') &&
                !current_user_can('manage_options')
            ) :
                $args['affiliate_id']   = get_current_user_id();
            endif;

            $data = sejolisa_get_acquisition_data($args);

            if(false !== $data['valid']) :
                $response['table'] = $data['acquisitions'];
                $color  = $data = $labels = [];
                $i      = 0;

                foreach($response['table'] as $_data) :
                    $labels[$i] = $_data['label'];
                    $data[$i]   = $_data['sales'];
                    $color[$i]  = sejolisa_get_text_color($_data['label']);
                    $i++;
                endforeach;

                $response['pie'] = [
                    'labels'    => $labels,
                    'data'      => $data,
                    'color'     => $color
                ];
            endif;
        endif;

        wp_send_json($response);
        exit;
    }

    /**
     * Get acquisition data
     * Hooked via action sejoli_ajax_get-acquisition-member-data, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function get_acquisition_member_data() {

        $response = [
            'table' => [],
            'pie'   => []
        ];

        if(isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'sejoli-get-acquisition-member-data')) :

            $args = [];

            $data = sejolisa_get_acquisition_member_data($args);

            if(false !== $data['valid']) :
                $response['table'] = $data['acquisitions'];
                $color  = $data = $labels = [];
                $i      = 0;

                foreach($response['table'] as $source => $_data) :
                    foreach($_data as $media => $__data) :

                        $key        = $source;

                        if(!isset($labels[$key])) :
                            $labels[$key] = $__data['label'];
                        endif;

                        if(isset($__data['tmp_value'])) :
                            if(!isset($data[$key])) :
                                $data[$key]   = $__data['tmp_value'];
                            else :
                                $data[$key] += $__data['tmp_value'];
                            endif;
                        endif;

                        if(!isset($color[$key])) :
                            $color[$key]  = sejolisa_get_text_color($__data['label']);
                        endif;
                        $i++;
                    endforeach;
                endforeach;

                foreach($labels as $key => $label) :
                    $response['pie']['labels'][]    = $label;
                endforeach;

                foreach($data as $key => $_data) :
                    $response['pie']['data'][]      = $_data;
                endforeach;

                foreach($color as $key => $_color) :
                    $response['pie']['color'][]     = $_color;
                endforeach;

            endif;
        endif;

        wp_send_json($response);
        exit;
    }
}
