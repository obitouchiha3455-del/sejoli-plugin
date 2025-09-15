<?php
namespace SejoliSA\JSON;

Class Commission extends \SejoliSA\JSON
{
    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set user options
     * @since   1.0.0
     * @return  json
     */
    public function set_for_options() {

    }

    /**
     * Set table data
     * Hooked via action wp_ajax_sejoli-commission-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

		$table = $this->set_table_args($_POST);
        $total = 0;
		$data  = [];

        if(isset($_POST['backend']) && current_user_can('manage_sejoli_commissions')) :

        else :
            $table['filter']['affiliate_id'] = get_current_user_id();
        endif;

		$respond = sejolisa_get_commissions($table['filter'], $table);

		if(false !== $respond['valid']) :
			$data = $respond['commissions'];
            foreach($data as $_com) :
                $total += $_com->commission;
            endforeach;
		endif;

		echo wp_send_json([
            'total'           => sejolisa_price_format($total),
			'table'           => $table,
			'draw'            => $table['draw'],
			'data'            => $data,
			'recordsTotal'    => $respond['recordsTotal'],
			'recordsFiltered' => $respond['recordsTotal'],
		]);

		exit;
    }

    /**
     * Set affiliate table data
     * Hooked via action wp_ajax_sejoli-affiliate-commission-table, priority 1
     * @since   1.1.3
     * @return  json
     */
    public function set_for_affiliate_table() {

		$table = $this->set_table_args($_POST);
        $total = 0;
		$data  = [];

		$response = sejolisa_get_affiliate_commission_info( $table );

        if ( false !== $response['valid'] ) :

            foreach( $response['commissions'] as $_commission ) :

                $affiliate    = sejolisa_get_user(intval($_commission->ID));

                if ( is_a( $affiliate, 'WP_User' ) ) :

                    $data[] = array(
                        'ID'                    => $affiliate->ID,
                        'display_name'          => $affiliate->display_name,
                        'pending_commission'    => $_commission->pending_commission,
                        'unpaid_commission'     => $_commission->unpaid_commission,
                        'paid_commission'       => $_commission->paid_commission,
                        'informasi_rekening'    => sejolisa_carbon_get_user_meta($affiliate->ID,'bank_info'),
                    );

                endif;

            endforeach;

        endif;

        $info = sejolisa_get_total_affiliate_commission_info( $table );

		echo wp_send_json([
            'info'            => $info,
			'table'           => $table,
			'draw'            => $table['draw'],
			'data'            => $data,
			'recordsTotal'    => $response['recordsTotal'],
			'recordsFiltered' => $response['recordsTotal'],
		]);

		exit;
    }

    /**
     * Set chart data
     * Hooked via wp_ajax_sejoli-commission-chart, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_chart() {

        $start_date = $end_date = $chart = NULL;
        $type       = $_GET['type'];
        $filter     = $this->set_filter_args($_GET['data']);

        if(isset($filter['date-range']) && !empty($filter['date-range'])) :
            list($start_date, $end_date) = explode(' - ', $filter['date-range']);
            unset($filter['date-range']);

        endif;

        $query = \SejoliSA\Model\Affiliate::set_chart_start_date($start_date)
                        ->set_chart_end_date($end_date);

        if(is_array($filter) && 0 < count($filter)) :
            $query = $query->set_filter_from_array($filter);
        endif;

        $respond = $query->set_for_chart($type)
                        ->respond();

        $commission_status = [
            'pending'   => __('Order belum selesai', 'sejoli'),
            'added'     => __('Order sudah selesai', 'sejoli'),
            'cancelled' => __('Order dibatalkan', 'sejoli')
        ];

        $chart = $this->set_chart_data($respond['data'], $respond['chart'], $commission_status);

        echo wp_send_json(wp_parse_args($chart,[
            'labels'   => NULL,
            'datasets' => NULL
        ]));
        exit;
    }

    /**
     * Get all unpaid commission for confirmation
     * @since   1.1.3
     * @return  json
     */

    public function set_for_paid_confirmation() {

        $data      = [];
        $post_data = wp_parse_args($_POST,[
            'commissions' => []
        ]);

        $commissions       = sejolisa_get_all_unpaid_commissions($post_data['commissions']);

        if(isset($commissions['commissions'])) :

            foreach( $commissions['commissions'] as $i => $commission ) :
                $commissions['commissions'][$i]->avatar           = get_avatar_url($commission->affiliate_id);
                $commissions['commissions'][$i]->total_commission = sejolisa_price_format( ceil($commission->total_commission) );
                $commissions['commissions'][$i]->affiliate_phone  = sejolisa_carbon_get_user_meta($commission->affiliate_id,'phone');
                $commissions['commissions'][$i]->bank_info        = sejolisa_carbon_get_user_meta($commission->affiliate_id,'bank_info');
            endforeach;

        endif;

        $commissions['id'] = $post_data['commissions'];
        echo wp_send_json($commissions);
        exit;
    }

    /**
     * GEt single affiliate commission info for confirmation
     * Hooked via action wp_ajax_sejoli-affiliate-commission-detail, priority 1
     * @since   1.1.3
     * @since   1.5.1   Add current time to not update all commission data
     * @return  json
     */
    public function set_for_affiliate_commission_confirmation() {

        $data      = [];
        $post_data = wp_parse_args($_POST,[
            'nonce'     => '',
            'affiliate' => 0,
            'date_range'=> '',
        ]);

        if(wp_verify_nonce($post_data['nonce'], 'sejoli-affiliate-commission-detail')) :

            $filter = [];
            if ( $post_data['date_range'] ) :
                $filter['date_range'] = $post_data['date_range'];
            endif;

            $data = sejolisa_get_single_affiliate_commission_info($post_data['affiliate'],$filter);

        endif;

        echo wp_send_json($data);
        exit;
    }
}
