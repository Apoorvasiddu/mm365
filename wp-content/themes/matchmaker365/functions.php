<?php
/*
functions.php
*/

 /*---------------------------------------*/
 //Theme Supports
 add_theme_support( "custom-header");
 add_theme_support( "custom-background");
 add_theme_support( "title-tag" );

/*---------------------------------------*/
//Theme Setup

function mm365_setup()
  {

    //Feed links
	add_theme_support( 'automatic-feed-links' );
	//Nav menu
    register_nav_menus( array(
      'mm365_standard' => esc_html__( 'mm365 Menu', 'mm365' ),
      'mm365_user' => esc_html__( 'Loggedin User Dashboard', 'mm365' ),
      'mm365_buyer' => esc_html__( 'Buyer Dashboard', 'mm365' ),
      'mm365_supplier' => esc_html__( 'Supplier Dashboard', 'mm365' ),
      'mm365_admin' => esc_html__( 'Admin Dashboard', 'mm365' ),
      'mm365_council' => esc_html__( 'Council Dashboard', 'mm365' ),
      'mm365_nonlogged' => esc_html__( 'Non Logged in', 'mm365' ),
      'mm365_superbuyer' => esc_html__( 'Super Buyer', 'mm365' ),
    ));
    //Content width
	if (!isset( $content_width ) ) $content_width = 1170;
    // Standard Size Thumbnails
	load_theme_textdomain('mm365', trailingslashit(get_template_directory()) . 'languages');
    // Standard Size Thumbnails
    add_theme_support( 'post-thumbnails', array('post') );
    //Custom LOGO
    add_theme_support( 'custom-logo' );
    add_theme_support('post-formats',array('aside','image','video'));


  }
  add_action( 'after_setup_theme', 'mm365_setup' );



/*----------------------------------------------------------*/
//Comment reply enqueue
    if(is_singular()): wp_enqueue_script( "comment-reply" ); endif;



 /*---------------------------------------*/
 //Editor Styles

 function mm365_add_editor_styles() {
     add_editor_style( get_template_directory_uri(). "/css/custom-editor-style.css" );
 }
 add_action( 'admin_init', 'mm365_add_editor_styles' );



/*---------------------------------*/
//Excerpt Length

function mm365_excerpt($limit) {
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt)>=$limit) {
    array_pop($excerpt);
    $excerpt = implode(" ",$excerpt).'...';
    } else {
    $excerpt = implode(" ",$excerpt);
    }
    $excerpt = preg_replace('`[[^]]*]`','',$excerpt);
    return $excerpt;
}


function mm365_excerpt_length($length) {
	return 115;
}
add_filter( 'excerpt_length', 'mm365_excerpt_length', 999 );
function mm365_excerpt_more( $more ) {
	return '';
}
add_filter('excerpt_more', 'mm365_excerpt_more');


/*---------------------------------------*/
//Script and Style Enqueue

   //Styles
   add_action('wp_enqueue_scripts','mm365_core_styles');

   //Scripts
   add_action('wp_enqueue_scripts','mm365_core_scripts');

/*---------------------------------------*/
//CSS : Initialize

function mm365_core_styles()
{

    //Thirdparty
    wp_enqueue_style("fancybox3", get_template_directory_uri(). "/assets/styles/jquery.fancybox.min.css");

    wp_enqueue_style("jquery-tables","https://cdn.datatables.net/v/dt/dt-1.13.4/r-2.4.1/datatables.min.css");
    
    wp_enqueue_style("dropzone", get_template_directory_uri(). "/assets/styles/dropzone.min.css");
    wp_enqueue_style("notiflix", get_template_directory_uri(). "/assets/styles/notiflix-3.2.6.min.css");
    wp_enqueue_style("jconfirm","https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.css");
    wp_enqueue_style("fontawesome","https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css");
    wp_enqueue_style("lineicons","https://cdn.lineicons.com/3.0/lineicons.css");
    
    wp_enqueue_style("flatpickr","https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css");
    wp_enqueue_style("select2",get_template_directory_uri(). "/assets/styles/select2.min.css");
    wp_enqueue_style("tagify","https://unpkg.com/@yaireo/tagify/dist/tagify.css");
    wp_enqueue_style("introjs","https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.0.1/introjs.min.css");
        
    wp_enqueue_style("slick",get_template_directory_uri(). "/assets/slick/slick.css");
    wp_enqueue_style("slick-theme",get_template_directory_uri(). "/assets/slick/slick-theme.css");
    wp_enqueue_style("atcb","https://cdn.jsdelivr.net/npm/add-to-calendar-button@1/assets/css/atcb.min.css");
    wp_enqueue_style('jquery-ui','http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css');

    //Theme only
    wp_enqueue_style("mm365_theme_style", get_template_directory_uri(). "/assets/styles/matchmaker365.css");

    //Default
    wp_enqueue_style("mm365_style", get_template_directory_uri(). "/style.css");

}

/*---------------------------------------------*/
//Scripts : Initialize

