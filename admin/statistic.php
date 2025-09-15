<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli
 * @subpackage Sejoli/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli
 * @subpackage Sejoli/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Statistic {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * Statistic value
     * @since   1.0.0
     * @access  protected
     * @param   array
     */
    protected $statistic = [];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Display statistic widgets
	 * Hooked via action wp_dashboard_setup
	 * @since 	1.0.0
	 * @return	void
	 */
	public function display_statistic_widgets() {

		if(current_user_can('manage_sejoli_orders')) :
			wp_add_dashboard_widget('sejoli-all-time-omset',		 __('Top 10 Omset Produk Sepanjang Waktu', 'sejoli'), 	[$this, 'display_all_time_top_omset']);
			wp_add_dashboard_widget('sejoli-monthly-omset',		 	 sprintf( __('Top 10 Omset Produk Bulan %s', 'sejoli'), date('M Y') ), 	[$this, 'display_monthly_top_omset']);
			wp_add_dashboard_widget('sejoli-onemonthago-omset',		 sprintf( __('Top 10 Omset Produk Bulan %s', 'sejoli'), date('M Y', strtotime('first day of -1 month')) ), 	[$this, 'display_monthly_top_omset']);
			wp_add_dashboard_widget('sejoli-twomonthsago-omset',	 sprintf( __('Top 10 Omset Produk Bulan %s', 'sejoli'), date('M Y', strtotime('first day of -2 months')) ), 	[$this, 'display_monthly_top_omset']);

			wp_add_dashboard_widget('sejoli-all-time-product',		 __('Top 10 Produk Sepanjang Waktu', 'sejoli'), 											[$this, 'display_all_time_top_product']);
			wp_add_dashboard_widget('sejoli-monthly-product',		 sprintf( __('Top 10 Produk Bulan %s', 'sejoli'), date('M Y') ), 							[$this, 'display_monthly_top_product']);
			wp_add_dashboard_widget('sejoli-onemonthago-product',	 sprintf( __('Top 10 Produk Bulan %s', 'sejoli'), date('M Y', strtotime('first day of -1 month')) ), 	[$this, 'display_monthly_top_product']);
			wp_add_dashboard_widget('sejoli-twomonthsago-product',	 sprintf( __('Top 10 Produk Bulan %s', 'sejoli'), date('M Y', strtotime('first day of -2 months')) ), 	[$this, 'display_monthly_top_product']);

			wp_add_dashboard_widget('sejoli-all-time-commission', 	 		__('Top 10 Affiliasi Sepanjang Waktu', 'sejoli'), 											[$this, 'display_all_time_top_commission']);
			wp_add_dashboard_widget('sejoli-monthly-commission', 	 		sprintf( __('Top 10 Affiliasi Bulan %s', 'sejoli'), date('M Y') ), 						[$this, 'display_monthly_top_commission']);
			wp_add_dashboard_widget('sejoli-onemonthago-commission', 	 	sprintf( __('Top 10 Affiliasi Bulan %s', 'sejoli'), date('M Y', strtotime('first day of -1 month')) ), 	[$this, 'display_monthly_top_commission']);
			wp_add_dashboard_widget('sejoli-twomonthsago-commission', 	 	sprintf( __('Top 10 Affiliasi Bulan %s', 'sejoli'), date('M Y', strtotime('first day of -2 months')) ), 	[$this, 'display_monthly_top_commission']);
		endif;
	}

	/**
	 * Set CSS and JS for statistic data menegement
	 * Hooked via admin_enqueue_scripts, priority 100
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function set_css_and_js() {

		global $pagenow;

		if( 'index.php' === $pagenow && current_user_can('manage_sejoli_orders') ) :

			wp_enqueue_style( 'daterangepicker');
			wp_enqueue_style( 	$this->plugin_name . '-widgets', SEJOLISA_URL . 'admin/css/widgets.css');
			wp_enqueue_script( 	$this->plugin_name . '-widgets', SEJOLISA_URL . 'admin/js/widgets.js', ['jquery', 'js-render'], $this->version, true);
			wp_enqueue_script( 'chartjs' );
			wp_enqueue_script( 'daterangepicker');
			wp_localize_script( $this->plugin_name . '-widgets', 'sejoli_widgets', [
				'text'	=> [
					'loading'	=> '<p>'. __('Mengambil data...', 'sejoli'). '</p>'
				],
				'all_time_omset' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'calculate' => 'omset',
						'order_status'	=> [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'monthly_omset' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'   => date('Y-m-01'),
						'end_date'     => date('Y-m-t'),
						'calculate'    => 'omset',
						'order_status' => [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'onemonthago_omset' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'   => date('Y-m-01', strtotime('first day of -1 month') ),
						'end_date'     => date('Y-m-t', strtotime('first day of -1 month') ),
						'calculate'    => 'omset',
						'order_status' => [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'twomonthsago_omset' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'   => date('Y-m-01', strtotime('first day of -2 months')),
						'end_date'     => date('Y-m-t', strtotime('first day of -2 months')),
						'calculate'    => 'omset',
						'order_status' => [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'all_time_product' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'calculate' => 'quantity',
						'order_status'	=> [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'monthly_product' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'   => date('Y-m-01'),
						'end_date'     => date('Y-m-t'),
						'calculate'    => 'quantity',
						'order_status' => [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'onemonthago_product' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'   => date('Y-m-01', strtotime('first day of -1 month') ),
						'end_date'     => date('Y-m-t', strtotime('first day of -1 month') ),
						'calculate'    => 'quantity',
						'order_status' => [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'twomonthsago_product' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-product',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-product-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'   => date('Y-m-01', strtotime('first day of -2 months')),
						'end_date'     => date('Y-m-t', strtotime('first day of -2 months')),
						'calculate'    => 'quantity',
						'order_status' => [
							'in-progress', 'shipping', 'completed'
						]
					],
				],
				'all_time_commission' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-commission',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-commission-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'calculate'	=> 'total',
						'commission_status' => [
							'added'
						],
					]
				],
				'monthly_commission' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-commission',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-commission-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'        => date('Y-m-01'),
						'end_date'          => date('Y-m-t'),
						'calculate'         => 'total',
						'commission_status' => [
							'added'
						],
					]
				],
				'onemonthago_commission' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-commission',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-commission-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'        => date('Y-m-01', strtotime('first day of -1 month') ),
						'end_date'          => date('Y-m-t', strtotime('first day of -1 month') ),
						'calculate'         => 'total',
						'commission_status' => [
							'added'
						],
					]
				],
				'twomonthsago_commission' => [
					'url'	=> add_query_arg([
							'action'	=> 'sejoli-statistic-commission',
							'nonce'		=> wp_create_nonce('sejoli-statistic-get-commission-data'),
						],admin_url('admin-ajax.php')
					),
					'data'	=> [
						'start_date'        => date('Y-m-01', strtotime('-2 month')),
						'end_date'          => date('Y-m-t', strtotime('-2 month')),
						'calculate'         => 'total',
						'commission_status' => [
							'added'
						],
					]
				]
			]);
		endif;

		if( 'edit.php' !== $pagenow ||!isset($_GET['post_type']) ||'sejoli-product' !== $_GET['post_type'] ) :
			return;
		endif;

		wp_enqueue_style( 'semantic-ui' );
	}

	/**
	 * Check all product in current product management page
	 * Hooked via load-edit.php, prirority 100;
	 * @return 	void
	 */
	public function check_product_page() {

		global $wp_query, $pagenow;

		if('edit.php' !== $pagenow ||!isset($_GET['post_type']) ||'sejoli-product' !== $_GET['post_type']) :
			return;
		endif;

		$product_ids = [];

		foreach( (array) $wp_query->posts as $product ) :
			$product_ids[] = $product->ID;
		endforeach;

        $temp = [];
		$total_order = sejolisa_get_product_statistic([
			'product_id' => $product_ids,
            'order_status' => [
                'completed', 'in-progress', 'shipping'
            ]
		]);

        foreach( $total_order['statistic'] as $data) :
            $temp[$data->ID] = [
                'product_nmme' => $data->product_name,
                'total'        => $data->total
            ];
        endforeach;

        $this->statistic['total_order'] = $temp;

        $temp = [];
        $total_omset = sejolisa_get_product_statistic([
			'product_id' => $product_ids,
            'calculate'  => 'omset',
            'order_status' => [
                'completed', 'in-progress', 'shipping'
            ]
		]);

        foreach( $total_omset['statistic'] as $data) :
            $temp[$data->ID] = [
                'product_nmme' => $data->product_name,
                'total'        => $data->total
            ];
        endforeach;

        $this->statistic['total_omset'] = $temp;

        $temp = [];
        $total_quantity = sejolisa_get_product_statistic([
			'product_id' => $product_ids,
            'calculate'  => 'quantity',
            'order_status' => [
                'completed', 'in-progress', 'shipping'
            ]
		]);

        foreach( $total_quantity['statistic'] as $data) :
            $temp[$data->ID] = [
                'product_nmme' => $data->product_name,
                'total'        => $data->total
            ];
        endforeach;

        $this->statistic['total_quantity'] = $temp;
	}

	/**
	 * Add statistic column to product columns
	 * Hooked via filter manage_sejoli-product_posts_columns, priority 100
	 * @since 	1.0.0
	 * @param	array $columns
	 * @return 	array
	 */
	public function add_product_columns(array $columns) {

		unset($columns['date']);

		$columns['sejoli-statistic']	= __('Statistik', 'sejoli');

		return $columns;
	}

	/**
	 * Display statistic data to product column
	 * Hooked via manage_posts_custom_column, priority 100
	 * @since 	1.0.0
	 * @param  	string 		$column
	 * @param  	integer 	$post_id
	 * @return 	void
	 */
	public function display_product_statistic_data($column, $post_id) {

		switch ( $column ) :

			case 'sejoli-statistic' :

				$statistic = [
					'total_order'    => isset($this->statistic['total_order'][$post_id]) ? $this->statistic['total_order'][$post_id]['total'] : 0,
					'total_omset'    => isset($this->statistic['total_omset'][$post_id]) ? $this->statistic['total_omset'][$post_id]['total'] : 0,
					'total_quantity' => isset($this->statistic['total_quantity'][$post_id]) ? $this->statistic['total_quantity'][$post_id]['total'] : 0
				];

				?>
				<div class="ui list">
					<div class="item">
						<span class="ui purple horizontal label" style='width:100px;text-align:left;'>Total Omset</span>
						<?php echo sejolisa_price_format( $statistic['total_omset'] ); ?>
					</div>
					<div class="item">
						<span class="ui red horizontal label" style='width:100px;text-align:left;'>Total Order</span>
						<?php echo $statistic['total_order']; ?>
					</div>
					<div class="item">
						<span class="ui green horizontal label" style='width:100px;text-align:left;'>Total Kuantiti</span>
						<?php echo $statistic['total_quantity']; ?>
					</div>
				</div>
				<?php

				break;

		endswitch;
	}

	/**
	 * Dashboard widget
	 * Display all time top product omset
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_all_time_top_omset(){
		?>
		<div class="ui middle aligned divided list sejoli-widgets">

		</div>
		<?php
	}

	/**
	 * Dashboard widget
	 * Display monthly top omset
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_monthly_top_omset(){
		?>
		<div class="ui middle aligned divided list sejoli-widgets">

		</div>
		<?php
	}

	/**
	 * Dashboard widget
	 * Display all time top selling products
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_all_time_top_product(){
		?>
		<div class="ui middle aligned divided list sejoli-widgets">

		</div>
		<?php
	}

	/**
	 * Dashboard widget
	 * Display monthly top selling products
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_monthly_top_product(){
		?>
		<div class="ui middle aligned divided list sejoli-widgets">

		</div>
		<?php
	}

	/**
	 * Dashboard widget
	 * Display all time top affiliate
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_all_time_top_commission(){
		?>
		<div class="ui middle aligned divided list sejoli-widgets">

		</div>
		<?php
	}

	/**
	 * Dashboard widget
	 * Display monthly top affiliate
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function display_monthly_top_commission(){
		?>
		<div class="ui middle aligned divided list sejoli-widgets">

		</div>
		<?php
	}

	/**
	 * Add widget template
	 * Hooked via admin_footer, priority 1
	 * @return void
	 */
	public function display_widget_template() {

		global $pagenow;

		if('index.php' !== $pagenow || !current_user_can('manage_sejoli_orders')) :
			return;
		endif;

		?>
		<script id='sejoli-widget-item' type="text/x-jsrender">
		<div class="item">
			<img class="ui avatar mini circular image" src="{{:image}}">
			<div class="content">
				<div class="product-name">{{:name}}</div>
				<div class="ui blue horizontal label product-value" >{{:total}}</div>
			</div>
	    </div>
		</script>
		<?php
	}

	/**
	 * Display full width statistic
	 * Hooked via admin_footer, priority 1
	 * @return void
	 */
	public function display_full_width_statistic() {

		if ( get_current_screen()->base !== 'dashboard' || !current_user_can('manage_sejoli_orders') ) {
			return;
		}
		?><div id="sejoli-full-widgets" style="display: none;"><?php
		require_once( plugin_dir_path( __FILE__) .'partials/dashboard/statistic-today.php' );
		// require_once( plugin_dir_path( __FILE__) .'partials/dashboard/statistic-yesteday.php' );
		require_once( plugin_dir_path( __FILE__) .'partials/dashboard/statistic-monthly.php' );
		require_once( plugin_dir_path( __FILE__) .'partials/dashboard/statistic-all.php' );
		require_once( plugin_dir_path( __FILE__) .'partials/dashboard/chart-monthly.php' );
		require_once( plugin_dir_path( __FILE__) .'partials/dashboard/chart-yearly.php' );
		?></div>
		<div id='sejoli-bottom-widgets' style="display:none;"><?php
		require_once( plugin_dir_path( __FILE__) .'partials/dashboard/acquisition.php' );
		?></div>
		<script>
			jQuery(document).ready(function($) {
				$('#welcome-panel').after($('#sejoli-full-widgets').show());
				$('#dashboard-widgets-wrap').after($('#sejoli-bottom-widgets').show());
			});
		</script>
		<?php
	}
}
