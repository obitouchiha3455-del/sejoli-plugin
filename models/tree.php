<?php

namespace SejoliSA\Model;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AffiliateTree extends \SejoliSA\Model
{
    static private $upline_tier   = 1;     // set maximal upline view;
    static private $downline_tier = 10;    // set maximal downline depth
    static private $downline_root = 0;     // set maximal downline feet, 0 if unlimited
    static private $force_balance = false; // force each foot to be balanced when $downline_root is set with value
    static private $format_return = 'id';  // return data format

    /**
     * Reset all property value
     */
    static public function reset() {
        self::$upline_tier   = 1;
        self::$downline_tier = 10;
        self::$downline_root = 0;
        self::$force_balance = false;
        self::$format_return = 'id';

        parent::reset();
        return new static;
    }

    /**
     * Set user_id
     */
    static public function set_user_id( $user_id )
    {
        self::$user_id = absint($user_id);
        return new static;
    }

    /**
     * Set upline max tier
     */
    static public function set_upline_tier($tier)
    {
        self::$upline_tier = absint($tier);
        return new static;
    }

    /**
     * Set downline max tier
     */
    static public function set_downline_tier($tier)
    {
        self::$downline_tier = absint($tier);
        return new static;
    }

    /**
     * Set maximal downline root
     */
    static public function set_downline_root($root)
    {
        self::$downline_root = absint($root);
        return new static;
    }

    /**
     * Set force balance value
     */
    static public function set_balance($balance)
    {
        self::$force_balance = boolval($root);
        return new static;
    }

    /**
     * Validate process
     */
    static protected function validate($action = '')
    {
        if(!is_numeric(parent::$user_id)) :
            self::set_valid(false);
            self::set_message(sprintf(__('User id %s is not valid','sejoli')),parent::$user_id);
        endif;
    }

    /**
     * Get uplines data
     */
    static public function get_uplines()
    {
        self::validate();

        if(true === parent::$valid) :
            $uplines = [];
            $tier    = 1; 
            $limit   = self::$upline_tier;
            $upline  = sejolisa_get_affiliate(parent::$user_id,self::$format_return);

            if(0 !== $upline) :
                
                $uplines[$tier] = $upline;
                

                while(0 === $limit || $tier < $limit) :

                    $_affiliate    = $uplines[$tier];
                    $_affiliate_id = (is_a($_affiliate,'WP_User')) ? $_affiliate->ID : $_affiliate;
                    $_affiliate    = sejolisa_get_affiliate($_affiliate_id,self::$format_return);

                    if(is_null($_affiliate) || 0 === $_affiliate) :
                        break;
                    endif;

                    $tier++;
                    $uplines[$tier]    = $_affiliate;

                endwhile;
            endif;

            self::set_valid(true);
            self::set_respond('uplines',$uplines);
            
        endif;

        return new static;
    }


    /**
     * Get user uplines data
     */
    static public function get_user_uplines()
    {
        self::validate();

        if(true === parent::$valid) :
            $uplines = [];
            $tier    = 1;
            $limit   = self::$upline_tier;
            $upline  = sejolisa_get_affiliate(parent::$user_id,self::$format_return);

            if(0 !== $upline) :

                $uplines[$tier] = $upline;

                while(0 === $limit || $tier < $limit) :

                    $_affiliate    = $uplines[$tier];
                    $_affiliate_id = (is_a($_affiliate,'WP_User')) ? $_affiliate->ID : $_affiliate;
                    $_affiliate    = sejolisa_get_affiliate($_affiliate_id,self::$format_return);

                    if(is_null($_affiliate) || 0 === $_affiliate) :
                        break;
                    endif;

                    $tier++;
                    $uplines[$tier]    = $_affiliate;

                endwhile;

            endif;

            $results = array_reverse($uplines);

            $list_data = array();
            $n_result = count($results);
            foreach ($results as $item) {

                $parent_id = '';
                $has_parent = get_user_meta( $item, sejolisa_get_affiliate_key() , true );

                if($has_parent){
                    
                    if($n_result > 1):
                        $parent_id = $has_parent; 
                    else:
                        $parent_id = '#';
                    endif;                    
                    
                }else{
                    $parent_id = '#';
                }

                $user_info = get_userdata($item);
                $display_name = $user_info->display_name;
                $avatar_url   = get_avatar_url( $item );

                $data = array(
                        'id'       => $item,
                        'parent'   => $parent_id,
                        'text'     => $display_name,
                        'icon'     => $avatar_url,
                        'a_attr'   => array(
                            'data-ID'   => $item
                        )
                );

                $list_data[] = $data;
            }


            self::set_valid(true);
            self::set_respond('uplines',$list_data);
            
        endif;

        return new static;
    }

    /**
     * Get downlines recursively
     */
    static private function get_downlines_recusively($results,$query,$downlines,$tier,$limit)
    {
        if(0 !== $limit && $tier === $limit) :
            return false;
        endif;

        $tier = $tier + 1;

        if(is_array($results) && 0 < count($results)) :
            foreach($results as $i => $_user_id) :
                $downlines[$i]['id'] = $_user_id;

                $args   = [
                    'meta_query' => [
                        [
                            'key'   => sejolisa_get_affiliate_key(),
                            'value' => $_user_id
                        ]
                    ],
                    'fields' => 'id'
                ];

                $query->prepare_query($args);
                $query->query();

                $_results = $query->get_results();
                $downlines[$i]['downlines'] = self::get_downlines_recusively($_results,$query,[],$tier,$limit);
            endforeach;
        endif;

        return $downlines;
    }

    /**
     * Get downline data
     */
    static public function get_downlines()
    {
        self::validate();

        if(true === parent::$valid) :
            $limit   = self::$downline_tier;
            $args   = [
                'meta_query' => [
                    [
                        'key'   => sejolisa_get_affiliate_key(),
                        'value' => parent::$user_id
                    ]
                ],
                'fields' => ('id' === self::$format_return) ? 'ID' : self::$format_return
            ];

            $query     = new \WP_User_Query($args);
            $results   = $query->get_results();
            $downlines = self::get_downlines_recusively($results,$query,[],0,$limit);

            self::set_valid(true);
            self::set_respond('downlines',$downlines);
        endif;

        return new static;
    }


    

    /**
     * Get user downlines for users.php page (admin)
     */
    static public function get_user_downlines()
    {
        self::validate();

        if(true === parent::$valid) :
            $limit   = self::$downline_tier;
            $args   = [
                'meta_query' => [
                    [
                        'key'   => sejolisa_get_affiliate_key(),
                        'value' => parent::$user_id
                    ]
                ],
                'fields' => ('id' === self::$format_return) ? 'ID' : self::$format_return
            ];

            $query     = new \WP_User_Query($args);
            $results   = $query->get_results();
            
            $downlines = self::get_downlines_recusively($results,$query,[],0,$limit);

            
            self::set_valid(true);
            self::set_respond('downlines',$downlines);            
        endif;

        return new static;
    }
}
