<?php

namespace Mm365;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class SuperBuyers
{

    use CouncilAddons;
    use NotificationAddon;
    use TimezoneAddon;
    use ReusableMethods;
    public $associated_buyers;

    function __construct()
    {
        $user = wp_get_current_user();

        $this->associated_buyers = get_user_meta($user->ID, '_mm365_associated_buyer');

        //Load assets
        add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

        //Email availability
        add_action('wp_ajax_email_availablity_check', array($this, 'email_availablity_check'));

        //Username availability
        add_action('wp_ajax_username_availability_check', array($this, 'username_availability_check'));

        //Search buyer companies
        add_action('wp_ajax_get_buyer_companies', array($this, 'get_buyer_companies'));

        add_action('wp_ajax_get_existing_buyer_companies', array($this, 'get_existing_buyer_companies'));
  
        //Create account
        add_action('wp_ajax_create_superbuyer', array($this, 'create_superbuyer'));

        add_action('wp_ajax_buyer_team_created_meetings', array($this, 'buyer_team_created_meetings'));
   
        //Update
        add_action('wp_ajax_update_superbuyer', array($this, 'update_superbuyer'));

        add_filter('mm365_superbuyer_get_matchrequests_count', array($this,'get_matchrequest_counts'),10,2);

        add_filter('mm365_superbuyer_get_meetings_created_count', array($this,'get_meetings_created_count'),10,0);

        add_action('wp_ajax_add_sub_buyer', [$this, 'add_sub_buyer'], 10, 2);
   

    }



    /*---------------------------------------------
    * Assets
    ----------------------------------------------*/
    function assets()
    {

        if (wp_register_script('mm365_superbuyer', plugins_url('matchmaker-core/assets/mm365_superbuyer.js'), array('jquery'), false, TRUE)) {
            wp_enqueue_script('mm365_superbuyer');
            wp_localize_script(
                'mm365_superbuyer',
                'superBuyerAjax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce("superBuyer_ajax_nonce")
                )
            );
        }

