<?php
namespace SejoliSA\Model;

class User {

    protected static $chunk   = 10;
    protected static $users   = [];
    protected static $args    = array(
                                    'offset'  => 0,
                                    'number'  => 10,
                                    'orderby' => 'display_name',
                                    'order'   => 'ASC'
                                );
    protected static $respond = [];
    protected static $start   = 0;
    protected static $total   = 10;
    protected static $total_retrieved_users = 0;

    /**
     * Construct
     * @param array $args [description]
     */
    public static function set_args($args = array())
    {
        self::$args = wp_parse_args($args, self::$args);

        return new static;
    }

    /**
     * Set chunk
     * @param  integer $chunk
     */
    public static function set_chunk($chunk)
    {
        $chunk    = intval($chunk);
        self::$chunk = (0 < $chunk) ? self::$chunk : $chunk;

        return new static;
    }

    /**
     * Set start / offset data
     * @since   1.3.0
     * @param   integer $start
     */
    public static function set_start($start) {

        $start = intval($start);
        self::$args['offset'] = self::$start = (0 < $start) ? $start : 0;

        return new static;
    }

    /**
     * Set how many posts to retrieve
     * @param integer $total [description]
     */
    public static function set_total($total)
    {
        $total  = intval($total);

        if(0 === $total || -1 === $total) :
            self::$total = self::$args['number'];
        else :
            self::$total = $total;
        endif;

        self::$args['number'] = self::$total;

        return new static;
    }

    /**
     * Set sorting data
     * @since   1.3.0
     * @param   array $order
     */
    public static function set_sort($order = array()) {

        $order = wp_parse_args(array(
            'column'    => 'display_name',
            'sort'      => 'ASC'
        ));

        self::$args['order']   = $order['sort'];
        self::$args['orderby'] = $order['column'];

        return new static;
    }

    /**
     * Set filter search argument
     * @since   1.3.0
     * @param   array $filter
     */
    public static function set_filter($filter) {

        $filter = wp_parse_args($filter, array(
                    'ID'           => NULL,
                    'user_id'      => NULL,
                    'role'         => NULL,
                    'group'        => NULL,
                    'affiliate_id' => NULL
                  ));

        $meta_query = array();

        if(!empty($filter['role'])) :
            self::$args['role'] = (array) $filter['role'];
        endif;

        if(is_array($filter['ID'])) :
            self::$args['include'] = $filter['ID'];
        endif;

        if(isset($filter['user_id']) && !empty($filter['user_id'])) :
            self::$args['include'] = array( $filter['user_id'] );
        endif;

        if(!empty($filter['group'])) :

            $meta_query[] = array(
                'key'   => '_user_group',
                'value' => $filter['group']
            );

        endif;

        if(!empty($filter['affiliate_id'])) :

            $meta_query[] = array(
                'key'   => '_affiliate_id',
                'value' => $filter['affiliate_id']
            );

        endif;

        if(0 < count($meta_query)) :
            self::$args['meta_query']  = $meta_query;
        endif;

        return new static;
    }

    /**
     * Do the query
     * @return void
     */
    protected static function query()
    {
        $total_data_users = 0;
        $get_total_users  = (isset(self::$args['number'])) ? self::$args['number'] : 10;

        $i = 1;

        $user_query  = new \WP_User_Query(self::$args);
        self::$users = array_merge(self::$users, $user_query->get_results());
        self::$total_retrieved_users = $user_query->get_total();

    }

    /**
     * Get the data
     * @return array WP_Post
     */
    public static function get()
    {
        self::query();

        return array(
            'data' => self::$users,
            'total' => self::$total_retrieved_users
        );
    }

}
