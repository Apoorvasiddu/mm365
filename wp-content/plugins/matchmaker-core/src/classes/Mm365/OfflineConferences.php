<?php
namespace Mm365;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class OfflineConferences
{
    use TimezoneAddon;
    use CouncilAddons;
    use OfflineConferencesAddon;
    use NotificationAddon;
    use MatchrequestAddon;

    function __construct()
    {

        //Load assets
        add_action('wp_enqueue_scripts', array($this, 'assets'), 11);
        //Search buyer companies
        add_action('wp_ajax_get_council_buyer_companies', array($this, 'get_council_buyer_companies'));
        //Search fellow council manager
        add_action('wp_ajax_get_fellow_council_manager', array($this, 'get_fellow_council_manager'));
        add_action('wp_ajax_get_existing_buyers_in_conference', array($this, 'get_existing_buyers_in_conference'));
        add_action('wp_ajax_get_existing_councilmanagers_in_conference', array($this, 'get_existing_councilmanagers_in_conference'));
        add_action('wp_ajax_council_create_offline_conference', array($this, 'create_offline_conference'));
        add_action('wp_ajax_council_update_offline_conference', array($this, 'update_offline_conference'));
        add_action('wp_ajax_publish_conference', array($this, 'publish_conference'));
        add_action('wp_ajax_apply_offline_conference_particiaption', array($this, 'apply_offline_conference_particiaption'));
        add_action('wp_ajax_process_particiaption_request', array( $this, 'process_particiaption_request' ) );

        add_filter('mm365_offline_conferences_list', array($this, 'list'), 11, 3);
        add_filter('mm365_offline_conference_get_deligates_count', array($this, 'get_deligates_total_count'), 10, 2);
        add_filter('mm365_offline_conferences_show', array($this, 'show_conference_details'), 11, 2);
        add_filter('mm365_offline_conferences_applications_received', array($this, 'conference_applications_list'), 10, 1);
        add_filter('mm365_offline_conferences_get_application_status', array($this, 'get_application_status'), 10, 2);

        add_action('mm365_offline_conferences_export_applicants_list', array($this, 'export_applicants_list'), 10, 1);

        add_filter('mm365_offline_get_suppliers_in_conference', array($this, 'get_suppliers_in_conference'), 10, 1);

        //
        add_filter('wp_ajax_offline_conferences_fordropdown', array($this, 'offline_conference_fordropdown'), 11, 0);
        

    }



    /**
     * Assets
     * 
     * 
     */
    function assets()
    {
        if (wp_register_script('mm365_conf_council', plugins_url('matchmaker-core/assets/confmodule/conf-council.js'), array('jquery'), false, TRUE)) {
            wp_enqueue_script('mm365_conf_council');
            wp_localize_script(
                'mm365_conf_council',
                'confCouncilAjax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce("confcouncil_ajax_nonce")
                )
            );
        }

