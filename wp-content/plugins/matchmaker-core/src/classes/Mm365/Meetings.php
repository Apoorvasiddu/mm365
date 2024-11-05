<?php

namespace Mm365;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}



class Meetings
{
  use TimezoneAddon;
  use MeetingAddon;
  use CertificateAddon;
  use CountryStateCity;
  use NotificationAddon;
  use CouncilAddons;



  function __construct()
  {


    add_action('wp_enqueue_scripts', array($this, 'assets'), 11);


    add_action('wp_ajax_create_meeting', array($this, 'create_meeting'));

    //edit meeting
    add_action('wp_ajax_edit_meeting', array($this, 'update_meeting'));

    //slot availability
    add_action('wp_ajax_is_slot_availabale', array($this, 'is_slot_availabale'));

    //Listing scheduled
    add_action('wp_ajax_meetings_scheduled', array($this, 'meetings_scheduled'));

    //Listing invites
    add_action('wp_ajax_meetings_invites', array($this, 'meetings_invites'));

    //Invite response
    add_action('wp_ajax_meeting_invite_response', array($this, 'meeting_invite_response'));

    //Final step for scheduling the meeting
    add_action('wp_ajax_schedule_meeting', array($this, 'schedule_meeting'));

    //Meeting termination
    add_action('wp_ajax_terminate_meeting', array($this, 'terminate_meeting'));

    //Meeting termination
    add_action('wp_ajax_reschedule_meeting', array($this, 'reschedule_meeting'));

    //Read users timezone
    add_action('wp_ajax_read_timezone', array($this, 'read_timezone'));
    add_action('wp_ajax_nopriv_read_timezone', array($this, 'read_timezone'));

    //Update meeting details
    add_action('wp_ajax_update_meeting_details', array($this, 'update_meeting_details'));

    add_action('wp_ajax_mm365_meetings_reports_view', [$this, 'mm365_admin_viewreport_meetings'], 11, 0);


    add_filter('mm365_meeting_status', array($this, 'meeting_status'), 1, 4);

    add_filter('mm365_meetings_ownership_check', array($this, 'is_meeting_belongsto_user'), 1, 2);

    add_filter('mm365_meetings_convert_time', array($this, 'convert_time'), 1, 4);

    add_filter('mm365_meetings_time_to_timestamp', array($this, 'meetingtime_timestamp'), 1, 4);

    add_filter('mm365_meetings_is_schedulable', array($this, 'is_schedulable'), 1, 4);

    add_filter('mm365_meetings_list_timezones', array($this, 'mm365_wp_timezone_choice'), 1, 2);

    add_filter('mm365_meetings_quick_reports_download', [$this, 'meeting_quickreport_download'], 11, 3);


  }

