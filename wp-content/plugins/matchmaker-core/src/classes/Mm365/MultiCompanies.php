<?php
namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * 
 * All the multiple compay related methods
 * 
 */

/**
 * 
 * Experimental
 * Add user's company id when logging in
 * Remove active company id when the user logout
 */


class MultiCompanies {

    use CompaniesAddon;

    function __construct() {


        add_action( 'wp_login', array( $this, 'companyId_add_cookie' ), 10, 2 );

        add_action( 'init', array( $this, 'companyId_clear_cookie'), 10, 2 );

         //Assets
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

         //Switch company
        add_action( 'wp_ajax_switch_company', array( $this, 'switch_company' ) );
        add_action( 'wp_ajax_nopriv_switch_company', array( $this, 'switch_company' ) );


        add_filter( 'mm365_businessuser_companies_list',  array($this, 'users_companies'), 10, 2 );

        add_filter('mm365_companies_clearcookies',[$this,'companyId_clear_cookie'],10,0);
        
    }


    /**
     * 
     * Assets
     * 
     */

    function assets(){
        wp_register_script('jscookie',plugins_url('matchmaker-core/assets/js.cookie.min.js'), array( 'jquery' ), false, TRUE );
        wp_enqueue_script('jscookie');

        if ( wp_register_script( 'mm365_multiplecompanies',plugins_url('matchmaker-core/assets/mm365_multiplecompanies.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_multiplecompanies' );
            wp_localize_script( 'mm365_multiplecompanies', 'multiplecompaniesAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("multiplecompanies_ajax_nonce")
            ) );
        }
        
    }


    /**
     * Add users company info to cookie
     * when user login
     * 
     * If the user has only one company, that will be added to cookie at the time of login
     * 
     * In the User landing page template check if the cookie is present else redirect to selection
     * screen
     * 
     */

    function companyId_add_cookie( $user_login, \WP_User $user) {


        if(in_array( 'business_user', (array) $user->roles )){

            //List of companies belog to this user
            $all_companies = $this->users_companies($user->ID);

            if(count($all_companies) == 1){
                //Get user's company ID
                $comp_id = $this->get_user_company_id($user,'any');
                
                setcookie('active_company_id', $comp_id , time() + 86400, '/',""); // expire in a day
                setcookie('active_company_name',  get_the_title($comp_id) , time() + 86400, '/',""); // expire in a day
                setcookie('active_council_id', get_post_meta( $comp_id, 'mm365_company_council', true ) , time() + 86400, '/',""); // expire in a day 
            }

        }
     }

     /**
      * Delete company id cookie when user logout
      */
    function companyId_clear_cookie() {
 
        if(!is_user_logged_in()){
            unset($_COOKIE['active_company_id']);
            unset($_COOKIE['active_company_name']);
            unset($_COOKIE['active_council_id']);
            setcookie('active_company_id', "", -1, '/',"");
            setcookie('active_company_name', "", -1, '/',"");
            setcookie('active_council_id', "", -1, '/',"");
        }
    
    }

    /**
     * 
     * Find all the companies belong to selected user
     * 
     */
    function users_companies($user_id, $status = 'any'){

        $args = array(  
            'author' => $user_id,
            'post_type' => 'mm365_companies',
            'post_status' => $status,
            'posts_per_page' => -1, 
            'orderby' => 'title', 
            'order' => 'asc',
            'fields'  => 'ids',
        );
        $companies = new \WP_Query( $args );  
        $list_companies = array();
        while ( $companies->have_posts() ) : $companies->the_post(); 
           $list_companies[] = array("id" => get_the_ID() , "status" => get_post_status (get_the_ID()));    
        endwhile;
        wp_reset_postdata();

        return $list_companies;
    }

    /**
     * 
     * Switch Company
     * 
     */
    function switch_company(){

        //Get company id to switch to
        $nonce     = $_POST['nonce'];
        $comp_id   = $_POST['company_id'];

        //Verify Nonce
        if (!wp_verify_nonce( $nonce, 'multiplecompanies_ajax_nonce' ) OR !is_user_logged_in() ){
            echo '0';
            die();
        }else{

            $company_name = get_the_title($comp_id);
            $council_id = get_post_meta( $comp_id, 'mm365_company_council', true );
            setcookie('active_company_id', $comp_id , time() + 86400, '/',""); // expire in a day
            setcookie('active_company_name',  $company_name , time() + 86400, '/',""); // expire in a day
            setcookie('active_council_id',  $council_id , time() + 86400, '/',""); // expire in a day  
            echo '1';
            die();
        }
    }


}
