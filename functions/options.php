<?php

use Nahid\JsonQ\Jsonq;

/**
 * Get available payment options
 * @since   1.0.0
 * @return  array
 */
function sejolisa_get_payment_options() {
    return apply_filters('sejoli/payment/payment-options', []);
}

/**
 * Get available district options
 * @since   1.0.0
 * @return  array
 */
// function sejolisa_get_district_options($term = '') {

//     $subdistrict_file = SEJOLISA_DIR . 'json/subdistrict.json';

//     $jsonq = new Jsonq($subdistrict_file);

//     $search = sanitize_text_field( ucwords( $term ) );
//     $subdistricts = $jsonq->where('subdistrict_name', 'contains' , $search )->get();

//     $response = [
//         'results' => []
//     ];

//     foreach ( $subdistricts as $key => $value ) :
//         $response['results'][] = [
//             'id' => $value['subdistrict_id'],
//             'text' => $value['type'].' '.$value['city'].' - '.$value['subdistrict_name'],
//         ];
//     endforeach;

//     return $response;
// }

function sejolisa_get_district_options($term = '') {
    $api_url = "https://rajaongkir.komerce.id/api/v1/destination/domestic-destination";

    $search = sanitize_text_field(strtolower(trim($term)));

    if (strlen($search) < 4) {
        return ['results' => []];
    }

    $search_key = md5($search);
    $cache_key  = 'sejolisa_raongkir_district_' . $search_key;

    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
        exit;
    }

    $api_key = esc_attr(sejolisa_carbon_get_theme_option('rajaongkir_pro_user'));

    $url = add_query_arg([
        'search' => $search,
        'limit'  => 100,
        'offset' => 0
    ], $api_url);

    $response = wp_remote_get($url, [
        'timeout' => 180,
        'headers' => [
            'key'          => $api_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]
    ]);

    if (is_wp_error($response)) {
        error_log("❌ RajaOngkir Error: " . $response->get_error_message());
        return ['results' => []];
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    $response_data = ['results' => []];

    if ($code === 200 && !empty($body['data']) && is_array($body['data'])) {
        foreach ($body['data'] as $item) {
            $response_data['results'][] = [
                'id'       => $item['id'],
                'text'     => $item['label'],
                'district' => $item['district_name'],
            ];
        }
    } else {
        error_log("⚠️ RajaOngkir Error: Invalid response or empty data");
    }

    $cache_duration = !empty($response_data['results']) ? MONTH_IN_SECONDS : HOUR_IN_SECONDS;

    set_transient($cache_key, $response_data, $cache_duration);

    return $response_data;
}

/**
 * Get available district option by IDs (local file version with caching)
 * @since 1.0.0
 * @param array $ids
 * @return array
 */
function sejolisa_get_district_options_by_ids($term = '') {
    $api_url = "https://rajaongkir.komerce.id/api/v1/destination/domestic-destination";

    $search = sanitize_text_field(strtolower(trim($term)));

    if (strlen($search) < 4) {
        return ['results' => []];
    }

    $search_key = md5($search);
    $cache_key  = 'sejolisa_raongkir_district_user_' . $search_key;

    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
        exit;
    }

    $api_key = esc_attr(sejolisa_carbon_get_theme_option('rajaongkir_pro_user'));

    $url = add_query_arg([
        'search' => $search,
        'limit'  => 100,
        'offset' => 0
    ], $api_url);

    $response = wp_remote_get($url, [
        'timeout' => 180,
        'headers' => [
            'key'          => $api_key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]
    ]);

    if (is_wp_error($response)) {
        error_log("❌ RajaOngkir Error: " . $response->get_error_message());
        return ['results' => []];
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    $response_data = ['results' => []];

    if ($code === 200 && !empty($body['data']) && is_array($body['data'])) {
        foreach ($body['data'] as $item) {
            $response_data['results'][] = [
                'id'       => $item['id'],
                'text'     => $item['label'],
                'district' => $item['district_name'],
            ];
        }
    } else {
        error_log("⚠️ RajaOngkir Error: Invalid response or empty data");
    }

    $cache_duration = !empty($response_data['results']) ? MONTH_IN_SECONDS : WEEK_IN_SECONDS;

    set_transient($cache_key, $response_data, $cache_duration);

    return $response_data;
}