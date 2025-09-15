<?php
namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Shipment {

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
	protected $shipping_data = NULL;

	/**
	 * Shipping libraries data
	 * @since	1.2.0
	 * @access 	protected
	 * @var 	array
	 */
	protected $libraries = array();

	/**
	 * Does order need shipment?
	 * @since 	1.0.0
	 * @var 	boolean
	 */
	protected $order_needs_shipment = false;

	/**
	 * List of used delivery couriers and services.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $couriers = array(
		'domestic' => array(
			'pos' => array(
				'label'    => 'POS Indonesia',
				'website'  => 'http://www.posindonesia.co.id',
				'active'   => true,
				'services' => array(
					'Surat Kilat Khusus'	=> array(
						'title'	 => 'Surat Kilat Khusus',
						'active' => false
					),
					'Paketpos Biasa'		=> array(
						'title'	 => 'Paket Pos Biasa',
						'active' => true
					),
					'Paket Kilat Khusus'	=> array(
						'title'	 => 'Paket Kilat Khusus',
						'active' => true
					),
					'Express Sameday Dokumen'  => array(
						'title'	 => 'Express Sameday Dokumen',
						'active' => false
					),
					'Express Sameday Barang'   => array(
						'title'	 => 'Express Sameday Barang',
						'active' => true
					),
					'Express Next Day Dokumen' => array(
						'title'	 => 'Express Next Day Dokumen',
						'active' => false
					),
					'Express Next Day Barang'  => array(
						'title'	 => 'Express Next Day Barang',
						'active' => true
					),
					'Paketpos Dangerous Goods' => array(
						'title'  => 'Paketpos Dangerous Goods',
						'active' => false
					),
					'Paketpos Valuable Goods'  => array(
						'title'  => 'Paketpos Valuable Goods',
						'active' => false
					),
					'Kargopos Ritel Train'  => array(
						'title'  => 'Kargopos Ritel Train',
						'active' => false
					),
					'Kargopos Ritel Udara Dn'  => array(
						'title'  => 'Kargopos Ritel Udara Dn',
						'active' => false
					),
					'Paket Jumbo Ekonomi'  => array(
						'title'  => 'Paket Jumbo Ekonomi',
						'active' => false
					),
				)
			),
			'tiki' => array(
				'label'    => 'TIKI',
				'website'  => 'http://tiki.id',
				'active'   => false,
				'services' => array(
					'TRC' => array(
						'title'  => 'Trucking Service',
						'active' => false
					),
					'REG' => array(
						'title'  => 'Regular Service',
						'active' => false
					),
					'ECO' => array(
						'title'	 => 'Economy Service',
						'active' => false
					),
					'ONS' => array(
						'title'  => 'Over Night Service',
						'active' => false
					),
					'SDS' => array(
						'title'  => 'Same Day Service',
						'active' => false
					),
					'HDS' => array(
						'title'	 => 'Holiday Service',
						'active' => false
					),
				),
			),
			'jne' => array(
				'label'    => 'JNE',
				'website'  => 'http://www.jne.co.id',
				'active'   => true,
				'services' => array(
					'CTC'    => array(
						'title'	 => 'City Courier',
						'active' => true
					),
					'CTCYES' => array(
						'title'	 => 'City Courier YES',
						'active' => true
					),
					'OKE'    => array(
						'title'	 => 'Ongkos Kirim Ekonomis',
						'active' => true
					),
					'REG'    => array(
						'title'	 => 'Layanan Reguler',
						'active' => true
					),
					'YES'    => array(
						'title'  => 'Yakin Esok Sampai',
						'active' => true
					),
				)
			),
			'rpx' => array(
				'label'    => 'RPX',
				'website'  => 'http://www.rpx.co.id',
				'active'   => false,
				'services' => array(
					'SDP' => array(
						'title'	 => 'Same Day Package',
						'active' => false
					),
					'MDP' => array(
						'title'	 => 'Mid Day Package',
						'active' => false
					),
					'NDP' => array(
						'title'	 => 'Next Day Package',
						'active' => false
					),
					'RGP' => array(
						'title'  => 'Regular Package',
						'active' => false
					),
					'PAS' => array(
						'title'	 => 'Paket Ambil Suka-Suka',
						'active' => false
					),
					'PSR' => array(
						'title'	 => 'PSR Reguler',
						'active' => false
					),
				)
			),
			'ninja' => array(
				'label'    => 'Ninja Xpress',
				'website'  => 'https://www.ninjaxpress.co',
				'active'   => true,
				'services' => array(
					'STANDARD' => array(
						'title'	 => 'Standard Service',
						'active' => false
					),
				)
			),
			'pcp' => array(
				'label'   => 'PCP Express',
				'website' => 'http://www.pcpexpress.com',
				'active'  => false,
				'services' => array(
					'TREX' => array(
						'title'  => 'Titipan Regular Express',
						'active' => false
					),
					'JET' => array(
						'title'  => 'Jaminan Esok Tiba',
						'active' => false
					),
					'HIT' => array(
						'title'  => 'Hari Ini Tiba',
						'active' => false
					),
					'EXIS' => array(
						'title'  => 'Express Ekonomi',
						'active' => false
					),
					'GODA' => array(
						'title'  => 'Kargo Darat',
						'active' => false
					),
				)
			),
			'star' => array(
				'label'    => 'Star Cargo',
				'website'  => 'http://www.starcargo.co.id',
				'active'   => false,
				'services' => array(
					'Express'    => array(
						'title'	 => 'Express',
						'active' => false
					),
					'Reguler'    => array(
						'title'	 => 'Regular',
						'active' => false
					),
					'Dokumen' => array(
						'title'	 => 'Dokumen',
						'active' => false
					),
					'MOTOR' => array(
						'title'	 => 'MOTOR',
						'active' => false
					),
					'MOTOR 150 - 250 CC' => array(
						'title'	 => 'MOTOR 150 - 250 CC',
						'active' => false
					),
				)
			),
			'sicepat' => array(
				'label'   => 'SiCepat Express',
				'website' => 'http://www.sicepat.com',
				'active'  => false,
				'services' => array(
					'REG' => array(
						'title'	 => 'Layanan Regular',
						'active' => false
					),
					'BEST' => array(
						'title'	 => 'Besok Sampai Tujuan',
						'active' => false
					),
					'Cargo' => array(
						'title'	 => 'Cargo',
						'active' => false
					),
				)
			),
			'jet' => array(
				'label'    => 'JET Express',
				'website'  => 'http://www.jetexpress.co.id',
				'active'   => false,
				'services' => array(
					'CRG' => array(
						'title'	 => 'Cargo',
						'active' => false
					),
					'PRI' => array(
						'title'	 => 'Priority',
						'active' => false
					),
					'REG' => array(
						'title'	 => 'Regular',
						'active' => false
					),
					'XPS' => array(
						'title'	 => 'Express',
						'active' => false
					)
				),
			),
			'sap' => array(
				'label'    => 'SAP Express',
				'website'  => 'http://sap-express.id',
				'active'   => false,
				'services' => array(
					'REG' => array(
						'title'	 => 'Regular Service',
						'active' => false
					),
					'SDS' => array(
						'title'	 => 'Same Day Service',
						'active' => false
					),
					'ODS' => array(
						'title'	 => 'One Day Service',
						'active' => false
					),
				)
			),
			'pahala' => array(
				'label'    => 'Pahala Express',
				'website'  => 'http://www.pahalaexpress.co.id',
				'active'   => false,
				'services' => array(
					'EXPRESS' => array(
						'title'  => 'Express Service',
						'active' => false
					),
					'ONS' => array(
						'title'  => 'One Night Service',
						'active' => false
					),
					'SDS' => array(
						'title'  => 'Same Day Service',
						'active' => false
					),
					'SEPEDA' => array(
						'title'  => 'Paket Sepeda',
						'active' => false
					),
					'MOTOR SPORT' => array(
						'title'  => 'Paket Motor Sport',
						'active' => false
					),
					'MOTOR BESAR' => array(
						'title'  => 'Paket Motor Besar',
						'active' => false
					),
					'MOTOR BEBEK' => array(
						'title'  => 'Paket Motor Bebek',
						'active' => false
					),
				)
			),
			'slis' => array(
				'label'    => 'Solusi Ekspres',
				'website'  => 'http://www.solusiekspres.com',
				'active'   => false,
				'services' => array(
					'REGULAR' => array(
						'title'	 => 'Regular Service',
						'active' => false
					),
					'EXPRESS' => array(
						'title'	 => 'Express Service',
						'active' => false
					),
				)
			),
			'jnt' => array(
				'label'    => 'JNT Express',
				'website'  => 'http://www.jet.co.id',
				'active'   => false,
				'services' => array(
					'EZ' => array(
						'title'	 => 'Regular Service',
						'active' => false
					),
					'JSD' => array(
						'title'	 => 'Same Day Service',
						'active' => false
					),
				)
			),
			'ncs' => array(
				'label'    => 'Nusantara Card Semesta',
				'website'  => 'http://www.ptncs.com',
				'active'   => false,
				'services' => array(
					'NRS' => array(
						'title'  => 'Regular Service',
						'active' => false
					),
					'ONS' => array(
						'title'  => 'Over Night Service',
						'active' => false
					),
					'SDS' => array(
						'title'  => 'Same Day Service',
						'active' => false
					)
				)
			),
			'lion' => array(
				'label'    => 'Lion Parcel',
				'website'  => 'http://lionparcel.com',
				'active'   => false,
				'services' => array(
					'ONEPACK' => array(
						'title'  => 'One Day Service',
						'active' => false
					),
					'LANDPACK' => array(
						'title'  => 'Logistic Service',
						'active' => false
					),
					'REGPACK' => array(
						'title'  => 'Regular Service',
						'active' => false
					)
				)
			),
			'pandu' => array(
				'label'    => 'Pandu Logistics',
				'website'  => 'http://www.pandulogistics.com',
				'active'   => false,
				'services' => array(
					'REG' => array(
						'title'	 => 'Regular Package',
						'active' => false
					),
				)
			),
			'wahana' => array(
				'label'    => 'Wahana Express',
				'website'  => 'http://www.wahana.com',
				'active'   => false,
				'services' => array(
					'Normal' => array(
						'title'	 => 'Normal Service',
						'active' => false
					),
				)
			),
			'indah' => array(
				'label'    => 'Indah Logistic',
				'website'  => 'http://www.indahonline.com',
				'active'   => false,
				'services' => array(
					'DARAT' => array(
						'title'	 => 'Cargo Darat',
						'active' => false
					),
					'UDARA' => array(
						'title'	 => 'Cargo Udara',
						'active' => false
					),
				)
			),
			'rex' => array(
				'label'    => 'Royal Express Indonesia',
				'website'  => 'http://www.rex.co.id',
				'active'   => false,
				'services' => array(
					'EXP' => array(
						'title'	 => 'EXPRESS',
						'active' => false
					),
					'REX-1' => array(
						'title'	 => 'REX-1',
						'active' => false
					),
					'REX-5' => array(
						'title'	 => 'REX-5',
						'active' => false
					),
					'REX-10' => array(
						'title'	 => 'REX-10',
						'active' => false
					),
				)
			),
			'idl' => array(
				'label'    => 'Indotama Domestik Lestari',
				'website'  => 'http://www.idlcargo.co.id',
				'active'   => false,
				'services' => array(
					'iSDS' => array(
						'title'	 => 'SAME DAY SERVICES',
						'active' => false
					),
					'iONS' => array(
						'title'	 => 'OVERNIGHT SERVICES',
						'active' => false
					),
					'iSCF' => array(
						'title'	 => 'SPECIAL FLEET',
						'active' => false
					),
					'iREG' => array(
						'title'	 => 'REGULAR',
						'active' => false
					),
					'iCon' => array(
						'title'	 => 'EKONOMIS',
						'active' => false
					),
				),
			),
			'expedito' => array(
				'label'    => 'Expedito',
				'website'  => 'http://www.expedito.co.id',
				'active'   => false,
				'services' => array(
					'CityLink' => array(
						'title'	 => 'CityLink',
						'active' => false
					),
					'DPEX' => array(
						'title'	 => 'DPEX',
						'active' => false
					),
					'ARAMEX Indonesia' => array(
						'title'	 => 'ARAMEX Indonesia',
						'active' => false
					),
					'DHL JKT' => array(
						'title'	 => 'DHL JKT',
						'active' => false
					),
					'DHL Singapore' => array(
						'title'	 => 'DHL Singapore',
						'active' => false
					),
					'SF EXPRESS' => array(
						'title'	 => 'SF EXPRESS',
						'active' => false
					),
					'SkyNet WorldWide' => array(
						'title'	 => 'SkyNet Worldwide',
						'active' => false
					),
					'TNT | Fedex' => array(
						'title'	 => 'TNT | Fedex',
						'active' => false
					),
				)
			)
		),
		'international' => array(
			'jne' => array(
				'label'    => 'JNE',
				'website'  => 'http://www.jne.co.id',
				'services' => array(
					'INTL',
				),
				'account' => array(
					'pro',
				),
			)
		)
	);

	/**
	 * Get active courier and services;
	 * @since 	1.0.0
	 * @access 	private
	 * @var 	false|array
	 */
	private $available_couriers = false;

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

		add_action('admin_head', function() {
		    echo '<style>
		        .cf-hidden {
		            display: none;
		        }
		    </style>';
		});

	}

	/**
	 * Register shipment libraries
	 * Hooked via action plugins_loaded, priority 100
	 * @since 	1.2.0
	 * @return 	void
	 */
	public function register_libraries() {

		require_once( SEJOLISA_DIR . 'shipments/main.php');
		require_once( SEJOLISA_DIR . 'shipments/cod.php');

		$this->libraries['cod']	= new \SejoliSa\Shipment\Cod;

	}

	/**
	 * Add JS Vars for localization
	 * Hooked via sejoli/admin/js-localize-data, priority 10
	 * @since 	1.0.0
	 * @param 	array 	$js_vars 	Array of js vars
	 * @return 	array
	 */
	public function set_localize_js_var(array $js_vars) {

		$js_vars['order']['check_physical']	= add_query_arg([
													'ajaxurl' => add_query_arg([
														'action' => 'sejoli-order-check-if-physical'
													], admin_url('admin-ajax.php')),
													'nonce' => wp_create_nonce('sejoli-order-check-if-physical')
												]);

		$js_vars['get_subdistricts'] = [
			'ajaxurl' => add_query_arg([
					'action' => 'get-subdistricts'
				], admin_url('admin-ajax.php')
			),
			'placeholder' => __('Ketik minimal 3 karakter', 'sejoli')
		];

		return $js_vars;

	}

	/**
	 * Get subdistriction options for json
	 * Hooked via action wp_ajax_get-subdistricts
	 * @since  1.0.0
	 * @return json
	 */
	public function get_json_subdistrict_options() {

		$response = sejolisa_get_district_options( $_REQUEST['term'] );

		wp_send_json( $response['results'] );

	}

	/**
	 * Get subdistriction options
	 * Hooked via filter sejoli/shipment/subdistricts, priority 1
	 * @since  1.0.0
	 * @return array
	 */
	public function get_subdistrict_options($search = '', $format_for_select2 = false) {
	    $api_url = "https://rajaongkir.komerce.id/api/v1/destination/domestic-destination";

	    if (is_array($search)) {
	        $search = '';
	    }

	    $search = sanitize_text_field(strtolower(trim($search)));

	    $search_key = md5($search);
	    $cache_key = 'sejolisa_raongkir_backend_subdistrict_' . $search_key;

	    $cached_data = get_transient($cache_key);
	    if ($cached_data !== false) {
	        return $cached_data;
	        exit;
	    }

	    if (strlen(str_replace(' ', '', $search)) < 4) {
	        set_transient($cache_key, [], WEEK_IN_SECONDS);
	        return $format_for_select2 ? [] : [];
	    }

	    $api_key = esc_attr(sejolisa_carbon_get_theme_option('rajaongkir_pro_user'));

	    $limit = 100;
	    $offset = 0;
	    $url = add_query_arg([
	        'search' => $search,
	        'limit'  => $limit,
	        'offset' => $offset
	    ], $api_url);

	    $response = wp_remote_get($url, [
	        'timeout' => 180,
	        'headers' => [
	            'key' => $api_key,
	            'Content-Type' => 'application/x-www-form-urlencoded',
	        ],
	    ]);

	    if (is_wp_error($response)) {
	        error_log("🚨 API Error: " . $response->get_error_message());
	        return $format_for_select2 ? [] : [];
	    }

	    $code = wp_remote_retrieve_response_code($response);
	    $body_response = json_decode(wp_remote_retrieve_body($response), true);

	    if ($code !== 200 || empty($body_response['data'])) {
	        error_log("⚠️ RajaOngkir Error: Invalid response or empty data");
	        set_transient($cache_key, [], HOUR_IN_SECONDS);
	        error_log("📦 Cache SET: {$cache_key} | Empty: Yes (API empty/404)");
	        return $format_for_select2 ? [] : [];
	    }

	    $options = [];

	    foreach ($body_response['data'] as $item) {
	        $label = $item['label'];
	        if ($format_for_select2) {
	            $options[] = [
	                'id'   => $item['id'],
	                'text' => $label,
	            ];
	        } else {
	            $options[$item['id']] = $label;
	        }
	    }

	    if (!$format_for_select2) {
	        asort($options);
	    }

	    set_transient($cache_key, $options, MONTH_IN_SECONDS);

	    return $options;
	}

	/**
	 * Get subdistrict detail
	 * @since 	1.2.0
	 * @since 	1.5.0 		Add conditional to check if subdistrict_id is 0
	 * @param  	integer 	$subdistrict_id 	District ID
	 * @return 	array|null 	District detail
	 */
	public function get_subdistrict_detail($subdistrict_id) {

		if( 0 !== intval($subdistrict_id) ) :

			ob_start();
			require SEJOLISA_DIR . 'json/subdistrict.json';
			$json_data = ob_get_contents();
			ob_end_clean();

			$subdistricts        = json_decode($json_data, true);
	        $key                 = array_search($subdistrict_id, array_column($subdistricts, 'subdistrict_id'));
	        $current_subdistrict = $subdistricts[$key];

			return $current_subdistrict;

		endif;

		return 	NULL;
	}

	/**
	 * Delete shipping transient data everytime carbon fields - theme options saved
	 * Hooked via action carbon_fields_theme_options_container_saved, priority 10
	 * @since 	1.4.0
	 * @return 	void
	 */
	public function delete_cache_data() {

		delete_transient('sejolisa-shipment');
	
	}

	/**
	 * Setup shipment fields for general options
	 * Hooked via fi;ter sejoli/general/fields, priority 40
	 * @since 	1.0.0
	 * @param  	array  $fields 	Plugin option shipment fields in array
	 * @return 	array
	 */
	public function setup_shipping_fields(array $fields) {

		$shipping_fields = [];

		// $shipping_fields[] = Field::make( 'radio', 'shipment_rajaongkir_apikey', __( 'Raja Ongkir API Key', 'sejoli' ) )
		//     ->add_options( array(
		//         // 'raongkir_api_1'     => __( 'API Key 1', 'sejoli' ),
		//         // 'raongkir_api_2'     => __( 'API Key 2', 'sejoli' ),
		//         'raongkir_api_other' => __( 'RajaOngkir API Key', 'sejoli' )
		//     ) )
		//     ->set_default_value('raongkir_api_1');

		$shipping_fields[] = Field::make( 'text', 'rajaongkir_pro_user', __('RajaOngkir API Key'))
					->set_help_text(__('Masukkan API Key RajaOngkir milik Anda.', 'sejoli'));
					// ->set_conditional_logic(array(
					// 	array(
					// 		'field'	=> 'shipment_rajaongkir_apikey',
					// 		'value'	=> 'raongkir_api_other'
					// 	)
					// ));

		foreach($this->couriers['domestic'] as $key => $_courier) :

			$main_key = 'shipment_'. $key . '_active';

			$shipping_fields[] = Field::make('checkbox', $main_key, $_courier['label'])
									->set_default_value($_courier['active'])
									->set_help_text($_courier['website'])
									->set_classes('main-title');

			foreach($_courier['services'] as $service => $setting):

				$service_key = sanitize_title($service);
				$shipping_fields[] = Field::make('checkbox', 'shipment_' . $key . '_' . $service_key . '_active', $setting['title'])
										->set_default_value($setting['active'])
										->set_conditional_logic([
											[
												'field'	=> $main_key,
												'value'	=> true
											]
										]);
			endforeach;

		endforeach;

		$shipping_fields = apply_filters('sejoli/shipment/fields', $shipping_fields);

		$fields[] = [
			'title'  => __('Pengiriman', 'sejoli'),
			'fields' => $shipping_fields
		];

		return $fields;

	}

    /**
	 * Setup shipment fields for product
	 * Hooked via filter sejoli/product/fields, priority 30
	 * @since  1.0.0	Initialization
	 * @since  1.2.0 	Add ability to modify product shipment fields
	 * @param  array  	$fields
	 * @return array
	 */
	public function setup_setting_fields(array $fields) {

		$currency = 'Rp. '; // later will be using hook filter;

        $conditionals = [
            'physical'  => [
                [
                    'field' => 'product_type',
                    'value' => 'physical'
                ],[
                    'field' => 'shipment_active',
                    'value' => true
                ]
            ]
        ];

		$fields['shipping'] = [
			'title'	=> __('Pengiriman', 'sejoli'),
			'fields' =>  [
				Field::make( 'separator', 'sep_shipment' , __('Pengaturan Pengiriman', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('shipping') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('html',     'html_info_shipment')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Pengaturan ini hanya <strong>BERLAKU</strong> jika tipe produk adalah produk fisik', 'sejoli') . '</p></div>'),

                Field::make('checkbox', 'shipment_active', __('Aktifkan pengaturan pengiriman'))
                    ->set_option_value('yes')
                    ->set_default_value(false)
                    ->set_conditional_logic([
                        [
                            'field' => 'product_type',
                            'value' => 'physical'
                        ]
                    ]),

				Field::make('checkbox', 'shipment_input_own_value', __('Customer tentukan sendiri biaya pengiriman', 'sejoli'))
					->set_default_value(false)
					->set_conditional_logic([
						[
							'field' => 'product_type',
							'value' => 'physical'
						]
					]),

				Field::make( 'separator', 'shipment_weight_sep', __('Berat barang (dalam gram)', 'sejoli') ),
                Field::make('text', 'shipment_weight')
                    ->set_attribute('type', 'number')
                    ->set_attribute('min', 100)
                    ->set_required(true)
                    ->set_conditional_logic($conditionals['physical']),

				Field::make('hidden', 'shipment_origin')
				    ->set_default_value('')
				    ->set_conditional_logic($conditionals['physical']),

				Field::make('hidden', 'shipment_origin_name')
				    ->set_default_value('')
				    ->set_conditional_logic($conditionals['physical']),

				Field::make( 'separator', 'shipment_origin_sep', __('Awal pengiriman', 'sejoli') ),
				
				Field::make('html', 'shipment_origin_html')
	                ->set_html('<select id="shipment_origin_select" style="width: 100%"></select>')
	                // ->set_options( $this->get_subdistrict_options('Jakarta Barat') ) // ambil awal data
	                ->set_required(true)
	                ->set_help_text(__('Ketik nama kecamatan untuk pengiriman', 'sejoli'))
	                ->set_conditional_logic($conditionals['physical']),
            ]
        ];

        return $fields;

    }

	/**
	 * Add shipping data to product meta
	 * Hooked via filter sejoli/product/meta-data, priority 100
	 * @since 	1.0.0
	 * @param  	WP_Post 	$product
	 * @param  	int     	$product_id
	 * @return 	WP_Post
	 */
	public function setup_product_meta(\WP_Post $product, int $product_id) {

		$product->shipping = [
			'active'    => boolval(sejolisa_carbon_get_post_meta($product_id, 'shipment_active')),
			'weight'    => intval(sejolisa_carbon_get_post_meta($product_id, 'shipment_weight')),
			'origin'    => intval(sejolisa_carbon_get_post_meta($product_id, 'shipment_origin')),
			'own_value' => boolval(sejolisa_carbon_get_post_meta($product_id, 'shipment_input_own_value')),
		];

		return $product;

	}

	/**
	 * Set current order needs shipment
	 * Hooked via action sejoli/order/need-shipment, priority 1
	 * @since 1.1.1
	 * @param boolean $need_shipment [description]
	 */
	public function set_order_needs_shipment($need_shipment = false) {

		$this->order_needs_shipment = $need_shipment;
	
	}

	/**
	 * Validate shipping
	 * Hooked via filter sejoli/checkout/is-shipping-valid, priority 1
	 * @since  1.0.0
	 * @param  bool    	$valid
	 * @param  WP_Post 	$product
	 * @param  array   	$post_data
	 * @param  bool  	$is_calculate	Check if current request is to calculate only or to checkout
	 * @return bool
	 */
	public function validate_shipping_when_checkout(bool $valid, \WP_Post $product, array $post_data, $is_calculate = false) {

		if('digital' === $product->type && !$this->order_needs_shipment) :
			return $valid;
		endif;

		/**
		 * Check courier data
		 */
		if(isset($post_data['shipment']) && !empty($post_data['shipment']) && 'undefined' !== $post_data['shipment']) :

			list($courier,$service,$cost)	= explode(':::', $post_data['shipment']);

			$this->shipping_data = [
				'courier'     => $courier,
				'service'     => $service,
				'cost'        => floatval($cost),
				'district_id' => intval($post_data['district_id']),
				'district_name' => $post_data['district_name']
			];

		elseif(isset($post_data['shipping_own_value']) && 'undefined' !== $post_data['shipping_own_value']) :

			$this->shipping_data = [
				'courier'     => 'MANUAL',
				'service'     => 'MANUAL',
				'cost'        => floatval($post_data['shipping_own_value']),
				'district_id' => 0,
				'district_name' => ''
			];

		else :
			$valid = false;
			sejolisa_set_message( __('Detil pengiriman belum lengkap', 'sejoli') );
		endif;

		if(false === $is_calculate) :

			if(!empty($post_data['user_name'])) :
				if(is_array($this->shipping_data)) :
					$this->shipping_data['receiver'] = sanitize_text_field($post_data['user_name']);
				endif;
			else :
				$valid = false;
				sejolisa_set_message( __('Nama penerima belum diisi', 'sejoli'));
			endif;

			if(!empty($post_data['user_phone'])) :
				if(is_array($this->shipping_data)) :
					$this->shipping_data['phone'] = sanitize_text_field($post_data['user_phone']);
				endif;
			else :
				$valid = false;
				sejolisa_set_message( __('Nomor telpon penerima belum diisi', 'sejoli'));
			endif;

			if(!empty($post_data['postal_code'])) :
				if(is_array($this->shipping_data)) :
					$this->shipping_data['postal_code'] = sanitize_text_field($post_data['postal_code']);
				endif;
			else :
				if( false !== $product->form['postal_code'] ) :
					$valid = false;
					sejolisa_set_message( __('Kode pos belum diisi', 'sejoli'));
				endif;
			endif;

			/**
			 * Check address
			 */
			if(isset($post_data['address']) && !empty($post_data['address'])) :
				if(is_array($this->shipping_data)) :
					$this->shipping_data['address'] = sanitize_textarea_field($post_data['address']);
				endif;
			endif;

		endif;

		return $valid;

	}

	/**
	 * Get available couriers
	 * Hooked via filter sejoli/shipment/available-couriers, priority 100
	 * @since 	1.0.0
	 * @param  	array 	$available_couriers
	 * @return 	array
	 */
	public function get_available_couriers($available_couriers = []) {

		if(false === $this->available_couriers) :

			foreach($this->couriers['domestic'] as $key => $_courier) :

				$main_key = 'shipment_'. $key . '_active';
				$active = boolval(sejolisa_carbon_get_theme_option($main_key));

				if(false !== $active) :

					foreach($_courier['services'] as $service => $active) :

						$service_key = sanitize_title($service);
						$service_key = 'shipment_' . $key . '_' . $service_key . '_active';
						$active      = boolval(sejolisa_carbon_get_theme_option($service_key));

						if(false !== $active) :

							if(!isset($available_couriers[$key])) :
								$available_couriers[$key] = array();
							endif;

							$available_couriers[$key][] = $service;

						endif;

					endforeach;
				endif;

			endforeach;

			$this->available_couriers = $available_couriers;

		endif;

		return $this->available_couriers;

	}

	/**
	 * Get available courier services
	 * Hooked via filter sejoli/shipment/available-courier-services, priority 100
	 * @since 	1.0.0
	 * @param  	array  $services
	 * @return 	array
	 */
	public function get_available_courier_services($services = array()) {

		$available_couriers = $this->get_available_couriers();

		if(
			false !== $available_couriers &&
			is_array($available_couriers) &&
			0 < count($available_couriers)
		) :

			foreach($available_couriers as $courier => $_services) :
				foreach($_services as $_service) :
					$services[] = $_service;
				endforeach;
			endforeach;

		endif;

		return $services;

	}

	/**
	 * Calculate shipment cost
	 * Hooked via action sejoli/shipment/calculation, priority 100
	 * @param  	array  $post_data
	 * @return 	void
	 */
	public function calculate_shipment_cost(array $post_data) {

		$available_couriers = apply_filters('sejoli/shipment/available-couriers', []);

		if(
			false !== $available_couriers &&
			is_array($available_couriers) &&
			0 < count($available_couriers)
		) :
			$couriers       = implode(':', array_keys($available_couriers));
			$product        = sejolisa_get_product($post_data['product_id']);
			$product_weight = intval($product->shipping['weight']);

			$response       = sejolisa_get_shipment_cost([
		        'destination_id' => $post_data['district_id'],
		        'origin_id'      => $product->shipping['origin'],
		        'weight'         => apply_filters('sejoli/product/weight', $product_weight, $post_data ),
		        'courier'        => $couriers,
		        'quantity'       => $post_data['quantity']
		    ]);

			$response['shipment'] = apply_filters('sejoli/shipment/options', $response['shipment'], $post_data);

			if (isset($response['shipment']) && is_array($response['shipment'])) {
				foreach ($response['shipment'] as $key => $value) :
				    
				    if (strpos($key, 'J&T') !== false) :
				    
				        $new_key = str_replace('J&T', 'JNT', $key ?? '');
				        $response['shipment'][$new_key] = $value;
				        unset($response['shipment'][$key]); 
				        
				        $response['shipment'][$new_key] = safe_str_replace('J&T', 'JNT', $response['shipment'][$new_key]);
				    
				    endif;

				endforeach;
			}

			sejolisa_set_respond($response, 'shipment');
		else :
			$couriers       = "jne";
			$product        = sejolisa_get_product($post_data['product_id']);
			$product_weight = 1000;

			if (is_array($product->cod) && isset($product->cod['cod-weight'])) {
			    $product_weight = intval($product->cod['cod-weight']);
			}

			$cod_data = maybe_unserialize($product->shipping);

			$cod_origin = '';
			if (is_array($cod_data) && isset($cod_data['origin'])) {
			    $cod_origin = $cod_data['origin'];
			}

			$response       = sejolisa_get_shipment_cost([
		        'destination_id' => $post_data['district_id'],
		        'origin_id'      => $cod_origin,
		        'weight'         => apply_filters('sejoli/product/weight', $product_weight, $post_data ),
		        'courier'        => $couriers,
		        'quantity'       => $post_data['quantity']
		    ]);

			$response['shipment'] = apply_filters('sejoli/shipment/options', $response['shipment'], $post_data);
			
			sejolisa_set_respond($response, 'shipment');
		endif;

	}

	/**
	 * Set shipment data to order meta,
	 * Hooked via filter sejoli/order/meta-data, priority 100
	 * @since 	1.0.0
	 * @param 	array 	$meta_data
	 * @param 	array  	$order_data
	 * @return  array
	 */
	public function set_order_meta($meta_data = [], $order_data = array()) {

		if(false !== $this->shipping_data && is_array($this->shipping_data)) :
			$meta_data['need_shipment'] = true;
			$meta_data['shipping_data'] = $this->shipping_data;
			$meta_data['free_shipping']	= apply_filters('sejoli/order/is-free-shipping', false);
		endif;

		return $meta_data;

	}

	/**
	 * Add shipping cost to grand total
	 * Hooked via filter sejoli/order/grand-total, priority 300
	 * @since  	1.0.0
	 * @param 	float 	$grand_total
	 * @param 	array 	$post_data
	 * @return 	float
	 */
	public function add_shipping_cost(float $grand_total, array $post_data) {

		$coupon = isset($post_data['coupon']) ? sejolisa_get_coupon_by_code($post_data['coupon']) : null;

		if (false !== $this->shipping_data && is_array($this->shipping_data) && isset($this->shipping_data['cost'])) :

		    $wallet_value = filter_var($post_data['wallet'], FILTER_VALIDATE_BOOLEAN);

		    if (true === $wallet_value):
		        $grand_total = $grand_total;
		    elseif (isset($coupon['coupon']['discount']['free_shipping']) && true === boolval($coupon['coupon']['discount']['free_shipping'])) :
		        $grand_total = $grand_total;
		    else:
		        $grand_total += $this->shipping_data['cost'];
		    endif;

		endif;

		return $grand_total;

	}

	/**
	 * Set Markup Price to order meta,
	 * Hooked via filter sejoli/order/meta-data, priority 100
	 * @since 	1.0.0
	 * @param 	array 	$meta_data
	 * @param 	array  	$order_data
	 * @return  array
	 */
	public function set_markup_price_meta( $meta_data = [], $order_data = array() ) {

		$product               = sejolisa_get_product( $order_data['product_id'] );
		$cart_detail           = apply_filters( 'sejoli/order/cart-detail', [], $order_data );
		$product_id            = intval( $order_data['product_id'] );
        $is_cod_active         = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ) );
        $is_markup_cod_jne_active = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_active' ) );
        $is_markup_cod_sicepat_active = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_active' ) );
        $markup_ongkir_jne     = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_markup_with_ongkir' ) );
        $markup_ongkir_sicepat = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_markup_with_ongkir' ) );

        $shipment = isset($order_data['shipment']) ? $order_data['shipment'] : '';
        if( $shipment !== '' && $order_data['payment_gateway'] === 'cod:::CashOnDelivery' ) {

			$shipment_fee 	   = isset( $cart_detail['shipment_fee'] ) ? $cart_detail['shipment_fee'] : 0;
			$variant_price     = isset( $cart_detail['variant-ukuran']['raw_price'] ) ? $cart_detail['variant-ukuran']['raw_price'] : 0;
			$get_product_total = isset( $variant_price ) ? $product->price + $variant_price : $product->price;

			if( \str_contains( strtolower( $order_data['shipment'] ), 'jne' ) ) {

				$markup_percentage = 0.04;
				
				if( true === $is_cod_active && false === $markup_ongkir_jne && true === $is_markup_cod_jne_active && $product->type === "physical" ) {
					$markup_price = $get_product_total * $markup_percentage; 
					$meta_data['markup_price'] = $markup_price;
				} else {
					$meta_data['markup_price'] = null;
				}
				
			} else if( \str_contains( strtolower( $order_data['shipment'] ), 'sicepat' ) ) {

				$markup_percentage = 0.08;
				
				if( true === $is_cod_active && false === $markup_ongkir_sicepat && true === $is_markup_cod_sicepat_active && $product->type === "physical" ) {
					$markup_price = $get_product_total * $markup_percentage; 
					$meta_data['markup_price'] = $markup_price;
				} else {
					$meta_data['markup_price'] = null;
				}

			}

		} else if ( $shipment !== '' && $order_data['payment_gateway'] !== 'cod:::CashOnDelivery' ) {
		
			$meta_data['markup_price'] = null;
		
		}

		return $meta_data;

	}

	/**
	 * Add markup price to grand total
	 * Hooked via filter sejoli/order/grand-total, priority 300
	 * @since  	1.0.0
	 * @param 	float 	$grand_total
	 * @param 	array 	$post_data
	 * @return 	float
	 */
	public function add_markup_price( float $grand_total, array $post_data ) {

		$product               = sejolisa_get_product( $post_data['product_id'] );
		$cart_detail           = apply_filters( 'sejoli/order/cart-detail', [], $post_data );
		$product_id            = intval( $post_data['product_id'] );
        $is_cod_active         = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ) );
        $is_markup_cod_jne_active = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_active' ) );
        $is_markup_cod_sicepat_active = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_active' ) );
        $markup_ongkir_jne     = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_markup_with_ongkir' ) );
        $markup_ongkir_sicepat = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_markup_with_ongkir' ) );

        $shipment = isset($post_data['shipment']) ? $post_data['shipment'] : '';

		if( $shipment !== '' && $post_data['payment_gateway'] === 'cod:::CashOnDelivery' ) {

			$shipment_fee 	   = isset( $cart_detail['shipment_fee'] ) ? $cart_detail['shipment_fee'] : 0;
			$variant_price     = isset( $cart_detail['variant-ukuran']['raw_price'] ) ? $cart_detail['variant-ukuran']['raw_price'] : 0;
			$get_product_total = isset( $variant_price ) ? $product->price + $variant_price : $product->price;

			if( \str_contains( strtolower( $post_data['shipment'] ), 'jne' ) ) {

				$markup_percentage = 0.04;
				
				if( true === $is_cod_active && false === $markup_ongkir_jne && true === $is_markup_cod_jne_active && $product->type === "physical" ) {
					$markup_price = $get_product_total * $markup_percentage; 
					$grand_total += $markup_price;
				} else {
					$grand_total += 0;
				}

			} else if( \str_contains( strtolower( $post_data['shipment'] ), 'sicepat' ) ) {

				$markup_percentage = 0.08;
				
				if( true === $is_cod_active && false === $markup_ongkir_sicepat && true === $is_markup_cod_sicepat_active && $product->type === "physical" ) {
					$markup_price = $get_product_total * $markup_percentage; 
					$grand_total += $markup_price;
				} else {
					$grand_total += 0;
				}

			}

		}

		return $grand_total;

	}

	/**
     * Set shipment value to cart
     * Hooked via filter sejoli/order/cart-detail, 10
     * @since 1.0.0
     * @param array $cart_detail
     * @param array $order_data
     * @return array $cart_detail
     */
    public function set_cart_detail( array $cart_detail, array $order_data ) {

		$product = sejolisa_get_product( $order_data['product_id'] );

		if( false !== $this->shipping_data && is_array( $this->shipping_data ) ) :

			$cart_detail['shipment_fee'] = $this->shipping_data['cost'];

			$product_id            = intval( $order_data['product_id'] );
	        $is_cod_active         = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ) );
	        $is_markup_cod_jne_active = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_active' ) );
        	$is_markup_cod_sicepat_active = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_active' ) );
	        $markup_ongkir_jne     = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_markup_with_ongkir' ) );
	        $markup_ongkir_sicepat = boolval( sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_markup_with_ongkir' ) );

			if( $order_data['shipment'] !== '' && $order_data['payment_gateway'] === 'cod:::CashOnDelivery' ) {

				$shipment_fee 	   = isset( $cart_detail['shipment_fee'] ) ? $cart_detail['shipment_fee'] : 0;
				$variant_price     = isset( $cart_detail['variant-ukuran']['raw_price'] ) ? $cart_detail['variant-ukuran']['raw_price'] : 0;
				$get_product_total = isset( $variant_price ) ? $product->price + $variant_price : $product->price;

				if( \str_contains( strtolower( $order_data['shipment'] ), 'jne' ) ) {

					$markup_percentage = 0.04;
					$markup_label      = sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_jne_markup_label' );
					$markup_price      = $get_product_total * $markup_percentage; 
					
					if( true === $is_cod_active && false === $markup_ongkir_jne && true === $is_markup_cod_jne_active && $product->type === "physical" ) {
						$cart_detail['markup_price_fee']   = $markup_price;
						$cart_detail['markup_price_label'] = $markup_label;	
					} else {
						$cart_detail['markup_price_fee']   = null;
						$cart_detail['markup_price_label'] = null;	
					}
					

				} else if( \str_contains( strtolower( $order_data['shipment'] ), 'sicepat' ) ) {

					$markup_percentage = 0.08;
					$markup_label      = sejolisa_carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_markup_label' );
					$markup_price      = $get_product_total * $markup_percentage; 
					
					if( true === $is_cod_active && false === $markup_ongkir_sicepat && true === $is_markup_cod_sicepat_active && $product->type === "physical" ) {
						$cart_detail['markup_price_fee']   = $markup_price;
						$cart_detail['markup_price_label'] = $markup_label;	
					} else {
						$cart_detail['markup_price_fee']   = null;
						$cart_detail['markup_price_label'] = null;	
					}

				}

			} else if( $order_data['shipment'] !== '' && $order_data['payment_gateway'] !== 'cod:::CashOnDelivery' ) {
			
				$cart_detail['markup_price_fee']   = null;
				$cart_detail['markup_price_label'] = null;	
			
			}

		endif;

        return $cart_detail;

    }

	/**
	 * Reduce grand total with shipment if there is any shipping data in order meta
	 * Hooked via filter sejoli/commission/order-grand-total, priority 1
	 * @param  float  $grand_total
	 * @param  array  $order_data
	 * @return float
	 */
	public function reduce_with_shipping_cost(float $grand_total, array $order_data) {

		if(isset($order_data['meta_data']['shipping_data']) && false === boolval($order_data['meta_data']['free_shipping'])) :
			$grand_total -= floatval($order_data['meta_data']['shipping_data']['cost']);
		elseif(isset($order_data['meta_data']['shipping_data']) && true === boolval($order_data['meta_data']['free_shipping'])) :
			$grand_total = $grand_total;
		endif;

		return $grand_total;

	}

	/**
	 * Translate order meta shipping data for order detail
	 * Hooked via sejoli/order/detail priority 100
	 * @since 	1.0.0
	 * @param 	array $order_data
	 * @return 	array
	 */
	public function add_shipping_info_in_order_data(array $order_data) {

		if(isset($order_data['meta_data']['need_shipment']) && true === boolval($order_data['meta_data']['need_shipment'])) :

			$buyer = sejolisa_get_user($order_data['user_id']);

			$order_data['meta_data']['shipping_data'] = wp_parse_args($order_data['meta_data']['shipping_data'],[
				'courier'     => NULL,
				'address'     => NULL,
				'receiver'    => $buyer->display_name,
				'phone'	      => $buyer->meta->phone,
				'postal_code' => $buyer->meta->postal_code
			]);

			$shipping = $order_data['meta_data']['shipping_data'];

			ob_start();
			printf( __('%s %s, ongkos %s', 'sejoli'), $shipping['courier'], $shipping['service'], sejolisa_price_format($shipping['cost']) );
			$content = ob_get_contents();
			ob_end_clean();

			$order_data['courier'] = $content;
			$order_data['address'] = $shipping['address'];
			$district              = $shipping['district_name'];

			$content = '';
			ob_start();

			if (!empty($district)) :
			    $parts = explode(',', $district);

			    // Bersihkan whitespace
			    $parts = array_map('trim', $parts);

			    if (count($parts) === 5) {
				    $output = "";
				    $output .= "\n";
				    $output .= sprintf("KELURAHAN %s\n", strtoupper($parts[0]));
				    $output .= sprintf("KECAMATAN %s\n", strtoupper($parts[1]));
				    $output .= sprintf("KOTA %s\n", strtoupper($parts[2]));
				    $output .= sprintf("PROPINSI %s\n", strtoupper($parts[3]));
				    $output .= sprintf("KODE POS %s", $parts[4]);

				    echo nl2br($output); // Untuk tampilan di web (opsional)
				    // atau langsung kirim ke email/WhatsApp: gunakan $output tanpa HTML
				} else {
				    echo "Format distrik tidak sesuai.";
				}
			endif;

			$content = ob_get_contents();
			ob_end_clean();

			$order_data['address'] .= $content;

		endif;

		return $order_data;

	}

	/**
	 * Display shipping info
	 * Hooked via sejoli/notification/content/order-meta
	 * @param  string $content      	[description]
	 * @param  string $media        	[description]
	 * @param  string $recipient_type   [description]
	 * @param  array  $invoice_data 	[description]
	 * @return string
	 */
	public function add_shipping_info_in_notification(string $content, string $media, $recipient_type, array $invoice_data) {

		if(
			in_array($recipient_type, ['buyer', 'admin']) &&
			isset($invoice_data['order_data']['meta_data']['need_shipment']) &&
			true === boolval($invoice_data['order_data']['meta_data']['need_shipment'])
		) :
			$shipping  = $invoice_data['order_data']['meta_data']['shipping_data'];
			$meta_data = $invoice_data['order_data']['meta_data'];
			$district  = $shipping['district_name'];

			$content .= sejoli_get_notification_content(
							'shipment',
							$media,
							array(
								'shipping'  => $shipping,
								'district'	=> $district,
								'meta_data' => $meta_data
							)
						);
		endif;

		return $content;

	}
	
}
