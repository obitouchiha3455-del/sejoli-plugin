<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;

Class Confirmation extends \SejoliSA\Model
{
    static protected $detail = NULL;
    static protected $table  = 'sejolisa_confirmations';
    /**
     * Create table if not exists
     * @return void
     */
    static public function create_table()
    {
        parent::$table = self::$table;

        if(!Capsule::schema()->hasTable( self::table() )):
            Capsule::schema()->create( self::table(), function($table){
                $table->increments('ID');
                $table->datetime('created_at');
                $table->integer('order_id');
                $table->integer('product_id');
                $table->integer('user_id')->default(0);
                $table->text('detail');
            });
        endif;
    }

    /**
     * Set detail value property
     * @since   1.1.6
     */
    static public function set_detail($detail) {
        self::$detail = (array) $detail;
        return new static;
    }

    /**
     * Validate
     * @since   1.1.6
     */
    static protected function validate() {

        if(empty(self::$order_id)) :
            self::set_valid(false);
            self::set_message( __('Nomor invoice belum diisi', 'sejoli'));
        endif;

        if(empty(self::$product_id)) :
            self::set_valid(false);
            self::set_message( __('Produk belum dipilih', 'sejoli'));
        endif;

    }

    /**
     * Check if confirmation is already made
     * @since   1.1.6
     */
    static protected function check() {

        return Capsule::table(self::table())
                    ->where('order_id', self::$order_id)
                    ->first();
    }

    /**
     * Get all order data
     */
    static public function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS confirmation' ))
                            ->select(Capsule::raw(' confirmation.*, product.post_title AS product'))
                            ->join( Capsule::raw( $wpdb->posts . ' AS product '), 'product.ID', '=', 'confirmation.product_id');

        $query        = self::set_filter_query( $query );

        $recordsTotal = $query->count();

        $query        = self::set_length_query($query);

        $confirmations= $query->get()
                            ->toArray();

        if ( $confirmations ) :

            self::set_respond('valid',true);
            self::set_respond('confirmations',$confirmations);
            self::set_respond('recordsTotal',$recordsTotal);
            self::set_respond('recordsFiltered',$recordsTotal);
        else:
            self::set_respond('valid', false);
            self::set_respond('confirmations', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Delete confirmation data before 30 days ago
     * @since   1.5.2
     */
    static public function delete() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table(self::table());
        $query        = self::set_filter_query( $query );

        $recordsTotal = $query->delete();

        return new static;
    }

    /**
     * Insert confirmation data
     * @since   1.1.6
     */
    static public function insert() {

        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $exists = self::check();

            if(NULL !== $exists) :
                Capsule::table(self::table())
                    ->where('order_id', self::$order_id)
                    ->update(array(
                        'created_at'    => current_time('mysql'),
                        'detail'        => serialize(self::$detail)
                    ));

                self::set_valid(true);
                self::set_message( __('Konfirmasi diupdate', 'sejoli'), 'success');
            else :

                Capsule::table(self::table())
                    ->insert(array(
                        'created_at' => current_time('mysql'),
                        'order_id'   => self::$order_id,
                        'user_id'    => self::$user_id,
                        'product_id' => self::$product_id,
                        'detail'     => serialize(self::$detail)
                    ));

                self::set_valid(true);
                self::set_message( __('Konfirmasi baru', 'sejoli'), 'success');

            endif;

        endif;

        return new static;
    }

    /**
     * Get single confirmation data
     * @since 1.1.6
     */
    static public function single() {

        global $wpdb;

        parent::$table = self::$table;

        $data  = Capsule::table( Capsule::raw( self::table() . ' AS confirmation' ))
                    ->select(Capsule::raw(' confirmation.*, product.post_title AS product'))
                    ->join( Capsule::raw( $wpdb->posts . ' AS product '), 'product.ID', '=', 'confirmation.product_id')
                    ->where('confirmation.id', self::$id)
                    ->first();

        if($data) :
            self::set_valid(true);
            self::set_respond('confirmation', $data);
        else :
            self::set_valid(false);
            self::set_respond('confirmation', NULL);
        endif;

        return new static;
    }
}