function mm365_core_scripts()
{
   
    //Thirdparty
    wp_enqueue_script("fancybox3", get_template_directory_uri()."/assets/javascripts/jquery.fancybox.min.js",array('jquery'),false,true);
   
    wp_enqueue_script("jquery-tables", "https://cdn.datatables.net/v/dt/dt-1.13.4/r-2.4.1/datatables.min.js",array('jquery'),false,true);
    //wp_enqueue_script("jquery-tables-responsive", "https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js",array('jquery'),false,true);

    wp_enqueue_script("dropzone", get_template_directory_uri()."/assets/javascripts/dropzone.js",array(),false,true);

    wp_enqueue_script("flatpickr2", "https://cdn.jsdelivr.net/npm/flatpickr",array(),false,true);
    wp_enqueue_script("flatpickr_range", "https://unpkg.com/flatpickr@4.6.13/dist/plugins/rangePlugin.js",array(),false,true);
    wp_enqueue_script("select2", get_template_directory_uri()."/assets/javascripts/select2.full.min.js",array(),false,true);
    wp_enqueue_script("notiflix", get_template_directory_uri()."/assets/javascripts/notiflix-3.2.6.min.js",array(),false,true);
    wp_enqueue_script("jConfirm", "https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js",array(),false,true);
    wp_enqueue_script("tagify", "https://cdn.jsdelivr.net/npm/@yaireo/tagify@4.25.0/dist/tagify.min.js",array(),false,true);
    wp_enqueue_script("tagify-polyfills", "https://unpkg.com/@yaireo/tagify/dist/tagify.polyfills.min.js",array(),false,true);
    wp_enqueue_script("introjs", "https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.0.1/intro.js",array(),false,true);  
    wp_enqueue_script("atcb", "https://cdn.jsdelivr.net/npm/add-to-calendar-button@1",array(),false,true);    
    wp_enqueue_script("slick", get_template_directory_uri()."/assets/slick/slick.min.js",array('jquery'),false,true);
    wp_enqueue_script("mm365_footer", get_template_directory_uri()."/assets/javascripts/mm365_footer.js",array('jquery'),false,true);
     
    wp_enqueue_script("jquery-ui", "http://code.jquery.com/ui/1.10.2/jquery-ui.js",array('jquery'),false,true);  
   
    //Theme scripts
     wp_enqueue_script("mm365_core", get_template_directory_uri()."/assets/javascripts/core.js",array("jquery"),false,true);

   
}

/*---------------------------------------------*/
//Google Fonts

function mm365_add_google_fonts() {
    wp_enqueue_style( 'mm365-google-fonts', 'https://fonts.googleapis.com/css?family=Raleway:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap', false ); 
}
add_action( 'wp_enqueue_scripts', 'mm365_add_google_fonts' );



/*---------------------------------------------*/
//Remove Admin Bar for normal users

add_action('after_setup_theme', 'remove_admin_bar');
 
function remove_admin_bar() {

    if (!current_user_can('administrator') && !is_admin()) {
           show_admin_bar(false);
    }
}



/*---------------------------------------------*/
//Show author info on backend

function add_author_support_to_posts() {
  add_post_type_support( 'mm365_companies', 'author' ); 
}
add_action( 'init', 'add_author_support_to_posts' );


/*---------------------------------------------*/
//Mobile menu
function mm365_usernav_no_ul($location='mm365_user')
{
    $options = array(
        'echo' => false,
        'container' => false,
        'theme_location' => $location,
        'fallback_cb'=> 'fall_back_menu'
    );

    $menu = wp_nav_menu($options);
    echo preg_replace(array(
        '#^<ul[^>]*>#',
        '#</ul>$#'
    ), '', $menu);

}

function fall_back_menu(){
    return;
}

//

//add_action( 'wp_print_scripts', 'my_deregister_javascript', 100 );
function my_deregister_javascript() 
 { 
    if ( is_page('login') ) 
      {
        wp_deregister_script(
            'save_matchmaking',
            'jquery-tables',
            'jquery-tables-responsive',
            'dashboard_filtercards',
            'admin_matchrequests',
            'scrollmagic',
            'mm365admin_reports',
            'matchlist_ajax',
            'admin_matchrequests',
            'mm365_matchclosure',
            'mm365_certificates'
         ); 
      } 

     //mm365_matchclosure
     if(!is_page_template('template-matchrequest-close.php' )){
         wp_deregister_script( 'mm365_matchclosure' );
     }

 } 



