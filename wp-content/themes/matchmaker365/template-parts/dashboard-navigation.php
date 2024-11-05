<?php
$user = wp_get_current_user();


if(is_user_logged_in() AND in_array( 'business_user', (array) $user->roles )){

    $mode = apply_filters('mm365_helper_current_businessuser_mode',1);
    switch ($mode) {
        case 'buyer':
            $menu_location = 'mm365_buyer';
            break;
        case 'seller':
            $menu_location = 'mm365_supplier';
            break;
        default:
            $menu_location = 'mm365_user';
            break;
    }

                  $menu_attrs = array(
                        'theme_location'  => $menu_location,
                        'container'       => 'ul',
                        'container_id'    => 'nav',
                        'menu_class'      => '',
                        'menu_id'         => 'nav',
                        'echo'            => true,
                        'fallback_cb'     => '',
                        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'depth'           => 2,
                    );


                    wp_nav_menu($menu_attrs);
                    
                    
 }elseif(in_array( 'mmsdc_manager', (array) $user->roles )){
    $menu_attrs = array(
        'theme_location'  => 'mm365_admin',
        'container'       => 'ul',
        'container_id'    => 'nav',
        'menu_class'      => '',
        'menu_id'         => 'nav',
        'echo'            => true,
        'fallback_cb'     => '',
        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'depth'           => 10,
    );
    wp_nav_menu($menu_attrs);
                    
}elseif(in_array( 'council_manager', (array) $user->roles )){
    $menu_attrs = array(
        'theme_location'  => 'mm365_council',
        'container'       => 'ul',
        'container_id'    => 'nav',
        'menu_class'      => '',
        'menu_id'         => 'nav',
        'echo'            => true,
        'fallback_cb'     => '',
        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'depth'           => 10,
        //'walker'          => new Description_Walker
    );
    wp_nav_menu($menu_attrs);
                    
}
elseif(in_array( 'super_buyer', (array) $user->roles )){
    $menu_attrs = array(
        'theme_location'  => 'mm365_superbuyer',
        'container'       => 'ul',
        'container_id'    => 'nav',
        'menu_class'      => '',
        'menu_id'         => 'nav',
        'echo'            => true,
        'fallback_cb'     => '',
        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'depth'           => 10,
        //'walker'          => new Description_Walker
    );
    wp_nav_menu($menu_attrs);
                    
}else{
    $menu_attrs = array(
        'theme_location'  => 'mm365_nonlogged',
        'container'       => 'ul',
        'container_id'    => 'nav',
        'menu_class'      => '',
        'menu_id'         => 'nav',
        'echo'            => true,
        'fallback_cb'     => '',
        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        'depth'           => 10,
    );
    wp_nav_menu($menu_attrs);
}




