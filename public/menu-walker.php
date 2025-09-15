<?php

namespace SejoliSA\Front;

class MenuWalker extends \Walker
{
    protected $object;

    protected $menu_has_children = false;

    /**
     * @since   1.1.7
     * @var     array
     */
    protected $menu_setup = array(
        'before'      => NULL,
        'after'       => NULL,
        'link_before' => NULL,
        'link_after'  => NULL,
    );

    /**
     * @since   1.1.7
     * @var     array
     */
    protected $menu_icon = array(
        'dashboard'   => 'tachometer alternate',
        'affiliasi'   => 'bullhorn',
        'leaderboard' => 'trophy',
        'order'       => 'shopping cart',
        'langganan'   => 'stopwatch',
        'download'    => 'download',
        'akses'       => 'download',
        'lisensi'     => 'key',
        'profile'     => 'user',
        'logout'      => 'sign-out'
    );

    /**
     * @since   1.1.9
     * @var     array
     */
    protected $menu_url = array();
    protected $menu_object_url = array();

    var $tree_type = array( 'post_type', 'taxonomy', 'custom' );
    var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );

    /**
     * Construction
     * @since 1.1.6
     */
    public function __construct() {
        $this->menu_url = array(
            'dashboard'   => home_url('/member-area/'),
            'leaderboard' => home_url('/member-area/leaderboard'),
            'order'       => home_url('/member-area/order'),
            'langganan'   => home_url('/member-area/subscription'),
            'akses'       => home_url('/member-area/akses'),
            'profile'     => home_url('/member-area/profile'),
            'lisensi'     => home_url('/member-area/license'),
            'logout'      => wp_logout_url( site_url('member-area/login/') )
        );

        $this->menu_object_url = array(
            'sejoli-dashboard'   => home_url('/member-area/'),
            'sejoli-leaderboard' => home_url('/member-area/leaderboard'),
            'sejoli-order'       => home_url('/member-area/order'),
            'sejoli-langganan'   => home_url('/member-area/subscription'),
            'sejoli-akses'       => home_url('/member-area/akses'),
            'sejoli-profile'     => home_url('/member-area/profile'),
            'sejoli-lisensi'     => home_url('/member-area/license'),
            'sejoli-logout'      => wp_logout_url( site_url('member-area/login/') )
        );
    }

    /**
     * Start child menu
     * @since   1.1.7
     * @param   string  $output [description]
     * @param   integer $depth  [description]
     * @param   array   $args   [description]
     * @return  void
     */
    public function start_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent";
        $output .= "<ul class=\"menu\">\n";
    }

    /**
     * End child menu
     * @since   1.1.7
     * @param   string  $output [description]
     * @param   integer $depth  [description]
     * @param   array   $args   [description]
     * @return  void
     */
    public function end_lvl(&$output, $depth = 0, $args = array()) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    /**
     * Set affiliate menu
     * @since   1.1.7
     * @param   array   $args   Parameters and argumnets
     * @return  string
     */
    protected function set_affiliate_menu($args) {

        // YES IM LAZY
        extract($args);

        $menus = sejolisa_get_member_area_menu();

        if(!sejolisa_check_user_can_access_affiliate_page()) :
            return;
        endif;

        ob_start();

        $cs_carbon_icon = carbon_get_nav_menu_item_meta( $object->ID, 'menu_icon');
        $menu_name      = strtolower($object->post_name);

        if( $cs_carbon_icon ) :

            $icon = $cs_carbon_icon;

        else:

            $icon = $this->menu_icon[$menu_name];

        endif;
        ?>
        <div class="master-menu">
            <a href="javascript:void(0)" class='item'>
                <?php echo '<i class="'.$icon.' icon"></i>'; ?>
                <?php echo apply_filters( 'the_title', $title, $ID ); ?>
            </a>
            <ul class="menu">
            <?php foreach( (array) $menus['affiliate']['submenu'] as $submenu ) : ?>
                <li>
                    <a href="<?php echo $submenu['link']; ?>" class="<?php echo $submenu['class']; ?>">
                    <?php if( !empty( $submenu['icon'] ) ) : ?>
                    <i class="<?php echo $submenu['icon']; ?>"></i>
                    <?php endif; ?>
                    <?php echo $submenu['label']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php

        $item_output = ob_get_contents();
        ob_end_clean();

        return $item_output;
    }

    /**
     * Set parent menu
     * @since   1.1.7
     * @param   array   $args           Parameters and arguments
     * @param   string  $current_page   Current member page
     * @return  string
     */
    protected function set_parent_member_menu($args, $current_page) {

        // YES IM LAZY
        extract($args);

        $class_names   .= ($current_page === $object->post_name) ? ' active' : '';
        $class_names   .= ' item';
        $class_names    = strlen( trim( $class_names ) ) > 0 ? ' class="' . esc_attr( $class_names ) . '"' : '';
        $item_output    = '';
        $cs_carbon_icon  = carbon_get_nav_menu_item_meta( $object->ID, 'menu_icon');

        if('sejoli-affiliasi' === $object->object) :

            $item_output = $this->set_affiliate_menu($args);

        else :

            $item_output = apply_filters('sejoli/member-area/menu-link', $item_output, $object, $args, $this->menu_setup);

            if(empty($item_output)) :

                if('sejoli-member-link' === $object->type ) :

                    $menu_name = strtolower($object->post_name);
                    $menu_object = strtolower($object->object);

                    if('dashboard' === $menu_name) :
                        $url = home_url('/member-area/');
                    elseif('logout' === $menu_name) :
                        $url = wp_logout_url( site_url('member-area/login/') );
                    elseif(isset($this->menu_url[$menu_name])) :
                        $url = $this->menu_url[$menu_name];
                    elseif(isset($this->menu_object_url[$menu_object])) :
                        $url = $this->menu_object_url[$menu_object];
                    else :
                        $url = apply_filters('sejoli/member-area/menu-url', '', $object);
                    endif;

                    if( $cs_carbon_icon ) :

                        $icon = $cs_carbon_icon;

                    else:

                        $icon = $this->menu_icon[$menu_name];

                    endif;
                    
                    $value = ' href="' . $url . '"';
                    $item_output .= $this->menu_setup['before'];
                    $item_output .= '<a'. $attributes . $attr_id . $value . $class_names . '>';
                    $item_output .= $this->menu_setup['link_before'] . apply_filters( 'the_title', $title, $ID ) . $this->menu_setup['link_after'];
                    $item_output .= '<i class="'.$icon.' icon"></i>';
                    $item_output .= "</a>\n";
                    $item_output .= $this->menu_setup['after'];

                    if(true === boolval($has_children)) :
                        $item_output = '<div class="master-menu">' . $item_output;
                    endif;

                else :

                    $value = ' href="' . get_permalink($object->ID) . '"';
                    $item_output .= $this->menu_setup['before'];
                    $item_output .= '<a'. $attributes . $attr_id . $value . $class_names . '>';
                    $item_output .= $this->menu_setup['link_before'] . apply_filters( 'the_title', $title, $ID ) . $this->menu_setup['link_after'];

                    $icon = carbon_get_nav_menu_item_meta( $object->ID, 'menu_icon');

                    if(!empty($icon)) :
                        $item_output .= '<i class="'.$icon.' icon"></i>';
                    endif;

                    $item_output .= "</a>\n";
                    $item_output .= $this->menu_setup['after'];

                endif;

            endif;

        endif;

        return $item_output;
    }

    /**
     * Set parent regular menu
     * @since   1.1.7
     * @param   array   $args           Parameters and arguments
     * @param   string  $current_page   Current member page
     * @return  string
     */
    protected function set_parent_regular_menu($args) {

        // YES IM LAZY
        extract($args);

        ob_start();

        if(false !== boolval($has_children)) :
            $icon = carbon_get_nav_menu_item_meta( $object->ID, 'menu_icon');

            ?>
            <div class="master-menu">
                <a href="javascript:void(0)" class='item'>
                    <?php if(!empty($icon)) : ?>
                    <i class='<?php echo $icon; ?> icon'></i>
                    <?php endif; ?>
                    <?php echo apply_filters( 'the_title', $title, $ID ); ?>
                </a>
            <?php
        else :
            $icon = carbon_get_nav_menu_item_meta( $object->ID, 'menu_icon');
            ?>
            <a href="<?php echo $object->url; ?>" class="item">
                <?php if( !empty( $icon ) ) : ?>
                <i class="<?php echo $icon; ?> icon"></i>
                <?php endif; ?>
                <?php echo apply_filters( 'the_title', $title, $ID ); ?>
            </a>
            <?php
        endif;

        $item_output = ob_get_contents();
        ob_end_clean();

        return $item_output;
    }

    /**
     * Set child regular menu
     * @since   1.1.7
     * @param   array   $args           Parameters and arguments
     * @param   string  $current_page   Current member page
     * @return  string
     */
    protected function set_child_regular_menu($args) {
        extract($args);

        $icon      = carbon_get_nav_menu_item_meta( $object->ID, 'menu_icon'); 
        $menu_name = strtolower( $object->post_name );
        
        ob_start();
        ?>
        <li>
            <?php if( $object->url ): ?>
                <a href="<?php echo $object->url; ?>" class="item">
            <?php elseif( isset( $this->menu_url[$menu_name] ) ) : ?>
                <?php $setChildLink = $this->menu_url[$menu_name]; ?>
                <a href="<?php echo $setChildLink; ?>" class="item">
            <?php else: ?>
                <?php $setChildLink = apply_filters( 'sejoli/member-area/menu-url', '', $object ); ?>
                <a href="<?php echo $setChildLink; ?>" class="item">
            <?php endif; ?>
                <?php if( !empty( $icon ) ) : ?>
                <i class="<?php echo $icon; ?> icon"></i>
                <?php endif; ?>
                <?php echo apply_filters( 'the_title', $title, $ID ); ?>
            </a>
        </li>
        <?php

        $item_output = ob_get_contents();
        ob_end_clean();

        return $item_output;
    }

    /**
     * Start element
     * @since   1.1.7
     * @param  [type]  $output [description]
     * @param  [type]  $object [description]
     * @param  integer $depth  [description]
     * @param  array   $args   [description]
     * @return [type]          [description]
     */
    public function start_el(&$output, $object, $depth = 0, $args = array(), $current_object_id = 0) {

        if (!isset($args->theme_location) || 'sejoli-member-nav' !== $args->theme_location) {
            return;
        }

        $value = '';

        $classes = empty($object->classes) ? array() : (array) $object->classes;
        $classes = in_array('current-menu-item', $classes) ? array('current-menu-item') : array();

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $object, $args));

        $id = apply_filters('nav_menu_item_id', '', $object, $args);
        $id = !empty($id) ? ' id="' . esc_attr($id) . '"' : '';

        $attributes  = !empty($object->attr_title) ? ' title="' . esc_attr($object->attr_title) . '"' : '';
        $attributes .= !empty($object->target)     ? ' target="' . esc_attr($object->target) . '"' : '';
        $attributes .= !empty($object->xfn)        ? ' rel="' . esc_attr($object->xfn) . '"' : '';
        $attributes .= !empty($object->url)        ? ' href="' . esc_attr($object->url) . '"' : '';

        $this->menu_holder = array(
            'before'      => $args->before,
            'after'       => $args->after,
            'link_before' => $args->link_before,
            'link_after'  => $args->link_after,
        );

        $has_children = isset($args->walker->has_children) ? $args->walker->has_children : false;

        $menu_args = array(
            'object'      => $object,
            'attributes'  => $attributes,
            'attr_id'     => $id,
            'value'       => $value,
            'class_names' => $class_names,
            'title'       => $object->title,
            'ID'          => $object->ID,
            'has_children' => $has_children
        );

        if (0 === intval($depth)) {
            if ('sejoli-member-link' === $object->type) {
                $item_output = $this->set_parent_member_menu(
                    $menu_args,
                    sejolisa_get_current_member_page()
                );
            } else {
                $item_output = $this->set_parent_regular_menu($menu_args);
            }

            $this->menu_has_children = false;

        } else {
            $this->menu_has_children = true;
            $item_output = $this->set_child_regular_menu($menu_args);
        }

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $object, $depth, $args);

        $this->object = $object;
    }

    /**
     * End element
     * @since   1.1.7
     * @param  string   $output [description]
     * @param  WP_Post  $object [description]
     * @param  integer  $depth  [description]
     * @param  array    $args   [description]
     * @return string
     */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        if(
            true === $this->menu_has_children &&
            0 === intval($depth)
        ) :
            $output .= '</div>';
        endif;
    }
}
