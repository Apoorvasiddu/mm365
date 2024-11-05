<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ManageBuyers
{

    function __construct(){

        //Load assets
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Search and find buyer
        add_action( 'wp_ajax_find_buyer', array( $this, 'find_buyer' ) );

        //Toggle user login status
        add_action( 'wp_ajax_toggle_status', array( $this, 'toggle_status' ) );

    }

    /*---------------------------------------------
    * Assets
    ----------------------------------------------*/
    function assets(){

        
        if ( wp_register_script( 'mm365_manage_buyer',plugins_url('matchmaker-core/assets/mm365_manage_buyer.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_manage_buyer' );
            wp_localize_script( 'mm365_manage_buyer', 'manageBuyerAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("manageBuyer_ajax_nonce")
            ) );
          
        }    

    }

    /*---------------------------------------------
    * Search and load buyers
    ----------------------------------------------*/
    public function find_buyer(){

        $nonce     = $_POST['nonce'];
        $keyword   = $_POST['search_buyer'];

        if (!wp_verify_nonce( $nonce, 'manageBuyer_ajax_nonce' ) OR !is_user_logged_in()) {
            echo '0';
            die();
        }

        //Search keyword against buyer companies
        if($keyword != ''){

            $args = array(
                'post_type'      => 'mm365_companies',
                'post_status'    => 'publish',
                'order'          => 'DESC',
                's'              => $keyword,
                'meta_query' => array(
                  array(
                      'key'     => 'mm365_service_type',
                      'value'   => 'buyer',
                      'compare' => '=',
                  ),
                )
              );

            $buyers  = new \WP_Query($args);        
            $totalData = $buyers->found_posts;

            if ( $buyers->have_posts() ) {

                echo '<table id="superadmin_searchresult_buyers" class="mm365datatable-list table table-striped"  cellspacing="0" width="100%" data-intro="List of buyer companies found">
                <thead class="thead-dark">
                  <tr>
                    <th data-intro="Buyer company name"><h6>Company</h6></th>
                    <th class="no-sort" data-intro="Email used for login"><h6>User Email</h6></th>
                    <th class="no-sort" data-intro="Username"><h6>User Login</h6></th>
                    <th data-intro="User\'s login status. Only the ACTIVE users can login to the platform. BLOCKED users cannot login to the system"><h6>Status</h6></th>
                    <th class="no-sort" data-intro="Change login status"><h6></h6></th>
                  </tr>
                </thead>
                <tbody>';


                while ( $buyers->have_posts() ) {
                    echo "<tr>";
                    $buyers->the_post();
                    $author_id = get_the_author_meta( 'ID' );

                    $user_lock_status = get_user_meta($author_id, 'baba_user_locked', true );

                    echo "<td>".get_the_title()."</td>";
                    echo "<td>".get_the_author_meta( 'user_email' , $author_id )."</td>";
                    echo "<td>".get_the_author_meta( 'user_login' , $author_id )."</td>";
                    echo "<td>";
                     echo ' <span class="'.$author_id.'-lock-status user_lock '.esc_html($user_lock_status).'">';
                     echo ($user_lock_status == '') ?  "ACTIVE" : "BLOCKED";
                     echo '</span>';
                    echo "</td>";
                    echo "<td>";
                       echo "<a href='#' class='".$author_id."-lock-button user-lock-toggle' data-userid='".$author_id."'>";
                       echo ($user_lock_status == '') ? "BLOCK" : "UNBLOCK";
                       echo "</a>";
                    echo "</td>";
                    echo "</tr>";
                }

                echo '</tbody>
                </table>';
                
            }else{
                echo '<h4 class="text-center">No buyer companies found!</h4>';
            }


        }

        wp_die();

    }


    /**----------------------------------------------
     * Toggle Status - Block or Unblock customers
     * Dependancy: Lock User Account by By teknigar
     * https://wordpress.org/plugins/lock-user-account/
     ------------------------------------------------*/
    public function toggle_status(){

        $nonce    = $_POST['nonce'];
        $userid   = $_POST['userid'];

        if (!wp_verify_nonce( $nonce, 'manageBuyer_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        //Check current status
        $user_lock_status = get_user_meta($userid, 'baba_user_locked', true );

        //Toggle the status
        if($user_lock_status == 'yes'){
          update_user_meta( $userid, 'baba_user_locked', '');
          echo "unblocked";
        }else{
          update_user_meta( $userid, 'baba_user_locked', 'yes'); 
          echo "blocked";
        }

        wp_die();

    }




//Class ends here
}