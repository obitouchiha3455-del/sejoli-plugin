<?php
namespace SejoliSA\Model;

use Illuminate\Database\Capsule\Manager as Capsule;

Class Reminder extends \SejoliSA\Model
{
    static protected $table         = 'sejolisa_reminders';
    static protected $recipient     = NULL;
    static protected $title         = NULL;
    static protected $content       = NULL;
    static protected $send_day      = NULL;
    static protected $send_hour     = NULL;
    static protected $interval      = NULL;
    static protected $date          = NULL;
    static protected $media_type    = NULL;
    static protected $reminder_type = 'order';
    static protected $status        = false;
    static protected $hour          = 24;
    static protected $day           = 30;

    /**
     * Create table if not exists
     * @return void
     */
    static public function create_table() {

        parent::$table = self::$table;

        if(!Capsule::schema()->hasTable( self::table() )):
            Capsule::schema()->create( self::table(), function($table){
                $table->increments('ID');
                $table->datetime('created_at');
                $table->datetime('sent_at')->default('0000-00-00 00:00:00');
                $table->mediumInteger('send_day');
                $table->integer('order_id');
                $table->string('title');
                $table->text('content');
                $table->string('media_type');
                $table->string('reminder_type');
                $table->string('status');
            });
        endif;

    }

     /**
     * Create table if not exists
     * @return void
     */
    static public function alter_table() {

        parent::$table = self::$table;

        if(Capsule::schema()->hasTable( self::table() )):

            if(!Capsule::schema()->hasColumn( self::table(), 'send_hours' )):
            
                Capsule::schema()->table( self::table(), function($table){

                    $table->mediumInteger('send_hours')->after('send_day');

                });

            endif;
        
        endif;

    }

    /**
     * Reset data
     */
    static public function reset() {
                                                                                                                                                                                                                         
        self::$recipient     = NULL;
        self::$title         = NULL;
        self::$content       = NULL;
        self::$send_day      = NULL;
        self::$send_hour     = NULL;
        self::$interval      = NULL;
        self::$date          = NULL;
        self::$media_type    = NULL;
        self::$reminder_type = 'order';
        self::$status        = false;
        self::$hour          = 24; 
        self::$day           = 30;

        parent::reset();

        return new static;
    }

    /**
     * Set recipient value
     * @since   1.1.9
     */
    static public function set_recipient($recipient) {
        self::$recipient = $recipient;
        return new static;
    }

    /**
     * Set title value
     * @since   1.1.9
     */
    static public function set_title($title) {
        self::$title = $title;
        return new static;
    }

    /**
     * Set content value
     * @since   1.1.9
     */
    static public function set_content($content) {
        self::$content = $content;
        return new static;
    }

    /**
     * Set send day
     * @since   1.1.9
     */
    static public function set_send_day($send_day) {
        self::$send_day = intval($send_day);
        return new static;
    }

    /**
     * Set send hour
     * @since   1.1.9
     */
    static public function set_send_hour($send_hour) {
        self::$send_hour = intval($send_hour);
        return new static;
    }

    /**
     * Set media type
     * @since   1.1.9
     */
    static public function set_media_type($media_type) {
        self::$media_type = in_array($media_type, array('email', 'whatsapp', 'sms')) ? $media_type : 'email';
        return new static;
    }

    /**
     * Set reminder_type
     * @since   1.1.9
     */
    static public function set_reminder_type($reminder_type) {
        self::$reminder_type = in_array($reminder_type, array('order', 'recurring')) ? $reminder_type : 'order';
        return new static;
    }

    /**
     * Set status
     * @since   1.1.9
     */
    static public function set_status($status) {
        self::$status = boolval($status);
        return new static;
    }

    /**
     * Set interval
     * @since   1.1.9
     */
    static public function set_interval($interval) {
        self::$interval = $interval;
        return new static;
    }

    /**
     * Set date
     * @since   1.1.9
     */
    static public function set_date($date) {
        self::$date = $date;
        return new static;
    }

    /**
     * Set different day
     * @since   1.1.9
     */
    static public function set_day($day) {
        self::$day = intval($day);
        return new static;
    }

    /**
     * Set different hour
     * @since   1.1.9
     */
    static public function set_hour($hour) {
        self::$hour = intval($hour);
        return new static;
    }

    /**
     * Validate input
     * @since   1.1.9
     */
    static protected function validate() {

        if(in_array(self::$action, ['add'])) :

            if(0 === self::$order_id) :
                self::set_valid(false);
                self::set_message( __('Order ID harus diisi', 'sejoli'));
            endif;

            if(empty(self::$recipient)) :
                self::set_valid(false);
                self::set_message( __('Penerima harus diisi', 'sejoli'));
            endif;

            if(empty(self::$title)) :
                self::set_valid(false);
                self::set_message( __('Title harus diisi', 'sejoli'));
            endif;

            if(empty(self::$content)) :
                self::set_valid(false);
                self::set_message( __('Konten harus diisi', 'sejoli'));
            endif;

            if(0 === empty(self::$send_day)) :
                self::set_valid(false);
                self::set_message( __('Hari pengiriman tidak boleh 0', 'sejoli'));
            endif;

            if(0 === empty(self::$send_hour)) :
                self::set_valid(false);
                self::set_message( __('Jam pengiriman tidak boleh 0', 'sejoli'));
            endif;

            if(!in_array(self::$media_type, array('email', 'whatsapp', 'sms'))) :
                self::set_valid(false);
                self::set_message( __('Media pengiriman tidak valid', 'sejoli'));
            endif;

            if(!in_array(self::$reminder_type, array('order', 'recurring'))) :
                self::set_valid(false);
                self::set_message( __('Tipe pengingat tidak valid', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['get' ])) :

            if(empty(self::$date)) :
                self::set_valid(false);
                self::set_message( __('Tanggal matches tidak ada', 'sejoli'));
            endif;

            if(empty(self::$day)) :
                self::set_valid(false);
                self::set_message( __('Hari pengiriman kosong', 'sejoli'));
            endif;

            if(empty(self::$hour)) :
                self::set_valid(false);
                self::set_message( __('Jam pengiriman kosong', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['send'] ) ) :

            if(!is_array(self::$ids) || 0 === count(self::$ids)) :
                self::set_valid(false);
                self::set_message( __('ID pengiriman tidak ada', 'sejoli'));
            endif;

        endif;

        if(in_array(self::$action, ['get'])) :


        endif;
    }

    /**
     * Add data
     * @since   1.1.9
     */
    static public function add() {

        self::set_action('add');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            $data = array(
                'order_id'      => self::$order_id,
                'created_at'    => current_time('mysql'),
                'send_day'      => self::$send_day,
                'send_hours'    => self::$send_hour,
                'title'         => self::$title,
                'content'       => self::$content,
                'media_type'    => self::$media_type,
                'reminder_type' => self::$reminder_type,
                'status'        => false
            );

            $result = Capsule::table( self::table() )
                        ->insertGetId($data);

            $data['ID'] = $result;

            self::set_valid(true);
            self::set_respond('reminder', $data);
        endif;

        return new static;
    }

    /**
     * Get all reminder data
     */
    static public function get() {

        global $wpdb;

        parent::$table = self::$table;

        $query        = Capsule::table( Capsule::raw( self::table() . ' AS reminder' ) )
                            ->select(
                                Capsule::raw('reminder.*, data_order.user_id')
                                )
                            ->join(
                                Capsule::raw($wpdb->prefix . 'sejolisa_orders AS data_order'),
                                'data_order.ID',
                                '=',
                                'reminder.order_id'
                            );

        $query        = self::set_filter_query( $query );

        $recordsTotal = $query->count();
        $query        = self::set_length_query($query);

        $reminders    = $query->get()
                            ->toArray();

        if ( $reminders ) :

            self::set_respond('valid', true);
            self::set_respond('reminders', $reminders);
            self::set_respond('recordsTotal', $recordsTotal);
            self::set_respond('recordsFiltered',
                    (0 < parent::$filter['length']) ?
                    parent::$filter['length'] :
                    $recordsTotal
                );
        else:
            self::set_respond('valid', false);
            self::set_respond('reminders', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Delete data
     * @since   1.2.0
     */
    static public function delete() {

        parent::$table = self::$table;

        $query         = Capsule::table( self::table() );
        $query         = self::set_filter_query( $query );
        $records_total = $query->count();


        if(0 < intval($records_total)) :

            $response      = $query->delete();

            self::set_valid(true);
            self::set_message( sprintf(__('Total %d record found and deleted', 'sejoli'), intval($records_total)), 'success' );

        else :

            self::set_valid(false);
            self::set_message( __('No record found'), 'success' );

        endif;

        return new static;

    }

    /**
     * Update sent status
     * @since   1.1.9
     */
    static public function update_send_status() {

        self::set_action('send');
        self::validate();

        if(false !== self::$valid) :

            parent::$table = self::$table;

            Capsule::table( self::table() )
                ->whereIn('ID', self::$ids)
                ->update(array(
                    'sent_at' => current_time('mysql'),
                    'status'  => true
                ));

            self::set_respond('valid', true);
        endif;

        return new static;
    }

    /**
     * Get queue data
     * @since   1.1.9
     */
    static protected function get_order_in_queue($type = 'order') {
        $order_in_queue = array();

        $result = Capsule::table( self::table() . ' AS reminder')
            ->select('order_id')
            ->where('reminder.send_day', self::$day)
            ->orWhere('reminder.send_hours', self::$hour)
            ->where('reminder.reminder_type', $type)
            ->get();

        if($result) :
            foreach($result as $_data) :
                $order_in_queue[] = $_data->order_id;
            endforeach;
        endif;

        return $order_in_queue;
    }

    /**
     * Get all order data that need to be reminded
     * @since   1.1.9
     */
    static public function get_by_order() {

        self::set_action('get');
        self::validate();

        if(false !== self::$valid) :

            global $wpdb;

            parent::$table = self::$table;
            $order_in_queue = self::get_order_in_queue();

            if(self::$interval === "reminder_per_day"):
                
                $query     = Capsule::table( Capsule::raw($wpdb->prefix. 'sejolisa_orders AS data_order'))
                                    ->select( Capsule::raw('data_order.ID, data_order.created_at') )
                                    // ->leftJoin( Capsule::raw( self::table() . ' AS reminder '), 'data_order.ID', '=', 'reminder.order_id' )
                                    ->whereBetween('data_order.created_at', array(
                                        self::$date . ' 00:00:00',
                                        self::$date . ' 23:59:59'
                                    ))
                                    ->where('data_order.status', 'on-hold');

            else:

                $query     = Capsule::table( Capsule::raw($wpdb->prefix. 'sejolisa_orders AS data_order'))
                                    ->select( Capsule::raw('data_order.ID, data_order.created_at') )
                                    ->where('data_order.created_at', "like", "%" . self::$date . "%")
                                    ->where('data_order.status', 'on-hold');
            endif;

            if(0 < count($order_in_queue)) :
                $query = $query->whereNotIn('data_order.ID', $order_in_queue);
            endif;

            $result = $query->get()->toArray();

            if($result) :
                self::set_valid(true);
                self::set_respond('orders', $result);
            else :
                self::set_valid(false);
                self::set_respond('orders', array());
            endif;

        endif;

        return new static;

    }

    /**
     * Get all subscription data that need to be reminded
     * @since   1.1.9
     */
    static public function get_by_subscription() {

        self::set_action('get');
        self::validate();

        if(false !== self::$valid) :

            global $wpdb;

            parent::$table = self::$table;
            $order_in_queue = self::get_order_in_queue('recurring');

            if(self::$interval === "reminder_per_day"):
                
                $query     = Capsule::table( Capsule::raw($wpdb->prefix. 'sejolisa_subscriptions AS subscription'))
                                ->select( Capsule::raw('subscription.order_id, subscription.end_date, subscription.order_parent_id, subscription.user_id') )
                                ->whereBetween('subscription.end_date', array(
                                    self::$date . ' 00:00:00',
                                    self::$date . ' 23:59:59'
                                ))
                                ->where('subscription.status', 'active');

            else:

                $query     = Capsule::table( Capsule::raw($wpdb->prefix. 'sejolisa_subscriptions AS subscription'))
                                ->select( Capsule::raw('subscription.order_id, subscription.end_date, subscription.order_parent_id, subscription.user_id') )
                                ->where('subscription.end_date', "like", "%" . self::$date . "%")
                                ->where('subscription.status', 'active');
                
            endif;

            if(0 < count($order_in_queue)) :
                $query = $query->whereNotIn('subscription.order_id', $order_in_queue);
            endif;

            $result = $query->get()->toArray();

            $parent_order_id = ($result) ? $result[0]->order_parent_id : null;
               
            if($parent_order_id) :

                $get_renewall_order = sejolisa_get_renewall_order($result[0]->order_parent_id, $result[0]->user_id);

                if(self::$interval === "reminder_per_day"):
                    
                    $query     = Capsule::table( Capsule::raw($wpdb->prefix. 'sejolisa_subscriptions AS subscription'))
                                    ->select( Capsule::raw('subscription.order_id, subscription.end_date, subscription.order_parent_id, subscription.user_id') )
                                    ->whereBetween('subscription.end_date', array(
                                        self::$date . ' 00:00:00',
                                        self::$date . ' 23:59:59'
                                    ))
                                    ->where('subscription.order_id', $get_renewall_order)
                                    ->where('subscription.status', 'active');

                else:

                    $query     = Capsule::table( Capsule::raw($wpdb->prefix. 'sejolisa_subscriptions AS subscription'))
                                    ->select( Capsule::raw('subscription.order_id, subscription.end_date, subscription.order_parent_id, subscription.user_id') )
                                    ->where('subscription.end_date', "like", "%" . self::$date . "%")
                                    ->where('subscription.order_id', $get_renewall_order)
                                    ->where('subscription.status', 'active');
                    
                endif;

                if(0 < count($order_in_queue)) :
                    $query = $query->whereNotIn('subscription.order_id', $order_in_queue);
                endif;

                $result = $query->get()->toArray();

            endif;

            if($result) :
                self::set_valid(true);
                self::set_respond('subscriptions', $result);
            else :
                self::set_valid(false);
                self::set_respond('subscriptions', array());
            endif;

        endif;

        return new static;

    }
}