        if (wp_register_script('mm365_conf_shared', plugins_url('matchmaker-core/assets/confmodule/conf-shared.js'), array('jquery'), false, TRUE)) {
            wp_enqueue_script('mm365_conf_shared');
            wp_localize_script('mm365_conf_shared', 'confSharedAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce("confshared_ajax_nonce")
            )
            );
        }
    }


    /**
     * Create conference
     * Created by council manager
     */

    function create_offline_conference()
    {

        //Collect inputs
        $conf_title = sanitize_text_field($_POST['conference_title']);
        $conf_description = wp_kses_post($_POST['conference_description']);
        $conf_business_value = sanitize_text_field($_POST['business_value']);
        $conf_keywords = sanitize_text_field($_POST['keywords']);

        //Date
        $conf_conference_date = sanitize_text_field($_POST['conference_date']);
        $conf_conference_starttime = sanitize_text_field($_POST['conference_starttime']);
        $conf_conference_endtime = sanitize_text_field($_POST['conference_endtime']);
        $conf_timezone = sanitize_text_field($_POST['timezone']);
        $start_timestamp = $this->make_timestamp_with_timezone($conf_conference_date . ' ' . $conf_conference_starttime, $conf_timezone);
        $end_timestamp = $this->make_timestamp_with_timezone($conf_conference_date . ' ' . $conf_conference_endtime, $conf_timezone);

        $conf_meeting_venue = sanitize_text_field($_POST['meeting_venue']);
        $conf_map_link = sanitize_text_field($_POST['map_link']);
        $conf_event_amneties = sanitize_text_field($_POST['event_amneties']);
        $conf_maximum_deligates = 800; //sanitize_text_field($_POST['maximum_deligates']);
        $conf_primary_contact_person = sanitize_text_field($_POST['primary_contact_person']);
        $conf_contact_phone_number = sanitize_text_field($_POST['contact_phone_number']);

        $conf_registration_closing_date = sanitize_text_field($_POST['registration_closing_date']);

        $conf_participating_buyers = $_POST['participating_buyers'];
        $conf_fellow_council_managers = $_POST['fellow_council_managers'];

        $conf_scope = sanitize_text_field($_POST['conf_scope']);


        //Create post
        $data = array(
            'post_type' => 'mm365_conferences',
            'post_title' => $conf_title,
            'post_status' => 'draft',
            'post_content' => $conf_description
        );

        $post_id = wp_insert_post($data);
        if (is_numeric($post_id)) {

            update_post_meta($post_id, 'conf_type', 'offline');

            //Time
            update_post_meta($post_id, 'conf_start_timestamp', $start_timestamp);
            update_post_meta($post_id, 'conf_end_timestamp', $end_timestamp);
            update_post_meta($post_id, 'conf_date', $conf_conference_date);
            update_post_meta($post_id, 'conf_start_time', $conf_conference_starttime);
            update_post_meta($post_id, 'conf_end_time', $conf_conference_endtime);
            update_post_meta($post_id, 'conf_timezone', $conf_timezone);
            update_post_meta($post_id, 'conf_date_iso', date("Y-m-d", strtotime($conf_conference_date)));

            update_post_meta($post_id, 'conf_business_value', $conf_business_value);
            update_post_meta($post_id, 'conf_keywords', $conf_keywords);
            update_post_meta($post_id, 'conf_venue', $conf_meeting_venue);
            update_post_meta($post_id, 'conf_map_link', $conf_map_link);
            update_post_meta($post_id, 'conf_event_amneties', $conf_event_amneties);
            update_post_meta($post_id, 'conf_maximum_deligates', $conf_maximum_deligates);
            update_post_meta($post_id, 'conf_registration_closing_date', $conf_registration_closing_date);


            //Buyers
            foreach ($conf_participating_buyers as $company_id) {
                add_post_meta($post_id, 'conf_buyers', $company_id);
            }

            //Organizers
            foreach ($conf_fellow_council_managers as $company_id) {
                add_post_meta($post_id, 'conf_council_managers', $company_id);
            }

            update_post_meta($post_id, 'conf_primary_contact_person', $conf_primary_contact_person);
            update_post_meta($post_id, 'conf_contact_phone_number', $conf_contact_phone_number);

             // MY CODE
            $new_naics = isset($_POST['naics_codes']) ? array_filter($_POST['naics_codes']) : [];
            delete_post_meta($post_id, 'conf_naics_codes');

            if (!empty($new_naics)) {
                foreach ($new_naics as $naic) {
                    add_post_meta($post_id, 'conf_naics_codes', $naic);
                }
            }

            //Organizer

            $cm_council = $this->get_userDC(get_current_user_id());
            $council_name = get_post_meta($cm_council, 'mm365_council_shortname', true);
            update_post_meta($post_id, 'conf_organizer', $council_name);
            update_post_meta($post_id, 'conf_organized_council_id', $cm_council);

            //Council level or national level
            update_post_meta($post_id, 'conf_scope', $conf_scope);

            $this->show_conference_details($post_id);

            //echo 'success';

        }
        wp_die();


    }


    /**
     * Edit conference
     * 
     * 
     */
    function update_offline_conference()
    {

        //Collect inputs
        $conf_id = sanitize_text_field($_POST['update_conf_id']);
        $conf_title = sanitize_text_field($_POST['conference_title']);
        $conf_description = wp_kses_post($_POST['conference_description']);
        $conf_business_value = sanitize_text_field($_POST['business_value']);
        $conf_keywords = sanitize_text_field($_POST['keywords']);

        //Date
        $conf_conference_date = sanitize_text_field($_POST['conference_date']);
        $conf_conference_starttime = sanitize_text_field($_POST['conference_starttime']);
        $conf_conference_endtime = sanitize_text_field($_POST['conference_endtime']);
        $conf_timezone = sanitize_text_field($_POST['timezone']);
        $start_timestamp = $this->make_timestamp_with_timezone($conf_conference_date . ' ' . $conf_conference_starttime, $conf_timezone);
        $end_timestamp = $this->make_timestamp_with_timezone($conf_conference_date . ' ' . $conf_conference_endtime, $conf_timezone);

        $conf_meeting_venue = sanitize_text_field($_POST['meeting_venue']);
        $conf_map_link = sanitize_text_field($_POST['map_link']);
        $conf_event_amneties = sanitize_text_field($_POST['event_amneties']);
        $conf_maximum_deligates = sanitize_text_field($_POST['maximum_deligates']);
        $conf_primary_contact_person = sanitize_text_field($_POST['primary_contact_person']);
        $conf_contact_phone_number = sanitize_text_field($_POST['contact_phone_number']);

        $conf_registration_closing_date = sanitize_text_field($_POST['registration_closing_date']);

        $conf_participating_buyers = $_POST['participating_buyers'];
        $conf_fellow_council_managers = $_POST['fellow_council_managers'];

        $conf_scope = sanitize_text_field($_POST['conf_scope']);

        //Create post
        $data = array(
            'ID' => $conf_id,
            'post_title' => $conf_title,
            'post_content' => $conf_description
        );
        wp_update_post($data);

        if (is_numeric($conf_id)) {

            //Time
            update_post_meta($conf_id, 'conf_start_timestamp', $start_timestamp);
            update_post_meta($conf_id, 'conf_end_timestamp', $end_timestamp);
            update_post_meta($conf_id, 'conf_date', $conf_conference_date);
            update_post_meta($conf_id, 'conf_start_time', $conf_conference_starttime);
            update_post_meta($conf_id, 'conf_end_time', $conf_conference_endtime);
            update_post_meta($conf_id, 'conf_timezone', $conf_timezone);
            update_post_meta($conf_id, 'conf_date_iso', date("Y-m-d", strtotime($conf_conference_date)));


            update_post_meta($conf_id, 'conf_business_value', $conf_business_value);
            update_post_meta($conf_id, 'conf_keywords', $conf_keywords);
            update_post_meta($conf_id, 'conf_venue', $conf_meeting_venue);
            update_post_meta($conf_id, 'conf_map_link', $conf_map_link);
            update_post_meta($conf_id, 'conf_event_amneties', $conf_event_amneties);
            update_post_meta($conf_id, 'conf_maximum_deligates', $conf_maximum_deligates);
            update_post_meta($conf_id, 'conf_registration_closing_date', $conf_registration_closing_date);

            //Buyers
            delete_post_meta($conf_id, 'conf_buyers');
            foreach ($conf_participating_buyers as $company_id) {
                add_post_meta($conf_id, 'conf_buyers', $company_id);
            }

            //Organizers
            delete_post_meta($conf_id, 'conf_council_managers');
            foreach ($conf_fellow_council_managers as $company_id) {
                add_post_meta($conf_id, 'conf_council_managers', $company_id);
            }

            update_post_meta($conf_id, 'conf_primary_contact_person', $conf_primary_contact_person);
            update_post_meta($conf_id, 'conf_contact_phone_number', $conf_contact_phone_number);

            // MY CODE
            $new_naics = isset($_POST['naics_codes']) ? array_filter($_POST['naics_codes']) : [];
            delete_post_meta($conf_id, 'conf_naics_codes');

            if (!empty($new_naics)) {
                foreach ($new_naics as $naic) {
                    add_post_meta($conf_id, 'conf_naics_codes', $naic);
                }
            }

            //Council level or national level
            update_post_meta($conf_id, 'conf_scope', $conf_scope);

            $this->show_conference_details($conf_id);

            //echo 'success';

        }
        wp_die();


    }

    /**
     * Publish conference
     * 
     * 
     */
    function publish_conference()
    {

        $conf_id = sanitize_text_field($_GET['conf_id']);
        $nonce = sanitize_text_field($_GET['nonce']);

        if (!wp_verify_nonce($nonce, 'confshared_ajax_nonce') or !is_user_logged_in()) {
            die();
        }

        $data = array(
            'ID' => $conf_id,
            'post_status' => 'publish',
        );

        // Update the post into the database
        if (wp_update_post($data)) {
            echo 'true';
        }
        $this->get_matching_companyList($conf_id);

        wp_die();

    }
    /**
     * list all conferences
     * @param boolean $councilOnly
     * @param boolean 
     */

    public function list($councilOnly = TRUE, $upcomingOnly = FALSE, $council_id = NULL)
    {

        $user = wp_get_current_user();

        if($council_id == null){
            $council = $this->get_userDC($user->ID);
        }else $council = $council_id;

        //Attributes
        if($councilOnly == true){
            $councilFilter =  array(
                'key' => 'conf_organized_council_id',
                'value' => $council,
                'compare' => '=',
            );
        }

        if($upcomingOnly == true){
            $upcomingFilter =  array(
                'key' => 'conf_date_iso', 
                'value' => date("Y-m-d"), 
                'type' => 'DATE',
                'compare' => '>'
            );
        }

        //
        $args = array(
            'post_type' => 'mm365_conferences',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'meta_query' => array(
                $councilFilter ?? '',
                $upcomingFilter ?? ''
            )
        );
        $conferences_list = array();
        $loop = new \WP_Query($args);
        while ($loop->have_posts()):
            $loop->the_post();

            array_push(
                $conferences_list,
                array(
                    "ID" => get_the_ID(),
                    "name" => get_the_title(),
                    "keywords" => get_post_meta(get_the_ID(), 'conf_keywords', TRUE),
                    "date" => get_post_meta(get_the_ID(), 'conf_date', TRUE),
                    "date_iso" => get_post_meta(get_the_ID(), 'conf_date_iso', TRUE),
                    "business_value" => get_post_meta(get_the_ID(), 'conf_business_value', TRUE),
                    'contact_person' => get_post_meta(get_the_ID(), 'conf_primary_contact_person', TRUE) . "<br/>" . get_post_meta(get_the_ID(), 'conf_contact_phone_number', TRUE),
                    "author_id" => get_the_author_meta('ID'),
                    "scope" => get_post_meta(get_the_ID(), 'conf_scope', TRUE),
                    "organizing_council" => get_post_meta(get_the_ID(), 'conf_organized_council_id', TRUE),
                )
            );

        endwhile;
        wp_reset_postdata();
        return $conferences_list;
    }


    /**
     * @param int $conf_id
     * @param string $actions
     * 
     */
    function show_conference_details($conf_id, $actions = TRUE)
    {

        //Buyers
        $buyer_companies = get_post_meta($conf_id, 'conf_buyers');
        $buyers = array();
        foreach ($buyer_companies as $comp_id) {
            $buyers[] = get_the_title($comp_id);
        }

        //Council Managers
        $council_managers = array();
        foreach (get_post_meta($conf_id, 'conf_council_managers') as $council_manager_id) {
            $user = get_userdata($council_manager_id);
            $council_managers[] = $user->display_name;
        }

        //NAICS Code
        $naics_code = array();
        foreach ((get_post_meta($conf_id, 'conf_naics_codes')) as $key => $value) {
            $naics_code[] = $value;
          }
          if (isset($naics_code)):
            $conf_naics_code = implode(', ', $naics_code);
          else:
            $conf_naics_code = "-";
          endif;


        //Layout
        echo '<div class="row">
        <!-- Left part -->
        <div class="col-6">
           <div class="form-row form-group">
            <h3>' . get_the_title($conf_id) . '</h3>
           </div>
        
           <div class="form-row form-group">
            <div class="col-12">
             <label for="">Requirement Description</label><br/>
             ' . get_post_field('post_content', $conf_id) . '
            </div>
           </div>
   <!--
           <div class="form-row form-group">
             <div class="col-6">
               <label for="">Approximate Value of Business</label><br/>
               ' . get_post_meta($conf_id, 'conf_business_value', true) . '
             </div>
             <div class="col-6">
               <label for="">Keywords</label><br/>
               ' . get_post_meta($conf_id, 'conf_keywords', true) . '
             </div>
           </div>
         
           <div class="form-row form-group">
           <div class="col-12">
             <label for="">Conference Scope</label><br/>
             <span class="text-capitalize badge ' . get_post_meta($conf_id, 'conf_scope', true) . '">' . get_post_meta($conf_id, 'conf_scope', true) . '</span>
           </div>
         </div>
       -->
          <!-- MY CODE -->
            <div class="form-row form-group">
              <div class="col-lg-5">
                <label for="">Selected NAICS codes<br />

                </label>
                <section class="naics-codes-dynamic">'.$conf_naics_code.'</section>

              </div>

            </div>
            <!-- end my code -->
           <div class="pto-30">
            <h5>Participating Buyer(s)</h5><hr>
           </div>
           <div class="form-row form-group">
               <div class="col-12">
                           <ul class="list-group">';
        foreach ($buyers as $index => $buyer) {
            $count = $index + 1;
            echo '<li class="list-group-item">' . $count . ". " . $buyer . '</li>';
        }
        echo '</ul></div>
           </div>
        
   
        </div>
        <!-- left part ends -->
   
        <!-- Right part -->
        <div class="col-6" data-intro="">
   
          <div class="form-row form-group">
               <div class="row">
                           <div class="col-sm-4">
                               <label for="">Conference Date </label><br/>
                               ' . get_post_meta($conf_id, 'conf_date', true) . '
                           </div>
                           <div class="col-6 col-sm-3">
                               <label for="">From</label><br/>
                               ' . get_post_meta($conf_id, 'conf_start_time', true) . '
                           </div>
                           <div class="col-6 col-sm-3">
                               <label for="">To</label><br/>
                               ' . get_post_meta($conf_id, 'conf_end_time', true) . '
                           </div>
                           <div class="col-6 col-sm-2">
                             <label for="">Time Zone </label><br/>
                             ' . get_post_meta($conf_id, 'conf_timezone', true) . '
                           </div>
               </div>
           </div>
           <div class="form-row form-group">
               <div class="col-6">
                           <label for="">Conference Venue</label><br/>
                           ' . get_post_meta($conf_id, 'conf_venue', true) . '
               </div>
               <div class="col-6 text-break">
                           <label for="">Map link</label><br/>';
                           $map_link = get_post_meta($conf_id, 'conf_map_link', true);
                           if($map_link!=null){
                                echo '<a target="_blank" href="' .$map_link. '" alt="">Location Map Link</a>';
                           }else echo "-";
                           
               echo '</div>
           </div>
           <!--
           <div class="form-row form-group">
               <div class="col-12">
                     <label for="">Event Amenities</label><br/>
                     ' . get_post_meta($conf_id, 'conf_event_amneties', true) . '  
               </div>
           </div>
   -->
           <div class="form-row form-group">
            <!--
               <div class="col-5">
                           <label for="">Maximum Occupancy </label><br/>
                           ' . get_post_meta($conf_id, 'conf_maximum_deligates', true) . '
   
               </div> -->
               <div class="col-5">
                   <label for="">Registration Closing Date </label><br/>
                   ' . get_post_meta($conf_id, 'conf_registration_closing_date', true) . '
               </div>
           </div>
   
           <div class="pto-30">
            <h5>' . get_post_meta($conf_id, 'conf_organizer', true) . ' Organizing Team</h5><hr>
           </div>
           <div class="form-row form-group">
               <div class="col-12">
                           <label for="">Council Managers</label><ul class="list-group">';
        foreach ($council_managers as $index => $cm) {
            $count = $index + 1;
            echo '<li class="list-group-item">' . $count . ". " . $cm . '</li>';
        }
        echo '</div>
           </div>
           <div class="form-row form-group">
               <div class="col-6">
                           <label for="">Primary Contact Person</label><br/>
                           ' . get_post_meta($conf_id, 'conf_primary_contact_person', true) . '
                           
               </div>
               <div class="col-6">
                           <label for="">Phone Number</label><br/>
                           ' . get_post_meta($conf_id, 'conf_contact_phone_number', true) . '
               </div>
           </div>
   
        </div>
        <!-- Right part ends-->
       
      </div>
      <div class="row">';

        if ($actions == TRUE) {
            echo '<div class="col-12 form-row pto-10">
            <div class="col text-right">
                <a href="' . add_query_arg('_wpnonce', wp_create_nonce('cm_edit_offline_conf'), site_url('cm-edit-offline-conference') . '?conf_id=' . $conf_id) . '" id="conf_edit"  class="btn btn-primary red">Edit</a>&nbsp;&nbsp;';
            if (get_post_status($conf_id) != 'publish') {
                echo '<a href="#"  id="conf_publish" data-redirect_to="' . add_query_arg('_wpnonce', wp_create_nonce('view_offline_conf'), site_url('view-offline-conference') . '?conf_id=' . $conf_id) . '" data-conf_id="' . $conf_id . '"  class="btn btn-primary green">Publish</a>';
            }
            echo '</div>
        </div>';
        }
        echo '</div>';

    }

    /**
     * Get matching supplier list
     * 
     * @param int $conf_id
     * 
     */

    function get_matching_companyList($conf_id){
        $post = get_post($conf_id);
        $search_details = $post->post_content;
        $naics_code = array();
        foreach ((get_post_meta($conf_id, 'conf_naics_codes')) as $key => $value) {
            $naics_code[] = $value;
        }
        $purged = '';

        //2.0 Onwards revised keyword patterning
        $searched_keywords = explode(',', $search_details);
        foreach ($searched_keywords as $keyword) {
            $purged .= '' . $this->keyword_cleanser($keyword) . ' ';
        }
        $keywords = explode(" ", trim($purged));

        $keyword_conditions = array_map(function ($keyword) {
            return array(
                'key'     => 'mm365_company_description',
                'value'   => $keyword,
                'compare' => 'LIKE',
            );
        }, $keywords);
        
        $naics_matching_args = array(
            'post_type'      => 'mm365_companies',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                
                // Condition for matching company type
                array(
                    'key'     => 'mm365_service_type',
                    'value'   => 'seller',
                    'compare' => '=',
                ),
                
                // OR condition for matching NAICS codes or keywords
                array(
                    'relation' => 'OR',
                    
                    // NAICS Codes condition
                    array(
                        'key'     => 'mm365_naics_codes',
                        'value'   => $naics_code,
                        'compare' => 'IN',
                    ),

                    ...$keyword_conditions
                ),
            ),
        );

        
        $naics_matching = new \WP_Query($naics_matching_args);
        
        $matching_company_ids = array();
        if ($naics_matching->have_posts()) {
            while ($naics_matching->have_posts()) {
                $naics_matching->the_post();
                $matching_company_ids[] = get_the_ID();
            }
            wp_reset_postdata();
        }
        foreach($matching_company_ids as $company_id){
            $this->mm365_conference_notification($company_id,$conf_id);
        }
    }

    /**
     * Upcoming Conference Notification Email
     * 
     * @param int $company_id
     * @param int $conf_id
     * 
     */
    function mm365_conference_notification($company_id,$conf_id){
        $request_details   = get_post_meta( $conf_id, 'mm365_service_type', true );
        $user_name  = get_post_meta( $company_id, 'mm365_contact_person', true );
        $user_email = get_post_meta( $company_id, 'mm365_company_email', true );
        $to   = $user_email;
        $link = site_url().'/upcoming-conferences/';
        $subject     = 'You have an upcoming conference';
    
        //Mail Body
        $title       = 'Upcoming Conference!';
        $content     = '
                    <p>Hi '.$user_name.',</p>
                    <p><strong>Conference Requirements Description:</strong></p>
                    <p style="font-style:italic;">"'.$request_details.'"</p>
                    <p>
                        We are pleased to inform you that a new conference has been scheduled for you.
                    </p>
                    <p>
                        Please make sure to review the details and confirm your participation at your earliest convenience.
                    </p>
                    <p>Please click on the button below to log in and accept the conference invitation.</p>
                '; 
        
    
        //$body        = $this->mm365_email_body($title,$content,$link,'Edit Match Request');
        $body        = $this->mm365_email_body_template($title,$content,$link,'Upcoming Conference');        
        $headers     = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * @param int $conf_id
     * 
     */
    public function conference_applications_list($conf_id)
    {

        $args = array(
            'post_type' => 'mm365_confappli',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'meta_query' => array(
                array(
                    'key' => 'conf_id',
                    'value' => $conf_id,
                )
            )
        );
        $applications_list = array();
        $loop = new \WP_Query($args);
        while ($loop->have_posts()):
            $loop->the_post();

            array_push(
                $applications_list,
                array(
                    "application_id" => get_the_ID(),
                    "supplier_id" => get_post_meta(get_the_ID(), 'supplier_id', TRUE),
                    "name" => get_the_title(get_post_meta(get_the_ID(), 'supplier_id', TRUE)),
                    "deligates" => get_post_meta(get_the_ID(), 'deligate_a_name', TRUE) . " - " . get_post_meta(get_the_ID(), 'deligate_a_phone', TRUE) . " <br/>" . get_post_meta(get_the_ID(), 'deligate_b_name', TRUE) . " - " . get_post_meta(get_the_ID(), 'deligate_b_phone', TRUE) . " ",
                    "deligates_count" => get_post_meta(get_the_ID(), 'deligates_count', TRUE),
                    "applied_date" => get_the_date('m/d/Y', get_the_ID()),
                    "covering_letter" => get_the_content(get_the_ID()),
                    "status" => get_post_meta(get_the_ID(), 'status', TRUE),
                )
            );
        endwhile;
        wp_reset_postdata();
        return $applications_list;
    }


    /**
     * @param int $conf_id
     * xls export of applicants list
     * 
     */
    function export_applicants_list($conf_id)
    {

        //Get all applications with - conf_id
        $args = array(
            'post_type' => 'mm365_confappli',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'meta_query' => array(
                array(
                    'key' => 'conf_id',
                    'value' => $conf_id,
                )
            )
        );
        $applications_list = array();
        $loop = new \WP_Query($args);
        while ($loop->have_posts()):
            $loop->the_post();

            $applications_list[] = array(

                get_the_title(get_post_meta(get_the_ID(), 'supplier_id', TRUE)),
                get_post_meta(get_the_ID(), 'deligates_count', TRUE),
                get_post_meta(get_the_ID(), 'deligate_a_name', TRUE),
                get_post_meta(get_the_ID(), 'deligate_a_designation', TRUE),
                get_post_meta(get_the_ID(), 'deligate_a_phone', TRUE),
                get_post_meta(get_the_ID(), 'deligate_b_name', TRUE),
                get_post_meta(get_the_ID(), 'deligate_b_designation', TRUE),
                get_post_meta(get_the_ID(), 'deligate_b_phone', TRUE),
                get_the_date('m/d/Y', get_the_ID()),
                ucfirst(get_post_meta(get_the_ID(), 'status', TRUE)),

            );

        endwhile;


        $file_name = "Deligates List for conference - " . get_the_title($conf_id);

        $writer = new XLSXWriter();

        $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 50, 'wrap_text' => true);
        $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
        $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');

        $writer->writeSheetHeader('Sheet1', array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string'), $col_options = ['widths' => [50, 20, 40, 30, 30, 30, 30, 30], 'suppress_row' => true]);

        $writer->writeSheetRow('Sheet1', $rowdata = array($file_name), $styles1);

        //heading row
        $writer->writeSheetRow(
            'Sheet1',
            array(
                'Company name',
                'Deligates Count',
                'Deligate - 1',
                'Designation',
                'Phone',
                'Deligate - 2',
                'Designation',
                'Phone',
                'Date Applied',
                'Status'
            ),
            $styles2
        );


        foreach ($applications_list as $dat) {
            $writer->writeSheetRow('Sheet1', $dat, $styles3);
        }


        $file = $file_name . '.xlsx';
        $writer->writeToFile($file);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            exit;
        }


        wp_reset_postdata();


    }

    /*----------------------------------------------------- END USER ----------------*/

    /**
     * 
     * Apply for participating in a conference by and end user(supplier)
     */
    function apply_offline_conference_particiaption()
    {

        $conf_id = sanitize_text_field($_REQUEST['conf_id']);
        $nonce = sanitize_text_field($_REQUEST['nonce']);

        if (!wp_verify_nonce($nonce, 'confshared_ajax_nonce') or !is_user_logged_in()) {
            die();
        }

        //Additional delegate application check
        if ($_POST['deligate_b_name'] != '') {

            $meta_input = array(
                'conf_id' => sanitize_text_field($conf_id),
                'supplier_id' => sanitize_text_field($_POST['supplier_id']),
                'deligate_a_name' => sanitize_text_field($_POST['deligate_a_name']),
                'deligate_a_phone' => sanitize_text_field($_POST['deligate_a_phone']),
                'deligate_a_designation' => sanitize_text_field($_POST['deligate_a_designation']),
                'deligate_b_name' => sanitize_text_field($_POST['deligate_b_name']),
                'deligate_b_phone' => sanitize_text_field($_POST['deligate_b_phone']),
                'deligate_b_designation' => sanitize_text_field($_POST['deligate_b_designation']),
                'deligates_count' => '2',
                'status' => 'applied'
            );

        } else {

            $meta_input = array(
                'conf_id' => sanitize_text_field($conf_id),
                'supplier_id' => sanitize_text_field($_POST['supplier_id']),
                'deligate_a_name' => sanitize_text_field($_POST['deligate_a_name']),
                'deligate_a_phone' => sanitize_text_field($_POST['deligate_a_phone']),
                'deligate_a_designation' => sanitize_text_field($_POST['deligate_a_designation']),
                'deligates_count' => '1',
                'status' => 'applied'
            );
        }


        //Create post
        $data = array(
            'post_type' => 'mm365_confappli',
            'post_title' => 'Applied for ' . $conf_id,
            'post_status' => 'publish',
            'post_content' => sanitize_text_field($_POST['covering_letter']),
            'meta_input' => $meta_input

        );

        $post_id = wp_insert_post($data);

        if ($post_id) {
            add_post_meta($conf_id, 'conf_applied_suppliers', sanitize_text_field($_POST['supplier_id']));
            add_post_meta($conf_id, 'conf_application_ids', sanitize_text_field($post_id));
            echo 'success';
        } else
            echo 'failed';

        wp_die();

    }


    /**
     * Process the participation
     * Accept or Reject application 
     * 
     * 
     */

     function process_particiaption_request(){

        $nonce  = sanitize_text_field($_POST['nonce']);
        $application_id = sanitize_text_field($_POST['application_id']);
        $act = sanitize_text_field($_POST['act']);

        //Message for rejection
        $cause_of_rejection = sanitize_text_field($_POST['message']);

        //check nonce
        if (!wp_verify_nonce( $nonce, 'confcouncil_ajax_nonce' ) OR !is_user_logged_in()) {
            die();
        }

        //Change status
        if(update_post_meta( $application_id, 'status', $act)){

                //Change status and notify supplier
                if($cause_of_rejection != ''){
                    $this->notify_supplier($application_id, $act, $cause_of_rejection);
                    update_post_meta( $application_id, 'cause_of_rejection', $cause_of_rejection);
                }else{
                    $this->notify_supplier($application_id, $act);
                }

               echo 'success';
        }

        wp_die();

     }

     /**
      * @param int $application_id
      * @param string $status
      * @param string $rejection_message
      *
      */
      function notify_supplier($application_id, $status ,$rejection_message = NULL){

        $supplier_id = get_post_meta( $application_id, 'supplier_id', true );
        $conf_id = get_post_meta( $application_id, 'conf_id', true );

        //User info
        $user_name  = get_post_meta( $supplier_id, 'mm365_contact_person', true );
        $user_email = get_post_meta( $supplier_id, 'mm365_company_email', true );
        $conference_title = get_the_title( $conf_id );

        //Send to all mmsdc_magaer user roles
        $to   = $user_email;
        $link = site_url().'/upcoming-conferences/';
        $subject = 'Your conference participation is '.$status.'';

        //Mail Body
        $title       = $subject ;
        $content     = '<p>Hi '.$user_name.',</p>
                        <p><strong>Your conference participation is '.$status.'</strong></p>
                        <p>
                        Your application to participate in '.$conference_title.' is '.$status.'
                        by the council manager. 
                        </p>';

        if($status == 'rejected'){
            $content .= '<p>Reason for rejection:<br/>'.$rejection_message.'</p>'; 
        }

        $content     .= '<p>Please click on the below button to login and see the details about conference</p>'; 
        
        $body        = $this->mm365_email_body_template_meetings($title,$content,$link,'Conferece Participation Application');
        $headers     = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * 
     * 
     * 
        */
    function offline_conference_fordropdown(){

        $council = $_POST['council'];
        
        //All national conferences will be visible
        $upcomingNationalConf = $this->list(FALSE, TRUE, false);

    
        //Selected council conferences
        $upcomingCouncilConf = $this->list(TRUE, TRUE, $council);

        echo '<optgroup label="National Level">';
        $nationalConfCount = 0;
        foreach ($upcomingNationalConf as $conf) {
            if($conf['scope'] == 'national'){
            echo "<option value='".$conf['ID']."'>".$conf['name']."</option>";
             $nationalConfCount++;
            }
        }
        if($nationalConfCount == 0){
            echo "<option disabled value=''>No upcoming conferences</option>";
        }
        echo "</optgroup>";

        echo '<optgroup label="Council Level">';
        $councilConfCount = 0;
        foreach ($upcomingCouncilConf as $conf) {
            if($conf['scope'] != 'national'){
            echo "<option value='".$conf['ID']."'>".$conf['name']."</option>";
            $councilConfCount++;
            }
        }
        if($councilConfCount == 0){
            echo "<option disabled value=''>No upcoming conferences</option>";
        }
        echo "</optgroup>";
        
        wp_die();
    }

}