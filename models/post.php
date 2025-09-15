<?php
namespace SejoliSA\Model;

class Post
{
    protected static $chunk   = 10;
    protected static $args    = [];
    protected static $posts   = [];
    protected static $respond = [];
    protected static $total   = 10;

    /**
     * Construct
     * @param array $args [description]
     */
    public static function set_args(array $args)
    {
        self::$args = wp_parse_args($args,[
            'post_type'              => 'post',
            'posts_per_page'         => 10,
            'cache_results'          => false, // do not cache the result
            'update_post_meta_cache' => false, // do not cache the result
            'update_post_term_cache' => false, // do not cache the result
        ]);

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
     * Set how many posts to retrieve
     * @param integer $total [description]
     */
    public static function set_total($total)
    {
        $total  = intval($total);

        if(0 === $total || -1 === $total) :
            self::$total = self::$args['posts_per_page'];
        else :
            self::$total = $total;
        endif;

        return new static;
    }

    /**
     * Set current query to be cached
     */
    public static function add_cache()
    {
        unset(
            self::$args['cache_results'],
            self::$args['update_post_meta_cache'],
            self::$args['update_post_term_cache']
        );

        return new static;
    }

    /**
     * Do the query
     * @return [type] [description]
     */
    protected static function query()
    {
        $total_posts = 0;
        $get_total_posts = (isset(self::$args['posts_per_page'])) ? self::$args['posts_per_page'] : 10;
        $query = new \WP_Query();

        while($total_posts < self::$total) :
            $args           = self::$args;
            $args['offset'] = $total_posts;
            $posts          = $query->query($args);

            if($query->have_posts()) :
                self::$posts    = array_merge(self::$posts,$query->posts);
                $total_posts += $query->found_posts;
            else :
                break;
            endif;
        endwhile;
    }

    /**
     * Get the data
     * @return array WP_Post
     */
    public static function get()
    {
        self::query();

        return self::$posts;
    }
}
