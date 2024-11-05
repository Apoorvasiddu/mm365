<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <!-- Preload -->
    <?php

    $user = wp_get_current_user();

    wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <!-- Main Menu -->
    <header class="desktop_header <?php if (is_front_page()):
        echo "home_transparent"; endif; ?>">
        <div class="container-fluid">
            <div class="row">

                <div class="<?php echo (is_user_logged_in()) ? ' col-4' : ' col-6' ?>">

                        <?php
                        if (isset($_COOKIE['active_council_id'])) {
                           $council_id =  $_COOKIE['active_council_id'];
                           $logo = get_post_meta($council_id, 'mm365_council_logo',TRUE);
                           if(!empty($logo)){
                                 foreach($logo as $key => $value){
                                     $path_to_file = $value;
                                 }
                           }else{ $path_to_file = ''; }
                           ?>
                            <img class="header-council-logo" src="<?php echo esc_url($path_to_file); ?>" height="55px" alt=""/>
                           <?php
                        }
                        ?>
                    <a href="<?php echo home_url(); ?>"><img class="desktop_header_logo"
                            src="<?php echo get_template_directory_uri() ?>/assets/images/mmsdc_logo.png"
                            alt="logo"></a>

                </div>
                <?php if (is_user_logged_in()): ?>
                    <div class="col-6 d-flex flex-row-reverse align-items-center">
                        <?php if (isset($_COOKIE['active_company_name'])): ?>
                            <a href="<?php echo site_url('select-company'); ?>">
                                <span class="selected-company">
                                    <img src="<?php echo get_template_directory_uri() ?>/assets/images/company_indic.png"
                                        alt="">
                                    <div class="company-title">
                                        <?php echo esc_html($_COOKIE['active_company_name']); ?>
                                    </div>
                                    <img height="18px" title="Change Company"
                                        src="<?php echo get_template_directory_uri() ?>/assets/images/change.svg" alt="">
                                </span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="<?php echo (is_user_logged_in()) ? ' col-2' : ' col-6' ?> d-flex flex-row-reverse">
                    <?php
                    if (!is_user_logged_in()):
                        $menu_attrs = array(
                            'theme_location' => 'mm365_standard',
                            'container' => 'ul',
                            'container_id' => 'nav',
                            'menu_class' => 'desktop_header_nav',
                            'menu_id' => 'nav',
                            'echo' => true,
                            'fallback_cb' => '',
                            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            'depth' => 10,
                        );
                        wp_nav_menu($menu_attrs);
                    endif;
                    ?>
                    <div class="logged-in-desktop">
                        <?php if (is_user_logged_in()): ?>
                            <ul class="desktop_header_usermenu">
                                <li class="has-drpdn"><a class="user-welcome" href="#">
                                        <?php $user = wp_get_current_user();
                                        if ($user->first_name != '')
                                            echo "Hi, " . $user->first_name . " " . $user->last_name; ?><span></span>
                                    </a>
                                    <div class="topbar-drpdn">
                                        <a href="<?php echo home_url('/change'); ?>">Change Password</a>
                                        <a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a>
                                    </div>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="logged-in-mobile">
                        <?php if (is_user_logged_in()): ?>
                            <div class="navigation__wrapper">
                                <input type="checkbox" id="hamburger">
                                <label for="hamburger" class="navigation__button">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </label>
                                <ul class="navigation__links">
                                    <?php

                                    if (is_user_logged_in() and in_array('business_user', (array) $user->roles)) {

                                        $mode = apply_filters('mm365_helper_current_businessuser_mode', 10);
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

                                        mm365_usernav_no_ul($menu_location);

                                    } elseif (in_array('mmsdc_manager', (array) $user->roles)) {
                                        mm365_usernav_no_ul('mm365_admin');
                                    } elseif (in_array('council_manager', (array) $user->roles)) {
                                        mm365_usernav_no_ul('mm365_council');
                                    }
                                    ?>
                                    <li><a href="<?php echo home_url('/change'); ?>">Change Password</a></li>
                                    <li><a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a></li>
                                </ul>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
    </header>

    <?php if (is_user_logged_in()): ?>
        <!-- Floating help -->
        <a href="#" class="floating-help" title="Click to activate help mode">
            <i class="far fa-question-circle my-floating-help"></i>
        </a>
    <?php endif; ?>