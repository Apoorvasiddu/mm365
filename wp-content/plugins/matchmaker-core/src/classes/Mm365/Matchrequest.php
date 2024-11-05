<?php

namespace Mm365;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Create / Edit / Show
 * 
 */

class Matchrequest extends Helpers
{

  use CompaniesAddon;
  use CertificateAddon;
  use MeetingAddon;
  use CountryStateCity;


  function __construct()
  {

    add_action('wp_enqueue_scripts', array($this, 'assets'), 11);

  
    add_action('wp_ajax_match_create', array($this, 'create'));

   
    add_action('wp_ajax_match_update', array($this, 'update'));

  
    add_action('wp_ajax_match_delete', array($this, 'delete'));


    add_action('wp_ajax_match_listing', array($this, 'listing'));

    add_action('wp_ajax_match_unsaved_preview', array($this, 'unsaved_preview'));
    

    add_filter('mm365_matchrequest_show',array($this, 'show'), 1, 2);

    add_filter('mm365_get_approved_matches',array($this, 'get_approved_matches'), 1, 3);

  }

  /**
   * Assets
   * 
   */

  function assets()
  {

    //Tagify localization
    $localize_tagify = array(
      'keyword_count' => apply_filters('mm365_helper_get_themeoption', 'mm365_mrform_keyword_count'),
      'keyword_chars' => apply_filters('mm365_helper_get_themeoption', 'mm365_mrform_keyword_charlimit')
    );
    wp_register_script('mm365_mr_tagify', plugins_url('matchmaker-core/assets/matchrequest_tagify.js'), array('jquery'), false, true);
    wp_localize_script('mm365_mr_tagify', 'mrTag', $localize_tagify);
    wp_enqueue_script('mm365_mr_tagify');


    $localize = array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce("mm365_matchrequest"),
      'current_user' => get_current_user_id(),
      'site_url' => site_url()
    );
    wp_register_script('save_matchmaking', plugins_url('matchmaker-core/assets/save_matchmaking.js'), array('jquery'), false, true);
    wp_localize_script('save_matchmaking', 'matchmakingAjax', $localize);
    wp_enqueue_script('save_matchmaking');