function mm365_pagewise_scripts_dequeue() {

    /**
     * Note: Do not use sub pages, which wont work with is_page
     */

    //Dequeue certification related script from other pages
    if(!is_page( array( 'certificate-upload', 'certificate-details','certificate-verification','admin-certificate-details')) ){
        wp_dequeue_script( 'mm365_certificates' );
    }

    //Dequeue meeting related script from other pages
    if(!is_page( array( 'meetings-scheduled','meeting-invites','meeting-details','schedule-meeting','edit-meeting','cancel-meeting','reschedule-meeting','edit-meeting-details','view-quick-reports-meetings')) ){
        wp_dequeue_script( 'mm365_meetings' );
    }

    if(!is_page( array( 'meeting-details')) ){
        wp_dequeue_script( 'atcb' );
        wp_dequeue_style( 'atcb' );
    }
    

    //Dequeue dropdownmanager from others
    if(!is_page( array( 'manage-dropdowns')) ){
        wp_dequeue_script( 'mm365_dropdownmanager' );
    }

    //Dequeue consolidated report script from other pages
    if(!is_page( array( 'report-consolidated', 'admin-view-report-consolidated')) ){
        wp_dequeue_script( 'mm365_consolidated_report' );
    }

    //Dequeue msdcmanagers script from other pages
    if(!is_page( array( 'list-council-managers','add_dcmanager','council-match-requests','edit-council-manager')) ){
        wp_dequeue_script( 'mm365_msdcmanagers' );
    }


    //Dequeue import script from other pages
    if(!is_page( array('import-companies')) ){
      wp_dequeue_script( 'matchmaker-365-importer-scripts' );
    }
    
    
    //Dequeue councils script from other pages
    if(!is_page( array('council-details', 'add-council','list-councils') ) ){
        wp_dequeue_script( 'mm365_council_script' );
    }

    
    //Dequeue import script from other pages
    if(!is_page( array('select-company')) ){
        wp_dequeue_script( 'mm365_multiplecompanies' );
    }

    if(!is_page( array('super-admin-guidelines','council-manager-guidelines','buyer-guidelines', 'supplier-guidelines')) ){
        wp_dequeue_script( 'mm365_manage_helppage' );
    }


    
    if(!is_page( array('search-companies')) ){
        wp_dequeue_script( 'mm365_search_company' );
    }

    

}
add_action( 'wp_print_scripts', 'mm365_pagewise_scripts_dequeue', 100 );



/*
*
* Conditionally exclude menu items
* Make sure the menu slug is same as in code
* Make sure the menu item title should be same as in code
*
*/

function mm365_exclude_menu_items( $items, $menu, $args ) {
    if( $menu->slug == 'council-managers' ){
       
        foreach ( $items as $key => $item ) {

         if ( $item->title  == 'Set Buyer Match Preference' AND !current_user_can( 'council_manager_approvers' ) ) {
                unset( $items[$key] );
          }
          
          if(current_user_can( 'council_manager_approvers' )){
            $pos = array_search("Match requests", array_column($items, 'title'));
            if($pos !== FALSE){
                  $items[$pos]->title = "Approve Match Requests";
            }
          }

        }
    }
    return $items;
}

add_filter( 'wp_get_nav_menu_items', 'mm365_exclude_menu_items', null, 3 );

/**
 * 
 * 2.0 Onwards
 * Replace error message, if the email is already existing
 * while registering
 * 
 */

add_filter('uwp_validate_fields_before', 'uwp_form_input_existing_email_cb', 10, 4);
function uwp_form_input_existing_email_cb($errors, $data, $type){ 
    if ($type == 'register' && email_exists($data['email'])) {
      $errors->add('email_exists', __('<strong>Error</strong>: This email is already registered; in case you want to add another company with the same email please login and you may add the additional company', 'userswp'));
    }
    return $errors;
    
}

/**
 * These following plugin should not be deactivated
 * 
 */
add_filter( 'plugin_action_links', 'mm365_disable_plugin_deactivation', 10, 4 );
function mm365_disable_plugin_deactivation( $actions, $plugin_file, $plugin_data, $context ) {
 
    if ( array_key_exists( 'deactivate', $actions ) && in_array( $plugin_file, array(
        'matchmaker-core/matchmaker.php',
        'userswp/userswp.php',
        'members/members.php',
        'lock-user-account/lock-user-account.php',
        'gd-mail-queue/gd-mail-queue.php',
        'cmb2/init.php'
    )))
        unset( $actions['deactivate'] );
    return $actions;
}

/**
 * 
 * SKIP mails from queue
 * 
 */
add_filter('gdmaq_mailer_add_to_queue', 'mm365__gdmaq_mailer_add_to_queue', 10, 3);
function mm365__gdmaq_mailer_add_to_queue($add, $email, $type) {
  $dont_add = array('wp_password_change_notification', 'wpmu_signup_user_confirmation', 'wp_email_change_confirmation');
  if (in_array($type, $dont_add)) {
    $add = false;
  }
  return $add;
}

//User switching - Enable for super buyers
add_filter( 'user_has_cap', function( $allcaps, $caps, $args, $user ) {
    if ( 'switch_to_user' === $args[0] ) {
      $allcaps['switch_users'] =   in_array('super_buyer', (array) $user->roles );
      $allcaps['switch_users'] .=   in_array('administrator', (array) $user->roles );
    }
    return $allcaps;
  }, 9, 4 );

  
//Hide default userswitching

add_filter( 'user_switching_in_footer', function(){ return false; } ,1);

//Clear cookies when user switches back to original role
function mm365_clearcookie_callback() {
    unset($_COOKIE['active_company_id']);
    unset($_COOKIE['active_company_name']);
    unset($_COOKIE['active_council_id']);
    setcookie('active_company_id', null, -1, '/');
    setcookie('active_company_name', null, -1, '/');
    setcookie('active_council_id', null, -1, '/');
}
add_action( 'switch_back_user', 'mm365_clearcookie_callback');