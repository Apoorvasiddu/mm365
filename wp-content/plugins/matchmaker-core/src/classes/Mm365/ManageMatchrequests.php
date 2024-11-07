<?php

namespace Mm365;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Create / Edit / Show
 * 
 */

class ManageMatchrequests extends Helpers
{
  use CouncilAddons;
  use CertificateAddon;
  use MatchrequestAddon;
  use CompaniesAddon;
  use NotificationAddon;
  use OfflineConferencesAddon;


  function __construct()
  {
    add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

    //Ajax
    add_action('wp_ajax_mm365_matchrequests_admin_listing', array($this, 'list'), 11);

    add_action('wp_ajax_mm365_admin_matchrequests_approve', array($this, 'approve'), 11);

    add_action('wp_ajax_mm365_delete_matchrequest', array($this, 'force_delete'), 11);

    add_action('wp_ajax_mm365_force_nomatch_matchrequest', array($this, 'force_nomatch'), 11);

    add_action('wp_ajax_mm365_search_companynames', array($this, 'find_companies'), 11);

    add_action('wp_ajax_mm365_manually_add_companies_to_mr', array($this, 'force_add_results'), 11 ); // wp_ajax_{action}

    add_action('wp_ajax_mm365_matchpreference_admin_listing', array($this, 'matchpreference_list_companies'), 11 ); 

    add_action('wp_ajax_mm365_matchpreference_toggle',array($this, 'matchpreference_toggle_status'), 11 ); 

    //filters
    add_filter('mm365_matchrequest_admin_preview', array($this, 'show'), 1, 2);

    //From traits
    add_filter('mm365_matchrequest_show_status', array($this, 'matchrequest_show_status'), 1, 2);

    add_action('wp_ajax_mm365_get_suppliers_in_conference', [$this, 'suppliers_in_conference'], 10, 1);

  }


  /**
   * 
   * 
   * 
   */

  function assets()
  {

    $localize = array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce("manage-matchrequest"),
      'current_user' => get_current_user_id()
    );
    wp_register_script('admin_matchrequests', plugins_url('matchmaker-core/assets/admin_matchrequests.js'), array('jquery'), false, true);
    wp_localize_script('admin_matchrequests', 'adminmatchrequestAjax', $localize);
    wp_enqueue_script('admin_matchrequests');
    wp_register_script('scrollmagic', plugins_url('matchmaker-core/assets/ScrollMagic.min.js'));
    wp_enqueue_script('scrollmagic');

    wp_register_script('admin_list_matchmaking', plugins_url('matchmaker-core/assets/admin_matchlist_ajax.js'), array('jquery'), false, true);
    wp_localize_script('admin_list_matchmaking', 'adminmatchlistAjax', $localize);
    wp_enqueue_script('admin_list_matchmaking');