    $localize_list = array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce("mm365_matchrequest_list"),
    );
    wp_register_script('list_matchmaking', plugins_url('matchmaker-core/assets/matchlist_ajax.js'),array('jquery') ,false,true );
    wp_localize_script('list_matchmaking', 'matchlistAjax',$localize_list);
    wp_enqueue_script('list_matchmaking');


  }

  /**
   * Create
   * 
   */

  function create()
  {

    $nonce      = sanitize_text_field($_POST['nonce']);   
    if (!wp_verify_nonce( $nonce, 'mm365_matchrequest' ) OR !is_user_logged_in()) {
        die();
    }

    $searchMode = $_POST['advanced_search'];

    $indstry = array();
    if (isset($_POST['industry'])) {
      $indstry = $_POST['industry'];
      if (in_array('other', $indstry)) {
        $oth_indus = explode(",", $_POST['other_industry']);
      } else
        $oth_indus = "";
    }

    $services = array();
    if (isset($_POST['services'])) {
      $services = $_POST['services'];
      if (in_array('other', $services)) {
        $oth_serv = explode(",", $_POST['other_services']);
      } else
        $oth_serv = "";
    }

    $certification = array();
    if (isset($_POST['certifications'])) {
      $certification = $_POST['certifications'];
      if (in_array('other', $certification)) {
        $oth_cert = explode(",", $_POST['other_certification']);
      } else
        $oth_cert = "";
    }


    //$mm365_company_addons = new mm365_company_addons();
    $s_local = $this->multi_countries_state_display($_POST['service_required_countries'], $_POST['service_required_states']);

    if ($s_local == '-') {
      $s_local = 'Any Country, Any States';
    }

    //Updates Countries and States

    //services looking for
    $keywords = $_POST['services_looking_for'];

    $post_information = array(
      'post_title' => wp_strip_all_tags('MR-' . sanitize_text_field($_POST['current_user']) . "-" . time()),
      'meta_input' => array(
        'mm365_services_details' => sanitize_text_field($keywords),
        'mm365_approval_type' => sanitize_text_field($_POST['approval_required']),
        'mm365_location_for_search' => $s_local,
        'mm365_number_of_employees' => sanitize_text_field($_POST['number_of_employees']),
        'mm365_size_of_company' => sanitize_text_field($_POST['size_of_company']),
        'mm365_requester_id' => sanitize_text_field($_POST['current_user']),
        'mm365_requester_company_id' => sanitize_text_field($_POST['requester_company_id']),
        'mm365_requester_company_name' => get_the_title($_POST['requester_company_id']),
        'mm365_requester_company_council' => sanitize_text_field($_POST['requester_council_id']),
        'mm365_matchrequest_status' => 'nomatch'

      ),
      'post_type' => 'mm365_matchrequests',
      'post_status' => 'draft',
      'post_author' => $_POST['current_user']
    );

    $post_id = wp_insert_post($post_information);
    //Apped updated time
    $modefied_time = get_the_modified_time("m/d/Y h:i A", $post_id);
    $modefied_time_iso = get_the_modified_time("Y-m-d", $post_id);
    update_post_meta($post_id, 'mm365_matched_companies_last_updated', $modefied_time);
    update_post_meta($post_id, 'mm365_matched_companies_last_updated_isodate', $modefied_time_iso);



    //Where you need services
    //'mm365_service_needed_country'
    //'mm365_service_needed_state'

    /**
     * TODO: Add 'all_<country>_states' option if no state is selected for a chosen country
     */
    $sorted_locations = $this->countries_states_sorter($_POST['service_required_countries'], $_POST['service_required_states']);
    if ($sorted_locations != NULL) {
      delete_post_meta($post_id, 'mm365_service_needed_country');
      foreach ($sorted_locations['countries'] as $country) {
        add_post_meta($post_id, 'mm365_service_needed_country', $country);
      }
      delete_post_meta($post_id, 'mm365_service_needed_state');
      foreach ($sorted_locations['states'] as $state) {
        add_post_meta($post_id, 'mm365_service_needed_state', $state);
      }
    }



    //Loop and save NAICS
    $new_naics = array_filter($_POST['naics_codes'], array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_naics_codes');
    foreach ($new_naics as $naic) {
      add_post_meta($post_id, 'mm365_naics_codes', $naic);
    }

    $new_certification = array_filter($certification, array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_certifications');
    foreach ($new_certification as $data) {
      add_post_meta($post_id, 'mm365_certifications', $data);
    }
    if (!empty($oth_cert)) {
      foreach ($oth_cert as $data) {
        if ($data != 'other')
          add_post_meta($post_id, 'mm365_certifications', $data);
      }
    }


    $new_services = array_filter($services, array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_services_looking_for');
    foreach ($new_services as $data) {
      add_post_meta($post_id, 'mm365_services_looking_for', $data);
    }
    if (!empty($oth_serv)) {
      foreach ($oth_serv as $data) {
        if ($data != 'other')
          add_post_meta($post_id, 'mm365_services_looking_for', $data);
      }
    }

    $new_industry = array_filter($indstry, array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_services_industry');
    foreach ($new_industry as $data) {
      add_post_meta($post_id, 'mm365_services_industry', $data);
    }
    if (!empty($oth_indus)) {
      foreach ($oth_indus as $data) {
        if ($data != 'other')
          add_post_meta($post_id, 'mm365_services_industry', $data);
      }
    }

    if (isset($_POST['mr_mbe_category']) and !empty($_POST['mr_mbe_category'])):
      $new_mbecat = array_filter($_POST['mr_mbe_category'], array($this,"purge_empty"));
      delete_post_meta($post_id, 'mm365_mr_mbe_category');
      foreach ($new_mbecat as $data) {
        add_post_meta($post_id, 'mm365_mr_mbe_category', $data);
      }
    endif;

    if (isset($_POST['looking_for']) and !empty($_POST['looking_for'])):
      $intassi_looking_for = array_filter($_POST['looking_for'], array($this,"purge_empty"));
      delete_post_meta($post_id, 'mm365_match_intassi_lookingfor');
      foreach ($intassi_looking_for as $data) {
        add_post_meta($post_id, 'mm365_match_intassi_lookingfor', $data);
      }
    endif;

    if ($searchMode == 'false') {
      $publish_url = site_url() . '/publish-match-requests?mr_id=' . $post_id;
      echo wp_nonce_url($publish_url, 'match_request');
    } else {
      echo $this->show($post_id);
    }

    wp_die();

  }


  /**
   * Update
   * 
   */

  function update()
  {

    $user = wp_get_current_user();
    $auth = get_post($_POST['mr_id']); // gets author from post
    $authid = $auth->post_author; // gets author id for the post

    $nonce      = sanitize_text_field($_POST['nonce']);   
    if (!wp_verify_nonce( $nonce, 'mm365_matchrequest' ) OR !is_user_logged_in() OR ($user->ID != $authid)) {
       echo 'Invalid';
        die();
    }


    $searchMode = $_POST['advanced_search'];

    $indstry = array();
    if (isset($_POST['industry'])) {
      $indstry = $_POST['industry'];
      if (in_array('other', $indstry)) {
        $oth_indus = explode(",", $_POST['other_industry']);
      } else
        $oth_indus = "";
    }

    $services = array();
    if (isset($_POST['services'])) {
      $services = $_POST['services'];
      if (in_array('other', $services)) {
        $oth_serv = explode(",", $_POST['other_services']);
      } else
        $oth_serv = "";
    }

    $certification = array();
    if (isset($_POST['certifications'])) {
      $certification = $_POST['certifications'];
      if (in_array('other', $certification)) {
        $oth_cert = explode(",", $_POST['other_certification']);
      } else
        $oth_cert = "";
    }

    //Fecthing names and adding them to meta for search related purposes
    if(!empty($_POST['service_required_countries'][0])){
    $s_local = $this->multi_countries_state_display($_POST['service_required_countries'], $_POST['service_required_states']);
    }else {$s_local = null;}

   
    if ($s_local == '-' OR $s_local == null OR $s_local == '') {
      $s_local = 'Any Country, Any States';
    }

   

    $keywords = $_POST['services_looking_for'];

    $post_information = array(
      'meta_input' => array(
        'mm365_services_details' => sanitize_text_field($keywords),
        'mm365_location_for_search' => $s_local,
        'mm365_number_of_employees' => sanitize_text_field($_POST['number_of_employees']),
        'mm365_size_of_company' => sanitize_text_field($_POST['size_of_company']),
        'mm365_approval_type' => sanitize_text_field($_POST['approval_required']),

      ),
      'post_type' => 'mm365_matchrequests',
      'ID' => $_POST['mr_id']
    );

    $post_id = wp_update_post($post_information);
    //Apped updated time
    $modefied_time = get_the_modified_time("m/d/Y h:i A", $post_id);
    $modefied_time_iso = get_the_modified_time("Y-m-d", $post_id);
    update_post_meta($post_id, 'mm365_matched_companies_last_updated', $modefied_time);
    update_post_meta($post_id, 'mm365_matched_companies_last_updated_isodate', $modefied_time_iso);


    $new_naics = array_filter($_POST['naics_codes'], array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_naics_codes');
    foreach ($new_naics as $naic) {
      add_post_meta($post_id, 'mm365_naics_codes', $naic);
    }

    $new_certification = array_filter($certification, array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_certifications');
    foreach ($new_certification as $data) {
      add_post_meta($post_id, 'mm365_certifications', $data);
    }
    if (!empty($oth_cert)) {
      foreach ($oth_cert as $data) {
        if ($data != 'other')
          add_post_meta($post_id, 'mm365_certifications', $data);
      }
    }

    $new_services = array_filter($services, array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_services_looking_for');
    foreach ($new_services as $data) {
      add_post_meta($post_id, 'mm365_services_looking_for', $data);
    }
    if (!empty($oth_serv)) {
      foreach ($oth_serv as $data) {
        if ($data != 'other')
          add_post_meta($post_id, 'mm365_services_looking_for', $data);
      }
    }

    $new_industry = array_filter($indstry, array($this,"purge_empty"));
    delete_post_meta($post_id, 'mm365_services_industry');
    foreach ($new_industry as $data) {
      add_post_meta($post_id, 'mm365_services_industry', $data);
    }
    if (!empty($oth_indus)) {
      foreach ($oth_indus as $data) {
        if ($data != 'other')
          add_post_meta($post_id, 'mm365_services_industry', $data);
      }
    }
    if (isset($_POST['mr_mbe_category']) and !empty($_POST['mr_mbe_category'])):
      $new_mbecat = array_filter($_POST['mr_mbe_category'], array($this,"purge_empty"));
      delete_post_meta($post_id, 'mm365_mr_mbe_category');
      foreach ($new_mbecat as $data) {
        add_post_meta($post_id, 'mm365_mr_mbe_category', $data);
      }
    endif;


    delete_post_meta($post_id, 'mm365_match_intassi_lookingfor');
    if (isset($_POST['looking_for'])):
      $intassi_looking_for = array_filter($_POST['looking_for'], array($this,"purge_empty"));
      foreach ($intassi_looking_for as $data) {
        add_post_meta($post_id, 'mm365_match_intassi_lookingfor', $data);
      }
    endif;


    delete_post_meta($post_id, 'mm365_service_needed_country');
    delete_post_meta($post_id, 'mm365_service_needed_state');
    if (!empty($_POST['service_required_countries'][0]) AND $s_local != 'Any Country, Any States') {
      $sorted_locations = $this->countries_states_sorter($_POST['service_required_countries'], $_POST['service_required_states']);
      //Countries
      
      foreach ($sorted_locations['countries'] as $country) {
        add_post_meta($post_id, 'mm365_service_needed_country', $country);
      }
      //States
      
      foreach ($sorted_locations['states'] as $state) {
        add_post_meta($post_id, 'mm365_service_needed_state', $state);
      }
    }


    //conditionally show preview or fire redirect URL

    if ($searchMode == 'false') {
      $publish_url = site_url() . '/publish-match-requests?mr_id=' . $post_id;
      echo wp_nonce_url($publish_url, 'match_request');
    } else {
      echo $this->show($post_id);
    }
  

    wp_die();


  }

  /**
   * Delete
   * 
   * 
   */
  function delete()
  {

    $mr_id = sanitize_text_field($_POST['mr_id']);
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'mm365_matchrequest')) {
      die();
    }

    if (wp_delete_post($mr_id, true)) {
      return true;
    } else
      return false;

  }

  /**
   * Show
   * @param int $mr_id
   */
  function show($mr_id, $def_stat = NULL)
  {

    $args = array(
      'p' => $mr_id,
      'post_type' => 'mm365_matchrequests',
      'posts_per_page' => 1,
      'orderby' => 'date',
    );
    $loop = new \WP_Query($args);

    while ($loop->have_posts()):
      $loop->the_post();

      $approval_time_flag = get_post_meta(get_the_ID(), 'mm365_matched_companies_approved_time', true);
      $match_status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);

      switch ($match_status) {
        case 'nomatch':
          $mr_stat_display = "<span class='" . $match_status . "'>No Match</span>";
          break;
        case 'auto-approved':
          $mr_stat_display = "<span class='" . $match_status . "'>Auto Approved</span>";
          break;
        default:
          $mr_stat_display = "<span class='" . $match_status . "'>" . ucfirst($match_status) . "</span>";
          break;
      }
      ?>

      <div class="row pbo-20">
        <div class="col-md-6">
          <a href="<?php echo site_url(); ?>/request-for-match#mr" class="">
            <h3 class='heading-large'><img class="back-arrow"
                src="<?php echo get_template_directory_uri() ?>/assets/images/arrow-left.svg" height="36px" alt="">&nbsp;Match
              Request Details</h3>
          </a>

        </div>
        <div class="col-md-6 text-right">
          <?php if (in_array($match_status, array('approved', 'auto-approved'))): ?>
            <a data-intro="Cancel the match request, if you are not satisfied with the results" data-step="3"
              href="<?php echo add_query_arg('_wpnonce', wp_create_nonce('close_matchrequest'), site_url() . '/close-match?mr_id=' . get_the_ID() . '&act=cancel'); ?>"
              class="btn btn-primary red">Cancel Match Request</a>&nbsp;
            <a data-intro="Complete the match request if you have successfully established a business with the supplier"
              data-step="4"
              href="<?php echo add_query_arg('_wpnonce', wp_create_nonce('close_matchrequest'), site_url() . '/close-match?mr_id=' . get_the_ID() . '&act=complete'); ?>"
              class="btn btn-primary green">Complete Match Request</a>&nbsp;
          <?php endif; ?>
        </div>
      </div>

      <section class="matchrequest-short-details" data-intro="Details of the match request including advanced paramters">
        <table class="table">
          <tbody>
            <tr>
              <td width="28%">Requested date & time</td>
              <td>
                <?php echo get_the_modified_time("m/d/Y h:i A"); ?>
              </td>
            </tr>
            <tr>
              <td>NAICS code</td>
              <td>
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
              </td>
            </tr>
            <tr>
              <td>Details of services or products you are looking for</td>
              <td>
                <?php echo get_post_meta(get_the_ID(), 'mm365_services_details', true); ?>
              </td>
            </tr>
            <tr>
              <td>Services or products required</td>
              <td>
                <?php
                $show_services = implode(', ', (get_post_meta(get_the_ID(), 'mm365_services_looking_for')));
                echo $show_services ?: '-';
                ?>
              </td>
            </tr>
            <tr>
              <td>Size of company (Annual sales in $USD)</td>
              <td>
                <?php
                echo (get_post_meta(get_the_ID(), 'mm365_size_of_company', true)) ?: '-';
                ?>
              </td>
            </tr>
            <tr>
              <td>Number of employees</td>
              <td>
                <?php
                echo (get_post_meta(get_the_ID(), 'mm365_number_of_employees', true)) ?: '-';
                ?>
              </td>
            </tr>
            <tr>
              <td>Industry</td>
              <td>
                <?php
                $show_industry = implode(', ', (get_post_meta(get_the_ID(), 'mm365_services_industry')));
                echo $show_industry ?: '-';

                ?>
              </td>
            </tr>
            <tr>
              <td>Minority classification</td>
              <td>
                <?php
                $minority_categories = (get_post_meta(get_the_ID(), 'mm365_mr_mbe_category'));
                if (!empty($minority_categories)) {

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
              </td>
            </tr>


            <tr>
              <td>Location where the services or products are needed</td>
              <td>
                <?php
                $allow = array('br' => array());
                echo wp_kses(get_post_meta(get_the_ID(), 'mm365_location_for_search', true), $allow)
                ?>
              </td>
            </tr>
      

            <tr>
              <td>Industry Certifications</td>
              <td>
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
              </td>
            </tr>

            
            <tr>
              <td>Looking for international assistance</td>
              <td>
                <?php

                $int_assi = get_post_meta(get_the_ID(), 'mm365_match_intassi_lookingfor');
                if (!empty($int_assi)) {
                  $int_assi_looking = implode(', ', $int_assi);
                } else {
                  $int_assi_looking = '';
                }

                echo ($int_assi_looking) ?: '-';

                ?>
              </td>
            </tr>

            <!-- Hide for draft state match requests to avoid confusion in preview -->
            <?php if (get_post_status(get_the_ID()) == 'publish'): ?>
              <tr class="matchrequests-list disable-shadow">
                <td>Match request status</td>
                <td>
                  <?php echo $mr_stat_display ?>
                </td>
              </tr>
            <?php endif; ?>

            <?php if ($match_status == 'closed'): ?>
              <tr>
                <td>Reason for closure</td>
                <td>
                  <?php
                  echo esc_html(get_post_meta(get_the_ID(), 'mm365_reason_for_closure_filter', true));
                  ?>
                </td>
              </tr>
              <tr>
                <td>Message</td>
                <td>
                  <?php echo esc_html(get_post_meta(get_the_ID(), 'mm365_reason_for_closure', true)); ?>
                </td>
              </tr>
            <?php endif; ?>

          </tbody>
        </table>
      </section>

      <?php if ($def_stat != 'publish'): ?>
        <div class="row">
          <div class="col-md-12 text-right">
            <?php
            $url = site_url() . '/edit-matchrequest?mr_id=' . get_the_ID();
            $publish_url = site_url() . '/publish-match-requests?mr_id=' . get_the_ID();
            ?>
            <a href="<?php echo wp_nonce_url($url, 'match_request'); ?>" class="btn btn-primary">Edit</a>
            <a href="<?php echo wp_nonce_url($publish_url, 'match_request'); ?>"
              class="btn btn-primary start-matching">Submit</a>
          </div>
        </div>
      <?php elseif ($def_stat == 'publish' and $approval_time_flag == ''): ?>
        <div class="row mbo-30">
          <div class="col-md-12 text-right">
            <?php
            $url = site_url() . '/edit-matchrequest?mr_id=' . get_the_ID() . "&mr_state=active";
            ?>
            <a href="<?php echo wp_nonce_url($url, 'match_request'); ?>" class="btn btn-primary active-edit-match">Edit</a>
          </div>
        </div>
      <?php endif; ?>
      

      <?php
    endwhile;
    wp_reset_postdata();


  }

  /**
   * Active preview renamed
   * 
   * 
   */
  function unsaved_preview(){
    $args = array(  
        'p' => $_POST['mr_id'],
        'post_type' => 'mm365_matchrequests',
        //'post_status' => $def_stat,
        'posts_per_page' => 1, 
        'orderby' => 'date', 
    );

 

    $loop = new \WP_Query( $args );  
    while ( $loop->have_posts() ) : $loop->the_post(); 
    $approval_time_flag =  get_post_meta( get_the_ID(), 'mm365_matched_companies_approved_time', true );

    ?>
      
      <section class="matchrequest-short-details"  data-intro="Details of the match request including advanced paramters">
          <table class="table">
            <tbody>
                  <tr>
                    <td width="25%">Last Requested date & time</td><td><?php echo get_the_modified_time("m/d/Y h:i A"); ?></td>
                  </tr>
                  <tr>
                    <td >Details of services or products you are looking for</td>
                    <td>
                      <?php 
                        echo  esc_html($_POST['services_looking_for']); 
                      ?>
                    </td>
                 </tr>       
                  <tr>
                    <td>Services or products required</td><td>  
                    <?php 
                    if(isset($_POST['services'])){ 
                        $show_services =  implode(', ', ($_POST['services'])); 
                        if(in_array('other',$_POST['services'])){
                          $show_services .=  ", ".$_POST['other_services'];
                        }
                    }else { $show_services =''; }

                    echo $show_services ?? '-';
                    ?></td>
                  </tr>
                  <tr>
                    <td>Size of company (Annual sales in $USD)</td>
                    <td> <?php echo $_POST['size_of_company'] ?? '-'; ?></td>
                 </tr>   
                 <tr>
                    <td >Number of employees</td>
                    <td> <?php echo $_POST['number_of_employees'] ?? '-'; ?></td>
                 </tr>
                  <tr>
                    <td>Industry</td>
                    <td>
                    <?php
                    if(isset($_POST['industry'])){ 
                      $show_industry = implode(', ', ($_POST['industry']));
                      if(in_array('other',$_POST['industry'])){
                        $show_industry .=  ", ".$_POST['other_industry'];
                      }
                   }else { $show_industry = ''; }
                    echo $show_industry ?? '-';                     
                     ?>
                     </td>
                  </tr>
                  <tr>
                    <td>Minority classification</td><td>
                      <?php 
                          $minority_categories = ($_POST['mr_mbe_category']);
                          if(!empty($minority_categories)){
                            $cnt = 0;
                            foreach ($minority_categories as $key => $value) {
                              echo $this->expand_minoritycode($value);
                              $cnt++;
                              if(count($minority_categories) > $cnt ) {echo ", ";}                    
                            }
                          }else{ echo "-"; }
                      ?>
                   </td>
                  </tr>

                  <tr>
                    <td >Location where the services or products are needed</td><td> 
                    <?php  
                    
                    $services_needed_countries = $_POST['service_required_countries'];
                    $services_needed_states    = $_POST['service_required_states'];
                    if(!empty($_REQUEST['service_required_countries'][0])){
                    
                      echo $this->multi_countries_state_display($services_needed_countries, $services_needed_states );
                    }
                    ?></td>
                  </tr>

                 <tr>
                    <td >Industry Certifications</td><td> <?php 
                    if(isset($_POST['certifications'])){ 
                      $show_certifications = implode(', ',$_POST['certifications']);
                         if(in_array('other',$_POST['certifications'])){
                            $show_certifications .=  ", ".$_POST['other_certification'];
                         }
                     }
                    else{ $show_certifications = ''; }  
                      echo $show_certifications ?? '-';  
                 ?></td>
                 </tr>  
                 <tr>
                    <td >NAICS code</td><td>  <?php   
                    if(isset($_POST['naics_codes'])){   
                      $purged = array_filter($_POST['naics_codes'],array($this,"purge_empty"));
                      $show_naics = implode(', ', $purged);
                    }else { $show_naics =''; } 
                    echo $show_naics ?? '-';
                  
               ?></td>
                 </tr>

                 <tr>
                    <td >Looking for international assistance</td>
                    <td> 
                        <?php 
                        if(isset($_POST['looking_for'])){ 
                          $int_assi_looking = implode(', ',$_POST['looking_for']);                        
                        }
                        else{ $int_assi_looking = ''; }  
                        echo $int_assi_looking ?? '-';  
                    ?>
                    </td>
                 </tr>
                      
            </tbody>
          </table>
        </section>   


         <div class="row">
           <div class="col-md-12 text-right">
           <?php
            $url = site_url().'/edit-matchrequest?mr_id='.get_the_ID()."&mr_state=active";
            $publish_url = site_url().'/publish-match-requests?mr_id='.get_the_ID();
           ?>
           <a href="<?php  echo wp_nonce_url( $url, 'match_request' ); ?>" class="btn btn-primary active-preview-edit">Edit</a> 
           <a href="#" data-publishurl="<?php  echo wp_nonce_url( $publish_url, 'match_request' ); ?>" class="btn btn-primary start-matching-again">Submit</a>
           </div>
         </div>
     
     
    <?php
    endwhile;
    wp_reset_postdata();
    die(); 

}

  /**
   * For Table
   * 
   * 
   */
  function listing()
  {

    header("Content-Type: application/json");

    $request = $_GET;

    $columns = array(
      0 => 'looking_for',
      1 => 'requested_date_and_time',
      2 => 'location',
      3 => 'status',
      4 => 'request',
      5 => 'match',
    );


    $args = array(
      'post_type' => 'mm365_matchrequests',
      'author' => get_current_user_id(),
      'post_status' => 'publish',
      'posts_per_page' => $_REQUEST['length'],
      'offset' => $_REQUEST['start'],
      'order' => $_REQUEST['order'][0]['dir'],
      'meta_query' => array(
        array(
          'key' => 'mm365_requester_company_id',
          'value' => $_COOKIE['active_company_id'],
          'compare' => '='
        ),
        'relation' => 'AND'
      )
    );

    if ($request['order'][0]['column'] == 0) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_services_details';
    } elseif ($request['order'][0]['column'] == 1) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => $request['order'][0]['dir']);
      //$args['meta_key'] =   'mm365_matched_companies_last_updated';
    } elseif ($request['order'][0]['column'] == 2) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_location_for_search';
    } elseif ($request['order'][0]['column'] == 3) {
      $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
      $args['meta_key'] = 'mm365_matchrequest_status';
    }

    if (!empty($request['search']['value'])) { // When datatables search is used
      $args['orderby'] = array('modified' => 'DESC');


      if ($request['order'][0]['column'] == 0) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
        $args['meta_key'] = 'mm365_services_details';
      } elseif ($request['order'][0]['column'] == 1) {
        $args['orderby'] = 'modified';
      } elseif ($request['order'][0]['column'] == 2) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
        $args['meta_key'] = 'mm365_location_for_search';
      } elseif ($request['order'][0]['column'] == 3) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir'], 'modified' => 'DESC');
        $args['meta_key'] = 'mm365_matchrequest_status';
      }


      $args['meta_query'] = array(
        array(
          'key' => 'mm365_requester_company_id',
          'value' => $_COOKIE['active_company_id'],
          'compare' => '='
        ),
        'relation' => 'AND',
        array(
          'relation' => 'OR',
          array(
            'key' => 'mm365_services_details',
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

        $service_country = get_post_meta(get_the_ID(), 'mm365_service_country', true);
        $service_state = get_post_meta(get_the_ID(), 'mm365_service_state', true);
        $service_city = get_post_meta(get_the_ID(), 'mm365_service_city', true);
        $status = get_post_meta(get_the_ID(), 'mm365_matchrequest_status', true);

        $last_updated_byuser = get_post_meta(get_the_ID(), 'mm365_matched_companies_last_updated', true);
        $nestedData = array();
        $nestedData[] = get_post_meta(get_the_ID(), 'mm365_services_details', true);
        $nestedData[] = implode(", ",get_post_meta(get_the_ID(),'mm365_naics_codes'));
        $nestedData[] = $last_updated_byuser;
        //Location Display
        $nestedData[] = get_post_meta(get_the_ID(), 'mm365_location_for_search', true);

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
        if (in_array($status, array('approved', 'auto-approved', 'completed', 'cancelled'))) {
          $nestedData[] = '<a href="' . site_url() . '/view-match?mr_id=' . get_the_ID() . '">View Match</a>';
        } else
          $nestedData[] = '<span class="text-disabled">View Match</span>';


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
   * Approved companies list for requester
   * 
   */
  function get_approved_matches($cids = array(),$mode = 'user',$mr_id = null){
 
    $councils_list = $this->get_councils_list();
  
      foreach($cids as $key){
        //Key is company_id
        ?>
          <tr>
            <td><strong><?php 
            $this->get_certified_badge($key);
            echo "<a href='".site_url()."/view-company?cid=".$key."&mr_id=".$mr_id."'>".get_the_title($key)."</a>"; ?></strong><br/>
            <small><?php echo $this->get_cityname(get_post_meta($key,'mm365_company_city',true)); ?>,
            <?php echo $this->get_countryname(get_post_meta($key,'mm365_company_country',true)); ?></small>
            </td>
            <td>
            <?php 
           
              $cmp_counil_id = get_post_meta($key,'mm365_company_council',true); 
              esc_html_e($this->get_council_info($cmp_counil_id)); 
            ?>
            </td>
            <td>
            <?php 
            //$services_provided_countries = get_post_meta($key, 'mm365_cmp_serviceable_countries');
            //$services_provided_states    = get_post_meta($key, 'mm365_cmp_serviceable_states');
            //echo $this->multi_countries_state_display($services_provided_countries, $services_provided_states );
            echo "<div class='naics_in_table'>".implode(", ",get_post_meta($key, 'mm365_naics_codes'))."</div>";
            
            ?></td>
            <td>
              <?php
                $company_info = get_post_meta($key,'mm365_company_description', true); 
                // To get full company description to search
                // echo (strlen($company_info) > 250) ? substr(wp_strip_all_tags($company_info),0,250)."..." : $company_info;
                
                $truncated_info = (strlen($company_info) > 250) ? substr(wp_strip_all_tags($company_info), 0, 250) . "..." : $company_info;
                // Include the full description in a hidden span for searching
                echo "<span class='visible-description'>" . $truncated_info . "</span>";
                echo "<span class='hidden-full-description' style='display:none;'>" . wp_strip_all_tags($company_info) . "</span>";
              
              ?>
            </td>
            <?php 
                   $current_meeting_status = $this->meeting_status($key,$mr_id,true,false);
                   $current_match_status   = get_post_meta( $mr_id, 'mm365_matchrequest_status', true);
  
                   if(isset($current_meeting_status['mid']) AND $current_meeting_status['mid'] != '' AND in_array($current_meeting_status['status'],array("declined","meeting_declined","cancelled"))){ 
                     $meeting_url = site_url()."/schedule-meeting?cid=".$key."&mr_id=".$mr_id; 
  
                    //Show only if match request is not 'closed'
                    if(!in_array($current_match_status, array('completed','cancelled'))):
                      $link = "<a class='meeting_status' href='".add_query_arg( '_wpnonce', wp_create_nonce( 'schedule_meeting' ), $meeting_url )."'>Schedule new Meeting</a>";  
                    else:
                        $link = "";
                    endif;
                     
                     $show_status = "<span class='meeting_status ".$current_meeting_status['status']."'>".preg_replace('/\_+/', ' ', $current_meeting_status['status'])."</span>";
  
                  }elseif(isset($current_meeting_status['mid']) AND $current_meeting_status['mid'] != '' AND in_array($current_meeting_status['status'],array("accepted","proposed","proposed_new_time","scheduled","rescheduled"))){
                    $meeting_url = site_url()."/meeting-details?mid=".$current_meeting_status['mid']; 
                    $link = "<a class='meeting_status ".$current_meeting_status['status']."' href='".add_query_arg( '_wpnonce', wp_create_nonce( 'schedule_meeting' ), $meeting_url )."'>".preg_replace('/\_+/', ' ', $current_meeting_status['status'])."</a>"; 
                    $show_status = "";
                  }else{
                    $meeting_url = site_url()."/schedule-meeting?cid=".$key."&mr_id=".$mr_id; 
                    if(!in_array($current_match_status, array('completed','cancelled'))):
                      $link = "<a class='meeting_status schedule_meeting_button' href='".add_query_arg( '_wpnonce', wp_create_nonce( 'schedule_meeting' ), $meeting_url )."'>Schedule Meeting</a>";  
                    else:
                      $link = "-";
                    endif;
                    $show_status = ""; 
                  }
            ?>
            <td class="text-capitalize"><?php 
            if($show_status != '') echo $show_status."<br/>";
            echo $link;
            ?></td>
            <td class="text-capitalize"><?php  echo  "<a href='".site_url()."/view-company?cid=".$key."&mr_id=".$mr_id."'>Company Details</a>";  ?></td>
          </tr>
          <?php
      }
  
  }
  
  


}