<?php
namespace Mm365;

trait SubscriptionAddon{



    /**-----------------------------------
     * Email notification cron
     * Sends out notification to suppliers whose 
     * subscription are about to expire in 
     * 1 day, 30 Days, 60 Days
     -------------------------------------*/
     function notify_expiring_subscriptions($expire_in = '90'){

        //Find certificates expiring in 90th day from today
        $today         = date('Y-m-d');
        switch ($expire_in) {
          case '0':
            $expiring_stamp = (strtotime($today) + 1440 * 60);
            $expiring = date('m/d/Y', $expiring_stamp);
            break;
          case '60':
            $expiring_stamp = (strtotime($today) + 86400 * 60);
            $expiring = date('m/d/Y', $expiring_stamp);
              break;
          case '30':
             $expiring_stamp = (strtotime($today) + 43200 * 60);
             $expiring = date('m/d/Y', $expiring_stamp);
              break;
          default:
            $expiring_stamp = (strtotime($today) + 129600 * 60);
            $expiring = date('m/d/Y', $expiring_stamp);
            break;
        }
    
        //$notify = array();
        $args = array(
          'post_type'      => 'mm365_companies',
          'post_status'    => 'publish',
          'posts_per_page' => -1, 
          'order'          => 'DESC',
          'fields'         => 'ids',
          'meta_query' => array(
            array(
                'key'     => 'mm365_subscription_status',
                'value'   => 'Active',
                'compare' => '=',
              ),
              array(
                'key'     => 'mm365_subscription_enddate',
                'value'   => $expiring,
                'compare' => '=',
              )
          )
        );
    
        $companies = new \WP_Query($args);
        $total = $companies->found_posts;
    
        if($companies->have_posts()){
          while($companies->have_posts()) {
            $companies->the_post();
            $mail_id      = get_post_meta( get_the_ID(), 'mm365_company_email', true );
            $contact_name = get_post_meta( get_the_ID(), 'mm365_contact_person', true );
            //echo  get_the_title( get_the_ID() ).$mail_id.$contact_name;
            $this->notification_mail($mail_id,$contact_name,$expire_in);
          }
          die();
          //return $notify;
        } else return false;
        
    }

    /**---------------------------------------------------
     * 
     * Notification email
     * 
     ------------------------------------------------------*/

      function notification_mail($mail_id, $contact_name, $period, $council_id = NULL){
        //$mail_id
        //$contact_name
        //$period - Change content based on period
        $link  = site_url();

        if($period != '0'){
          $subject     = 'Your Matchmaker365 subscription is expiring in '.$period.' days';
          $title       =  $subject;
          $content     = '
                  <p>Hi '.$contact_name.',</p>
                  <p>Your Matchmaker365 subscription is expiring in '.$period.' days. Please contact your council or Matchmaker365 team to get it renewed. 
                  Expired subscriptions will block your chance for being visible in match requests thus affecting more business alliances coming to your way</p>'; 
        }else{
          $subject     = 'Your Matchmaker365 subscription is expiring tommorow';
          $title       =  $subject;
          $content     = '
          <p>Hi '.$contact_name.',</p>
          <p>Your Matchmaker365 subscription is expiring tommorow. Please contact your council or Matchmaker365 team to get it renewed. 
          Expired subscriptions will block your chance for being visible in match requests thus affecting more business alliances coming to your way</p>';
        }

        $body        = $this->mm365_email_body($title,$content,$link,'Renew Subscription');
        $headers     = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $mail_id, $subject, $body, $headers ); 

      }

      


    /**-----------------------------------
     * Deactivation - CRON 
     * Deactivate subscription based on mm365_subscription_enddate
     * Task runs every day
     * Change 'mm365_subscription_status' to expired
     -------------------------------------*/
     function deactivate_subscription(){

        //Check all companies with 'Active' subscription
        $companies = new \WP_Query( array(
            'post_type'   => 'mm365_companies',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'fields' => 'ids',
            'meta_query' => array(
              array(
                'key'     => 'mm365_subscription_status',
                'value'   => 'Active',
                'compare' => '=',
              ),
              array(
                'key'     => 'mm365_subscription_enddate',
                'value'   => date('Y-m-d'),
                'compare' => '<',
                'type'    => 'DATE'
              )
            )
        ));
        //Check if end date is today - hold

        if( $companies->have_posts() ) :
            while( $companies->have_posts() ) : $companies->the_post();
                $companies_to_expire[] = get_the_ID();
            endwhile;
        endif;

        //Delete Metas
        if(!empty($companies_to_expire)){
            foreach ($companies_to_expire as $cmp_id) {
                
                delete_post_meta( $cmp_id, 'mm365_subscription_startdate' );
                delete_post_meta( $cmp_id, 'mm365_subscription_enddate' );
                delete_post_meta( $cmp_id, 'mm365_subscription_status' );
                delete_post_meta( $cmp_id, 'mm365_subscription_enabledby');
                delete_post_meta( $cmp_id, 'mm365_subscription_type');

                add_post_meta($cmp_id, 'mm365_subscription_status',  'Expired');
            }
        }
       
        wp_reset_query();

     }

         /**-----------------------------------
     * Enable subscription
     -------------------------------------*/
    function enable_council_wise_subscription($council_id){

      //Function is used - mm365_council.php
          $companies = new \WP_Query( array(
              'post_type'   => 'mm365_companies',
              'post_status' => 'publish',
              'posts_per_page' => -1, 
              'fields'         => 'ids',
              'meta_query' => array(
                array(
                  'key'     => 'mm365_company_council',
                  'value'   => $council_id,
                  'compare' => '=',
                ),
                array(
                  'key'     => 'mm365_subscription_status',
                  'compare' => 'NOT EXISTS',
                )
              )
          ));
          //Check if end date is today - hold
  
          if( $companies->have_posts() ) :
              while( $companies->have_posts() ) : $companies->the_post();
                add_post_meta( get_the_ID(),'mm365_subscription_status', 'Not Subscribed' );
              endwhile;
          endif;
  
          wp_reset_postdata();

    }

    /**-----------------------------------
     * Disable council wise subscription
     -------------------------------------*/
     function disable_council_wise_subscription($council_id){

         $companies = new \WP_Query( array(
            'post_type'   => 'mm365_companies',
            'post_status' => 'publish',
            'posts_per_page' => -1, 
            'fields'         => 'ids',
            'meta_query' => array(
              array(
                'key'     => 'mm365_company_council',
                'value'   => $council_id,
                'compare' => '=',
              ),
              array(
                'key'     => 'mm365_subscription_status',
                'compare' => 'EXISTS',
              )
            )
        ));
        if( $companies->have_posts() ) :
          while( $companies->have_posts() ) : $companies->the_post();
              delete_post_meta( get_the_ID(), 'mm365_subscription_startdate' );
              delete_post_meta( get_the_ID(), 'mm365_subscription_enddate' );
              delete_post_meta( get_the_ID(), 'mm365_subscription_status' );
              delete_post_meta( get_the_ID(), 'mm365_subscription_enabledby');
              delete_post_meta( get_the_ID(), 'mm365_subscription_type');
          endwhile;
      endif;
      wp_reset_postdata();
     }

}