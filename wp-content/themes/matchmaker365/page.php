<?php

$user = wp_get_current_user();
if (is_user_logged_in() and in_array('business_user', (array) $user->roles)) {

    //Check if user has atleast one registered company

    //If user has more than one registered company redirect to switch

    //If Only one user write cookie and redirect

    //Get the list fo companies added by the user
    $companies_list = apply_filters('mm365_businessuser_companies_list', $user->ID);
    //More than two companies redirect to select-company page
    if (count($companies_list) > 1 and !isset($_COOKIE['active_company_id'])) {
        $redirect = site_url() . '/select-company';
        wp_redirect($redirect);
        exit;
    } else {

        wp_redirect('user-dashboard');
    }


} elseif (is_user_logged_in() and in_array('mmsdc_manager', (array) $user->roles)) {
    wp_redirect('admin-dashboard');
} elseif (is_user_logged_in() and in_array('council_manager', (array) $user->roles)) {
    wp_redirect('council-dashboard');
} elseif (is_user_logged_in() and in_array('super_buyer', (array) $user->roles)) {
    wp_redirect('superbuyer-dashboard');
}



get_header();
while (have_posts()):
    the_post();
    the_content();
endwhile;

get_footer();