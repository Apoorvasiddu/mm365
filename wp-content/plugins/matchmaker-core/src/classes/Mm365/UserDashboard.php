<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// User dashboard

class UserDashboard
{

    function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Adding content
        add_action( 'wp_ajax_add_dashboard_content', array( $this, 'add_dashboard_content' ) );
        add_action( 'wp_ajax_nopriv_add_dashboard_content', array( $this, 'add_dashboard_content' ) );
        add_action( 'wp_ajax_toggle_post_visibility', array( $this, 'toggle_post_visibility' ) );
        add_action( 'wp_ajax_nopriv_toggle_post_visibility', array( $this, 'toggle_post_visibility' ) );
        add_action( 'wp_ajax_delete_post', array( $this, 'delete_post' ) );
        add_action( 'wp_ajax_nopriv_delete_post', array( $this, 'delete_post' ) );

        add_filter( 'mm365_supplierdash_match_apperance_count',  array($this, 'supplierdash_match_apperance_count'), 10, 1 );
        add_filter( 'mm365_supplierdash_subscription_status_card',  array($this, 'subscription_status_card'), 10, 1 );
        add_filter( 'mm365_supplierdash_welcome_cards',  array($this, 'supplier_welcome_cards'), 10, 1 );
        add_filter( 'mm365_buyerdash_welcome_cards',  array($this, 'buyer_welcome_cards'), 10, 1 );

    }

    /**-----------------------------------
     * Assets
     -------------------------------------*/
     function assets(){
        if ( wp_register_script( 'mm365_dashboardcontent_script',plugins_url('matchmaker-core/assets/mm365_sa_dashboardcontent.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_dashboardcontent_script' );
            wp_localize_script( 'mm365_dashboardcontent_script', 'sadashboardAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("sadashboard_ajax_nonce")
            ) );
        }    
    }

    /**-----------------------------------
     * Match appearance count
     -------------------------------------*/

     public function supplierdash_match_apperance_count($cmp_id){

        //Count the number of appearances in match results 

        //Experimental
         global $wpdb;
         $sql  =  "SELECT ".$wpdb->prefix."postmeta.meta_value AS matched_data FROM ".$wpdb->prefix."postmeta INNER JOIN ".$wpdb->prefix."posts ON ".$wpdb->prefix."postmeta.post_id = ".$wpdb->prefix."posts.ID WHERE ".$wpdb->prefix."postmeta.meta_key = 'mm365_matched_companies' AND ".$wpdb->prefix."postmeta.`meta_value` != '' AND ".$wpdb->prefix."posts.post_status = 'publish'";
         //Company description
         $matchresults_jsons  = $wpdb->get_results($sql);
         $match_results = array();
         foreach ($matchresults_jsons as  $value) {
             $matched =  maybe_unserialize(maybe_unserialize($value->matched_data));
           
             if(is_array($matched)):
           
                 foreach ($matched as $key => $subArr) { 
                     unset($matched[$key][1]);      
                 }
                 //Push to master array with flattening of array to single dimension
                 array_push($match_results, call_user_func_array('array_merge', $matched));
           
             endif;
           }
           
         $master_MatchedCompanies = call_user_func_array('array_merge', $match_results);
         $mbes = array_count_values($master_MatchedCompanies);
         arsort($mbes);

         if(!empty($mbes[$cmp_id])){
            return $mbes[$cmp_id];
         }else return 0;

         


        //return the text

     }

    /**-----------------------------------
     * Match requests created 
     *  count - Buyer/Supplier
     -------------------------------------*/
     public function count_match_requests_submitted($cmp_id, $status = NULL){

        //Include status condition meta - 
        if($status != NULL){

            $incl_status_condition  = array(
                'key'     => 'mm365_matchrequest_status',
                'value'   => $status,
                'compare' => '=',
            );

            $time_column = 'post_modified';

        } else {
            $incl_status_condition = NULL; 
            $time_column = 'post_created';
        } 


        $args = array(  
            'post_type' => 'mm365_matchrequests',
            'posts_per_page' => -1, 
            'orderby' => 'date', 
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key'     => 'mm365_requester_company_id',
                    'value'   => $cmp_id,
                    'compare' => '=',
                ),
                $incl_status_condition
            ),
            'date_query' => array(
                'column' => $time_column,
                'after' => date('Y-m-01')
            )

        );
        $loop = new \WP_Query($args);  
        return $loop->found_posts;
     }
     
    /**-----------------------------------
     * Meeting proposed count 
     *  count - Buyer/Supplier
     -------------------------------------*/
     public function count_meetings_proposed($cmp_id){

        $args = array(  
            'post_type' => 'mm365_meetings',
            'posts_per_page' => -1, 
            'orderby' => 'date', 
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key'     => 'mm365_proposed_company_id',
                    'value'   => $cmp_id,
                    'compare' => '=',
                )
            ),
            'date_query' => array(
                'after' => date('Y-m-01')
            )

        );
        $loop = new \WP_Query($args);  
        return $loop->found_posts;
     }
     
    /**-----------------------------------
     * Meetings invited to - count 
     * Supplier
     -------------------------------------*/
     public function count_meetings_invited_to($cmp_id){

        $args = array(  
            'post_type' => 'mm365_meetings',
            'posts_per_page' => -1, 
            'orderby' => 'date', 
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key'     => 'mm365_meeting_with_company',
                    'value'   => $cmp_id,
                    'compare' => 'IN',
                )
            ),
            'date_query' => array(
                'after' => date('Y-m-01')
            )

        );
        $loop = new \WP_Query($args);  
        return $loop->found_posts;
     }





    /**-----------------------------------
     * Supplier Cards
     -------------------------------------*/

     public function supplier_welcome_cards($cmp_id){

        $cards = '';
        // Created match requests
        $cards .= '<div class="stat-highlight-box">
                    <div class="count">'.$this->count_match_requests_submitted($cmp_id).'</div>
                    <div class="info">Match requests created</div>
             </div>';
        
        // Meeting Invites
        $cards .= '<div class="stat-highlight-box">
                    <div class="count">'.$this->count_meetings_invited_to($cmp_id).'</div>
                    <div class="info">Meeting Invites Received</div>
             </div>';
        

        // Meeting Schduled
        $cards .= '<div class="stat-highlight-box">
                    <div class="count">'.$this->count_meetings_proposed($cmp_id).'</div>
                    <div class="info">Meetings Proposed</div>
             </div>';
        

        // Appearace In Matches - approved
        $cards .= '<div class="stat-highlight-box">
                <div class="count">'.$this->supplierdash_match_apperance_count($cmp_id).'</div>
                <div class="info">Appearance in match results</div>
             </div>';

        return $cards;     

     }



    /**-----------------------------------
     * Buyer Cards
     -------------------------------------*/

     public function buyer_welcome_cards($cmp_id){

        $buyer_cards = '';
        // Created match requests
        $buyer_cards .= '<div class="stat-highlight-box">
                    <div class="count">'.$this->count_match_requests_submitted($cmp_id).'</div>
                    <div class="info">Match requests created</div>
             </div>';
        
        $buyer_cards .= '<div class="stat-highlight-box">
             <div class="count">'.$this->count_match_requests_submitted($cmp_id, 'completed').'</div>
             <div class="info">Match requests completed</div>
        </div>';
        
        $buyer_cards .= '<div class="stat-highlight-box">
             <div class="count">'.$this->count_match_requests_submitted($cmp_id, 'cancelled').'</div>
             <div class="info">Match requests cancelled</div>
        </div>';        

        // Meeting Schduled
        $buyer_cards .= '<div class="stat-highlight-box">
                    <div class="count">'.$this->count_meetings_proposed($cmp_id).'</div>
                    <div class="info">Meetings Proposed</div>
             </div>';

        return $buyer_cards;    

     }

    /**-----------------------------------
     * Subscription widget
     -------------------------------------*/
     public function subscription_status_card($cmp_id){

        if ( metadata_exists( 'post', $cmp_id, 'mm365_subscription_status' ) ) {
            $status = get_post_meta( $cmp_id, 'mm365_subscription_status', true );
            $end_date = get_post_meta( $cmp_id, 'mm365_subscription_enddate',true );
            $level = get_post_meta( $cmp_id, 'mm365_subscription_type',true );

            return array("status" => $status, "end_date" => $end_date, "level" => $level );
        }else return NULL;
        
        
     }

    /**-----------------------------------
     * Add Dashboard Content
     -------------------------------------*/
     public function add_dashboard_content(){

        $nonce      = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce( $nonce, 'sadashboard_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        //Collect data
        $title           = sanitize_text_field($_POST['title']);
        $content         = sanitize_text_field($_POST['user_tip']);
        $type            = sanitize_text_field($_POST['type']);
        $visible_to      = sanitize_text_field($_POST['content_visible_to']);

        //Create post - 
        $data = array(
                        'post_type'  => 'mm365_updatesandtips',
                        'post_title' => $title,
                        'post_content'  => $content,
                        'post_status'=> 'publish',
                        'meta_input' => array(
                            '_mm365_content_type' => $type,
                            '_mm365_visible_to' => $visible_to,
                            '_mm365_visibility' => 1
                        )
        );
                    
        $post_id = wp_insert_post( $data );

        if($post_id){
            echo 'success';
        }else echo 'failed';
        
        wp_die();

     }

    /**-----------------------------------
     * Post visibility
     -------------------------------------*/
     function toggle_post_visibility(){

        $nonce      = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce( $nonce, 'sadashboard_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        $post_id = $_POST['post_id'];

        $current = get_post_meta( $post_id, '_mm365_visibility', true);

        $current = 1 - $current;

        if(update_post_meta( $post_id,'_mm365_visibility', $current)){
            echo 'success';
        }else echo 'fail';

        wp_die();

     }

    /**-----------------------------------
     * Post deletion
     -------------------------------------*/
     function delete_post(){

        $nonce      = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce( $nonce, 'sadashboard_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        $post_id = $_POST['post_id'];

        if(wp_delete_post($post_id , TRUE)){
            echo 'success';
        }else echo 'fail';

        wp_die();


     }


}
