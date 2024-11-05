<?php

namespace Mm365;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * All the methods thaat is associated with closing a match request
 * 
 * 
 */

Class MatchrequestClosure{

  use MeetingAddon;
  use NotificationAddon;
  
    function __construct(){

        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), 11 );

        //Create meeting
        add_action( 'wp_ajax_close_match', array( $this, 'close_match' ) );
    }


    function assets(){

        if ( wp_register_script( 'mm365_matchclosure',plugins_url('matchmaker-core/assets/mm365_matchclosure.js'), array( 'jquery' ), false, TRUE ) ) {
            wp_enqueue_script( 'mm365_matchclosure' );
            wp_localize_script( 'mm365_matchclosure', 'mrclosureAjax', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce("mrclosure_ajax_nonce")
            ) );
        }

    }


/*-----------------------------------------------------------
    Close match request
    ---------------------------------------------------------
    *This method will capture the reason for closure from form
    and change the respective match request to 'closed' stattus
    The method should look for all the meetings associated with
    this match request and cancel them based on thir status
    ie scheduled, proposed, proposed new time, accepted items will
    be put in to cancelld status
------------------------------------------------------------- */
  function close_match(){
    //decrypt id
    $mr_id_dec = $_POST['mr_id'];
    $act       = $_POST['act'];

    //Status of the the POST
    $match_status = get_post_status($mr_id_dec);

    //Check current user is owner of post
    $current_user   = wp_get_current_user();
    $auth   = get_post($mr_id_dec); // gets author from post
    $authid = $auth->post_author; // gets author id for the post
 
    if (wp_verify_nonce( $_REQUEST['nonce'], 'mrclosure_ajax_nonce') AND ($current_user->ID == $authid) AND $act != '' AND $match_status == 'publish') {

      //Get list of approved companis to capture the grade from input
      $matched_companies =  maybe_unserialize(get_post_meta($mr_id_dec, 'mm365_matched_companies', true ));
      $approved_companies = array();
      foreach ($matched_companies as $key => $value) {
        if($value[1]=='1'){
          array_push($approved_companies,$value[0]);
        }
      }

      switch ($act) {
        case 'cancel':
          $status = 'cancelled';
          break;
        
        case 'complete':
          $status = 'completed';
            break;
      }

        update_post_meta( $mr_id_dec, 'mm365_matchrequest_status',$status);
        update_post_meta( $mr_id_dec, 'mm365_reason_for_closure',$_POST['reason_for_mrclosure']);
        update_post_meta( $mr_id_dec, 'mm365_reason_for_closure_filter',$_POST['match_closure_filter']);

        //Grade scores
        foreach ($approved_companies as $cmp_id) {
          add_post_meta( $mr_id_dec, 'mm365_match_grade',$cmp_id."|".$_POST[$cmp_id.'_grade']);
        }

        //Apped updated time
        $modefied_time = get_the_modified_time("m/d/Y h:i A",$mr_id_dec);
        $modefied_time_iso = get_the_modified_time("Y-m-d",$mr_id_dec);
        update_post_meta($mr_id_dec, 'mm365_matched_companies_last_updated',$modefied_time);
        update_post_meta($mr_id_dec, 'mm365_matched_companies_last_updated_isodate',$modefied_time_iso);

        //Contract details
        if($_POST['contract_value'] != NULL){
              update_post_meta( $mr_id_dec, 'mm365_contract_value',$_POST['contract_value']);
              update_post_meta( $mr_id_dec, 'mm365_contract_termsandconditions',$_POST['contract_termsandconditions']);
        }

        //Find and close all meetings associated with the match
        $this->close_meetings($mr_id_dec);

        echo '1';
        die();

    } else echo '0';
    die();
    
  }

  
/*-----------------------------------------------------------
    Close meetings associated
    ---------------------------------------------------------*/

  function close_meetings($mr_id,$message = NULL){

    //find meetings based on MRID
    $find_meetings = array(
        'posts_per_page' => -1,    // No limit
        'fields'         => 'ids', // Reduce memory footprint
        'post_type'      => 'mm365_meetings',
        'post_status' => array( 'publish'),
        'orderby'     => 'date',
        'order'       => 'ASC',
        'meta_query' => array(
            array(
                'key'     => 'mm365_from_matchrequest',
                'value'   => $mr_id,
                'compare' => '=',
            ),
        )
        
    );
    $query = new \WP_Query($find_meetings);
    foreach($query->posts AS $meeting_id){ 
        //Cancel meetings in proposed, accepted, proposed_new_time
        $meeting_status = get_post_meta( $meeting_id, 'mm365_meeting_status',true);
        if(in_array($meeting_status,array("proposed", "accepted", "proposed_new_time"))){
            $this->notification($meeting_id);
        }elseif(in_array($meeting_status,array("scheduled", "rescheduled"))){
            //Close meeting if schedulked for future date
            $slots         = get_post_meta( $meeting_id, 'mm365_meeting_slots');
            $accepted_slot = get_post_meta( $meeting_id, 'mm365_accepted_meeting_slot',true);
            if($accepted_slot < 4)
            {
              $match_slot = ($accepted_slot - 1);
              $slot       = explode("|",$slots[$match_slot]);
              if($slot[0] >= time()){ $this->notification($meeting_id); }
            }else{
              $slots_proposed_get  = get_post_meta( $meeting_id, 'mm365_meeting_reschedule_timestamp',true);
              $slots_proposed      = explode("|",$slots_proposed_get);
              if($slots_proposed[0] >= time()){ $this->notification($meeting_id); }                
            }

        }
             
    }

    
  }
  /*-----------------------------------------------------------
    Notify meetings cancellation
  ---------------------------------------------------------*/
    function notification($meeting_id){
        //$meetings = new mm365_meetings;

        //to attendee
        $proposer_info = get_post_meta($meeting_id,'mm365_proposed_company');
        $subject       = $proposer_info[0]." has cancelled the meeting";
        $mail_title    = $subject ;
        update_post_meta($meeting_id, 'mm365_meeting_status','cancelled');
        update_post_meta($meeting_id, 'mm365_meeting_termination_message',$proposer_info[1]." has closed the match request");
        $additional_cont = "<p>".$proposer_info[1]." from ".$proposer_info[0]." has cancelled the meeting titled \"".get_post_meta($meeting_id,'mm365_meeting_title',true)."\".<p>
                            <p>Reason for cancellation:<br/>".$proposer_info[1]." has closed the match request</p>
                            <p>Please click on the below button to login and view the details.</p>";
                            
        $this->notification_to_attendee($meeting_id,$subject,$mail_title,$additional_cont);

    }



}