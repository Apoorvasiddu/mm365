<?php
/**
 * 
 * Helper functions
 * 
 * These class holds various helper methods
 * 
 * 
 */

 /* All methods related to meeting */

 namespace Mm365;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class Helpers
{
    //Traits
    use CountryStateCity;
    use CouncilAddons;
    use ReusableMethods;

    public $user;

    function __construct($user = FALSE) {

        $this->user = wp_get_current_user();

        add_action( 'wp_enqueue_scripts', array( $this, 'helper_assets' ), 11 );

        //AJAX
        add_action('wp_ajax_state_city_select',array($this, 'state_city_select')); 
        add_action('wp_ajax_validate_naics_code',array($this, 'validate_naics_code')); 
        add_action('wp_ajax_suggest_naics_code',array($this, 'suggest_naics_code')); 

        //Filters
        add_filter( 'mm365_helper_current_businessuser_mode',  array($this, 'current_businessuser_mode'), 10, 0 );
        add_filter( 'mm365_helper_get_usercouncil',  array($this, 'get_userDC'), 10, 1 );
        add_filter( 'mm365_helper_get_themeoption',array($this, 'mm365_get_option'),10,2);

        //Actions
        add_action('mm365_helper_check_loginandrole',  array($this, 'check_loginandrole'), 10, 1 );
        add_action('mm365_helper_update_user_council', array($this, 'update_user_council'), 10, 2);
        add_action('mm365_helper_check_companyregistration',  array($this, 'check_companyregistration'), 10, 2 );
        
        //From traits
        add_filter('mm365_helper_countries_list',  array($this, 'get_countries_list' ), 10, 0);
        add_filter('mm365_helper_states_list',  array($this, 'get_states_list' ), 10, 2);
        add_filter('mm365_helper_cities_list',  array($this, 'get_cities_list' ), 10, 2);
        add_filter('mm365_helper_get_cityname',array($this, 'get_cityname' ), 10, 1);
        add_filter('mm365_helper_get_statename',array($this, 'get_statename' ), 10, 1);
        add_filter('mm365_helper_get_countryname',array($this, 'get_countryname' ), 10, 1);
        add_filter('mm365_helper_multi_countries_state_display',array($this, 'multi_countries_state_display' ), 10, 2);
    }



