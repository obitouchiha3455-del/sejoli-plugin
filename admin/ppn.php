<?php
namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class PPN {

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
	 * Current product commission
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	null|array
	 */
	protected $current_commission = NULL;

	/**
	 * Shipping data
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	null|array
	 */
	protected $ppn_data = NULL;

	/**
	 * PPN value
	 * @since 	1.0.0
	 * @access 	protected
	 * @var 	null|array
	 */
	private $ppn = NULL;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version 	   = $version;

		$this->ppn = get_option('_sejoli_ppn_price');

	}

	/**
	 * Set ppn to order meta,
	 * Hooked via filter sejoli/order/meta-data, priority 100
	 * @since 	1.0.0
	 * @param 	array 	$meta_data
	 * @param 	array  	$order_data
	 * @return  array
	 */
	public function set_order_meta($meta_data = [], $order_data = array()) {

		$enable_ppn = sejolisa_carbon_get_post_meta( $order_data['product_id'], 'enable_ppn' );

		if(true === $enable_ppn && $this->ppn > 0 || true === $enable_ppn &&  $this->ppn) :
			$meta_data['ppn'] = $this->ppn;
		endif;

		return $meta_data;

	}

	/**
	 * Add ppn to grand total
	 * Hooked via filter sejoli/order/grand-total, priority 300
	 * @since  	1.0.0
	 * @param 	float 	$grand_total
	 * @param 	array 	$post_data
	 * @return 	float
	 */
	public function add_ppn_cost(float $grand_total, array $post_data) {

		$enable_ppn = sejolisa_carbon_get_post_meta( $post_data['product_id'], 'enable_ppn' );

		if(true === $enable_ppn && $this->ppn > 0 || true === $enable_ppn &&  $this->ppn) :
			
			$total_with_ppn = $grand_total * $this->ppn / 100;
			$grand_total   += $total_with_ppn;
						
		endif;

		return $grand_total;

	}

	/**
     * Set ppn value to cart detail
     * Hooked via filter sejoli/order/cart-detail, 10
     * @since 1.0.0
     * @param array $cart_detail
     * @param array $order_data
     * @return array $cart_detail
     */
    public function set_cart_detail( array $cart_detail, array $order_data ) {

		$product = sejolisa_get_product( $order_data['product_id'] );	
		$enable_ppn = sejolisa_carbon_get_post_meta( $order_data['product_id'], 'enable_ppn' );

		if(true === $enable_ppn && $this->ppn > 0 || true === $enable_ppn &&  $this->ppn) :

			$cart_detail['ppn'] = $this->ppn;

		endif;

        return $cart_detail;

    }

	/**
	 * Reduce grand total with ppn if there is any ppn data in order meta
	 * Hooked via filter sejoli/commission/order-grand-total, priority 1
	 * @param  float  $grand_total
	 * @param  array  $order_data
	 * @return float
	 */
	public function reduce_with_ppn_cost(float $grand_total, array $order_data) {

		if(isset($order_data['meta_data']['ppn'])) :

			$payment_gateway = $order_data['payment_gateway'];

            $unique_code = '';
            if(isset($order_data['meta_data'][$payment_gateway]['unique_code'])):

                $unique_code = $order_data['meta_data'][$payment_gateway]['unique_code'];
                $order_data['meta_data']['unique_code'] = sejolisa_price_format($unique_code);
                
            endif;

            $total_wt_additionalfee = $order_data['grand_total'];
            if(isset($order_data['meta_data'][$payment_gateway]['unique_code'])):
                $total_wt_additionalfee = $order_data['grand_total'] - $order_data['meta_data'][$payment_gateway]['unique_code'];
            elseif(isset($order_data['meta_data']['shipping_data']['cost'])):
                $total_wt_additionalfee = $order_data['grand_total'] - $order_data['meta_data']['shipping_data']['cost'];
            elseif(isset($order_data['meta_data']['shipping_data']['cost']) && isset($order_data['meta_data'][$payment_gateway]['unique_code'])):
                $total_wt_additionalfee = $order_data['grand_total'] - $order_data['meta_data']['shipping_data']['cost'] - $order_data['meta_data'][$payment_gateway]['unique_code'];
            endif;

            if(isset($order_data['meta_data']['ppn'])) :

                $price_without_ppn = ($total_wt_additionalfee / (1 + $order_data['meta_data']['ppn'] / 100));
                $value_ppn         = $price_without_ppn * $order_data['meta_data']['ppn'] / 100;

            endif;

			$grand_total -= $value_ppn;

		endif;

		return $grand_total;

	}

	/**
	 * Translate order meta ppn data for order detail
	 * Hooked via sejoli/order/detail priority 100
	 * @since 	1.0.0
	 * @param 	array $order_data
	 * @return 	array
	 */
	public function add_ppn_info_in_order_data(array $order_data) {

		$enable_ppn = sejolisa_carbon_get_post_meta( $order_data['product_id'], 'enable_ppn' );

		if(true === $order_data['product']->enable_ppn && isset($order_data['meta_data']['ppn'])) :

			$ppn = $order_data['meta_data']['ppn'];

			ob_start();
			printf( __('%s', 'sejoli'), $ppn );
			$content = ob_get_contents();
			ob_end_clean();

		endif;

		return $order_data;

	}
	
}
