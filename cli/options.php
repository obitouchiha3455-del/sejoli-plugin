<?php

namespace SejoliSA\CLI;

class Options {

    /**
     * Get all available payment options
     *
     * ## EXAMPLES
     *
     *  wp sejolisa options payment_options
     *
     * @when after_wp_load
     */
    public function payment_options() {
        __debug(sejolisa_get_payment_options());
    }

    /**
     * Get all district options
     *
     * <search>
     * : Search term
     *
     *  wp sejolisa options district_options pondok
     *
     * @when after_wp_load
     */
    public function distric_options(array $args) {

        list($search) = $args;

        __debug(sejolisa_get_district_options($search));
    }

    /**
     * Display district detail
     *
     * <district_id>
     * : District ID
     *
     *  wp sejolisa options display_district_detail 11
     *
     * @when after_wp_load
     */
    public function display_district_detail(array $args) {

        list($subdistrict_id) = $args;

        ob_start();
		require SEJOLISA_DIR . 'json/subdistrict.json';
		$json_data = ob_get_contents();
		ob_end_clean();

		$subdistricts        = json_decode($json_data, true);
        $key                 = array_search($subdistrict_id, array_column($subdistricts, 'subdistrict_id'));
        $current_subdistrict = $subdistricts[$key];

		__debug($current_subdistrict);
    }
}