    /**
     * Assets
     * 
     * 
     */
     function helper_assets(){

        //State City Select on change
        if ( wp_register_script('mm365_state_city_select',plugins_url('matchmaker-core/assets/state_city_select.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script('mm365_state_city_select' );
            wp_localize_script( 'mm365_state_city_select', 'mm365_helper_Ajax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("mm365_helper_ajax_nonce")
            ) );
        }  

        //State City Select on change
        if ( wp_register_script('mm365_helper',plugins_url('matchmaker-core/assets/helper.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script('mm365_helper' );
            wp_localize_script( 'mm365_helper', 'helperAjax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce("mm365_helper_nonce")
            ));
        }  

    }


    /*-----------------------------------------------------------
    Check user ROLE and restrict page acess based on this
    ------------------------------------------------------------- */
    public function check_loginandrole($role){

        if(!is_user_logged_in() AND !in_array( $role, (array) $this->user->roles )){
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            wp_redirect(wp_login_url($actual_link));
            die();
        }
        elseif(is_user_logged_in() AND !array_intersect( (array)$role, (array) $this->user->roles )){
            wp_redirect(site_url());
            die();
        }

    }


    /*-----------------------------------------------------------
    Redirect if the user hasn't registred any company
    ------------------------------------------------------------- */
    public function check_companyregistration($redirect_slug, $user_id = NULL){

        //If no active company in cookie
        if(!isset($_COOKIE['active_company_id']) OR $_COOKIE['active_company_id'] == NULL ){

            //Check if user has any registred company - any status
            $args = array(
                'post_type'   =>'mm365_companies',
                'post_status' =>'any',
                'author' => get_current_user_id(),
                'fields' => 'ids'
            );
            $wp_posts = get_posts($args);

            
            if(count($wp_posts)==0) {
                //If user has no companies
                wp_redirect(site_url()."/".$redirect_slug);
                exit;

            }else{
                //If the user has any company
                wp_redirect(site_url('select-company'));
                exit;
            }

        }else{

              $args = array(
                'post_type'   =>'mm365_companies',
                'post_status' =>'publish',
                'p' => $_COOKIE['active_company_id']
              );
              $wp_posts = get_posts($args);

            if(!count($wp_posts)) {
                wp_redirect(site_url()."/".$redirect_slug);
                exit;
            }

        }
      
    }

    
    /*-----------------------------------------------------------
    Get Company Council ID
    * This is looking at company post meta
    ------------------------------------------------------------- */
    public function company_council($company_id, $return = "id"){

        $council_id = get_post_meta( $company_id, 'mm365_company_council', true);

        switch ($return) {
            case 'name':
                $ret = get_the_title($council_id);
                break;
            case 'shortname':
                $ret = get_post_meta($council_id, 'mm365_council_shortname', true);
                break;
            default:
                $ret = $council_id;
                break;
        }

        return $ret;

    }


    /*-----------------------------------------------------------
    Post type confirmation 
    ------------------------------------------------------------- */
    public function is_right_post($post_id,$post_type_slug,$redirect_slug){

        //If the post is not benlonging to the expected post type exit
        if ( get_post_type( $post_id) != $post_type_slug ) {
           wp_redirect(site_url()."/".$redirect_slug);
           exit;
        }

    }

    /*-----------------------------------------------------------
    Council access right - 
    check wether council manager can acess this post
    ------------------------------------------------------------- */
    public function council_access_right($post_id, $user_council_id, $council_id_meta ){

       if($user_council_id == get_post_meta( $post_id, $council_id_meta, true)){
           return true;
       }else return false;

    }

    /*-----------------------------------------------------------
    Business user type
    * Check if buyer or seller
    ------------------------------------------------------------- */   
    public function current_businessuser_mode(){

        //Check role
        if(in_array( 'business_user', (array) $this->user->roles )){  
            if(isset($_COOKIE['active_company_id'])){
                $service_type = get_post_meta($_COOKIE['active_company_id'], 'mm365_service_type', true);
                return $service_type;
            }else return false;

        }else{
            return false;
        }

    }



/**------------------------------------------------------------------------
 * Make time stamp with timezone addition 
 * - similar function is availble in meetings class aswell
 --------------------------------------------------------------------------*/

   function make_timestamp_with_timezone($date_and_time, $timezone){
    $date = \DateTime::createFromFormat('m/d/Y  h:i A', $date_and_time,new \DateTimeZone($timezone));
    return $date->format('U');
   }



    /**
     * Wrapper function around cmb2_get_option
     * since 3.0.0
     * 
     * @since  0.1.0
     * @param  string $key     Options array key
     * @param  mixed  $default Optional default value
     * @return mixed           Option value
     * 
     */
    function mm365_get_option( $key = '', $default = false ) {
        if ( function_exists( 'cmb2_get_option' ) ) {
            // Use cmb2_get_option as it passes through some key filters.
            return cmb2_get_option( 'mm365_options', $key, $default );
        }

        // Fallback to get_option if CMB2 is not loaded yet.
        $opts = get_option( 'mm365_options', $default );

        $val = $default;

        if ( 'all' == $key ) {
            $val = $opts;
        } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
            $val = $opts[ $key ];
        }

        return $val;
    }




    /**
     * Since 3.0.0
     * @request 
     * AJAX function
     */
    function state_city_select(){

        //Get values
        $identifier   = sanitize_text_field($_POST['identifier']);
        $nonce      = sanitize_text_field($_POST['nonce']);
             
        if (!wp_verify_nonce( $nonce, 'mm365_helper_ajax_nonce' )) {
            die();
        }
       
        //code
        switch($identifier){
 
          case 'for_state':
            echo "<option value=''>-select-</option>";
            if($_POST['mode'] == 'with_all'){ echo "<option value='all' >All</option>"; }
            $states_list = $this->get_states_list($_POST['country']);
            if(!empty($states_list)){
                foreach ($states_list as $key => $value) {   
                   echo "<option value='".$value->id."' >".$value->name."</option>";
                }
             }else{
                if($_POST['mode'] != 'with_all'){ echo "<option value='all' >NA</option>";}
             }
          break;
 
          case 'for_cities':
          echo "<option value=''>-select-</option>";
          if($_POST['mode'] == 'with_all'){ echo "<option value='all' >All</option>"; }
           $cities_list = $this->get_cities_list($_POST['state']);
            if(!$cities_list){
             if($_POST['mode'] != 'with_all'){ echo "<option value='all'>NA</option>"; }
            }else{
             foreach ($cities_list as $key => $value) {   
                 echo "<option value='".$value->id."' >".$value->name."</option>";
             }
          }
          break;
 
          case 'for_multi_state':
             echo "<option value=''>-select-</option>";
             if($_POST['mode'] == 'with_all'){ echo "<option value='all' >All</option>"; }
             $states_list          = $this->get_states_list($_POST['country']);
             $states_list_selected = $_POST['selected_states'];
             
             if(!empty($states_list)){
                 foreach ($states_list as $key => $value) {   
                    echo "<option ";
                    if(!empty($_POST['selected_states']) AND in_array($value->id, $states_list_selected)) { echo " selected "; }
                    echo " value='".$value->id."' >".$value->name."</option>";
                 }
              }else{
                 if($_POST['mode'] != 'with_all'){ echo "<option value='all' >NA</option>";}
              }
           break;
 
          case 'for_multi_cities':
             //echo "<option value=''>-select-</option>";
             // if($_POST['mode'] == 'with_all'){ echo "<option value='all' >All</option>";}
             if(!in_array("all",$_POST['state'])){
                foreach ($_POST['state'] AS $stc) {                  
                   echo '<optgroup label="'.$this->get_statename($stc).'">';
                   $cities_list = $this->get_cities_list($stc);
                   //print_r($cities_list);
                   foreach ($cities_list as $key => $value) {     
                      //if(isset($_POST['city'])):
                         $selected_cities = $_POST['city'];                 
                         echo "<option ";
                         if(!empty($_POST['city']) AND in_array($value->id, $selected_cities)) { echo " selected "; }
                         echo "value='".$value->id."' >".$value->name."</option>";
                      //endif;   
                  }
                  echo '</optgroup>';
                }
             }else{
                echo "<option value='all'>All</option>";
             }
          break;
 
        }
   
        die();
    }


    /**
     * 
     * For matchimaking
     * 
     */
    function mergeCompanyArrays($array1, $array2) {
        $result = $array1;
        
        foreach ($array2 as $item) {
            $post_id = $item["post_id"];
            $found = false;
            
            // Check if the post_id already exists in the result array
            foreach ($result as $existingItem) {
                if ($existingItem["post_id"] == $post_id) {
                    $found = true;
                    break;
                }
            }
            
            // If post_id not found, add it to the result array
            if (!$found) {
                $result[] = $item;
            }
        }
        
        return $result;
    }


    /**
     * For matchmaking.php
     * 
     * 
     */
    function mapScore($score, $minInput, $maxInput, $minOutput, $maxOutput) {
        return (($score - $minInput) / ($maxInput - $minInput)) * ($maxOutput - $minOutput) + $minOutput;
    }

    /**
     * Validate NAICS Code
     * 
     * 
     */
    function validate_naics_code(){

       //Get values
       $naics_to_validate   = sanitize_text_field($_POST['naics_to_validate']);
       $nonce      = sanitize_text_field($_POST['nonce']);
            
       if (!wp_verify_nonce( $nonce, 'mm365_helper_nonce' )) {
           die();
       }


        global $wpdb;

        //Code validation
        if(is_numeric($naics_to_validate)){
        $find_codes = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."2017_naics_codes WHERE code = $naics_to_validate" );
        $result = false;
        foreach ($find_codes as $code){
            $result = $code->title;
        }

        //Search suggestion

        echo $result ?: 'Invalid Code';
    }else echo 'Invalid Code';

       wp_die();
    }

    /**
     * Suggest NAICS Code
     * 
     * 
     */
    function suggest_naics_code(){

        //Get values
        $input   = sanitize_text_field($_REQUEST['naics_to_validate']);
        // $nonce      = sanitize_text_field($_REQUEST['nonce']);
             
        // if (!wp_verify_nonce( $nonce, 'mm365_helper_nonce' )) {
        //     die();
        // }
 
         global $wpdb;

         if(strlen($input) > 1){

            $my_query = 'SELECT * FROM '.$wpdb->prefix.'2017_naics_codes WHERE `code` = "'.$input.'" OR `title` LIKE "%'.$input.'%" AND code  regexp \'[0-9]{6}\'';
            //Code validation
            $find_codes = $wpdb->get_results($my_query);
        
            //Search suggestion
            if(count($find_codes) > 0){
                echo "<ul class='naics_codes_found'>";
                foreach($find_codes as $naic){
                    if(strlen($naic->code) > 5)
                    echo "<li data-naic='$naic->code'>$naic->code - $naic->title <span>SELECT</span></li>";
                }
                echo "</ul>";
            }else{
                echo "Invalid code or no records found!";
            }
         }
     
 
        wp_die();
     }
 
}

//new Helpers;