        if (wp_register_script('mm365_superbuyer_public', plugins_url('matchmaker-core/assets/super_buyer/sb_public.js'), array('jquery'), false, TRUE)) {
            wp_enqueue_script('mm365_superbuyer_public');
            wp_localize_script(
              'mm365_superbuyer_public',
              'superBuyerPubAjax',
              array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("superBuyerPub_ajax_nonce")
              )
            );
          }

    }


    /**------------------------------------------------------------
        * 
        * Email availability
        * 
        --------------------------------------------------------------*/
    function email_availablity_check()
    {

        //Get values
        $email = sanitize_email($_POST['email']);
        $nonce = sanitize_text_field($_POST['nonce']);
        $return = '0';

        if (!wp_verify_nonce($nonce, 'superBuyer_ajax_nonce') or !is_user_logged_in()) {
            die();
        } else {
            //Check valid email
            if (filter_var($email, FILTER_VALIDATE_EMAIL) and email_exists($email) == FALSE) {
                $return = "1";
            } else {
                $return = "0";
            }
            echo $return;
            die();
        }

        //1=available 0=not available

    }


    /**---------------------------------------------------------------------
     * 
     * User name availability
     * 
     -----------------------------------------------------------------------*/

    function username_availability_check()
    {

        //Get values
        $username = sanitize_text_field($_POST['username']);
        $nonce = sanitize_text_field($_POST['nonce']);
        $return = '0';

        if (!wp_verify_nonce($nonce, 'superBuyer_ajax_nonce') or !is_user_logged_in()) {
            die();
        } else {
            //Check valid email
            if (username_exists($username) == FALSE) {
                $return = "1";
            } else {
                $return = "0";
            }
            echo $return;
            die();
        }
    }



    /*---------------------------------------------
    * Buyer list select
    ----------------------------------------------*/
    function get_buyer_companies()
    {
        $return = array();
        $buyer_companies = new \WP_Query(
            array(
                's' => $_GET['q'],
                'post_type' => 'mm365_companies',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'mm365_service_type',
                        'value' => 'buyer',
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'mm365_company_council',
                        'value' => $_GET['council'],
                        'compare' => '=',
                    )
                )
            )
        );
        if ($buyer_companies->have_posts()):
            while ($buyer_companies->have_posts()):
                $buyer_companies->the_post();
                $title = (mb_strlen($buyer_companies->post->post_title) > 50) ? mb_substr($buyer_companies->post->post_title, 0, 49) . '...' : $buyer_companies->post->post_title;
                $return[] = array($buyer_companies->post->ID, $title);
            endwhile;
        endif;
        echo json_encode($return);
        wp_reset_postdata();
        wp_die();
    }


    /*---------------------------------------------
    * Existing Buyer list of a super buyer
    ----------------------------------------------*/
    function get_existing_buyer_companies()
    {

        //get supb id
        $superbuyer_id = sanitize_text_field($_POST['superbuyer_id']);
        $nonce = sanitize_text_field($_POST['nonce']);

        if (!wp_verify_nonce($nonce, 'superBuyer_ajax_nonce') or !is_user_logged_in()) {
            die();
        }

        if ($superbuyer_id != NULL) {
            //Get user meta - _mm365_associated_buyer
            $associated_buyers = get_user_meta($superbuyer_id, '_mm365_associated_buyer');

            $return = array();

            if(!empty($associated_buyers)){
            $buyer_companies = new \WP_Query(
                array(
                    'post_type' => 'mm365_companies',
                    'post__in' => $associated_buyers,
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                )
            );
            if ($buyer_companies->have_posts()):
                while ($buyer_companies->have_posts()):
                    $buyer_companies->the_post();
                    $title = (mb_strlen($buyer_companies->post->post_title) > 50) ? mb_substr($buyer_companies->post->post_title, 0, 49) . '...' : $buyer_companies->post->post_title;
                    $return[] = array("id" => $buyer_companies->post->ID, "text" => $title);
                endwhile;
            endif;
          }
            echo json_encode($return);

            wp_reset_postdata();
            wp_die();

        }



    }



    /*---------------------------------------------
    * Add Super Buyer Account
    ----------------------------------------------*/
    function create_superbuyer()
    {

        $nonce = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce($nonce, 'superBuyer_ajax_nonce') or !is_user_logged_in()) {
            die();
        }

        //Create account
        //Inputs
        $email = sanitize_email($_POST['superbuyer_email']);
        $username = sanitize_text_field($_POST['superbuyer_username']);
        $first_name = sanitize_text_field($_POST['superbuyer_first_name']);
        $last_name = sanitize_text_field($_POST['superbuyer_last_name']);
        $phone = sanitize_text_field($_POST['superbuyer_phone']);
        $council_id = sanitize_text_field($_POST['superbuyer_council_id']);
        $associated_buyers = $_POST['associated_buyers'];

        
        $for_conference = sanitize_text_field($_POST['sb_for_event']);
        $conference_id = sanitize_text_field($_POST['associatedbuyer_upcoming_conference']);

        //Generate Password
        $password = wp_generate_password(8);

        //Attempt registartion
        //Confirm email and username availability
        $return = '';
        //check 
        if (username_exists($username) == FALSE && email_exists($email) == FALSE and filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userdata = array(
                'user_pass' => $password,
                //(string) The plain-text user password.
                'user_login' => $username,
                //(string) The user's login username.
                'user_email' => strtolower($email),
                //(string) The user email address.
                'display_name' => $first_name . ' ' . $last_name,
                //(string) The user's display name. Default is the user's username.
                'first_name' => $first_name,
                //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
                'last_name' => $last_name,
                //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
                // 'description'           => 'Council Manager -'.$phone,   //(string) The user's biographical description.
                'user_registered' => date('Y-m-d H:i:s'),
                //(string) Date the user registered. Format is 'Y-m-d H:i:s'.
                'show_admin_bar_front' => 'false',
                //(string|bool) Whether to display the Admin Bar for the user on the site's front end. Default true.
                'role' => 'super_buyer', //(string) User's role.
            );
            $user_id = wp_insert_user($userdata);
            add_user_meta($user_id, '_mm365_superbuyer_phone', $phone);

            //Add associated buyer companies are user meta
            foreach ($associated_buyers as $company_id) {
                add_user_meta($user_id, '_mm365_associated_buyer', sanitize_text_field($company_id));
            }

            if($for_conference == 'yes'){
              add_user_meta($user_id, '_mm365_only_for_conference', $for_conference);
              add_user_meta($user_id, '_mm365_conference_participation', $conference_id);
            }

            /*
             * @Dependency UsersWP Plugin
             * Find user_id in 'uwp_usermeta' table and update primary_msdc     with id
             */
            global $wpdb;
            $table_name = $wpdb->prefix . 'uwp_usermeta';
            $wpdb->update($table_name, array('primary_msdc' => $council_id), array('user_id' => $user_id));


            //Send welcome email
            $this->send_welcome_email_to_new_user($user_id,$password);
            $return = 'success';

        } else {
            $return = 'failed';
        }
        echo $return;
        die();

    }



    /*---------------------------------------------
    * Update Super Buyer Account
    ----------------------------------------------*/
    function update_superbuyer()
    {

        $nonce = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce($nonce, 'superBuyer_ajax_nonce') or !is_user_logged_in()) {
            die();
        }

        $email = sanitize_email($_POST['superbuyer_email']);
        $first_name = sanitize_text_field($_POST['superbuyer_first_name']);
        $last_name = sanitize_text_field($_POST['superbuyer_last_name']);
        $phone = sanitize_text_field($_POST['superbuyer_phone']);
        $council_id = sanitize_text_field($_POST['superbuyer_council_id']);
        $associated_buyers = $_POST['associated_buyers'];
        $superbuyer_id = sanitize_text_field($_POST['superbuyer_id']);
        $current_state = sanitize_text_field($_POST['login_stat']);

        $conference_id = sanitize_text_field($_POST['superbuyer_upcoming_conference']);

        $return = '';

        //Check email change
        $user_details = get_userdata($superbuyer_id);

        if ($email == $user_details->user_email) {

            $userdata = array(
                'ID' => $superbuyer_id,
                //(string) The user's login username.
                'display_name' => $first_name . ' ' . $last_name,
                //(string) The user's display name. Default is the user's username.
                'first_name' => $first_name,
                //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
                'last_name' => $last_name, //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.             );
            );
            $verify_email = $superbuyer_id;

        } else {

            $userdata = array(
                'ID' => $superbuyer_id,
                //(string) The user's login username.
                'user_email' => strtolower($email),
                //(string) The user email address.
                'display_name' => $first_name . ' ' . $last_name,
                //(string) The user's display name. Default is the user's username.
                'first_name' => $first_name,
                //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
                'last_name' => $last_name, //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
            );
            $verify_email = FALSE;

        }

        //check 
        if (email_exists($email) == $verify_email and filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $user_id = wp_update_user($userdata);
            update_user_meta($user_id, '_mm365_superbuyer_phone', $phone);

            //Add associated buyer companies are user meta
            delete_user_meta($user_id, '_mm365_associated_buyer');
            foreach ($associated_buyers as $company_id) {
                add_user_meta($user_id, '_mm365_associated_buyer', sanitize_text_field($company_id));
            }


            $for_conference = sanitize_text_field($_POST['edit_sb_for_event']);

            if($for_conference == 'yes'){

              delete_user_meta($user_id, '_mm365_conference_participation');
              delete_user_meta($user_id, '_mm365_only_for_conference');

              add_user_meta($user_id, '_mm365_only_for_conference', $for_conference);
              add_user_meta($user_id, '_mm365_conference_participation', $conference_id);
            }else{
              delete_user_meta($user_id, '_mm365_conference_participation');
              delete_user_meta($user_id, '_mm365_only_for_conference');
            }



            //Map users to Council here
            /*
             * @Dependency UsersWP Plugin
             * Find user_id in 'uwp_usermeta' table and update primary_msdc     with id
             */
            $this->update_user_council($user_id, $council_id);

            //Login status toggle
            switch ($current_state) {
                case 'yes':
                    $new_stat = "yes";
                    break;

                default:
                    $new_stat = "";
                    break;
            }
            update_user_meta($user_id, 'baba_user_locked', $new_stat);

            //Return
            $return = 'success';

        } else {
            $return = 'failed';
        }

        echo $return;
        die();


    }

    /**-----------------------------------------------------------
     * 
     * New user welcome mail - Super Buyer Users
     * 
     -------------------------------------------------------------*/

    public function send_welcome_email_to_new_user($user_id, $password)
    {
        $user = get_userdata($user_id);
        $user_email = $user->user_email;
        // Ful name
        $user_full_name = $user->user_firstname . " " . $user->user_lastname;

        //Council name
        $user_council_id = $this->get_userDC($user_id);
        $council_shortname = $this->get_council_info($user_council_id);
        // Welcome email
        $to = $user_email;
        $subject = "Hi " . $user_full_name . ", Welcome to Matchmaker365!";
        $content = '
                  <p>We are previleged to add you as a Super Buyer in Matchmaker365.</p>
                  <p>Username: ' . $user->user_login . '<br/> email:' . $user->user_email . '</p>
                  <p>To login, Please click on the below button to reset the password and login.</p>';

       // $body = $this->mm365_email_body($subject, $content, site_url('forgot'), 'Reset Password');
        $body = $this->mm365_email_body_template($subject, $content, site_url('forgot'), 'Reset Password');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (wp_mail($to, $subject, $body, $headers)) {
            error_log("email has been successfully sent to user whose email is " . $user_email);
        } else {
            error_log("email failed to sent to user whose email is " . $user_email);
        }
    }



    //Public Side

    /**
     * @param string $key - meta_option_name
     * @param string $value - meta value
     * 
     */
    function get_matchrequest_counts($key = NULL, $value = NULL)
    {

      //Optional meta query
      if ($key != '' and $value != '') {
        $meta_search = array('key' => $key, 'value' => $value, 'compare' => '=');
      } else {
        $meta_search = NULL;
      }

      if (!empty($this->associated_buyers)) {

        $query_args = array(
          'posts_per_page' => -1,
          // No limit
          'fields' => 'ids',
          // Reduce memory footprint
          'post_type' => 'mm365_matchrequests',
          'post_status' => array('publish'),
          'meta_query' => array(
            array(
              'key' => 'mm365_requester_company_id',
              'value' => $this->associated_buyers,
              'compare' => 'IN'
            ),
            $meta_search
          )


        );

        $week_query = new \WP_Query($query_args);
        return $week_query->found_posts;
      } else
        return 0;


    }

    /**
     * 
     * 
     */
    function get_meetings_created_count()
    {
  
      if (!empty($this->associated_buyers)) {
        $query_args = array(
          'posts_per_page' => -1,
          // No limit
          'fields' => 'ids',
          // Reduce memory footprint
          'post_type' => 'mm365_meetings',
          'post_status' => array('publish'),
          'meta_query' => array(
            array(
              'key' => 'mm365_proposed_company_id',
              'value' => $this->associated_buyers,
              'compare' => 'IN'
            )
          )
    
    
        );
    
        $week_query = new \WP_Query($query_args);
        return $week_query->found_posts;
      }else return 0;
  
    }


    /**
     * List of meetings created by associated (sub) buyers
     * 
     * 
     */
    function buyer_team_created_meetings()
    {
  
  
      $meetingsClass = $this;
  
      header("Content-Type: application/json");
  
      $prposed_buyers = array();
      $request = $_REQUEST;
      $columns = array(
        0 => 'buyer',
        1 => 'supplier',
        2 => 'council',
        3 => 'contact_person',
        4 => 'meeting_title',
        5 => 'date_and_time',
        6 => 'current_status',
        7 => 'view',
      );
  
  
     
      //Main argument
      $args = array(
        'post_type' => 'mm365_meetings',
        'post_status' => 'publish',
        'posts_per_page' => $request['length'],
        'offset' => $request['start'],
        'order' => 'DESC',
        'order_by' => 'modified',
        'meta_query' => array(
          array(
            'key' => 'mm365_proposed_company_id',
            'value' => $this->associated_buyers,
            'compare' => 'IN',
          ),
          //$add_council_filtering
  
        )
      );
  
      // Filter
      if (isset($request['order'])):
        if ($request['order'][0]['column'] == 0 and $request['order'][0]['dir'] != '') {
  
          $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
          $args['meta_key'] = 'mm365_meeting_with_company';
  
        } elseif ($request['order'][0]['column'] == 1) {
          $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
          $args['meta_key'] = 'mm365_meeting_with_contactperson';
        } elseif ($request['order'][0]['column'] == 5) {
          $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
          $args['meta_key'] = 'mm365_meeting_status';
        }
      endif;
  
      //Search parameter
      if (!empty($request['search']['value'])) { // When datatables search is used
  
        //Verify is its a date
        $look_date = strtotime($request['search']['value']);
        if ($look_date != '') {
          $search_time = array(
            'relation' => 'OR',
            array(
              'key' => 'mm365_meeting_reschedule_timestamp',
              'value' => $look_date,
              'compare' => 'LIKE'
            ),
            array(
              'key' => 'mm365_meeting_slots',
              'value' => $look_date,
              'compare' => 'LIKE'
            )
          );
  
        } else {
          $search_time = '';
        }
        $args['orderby'] = array('modified' => 'DESC');
        $args['meta_query'] = array(
          array(
            'key' => 'mm365_proposed_company_id',
            'value' => $this->associated_buyers,
            'compare' => 'IN',
          ),
          array(
            'relation' => 'OR',
            array(
              'key' => 'mm365_meeting_with_contactperson',
              'value' => sanitize_text_field($request['search']['value']),
              'compare' => 'LIKE',
            ),
            array(
              'key' => 'mm365_meeting_title',
              'value' => sanitize_text_field($request['search']['value']),
              'compare' => 'LIKE'
            ),
            array(
              'key' => 'mm365_proposed_company',
              'value' => sanitize_text_field($request['search']['value']),
              'compare' => 'LIKE'
            ),
            array(
              'key' => 'mm365_meeting_with_company',
              'value' => sanitize_text_field($request['search']['value']),
              'compare' => 'LIKE'
            ),
            array(
              'key' => 'mm365_meeting_status',
              'value' => sanitize_text_field($request['search']['value']),
              'compare' => 'LIKE'
            ),
            $search_time
          )
  
        );
  
  
      }
  
      if(!empty($this->associated_buyers)){
      $meetings = new \WP_Query($args);
      $totalData = $meetings->found_posts;
  
      if ($meetings->have_posts()) {
        while ($meetings->have_posts()) {
  
          $meetings->the_post();
  
          $buyer = get_post_meta(get_the_ID(), 'mm365_proposed_company');
          $with_company = get_post_meta(get_the_ID(), 'mm365_meeting_with_company');
          $company_name = preg_replace("/&#?[a-z0-9]+;/i", " ", wp_filter_nohtml_kses($with_company[0]));
  
          $proposer_tz = get_post_meta(get_the_ID(), 'mm365_proposer_timezone', true);
  
          //Accepted
          $accepted = get_post_meta(get_the_ID(), 'mm365_accepted_meeting_slot', true);
          if ($accepted != FALSE and $accepted <= 3):
            $array_pos = ($accepted - 1);
            $slot = get_post_meta(get_the_ID(), 'mm365_meeting_slots');
  
            $accepted_date = explode("|", $slot[$array_pos]);
            $start = $meetingsClass->convert_time($accepted_date[0], $proposer_tz, $proposer_tz, 'm/d/Y  h:ia');
            $end = $meetingsClass->convert_time($accepted_date[1], $proposer_tz, $proposer_tz, 'h:ia');
            $accepted_slot = $start . " - " . $end;
          elseif ($accepted != FALSE and $accepted == 4):
            //convert from attendee zone
            $slot = get_post_meta(get_the_ID(), 'mm365_meeting_reschedule_timestamp', true);
            $accepted_date = explode("|", $slot);
            $start = $meetingsClass->convert_time($accepted_date[0], $$proposer_tz, $proposer_tz, 'm/d/Y  h:ia');
            $end = $meetingsClass->convert_time($accepted_date[1], $proposer_tz, $proposer_tz, 'h:ia');
            $accepted_slot = $start . " - " . $end;
            //$accepted_slot .= get_post_meta( get_the_ID(), 'mm365_meeting_reschedule_date',true);
          else:
            $accepted_slot = "-";
          endif;
  
  
  
          $meeting_status = get_post_meta(get_the_ID(), 'mm365_meeting_status', true);
  
          //Attendees council
          $attendees_council = get_post_meta(get_the_ID(), 'mm365_proposer_council_id', true);
  
          $nestedData = array();
          $nestedData[] = $buyer[0];
          $nestedData[] = $company_name;
          $nestedData[] = get_post_meta($attendees_council, 'mm365_council_shortname', true);
          $nestedData[] = get_post_meta(get_the_ID(), 'mm365_meeting_with_contactperson', true);
          $nestedData[] = get_post_meta(get_the_ID(), 'mm365_meeting_title', true);
          $nestedData[] = $accepted_slot . "<br/><small>Time zone: " . $proposer_tz . "</small>";
          $nestedData[] = "<span class='meeting_status " . $meeting_status . "'>" . preg_replace('/\_+/', ' ', $meeting_status) . "</span>";
          $nestedData[] = '<a href="' . site_url() . '/sb-view-meeting-details?mid=' . get_the_ID() . '">View</a>'; //Page slug
  
          $data[] = $nestedData;
  
        }
  
        wp_reset_query();
        $json_data = array(
          "draw" => intval($request['draw']),
          "recordsTotal" => intval($totalData),
          "recordsFiltered" => intval($totalData),
          "data" => $data
        );
        echo json_encode($json_data);
      } else {
        $json_data = array(
          "data" => array()
        );
        echo json_encode($json_data);
      }
      wp_die();
    }
  
  
    }


    /**
     * v3.0.3 Onwards
     * Sub Buyer onboarding by Super Buyer
     * 
     */

    function add_sub_buyer(){

      $nonce = sanitize_text_field($_POST['nonce']);
      if (!wp_verify_nonce($nonce, 'superBuyer_ajax_nonce') or !is_user_logged_in()) {
          die();
      }

      //Create account
      //Inputs
      $email = sanitize_email($_POST['associatedbuyer_email']);
      $username = sanitize_text_field($_POST['associatedbuyer_username']);
      $first_name = sanitize_text_field($_POST['associatedbuyer_first_name']);
      $last_name = sanitize_text_field($_POST['associatedbuyer_last_name']);
      $phone = sanitize_text_field($_POST['associatedbuyer_phone']);
      $alt_phone = sanitize_text_field($_POST['associatedbuyer_alt_phone']);
      $council_id = sanitize_text_field($_POST['associatedbuyer_council_id']);

      $superbuyer_id = sanitize_text_field($_POST['current_user']);

      $representing_brand = sanitize_text_field($_POST['associatedbuyer_brand']);
      $opportunities = sanitize_text_field($_POST['opportunities']);
      $conference_participation = sanitize_text_field($_POST['associatedbuyer_upcoming_conference']);

      $naics_codes = sanitize_text_field($_POST['sb_naics_codes']);

      //Generate Password
      $password = wp_generate_password(8);

      //Attempt registartion
      //Confirm email and username availability
      $return = '';
      //check 
      if (username_exists($username) == FALSE && email_exists($email) == FALSE and filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $userdata = array(
              'user_pass' => $password,
              //(string) The plain-text user password.
              'user_login' => $username,
              //(string) The user's login username.
              'user_email' => strtolower($email),
              //(string) The user email address.
              'display_name' => $first_name . ' ' . $last_name,
              //(string) The user's display name. Default is the user's username.
              'first_name' => $first_name,
              //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
              'last_name' => $last_name,
              //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
              // 'description'           => 'Council Manager -'.$phone,   //(string) The user's biographical description.
              'user_registered' => date('Y-m-d H:i:s'),
              //(string) Date the user registered. Format is 'Y-m-d H:i:s'.
              'show_admin_bar_front' => 'false',
              //(string|bool) Whether to display the Admin Bar for the user on the site's front end. Default true.
              'role' => 'business_user', //(string) User's role.
          );
          $user_id = wp_insert_user($userdata);
 
  
          /*
           * @Dependency UsersWP Plugin
           * Find user_id in 'uwp_usermeta' table and update primary_msdc   with id
           */
          global $wpdb;
          $table_name = $wpdb->prefix . 'uwp_usermeta';
          $wpdb->update($table_name, array('primary_msdc' => $council_id), array('user_id' => $user_id));

          //Insert buyer company with minium information
          $buyer_company = array(
            'post_title'  => wp_strip_all_tags($representing_brand."-".$first_name . ' ' . $last_name),
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_type'   => 'mm365_companies'
          );
      
          // Insert the post into the database.
          $company_id = wp_insert_post( $buyer_company );
          update_post_meta( $company_id, 'mm365_service_type',  'buyer' );
          update_post_meta( $company_id, 'mm365_company_council', $council_id );
          update_post_meta( $company_id, 'mm365_contact_person',$first_name . ' ' . $last_name);
          update_post_meta( $company_id, 'mm365_company_email', strtolower($email) );
          update_post_meta( $company_id, 'mm365_company_phone', $phone );
          update_post_meta( $company_id, 'mm365_alt_phone', $alt_phone );
          
          update_post_meta( $company_id, 'mm365_company_city', 111367 );
          update_post_meta( $company_id, 'mm365_company_state', 1434 );
          update_post_meta( $company_id, 'mm365_company_country', 233 );

          update_post_meta( $company_id, 'mm365_company_description', $opportunities );

          update_post_meta($company_id, 'mm365_upcoming_conference_participation', $conference_participation);

          update_post_meta($company_id, 'mm365_main_customers', "");

          update_post_meta( $company_id, 'mm365_approval_required_feature', 'enabled' );

          // $split_naics_codes = explode(", ",$naics_codes);
    
          // foreach($split_naics_codes as $naic){
          //   add_post_meta( $company_id, 'mm365_naics_codes', $naic );
          // }

          //Loop and save NAICS
          $new_naics = array_filter($_POST['naics_codes'], array($this, "purge_empty"));
          delete_post_meta($company_id, 'mm365_naics_codes');
          foreach ($new_naics as $naic) {
            add_post_meta($company_id, 'mm365_naics_codes', $naic);
          }

        
          //Add to buyer team
          add_user_meta($superbuyer_id, '_mm365_associated_buyer', sanitize_text_field($company_id));
        
          //Send welcome email
          $this->welcome_email_to_associate_buyer($user_id,$superbuyer_id,$password);
          $return = 'success';

      } else {
          $return = 'failed';
      }
      echo $return;
      die();
      
    }


    /**
     * Welcome email to associate buyer
     * @param int $user_id
     * @param int $superbuyer_id
     * @param string $password generated by wordpress function
     */

     
    public function welcome_email_to_associate_buyer($user_id, $superbuyer_id, $password)
    {
        $user = get_userdata($user_id);
        $user_email = $user->user_email;
        // Ful name
        $user_full_name = $user->user_firstname . " " . $user->user_lastname;

        //Super buyer name
        $superbuyer = get_userdata($superbuyer_id);
        $superbuyer_full_name = $superbuyer->user_firstname . " " . $superbuyer->user_lastname;


        // Welcome email
        $to = $user_email;
        $subject = "Hi " . $user_full_name . ", Welcome to Matchmaker365!";
        $content = '
                  <p>We are previleged to add you as an Associate Buyer for '.$superbuyer_full_name.' in Matchmaker365. Your login details are </p>
                  <p>Username: ' . $user->user_login . '<br/> email:' . $user->user_email . '</p>
                  <p>To login, Please click on the below button to receive a password reset email in your inbox. Once you receive the email do reset the password and continue to login.</p>';

        //$body = $this->mm365_email_body($subject, $content, site_url('forgot'), 'Reset Password');
        $body = $this->mm365_email_body_template($subject, $content, site_url('forgot'), 'Reset Password');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (wp_mail($to, $subject, $body, $headers)) {
            error_log("email has been successfully sent to user whose email is " . $user_email);
        } else {
            error_log("email failed to sent to user whose email is " . $user_email);
        }
    }


    //Class ends here
}