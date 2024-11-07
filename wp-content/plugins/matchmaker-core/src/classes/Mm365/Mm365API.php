<?php

namespace Mm365;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Mm365API extends \WP_REST_Controller
{
  use CouncilAddons;
  use AdminAddons;
  function __construct()
  {
    add_action('rest_api_init', array($this, 'register_routes'), 10);
  }

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes()
  {
    $version = '1';
    $namespace = 'mm365/v' . $version;
    $base = 'route';

    register_rest_route($namespace, $base . '/' . 'dashboard' . '/', array(
      array(
        'methods' => 'GET',
        'callback' => array($this, 'get_items'),
        //'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args' => array(
          'council' => array(
            'validate_callback' => function ($param, $request, $key) {

              $councilShortnames = $this->council_shortnames();

              $keyFound = array_search($param, $councilShortnames); // Search for the value
        
                                  // If found, set the validated ID in the request
                                  if ($keyFound !== false) {
                                    $request->set_param('council_id', $keyFound); // Store the ID in a new parameter
                                    return true; // Validation successful
                                }
            
                                return false; // Validation failed
            },
          ),
          'period' => array(
            'validate_callback' => function ($param, $request, $key) {
              if (in_array($param, array('week', 'month', 'year'))) {
                return true; // Return true for valid input
              }
              return false; // Explicitly return false for invalid input
            }
          )
        ),
        'permission_callback' => [$this, 'mm365_rest_permission_callback']
      )
    ));

  }

  /**
   * End point - Dashboard Stats
   * 
   */
  /**
   * Get a collection of items
   *
   * @param \WP_REST_Request $request Full data about the request.
   * @return \WP_Error|\WP_REST_Response
   */
  public function get_items($request)
  {

    //Status based on council ID
    $filter = $request->get_param('period');
    $council_id = $request->get_param('council_id');

    if($council_id != '' AND $council_id != 1000){
      $company_council_key = 'mm365_company_council';
      $mr_council_key = 'mm365_requester_company_council';
      $meeting_council_key = 'mm365_attendees_council_id';
      $certificate_council_key = 'mm365_submitted_council';
  
  }else{
      $company_council_key = NULL;
      $mr_council_key = NULL;
      $meeting_council_key = NULL;
      $certificate_council_key = NULL;
  }

    //
    $mbe_count = $this->mm365_postcounts_timeperiod('mm365_companies',$filter,'mm365_service_type','seller','post_date', $council_id, $company_council_key);
    $corp_count = $this->mm365_postcounts_timeperiod('mm365_companies',$filter,'mm365_service_type','buyer','post_date', $council_id, $company_council_key);
    $companies_count   = $mbe_count + $corp_count;
    
    
    $mr_count =  $this->mm365_find_matchrequests_between($filter,'','', $council_id, $mr_council_key); 
    $approved_mr_count = $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','approved', $council_id, $mr_council_key);
    $auto_approved_mr_count = $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','auto-approved', $council_id, $mr_council_key); 
    
    $completed_mr_count =  $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','completed', $council_id, $mr_council_key); 
    //$completed_mr_count = $this->mm365_postcounts_timeperiod('mm365_matchrequests',$filter,'mm365_matchrequest_status','completed', $council_id, $mr_council_key);
    //$cancelled_mr_count = $this->mm365_postcounts_timeperiod('mm365_matchrequests',$filter,'mm365_matchrequest_status','cancelled', $council_id, $mr_council_key);
    $cancelled_mr_count =  $this->mm365_find_matchrequests_between($filter,'mm365_matchrequest_status','cancelled', $council_id, $mr_council_key); 
    
    $scheduled_count   = $this->mm365_postcounts_timeperiod('mm365_meetings',$filter,'mm365_meeting_status','scheduled','post_modified',$council_id, $meeting_council_key);
    $rescheduled_count = $this->mm365_postcounts_timeperiod('mm365_meetings',$filter,'mm365_meeting_status','rescheduled','post_modified',$council_id, $meeting_council_key);
    
    $pending_certificates_count   =   $this->mm365_postcounts_timeperiod('mm365_certification',$filter,'mm365_certificate_status','pending','post_modified', $council_id, $certificate_council_key);
    $expired_certificates_count   =   $this->mm365_postcounts_timeperiod('mm365_certification',$filter,'mm365_certificate_status','expired','post_modified', $council_id, $certificate_council_key);
    
    $council_selected = ($council_id == 1000) ? '':$council_id;


    $data = array(
      "companies" => $companies_count,
      "companies_view" => site_url('/quick-reports?type=company&period='.$filter.'&meta=x&sacouncilfilter='.$council_selected),
      "companies_download" => site_url('/view-quick-reports-companies?period='.$filter.'&meta=x&sacouncilfilter='.$council_selected),
      "suppliers" => $mbe_count ,
      "suppliers_view" => site_url('/quick-reports?type=company&period='.$filter.'&meta=seller&sacouncilfilter='.$council_selected),
      "suppliers_download" => site_url('/view-quick-reports-companies?period='.$filter.'&meta=seller&sacouncilfilter='.$council_selected),
      "buyers" => $corp_count,
      "buyers_view" => site_url('/quick-reports?type=company&period='.$filter.'&meta=buyer&sacouncilfilter='.$council_selected),
      "buyers_download" => site_url('/view-quick-reports-companies?period='.$filter.'&meta=buyer&sacouncilfilter='.$council_selected),
      "matchrequests" => $mr_count,
      "matchrequests_view" => site_url('/quick-reports?type=match&period='.$filter.'&meta=x&sacouncilfilter='.$council_selected),
      "matchrequests_download" => site_url('/view-quick-reports-match?period='.$filter.'&meta=x&sacouncilfilter='.$council_selected),
      "approved_match_requests" => $approved_mr_count,
      "approved_match_requests_view" => site_url('/quick-reports?type=match&period='.$filter.'&meta=approved&sacouncilfilter='.$council_selected),
      "approved_match_requests_download" => site_url('/view-quick-reports-match?period='.$filter.'&meta=approved&sacouncilfilter='.$council_selected),
      "auto_approved_match_requests" => $auto_approved_mr_count,
      "auto_approved_match_requests_view" => site_url('/quick-reports?type=match&period='.$filter.'&meta=auto-approved&sacouncilfilter='.$council_selected),
      "auto_approved_match_requests_download" =>site_url('/view-quick-reports-match?period='.$filter.'&meta=auto-approved&sacouncilfilter='.$council_selected),
      "completed_match_requests" => $completed_mr_count,
      "completed_match_requests_view" =>site_url('/quick-reports?type=match&period='.$filter.'&meta=completed&sacouncilfilter='.$council_selected),
      "completed_match_requests_download" =>site_url('/view-quick-reports-match?period='.$filter.'&meta=completed&sacouncilfilter='.$council_selected),
      "cancelled_match_requests" => $cancelled_mr_count,
      "cancelled_match_requests_view" => site_url('/quick-reports?type=match&period='.$filter.'&meta=cancelled&sacouncilfilter='.$council_selected),
      "cancelled_match_requests_download" => site_url('/view-quick-reports-match?period='.$filter.'&meta=cancelled&sacouncilfilter='.$council_selected),
      "meetings_scheduled" => $scheduled_count,
      "meetings_scheduled_view" =>site_url('/quick-reports?type=meetings&period='.$filter.'&meta=x&sacouncilfilter='.$council_selected),
      "meetings_scheduled_download" =>site_url('/view-quick-reports-meeting?period='.$filter.'&meta=auto-approved&sacouncilfilter='.$council_selected),
      "certificates_pending_for_verification" => $pending_certificates_count ,
      "certificates_pending_for_verification_view" => site_url('/quick-reports?type=certificates&period='.$filter.'&meta=pending&sacouncilfilter='.$council_selected),
      "certificates_pending_for_verification_download" => site_url('/certificate-verification?stat=pending&period='.$filter.'&sacouncilfilter='.$council_selected),
      "certificates_expired" => $expired_certificates_count,
      "certificates_expired_view" => site_url('/quick-reports?type=certificates&period='.$filter.'&meta=expired&sacouncilfilter='.$council_selected),
      "certificates_expired_download" => site_url('/certificate-verification?stat=expired&period='.$filter.'&sacouncilfilter='.$council_selected),
      "generated_time" => time(),
      "council" => $council_selected,
      "period"  => $filter 
    );


    return new \WP_REST_Response($data, 200);
  }





  //https://wordpress.stackexchange.com/questions/414034/custom-rest-endpoints-and-application-passwords
//ZhMb yZBa 5vN1 5wVq 7Bp1 IejU
  function mm365_rest_permission_callback(\WP_REST_Request $request)
  {
    $app_password = $request->get_header('X-WP-Application-Password');

    if (empty($app_password)) {
      return new \WP_Error('rest_forbidden', __('Authentication required.'), array('status' => 401));
    }

    $user_id = $request->get_header('user'); // or get the user ID from the request data

    $result = wp_authenticate_application_password(null, $user_id, $app_password);

    if (is_wp_error($result)) {
      return new \WP_Error('rest_forbidden', $result->get_error_message(), array('status' => 403));
    }

    // Here you can do additional checks or data processing based on the password information
    // ...

    return true; // Access granted
  }

  /**
   * Council list
   * 
   */
  function council_shortnames()
  {

    $data = $this->get_councils_list();
    $councils = []; // Initialize the councils array

    foreach ($data as $key => $value) {
      $councils[$key] = $value[0]; // Assign the short name directly
    }
    $councils[1000] = 'all';

    return $councils; // Return the associative array

  }


}