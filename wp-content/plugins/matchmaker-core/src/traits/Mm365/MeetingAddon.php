<?php
namespace Mm365;


trait MeetingAddon
{

    /**
     * @param int $company_id
     * @param int $mr_id
     * @param int $return_id
     * @param bool $find_all
     * @return string $status
     */
    function meeting_status($company_id, $mr_id, $return_id = NULL, $find_all = FALSE)
    {

        //Find meetings 
        if ($find_all == TRUE) {
            //ignore terminated items
            $add_meta = array(
                'key' => 'mm365_meeting_status',
                'value' => array('cancelled', 'declined', 'meeting_declined'),
                'compare' => 'NOT IN',
            );
        } else
            $add_meta = array();

        $find_meetings_stat = array(
            'posts_per_page' => -1,
            // No limit
            'fields' => 'ids',
            // Reduce memory footprint
            'post_type' => 'mm365_meetings',
            'post_status' => array('publish'),
            'orderby' => 'date',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'mm365_from_matchrequest',
                    'value' => $mr_id,
                    'compare' => '=',
                ),
                array(
                    'key' => 'mm365_meeting_with_company',
                    'value' => $company_id,
                    'compare' => '=',
                ),
                $add_meta
            )


        );

        $query = new \WP_Query($find_meetings_stat);
        $status = array();
        if ($return_id == NULL) {
            return $query->found_posts;
        } else {

            foreach ($query->posts as $meeting) {
                $status = array("mid" => $meeting, "status" => get_post_meta($meeting, 'mm365_meeting_status', true));
            }
            return $status;
        }


    }



    /**
     * value in range
     * 
     * 
     */
    function withinRange($int, $min, $max)
    {
        return ($min <= $int && $int <= $max);
    }

    /**
     * 
     * 
     * 
     */

    function is_slot_availabale()
    {

        $proposer_timezone = $_POST['timezone'];
        $start = $this->make_timestamp($_POST['start'], $proposer_timezone);
        $end = $this->make_timestamp($_POST['end'], $proposer_timezone);

        $attendee = $_POST['attendee'];
        $proposer = $_POST['proposer'];
        $exclude = $_POST['exclude'];
        $_slot_free = $this->is_slot_availabale_self($start, $end, $attendee, $proposer, $exclude);

        if ($_slot_free == 'no') {
            $ret = 'n';
        } else {
            $ret = '';
        }

        echo $ret;
        wp_die();


    }

    /**
     * If the selected slot is available
     * 
     * 
     */
    function is_slot_availabale_self($start, $end, $attendee, $proposer, $exclude = NULL)
    {

        //echo  $start.$end.$attendee.$proposer;
        $find_meetings_stat = array(
            'posts_per_page' => -1,
            // No limit
            'fields' => 'ids',
            // Reduce memory footprint
            'post_type' => 'mm365_meetings',
            'post_status' => array('publish'),
            'post__not_in' => array($exclude),
            'meta_query' => array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mm365_proposed_company_id',
                        'value' => $proposer,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'mm365_proposed_company_id',
                        'value' => $attendee,
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'mm365_meeting_with_company',
                        'value' => array($attendee),
                        'compare' => 'IN',
                    ),
                    array(
                        'key' => 'mm365_meeting_with_company',
                        'value' => array($proposer),
                        'compare' => 'IN',
                    ),
                ),
                array(
                    array(
                        'key' => 'mm365_meeting_status',
                        'value' => array("accepted", "proposed", "scheduled", "proposed_new_time"),
                        'compare' => 'IN',
                    )
                )
            )


        );


        $query = new \WP_Query($find_meetings_stat);
        $_slot_free = 'y';
        foreach ($query->posts as $meeting) {
            $slots = get_post_meta($meeting, 'mm365_meeting_slots');
            $accepted_slot = get_post_meta($meeting, 'mm365_accepted_meeting_slot', true);
            //ignore slots and look for meeting time only in scheduled meetings
            if ($accepted_slot != '') {

                if ($accepted_slot < 4) {
                    $match_slot = ($accepted_slot - 1);
                    if ($slots[$match_slot] != '') {
                        $slot = explode("|", $slots[$match_slot]);
                        if ($this->withinRange($start, $slot[0], $slot[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($end, $slot[0], $slot[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($slot[0], $start, $end)) {
                            $_slot_free = 'n';
                        }
                    }
                } else {
                    $slots_proposed_get = get_post_meta($meeting, 'mm365_meeting_reschedule_timestamp', true);
                    if ($slots_proposed_get != '') {
                        $slots_proposed = explode("|", $slots_proposed_get);
                        if ($this->withinRange($start, $slots_proposed[0], $slots_proposed[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($end, $slots_proposed[0], $slots_proposed[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($slots_proposed[0], $start, $end)) {
                            $_slot_free = 'n';
                        }
                    }
                }

            } else {
                foreach ($slots as $key => $value) {
                    if ($value != ''):
                        $slot = explode("|", $value);
                        if ($this->withinRange($start, $slot[0], $slot[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($end, $slot[0], $slot[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($slot[0], $start, $end)) {
                            $_slot_free = 'n';
                        }
                    endif;
                }
                //Proposed time
                $slots_proposed_get = get_post_meta($meeting, 'mm365_meeting_reschedule_timestamp', true);
                if (!empty($slots_proposed_get)) {
                    if ($slots_proposed_get != '') {
                        $slots_proposed = explode("|", $slots_proposed_get);
                        if ($this->withinRange($start, $slots_proposed[0], $slots_proposed[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($end, $slots_proposed[0], $slots_proposed[1])) {
                            $_slot_free = 'n';
                        }
                        if ($this->withinRange($slots_proposed[0], $start, $end)) {
                            $_slot_free = 'n';
                        }
                    }
                }

            }


        }
        if ($_slot_free == 'n') {
            return 'no';
        } else {
            return 'yes';
        }

    }



    /**
     * Check meeting Ownership
     * @param int $meeting_id
     * @param int $user_company_id
     * @return string
     */
    function is_meeting_belongsto_user($meeting_id, $user_company_id)
    {

        //read mm365_proposed_company_id, mm365_meeting_with_company
        $proposed = get_post_meta($meeting_id, 'mm365_proposed_company_id', true);
        $attendee = get_post_meta($meeting_id, 'mm365_meeting_with_company');

        switch ($user_company_id) {
            case $proposed:
                $ret = 'scheduled';
                break;
            case $attendee[1]:
                $ret = 'invited';
                break;
            default:
                $ret = 'unauth';
                break;

        }

        return $ret;

    }





    /**
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param string $return - mode
     * @return mixed
     */
    function meetingtime_timestamp($date, $startTime, $endTime, $return = NULL)
    {

        //prepare date
        $start = strtotime($date . ' ' . $startTime);
        $end = strtotime($date . ' ' . $endTime);
        $diff = $end - $start;


        switch ($return) {
            case 'start':
                $ret = $start;
                break;
            case 'end':
                $ret = $end;
                break;
            case 'difference':
                $fullDays = floor($diff / (60 * 60 * 24));
                $fullHours = floor(($diff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
                $fullMinutes = floor(($diff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);
                $ret = array("days" => $fullDays, "hours" => $fullHours, "minutes" => $fullMinutes);
                break;
            default:
                $ret = array("start" => $start, "end" => $end);
                break;
        }

        return $ret;

    }



    /**
     * @param int $company_id
     * @param int $mr_id 
     * @return bool
     */
    function is_schedulable($company_id, $mr_id)
    {
        $matched_companies = maybe_unserialize(get_post_meta($mr_id, 'mm365_matched_companies', true));
        $approved_companies = array();
        foreach ($matched_companies as $key => $value) {
            if ($value[1] == '1') {
                array_push($approved_companies, $value[0]);
            }
        }
        if (in_array($company_id, $approved_companies)) {
            return true;
            //Look for already configured meetings in the combo
        } else
            return false;
    }



    /*------------------------------------------------------------
      Email notifications
      
      //New meeting proposed (both)
      //Accepted invite (both)
      //Proposed new time
      //Declined
      //Cancelled
      //Meeting declined
      --------------------------------------------------------------*/
    /**
     * Emails notification to attendees of the meeting
     * 
     * 
     */
    function notification_to_attendee($mid, $title, $subject, $additional_cont)
    {

        $attendee_email = get_post_meta($mid, 'mm365_meeting_with_contactemail', true);
        $attendee_name = get_post_meta($mid, 'mm365_meeting_with_contactperson', true);
        $attendee_alt_email = ",".get_post_meta($mid, 'mm365_meeting_with_alt_contactemail', true) ?? '';
        

        $link = site_url() . '/meeting-invites/';
        $content = '
                      <p>Hi ' . $attendee_name . ',</p>
                  ' . $additional_cont;

        $to = $attendee_email.$attendee_alt_email;
        $body = $this->mm365_email_body($title, $content, $link, 'My Meeting Invites');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        //Trigger email
        wp_mail($to, $subject, $body, $headers);

    }

    /**
     * Emails to proposer
     * 
     * 
     */

    function notification_to_proposer($mid, $title, $subject, $additional_cont)
    {

        $proposer_details = get_post_meta($mid, 'mm365_proposed_company');
        $link = site_url() . '/meetings-scheduled/';
        $content = '
                      <p>Hi ' . $proposer_details[1] . ',</p>' . $additional_cont;

        $to = $proposer_details[2];
        $body = $this->mm365_email_body($title, $content, $link, 'My Meetings');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        //Trigger email
        wp_mail($to, $subject, $body, $headers);

    }




}