<?php

namespace SejoliSA\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Attachment {

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

	protected $blacklist_extension_for_email = array('zip', 'exe');

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
	 * Setup attachment fields for product
	 * Hooked via filter sejoli/product/fields, priority 40
	 * @param  array  $fields
	 * @return array
	 */
	public function setup_attachment_setting_fields(array $fields) {

		$fields[] = [
			'title'	=> __('File', 'sejoli'),
			'fields' =>  [
				Field::make( 'separator', 'sep_file' , __('Pengaturan File', 'sejoli'))
					->set_classes('sejoli-with-help')
					->set_help_text('<a href="' . sejolisa_get_admin_help('file') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('complex', 'attachments', __('File produk', 'sejoli'))
                    ->add_fields([
                        Field::make('file', 'file', __('File', 'sejoli'))
                            ->set_required(true),
                        Field::make('textarea', 'description', __('Deskripsi produk', 'sejoli'))
                    ])
                    ->set_layout('tabbed-vertical')
            ]
        ];

        return $fields;
    }

	/**
	 * Add file data to product
	 * Hookes via filter sejoli/product/meta-data, priority 99
	 * @since 	1.0.0
	 * @param  	WP_Post 	$product [description]
	 * @return 	WP_Post
	 */
	public function setup_product_file_data(\WP_Post $product) {

		$product->files = [];

		$files = sejolisa_carbon_get_post_meta($product->ID, 'attachments');

		if( is_array($files) && 0 < count($files) ) :
			foreach( $files as $file ) :
				$file_id = $file['file'];
				$product->files[] = [
					'ID'	=> $file_id,
					'path'	=> get_attached_file( $file_id ),
					'link'	=> wp_get_attachment_url( $file_id )
				];
			endforeach;
		endif;

		return $product;
	}

	/**
	 * Add attchment files
	 * Hooked via filter sejoli/notification/email/attachments, priority 10
	 * @since 1.0.0
	 * @param array $attachments
	 * @param array $invoice_data
	 * @return array
	 */
	public function set_email_attachments($attachments = array(), $invoice_data = array()) {

		if(!isset($invoice_data['order_data']) || 'completed' !== $invoice_data['order_data']['status']) :
			return $attachments;
		endif;

		$files = $invoice_data['product_data']->files;

		foreach( (array) $files as $file ) :

			$file_parts = pathinfo($file['path']);
			$exts = isset($file_parts['extension']) ? $file_parts['extension'] : null;
			if(!in_array($exts, $this->blacklist_extension_for_email)) :
				$attachments[] = $file['path'];
			endif;
		endforeach;

		return $attachments;
	}

	/**
	 * Render attachment carbon field data to link
	 * Hooked via filter sejoli/attachments/links, priority 1
	 * @since 	1.0.0
	 * @param  array|null $attachments
	 * @return array
	 */
	public function get_links($attachments, $product_id) {

		if(is_array($attachments) && 0 < count($attachments)) :
			$temp = [];
			foreach($attachments as $attachment) :

				$key    = sejolisa_encrypt_decrypt('encrypt', get_current_user_id().':::'.$product_id.':::'.$attachment['file']);
				$file 	=  pathinfo(get_attached_file($attachment['file']));
				$exts = isset($file['extension']) ? $file['extension'] : null;
				$temp[] = [
					'name'	=> $file['filename'].'.'.$exts,
					'link'	=> home_url('/member-download/' . $key)
				];
			endforeach;
			$attachments = $temp;
		endif;

		return $attachments;
	}

}
