<?php
/**
 * Template Name: Company Edit
 *
 */


$user = wp_get_current_user();
if (is_user_logged_in() and in_array('business_user', (array) $user->roles)) {
    $company_id = $_REQUEST['cid'];
    if (get_current_user_id() == get_post_field('post_author', $company_id)) {
        
            
            //Clear cookie
            apply_filters('mm365_company_delete', $company_id);
            
            apply_filters('mm365_companies_clearcookies',1);

            unset($_COOKIE['active_company_id']);
            unset($_COOKIE['active_company_name']);
            unset($_COOKIE['active_council_id']);
            setcookie('active_company_id', null, -1, '/');
            setcookie('active_company_name', null, -1, '/');
            setcookie('active_council_id', null, -1, '/');

            //If user has more companies redirect to 
            if(count_user_posts($user->ID,'mm365_companies') > 0){
                wp_redirect('select-company');
            }else{
                wp_redirect('register-your-company');
            }

            //Else redirect to register
            
        
    } else {

        get_header();
        ?>

        <img class="card-img-top" height="120px" src="<?php echo get_template_directory_uri() ?>/assets/images/failure.svg"
            alt="Card image cap">
        <div class="card-body">
            <h1 class="card-title">
                <?php echo esc_html('Oops! Something went wrong!'); ?>
            </h1>
            <?php
    }
    //delete function execute here
}
