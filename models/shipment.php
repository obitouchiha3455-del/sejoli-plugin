<?php
namespace SejoliSA\Model;

Class Shipment extends \SejoliSA\Model
{
    /**
     * @since   1.5.3.
     */
    static protected $api_key = array(
        0 => 'c1bc5e2b11ab236bf4b4988ead182b59',
        1 => '055643e84c3e88687afa675aede2061d'
    );

    static protected $origin      = null;
    static protected $destination = null;
    static protected $weight      = null; // weight per single product
    static protected $quantity    = 1;
    static protected $courier     = null;
    static protected $res_code    = null;

    /**
     * Reset all property data
     * @since   1.0.0
     * @access  public
     */
    static public function reset() {
        self::$origin      = null;
        self::$destination = null;
        self::$weight      = null; // weight per single product
        self::$quantity    = 1;
        self::$courier     = null;

        return new static;
    }

    /**
     * Set district origin id
     * @since   1.0.0
     * @access  public
     */
    static public function set_origin($origin) {
        self::$origin = $origin;
        return new static;
    }

    /**
     * Set district destination id
     * @since   1.0.0
     * @access  public
     */
    static public function set_destination($destination) {
        self::$destination = $destination;
        return new static;
    }

    /**
     * Set weight per single product
     * @since   1.0.0
     * @access  public
     */
    static public function set_weight($weight) {
        self::$weight = intval($weight);
        return new static;
    }

    /**
     * Set product quantity
     * @since   1.0.0
     * @access  public
     */
    static public function set_quantity($quantity) {
        self::$quantity = intval($quantity);
        return new static;
    }

    /**
     * Set courier
     * @since   1.0.0
     * @access  public
     */
    static public function set_courier($courier) {
        self::$courier = $courier;
        return new static;
    }

    /**
     * Validate all data
     * @since   1.0.0
     * @access  protected
     */
    static protected function validate() {

        if(empty(self::$origin)) :
            self::set_valid(false);
            self::set_message(__('Asal pengiriman belum diisi', 'sejoli'));
        endif;

        if(empty(self::$destination)) :
            self::set_valid(false);
            self::set_message(__('Tujuan pengiriman belum diisi', 'sejoli'));
        endif;

        if(empty(self::$courier)) :
            self::set_valid(false);
            self::set_message(__('Kurir pengiriman belum dipilih', 'sejoli'));
        endif;

        if(0 === self::$weight) :
            self::set_valid(false);
            self::set_message(__('Berat barang tidak benar', 'sejoli'));
        endif;

        if(0 === self::$quantity) :
            self::set_valid(false);
            self::set_message(__('Jumlah barang tidak benar', 'sejoli'));
        endif;
    }

    /**
     * Get temporary shipment data
     * @since   1.0.0
     * @access  protected
     * @return  false|array
     */
    static protected function get_temporary_data() {

        $shipment_data = get_transient('sejolisa-shipment');

        if(false !== $shipment_data) :
            if(isset($shipment_data[self::$origin]) && isset($shipment_data[self::$origin][self::$destination])) :
                return $shipment_data[self::$origin][self::$destination];
            endif;
        endif;

        return false;
    }

    /**
     * Set temporary shipment data
     * @since   1.0.0
     * @access  protected
     * @return  false|array
     */
    static protected function set_temporary_data($shipment_data) {

        $all_shipment_data = get_transient('sejolisa-shipment');

        if(false === $all_shipment_data) :
            $all_shipment_data = [];
        endif;

        if(!isset($all_shipment_data[self::$origin])) :
            $all_shipment_data[self::$origin] = [];
        endif;

        if(!isset($all_shipment_data[self::$origin][self::$destination])) :
            $all_shipment_data[self::$origin][self::$destination] = [];
        endif;

        $all_shipment_data[self::$origin][self::$destination] = $shipment_data;

        set_transient('sejolisa-shipment', $all_shipment_data, 1 * DAY_IN_SECONDS);
    }

    /**
     * Set shipping data as dropdown optios
     * @since   1.0.0
     * @access  protected
     * @return  array
     */
    static protected function set_shipping_as_options($shipping_data) {

        $options     = [];
        $weight_cost = (int) ceil((self::$quantity * self::$weight) / 1000);
        $weight_cost = (0 === $weight_cost) ? 1 : $weight_cost;

        foreach($shipping_data as $key => $data) :
            list($courier, $service, $cost) = explode(':::', $key);
            $total_cost    = $data['cost'] * $weight_cost;
            $_key = $courier . ':::' . $service . ':::' . $total_cost;
            $options[$_key] = sprintf(
                                    __('%s %s (%s) - %s, estimasi %s', 'sejoli'),
                                    $data['courier'],
                                    $data['service'],
                                    $data['description'],
                                    sejolisa_price_format($total_cost),
                                    $data['etd']
                              );
        endforeach;

        return $options;
    }

    /**
     * @since   1.5.3.3
     * @param   string  $api_key
     */
    static protected function contact_service( $api_key ) {

        $params = [
            'origin'      => self::$origin,
            'destination' => self::$destination,
            'courier'     => self::$courier,
            'weight'      => (int) ceil((self::$quantity * self::$weight) / 1000) ?: 1
        ];

        $cache_key = 'sejolisa_shipping_' . md5(json_encode($params));

        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            self::set_temporary_data($cached_data);
            self::set_valid(true);
            self::set_respond('shipment', self::set_shipping_as_options($cached_data));
            return;
        }

        $response = wp_remote_post(
            'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost',
            [
                'timeout' => 180,
                'headers' => [
                    'key'          => $api_key,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $params
            ]
        );

        $code          = wp_remote_retrieve_response_code($response);
        $body_response = json_decode(wp_remote_retrieve_body($response), true);
        self::$res_code = $code;

        if (200 === intval($code)) {

            $shipment_data = [];
            $services = apply_filters('sejoli/shipment/available-courier-services', []);

            foreach ((array) $body_response['data'] as $_courier_data) {

                $courier_key  = strtoupper($_courier_data['code'] ?? $_courier_data['courier']);
                $courier_name = $_courier_data['name'] ?? $courier_key;
                $service      = $_courier_data['service'];
                $cost         = $_courier_data['cost'];
                $etd          = $_courier_data['etd'];
                $description  = $_courier_data['description'];

                if (in_array($service, $services)) {
                    $key = strtoupper($courier_key . ':::' . $service . ':::' . intval($cost));

                    $shipment_data[$key] = [
                        'courier'     => $courier_key,
                        'service'     => $service,
                        'description' => $description,
                        'cost'        => $cost,
                        'etd'         => $etd
                    ];
                }
            }

            set_transient($cache_key, $shipment_data, WEEK_IN_SECONDS);

            self::set_temporary_data($shipment_data);
            self::set_valid(true);
            self::set_respond('shipment', self::set_shipping_as_options($shipment_data));

        } else {

            $message = $body_response['message'] ?? 'Terjadi kesalahan saat mengambil data ongkir.';
            self::set_valid(false);
            self::set_message($message);
            do_action('sejoli/log/write', 'courier service log', $message);
        }
    }

    /**
     * Get shipment cos
     * @since   1.0.0
     * @access  public
     */
    static public function get_cost() {

        self::validate();

        if(false !== self::$valid) :

            $shipment_data = self::get_temporary_data();

            if(false !== $shipment_data && (is_array($shipment_data) && 0 < count($shipment_data))) :

                self::set_valid(true);
                self::set_respond('shipment', self::set_shipping_as_options($shipment_data));
                return new static;

            endif;

            // $shipment_rajaongkir_apikey      = sejolisa_carbon_get_theme_option('shipment_rajaongkir_apikey');
            $shipment_rajaongkir_user_apikey = esc_attr(sejolisa_carbon_get_theme_option('rajaongkir_pro_user'));

            // if( in_array( $shipment_rajaongkir_apikey, array('raongkir_api_1', 'raongkir_api_2') ) ) :

            //     $raongkir_api_key = get_transient('sejolisa-raongkir-api');
                
            //     self::contact_service( $raongkir_api_key );

            //     if( !$raongkir_api_key ) :

            //         if( $shipment_rajaongkir_apikey === "raongkir_api_1" ) :
            //             self::contact_service( self::$api_key[0] );
            //         elseif( $shipment_rajaongkir_apikey === "raongkir_api_2" ) :
            //             self::contact_service( self::$api_key[1] );
            //         endif;

            //     endif;

            //     if( 200 !== intval(self::$res_code) ) :

            //         if( $shipment_rajaongkir_apikey === "raongkir_api_1" ) :
            //             set_transient('sejolisa-raongkir-api', self::$api_key[1], 1 * DAY_IN_SECONDS);
            //         elseif( $shipment_rajaongkir_apikey === "raongkir_api_2" ) :
            //             set_transient('sejolisa-raongkir-api', self::$api_key[0], 1 * DAY_IN_SECONDS);
            //         endif;

            //         $raongkir_api_key = get_transient('sejolisa-raongkir-api');
                    
            //         self::contact_service( $raongkir_api_key );
                    
            //     endif;

            // elseif( $shipment_rajaongkir_apikey === "raongkir_api_other" ) :

                self::contact_service( $shipment_rajaongkir_user_apikey );

            // endif;

        endif;

        return new static;

    }
    
}