    wp_register_script('admin_list_matchpreference', plugins_url('matchmaker-core/assets/admin_matchpreference_ajax.js'),array('jquery') ,false,true );
    wp_localize_script('admin_list_matchpreference', 'adminmatchpreferenceAjax',$localize);
    wp_enqueue_script('admin_list_matchpreference');
  }


  /**
   * Approving a match request
   * 
   * 
   */
  function approve()
  {

    $mr_id = $_POST['mr_id'];
    $existing = maybe_unserialize(get_post_meta($_POST['mr_id'], 'mm365_matched_companies', true));

    //Match with selected list and update status variable 0 - Pending, 1 - Approved
    $to_approve = explode(",", $_POST['to_approve_list']);

    $to_approve_ids = array();
    foreach ($to_approve as $key => $value) {
      $cmp = explode("-", $value);
      $to_approve_ids[] = $cmp[1];
    }

    $selected = $to_approve_ids;
    $updated_list = array();
    foreach ($existing as $key => $value) {
      //echo $value[0]."-".$value[1]."<br/>";
      if (in_array($value[0], $selected)) {
        array_push($updated_list, array($value[0], "1"));
      } else {
        if ($value[1] != '1') {
          array_push($updated_list, array($value[0], "0"));
        } else {
          array_push($updated_list, array($value[0], "1"));
        }
      }
    }

    //update 'mm365_matched_companies'
    update_post_meta($mr_id, 'mm365_matched_companies', maybe_serialize($updated_list));

    //Update 'mm365_matchrequest_status' to approved
    update_post_meta($mr_id, 'mm365_matchrequest_status', 'approved');
    //update_post_meta($mr_id, 'mm365_matched_companies_approved_time',time());
    update_post_meta($mr_id, 'mm365_matched_companies_approved_by', get_current_user_id());
    $data = array(
      'ID' => $mr_id,
      'post_content' => "",
      'meta_input' => array(
        'mm365_matched_companies_approved_time' => time(),
      )
    );
    if (wp_update_post($data)) {

      $this->notfication_matchapproved($mr_id);

      echo 'success';
    } else {
      echo 'fail';
    }

    die();
  }

  /**
   * Converts a match request wih results to no results forcefully 
   * 
   */
  function force_nomatch()
  {

    //Get post ID
    $nonce = $_POST['nonce'];
    $mr_id = $_POST['mr_id'];

    if (!wp_verify_nonce($nonce, 'manage-matchrequest') or !is_user_logged_in()) {
      echo '0';
      die();
    }

    if (update_post_meta($mr_id, 'mm365_matchrequest_status', 'nomatch')) {

      delete_post_meta($mr_id, 'mm365_matched_companies');
      delete_post_meta($mr_id, 'mm365_matched_companies_scores');

      
      $this->notfication_nomatch($mr_id);

      echo '1';
    }
    wp_die();


  }
  /**
   * Admin only option - deletes a match request completely
   * 
   * 
   */
  function force_delete()
  {

    //Performs a soft deletion

    //Get post ID
    $nonce = $_POST['nonce'];
    $mr_id = $_POST['mr_id'];

    if (!wp_verify_nonce($nonce, 'manage-matchrequest') or !is_user_logged_in()) {
      die();
    }

    $arg = array(
      'ID' => $mr_id,
      'post_status' => 'pending',
    );
    wp_update_post($arg);
    echo '1';
    wp_die();


  }


  /**
   * 
   * 
   * 
   */
  function find_companies()
  {
    $return = array();
    $sched_results = new \WP_Query(
      array(
        's' => $_GET['q'],
        'post_type' => 'mm365_companies',
        'post_status' => 'publish',
        'meta_query' => array(
          array(
            'key' => 'mm365_service_type',
            'value' => 'seller',
            'compare' => '=',
          )
        )
      )
    );
    if ($sched_results->have_posts()):
      while ($sched_results->have_posts()):
        $sched_results->the_post();
        $title = (mb_strlen($sched_results->post->post_title) > 50) ? mb_substr($sched_results->post->post_title, 0, 49) . '...' : $sched_results->post->post_title;
        $return[] = array($sched_results->post->ID, $title);
      endwhile;
    endif;
    echo json_encode($return);
    wp_die();
  }


  /**
   * Forcefully add companies to match results that didn't picked up
   * 
   * 
   */
  function force_add_results()
  {

    //Get post ID
    $nonce = $_POST['nonce'];
    $mr_id = $_POST['adding_to_mr_id'];
    $companies_to_add = $_POST['companies_to_add'];

    //Existing companies
    $existing_results = maybe_unserialize(get_post_meta($mr_id, 'mm365_matched_companies', true));

    //Check current match status - if its auto approved, newly added items will also be auto approved
    (get_post_meta($mr_id, 'mm365_matchrequest_status', true) == 'auto-approved') ? $approval_state = "1" : $approval_state = "0";


    //Newly adding companies
    $adding_companies = array();
    foreach ($companies_to_add as $cmp_id) {
      array_push($adding_companies, array($cmp_id, $approval_state));
    }

    //Check for duplicates and prepare new array
    $merged = array_merge($existing_results, $adding_companies);
    $revised_result = array();
    foreach ($merged as $v) {
      $id = $v[0];
      isset($revised_result[$id]) or $revised_result[$id] = $v;
    }

    if (!wp_verify_nonce($nonce, 'manage-matchrequest') or !is_user_logged_in()) {
      echo '0';
      wp_die();
    } else {

      if (update_post_meta($mr_id, 'mm365_matched_companies', maybe_serialize($revised_result))) {
        echo "1";
      } else {
        echo "3";
      }

    }

    wp_die();



  }


  /**
   * View Matchrequest details - preview
   * 
   * 
   */
  function show($post_id, $def_stat = 'publish')
  {

    $args = array(
      'p' => $post_id,
      'post_type' => 'mm365_matchrequests',
      'post_status' => $def_stat,
      'posts_per_page' => 1,
      'orderby' => 'date',
    );
    $loop = new \WP_Query($args);


    while ($loop->have_posts()):
      $loop->the_post();
      $requester_company_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);
      $match_status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
      ?>

      <section class="company_preview matchrequest_details_block">

        <div class="row">

          <div class="col-md-3">
            <h6>Requester Company</h6>
            <a
              href="<?php echo site_url(); ?>/view-company?ret=details&cid=<?php echo $requester_company_id ?>&mr_id=<?php echo get_the_ID() ?>">
              <?php
              echo $this->get_certified_badge($requester_company_id, true) . get_the_title($requester_company_id);
              ?>
            </a>
            <?php $cmp_council = get_post_meta($requester_company_id, 'mm365_company_council', true);
            echo " (" . get_post_meta($cmp_council, 'mm365_council_shortname', true) . ")"; ?>
          </div>

          <div class="col-md-5">
            <h6>Details of services or products you are looking for</h6>
            <p>
              <?php echo get_post_meta(get_the_ID(), 'mm365_services_details', true); ?>
            </p>
          </div>

          <div class="col-md-4">
            <h6>Location where the services or products are needed</h6>
            <p>
              <?php
              $locations_to_search = get_post_meta(get_the_ID(), 'mm365_location_for_search', true);
              echo $locations_to_search;
              ?>
            </p>
          </div>

        </div>

        <div class="row pto-30">

          <div class="col-md-3">
            <h6>Requested date & time</h6>
            <?php echo get_the_modified_time("m/d/Y h:i A"); ?>
          </div>

          <div class="col-md-3">
            <h6>Contact Person</h6>
            <?php echo get_post_meta($requester_company_id, 'mm365_contact_person', true); ?>
          </div>

          <div class="col-md-3">
            <h6>Phone</h6>
            <?php echo get_post_meta($requester_company_id, 'mm365_company_phone', true); ?>
          </div>

          <div class="col-md-3">
            <h6>Email</h6>
            <?php echo get_post_meta($requester_company_id, 'mm365_company_email', true); ?>
          </div>

        </div>
        <!-- Toggle area -->
        <section id="mr-advanced-block">
          <div class="row pto-30">

            <div class="col-md-3">
              <h6>Service or products required</h6>
              <?php
              $show_services = implode(', ', (get_post_meta(get_the_ID(), 'mm365_services_looking_for')));
              echo ($show_services != '') ? $show_services : '-';
              ?>
            </div>

            <div class="col-md-3">
              <h6>Industry</h6>
              <?php
              $show_industry = implode(', ', (get_post_meta(get_the_ID(), 'mm365_services_industry')));
              echo ($show_industry != '') ? $show_industry : '-';
              ?>
            </div>

            <div class="col-md-3">
              <h6>Minority classifications</h6>
              <?php
              $minority_categories = (get_post_meta(get_the_ID(), 'mm365_mr_mbe_category'));
              if (!empty($minority_categories)) {
                //array_walk_recursive($minority_categories,'mm365_expand_minoritycode' );
                $cnt = 0;
                foreach ($minority_categories as $key => $value) {
                  echo $this->expand_minoritycode($value);
                  $cnt++;
                  if (count($minority_categories) > $cnt) {
                    echo ", ";
                  }
                }
              } else {
                echo "-";
              }
              ?>

            </div>
            <div class="col-md-3">
              <h6>Industry Certifications</h6>
              <?php
              $certifications = (get_post_meta(get_the_ID(), 'mm365_certifications'));

              if (!empty($certifications) or $certifications != ''):
                foreach ($certifications as $key => $value) {
                  $mm365_certifications[] = $value;
                }
                if (isset($mm365_certifications)):
                  echo implode(', ', $mm365_certifications);
                else:
                  echo "-";
                endif;
              endif;
              ?>
            </div>


          </div>

          <div class="row pto-30">

            <div class="col-md-3">
              <h6>NAICS Codes</h6>
              <?php
              foreach ((get_post_meta(get_the_ID(), 'mm365_naics_codes')) as $key => $value) {
                $naics[] = $value;
              }
              if (isset($naics)):
                echo implode(', ', $naics);
              else:
                echo "-";
              endif;
              ?>
            </div>
            <div class="col-md-3">
              <h6>Size of company (Annual sales in $USD)</h6>
              <?php
              $company_size = get_post_meta(get_the_ID(), 'mm365_size_of_company', true);
              echo $company_size ?: '-';
              ?>
            </div>

            <div class="col-md-3">
              <h6>Number of employees</h6>
              <?php
              $employee_count = get_post_meta(get_the_ID(), 'mm365_number_of_employees', true);
              echo $employee_count ?: '-';

              ?>
            </div>
            <div class="col-md-3">
              <h6>Looking for international assistance</h6>
              <?php $int_assi = get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor');

              if (!empty($int_assi)) {
                $int_assi_looking = implode(', ', $int_assi);
              } else {
                $int_assi_looking = '';
              }
              echo $int_assi_looking ?: '-';

              ?>
            </div>
          </div>

          <?php if ($match_status == 'completed' or $match_status == 'cancelled'): ?>
            <div class="row pto-30">

              <div class="col-md-3">
                <h6>Reason for
                  <?php echo ($match_status == 'completed') ? 'completion' : 'cancellation'; ?>
                </h6>
                <?php echo esc_html(get_post_meta(get_the_ID(), 'mm365_reason_for_closure_filter', true)); ?><br />
              </div>

              <div class="col-md-3">
                <h6>
                  <?php echo ($match_status == 'completed') ? 'Completion' : 'Cancellation'; ?> Message
                </h6>
                <?php echo esc_html(get_post_meta(get_the_ID(), 'mm365_reason_for_closure', true)); ?>
              </div>


              <?php
              $contract_value = get_post_meta(get_the_ID(), 'mm365_contract_value', true);
              if ($contract_value != NULL) {
                ?>
                <div class="col-md-3">
                  <h6>Contract Value</h6>
                  <?php echo esc_html(get_post_meta(get_the_ID(), 'mm365_contract_value', true)); ?>
                </div>
                <div class="col-md-3">
                  <h6>Contract Terms & Conditions</h6>
                  <p>
                    <?php echo esc_html(get_post_meta(get_the_ID(), 'mm365_contract_termsandconditions', true)); ?>
                  </p>
                </div>
                <?php
              }
              ?>

            </div>
          <?php endif; ?>



        </section>

        <div class="pto-20 pbo-20 text-left">
          <a href="#" id="expand-mr-block">+ More Details</a>
        </div>

      </section>


      <?php
    endwhile;
    wp_reset_postdata();
    //die(); 

  }

  /**
   * Show table of match reqests (Data tables)
   * 
   */
  function list()
  {
    header("Content-Type: application/json");

    //$user = wp_get_current_user();

    //Get list of councils
    $councils_array = $this->get_councils_list();

    $request = $_POST;

    $columns = array(
      0 => 'company_name',
      1 => 'type',
      2 => 'council_name',
      3 => 'requested_date_and_time',
      4 => 'location',
      5 => 'looking_for',
      6 => 'status',
      7 => 'request',
      8 => 'match',

    );

    if ($request['council_filter'] != '') {
      $council_filtering = array(
        'key' => 'mm365_requester_company_council',
        'value' => sanitize_text_field($request['council_filter']),
        'compare' => '='
      );
    } else
      $council_filtering = NULL;


    $args = array(
      'post_type' => 'mm365_matchrequests',
      'post_status' => 'publish',
      'posts_per_page' => $request['length'],
      'offset' => $request['start'],
      'order' => $request['order'][0]['dir'],
    );

    if ($request['order'][0]['column'] == 0) {
      //$args['orderby'] = $columns[$request['order'][0]['column']];
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_requester_company_name';

    } elseif ($request['order'][0]['column'] == 3) {
      $args['orderby'] = array('modified' => $request['order'][0]['dir']);
      //$args['meta_key'] =   'mm365_matched_companies_last_updated';
    } elseif ($request['order'][0]['column'] == 4) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_location_for_search';
    } elseif ($request['order'][0]['column'] == 5) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_services_details';
    } elseif ($request['order'][0]['column'] == 7) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_matchrequest_status';
    }



    $args['meta_query'] = array(
      $council_filtering
    );

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
        $council_filtering,
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

        // $service_country     = get_post_meta( get_the_ID(), 'mm365_service_country', true );
        // $service_state       = get_post_meta( get_the_ID(), 'mm365_service_state', true );
        // $service_city        = get_post_meta( get_the_ID(), 'mm365_service_city', true );
        $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);
        $company_id = get_post_meta(get_the_ID(), 'mm365_requester_company_id', true);
        $last_updated_byuser = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);

        if ($company_id != '') {
          $company_name = $this->get_certified_badge($company_id, true) . get_the_title($company_id);
        } else
          $company_name = '';
        //type badge

        $service_type = get_post_meta($company_id, 'mm365_service_type', true);
        ($service_type == 'buyer') ? $badge = '<span class="cmp_badge">Buyer</span>' : $badge = '<span class="cmp_badge supplier">Supplier</span>';

        $council_id = get_post_meta(get_the_ID(), 'mm365_requester_company_council', true);
        $council = get_post_meta($council_id, 'mm365_council_shortname', true);

        $nestedData = array();

        $nestedData[] = $company_name;
        $nestedData[] = $badge;
        $nestedData[] = $council;
        //$nestedData[] = "<span colo_code='" . $councils_array[$council_id][2] . "'>" . $councils_array[$council_id][2] . "</span>";
        $nestedData[] = implode(", ",get_post_meta(get_the_ID(),'mm365_naics_codes'));
        
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
        $nestedData[] = $last_updated_byuser;
        if ($status != 'nomatch') {
          $nestedData[] = '<a href="' . site_url() . '/view-match-request-details?mr_id=' . get_the_ID() . '">View Details</a><br/>
                             <a href="' . site_url() . '/admin-match-request-manage?mr_id=' . get_the_ID() . '">View Match</a>';
        } else {
          $nestedData[] = '<a href="' . site_url() . '/view-match-request-details?mr_id=' . get_the_ID() . '">View Details</a><br/>
                                 <span class="text-disabled">View Match</span>';
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





/**
 * @param int $mr_id
 * Notify requester when the query produced no matches
 * 
 */

 function notfication_nomatch($mr_id) {

  //Trigger along with match request send if no match is displayed
  
  $request_details   = get_post_meta( $mr_id, 'mm365_services_details', true );
  $requester_comp_id = get_post_meta( $mr_id, 'mm365_requester_company_id', true );
  
  //User info
  $user_name  = get_post_meta( $requester_comp_id, 'mm365_contact_person', true );
  $user_email = get_post_meta( $requester_comp_id, 'mm365_company_email', true );

  //Send to all mmsdc_magaer user roles
  $to   = $user_email;
  $link = site_url().'/request-for-match/';
  $subject     = 'No companies matched against your request';

  //Mail Body
  $title       = 'No matches found!';
  $content     = '
              <p>Hi '.$user_name.',</p>
              <p><strong>Match Request Description:</strong></p>
              <p style="font-style:italic;">"'.$request_details.'"</p>
              <p>
              Your match request produced no matches. Please refine your search by selecting as many drop down details 
              as possible and by providing more specific details in the description field.
              </p>
              <p>Please click on the below button to login and edit your match request.</p>
          '; 
  

  //$body        = $this->mm365_email_body($title,$content,$link,'Edit Match Request');
  $body        = $this->mm365_email_body_template($title,$content,$link,'Edit Match Request');
  $headers     = array('Content-Type: text/html; charset=UTF-8');
  wp_mail( $to, $subject, $body, $headers );


}



/**
 * @param int $mr_id
 * Notify customer everytime when the match is approved
 * 
 */

function notfication_matchapproved($mr_id) {

      $request_details  = get_post_meta( $mr_id, 'mm365_services_details', true );
      $approved_matches = maybe_unserialize(get_post_meta( $mr_id, 'mm365_matched_companies', true )); 
      $requester_comp_id = get_post_meta( $mr_id, 'mm365_requester_company_id', true );
      //User name
      $user_name  = get_post_meta( $requester_comp_id, 'mm365_contact_person', true );
      $user_email = get_post_meta( $requester_comp_id, 'mm365_company_email', true );

      $count = 0;
      foreach ($approved_matches as $key => $value) {
          if($value[1] == '1'){
              $count++;
          }
      }
      //Mail Body
      if($count == 1){
          $title       = 'We have found one company matching your request';
          $subject     = 'We have found one company matching your request';
      }else{
          $title       = 'We have found '.$count.' companies matching your request';
          $subject     = 'We have found '.$count.' companies matching your request';
      }

      $link = site_url().'/request-for-match/';
      $content     = '
                  <p>Hi '.$user_name.',</p>
                  <p><strong>Match Request Description:</strong></p>
                  <p style="font-style:italic;">"'.$request_details.'"</p>
                  <p>
                  Your match request is approved by Matchmaker365. Please click on the below button and login to see the details of matched companies.
                  </p>
              '; 
      $to          = $user_email;
      //$body        = $this->mm365_email_body($title,$content,$link,'View Details');
       $body        = $this->mm365_email_body_template($title,$content,$link,'View Details');
      $headers     = array('Content-Type: text/html; charset=UTF-8');
      wp_mail( $to, $subject, $body, $headers );


    }


    /**
     * Match Auto Approval Preference settings
     * List all companies
     * 
     */
    function matchpreference_list_companies(){

      //$mm365_helper = new mm365_helpers();
      //$certificationClass = new mm365_certification;
      
      $council_list = $this->get_councils_list();
      $user = wp_get_current_user();
    
      header("Content-Type: application/json");
      $request = $_POST;
    
      //Council manager check
      $current_user_council_id = $this->get_userDC($user->ID);
    
      //Super admin filtering
      $sa_filtering = FALSE;
      if( $current_user_council_id == '' AND $request['council_filter'] != ''){
        //Override with filtering council id
        $current_user_council_id = $request['council_filter'];
        $sa_filtering = TRUE;
      }
    
    
      $columns = array(
        0 => 'company_name',
        1 => 'contact_info',
        2 => 'type',
        3 => 'approval_status',
        4 => 'approval_toggle',
        
      );
    
      $args = array(
        'post_type'       => 'mm365_companies',
        'post_status'     => 'publish',
        'posts_per_page'  => $request['length'],
        'offset'          => $request['start'],
        // 'orderby'         => 'title',
        'order'           => $request['order'][0]['dir'],
      );
    
    
    
      if($current_user_council_id != ''){
        $council_filter =
          array( 
            array(
              'key'     => 'mm365_company_council',
              'value'   => $current_user_council_id,
              'compare' => '=',
            ),
            'relation' => 'AND'
          );
      }else $council_filter = '';
    
    
    
      if ($request['order'][0]['column'] == 0) {
        //$args['orderby'] = $columns[$request['order'][0]['column']];
        $args['orderby']  =  array( 'title' => $request['order'][0]['dir']);
    
      }elseif ($request['order'][0]['column'] == 1) {
        $args['orderby']  =   array( 'meta_value' => $request['order'][0]['dir'] );
        $args['meta_key'] =   'mm365_service_type';
      }elseif ($request['order'][0]['column'] == 2) {
        $args['orderby']  =   array( 'meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] =   'mm365_approval_required_feature';
      }
    
      //search hack : search for seller if the key word is supplier
      switch ($request['search']['value']) {
        case 'supplier':
          $search_term = 'seller';
        break;
        default:
           $search_term = $request['search']['value'];
        break;
      }
    
     
      $args['meta_query'] = array(
        
        $council_filter,
        array(
          'relation' => 'OR',
          array(
            'key'     => 'mm365_service_type',
            'value'   => sanitize_text_field($search_term),
            'compare' => 'LIKE'
          ),
          array(
            'key'     => 'mm365_approval_required_feature',
            'value'   => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          //$conditional_council_search,
          array(
            'key'     => 'mm365_company_name',
            'value'   => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          )
        )
      );
    
    
    
      //add_filter( 'posts_where', 'mm365_company_title_filter', 10, 2 );
      $companies_query = new \WP_Query($args);
      //remove_filter( 'posts_where', 'mm365_company_title_filter', 10, 2 );
      $totalData = $companies_query->found_posts;
    
     
    
      if ( $companies_query->have_posts() ) {
        while ( $companies_query->have_posts() ) {
          $companies_query->the_post();
          $company_name = get_the_title(get_the_ID());
          $nestedData   = array();
          $approval_req = get_post_meta(get_the_ID(), 'mm365_approval_required_feature', true );
          if($approval_req == ''){ $approval_req = 'enabled'; }
    
          $company_council_id = get_post_meta(get_the_ID(), 'mm365_company_council', true );
    
          $nestedData[] = $this->get_certified_badge(get_the_ID(), true).'<a href="'.site_url().'/view-company?cid='.get_the_ID().'">'.$company_name.'</a>';    
         
          //Contact info
          $nestedData[] = '<div class="intable_span">'.get_post_meta(get_the_ID(), 'mm365_contact_person', true ).'</div>'.
          '<div class="intable_span">'.get_post_meta(get_the_ID(), 'mm365_company_phone', true ).'</div>'.
              '<div class="intable_span">'.get_post_meta(get_the_ID(), 'mm365_company_email', true ).'</div>';
         
          if($current_user_council_id == '' OR  $sa_filtering == TRUE)
          { 
            $nestedData[] = $council_list[$company_council_id][0]; 
          }
               
          $nestedData[] = $this->get_company_service_type(get_post_meta(get_the_ID(), 'mm365_service_type', true ));
          //$nestedData[] = "";
          if($approval_req == 'enabled'): $check_toggle = 'checked'; $show_stat = 'Enabled'; else: $check_toggle = ''; $show_stat = 'Disabled'; endif;
          $nestedData[] = "<div class='stat-approval-group'><span id='stat-".get_the_ID()."' class='approval-required ".$approval_req."'>".ucfirst($show_stat)."</span>".'<div class="md-checkbox md-checkbox-inline">
                              <input id="cb-'.get_the_ID().'" type="checkbox" '.$check_toggle.' class="matchpreference_toggle" "name="match_pref_toggle" value="'.get_the_ID().'">
                              <label for="cb-'.get_the_ID().'"></label>
                          </div></div>';    
          $nestedData[] = '<a href="'.add_query_arg( '_wpnonce', wp_create_nonce( 'matchpref_log' ), site_url().'/match-preference-changelog?cid='.get_the_ID() ).'">View</a>';
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

    /**
     * 
     * 
     * 
     */
    function matchpreference_toggle_status(){

      $company_id    = $_POST['company_id'];
      $state         = get_post_meta( $company_id , 'mm365_approval_required_feature', true ); 
      if($state == ''){ $state = 'disabled'; }
      $state         = ($state == 'disabled') ? 'enabled' : 'disabled';
      $history       = time()."|".$state."|".get_current_user_id();
      update_post_meta( $company_id, 'mm365_approval_required_feature', $state );
      add_post_meta( $company_id, 'mm365_approval_required_feature_history', $history );
      echo $state;
      die();
    
    }


    /**
     * Get the list of suppliers attending a conference
     * for filtering them and approve them against match request 
     * 
     */
    function suppliers_in_conference(){

      $conf_id = sanitize_text_field($_POST['conf_id']);
      $nonce = sanitize_text_field($_POST['nonce']);

      if (!wp_verify_nonce($nonce, 'manage-matchrequest') or !is_user_logged_in()) {
        die();
      }

      echo json_encode($this->get_suppliers_in_conference($conf_id));
      die();
    }


}