<?php
namespace Mm365;

/* All methods related to meeting */

if (!defined('ABSPATH'))
  exit;

class CouncilManagers
{
  use CouncilAddons;
  use NotificationAddon;
  use CertificateAddon;

  function __construct()
  {

    add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

    //Email availability
    add_action('wp_ajax_is_email_available', array($this, 'is_email_available'));

    //Username availability
    add_action('wp_ajax_is_username_available', array($this, 'is_username_available'));

    //Create user
    add_action('wp_ajax_create_user', array($this, 'create_user'));

    //Create user
    add_action('wp_ajax_update_user', array($this, 'update_user'));

    //Toggle user lock
    add_action('wp_ajax_toggle_user_lock', array($this, 'toggle_user_lock'));

    //List Council wise MR
    add_action('wp_ajax_council_match_listing', array($this, 'council_match_listing'));

  }

  /**------------------------------------------------------------
   * 
   * Required assets
   * 
   --------------------------------------------------------------*/
  function assets()
  {
    if (wp_register_script('mm365_msdcmanagers', plugins_url('matchmaker-core/assets/mm365_msdcmanagers.js'), array('jquery'), false, TRUE)) {
      wp_enqueue_script('mm365_msdcmanagers');
      wp_localize_script('mm365_msdcmanagers', 'msdcmanagerAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce("msdcmanager_ajax_nonce")
      ));
    }
  }



  /**------------------------------------------------------------
   * 
   * Email availability
   * 
   --------------------------------------------------------------*/
  function is_email_available()
  {

    //Get values
    $email = sanitize_email($_POST['email']);
    $nonce = sanitize_text_field($_POST['nonce']);
    $return = '0';

    if (!wp_verify_nonce($nonce, 'msdcmanager_ajax_nonce') or !is_user_logged_in()) {
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

  function is_username_available()
  {

    //Get values
    $username = sanitize_text_field($_POST['username']);
    $nonce = sanitize_text_field($_POST['nonce']);
    $return = '0';

    if (!wp_verify_nonce($nonce, 'msdcmanager_ajax_nonce') or !is_user_logged_in()) {
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

  /**--------------------------------------------------------------------
   * 
   * Create user
   * 
   ----------------------------------------------------------------------*/

  function create_user()
  {
    //Inputs
    $email = sanitize_email($_POST['dcm_email']);
    $username = sanitize_text_field($_POST['dcm_username']);
    $first_name = sanitize_text_field($_POST['dcm_first_name']);
    $last_name = sanitize_text_field($_POST['dcm_last_name']);
    $phone = sanitize_text_field($_POST['dcm_phone']);
    //$verification_permission = sanitize_text_field($_POST['enable_certification_verification']);
    $council_id = sanitize_text_field($_POST['dcm_council_id']);
    $password = wp_generate_password(8);
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
        'description' => 'Council Manager -' . $phone,
        //(string) The user's biographical description.
        'user_registered' => date('Y-m-d H:i:s'),
        //(string) Date the user registered. Format is 'Y-m-d H:i:s'.
        'show_admin_bar_front' => 'false',
        //(string|bool) Whether to display the Admin Bar for the user on the site's front end. Default true.
        'role' => 'council_manager',
        //(string) User's role.
      );
      $user_id = wp_insert_user($userdata);
      add_user_meta($user_id, '_mm365_dcm_phone', $phone);

      //Map users to Council here
      /*
       * @Dependency UsersWP Plugin
       * Find user_id in 'uwp_usermeta' table and update primary_msdc 	with id
       */
      global $wpdb;
      $table_name = $wpdb->prefix . 'uwp_usermeta';
      $wpdb->update($table_name, array('primary_msdc' => $council_id), array('user_id' => $user_id));

      //Add additional roles if the council has additional peemissions
      $permission_status = get_post_meta($council_id, 'mm365_additional_permissions', TRUE);
      if ($permission_status == 1) {
        $this->councilmanager_additional_permissions($council_id);
      }

      //Send welcome email
      $this->send_welcome_email_to_new_user($user_id, $password);

      $return = 'success';

    } else {
      $return = 'failed';
    }
    echo $return;
    die();

  }


  /**------------------------------------------------------------
   * 
   * Edit and Update user
   * 
   --------------------------------------------------------------*/
  function update_user()
  {

    //Get values
    $email = sanitize_email($_POST['dcm_email']);
    $first_name = sanitize_text_field($_POST['dcm_first_name']);
    $last_name = sanitize_text_field($_POST['dcm_last_name']);
    $phone = sanitize_text_field($_POST['dcm_phone']);
    $council_id = sanitize_text_field($_POST['dcm_council_id']);
    $cmu_id = sanitize_text_field($_POST['cmu_id']);
    $current_state = sanitize_text_field($_POST['login_stat']);
    $return = '';

    //Check email change
    $user_details = get_userdata($cmu_id);

    if ($email == $user_details->user_email) {

      $userdata = array(
        'ID' => $cmu_id,
        //(string) The user's login username.
        'display_name' => $first_name . ' ' . $last_name,
        //(string) The user's display name. Default is the user's username.
        'first_name' => $first_name,
        //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
        'last_name' => $last_name,
        //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
        'description' => 'Council Manager -' . $phone,
        //(string) The user's biographical description.
      );

      $verify_email = $cmu_id;

    } else {

      $userdata = array(
        'ID' => $cmu_id,
        //(string) The user's login username.
        'user_email' => strtolower($email),
        //(string) The user email address.
        'display_name' => $first_name . ' ' . $last_name,
        //(string) The user's display name. Default is the user's username.
        'first_name' => $first_name,
        //(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified.
        'last_name' => $last_name,
        //(string) The user's last name. For new users, will be used to build the second part of the user's display name if $display_name is not specified.
        'description' => 'Council Manager -' . $phone,
        //(string) The user's biographical description.
      );
      $verify_email = FALSE;

    }

    //check 
    if (email_exists($email) == $verify_email and filter_var($email, FILTER_VALIDATE_EMAIL)) {

      $user_id = wp_update_user($userdata);
      update_user_meta($user_id, '_mm365_dcm_phone', $phone);

      //Map users to Council here
      /*
       * @Dependency UsersWP Plugin
       * Find user_id in 'uwp_usermeta' table and update primary_msdc 	with id
       */
      // global $wpdb;
      // $table_name = $wpdb->prefix . 'uwp_usermeta';
      // $wpdb->update( $table_name, array( 'primary_msdc' => $council_id ), array( 'user_id' => $user_id ) );
      $this->update_user_council($user_id, $council_id);

      //Login toggle
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
   * Toggle User State
   * 
   -------------------------------------------------------------*/
  public function toggle_user_lock()
  {

    //Get values
    $user_id = sanitize_text_field($_POST['user_id']);
    $current_state = sanitize_text_field($_POST['current_state']);
    $nonce = sanitize_text_field($_POST['nonce']);
    $return = '0';

    switch ($current_state) {
      case 'yes':
        $new_stat = "";
        break;

      default:
        $new_stat = "yes";
        break;
    }

    if (update_user_meta($user_id, 'baba_user_locked', $new_stat)) {
      $return = 'success';
    } else
      $return = 'failed';

    echo $return;
    die();

  }

  /**-----------------------------------------------------------
   * 
   * New user welcome mail - for council manager
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
                  <p>We are previleged to add you as a Council Manager for ' . $council_shortname . ' in Matchmaker365 Platform.</p>
                  <p>Username: ' . $user->user_login . '<br/> email:' . $user->user_email . '</p>
                  <p>To login, Please click on the below button to set the password and login.</p>';

    $body = $this->mm365_email_body($subject, $content, site_url('forgot'), 'Reset Password');

    $headers = array('Content-Type: text/html; charset=UTF-8');
    if (wp_mail($to, $subject, $body, $headers)) {
      error_log("email has been successfully sent to user whose email is " . $user_email);
    } else {
      error_log("email failed to sent to user whose email is " . $user_email);
    }
  }



  /**-----------------------------------------------------------
   * 
   * Council wise match request listing
   * Data mapped with user's council id againt Match request council id
   * 
   -------------------------------------------------------------*/

  function council_match_listing()
  {

    $user = wp_get_current_user();

    header("Content-Type: application/json");


    $request = $_GET;

    $columns = array(
      0 => 'company_name',
      1 => 'requested_date_and_time',
      2 => 'location',
      3 => 'looking_for',
      4 => 'status',
      5 => 'request',
      6 => 'match',

    );


    $args = array(
      'post_type' => 'mm365_matchrequests',
      'post_status' => 'publish',
      'posts_per_page' => $request['length'],
      'offset' => $request['start'],
      'order' => $request['order'][0]['dir'],
      'meta_query' => array(
        array(
          'key' => 'mm365_requester_company_council',
          'value' => $this->get_userDC($user->ID),
          'compare' => '='
        )
      )
    );

    if ($request['order'][0]['column'] == 0) {
      //$args['orderby'] = $columns[$request['order'][0]['column']];
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_requester_company_name';

    } elseif ($request['order'][0]['column'] == 2) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => $request['order'][0]['dir']);
      //$args['meta_key'] =   'mm365_matched_companies_last_updated';
    } elseif ($request['order'][0]['column'] == 3) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_location_for_search';
    } elseif ($request['order'][0]['column'] == 4) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_services_details';
    } elseif ($request['order'][0]['column'] == 5) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_matchrequest_status';
    }

    if (!empty($request['search']['value'])) { // When datatables search is used
      $args['orderby'] = array('modified' => 'DESC');


      if ($request['order'][0]['column'] == 0) {
        //$args['orderby'] = $columns[$request['order'][0]['column']];
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
        $args['meta_key'] = 'mm365_requester_company_name';

      } elseif ($request['order'][0]['column'] == 2) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => $request['order'][0]['dir']);
        //$args['meta_key'] =   'mm365_matched_companies_last_updated';
      } elseif ($request['order'][0]['column'] == 3) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
        $args['meta_key'] = 'mm365_location_for_search';
      } elseif ($request['order'][0]['column'] == 4) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
        $args['meta_key'] = 'mm365_services_details';
      } elseif ($request['order'][0]['column'] == 5) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
        $args['meta_key'] = 'mm365_matchrequest_status';
      }




      $args['meta_query'] = array(
        array(
          'key' => 'mm365_requester_company_council',
          'value' => $this->get_userDC($user->ID),
          'compare' => '='
        ),
        array(
          'relation' => 'OR',
          array(
            'key' => 'mm365_services_details',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_requester_company_name',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_location_for_search',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_matchrequest_status',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_matchrequest_status',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_requester_company_council',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => '='
          ),
          array(
            'key' => 'mm365_matched_companies_last_updated',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
        )


      );



    }

    $match_query = new \WP_Query($args);
    $totalData = $match_query->found_posts;

    if ($match_query->have_posts()) {
      while ($match_query->have_posts()) {
        $match_query->the_post();

        $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
        $company_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);
        $last_updated_byuser = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);

        if ($company_id != ''):
          $company_name = $this->get_certified_badge($company_id, true) . get_the_title($company_id);
        else:
          $company_name = '';
        endif;
        $service_type = get_post_meta($company_id, 'mm365_service_type', true);
        ($service_type == 'buyer') ? $badge = '<span class="cmp_badge">Buyer</span>' : $badge = '<span class="cmp_badge supplier">Supplier</span>';

        $nestedData = array();

        $nestedData[] = $company_name;
        $nestedData[] = $badge;
        $nestedData[] = $last_updated_byuser;
        //Location Display
        $nestedData[] = get_post_meta(get_the_ID(), 'mm365_location_for_search', true);


        $nestedData[] = get_post_meta(get_the_ID(), 'mm365_services_details', true);
        switch ($status) {
          case 'nomatch':
            $nestedData[] = "<span class='" . $status . "'>No Match</span>";
            break;
          case 'auto-approved':
            $nestedData[] = "<span class='" . $status . "'>Auto Approved</span>";
            break;
          default:
            $nestedData[] = "<span class='" . $status . "'>" . ucfirst($status) . "</span>";
            break;
        }
        $nestedData[] = '<a href="' . site_url() . '/view-match-request-details?mr_id=' . get_the_ID() . '">View Details</a>';
        if ($status != 'nomatch') {
          $nestedData[] = '<a href="' . site_url() . '/admin-match-request-manage?mr_id=' . get_the_ID() . '">View Match</a>';
        } else {
          $nestedData[] = '<span class="text-disabled">View Match</span>';
        }

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


  //Class ends here
}