  /**
   * 
   * 
   */
  function assets()
  {

    wp_register_script('parsley', plugins_url('matchmaker-core/assets/parsley.min.js'), array('jquery'));
    wp_register_script('tinymce', 'https://cdn.tiny.cloud/1/p6bk80euywie64hssqi0gwfxqnq38mhua220uavgw645g2tn/tinymce/6/tinymce.min.js', array());
    wp_register_script('admin_list_admin_viewreport_meetings', plugins_url('matchmaker-core/assets/admin_view_meetings_report.js'), array('jquery'), false, true);

    wp_enqueue_script('parsley');
    wp_enqueue_script('tinymce');
    wp_enqueue_script('admin_list_admin_viewreport_meetings');

    if (wp_register_script('mm365_meetings', plugins_url('matchmaker-core/assets/mm365_meetings.js'), array('jquery'), false, TRUE)) {

      wp_enqueue_script('mm365_meetings');

      wp_localize_script(
        'mm365_meetings',
        'meetingAjax',
        array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'nonce' => wp_create_nonce("meeting_ajax_nonce")
        )
      );

      wp_localize_script(
        'admin_list_admin_viewreport_meetings',
        'adminViewReportMeetingsAjax',
        array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'nonce' => wp_create_nonce("meeting_ajax_nonce")
        )
      );



    }

    //wp_enqueue_script('mm365_meetings');
  }



  /**
   * Create Meeting
   * 
   * 
   */
  function create_meeting()
  {

    $user = wp_get_current_user();

    //Get match status and match request authority
    $mr_id = $_POST['from_match_request'];
    $auth = get_post($mr_id); // gets author from post
    $authid = $auth->post_author; // gets author id for the post
    $match_status = get_post_meta($mr_id, 'mm365_matchrequest_status', true);

    if (($user->ID != $authid) or !in_array($match_status, array("approved", "auto-approved"))) {

      echo "Unauthorised action";
      die();

    } else {

      $proposer = $_POST['proposed_company_id'];
      $attendee = $_POST['meeting_with_company_id'];
      $prop_timezone = $_POST['proposer_timezone'];

      //Convert timestamp based on users choice
      $first_choice = $_POST['first_choice'];
      $first_choice_st = $_POST['first_choice_starttime'];
      $first_choice_et = $_POST['first_choice_endtime'];
      $fc_start = $first_choice . ' ' . $first_choice_st;
      $fc_end = $first_choice . ' ' . $first_choice_et;

      $s1_start = $this->make_timestamp($fc_start, $prop_timezone);
      $s1_end = $this->make_timestamp($fc_end, $prop_timezone);

      $first = $this->meetingtime_timestamp(date('m/d/Y', $s1_start), date('h:ia', $s1_start), date('h:ia', $s1_end));
      $fst_available = $this->is_slot_availabale_self($first['start'], $first['end'], $attendee[1], $proposer, $_POST['mid']);

      //Second choice
      if ($_POST['second_choice'] != '' and $_POST['second_choice_endtime'] != '') {

        $second_choice = $_POST['second_choice'];
        $second_choice_st = $_POST['second_choice_starttime'];
        $second_choice_et = $_POST['second_choice_endtime'];
        $sc_start = $second_choice . ' ' . $second_choice_st;
        $sc_end = $second_choice . ' ' . $second_choice_et;

        $s2_start = $this->make_timestamp($sc_start, $prop_timezone);
        $s2_end = $this->make_timestamp($sc_end, $prop_timezone);

        $second = $this->meetingtime_timestamp(date('m/d/Y', $s2_start), date('h:ia', $s2_start), date('h:ia', $s1_end));
        $snd_available = $this->is_slot_availabale_self($second['start'], $second['end'], $attendee[1], $proposer, $_POST['mid']);

      } else
        $snd_available = '';

      //Third Choice
      if ($_POST['third_choice'] != '' and $_POST['third_choice_endtime'] != '') {

        $third_choice = $_POST['third_choice'];
        $third_choice_st = $_POST['third_choice_starttime'];
        $third_choice_et = $_POST['third_choice_endtime'];
        $tc_start = $third_choice . ' ' . $third_choice_st;
        $tc_end = $third_choice . ' ' . $third_choice_et;

        $s3_start = $this->make_timestamp($tc_start, $prop_timezone);
        $s3_end = $this->make_timestamp($tc_end, $prop_timezone);

        $third = $this->meetingtime_timestamp(date('m/d/Y', $s3_start), date('h:ia', $s3_start), date('h:ia', $s3_end));
        $thd_available = $this->is_slot_availabale_self($third['start'], $third['end'], $attendee[1], $proposer, $_POST['mid']);

      } else
        $thd_available = '';


      //Check slot availability
      if ($fst_available == 'no' or $snd_available == 'no' or $thd_available == 'no') {
        echo 'slot_error';
        die();
      }

      $post_information = array(
        'post_title' => wp_strip_all_tags($_POST['meeting_title']),
        'meta_input' => array(
          'mm365_meeting_title' => sanitize_text_field($_POST['meeting_title']),
          'mm365_meeting_with_contactperson' => sanitize_text_field($_POST['meeting_contact_person']),
          'mm365_meeting_with_contactemail' => sanitize_text_field($_POST['meeting_contact_email']),
          'mm365_meeting_with_alt_contactemail' => sanitize_text_field($_POST['meeting_contact_alt_email']),
          'mm365_meeting_date_1' => sanitize_text_field($_POST['first_choice']),
          'mm365_meeting_date_1_from' => sanitize_text_field($_POST['first_choice_starttime']),
          'mm365_meeting_date_1_to' => sanitize_text_field($_POST['first_choice_endtime']),
          'mm365_meeting_date_2' => sanitize_text_field($_POST['second_choice']),
          'mm365_meeting_date_2_from' => sanitize_text_field($_POST['second_choice_starttime']),
          'mm365_meeting_date_2_to' => sanitize_text_field($_POST['second_choice_endtime']),
          'mm365_meeting_date_3' => sanitize_text_field($_POST['third_choice']),
          'mm365_meeting_date_3_from' => sanitize_text_field($_POST['third_choice_starttime']),
          'mm365_meeting_date_3_to' => sanitize_text_field($_POST['third_choice_endtime']),
          'mm365_meeting_agenda' => sanitize_text_field($_POST['meeting_agenda']),
          'mm365_attendees_council_id' => sanitize_text_field($_POST['mm365_attendees_council_id']),
          'mm365_proposer_council_id' => sanitize_text_field($_POST['mm365_proposer_council_id']),
          'mm365_meeting_status' => 'proposed',
        ),
        'post_type' => 'mm365_meetings',
        'post_status' => 'publish',
        'post_author' => $user->ID
      );

      $post_id = wp_insert_post($post_information);

      //Meeting with
      add_post_meta($post_id, 'mm365_meeting_with_company', $_POST['meeting_with_company'], false);
      add_post_meta($post_id, 'mm365_meeting_with_company', $_POST['meeting_with_company_id'], false);

      //Proposed info
      add_post_meta($post_id, 'mm365_proposed_company_id', $proposer, true);
      add_post_meta($post_id, 'mm365_proposed_company', get_the_title($proposer), false);
      add_post_meta($post_id, 'mm365_proposed_company', get_post_meta($proposer, 'mm365_contact_person', true), false);
      add_post_meta($post_id, 'mm365_proposed_company', get_post_meta($proposer, 'mm365_company_email', true), false);

      //Match info
      add_post_meta($post_id, 'mm365_from_matchrequest', $_POST['from_match_request'], true);

      //Time slots
      add_post_meta($post_id, 'mm365_meeting_slots', $s1_start . "|" . $s1_end, false);
      if ($_POST['second_choice'] != '' and $_POST['second_choice_endtime'] != ''):
        add_post_meta($post_id, 'mm365_meeting_slots', $s2_start . "|" . $s2_end, false);
      else:
        add_post_meta($post_id, 'mm365_meeting_slots', "", false);
      endif;
      if ($_POST['third_choice'] != '' and $_POST['third_choice_endtime'] != ''):
        add_post_meta($post_id, 'mm365_meeting_slots', $s3_start . "|" . $s3_end, false);
      else:
        add_post_meta($post_id, 'mm365_meeting_slots', "", false);
      endif;

      //Proposer timezone
      add_post_meta($post_id, 'mm365_proposer_timezone', $_POST['proposer_timezone'], false);

      //Attendee location
      $attendee_country = get_post_meta($_POST['meeting_with_company_id'], 'mm365_company_country', true);
      $attendee_state = get_post_meta($_POST['meeting_with_company_id'], 'mm365_company_state', true);
      $attendee_city = get_post_meta($_POST['meeting_with_company_id'], 'mm365_company_city', true);

      //Proposers location
      $proposer_country = $this->get_countryname(get_post_meta($proposer, 'mm365_company_country', true));
      $proposer_state = $this->get_statename(get_post_meta($proposer, 'mm365_company_state', true));
      $proposer_city = $this->get_cityname(get_post_meta($proposer, 'mm365_company_city', true));


      //Proposer conference participation
      add_post_meta($post_id, 'mm365_meeting_in_conference', $_POST['conference_participation'], true);

      if ($post_id != '') {
        //to proposer
        $additional_cont = "
            <p>Your proposed meeting timeslots for the meeting titled “" . $_POST['meeting_title'] . "” is successfully sent to " . $_POST['meeting_with_company'] . ", 
            " . $this->get_cityname($attendee_city) . "," . $this->get_statename($attendee_state) . " - " . $this->get_countryname($attendee_country) . ".</p>
            <p>Please click on the button below to login and view the meeting details.</p>";

        $this->notification_to_proposer($post_id, "Meeting invitation with proposed timeslots is sent Successfully!", "Meeting invitation sent to " . $_POST['meeting_with_company'] . " " . $this->get_cityname($attendee_city) . "," . $this->get_statename($attendee_state) . " - " . $this->get_countryname($attendee_country), $additional_cont);

        //to attendee
        $additional_cont = "
            <p>" . get_post_meta($proposer, 'mm365_contact_person', true) . " from " . get_the_title($proposer) . ", $proposer_city $proposer_state - $proposer_country 
            has proposed the timeslots for the meeting titled “" . $_POST['meeting_title'] . "”.</p>
            <p>Please click on the button below to login and view the meeting details. Once you accept a timeslot, meeting details will be shared with you.</p>";
        $this->notification_to_attendee($post_id, "Meeting invite proposal from " . get_the_title($proposer), "New meeting invite proposal from " . get_the_title($proposer), $additional_cont);

        echo 'success';
      } else
        echo 'fail';
      die();

    }
  }


  /**
   * Update meeting details 
   * 
   * 
   */

  function update_meeting()
  {
    $user = wp_get_current_user();

    $mr_id = get_post_meta($_POST['mid'], 'mm365_from_matchrequest', true);
    $auth = get_post($mr_id); // gets author from post
    $authid = $auth->post_author; // gets author id for the post
    $match_status = get_post_meta($mr_id, 'mm365_matchrequest_status', true);

    if (($user->ID != $authid) or !in_array($match_status, array("approved", "auto-approved"))) {
      echo "Unauthorised action";
      die();
    } else {
      $meeting_status = get_post_meta($_POST['mid'], 'mm365_meeting_status', true);
      $attendee = get_post_meta($_POST['mid'], 'mm365_meeting_with_company');
      $proposer = get_post_meta($_POST['mid'], 'mm365_proposed_company_id', true);

      $prop_timezone = $_POST['proposer_timezone'];
      //Convert timestamp based on users choice
      $first_choice = $_POST['first_choice'];
      $first_choice_st = $_POST['first_choice_starttime'];
      $first_choice_et = $_POST['first_choice_endtime'];
      $fc_start = $first_choice . ' ' . $first_choice_st;
      $fc_end = $first_choice . ' ' . $first_choice_et;

      $s1_start = $this->make_timestamp($fc_start, $prop_timezone);
      $s1_end = $this->make_timestamp($fc_end, $prop_timezone);

      $first = $this->meetingtime_timestamp(date('m/d/Y', $s1_start), date('h:ia', $s1_start), date('h:ia', $s1_end));
      $fst_available = $this->is_slot_availabale_self($first['start'], $first['end'], $attendee[1], $proposer, $_POST['mid']);

      if ($_POST['second_choice'] != '' and $_POST['second_choice_endtime'] != '') {

        $second_choice = $_POST['second_choice'];
        $second_choice_st = $_POST['second_choice_starttime'];
        $second_choice_et = $_POST['second_choice_endtime'];
        $sc_start = $second_choice . ' ' . $second_choice_st;
        $sc_end = $second_choice . ' ' . $second_choice_et;

        $s2_start = $this->make_timestamp($sc_start, $prop_timezone);
        $s2_end = $this->make_timestamp($sc_end, $prop_timezone);

        $second = $this->meetingtime_timestamp(date('m/d/Y', $s2_start), date('h:ia', $s2_start), date('h:ia', $s1_end));
        $snd_available = $this->is_slot_availabale_self($second['start'], $second['end'], $attendee[1], $proposer, $_POST['mid']);

      } else
        $snd_available = '';

      if ($_POST['third_choice'] != '' and $_POST['third_choice_endtime'] != '') {

        $third_choice = $_POST['third_choice'];
        $third_choice_st = $_POST['third_choice_starttime'];
        $third_choice_et = $_POST['third_choice_endtime'];
        $tc_start = $third_choice . ' ' . $third_choice_st;
        $tc_end = $third_choice . ' ' . $third_choice_et;

        $s3_start = $this->make_timestamp($tc_start, $prop_timezone);
        $s3_end = $this->make_timestamp($tc_end, $prop_timezone);

        $third = $this->meetingtime_timestamp(date('m/d/Y', $s3_start), date('h:ia', $s3_start), date('h:ia', $s3_end));
        $thd_available = $this->is_slot_availabale_self($third['start'], $third['end'], $attendee[1], $proposer, $_POST['mid']);

      } else
        $thd_available = '';


      if ($fst_available == 'no' or $snd_available == 'no' or $thd_available == 'no') {
        echo 'slot_error';
        die();
      }


      if ($meeting_status == 'proposed'):
        $post_information = array(
          'ID' => $_POST['mid'],
          'post_title' => wp_strip_all_tags($_POST['meeting_title']),
          'meta_input' => array(
            'mm365_meeting_title' => $_POST['meeting_title'],
            'mm365_meeting_date_1' => $_POST['first_choice'],
            'mm365_meeting_date_1_from' => $_POST['first_choice_starttime'],
            'mm365_meeting_date_1_to' => $_POST['first_choice_endtime'],
            'mm365_meeting_date_2' => $_POST['second_choice'],
            'mm365_meeting_date_2_from' => $_POST['second_choice_starttime'],
            'mm365_meeting_date_2_to' => $_POST['second_choice_endtime'],
            'mm365_meeting_date_3' => $_POST['third_choice'],
            'mm365_meeting_date_3_from' => $_POST['third_choice_starttime'],
            'mm365_meeting_date_3_to' => $_POST['third_choice_endtime'],
            'mm365_meeting_agenda' => $_POST['meeting_agenda'],
            'mm365_meeting_status' => 'proposed',
            'mm365_proposer_timezone' => $_POST['proposer_timezone']
          ),
          'post_type' => 'mm365_meetings',
        );
        $post_id = wp_update_post($post_information);
        //Time slots
        delete_post_meta($post_id, 'mm365_meeting_slots');
        //$first  = $this->meetingtime_timestamp(date('m/d/Y',$converted_ts_start),date('h:ia',$converted_ts_start),date('h:ia',$converted_ts_end));
        add_post_meta($post_id, 'mm365_meeting_slots', $s1_start . "|" . $s1_end, false);


        if ($_POST['second_choice'] != ''):
          //$second = $this->meetingtime_timestamp($_POST['second_choice'],$_POST['second_choice_starttime'],$_POST['second_choice_endtime']);
          add_post_meta($post_id, 'mm365_meeting_slots', $s2_start . "|" . $s2_end, false);
        else:
          add_post_meta($post_id, 'mm365_meeting_slots', "", false);
        endif;

        if ($_POST['third_choice'] != ''):
          //$third  = $this->meetingtime_timestamp($_POST['third_choice'],$_POST['third_choice_starttime'],$_POST['third_choice_endtime']);
          add_post_meta($post_id, 'mm365_meeting_slots', $s3_start . "|" . $s3_end, false);
        else:
          add_post_meta($post_id, 'mm365_meeting_slots', "", false);
        endif;

          //Proposer conference participation
          if ( metadata_exists( 'post', $post_id, 'mm365_meeting_in_conference' ) ) {
             delete_post_meta($post_id,'mm365_meeting_in_conference');
          }
          add_post_meta($post_id, 'mm365_meeting_in_conference', $_POST['conference_participation'], true);

        if ($post_id != '')
          echo 'success';
        else
          echo 'failed';
      else:
        echo 'edit_lock';
      endif;
    }
    die();
  }



  /**
   * 
   * 
   */
  function schedule_meeting()
  {

    $type = $_REQUEST['meeting_type'];
    $details = $_REQUEST['meeting_details'];
    $meeting_id = $_REQUEST['meeting_id'];
    //update meeting status to scheduled
    update_post_meta($meeting_id, 'mm365_meeting_status', 'scheduled');
    //Add info
    update_post_meta($meeting_id, 'mm365_meeting_type', $type);
    update_post_meta($meeting_id, 'mm365_meeting_details', $details);

    //Get details
    $accepted_slot = get_post_meta($meeting_id, 'mm365_accepted_meeting_slot', true);
    if ($accepted_slot != 4):
      // //Adjust array position 
      $array_pos = ($accepted_slot - 1);
      $slot = get_post_meta($meeting_id, 'mm365_meeting_slots');
      $accepted_date = explode("|", $slot[$array_pos]);
      $meeting_slot = date('m/d/Y', $accepted_date[0]) . " between " . date('h:i A', $accepted_date[0]) . " - " . date('h:i A', $accepted_date[1]);

    else:
      $rescheduled_requested_to = get_post_meta($meeting_id, 'mm365_meeting_reschedule_timestamp', true);
      $proposed_date = explode("|", $rescheduled_requested_to);
      $meeting_slot = date('m/d/Y', $proposed_date[0]) . " between " . date('h:i A', $proposed_date[0]) . " - " . date('h:i A', $proposed_date[1]);

    endif;

    //to attendee
    $subject = "Meeting with " . get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " is scheduled on " . $meeting_slot;
    $mail_title = $subject;

    //Proposers location
    $proposer = get_post_meta($meeting_id, 'mm365_proposed_company_id', true);
    $proposer_country = $this->get_countryname(get_post_meta($proposer, 'mm365_company_country', true));
    $proposer_state = $this->get_statename(get_post_meta($proposer, 'mm365_company_state', true));
    $proposer_city = $this->get_cityname(get_post_meta($proposer, 'mm365_company_city', true));

    $additional_cont = "<p>" . get_post_meta($meeting_id, 'mm365_proposed_company', true) . " $proposer_city $proposer_state - $proposer_country has scheduled a meeting with you on " . $meeting_slot . " to discuss regarding " . get_the_title($meeting_id) . ".</p>
    <p>Meeting type: " . $type . "</p>
    <p>Meeting details: " . $details . "</p>
    <p>Please click on the below button to login and view the meeting details.</p>";
    $this->notification_to_attendee($meeting_id, $subject, $mail_title, $additional_cont);

    return true;

  }




  /**
   * Reschedule meeting
   * 
   */
  function reschedule_meeting()
  {

    $mid = $_POST['mid'];
    $first_choice = $_POST['first_choice'];
    $first_choice_st = $_POST['first_choice_starttime'];
    $first_choice_et = $_POST['first_choice_endtime'];
    $fc_start = $first_choice . ' ' . $first_choice_st;
    $fc_end = $first_choice . ' ' . $first_choice_et;

    $s1_start = $this->make_timestamp($fc_start, $_POST['rescheduler_timezone']);
    $s1_end = $this->make_timestamp($fc_end, $_POST['rescheduler_timezone']);
    $first = $this->meetingtime_timestamp(date('m/d/Y', $s1_start), date('h:ia', $s1_start), date('h:ia', $s1_end));

    $attendee = get_post_meta($mid, 'mm365_meeting_with_company');
    $proposer = get_post_meta($mid, 'mm365_proposed_company_id', true);
    $fst_available = $this->is_slot_availabale_self($first['start'], $first['end'], $attendee[1], $proposer);

    if ($fst_available == 'no') {
      echo 'slot_error';
      die();
    } else {

      /**
       * 
       * Rescheduleing  can be done by both the parties after the meeting is set
       * Rescheduling will update the slot selection as 4 and add values to propose new time slot
       * Attendee timezone will be updated by whom ever is changing the date
       */

      //who is doing and reason
      update_post_meta($mid, 'mm365_meeting_active_reschedule_by', $_POST['rescheduled_by']);
      update_post_meta($mid, 'mm365_meeting_active_reschedule_reason', $_POST['reason_for_active_reschedule']);

      update_post_meta($mid, 'mm365_meeting_date_attendee_preffered', $_POST['first_choice']);
      update_post_meta($mid, 'mm365_meeting_date_attendee_preffered_from', $_POST['first_choice_starttime']);
      update_post_meta($mid, 'mm365_meeting_date_attendee_preffered_to', $_POST['first_choice_endtime']);
      update_post_meta($mid, 'mm365_accepted_meeting_slot', 4);
      update_post_meta($mid, 'mm365_meeting_reschedule_date', $_POST['first_choice'] . ' ' . $_POST['first_choice_starttime'] . " - " . $_POST['first_choice_endtime']);
      update_post_meta($mid, 'mm365_meeting_reschedule_timestamp', $s1_start . '|' . $s1_end);
      //timezone
      update_post_meta($mid, 'mm365_attendee_timezone', $_POST['rescheduler_timezone']);
      //New status
      update_post_meta($mid, 'mm365_meeting_status', 'rescheduled');

      //Email both parties about the rescheduling

      //Email attendee if the proposer rescheduled the meeting
      switch ($_POST['rescheduled_by']) {
        case 'proposer':
          //to attendee
          $proposer_info = get_post_meta($mid, 'mm365_proposed_company');
          $subject = $proposer_info[0] . " has rescheduled the meeting";
          $mail_title = $subject;

          $additional_cont = "<p>" . $proposer_info[1] . " from " . $proposer_info[0] . " has rescheduled the meeting titled \"" . get_post_meta($mid, 'mm365_meeting_title', true) . "\".<p>
                                <p>Reason for reschedule:<br/>" . $_POST['reason_for_active_reschedule'] . "</p>
                                <p>Please click on the below button and login to view the details.</p>";
          $this->notification_to_attendee($mid, $subject, $mail_title, $additional_cont);
          break;
        //Email proposer if attendee rescheduled the meeting
        default:
          //to proposer
          $attendee_comp = get_post_meta($mid, 'mm365_meeting_with_company', true);
          $attendee_cp = get_post_meta($mid, 'mm365_meeting_with_contactperson', true);
          $subject = $attendee_comp . " has rescheduled the meeting";
          $mail_title = $subject;

          $additional_cont = "<p>" . $attendee_cp . " from $attendee_comp has rescheduled the meeting titled \"" . get_post_meta($mid, 'mm365_meeting_title', true) . "\".<p>
                              <p>Reason for reschedule:<br/>" . $_POST['reason_for_active_reschedule'] . "</p>
                              <p>Please click on the below button and login to view the details.</p>";
          $this->notification_to_proposer($mid, $subject, $mail_title, $additional_cont);
          break;
      }

      echo 'success';
      die();
    }


  }


  /***
   * Terminate / cancel meeting
   * 
   * 
   */
  function terminate_meeting()
  {

    $mode = $_REQUEST['meeting_mode'];
    $message = $_REQUEST['terminate_meeting_message'];
    $meeting_id = $_REQUEST['meeting_id'];

    switch ($mode) {
      case 'invited':
        //decline the meeting
        update_post_meta($meeting_id, 'mm365_meeting_status', 'meeting_declined');
        update_post_meta($meeting_id, 'mm365_meeting_termination_message', $message);

        //to proposer
        $subject = get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has declined the meeting";
        $mail_title = $subject;
        $additional_cont = "<p>" . get_post_meta($meeting_id, 'mm365_meeting_with_contactperson', true) . " from " . get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has declined the meeting titled \"" . get_post_meta($meeting_id, 'mm365_meeting_title', true) . "\".<p>
            <p>Reason for declining the meeting:<br/>" . $message . "</p>
            <p>Please click on the below button to login and view the details.</p>";
        $this->notification_to_proposer($meeting_id, $subject, $mail_title, $additional_cont);

        echo "declined";
        break;
      case 'scheduled':
        //Cancel the meeting
        update_post_meta($meeting_id, 'mm365_meeting_status', 'cancelled');
        update_post_meta($meeting_id, 'mm365_meeting_termination_message', $message);


        //to attendee
        $proposer_info = get_post_meta($meeting_id, 'mm365_proposed_company');
        $subject = $proposer_info[0] . " has cancelled the meeting";
        $mail_title = $subject;
        $additional_cont = "<p>" . $proposer_info[1] . " from " . $proposer_info[0] . " has cancelled the meeting titled \"" . get_post_meta($meeting_id, 'mm365_meeting_title', true) . "\".<p>
            <p>Reason for cancellation:<br/>" . $message . "</p>
            <p>Please click on the below button to login and view the details.</p>";
        $this->notification_to_attendee($meeting_id, $subject, $mail_title, $additional_cont);

        echo "cancelled";
        break;
    }
    die();
  }


  /**
   * Meeting which the supplier is invited as attendee
   * 
   * 
   */

  function meetings_invites()
  {

    //$attendee_company_id = $this->users_company_id(wp_get_current_user());
    $attendee_company_id = $_COOKIE['active_company_id'];

    header("Content-Type: application/json");
    $request = $_GET;

    $tz = $_REQUEST['timezone'];
    $offset = $_REQUEST['offset'];
    $dst = $_REQUEST['dst'];
    $filtering_council = $_REQUEST['council'];
    $viewer_time_zone = $this->detect_timezone_id($offset, $dst);

    $columns = array(
      0 => 'company_name',
      1 => 'council',
      2 => 'contact_person',
      3 => 'meeting_title',
      4 => 'proposed_time_slots',
      5 => 'accepted_time_slot',
      6 => 'current_status',
      7 => 'view',
      8 => 'edit',
    );

    if ($filtering_council != '') {
      $add_council_filtering = array(
        'key' => 'mm365_proposer_council_id',
        'value' => sanitize_text_field($filtering_council),
        'compare' => '='
      );
    } else
      $add_council_filtering = NULL;

    $args = array(
      'post_type' => 'mm365_meetings',
      'post_status' => 'publish',
      'posts_per_page' => $request['length'],
      'offset' => $request['start'],
      'order' => 'DESC',
      'order_by' => 'modified',
      'meta_query' => array(
        array(
          'key' => 'mm365_meeting_with_company',
          'value' => array($attendee_company_id),
          'compare' => 'IN',
        ),
        $add_council_filtering
      )
    );





    if (isset($request['order'])):
      if ($request['order'][0]['column'] == 0) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] = 'mm365_proposed_company';
      } elseif ($request['order'][0]['column'] == 1) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] = 'mm365_proposed_company';
      } elseif ($request['order'][0]['column'] == 5) {
        $args['orderby'] = array('meta_value' => $request['order'][0]['dir']);
        $args['meta_key'] = 'mm365_meeting_status';
      }
    endif;

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
          'key' => 'mm365_meeting_with_company',
          'value' => array($attendee_company_id),
          'compare' => 'IN',
        ),
        array(
          'relation' => 'OR',
          array(
            'key' => 'mm365_proposed_company',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_meeting_title',
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

    $meetings = new \WP_Query($args);

    $totalData = $meetings->found_posts;
    //echo $totalData; print_r($args); die();

    if ($meetings->have_posts()) {
      while ($meetings->have_posts()) {

        $meetings->the_post();

        $with_company = get_post_meta(get_the_ID(), 'mm365_proposed_company_id');
        $company_name = preg_replace("/&#?[a-z0-9]+;/i", " ", wp_filter_nohtml_kses($with_company[0]));
        $from_match = get_post_meta(get_the_ID(), 'mm365_from_matchrequest', true);

        $time_slots = get_post_meta(get_the_ID(), 'mm365_meeting_slots');
        $proposer_tz_get = get_post_meta(get_the_ID(), 'mm365_proposer_timezone', true);
        $attendee_tz_get = get_post_meta(get_the_ID(), 'mm365_attendee_timezone', true);


        ($proposer_tz_get != '') ? $proposer_tz = $proposer_tz_get : $proposer_tz = 'UTC';
        ($attendee_tz_get != '') ? $attendee_tz = $attendee_tz_get : $attendee_tz = 'UTC';

        $slots = "<ol class='meeting-slots-list'>";
        foreach ($time_slots as $key => $value) {
          if ($value != ''):
            $slot = explode("|", $value);
            //convert slot
            $start = $this->convert_time($slot[0], $proposer_tz, $viewer_time_zone, 'm/d/Y  h:ia');
            $end = $this->convert_time($slot[1], $proposer_tz, $viewer_time_zone, 'h:ia');
            $slots .= "<li>" . $start . " - " . $end . "</li>";
          endif;
        }
        //$slots .= "<li>".$proposer_tz."</li>";
        $slots .= "</ol>";

        $accepted = get_post_meta(get_the_ID(), 'mm365_accepted_meeting_slot', true);
        if ($accepted != FALSE and $accepted <= 3):
          $array_pos = ($accepted - 1);
          $slot = get_post_meta(get_the_ID(), 'mm365_meeting_slots');

          $accepted_date = explode("|", $slot[$array_pos]);
          $start = $this->convert_time($accepted_date[0], $proposer_tz, $viewer_time_zone, 'm/d/Y  h:ia');
          $end = $this->convert_time($accepted_date[1], $proposer_tz, $viewer_time_zone, 'h:ia');
          $accepted_slot = $start . " - " . $end;
        elseif ($accepted != FALSE and $accepted == 4):
          //convert from attendee zone
          $slot = get_post_meta(get_the_ID(), 'mm365_meeting_reschedule_timestamp', true);
          $accepted_date = explode("|", $slot);
          $start = $this->convert_time($accepted_date[0], $attendee_tz, $viewer_time_zone, 'm/d/Y  h:ia');
          $end = $this->convert_time($accepted_date[1], $attendee_tz, $viewer_time_zone, 'h:ia');
          $accepted_slot = $start . " - " . $end;
          //$accepted_slot .= get_post_meta( get_the_ID(), 'mm365_meeting_reschedule_date',true);
        else:
          $accepted_slot = "-";
        endif;

        $meeting_status = get_post_meta(get_the_ID(), 'mm365_meeting_status', true);
        $company_info = get_post_meta(get_the_ID(), 'mm365_proposed_company');
        //Attendees council
        $attendees_council = get_post_meta(get_the_ID(), 'mm365_proposer_council_id', true);

        $nestedData = array();
        $nestedData[] = '<a href="' . site_url() . '/view-company?cid=' . ($with_company[0]) . '&mr_id=' . $from_match . '">' . $company_info[0] . '</a>';
        $nestedData[] = get_post_meta($attendees_council, 'mm365_council_shortname', true);
        $nestedData[] = $company_info[1];
        $nestedData[] = get_post_meta(get_the_ID(), 'mm365_meeting_title', true);
        $nestedData[] = $slots;
        $nestedData[] = $accepted_slot;
        $nestedData[] = "<span class='meeting_status " . $meeting_status . "'>" . preg_replace('/\_+/', ' ', $meeting_status) . "</span>";


        $url_invite = site_url() . '/meeting-details?mid=' . get_the_ID();
        $nestedData[] = '<a href="' . add_query_arg('_wpnonce', wp_create_nonce('meeting_invite'), $url_invite) . '">View</a>';
        $nestedData[] = ''; //Edit mode pending

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
   * List of meetings scheduled buy supplier/buyer
   * 
   * 
   */
  function meetings_scheduled()
  {
    //$propoer_company_id = $this->users_company_id(wp_get_current_user());
    $propoer_company_id = $_COOKIE['active_company_id'];

    header("Content-Type: application/json");

    $request = $_GET;

    //Viewwer timezone
    $tz = $_REQUEST['timezone'];
    $offset = $_REQUEST['offset'];
    $dst = $_REQUEST['dst'];
    $viewer_time_zone = $this->detect_timezone_id($offset, $dst);

    //From specific council filtering
    $filtering_council = $_REQUEST['council'];

    if ($filtering_council != '') {
      $add_council_filtering = array(
        'key' => 'mm365_attendees_council_id',
        'value' => sanitize_text_field($filtering_council),
        'compare' => '='
      );
    } else
      $add_council_filtering = NULL;


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
          'value' => $propoer_company_id,
          'compare' => '=',
        ),
        $add_council_filtering

      )
    );




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
          'value' => $propoer_company_id,
          'compare' => '=',
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

    $meetings = new \WP_Query($args);
    $totalData = $meetings->found_posts;
    //echo $totalData ; print_r($args); die();
    if ($meetings->have_posts()) {
      while ($meetings->have_posts()) {

        $meetings->the_post();

        $with_company = get_post_meta(get_the_ID(), 'mm365_meeting_with_company');
        $company_name = preg_replace("/&#?[a-z0-9]+;/i", " ", wp_filter_nohtml_kses($with_company[0]));
        $from_match = get_post_meta(get_the_ID(), 'mm365_from_matchrequest', true);

        $get_proposer_tz = get_post_meta(get_the_ID(), 'mm365_proposer_timezone', true);
        $get_attendee_tz = get_post_meta(get_the_ID(), 'mm365_attendee_timezone', true);

        ($get_proposer_tz != '') ? $proposer_tz = $get_proposer_tz : $proposer_tz = 'UTC';
        ($get_attendee_tz != '') ? $attendee_tz = $get_attendee_tz : $attendee_tz = 'UTC';

        //Proposed
        $time_slots = get_post_meta(get_the_ID(), 'mm365_meeting_slots');
        $slots = "<ol class='meeting-slots-list'>";
        foreach ($time_slots as $key => $value) {
          if ($value != ''):
            $slot = explode("|", $value);
            //convert slot
            $start = $this->convert_time($slot[0], $proposer_tz, $viewer_time_zone, 'm/d/Y  h:ia');
            $end = $this->convert_time($slot[1], $proposer_tz, $viewer_time_zone, 'h:ia');
            $slots .= "<li>" . $start . " - " . $end . "</li>";
          endif;
        }
        $slots .= "</ol>";

        //Accepted
        $accepted = get_post_meta(get_the_ID(), 'mm365_accepted_meeting_slot', true);
        if ($accepted != FALSE and $accepted <= 3):
          $array_pos = ($accepted - 1);
          $slot = get_post_meta(get_the_ID(), 'mm365_meeting_slots');

          $accepted_date = explode("|", $slot[$array_pos]);
          $start = $this->convert_time($accepted_date[0], $proposer_tz, $viewer_time_zone, 'm/d/Y  h:ia');
          $end = $this->convert_time($accepted_date[1], $proposer_tz, $viewer_time_zone, 'h:ia');
          $accepted_slot = $start . " - " . $end;
        elseif ($accepted != FALSE and $accepted == 4):
          //convert from attendee zone
          $slot = get_post_meta(get_the_ID(), 'mm365_meeting_reschedule_timestamp', true);
          $accepted_date = explode("|", $slot);
          $start = $this->convert_time($accepted_date[0], $attendee_tz, $viewer_time_zone, 'm/d/Y  h:ia');
          $end = $this->convert_time($accepted_date[1], $attendee_tz, $viewer_time_zone, 'h:ia');
          $accepted_slot = $start . " - " . $end;
          //$accepted_slot .= get_post_meta( get_the_ID(), 'mm365_meeting_reschedule_date',true);
        else:
          $accepted_slot = "-";
        endif;

        $meeting_status = get_post_meta(get_the_ID(), 'mm365_meeting_status', true);

        //Attendees council
        $attendees_council = get_post_meta(get_the_ID(), 'mm365_attendees_council_id', true);

        $nestedData = array();
        $nestedData[] = $this->get_certified_badge($with_company[1], true) . '<a href="' . site_url() . '/view-company?cid=' . ($with_company[1]) . '&mr_id=' . $from_match . '">' . $company_name . '</a>';
        $nestedData[] = get_post_meta($attendees_council, 'mm365_council_shortname', true);
        $nestedData[] = get_post_meta(get_the_ID(), 'mm365_meeting_with_contactperson', true);
        $nestedData[] = get_post_meta(get_the_ID(), 'mm365_meeting_title', true);
        $nestedData[] = $slots;
        $nestedData[] = $accepted_slot;
        $nestedData[] = "<span class='meeting_status " . $meeting_status . "'>" . preg_replace('/\_+/', ' ', $meeting_status) . "</span>";
        $nestedData[] = '<a href="' . site_url() . '/meeting-details?mid=' . get_the_ID() . '">View</a>';
        if ($meeting_status == 'proposed'):
          $nestedData[] = '<a href="' . add_query_arg('_wpnonce', wp_create_nonce('edit_meeting'), site_url() . '/edit-meeting?mid=' . get_the_ID()) . '">Edit</a>'; //Edit mode pending
        else:
          $nestedData[] = '<span class="text-disabled">Edit</span>';
        endif;
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
   * Update meeting details
   * v2.0 Onwards
   * 
   */

  function update_meeting_details()
  {

    $nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($nonce, 'meeting_ajax_nonce') or !is_user_logged_in()) {
      die();
    }

    $details = wp_kses_post($_POST['meeting_details']);
    $mid = sanitize_text_field($_POST['meeting_id']);
    if (update_post_meta($mid, 'mm365_meeting_details', $details)) {


      $proposer_info = get_post_meta($mid, 'mm365_proposed_company');
      $subject = $proposer_info[0] . " has updated the meeting details";
      $mail_title = $subject;

      $additional_cont = "<p>" . $proposer_info[1] . " from " . $proposer_info[0] . " has updated the meeting details titled \"" . get_post_meta($mid, 'mm365_meeting_title', true) . "\".<p>
                      <p>Please click on the below button and login to view the details.</p>";

      $this->notification_to_attendee($mid, $subject, $mail_title, $additional_cont);

      echo '1';

      die();
    } else {
      echo '0';
      die();
    }


  }


  /**
   * 
   * 
   * 
   */


  function meeting_invite_response()
  {


    $response_mode = $_REQUEST['preffered'];
    $meeting_id = $_REQUEST['meeting_id'];

    switch ($response_mode) {
      case 'first_slot':
        add_post_meta($meeting_id, 'mm365_accepted_meeting_slot', '1', true);
        update_post_meta($meeting_id, 'mm365_meeting_status', 'accepted');

        //to proposer
        $subject = get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has accepted a suitable timeslot for the meeting";
        $mail_title = $subject;
        $additional_cont = "<p>" . get_post_meta($meeting_id, 'mm365_meeting_with_contactperson', true) . " from " . get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has accepted your meeting invite.
            Please click on the button below to login and view the meeting details.
            </p>";
        $this->notification_to_proposer($meeting_id, $subject, $mail_title, $additional_cont);

        echo '1';
        break;
      case 'second_slot':
        add_post_meta($meeting_id, 'mm365_accepted_meeting_slot', '2', true);
        update_post_meta($meeting_id, 'mm365_meeting_status', 'accepted');

        $subject = get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has accepted a suitable timeslot for the meeting";
        $mail_title = $subject;
        $additional_cont = "<p>" . get_post_meta($meeting_id, 'mm365_meeting_with_contactperson', true) . " from " . get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has accepted your meeting invite.
            Please click on the button below to login and view the meeting details.
            </p>";
        $this->notification_to_proposer($meeting_id, $subject, $mail_title, $additional_cont);

        echo '2';
        break;
      case 'third_slot':
        add_post_meta($meeting_id, 'mm365_accepted_meeting_slot', '3', true);
        update_post_meta($meeting_id, 'mm365_meeting_status', 'accepted');

        //to proposer
        $subject = get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has accepted a suitable timeslot for the meeting";
        $mail_title = $subject;
        $additional_cont = "<p>" . get_post_meta($meeting_id, 'mm365_meeting_with_contactperson', true) . " from " . get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has accepted your meeting invite.
            Please click on the button below to login and view the meeting details.
            </p>";
        $this->notification_to_proposer($meeting_id, $subject, $mail_title, $additional_cont);

        echo '3';
        break;
      case 'requesting_new_slot':
        add_post_meta($meeting_id, 'mm365_accepted_meeting_slot', '4', true);

        $reschedule_choice = $_POST['reschedule_date'];
        $reschedule_choice_st = $_POST['reschedule_time_from'];
        $reschedule_choice_et = $_POST['reschedule_time_to'];
        $resc_start = $reschedule_choice . ' ' . $reschedule_choice_st;
        $resc_end = $reschedule_choice . ' ' . $reschedule_choice_et;

        $resch_start = $this->make_timestamp($resc_start, $_POST['attendee_timezone']);
        $resch_end = $this->make_timestamp($resc_end, $_POST['attendee_timezone']);

        //$new_slot  = $this->meetingtime_timestamp($_POST['reschedule_date'],$_POST['reschedule_time_from'],$_POST['reschedule_time_to']);

        add_post_meta($meeting_id, 'mm365_meeting_reschedule_timestamp', $resch_start . "|" . $resch_end, true);
        add_post_meta($meeting_id, 'mm365_meeting_reschedule_date', $_POST['reschedule_date'] . " " . $_POST['reschedule_time_from'] . " - " . $_POST['reschedule_time_to'], true);
        update_post_meta($meeting_id, 'mm365_meeting_status', 'proposed_new_time');
        update_post_meta($meeting_id, 'mm365_attendee_timezone', $_POST['attendee_timezone']);

        //to proposer
        $subject = get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " is requesting to reschedule the meeting";
        $mail_title = $subject;
        $additional_cont = "<p>" . get_post_meta($meeting_id, 'mm365_meeting_with_contactperson', true) . " from " . get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " is requesting you to reschedule the meeting titled \"" . get_the_title($meeting_id) . "\".</p>
            <p>Please click on the button below to login and view the reschedule details.</p>";
        $this->notification_to_proposer($meeting_id, $subject, $mail_title, $additional_cont);

        echo '4';
        break;
      case 'decline_invite':
        $message = $_REQUEST['decline_message'];
        add_post_meta($meeting_id, 'mm365_invite_declined_message', $message, true);
        update_post_meta($meeting_id, 'mm365_meeting_status', 'declined');

        //to proposer
        $subject = get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has declined the meeting invite";
        $mail_title = $subject;
        $additional_cont = "<p>" . get_post_meta($meeting_id, 'mm365_meeting_with_contactperson', true) . " from " . get_post_meta($meeting_id, 'mm365_meeting_with_company', true) . " has declined the meeting titled " . get_the_title($meeting_id) . ".</p>
            <p>Reason for declining the meeting:<br/> " . get_post_meta($meeting_id, 'mm365_invite_declined_message', true) . "</p>
            <p>Please click on the button below to login and view the reschedule details.</p>";
        $this->notification_to_proposer($meeting_id, $subject, $mail_title, $additional_cont);

        echo '5';
        break;
    }

    die();


  }


  /**
   * Quick reports - Download
   * 
   * 
   */
  function meeting_quickreport_download($period = 'week', $status = NULL, $sa_council_filter = NULL)
  {

    $user = wp_get_current_user();
    //Check if council manager is reading the reports
    $council_id = $this->get_userDC($user->ID);

    //IF sa_council_filter is present ovveride councilid
    if ($sa_council_filter != NULL)
      $council_id = $sa_council_filter;

    //sacouncilfilter

    if ($council_id != '') {
      $council_filter = array(
        'key' => 'mm365_attendees_council_id',
        'value' => $council_id,
        'compare' => '=',
      );
    } else
      $council_filter = '';

    $quickreports_meeting_args = array(
      'posts_per_page' => -1,    // No limit
      'post_type' => 'mm365_meetings',
      'post_status' => array('publish'),
      'date_query' => array(
        array('column' => 'post_modified', 'after' => '1 ' . $period . ' ago')
      ),
      'meta_query' => array(
        array(
          'key' => 'mm365_meeting_status',
          'value' => array('scheduled', 'rescheduled'),
          'compare' => 'in',
        ),
        $council_filter
      )
    );
    $file_name = "Report - Meetings scheduled with in a " . $period;

    $data = array();
    $meetings_query = new \WP_Query($quickreports_meeting_args);

    while ($meetings_query->have_posts()):
      $meetings_query->the_post();
      $title = get_post_meta(get_the_ID(), 'mm365_meeting_title', true);
      $agenda = get_post_meta(get_the_ID(), 'mm365_meeting_agenda', true);
      $meeting_slot = get_post_meta(get_the_ID(), 'mm365_accepted_meeting_slot', true);
      $details = get_post_meta(get_the_ID(), 'mm365_meeting_details', true);
      $status = get_post_meta(get_the_ID(), 'mm365_meeting_status', true);
      $type = get_post_meta(get_the_ID(), 'mm365_meeting_type', true);
      $with_company = get_post_meta(get_the_ID(), 'mm365_meeting_with_company', true);
      $with_contactemail = get_post_meta(get_the_ID(), 'mm365_meeting_with_contactemail', true);
      $with_contactperson = get_post_meta(get_the_ID(), 'mm365_meeting_with_contactperson', true);
      $company = get_post_meta(get_the_ID(), 'mm365_proposed_company');
      $timezone = get_post_meta(get_the_ID(), 'mm365_proposer_timezone', true);
      $accepted_slot = get_post_meta(get_the_ID(), 'mm365_accepted_meeting_slot', true);

      if ($accepted_slot != 4):
        $array_pos = ($accepted_slot - 1);
        $slot = get_post_meta(get_the_ID(), 'mm365_meeting_slots');
        $accepted_date = explode("|", $slot[$array_pos]);
        $date = date('m/d/Y', $accepted_date[0]);
        $from = date('h:i A', $accepted_date[0]);
        $to = date('h:i A', $accepted_date[1]);
      else:
        $rescheduled_requested_to = get_post_meta(get_the_ID(), 'mm365_meeting_reschedule_timestamp', true);
        $proposed_date = explode("|", $rescheduled_requested_to);
        $date = date('m/d/Y', $proposed_date[0]);
        $from = date('h:i A', $proposed_date[0]);
        $to = date('h:i A', $proposed_date[1]);
      endif;
      //Get Buyer Council Details
      $council_buyer_id = get_post_meta(get_the_ID(), 'mm365_proposer_council_id', true);
      //  $council_name       = get_the_title($council_buyer_id);
      $council_buyer_short_name = get_post_meta($council_buyer_id, 'mm365_council_shortname', true);

      //Get Seller Council Details
      $council_seller_id = get_post_meta(get_the_ID(), 'mm365_attendees_council_id', true);
      //  $council_name       = get_the_title($council_seller_id);
      $council_seller_short_name = get_post_meta($council_seller_id, 'mm365_council_shortname', true);

      $meeting_details = array(
        $title,
        preg_replace("/&#?[a-z0-9]+;/i", "", wp_filter_nohtml_kses($agenda)),
        $company[0],
        $council_buyer_short_name,
        $company[1],
        $company[2],
        $with_company,
        $council_seller_short_name,
        $with_contactperson,
        $with_contactemail,
        $date . " " . $from . "-" . $to . " (UTC)",
        $type,
        preg_replace("/&#?[a-z0-9]+;/i", "", wp_filter_nohtml_kses($details)),
        ucfirst($status)
      );
      array_push($data, $meeting_details);
    endwhile;


    $writer_2 = new XLSXWriter();
    $styles1 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#ffc00', 'color' => '#000', 'halign' => 'center', 'valign' => 'center', 'height' => 30, 'wrap_text' => true);
    $styles2 = array('font' => 'Arial', 'font-size' => 10, 'font-style' => 'bold', 'fill' => '#356ab3', 'color' => '#fff', 'halign' => 'center', 'valign' => 'center', 'height' => 20);
    $styles3 = array('border' => 'left,right,top,bottom', 'border-color' => '#000', 'border-style' => 'thin', 'wrap_text' => true, 'valign' => 'top');
    $writer_2->writeSheetHeader('Sheet1', array('1' => 'string', '2' => 'string', '3' => 'string', '4' => 'string', '5' => 'string', '6' => 'string', '7' => 'string', '8' => 'string', '9' => 'string', '10' => 'string', '11' => 'string', '12' => 'string'), $col_options = ['widths' => [30, 50, 40, 30, 30, 30, 30, 30, 30, 30, 30, 30], 'suppress_row' => true]);
    $writer_2->writeSheetRow('Sheet1', $rowdata = array($file_name, 'From ' . date("m/d/Y", strtotime(date("m/d/Y", strtotime(date("m/d/Y"))) . "-1 " . $period)) . ' To ' . date('m/d/Y', time())), $styles1);
    $writer_2->writeSheetRow('Sheet1', $rowdata = array(
      'Meeting title',
      'Agenda',
      'Buyer company',
      'Buyer council',
      'Buyer contact person',
      'Buyer email',
      'Supplier company',
      'Supplier council',
      'Supplier contact person',
      'Supplier email',
      'Meeting time',
      'Meeting type',
      'Meeting details',
      'Status'
    ), $styles2);

    foreach ($data as $dat) {
      $writer_2->writeSheetRow('Sheet1', $dat, $styles3);
    }

    $file_2 = $file_name . '.xlsx';
    $writer_2->writeToFile($file_2);

    if (file_exists($file_2)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="' . basename($file_2) . '"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file_2));
      readfile($file_2);
      unlink($file_2);
      exit;
    }



  }


  /**
   * Quick Reports - View 
   * 
   * 
   */
  function mm365_admin_viewreport_meetings()
  {


    $user = wp_get_current_user();

    //Check if council manager is reading the reports
    $council_id = $this->get_userDC($user->ID);


    $tz = $_REQUEST['timezone'];
    $offset = $_REQUEST['offset'];
    $dst = $_REQUEST['dst'];
    $admin_time_zone = $this->detect_timezone_id($offset, $dst);

    header("Content-Type: application/json");

    $request = $_GET;
    $period = $_REQUEST['period'];

    //If current user is not council manager, check if super admin is filterin view with specific council
    $is_admin_filtering = 'no';
    if ($_REQUEST['sa_council_filter'] != '' and $council_id == '') {
      $is_admin_filtering = 'yes';
    }

    //override council id by admin selected council id while filtering
    if ($council_id == '') {
      $council_id = $_REQUEST['sa_council_filter'];
    }

    //If admin is not filtering or selected all council, this will be skipped in council_id check below
    $columns = array(
      0 => 'meeting_title',
      1 => 'buyer',
      2 => 'supplier',
      3 => 'meeting_time',
      4 => 'meeting_type',
      5 => 'status'
    );


    if ($council_id != '') {
      $council_filter = array(
        'key' => 'mm365_attendees_council_id',
        'value' => $council_id,
        'compare' => '=',
      );
    } else
      $council_filter = '';

    $args = array(
      'post_type' => 'mm365_meetings',
      'post_status' => 'publish',
      'posts_per_page' => $request['length'],
      'offset' => $request['start'],
      'order' => 'DESC',
      'order_by' => 'modified',
      'date_query' => array(
        array('column' => 'post_modified', 'after' => '1 ' . $period . ' ago')
      ),
      'meta_query' => array(
        array(
          'key' => 'mm365_meeting_status',
          'value' => array('scheduled', 'rescheduled'),
          'compare' => 'in',
        ),
        $council_filter
      )
    );

    if (isset($request['order'])):
      if ($request['order'][0]['column'] == 0 and $request['order'][0]['dir'] != '') {
        $args['orderby'] = array('title' => $request['order'][0]['dir']);
      }
    endif;


    //Council ID condition to search
    if ($council_id == '') {
      $conditional_council_search = array(
        'relation' => 'OR',
        array(
          'key' => 'mm365_attendees_council_id',
          'value' => sanitize_text_field($request['search']['value']),
          'compare' => 'LIKE'
        ),

        array(
          'key' => 'mm365_proposer_council_id',
          'value' => sanitize_text_field($request['search']['value']),
          'compare' => 'LIKE'
        ),
      );
    } else {
      $conditional_council_search = NULL;
    }

    if (!empty($request['search']['value'])) {

      //Seach stuff goes here
      $args['meta_query'] = array(
        array(
          'key' => 'mm365_meeting_status',
          'value' => array('scheduled', 'rescheduled'),
          'compare' => 'in',
        ),
        $council_filter,
        array(
          'relation' => 'OR',
          array(
            'key' => 'mm365_meeting_title',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_meeting_type',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_meeting_with_company',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_proposed_company',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_meeting_with_contactemail',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_meeting_with_contactperson',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          array(
            'key' => 'mm365_meeting_status',
            'value' => sanitize_text_field($request['search']['value']),
            'compare' => 'LIKE'
          ),
          $conditional_council_search


        )
      );

    }

    $meetings_query = new \WP_Query($args);
    $totalData = $meetings_query->found_posts;

    if ($meetings_query->have_posts()) {
      while ($meetings_query->have_posts()) {
        $meetings_query->the_post();

        $title = get_post_meta(get_the_ID(), 'mm365_meeting_title', true);
        $agenda = get_post_meta(get_the_ID(), 'mm365_meeting_agenda', true);
        $meeting_slot = get_post_meta(get_the_ID(), 'mm365_accepted_meeting_slot', true);
        $details = get_post_meta(get_the_ID(), 'mm365_meeting_details', true);
        $status = get_post_meta(get_the_ID(), 'mm365_meeting_status', true);
        $type = get_post_meta(get_the_ID(), 'mm365_meeting_type', true);
        $with_company = get_post_meta(get_the_ID(), 'mm365_meeting_with_company', true);
        $with_contactemail = get_post_meta(get_the_ID(), 'mm365_meeting_with_contactemail', true);
        $with_contactperson = get_post_meta(get_the_ID(), 'mm365_meeting_with_contactperson', true);
        $company = get_post_meta(get_the_ID(), 'mm365_proposed_company');
        $timezone = get_post_meta(get_the_ID(), 'mm365_proposer_timezone', true);
        $accepted_slot = get_post_meta(get_the_ID(), 'mm365_accepted_meeting_slot', true);

        if ($accepted_slot != 4):
          $array_pos = ($accepted_slot - 1);
          $slot = get_post_meta(get_the_ID(), 'mm365_meeting_slots');
          $accepted_date = explode("|", $slot[$array_pos]);
          $date = $this->convert_time($accepted_date[0], $timezone, $admin_time_zone, 'm/d/Y');
          $from = $this->convert_time($accepted_date[0], $timezone, $admin_time_zone, 'h:i A');
          $to = $this->convert_time($accepted_date[1], $timezone, $admin_time_zone, 'h:i A');
        else:
          $rescheduled_requested_to = get_post_meta(get_the_ID(), 'mm365_meeting_reschedule_timestamp', true);
          $attendee_timezone = get_post_meta(get_the_ID(), 'mm365_proposer_timezone', true);
          $proposed_date = explode("|", $rescheduled_requested_to);
          $date = $this->convert_time($proposed_date[0], $attendee_timezone, $admin_time_zone, 'm/d/Y');
          $from = $this->convert_time($proposed_date[0], $attendee_timezone, $admin_time_zone, 'h:i A');
          $to = $this->convert_time($proposed_date[1], $attendee_timezone, $admin_time_zone, 'h:i A');
        endif;
        //Get Buyer Council Details
        $council_buyer_id = get_post_meta(get_the_ID(), 'mm365_proposer_council_id', true);
        //  $council_name       = get_the_title($council_buyer_id);
        $council_buyer_short_name = get_post_meta($council_buyer_id, 'mm365_council_shortname', true);

        //Get Seller Council Details
        $council_seller_id = get_post_meta(get_the_ID(), 'mm365_attendees_council_id', true);
        //  $council_name       = get_the_title($council_seller_id);
        $council_seller_short_name = get_post_meta($council_seller_id, 'mm365_council_shortname', true);



        $nestedData = array();
        $nestedData[] = $title;
        $nestedData[] = "<div class='intable_span'>" . $company[0] . "</div><div class='intable_span'>" . $company[1] . "</div><div class='intable_span'>" . $company[2] . "</div><div class='intable_span'>Council: " . $council_buyer_short_name . "</div>";
        $nestedData[] = "<div class='intable_span'>" . $with_company . "</div><div class='intable_span'>" . $with_contactperson . "</div><div class='intable_span'>" . $with_contactemail . "</div><div class='intable_span'>Council: " . $council_seller_short_name . "</div>";
        $nestedData[] = $date . "<br/>" . $from . " - " . $to;
        $nestedData[] = $type;
        $nestedData[] = "<span class='meeting_status " . $status . "'>" . ucfirst($status) . "</span>";

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