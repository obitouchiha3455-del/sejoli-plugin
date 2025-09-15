<?php

$fallback_cb = function() {

    $menus = sejolisa_get_member_area_menu();

    if ( is_array( $menus ) && !empty( $menus ) ) :

        foreach ( $menus as $menu ) :

            if ( !empty( $menu['submenu'] ) ) :
                ?>
                <div class="master-menu">
                    <a href="<?php echo $menu['link']; ?>" class="<?php echo $menu['class']; ?>">
                        <?php
                        if ( !empty( $menu['icon'] ) ) :
                            ?>
                            <i class="<?php echo $menu['icon']; ?>"></i>
                            <?php
                        endif;
                        ?>
                        <?php echo $menu['label']; ?>
                    </a>
                    <ul class="menu">
                    <?php
                    foreach ( $menu['submenu'] as $submenu ) :
                        ?>
                        <li>
                            <a href="<?php echo $submenu['link']; ?>" class="<?php echo $submenu['class']; ?>">
                                <?php
                                if ( !empty( $submenu['icon'] ) ) :
                                    ?>
                                    <i class="<?php echo $submenu['icon']; ?>"></i>
                                    <?php
                                endif;
                                ?>
                                <?php echo $submenu['label']; ?>
                            </a>
                        </li>
                        <?php
                    endforeach;
                    ?>
                    </ul>
                </div>
                <?php
            else:
                ?>
                <a href="<?php echo $menu['link']; ?>" class="<?php echo $menu['class']; ?>">
                    <?php
                    if ( !empty( $menu['icon'] ) ) :
                        ?>
                        <i class="<?php echo $menu['icon']; ?>"></i>
                        <?php
                    endif;
                    ?>
                    <?php echo $menu['label']; ?>
                </a>
                <?php
            endif;

        endforeach;

    else:
        ?>
        <a href="#" class="item">Tidak ada menu</a>
        <?php
    endif;
};

wp_nav_menu(array(
    'theme_location' => 'sejoli-member-nav',
    'container'      => false,
    'walker'         => new \SejoliSA\Front\MenuWalker(),
    'fallback_cb'    => $fallback_cb,
    'items_wrap'     => '%3$s'
));
