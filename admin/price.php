<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Price {

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
     * The product data
     * @since   1.0.0
     * @access  protected
     * @var     WP_Post
     */
    protected $product;

    /**
     * Plan price of the product
     * @since   1.0.0
     * @access  protected
     * @var     NULL|array
     */
    protected $plan = NULL;

    /**
     * Current product price
     * @since  1.0.0
     * @access protected
     * @var    float
     */
    protected $price = 0.0;

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
     * Set product pricing plan
     * @since   1.0.0
	 * @since 	1.5.3	Add option to disable multi checkout for tryout page
	 * 					Add option to disable renew after x days expired
     * @return void
     */
    protected function set_pricing_plan() {

        $this->plan = [
            'product' => sejolisa_carbon_get_post_meta($this->product->ID, 'product_type'),
            'payment' => sejolisa_carbon_get_post_meta($this->product->ID, 'payment_type'),
            'price'   => [

                'regular'       => floatval(sejolisa_carbon_get_post_meta($this->product->ID, 'price')),

                'subscription'  => [
                    'type'     => sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_type'),
                    'duration' => intval(sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_duration')),

                    'tryout'   => [
                        'active'     => boolval(sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_has_tryout')),
                        'duration'   => intval(sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_tryout_duration')),
                        'period'     => sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_tryout_period'),
						'first_only' => sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_tryout_first_time_only'),
                    ],

                    'signup'    => [
                        'active' => boolval(sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_has_signup_fee')),
                        'fee'    => floatval(sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_signup_fee'))
                    ],

					'max_renewal' => absint( sejolisa_carbon_get_post_meta($this->product->ID, 'subscription_max_renewal_days') )
                ]
            ],
            'dimesale'  => [
                'type' => sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale'),
                'max'  => floatval(sejolisa_carbon_get_post_meta($this->product->ID, 'max_dimesale_price')),
                'by_sale' => [
                    'increase' 		=> floatval(sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale_by_sale_price_step')),
                    'step'     		=> intval(sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale_by_sale_step')),
					'completed_only'=> sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale_by_sale_calculate_completed_only')
                ],
                'by_time' => [
                    'increase' => floatval(sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale_by_time_price_step')),
                    'step'     => intval(sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale_by_time_step')),
                    'start'    => sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale_by_time_start'),
                    'end'      => sejolisa_carbon_get_post_meta($this->product->ID, 'dimesale_by_time_end'),
                ]
            ]
        ];
    }

    /**
     * Get product pricing plan
     * Hooekd via filter sejoli/product/pricing-plan, priority 999
     * @since   1.0.0
     * @param  array    $plan
     * @param  mixed    $product
     * @return array
     */
    public function get_pricing_plan($plan = [], $product = NULL) {

        if(is_a($product, 'WP_Post') && 'sejoli-product' === $product->post_type) :
            $this->product = $product;
        endif;

        if(is_a($this->product, 'WP_Post') && 'sejoli-product' === $this->product->post_type) :
            if(!is_array($this->plan) || !isset($this->plan['price']['regular'])) :
            endif;

            return $this->plan;
        else :
            return new \WP_Error( 'warning', __('Product is not set up', 'sejoli'));
        endif;

        return $this->plan;
    }

	/**
	 * Calcuate price by time
	 * @since 1.0.0
	 */
	private function set_price_with_dimesale_by_time() {

	    $config  = $this->plan['dimesale']['by_time'];

	    if(
	        !empty($config['start']) &&
	        current_time('timestamp') < strtotime($config['start'])
	    ) :
	        $this->price = $this->price;
	        return;
	    endif;

	    $start   = strtotime(empty($config['start']) ? $this->product->post_date : $config['start']);
	    $end     = strtotime(empty($config['end']) ? current_time('mysql') : $config['end']);
	    $current = current_time('timestamp');
	    $end     = ($current > $end) ? $end : $current;

	    // Pastikan bahwa $config['step'] tidak bernilai nol
	    $step = !empty($config['step']) ? $config['step'] : 1; // Atur nilai default jika diperlukan

	    // Cek apakah $step tidak nol untuk mencegah pembagian oleh nol
	    if ($step > 0) :
	        $mod = intval(($end - $start) / ($step * HOUR_IN_SECONDS));
	        $this->price += $mod * $config['increase'];
		endif;

	}

	/**
	 * Calculate price by sale
	 * @since 1.0.0
	 */
	private function set_price_with_dimesale_by_sale() {
		$config = $this->plan['dimesale']['by_sale'];
		$args   = [
			'product_id' => $this->product->ID
		];

		if(false !== $config['completed_only']) :
			$args['status'] = 'completed';
		endif;

		$respond = sejolisa_get_total_order($args);

		if(false !== $respond['valid']) :
			$total = intval($respond['total']);
			$mod   = intval($total / $config['step']);
			$this->price += $mod * $config['increase'];
		endif;
	}

    /**
     * Set product price based on pan
     * @since  1.0.0
     */
    protected function set_price() {

        $this->price = $this->plan['price']['regular'];

		if(!empty($this->plan['dimesale']['type'])) :

			if('dimesale-by-time' === $this->plan['dimesale']['type']) :
				$this->set_price_with_dimesale_by_time();
			elseif('dimesale-by-sale' === $this->plan['dimesale']['type']) :
				$this->set_price_with_dimesale_by_sale();
			endif;

			if(!empty($this->plan['dimesale']['max']) && $this->price > $this->plan['dimesale']['max']) :
				$this->price = $this->plan['dimesale']['max'];
			endif;
		endif;
    }

    /**
     * Get product price
     * Hooked via filter sejoli/product/price, priority 1
     * @since   1.0.0
     * @param   float   $price
     * @param   WP_Post $product
     * @return  float
     */
    public function get_price( float $price, \WP_Post $product) {

        $this->product = $product;

        $this->set_pricing_plan();
        $this->set_price();

        return $this->price;
    }
}
