<?php
namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/* 
*
* All methods related to Subscription Module
* 
*/

class Subscription 
{
    use CompaniesAddon;
    use CouncilAddons;
    use NotificationAddon;
    use SubscriptionAddon;

    function __construct(){
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //
        add_action( 'wp_ajax_subscription_get_companies', array( $this, 'get_companies' ) );

        //
        add_action( 'wp_ajax_update_subscriptions', array( $this, 'update_subscriptions' ) );

        //Report viewing
        add_action( 'wp_ajax_subscription_report', array( $this, 'subscription_report' ) );

    }

    
    /**-----------------------------------
     * Assets
     -------------------------------------*/
    function assets(){

        if ( wp_register_script( 'mm365_subscription',plugins_url('matchmaker-core/assets/mm365_subscription.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_subscription' );
            wp_localize_script( 'mm365_subscription', 'subscriptionAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("subscription_ajax_nonce")
            ));
        }    

    }

    /**---------------------------------------------------------------
     * Get list of companies whom are not subscribed / expired / no data
     ------------------------------------------------------------------*/

    function get_companies(){

        if (!wp_verify_nonce( $_GET['nonce'], 'subscription_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        $return = array();
        $companies = new \WP_Query( array(
            's'           => $_GET['q'],
            'post_type'   => 'mm365_companies',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'meta_query' => array(
              array(
                  'key'     => 'mm365_service_type',
                  'value'   => $_GET['service_type'],
                  'compare' => '=',
              ),
              array(
                'key'     => 'mm365_company_council',
                'value'   => $_GET['council'],
                'compare' => '=',
              ),
              array(
                'relation' => 'OR',
                array(
                    'key'        => 'mm365_subscription_status',
                    'compare'    => 'NOT EXISTS',
                ),
                array(
                    'key'     => 'mm365_subscription_status',
                    'value'   => 'Active',
                    'compare' => '!=',
                )
             ),
            )
        ));

        if( $companies->have_posts() ) :
            while( $companies->have_posts() ) : $companies->the_post();
                $title = ( mb_strlen( $companies->post->post_title ) > 50 ) ? mb_substr( $companies->post->post_title, 0, 49 ) . '...' : $companies->post->post_title;
                $return[] = array( $companies->post->ID, $title );
            endwhile;
        endif;
        echo json_encode( $return );
        wp_reset_postdata();
        wp_die();

    }

    /**-----------------------------------
     * Update subscription
     * Select companies and add subscription to them
     -------------------------------------*/
     function update_subscriptions(){

        if (!wp_verify_nonce( $_REQUEST['nonce'], 'subscription_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        $council = $_REQUEST['council'];
        $companies_to_subscribe = $_REQUEST['suppliers_to_subscribe'];
        $from = $_REQUEST['from_date'];
        $to = $_REQUEST['to_date'];
        $added_by = $_REQUEST['current_user'];
        $subscription_type = $_REQUEST['subscription_type'];

        if(!empty($companies_to_subscribe)){
            foreach ($companies_to_subscribe as $cmp_id) {
                
                delete_post_meta( $cmp_id, 'mm365_subscription_startdate' );
                delete_post_meta( $cmp_id, 'mm365_subscription_enddate' );
                delete_post_meta( $cmp_id, 'mm365_subscription_status' );
                delete_post_meta( $cmp_id, 'mm365_subscription_enabledby');
                delete_post_meta( $cmp_id, 'mm365_subscription_type');

                add_post_meta($cmp_id, 'mm365_subscription_startdate', date('Y-m-d',strtotime($from)) );
                add_post_meta($cmp_id, 'mm365_subscription_enddate', date('Y-m-d',strtotime($to) ));
                add_post_meta($cmp_id, 'mm365_subscription_status',  'Active');
                add_post_meta($cmp_id, 'mm365_subscription_enabledby',  $added_by);
                add_post_meta($cmp_id, 'mm365_subscription_history', $from."|".$to."|".$added_by);
                add_post_meta($cmp_id, 'mm365_subscription_type',  $subscription_type);
            }
            echo 'success';
        }else{
            echo 'failed';
        }
        wp_reset_postdata();
        wp_die();

     }






    /**-----------------------------------
     * Generate subscription report
     -------------------------------------*/
     function subscription_report(){

              //Check NONCE
              if (!wp_verify_nonce( $_REQUEST['nonce'], 'subscription_ajax_nonce' ) OR !is_user_logged_in()) {
                die();
              }

              header("Content-Type: application/json");
              $request= $_POST;
              $columns = array(
                0 => 'company_name',
                1 => 'council',
                2 => 'type',
                3 => 'subscription_type',
                4 => 'start_date',
                5 => 'end_date',
                6 => 'status', 
              );
      

              
              $council_id = sanitize_text_field($_REQUEST['council_id']);
              $service_type = sanitize_text_field($_REQUEST['service_type']);
              $status = sanitize_text_field($_REQUEST['status']);
              $subscription_type = sanitize_text_field($_REQUEST['subscription_type']);
              $startdate = sanitize_text_field($_REQUEST['start_date']);
              $enddate = sanitize_text_field($_REQUEST['end_date']);

              if(!empty($startdate) AND !empty($enddate)){
                $date_between = array(
                  'key'     => 'mm365_subscription_enddate',
                  'value'   => array(date('Y-m-d',strtotime($startdate)),date('Y-m-d',strtotime($enddate))),
                  'compare' => 'BETWEEN',
                  'type'    => 'DATE'
                );
              }else $date_between = array();

              //
              if(!empty($status)){
                $status_filter = array(
                  'key'     => 'mm365_subscription_status',
                  'value'   => $status,
                  'compare' => '='
                );
              }else $status_filter = array();

              if(!empty($subscription_type)){
                $subscription_type_filter = array(
                  'key'     => 'mm365_subscription_type',
                  'value'   => $subscription_type,
                  'compare' => '='
                );
              }else $subscription_type_filter = array();
              

              //Check all companies with 'Active' subscription
              $companies = new \WP_Query( array(
                'post_type'   => 'mm365_companies',
                'post_status' => 'publish',
                'posts_per_page' => $_REQUEST['length'],
                'offset'         => $_REQUEST['start'],
                'meta_query' => array(
                  array(
                    'key'     => 'mm365_company_council',
                    'value'   => $council_id,
                    'compare' => '=',
                  ),
                  array(
                    'key'     => 'mm365_service_type',
                    'value'   => $service_type,
                    'compare' => '=',
                  ),
                  $status_filter,
                  $date_between,
                  $subscription_type_filter
                )
            ));

            $totalData = $companies->found_posts;


            if( $companies->have_posts() ) :
                while( $companies->have_posts() ) : $companies->the_post();

                  $council_id = get_post_meta(get_the_ID(), 'mm365_company_council',true);

                  $start_date = get_post_meta(get_the_ID(), 'mm365_subscription_startdate',true);
                  $end_date = get_post_meta(get_the_ID(), 'mm365_subscription_enddate',true);
                  $status = get_post_meta(get_the_ID(), 'mm365_subscription_status',true);

                  if($start_date != ""): $start_date = date('m/d/Y',strtotime($start_date)); else: $start_date = '-'; endif;
                  if($end_date != ""): $end_date = date('m/d/Y',strtotime($end_date)); else: $end_date = '-'; endif;
                  $subscription_type = get_post_meta(get_the_ID(), 'mm365_subscription_type',true);

                  $nestedData   = array();
                  $nestedData[] = "<a target='_blank' href='".site_url()."/view-company?cid=".get_the_ID()."'>".get_the_title()."</a>";
                  $nestedData[] = $this->get_council_info($council_id);
                  $nestedData[] = $this->get_company_service_type(get_post_meta(get_the_ID(), 'mm365_service_type',true));
                  $nestedData[] = ($subscription_type) ? $subscription_type : '-';
                  $nestedData[] = $start_date;
                  $nestedData[] = $end_date;
                  $nestedData[] = "<span class='subscription-status ".strtolower($status)."'>".$status."</span>";

                  $data[] = $nestedData;
                endwhile;

                $json_data = array(
                  "draw" => intval($request['draw']),
                  "recordsTotal" => intval($totalData),
                  "recordsFiltered" => intval($totalData),
                  "data" => $data
                );

                echo json_encode($json_data);

            else:
              $json_data = array("data" => array());
              echo json_encode($json_data);
            endif;

            wp_die();
            

     }



}